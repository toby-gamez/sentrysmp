<?php
session_start();
require "vendor/autoload.php";

use Thedudeguy\Rcon;

// Parse JSON data from the request
$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Check if this is a key command
$isKeyCommand = isset($data["key"]) && $data["key"] === true;

// Get quantity if provided, default to 1
$quantity =
    isset($data["quantity"]) && is_numeric($data["quantity"])
        ? intval($data["quantity"])
        : 1;
if ($quantity < 1) {
    $quantity = 1;
} // Safety check

// Prepare response array
$response = ["success" => false, "message" => ""];

// Check if this is a system command
$isSystemCommand = isset($data["system"]) && $data["system"] === true;

// Validate input
if (!$data) {
    $response["message"] = "No data provided";
    echo json_encode($response);
    exit();
}

// For regular commands, we need a command string
if (
    !$isSystemCommand &&
    (!isset($data["command"]) || empty($data["command"]))
) {
    $response["message"] = "No command provided";
    echo json_encode($response);
    exit();
}

// Get username from request or session
$username = isset($data["username"])
    ? $data["username"]
    : (isset($_SESSION["usernamemc"])
        ? $_SESSION["usernamemc"]
        : null);

if (!$username) {
    $response["message"] = "No username provided";
    echo json_encode($response);
    exit();
}

// Validate username format
if (!preg_match('/^[a-zA-Z0-9_]{3,16}$/', $username)) {
    $response["message"] = "Invalid username format";
    echo json_encode($response);
    exit();
}

// Load environment variables if not already loaded
if (!isset($_ENV["RCON_HOST"])) {
    require_once __DIR__ . "/vendor/autoload.php";
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// RCON connection details from environment variables
$host =
    $_ENV["RCON_HOST"] ??
    throw new Exception("RCON_HOST not set in environment");
$port =
    (int) ($_ENV["RCON_PORT"] ??
        throw new Exception("RCON_PORT not set in environment"));
$password =
    $_ENV["RCON_PASSWORD"] ??
    throw new Exception("RCON_PASSWORD not set in environment");
$timeout = 3;

// Clean and prepare the command
if ($isSystemCommand) {
    // For system commands, we use the username to execute special actions
    // For example, we'll use 'op' to give operator privileges
    $command = "op " . $username;
} elseif ($isKeyCommand) {
    // For key commands
    $keyId = isset($data["keyId"]) ? intval($data["keyId"]) : 0;

    if ($keyId <= 0) {
        $response["message"] = "Invalid key ID";
        echo json_encode($response);
        exit();
    }

    // Get command from keys database
    try {
        $db = new SQLite3("keys.sqlite");
        $stmt = $db->prepare("SELECT prikaz FROM Keys WHERE id = ?");
        $stmt->bindValue(1, $keyId, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);

        if ($row && !empty($row["prikaz"])) {
            $command = trim($row["prikaz"]);
            // Replace username placeholder
            $command = str_replace('$usernamemc', $username, $command);
            $command = str_replace("{username}", $username, $command);
            $command = str_replace("%USERNAME%", $username, $command);
        } else {
            $response["message"] = "No command found for this key";
            echo json_encode($response);
            exit();
        }
    } catch (Exception $e) {
        $response["message"] =
            "Error accessing key database: " . $e->getMessage();
        echo json_encode($response);
        exit();
    }
} else {
    $command = trim($data["command"]);

    // Additional validation for non-empty command
    if (empty($command)) {
        $response["message"] = "Empty command provided";
        echo json_encode($response);
        exit();
    }
}

// Log the command for debugging
file_put_contents(
    "rcon-debug.log",
    date("Y-m-d H:i:s") . " - User: $username - Command: $command\n",
    FILE_APPEND
);

// Connect to RCON
$rcon = new Rcon($host, $port, $password, $timeout);

try {
    if ($rcon->connect()) {
        $allResponses = [];

        // Execute the command quantity times
        for ($i = 0; $i < $quantity; $i++) {
            // Send the command
            $rconResponse = $rcon->sendCommand($command);
            $allResponses[] = $rconResponse;

            // If multiple executions, add a small delay to prevent server flooding
            if ($quantity > 1 && $i < $quantity - 1) {
                usleep(500000); // 0.5 seconds delay between commands
            }
        }

        // Join responses with line breaks if multiple commands were executed
        $finalResponse = implode("\n", $allResponses);

        // Process successful response
        $response = [
            "success" => true,
            "message" => $isSystemCommand
                ? "System command executed successfully"
                : ($isKeyCommand
                    ? "Key command executed successfully (x$quantity)"
                    : "Command executed successfully (x$quantity)"),
            "command" => $command,
            "quantity" => $quantity,
            "response" => $finalResponse,
            "username" => $username,
            "system" => $isSystemCommand,
            "key" => $isKeyCommand,
        ];
    } else {
        $response["message"] = "Failed to connect to the server";
    }
} catch (Exception $e) {
    $response["message"] = "Error: " . $e->getMessage();
} finally {
    // Always close the connection if it was opened
    if ($rcon && $rcon->isConnected()) {
        $rcon->disconnect();
    }
}

// Return JSON response
header("Content-Type: application/json");
echo json_encode($response);
