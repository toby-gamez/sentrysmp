<?php
/**
 * Webhook for Third-Party Cron Services
 * 
 * This file provides an HTTP endpoint that can be called by third-party 
 * cron services to trigger the VIP user cleanup process.
 * 
 * For security, it requires a secret token to be provided in the request.
 * Example URL: https://example.com/cron_webhook.php?token=YOUR_SECRET_TOKEN
 */

// Change this to a random, secure string 
$SECRET_TOKEN = "change_this_to_a_secure_random_string";

// Check if the token is provided and valid
if (!isset($_GET['token']) || $_GET['token'] !== $SECRET_TOKEN) {
    http_response_code(403);
    echo "Access denied";
    exit;
}

// Log access
$access_log = date('Y-m-d H:i:s') . " - Webhook accessed from IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
file_put_contents(__DIR__ . "/webhook_access.log", $access_log, FILE_APPEND);

// Initialize response
$response = [];
$response['timestamp'] = date('Y-m-d H:i:s');
$response['status'] = 'running';

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

// Connect to the database and process expired users
try {
    $db = new SQLite3(__DIR__ . "/vip.sqlite");
    $response['database'] = 'connected';
    
    // Get expired users
    $result = $db->query("SELECT id, username, created_at FROM vip_users");
    $expired_count = 0;
    $expired_users = [];
    
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $days_left = getDaysLeft($row["created_at"]);
        if ($days_left <= 0) {
            // Delete expired user
            try {
                $stmt = $db->prepare("DELETE FROM vip_users WHERE username = :username");
                $stmt->bindValue(":username", $row["username"], SQLITE3_TEXT);
                $stmt->execute();
                $expired_users[] = $row["username"];
                $expired_count++;
            } catch (Exception $e) {
                $response['errors'][] = "Error deleting user " . $row["username"] . ": " . $e->getMessage();
            }
        }
    }
    
    $response['expired_count'] = $expired_count;
    $response['expired_users'] = $expired_users;
    $response['status'] = 'completed';
    
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['error'] = $e->getMessage();
    
    // Log error
    $error_log = date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n";
    file_put_contents(__DIR__ . "/webhook_error.log", $error_log, FILE_APPEND);
}

// Return response as JSON
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
exit;