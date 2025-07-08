<?php
$db = new PDO("sqlite:blog.sqlite");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$query = "CREATE TABLE IF NOT EXISTS spawners (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nazev TEXT NOT NULL,
    popis TEXT,
    obrazek TEXT,
    prikaz TEXT
)";
$db->exec($query);

echo "Tabulka 'spawners' vytvořena.";
?>
