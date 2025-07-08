<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);
// Cesta k databázovému souboru SQLite
$db = new PDO("sqlite:/var/www/html/blog.sqlite");
// Vytvoření tabulky, pokud neexistuje
$db->exec("CREATE TABLE IF NOT EXISTS blogs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    content TEXT NOT NULL,
    author TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

echo "Database and table created!";
?>
