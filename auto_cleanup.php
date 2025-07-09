<?php
/**
 * Auto Cleanup Script for VIP Users
 *
 * This script automatically removes expired VIP users from the database.
 * It should be set up as a cron job to run daily.
 *
 * Example cron setup:
 * 0 0 * * * php /path/to/http/sentrysmp/auto_cleanup.php
 */

// Disable direct web access to this script
if (isset($_SERVER['REMOTE_ADDR'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Access Denied";
    exit;
}

// Include the RCON functionality
require_once __DIR__ . "/vip-rcon.php";

// Initialize log
$log = [];
$log[] = "VIP auto cleanup started at " . date('Y-m-d H:i:s');

// Helper function to calculate days left
function getDaysLeft($created_at)
{
    $created_ts = strtotime($created_at);
    $expire_ts = $created_ts + 30 * 24 * 60 * 60;
    $now = time();
    $diff = $expire_ts - $now;
    $days_left = ceil($diff / (60 * 60 * 24));
    if ($days_left < 0) {
        $days_left = 0;
    }
    return $days_left;
}

// Connect to the database
try {
    $db = new SQLite3(__DIR__ . "/vip.sqlite");
    $log[] = "Successfully connected to the database";

    // Get expired users
    $result = $db->query("SELECT id, username, created_at FROM vip_users");
    $expired_count = 0;
    $rcon_success = 0;
    $rcon_failed = 0;

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $days_left = getDaysLeft($row["created_at"]);
        if ($days_left <= 0) {
            $username = $row["username"];

            // First remove VIP permissions via RCON
            $rcon_result = false;
            try {
                $rcon_result = removeVipPermissions($username);
                if ($rcon_result) {
                    $rcon_success++;
                } else {
                    $rcon_failed++;
                }
            } catch (Exception $e) {
                $rcon_failed++;
                $log[] = "RCON error for user $username: " . $e->getMessage();
            }

            // Then delete expired user from database
            try {
                $stmt = $db->prepare("DELETE FROM vip_users WHERE username = :username");
                $stmt->bindValue(":username", $username, SQLITE3_TEXT);
                $stmt->execute();
                $log[] = "Deleted expired user: $username (RCON: " . ($rcon_result ? "Success" : "Failed") . ")";
                $expired_count++;
            } catch (Exception $e) {
                $log[] = "Error deleting user $username from database: " . $e->getMessage();
            }
        }
    }

    $log[] = "Cleanup complete. Removed $expired_count expired users.";
    $log[] = "RCON operations: $rcon_success successful, $rcon_failed failed";

    // Save log to file
    file_put_contents(__DIR__ . "/vip_cleanup_log.txt", implode("\n", $log) . "\n\n", FILE_APPEND);

    // Output summary for cron
    echo "VIP Cleanup Summary:\n";
    echo "- Expired users removed: $expired_count\n";
    echo "- RCON operations successful: $rcon_success\n";
    echo "- RCON operations failed: $rcon_failed\n";
    echo "- Log saved to vip_cleanup_log.txt\n";

} catch (Exception $e) {
    $error_message = "Error during cleanup: " . $e->getMessage();
    file_put_contents(__DIR__ . "/vip_cleanup_log.txt", date('Y-m-d H:i:s') . " - " . $error_message . "\n", FILE_APPEND);
    echo "ERROR: $error_message\n";
    exit(1);
}

exit(0);
