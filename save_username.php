<?php
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the JSON data from the request
$data = json_decode(file_get_contents('php://input'), true);

// Prepare the response
$response = [
    'success' => false,
    'message' => ''
];

// Check if username is provided
if (isset($data['username']) && !empty($data['username'])) {
    $username = trim($data['username']);
    
    // Store in session
    $_SESSION['usernamemc'] = $username;
    
    $response['success'] = true;
    $response['message'] = 'Username saved to session';
    $response['username'] = $username;
} else {
    $response['message'] = 'No username provided';
}

// Return response
echo json_encode($response);
?>