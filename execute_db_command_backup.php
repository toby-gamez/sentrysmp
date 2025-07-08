<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database file for tracking executed commands
define("EXECUTED_COMMANDS_FILE", "executed_commands.json");

// Import RCON library
require "vendor/autoload.php";
use Thedudeguy\Rcon;

// Parse input data from POST request
$data = json_decode(file_get_contents("php://input"), true);

// Get username from the request or session
$username = isset($data["username"])
    ? $data["username"]
    : (isset($_SESSION["usernamemc"])
        ? $_SESSION["usernamemc"]
        : null);

// Get cart data from the request or use empty array
$cart = isset($data["cart"]) ? $data["cart"] : [];

// Check if cart is in new format (objects with id, quantity, price) or old format
$cartNeedsConversion = false;
if (!empty($cart) && is_array($cart)) {
    $firstItem = reset($cart);
    if (!is_array($firstItem) && !is_object($firstItem)) {
        $cartNeedsConversion = true;
    }
}

// Convert old format to new format if needed
if ($cartNeedsConversion) {
    $newCart = [];
    foreach ($cart as $id) {
        $newCart[] = [
            "id" => $id,
            "quantity" => 1,
            "price" => 0,
        ];
    }
    $cart = $newCart;
}

// Log the cart format we're using
error_log(
    "Cart format: " .
        ($cartNeedsConversion
            ? "Converted from old format"
            : "Using new format")
);
error_log("Cart contents: " . json_encode($cart));

// Log the username we're using
error_log("Using username for RCON commands: " . $username);

// Initialize response array
$response = [
    "success" => false,
    "message" => "",
    "command" => "",
];

// Verify username
if (!$username) {
    $response["message"] = "Please enter your Minecraft username first";
    $response["needUsername"] = true;
    echo json_encode($response);
    exit();
}

// Validate username format - méně přísné ověření
if (!$username || !preg_match('/^[a-zA-Z0-9_]{2,20}$/', $username)) {
    $response["message"] = "Invalid Minecraft username format: " . $username;
    $response["needUsername"] = true;
    echo json_encode($response);
    exit();
}

// Load previously executed commands
$executedCommands = [];
if (file_exists(EXECUTED_COMMANDS_FILE)) {
    $executedCommandsJson = file_get_contents(EXECUTED_COMMANDS_FILE);
    if ($executedCommandsJson) {
        $executedCommands = json_decode($executedCommandsJson, true) ?: [];
    }
}

// Get transaction ID from session or generate one if needed
$transactionId =
    $_SESSION["transaction_id"] ??
    (isset($_GET["transaction_id"]) ? $_GET["transaction_id"] : "");
if (empty($transactionId)) {
    $transactionId = uniqid("tx_", true);
    $_SESSION["transaction_id"] = $transactionId;
}

// Check if this transaction has already been processed
// Only check if we have a valid transaction ID
if (!empty($transactionId) && isset($executedCommands[$transactionId])) {
    error_log(
        "Transaction $transactionId already processed, but will process again to ensure all commands execute"
    );
    // Jen zalogujeme, ale necháme proběhnout znovu pro jistotu
    /*
    $response['success'] = false;
    $response['message'] = 'Commands for this transaction have already been executed';
    $response['alreadyExecuted'] = true;
    echo json_encode($response);
    exit;
    */
}

