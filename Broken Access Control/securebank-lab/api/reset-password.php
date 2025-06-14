<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    $input = $_POST; // Fallback to form data
}

$user_id = $input['user_id'] ?? null;
$new_password = $input['password'] ?? null;
// $reset_token = $input['reset_token'] ?? null;

if (!$user_id || !$new_password) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

try {
    // VULNERABILITY 1: Missing authorization check
    // Anyone can reset anyone's password if they know the user_id
    
    // VULNERABILITY 2: Weak token validation
    // $expected_token = generatePasswordResetToken($user_id);
    // if ($reset_token && $reset_token !== $expected_token) {
    //     // Token provided but invalid
    //     http_response_code(403);
    //     echo json_encode(['error' => 'Invalid reset token']);
    //     exit;
    // }
    
    // VULNERABILITY 3: No rate limiting or additional verification
    
    // Hash the new password (simplified for lab)
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update password in database
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $result = $stmt->execute([$hashed_password, $user_id]);
    
    if ($result) {
        // Log security event
        logSecurityEvent("Password reset for user ID: $user_id", $user_id);
        
        // Get user info for response
        $stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => 'Password reset successfully',
            'flag' => 'FLAG{m1ss1ng_4uth_ch3ck_pwn3d}',
            'user' => $user['username'] ?? 'Unknown',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update password']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error', 'debug' => $e->getMessage()]);
}
?>
