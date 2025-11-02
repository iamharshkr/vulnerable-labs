<?php
// api/logout.php - Logout Endpoint

header('Content-Type: application/json');
require_once '../includes/jwt.php';

// Delete JWT cookie
deleteJWTCookie();

echo json_encode([
    'success' => true,
    'message' => 'Logged out successfully'
]);
?>