<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';


// Log security event
if (isLoggedIn()) {
    logSecurityEvent("User logout", $_SESSION['user_id']);
}

// Destroy session
session_destroy();

// Redirect to home page
header('Location: index.php');
exit;
?>
