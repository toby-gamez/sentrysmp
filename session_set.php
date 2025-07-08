<?php
session_start();

// Set username in session from POST data
if (isset($_POST['usernamemc']) && !empty($_POST['usernamemc'])) {
    $_SESSION["usernamemc"] = $_POST['usernamemc'];
    
    // Return nothing to indicate success
    header("Content-Type: text/plain");
    echo "";
    exit();
} else {
    // Return error message if no username provided
    header("Content-Type: text/plain");
    echo "No username provided";
    exit();
}