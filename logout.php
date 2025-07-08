<?php
session_start();
$username = $_SESSION["username"] ?? "Administrator";
session_destroy();
header("Location: login.php?logout=true");
exit();
?>
