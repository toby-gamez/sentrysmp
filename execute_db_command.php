<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Debug log - start skriptu
error_log("=== START EXECUTE_DB_COMMAND === " . date("c"));

// Zachycení fatálních chyb
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null) {
        error_log("FATAL ERROR: " . print_r($error, true));
        // Pokud ještě nebyl žádný výstup, pošli JSON chybu
        if (!headers_sent()) {
            header("Content-Type: application/json");
        }
        echo json_encode([
            "success" => false,
            "message" => "Fatal error: " . $error["message"],
            "command" => "",
        ]);
    }
    error_log("=== END EXECUTE_DB_COMMAND === " . date("c"));
});

// Načtení .env proměnných pomocí vlucas/phpdotenv
require_once "vendor/autoload.php";
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

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

// Get cart data from the request - if no cart, exit early
$cart = isset($data["cart"]) ? $data["cart"] : [];

// Initialize response array
$response = [
    "success" => false,
    "message" => "",
    "command" => "",
];

// If no cart is provided, exit early to prevent executing all commands
if (empty($cart)) {
    $response["success"] = false;
    $response["message"] = "No items in cart to process";
    echo json_encode($response);
    exit();
}

// Function to save transaction to paid_users database
function saveToPaidUsers($username, $cart, $transactionId)
{
    try {
        $db = new SQLite3("paid_users.sqlite");

        // Create enhanced table structure if it doesn't exist
        $db->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL,
            transaction_id TEXT UNIQUE,
            cart_data TEXT,
            amount REAL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Calculate total amount from cart
        $total_amount = 0;
        foreach ($cart as $item) {
            if (isset($item["price"]) && isset($item["quantity"])) {
                $total_amount +=
                    floatval($item["price"]) * intval($item["quantity"]);
            }
        }

        // Save transaction
        $stmt = $db->prepare(
            "INSERT OR IGNORE INTO users (username, transaction_id, cart_data, amount) VALUES (:username, :transaction_id, :cart_data, :amount)"
        );
        $stmt->bindValue(":username", $username, SQLITE3_TEXT);
        $stmt->bindValue(":transaction_id", $transactionId, SQLITE3_TEXT);
        $stmt->bindValue(":cart_data", json_encode($cart), SQLITE3_TEXT);
        $stmt->bindValue(":amount", $total_amount, SQLITE3_FLOAT);
        $result = $stmt->execute();

        $db->close();

        if ($result) {
            error_log(
                "SUCCESS: Transaction saved to paid_users database: $username (Amount: $total_amount, Transaction: $transactionId)"
            );
            return true;
        } else {
            error_log(
                "FAILED: Could not save transaction to paid_users database: $username"
            );
            return false;
        }
    } catch (Exception $e) {
        error_log(
            "ERROR: Exception while saving transaction to paid_users database: " .
                $e->getMessage()
        );
        return false;
    }
}

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

// Zjednodušené: Vykonávej všechny příkazy z tabulek spawners, Keys, ranks podle id, nahraď $usernamemc jménem

