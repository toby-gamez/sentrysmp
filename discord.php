<?php
header("Content-Type: application/json");

// Load environment variables
require_once __DIR__ . "/vendor/autoload.php";
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$botToken = $_ENV["DISCORD_BOT_TOKEN"] ?? null;
$guildId = $_ENV["DISCORD_GUILD_ID"] ?? "1159130895190605854";
$url = "https://discord.com/api/v10/guilds/$guildId?with_counts=true";

if (!$botToken) {
    echo json_encode(["error" => "Token není dostupný"]);
    exit();
}

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bot $botToken"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200 || !$response) {
    echo json_encode(["error" => "Can't load data"]);
} else {
    $data = json_decode($response, true);
    echo json_encode([
        "total" => $data["approximate_member_count"],
    ]);
}
?>
