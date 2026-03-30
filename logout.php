<?php
require_once 'config/session.php';
require_once 'config/database.php';

// Destroy session
session_unset();
session_destroy();

// Clear session cookies
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirect to login page
header("Location: index.php");
exit();
?>
