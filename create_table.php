<?php
$db = new SQLite3("keys.sqlite");
$db->exec("CREATE TABLE IF NOT EXISTS Keys (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    value TEXT NOT NULL,
    image TEXT
)");
echo "Tabulka Keys byla vytvořena v keys.sqlite.<br>";
echo "Umístění databáze: " . realpath("keys.sqlite");
?>
