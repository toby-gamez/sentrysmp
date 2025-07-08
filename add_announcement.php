<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set("display_errors", 1);

$input = file_get_contents("php://input"); // load only once

if (!$input) {
    echo json_encode([
        "status" => "error",
        "message" => "Empty request body",
    ]);
    exit();
}

$data = json_decode($input, true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON input"]);
    exit();
}

try {
    $db = new SQLite3("blog.sqlite");
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection error",
    ]);
    exit();
}

$title = trim($data["title"] ?? "");
$author = trim($data["author"] ?? "");
$content = trim($data["content"] ?? "");

if ($title === "" || $author === "" || $content === "") {
    echo json_encode([
        "status" => "error",
        "message" => "Please fill in all fields",
    ]);
    exit();
}

$stmt = $db->prepare(
    "INSERT INTO blogs (title, author, content, created_at) VALUES (:title, :author, :content, datetime('now'))"
);
$stmt->bindValue(":title", $title, SQLITE3_TEXT);
$stmt->bindValue(":author", $author, SQLITE3_TEXT);
$stmt->bindValue(":content", $content, SQLITE3_TEXT);

$result = $stmt->execute();

if ($result) {
    echo json_encode([
        "status" => "success",
        "message" => "The post has been added",
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Error while saving: " . $db->lastErrorMsg(),
    ]);
}
exit();
