<?php
ob_start();
// Start a session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Check if the user is not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: auth-login.php");
    exit;
}
