<?php
/**
 * Auto Cleanup Script for VIP Users
 * 
 * This script automatically removes expired VIP users from the database.
 * It should be set up as a cron job to run daily.
 * 
 * Example cron setup:
 * 0 0 * * * php /path/to/http/auto_cleanup.php
 */

// Disable direct web access to this script
if (isset($_SERVER['REMOTE_ADDR'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Access Denied";
    exit;
}

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
    
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $days_left = getDaysLeft($row["created_at"]);
        if ($days_left <= 0) {
            // Delete expired user
            try {
                $stmt = $db->prepare("DELETE FROM vip_users WHERE username = :username");
                $stmt->bindValue(":username", $row["username"], SQLITE3_TEXT);
                $stmt->execute();
                $log[] = "Deleted expired user: " . $row["username"];
                $expired_count++;
            } catch (Exception $e) {
                $log[] = "Error deleting user " . $row["username"] . ": " . $e->getMessage();
            }
        }
    }
    
    $log[] = "Cleanup complete. Removed $expired_count expired users.";
    
    // Optional: Save log to file
    file_put_contents(__DIR__ . "/cleanup_log.txt", implode("\n", $log) . "\n\n", FILE_APPEND);
    
} catch (Exception $e) {
    $error_message = "Error during cleanup: " . $e->getMessage();
    file_put_contents(__DIR__ . "/cleanup_error_log.txt", date('Y-m-d H:i:s') . " - " . $error_message . "\n", FILE_APPEND);
    exit(1);
}

exit(0);