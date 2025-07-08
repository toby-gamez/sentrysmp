<?php
header("Content-Type: application/json");

// Check query parameter first
$usernamemc = isset($_GET["usernamemc"]) ? $_GET["usernamemc"] : "";

// If not in query, try cookies
if (empty($usernamemc)) {
    $usernamemc = $_COOKIE["minecraftmc"] ?? "";
}

// Check username format
if (empty($usernamemc) || !preg_match('/^[a-zA-Z0-9_]{3,16}$/', $usernamemc)) {
    echo json_encode([
        "success" => false,
        "skin" => "https://minotar.net/helm/MHF_Steve/100"
    ]);
    exit();
}

// Simple approach: Use direct skin services with usernames
// This bypasses verification issues and works for all usernames

// Option 1: Use minotar.net which is simple and reliable
$skinUrl = "https://minotar.net/helm/" . urlencode($usernamemc) . "/100";

// Return the skin URL
echo json_encode([
    "success" => true,
    "skin" => $skinUrl
]);
exit();