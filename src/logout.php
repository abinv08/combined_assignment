<?php
session_start();
// Unset and destroy session regardless
$_SESSION = array();
session_destroy();

// Redirect to homepage where header shows only Login
header("Location: index.php");
exit;
?>