// Get all commands from the database (both spawners and keys)
try {
    // Initialize commands array
    $commands = [];

    // Get commands from spawners table
    $db = new SQLite3("blog.sqlite");
    $stmt = $db->prepare(
        'SELECT id, nazev, prikaz FROM spawners WHERE prikaz IS NOT NULL AND trim(prikaz) != ""'
    );
    $result = $stmt->execute();

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        if (!empty($row["prikaz"])) {
            $commands[] = [
                "id" => $row["id"],
                "name" => $row["nazev"] ?? "Spawner",
                "command" => $row["prikaz"],
                "type" => "spawner",
            ];
        }
    }

    // Get commands from keys table
    $dbKeys = new SQLite3("keys.sqlite");
    $stmtKeys = $dbKeys->prepare(
        'SELECT id, name, prikaz FROM Keys WHERE prikaz IS NOT NULL AND trim(prikaz) != ""'
    );
    $resultKeys = $stmtKeys->execute();

    while ($row = $resultKeys->fetchArray(SQLITE3_ASSOC)) {
        if (!empty($row["prikaz"])) {
            $keyId = "key_" . $row["id"];
            error_log(
                "Adding key command: ID=" .
                    $keyId .
                    ", Name=" .
                    $row["name"] .
                    ", Command=" .
                    $row["prikaz"]
            );
            $commands[] = [
                "id" => $keyId,
                "name" => $row["name"] ?? "Key",
                "command" => $row["prikaz"],
                "type" => "key",
                "originalId" => $row["id"],
            ];
        }
    }

    if (count($commands) === 0) {
        $response["message"] = "No data provided";
        echo json_encode($response);
        exit();
    }

    // Log the fetched commands
    error_log(
        "Fetched " .
            count($commands) .
            " commands from database for user: {$username}"
    );

    // RCON connection details
    $host = "45.157.17.246"; // IP address of the server
    $port = 25561; // RCON port
    $password = "539871"; // RCON password
    $timeout = 3;

    // Connect to RCON
    $rcon = new Rcon($host, $port, $password, $timeout);

    if ($rcon->connect()) {
        $successfulCommands = [];
        $failedCommands = [];

        // Track successfully executed command IDs for later deletion
        $executedCommandIds = [
            "spawner" => [],
            "key" => [],
        ];

        // Execute each command, filtering by cart contents if cart is provided
        foreach ($commands as $cmd) {
            // Check if we need to filter by cart contents
            if (!empty($cart)) {
                // For keys, check if key_id is in cart
                if ($cmd["type"] === "key") {
                    $keyMatches = false;
                    $keyId = $cmd["id"]; // Přímo použijeme ID klíče ve formátu key_123
                    $itemQuantity = 0;

                    foreach ($cart as $cartItem) {
                        // Check if cartItem is in the new format (object with id property)
                        if (is_array($cartItem) || is_object($cartItem)) {
                            $cartItemId = isset($cartItem["id"])
                                ? $cartItem["id"]
                                : null;
                            if (
                                $cartItemId === $keyId ||
                                "key_" . $cmd["originalId"] === $cartItemId
                            ) {
                                $keyMatches = true;
                                $itemQuantity = isset($cartItem["quantity"])
                                    ? intval($cartItem["quantity"])
                                    : 1;
                                break;
                            }
                        }
                        // Fallback for old format (string IDs)
                        elseif (
                            is_string($cartItem) &&
                            ($cartItem === $keyId ||
                                "key_" . $cmd["originalId"] === $cartItem)
                        ) {
                            $keyMatches = true;
                            $itemQuantity = 1;
                            break;
                        }
                    }

                    // Pokud není shoda, zkontrolujeme ještě jednou samotné ID klíče
                    if (!$keyMatches) {
                        // Přidání debugovacích informací
                        error_log(
                            "Key command not matched with cart items. Key ID: " .
                                $cmd["id"] .
                                ", Original ID: " .
                                $cmd["originalId"]
                        );
                        error_log("Cart items: " . json_encode($cart));
                        continue;
                    }

                    // Store the quantity for future use
                    $cmd["quantity"] = $itemQuantity;
                }
                // For spawners, check if id is in cart
                elseif ($cmd["type"] === "spawner") {
                    $spawnerMatches = false;
                    $itemQuantity = 0;

                    foreach ($cart as $cartItem) {
                        // Check if cartItem is in the new format (object with id property)
                        if (is_array($cartItem) || is_object($cartItem)) {
                            $cartItemId = isset($cartItem["id"])
                                ? $cartItem["id"]
                                : null;
                            if ((string) $cmd["id"] === (string) $cartItemId) {
                                $spawnerMatches = true;
                                $itemQuantity = isset($cartItem["quantity"])
                                    ? intval($cartItem["quantity"])
                                    : 1;
                                break;
                            }
                        }
                        // Fallback for old format (string or numeric IDs)
                        elseif ((string) $cmd["id"] === (string) $cartItem) {
                            $spawnerMatches = true;
                            $itemQuantity = 1;
                            break;
                        }
                    }

                    if (!$spawnerMatches) {
                        // Přidání debugovacích informací
                        error_log(
                            "Spawner command not matched with cart items. Spawner ID: " .
                                $cmd["id"]
                        );
                        error_log("Cart items: " . json_encode($cart));
                        continue;
                    }

                    // Store the quantity for future use
                    $cmd["quantity"] = $itemQuantity;
                }
            }

            // Replace any username placeholders in the command
            $commandText = str_replace(
                "{username}",
                $username,
                $cmd["command"]
            );
            $commandText = str_replace("%USERNAME%", $username, $commandText);
            $commandText = str_replace('$usernamemc', $username, $commandText);

            try {
                // Get quantity for command execution
                $quantity = isset($cmd["quantity"])
                    ? max(1, intval($cmd["quantity"]))
                    : 1;
                $allResponses = [];

                // Execute the command quantity times
                for ($i = 0; $i < $quantity; $i++) {
                    // Send the command
                    $rconResponse = $rcon->sendCommand($commandText);
                    $allResponses[] = $rconResponse;

                    // If multiple executions, add a small delay to prevent server flooding
                    usleep(200000); // 200ms delay
                }

                // Join responses with line breaks if multiple commands were executed
                $finalResponse = implode("\n", $allResponses);

                $successfulCommands[] = [
                    "name" => $cmd["name"],
                    "command" => $commandText,
                    "quantity" => $quantity,
                    "response" => $finalResponse,
                ];

                // Track this command ID for deletion
                if ($cmd["type"] === "spawner") {
                    $executedCommandIds["spawner"][] = $cmd["id"];
                } elseif ($cmd["type"] === "key") {
                    $executedCommandIds["key"][] = $cmd["originalId"];
                }
            } catch (Exception $e) {
                $failedCommands[] = [
                    "name" => $cmd["name"],
                    "command" => $commandText,
                    "error" => $e->getMessage(),
                ];
            }
        }

        // Close the connection
        $rcon->disconnect();

        // Delete successfully executed commands from database
        try {
            // Delete executed spawner commands
            if (!empty($executedCommandIds["spawner"])) {
                $spawnerIds = array_unique($executedCommandIds["spawner"]);
                $placeholders = implode(
                    ",",
                    array_fill(0, count($spawnerIds), "?")
                );
                $deleteStmt = $db->prepare(
                    "DELETE FROM spawners WHERE id IN ($placeholders)"
                );

                // Bind parameters
                $paramIndex = 1;
                foreach ($spawnerIds as $id) {
                    $deleteStmt->bindValue($paramIndex++, $id, SQLITE3_INTEGER);
                }

                $deleteResult = $deleteStmt->execute();
                error_log(
                    "Deleted " .
                        count($spawnerIds) .
                        " executed commands from spawners table"
                );
            }

            // Delete executed key commands
            if (!empty($executedCommandIds["key"])) {
                $keyIds = array_unique($executedCommandIds["key"]);
                $placeholders = implode(
                    ",",
                    array_fill(0, count($keyIds), "?")
                );
                $deleteStmt = $dbKeys->prepare(
                    "DELETE FROM Keys WHERE id IN ($placeholders)"
                );

                // Bind parameters
                $paramIndex = 1;
                foreach ($keyIds as $id) {
                    $deleteStmt->bindValue($paramIndex++, $id, SQLITE3_INTEGER);
                }

                $deleteResult = $deleteStmt->execute();
                error_log(
                    "Deleted " .
                        count($keyIds) .
                        " executed commands from keys table"
                );
            }
        } catch (Exception $e) {
            error_log("Error deleting executed commands: " . $e->getMessage());
        }

        // Track this transaction as executed
        $executedCommands[$transactionId] = [
            "timestamp" => time(),
            "username" => $username,
            "commands" => array_map(function ($cmd) {
                return $cmd["name"] . ": " . $cmd["command"];
            }, $successfulCommands),
        ];

        // Save to file
        file_put_contents(
            EXECUTED_COMMANDS_FILE,
            json_encode($executedCommands)
        );

        // Process response
        $response = [
            "success" => true,
            "message" =>
                count($successfulCommands) . " commands executed successfully",
            "commands" => $successfulCommands,
            "failedCommands" => $failedCommands,
            "username" => $username,
            "transaction_id" => $transactionId,
            "cartItems" => $cart, // Add cart items for debugging
            "commandsDeleted" => [
                "spawner" => count($executedCommandIds["spawner"]),
                "key" => count($executedCommandIds["key"]),
            ],
        ];
    } else {
        $response["message"] = "Failed to connect to the server";
    }
} catch (Exception $e) {
    $response["message"] = "Error: " . $e->getMessage();
    error_log("Error in execute_db_command.php: " . $e->getMessage());
}

// Return response as JSON
echo json_encode($response);
?>
