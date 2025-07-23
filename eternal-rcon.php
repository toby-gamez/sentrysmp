<?php
// RCON functionality for Eternal permission management
require_once "vendor/autoload.php"; // Assuming Composer autoload for RCON library
use Thedudeguy\Rcon;

/**
 * Remove Eternal permissions from a player via RCON
 *
 * @param string $remove_name The Minecraft username to remove permissions from
 * @return bool True if successful, false otherwise
 */
function removeEternalPermissions($remove_name)
{
    // Load environment variables if not already loaded
    if (!isset($_ENV["RCON_HOST"])) {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
    }

    // RCON connection settings from environment variables
    $host =
        $_ENV["RCON_HOST"] ??
        throw new Exception("RCON_HOST not set in environment");
    $port =
        (int) ($_ENV["RCON_PORT"] ??
            throw new Exception("RCON_PORT not set in environment"));
    $password =
        $_ENV["RCON_PASSWORD"] ??
        throw new Exception("RCON_PASSWORD not set in environment");
    $timeout = 3; // Connection timeout in seconds

    // Create RCON connection
    $rcon = new Rcon($host, $port, $password, $timeout);

    // Connect to the Minecraft server
    try {
        if ($rcon->connect()) {
            // Execute permission clear command
            $command = "lp user $remove_name clear";
            $response = $rcon->sendCommand($command);

            // Log the action
            $log_message =
                date("Y-m-d H:i:s") .
                " - Removed Eternal permissions for user: $remove_name - Response: " .
                ($response ?: "No response") .
                "\n";
            file_put_contents("eternal_rcon_log.txt", $log_message, FILE_APPEND);

            return true;
        } else {
            // Log connection failure
            $log_message =
                date("Y-m-d H:i:s") .
                " - Failed to connect to RCON for user: $remove_name\n";
            file_put_contents("eternal_rcon_log.txt", $log_message, FILE_APPEND);

            return false;
        }
    } catch (Exception $e) {
        // Log error
        $log_message =
            date("Y-m-d H:i:s") .
            " - RCON error for user: $remove_name - " .
            $e->getMessage() .
            "\n";
        file_put_contents("eternal_rcon_log.txt", $log_message, FILE_APPEND);

        return false;
    } finally {
        // Always disconnect when done
        if ($rcon && $rcon->isConnected()) {
            $rcon->disconnect();
        }
    }
}
?>
