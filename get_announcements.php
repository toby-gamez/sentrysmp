<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

// connect to database
try {
    $db = new SQLite3("blog.sqlite");
} catch (Exception $e) {
    echo json_encode(["message" => "Connection error"]);
    exit();
}

// Get all blogs from the database
$query = "SELECT * FROM blogs ORDER BY created_at DESC";
$results = $db->query($query);

$blogs = [];
while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    $blogs[] = $row;
}

// Return blogs in JSON format
echo json_encode($blogs);
?>
