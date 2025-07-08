<?php
// URL API pro získání stavu serveru
$url = "https://api.mcstatus.io/v2/status/java/mc.sentrysmp.eu";

// Inicializace cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
curl_close($ch);

// Dekódování JSON odpovědi
$data = json_decode($response, true);

// Získání počtu hráčů
if (isset($data["players"]["online"])) {
    echo json_encode([
        "status" => "success",
        "players" => $data["players"]["online"],
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Nepodařilo se získat počet hráčů.",
    ]);
}
?>
