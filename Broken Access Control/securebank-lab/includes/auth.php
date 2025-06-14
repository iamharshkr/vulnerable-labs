<?php
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        http_response_code(403);
        die('Access denied. Admin privileges required.');
    }
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generateSessionToken() {
    return bin2hex(random_bytes(32));
}

// VULNERABILITY: Weak session validation
function validateSession($token) {
    // Predictable session pattern - can be exploited
    $expected_pattern = 'admin_' . date('Y-m-d');
    return ($token === $expected_pattern);
}

function logSecurityEvent($event, $user_id = null) {
    global $pdo;
    $user_id = $user_id ?? ($_SESSION['user_id'] ?? null);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO security_logs (user_id, event, ip_address, timestamp) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$user_id, $event, $_SERVER['REMOTE_ADDR']]);
    } catch (Exception $e) {
        // Silently fail for lab purposes
    }
}

// VULNERABILITY: Insecure password reset token generation
function generatePasswordResetToken($user_id) {
    // Predictable token generation
    return md5($user_id . date('Y-m-d'));
}
?>