try {
    $commands = [];

    // Spawners
    $db = new SQLite3("blog.sqlite");
    $spawners = $db->query(
        "SELECT id, nazev, prikaz FROM spawners WHERE prikaz IS NOT NULL AND trim(prikaz) != ''"
    );
    while ($row = $spawners->fetchArray(SQLITE3_ASSOC)) {
        if (!empty($row["prikaz"])) {
            $commands[] = [
                "id" => $row["id"],
                "name" => $row["nazev"] ?? "Spawner",
                "command" => $row["prikaz"],
                "type" => "spawner",
            ];
        }
    }

    // Keys
    $dbKeys = new SQLite3("keys.sqlite");
    $keys = $dbKeys->query(
        "SELECT id, name, prikaz FROM Keys WHERE prikaz IS NOT NULL AND trim(prikaz) != ''"
    );
    while ($row = $keys->fetchArray(SQLITE3_ASSOC)) {
        if (!empty($row["prikaz"])) {
            $commands[] = [
                "id" => $row["id"],
                "name" => $row["name"] ?? "Key",
                "command" => $row["prikaz"],
                "type" => "key",
            ];
        }
    }

    // Ranks
    $dbRanks = new SQLite3("ranks.sqlite");
    $ranks = $dbRanks->query(
        "SELECT id, nazev, prikaz FROM ranks WHERE prikaz IS NOT NULL AND trim(prikaz) != ''"
    );
    while ($row = $ranks->fetchArray(SQLITE3_ASSOC)) {
        if (!empty($row["prikaz"])) {
            $commands[] = [
                "id" => $row["id"],
                "name" => $row["nazev"] ?? "Rank",
                "command" => $row["prikaz"],
                "type" => "rank",
            ];
        }
    }

    if (count($commands) === 0) {
        $response["message"] = "No data provided";
        echo json_encode($response);
        exit();
    }

    error_log(
        "Fetched " .
            count($commands) .
            " commands from all DBs for user: {$username}"
    );

    // RCON connection details z .env
    $host = $_ENV["RCON_HOST"] ?? "127.0.0.1";
    $port = isset($_ENV["RCON_PORT"]) ? intval($_ENV["RCON_PORT"]) : 25575;
    $password = $_ENV["RCON_PASSWORD"] ?? "";
    $timeout = isset($_ENV["RCON_TIMEOUT"]) ? intval($_ENV["RCON_TIMEOUT"]) : 3;

    // Connect to RCON
    $rcon = new Rcon($host, $port, $password, $timeout);

    if ($rcon->connect()) {
        // Save transaction to paid_users database first
        saveToPaidUsers($username, $cart, $transactionId);

        $successfulCommands = [];
        $failedCommands = [];

        // Track successfully executed command IDs for later deletion
        $executedCommandIds = [
            "spawner" => [],
            "key" => [],
            "rank" => [],
        ];

        // Execute each command, filtering by cart contents
        foreach ($commands as $cmd) {
            // Always filter by cart contents - cart is guaranteed to not be empty at this point
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
                            false // odstraněno originalId, už není potřeba
                        ) {
                            $keyMatches = true;
                            $itemQuantity = isset($cartItem["quantity"])
                                ? intval($cartItem["quantity"])
                                : 1;
                            break;
                        }
                    }
                    // Fallback for old format (string IDs)
                    elseif (is_string($cartItem) && $cartItem === $keyId) {
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
                            $cmd["id"]
                    );
                    error_log("Cart items: " . json_encode($cart));
                    continue;
                }

                // Store the quantity for future use
                $cmd["quantity"] = $itemQuantity;
            }
            // For ranks, check if rank_id is in cart
            elseif ($cmd["type"] === "rank") {
                $rankMatches = false;
                $rankId = $cmd["id"]; // Přímo použijeme ID ranku ve formátu rank_123
                $itemQuantity = 0;

                foreach ($cart as $cartItem) {
                    // Check if cartItem is in the new format (object with id property)
                    if (is_array($cartItem) || is_object($cartItem)) {
                        $cartItemId = isset($cartItem["id"])
                            ? $cartItem["id"]
                            : null;
                        if (
                            $cartItemId === $rankId ||
                            false // odstraněno originalId, už není potřeba
                        ) {
                            $rankMatches = true;
                            $itemQuantity = isset($cartItem["quantity"])
                                ? intval($cartItem["quantity"])
                                : 1;
                            break;
                        }
                    }
                    // Fallback for old format (string IDs)
                    elseif (is_string($cartItem) && $cartItem === $rankId) {
                        $rankMatches = true;
                        $itemQuantity = 1;
                        break;
                    }
                }

                // Pokud není shoda, zkontrolujeme ještě jednou samotné ID ranku
                if (!$rankMatches) {
                    // Přidání debugovacích informací
                    error_log(
                        "Rank command not matched with cart items. Rank ID: " .
                            $cmd["id"]
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

            // Replace any username placeholders in the command
            $commandText = str_replace(
                "{username}",
                $username,
                $cmd["command"]
            );
            $commandText = str_replace("%USERNAME%", $username, $commandText);
            $commandText = str_replace('$usernamemc', $username, $commandText);

            try {
                $quantity = isset($cmd["quantity"])
                    ? max(1, intval($cmd["quantity"]))
                    : 1;
                $allResponses = [];

                // Execute the command quantity times
                for ($i = 0; $i < $quantity; $i++) {
                    $rconResponse = $rcon->sendCommand($commandText);
                    $allResponses[] = $rconResponse;
                    usleep(200000); // 200ms delay
                }

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
                } elseif ($cmd["type"] === "rank") {
                    $executedCommandIds["rank"][] = $cmd["originalId"];

                    // Enhanced VIP detection with better debugging
                    $isVipRank = false;
                    $vipDetectionReason = "";

                    // Check for VIP in name (case insensitive)
                    if (stripos($cmd["name"], "vip") !== false) {
                        $isVipRank = true;
                        $vipDetectionReason =
                            "VIP found in name: " . $cmd["name"];
                    }

                    // Check for VIP in command (case insensitive)
                    if (stripos($cmd["command"], "vip") !== false) {
                        $isVipRank = true;
                        $vipDetectionReason .=
                            ($vipDetectionReason ? " AND " : "") .
                            "VIP found in command: " .
                            $cmd["command"];
                    }

                    // Additional checks for common VIP patterns
                    if (
                        stripos($cmd["name"], "premium") !== false ||
                        stripos($cmd["command"], "premium") !== false ||
                        stripos($cmd["name"], "membership") !== false ||
                        preg_match(
                            "/\b(vip|premium|member)\b/i",
                            $cmd["name"]
                        ) ||
                        preg_match(
                            "/\b(vip|premium|member)\b/i",
                            $cmd["command"]
                        )
                    ) {
                        $isVipRank = true;
                        $vipDetectionReason .=
                            ($vipDetectionReason ? " AND " : "") .
                            "Premium/Membership pattern detected";
                    }

                    // Log VIP detection attempt
                    error_log(
                        "VIP Detection - Rank: " .
                            $cmd["name"] .
                            ", Command: " .
                            $cmd["command"] .
                            ", IsVIP: " .
                            ($isVipRank ? "YES" : "NO") .
                            ", Reason: " .
                            ($vipDetectionReason ?: "No VIP pattern found")
                    );

                    // Special handling for VIP rank - save to vip.sqlite database
                    if ($isVipRank) {
                        try {
                            error_log(
                                "Attempting to save VIP user to database: " .
                                    $username
                            );

                            $dbVip = new SQLite3("vip.sqlite");
                            $dbVip->exec(
                                "CREATE TABLE IF NOT EXISTS vip_users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT UNIQUE, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)"
                            );

                            // Check if user already exists
                            $checkStmt = $dbVip->prepare(
                                "SELECT COUNT(*) as count FROM vip_users WHERE username = :username"
                            );
                            $checkStmt->bindValue(
                                ":username",
                                $username,
                                SQLITE3_TEXT
                            );
                            $checkResult = $checkStmt->execute();
                            $existingCount = $checkResult->fetchArray(
                                SQLITE3_ASSOC
                            )["count"];

                            if ($existingCount > 0) {
                                error_log(
                                    "VIP user already exists in database: " .
                                        $username
                                );
                            } else {
                                $stmtVip = $dbVip->prepare(
                                    "INSERT INTO vip_users (username) VALUES (:username)"
                                );
                                $stmtVip->bindValue(
                                    ":username",
                                    $username,
                                    SQLITE3_TEXT
                                );
                                $result = $stmtVip->execute();

                                if ($result) {
                                    error_log(
                                        "SUCCESS: VIP user saved to database: " .
                                            $username .
                                            " (Reason: " .
                                            $vipDetectionReason .
                                            ")"
                                    );
                                } else {
                                    error_log(
                                        "FAILED: Could not save VIP user to database: " .
                                            $username
                                    );
                                }
                            }

                            $dbVip->close();
                        } catch (Exception $e) {
                            error_log(
                                "ERROR: Exception while saving VIP user to database: " .
                                    $username .
                                    " - " .
                                    $e->getMessage()
                            );
                        }
                    }
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

        // POZNÁMKA: Nabídky (spawners, keys a ranks) se NEMAŽOU z databází po vykonání!
        // Databáze obsahují PRODUKTY/NABÍDKY, které musí zůstat dostupné pro další zákazníky.
        // Pouze se loguje, že byly příkazy vykonány.
        error_log(
            "Príkazy vykonané pro transakci $transactionId - " .
                count($executedCommandIds["spawner"]) .
                " spawnerů, " .
                count($executedCommandIds["key"]) .
                " klíčů, " .
                count($executedCommandIds["rank"]) .
                " ranků"
        );

        // Track this transaction as executed
        // Uložení všech úspěšně vykonaných příkazů včetně ranků
        $executedCommands[$transactionId] = [
            "timestamp" => time(),
            "username" => $username,
            "commands" => array_map(function ($cmd) {
                return [
                    "name" => $cmd["name"],
                    "command" => $cmd["command"],
                    "quantity" => isset($cmd["quantity"])
                        ? $cmd["quantity"]
                        : 1,
                    "type" => isset($cmd["type"]) ? $cmd["type"] : null,
                ];
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
            "commandsExecuted" => [
                "spawner" => count($executedCommandIds["spawner"]),
                "key" => count($executedCommandIds["key"]),
                "rank" => count($executedCommandIds["rank"]),
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
