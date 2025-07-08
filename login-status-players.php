<?php
session_start();
header("Content-Type: application/json");

// Pokud není uživatel přihlášen
if (!isset($_SESSION["usernamemc"])) {
    echo json_encode(["logged_in" => false]);
    exit();
}

// Pokud je uživatel přihlášen
$usernamemc = $_SESSION["usernamemc"];
$edition = $_SESSION["edition"] ?? "java";

// Generujeme URL pro skin podle edice
if ($edition === "java") {
    // Pro Java účty použijeme jejich skutečný skin
    $skinUrl = "https://minotar.net/helm/" . urlencode($usernamemc) . "/100";
} else {
    // Pro Bedrock a cracked účty použijeme Steve skin
    $skinUrl = "https://minotar.net/helm/MHF_Steve/100";
}

echo json_encode([
    "logged_in" => true,
    "username" => $usernamemc,
    "skin" => $skinUrl,
    "edition" => $edition
]);
