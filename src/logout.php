<?php
session_start();
if (isset($_SESSION['user_id'])) {
    // Unset all of the session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Redirect to the login page
    header("Location: login.php");
    exit;
} else {
    // If no session exists, redirect to the login page
    header("Location: login.php");
    exit;
}
?>