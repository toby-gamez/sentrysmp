<?php
session_start();
require "vendor/autoload.php";

use Thedudeguy\Rcon;

// Check if user is logged in
if (!isset($_SESSION["usernamemc"])) {
    echo json_encode(["error" => "User not logged in"]);
    exit();
}

$usernamemc = $_SESSION["usernamemc"];

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

// Get command from request if available, otherwise use default
$command = isset($_POST["command"])
    ? $_POST["command"]
    : "lp user " . $usernamemc . " parent set vip";
// "lp user " . $usernamemc . " parent addtemp vip 30d";
$rcon = new Rcon($host, $port, $password, $timeout);

if ($rcon->connect()) {
    $response = $rcon->sendCommand($command);
    echo "Response: " . $response;
} else {
    echo "Failed to connect to the server.";
}
