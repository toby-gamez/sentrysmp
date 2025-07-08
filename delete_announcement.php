<?php
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data["id"];

    // Připojení k SQLite DB
    $db = new SQLite3("blog.sqlite");
    $stmt = $db->prepare("DELETE FROM blogs WHERE id = :id");
    $stmt->bindValue(":id", $id, SQLITE3_INTEGER);

    $result = $stmt->execute();
    if ($db->changes() > 0) {
        echo json_encode(["message" => "Announcement deleted"]);
    } else {
        echo json_encode(["message" => "Nothing was deleted (wrong ID?)"]);
    }
}
?>
