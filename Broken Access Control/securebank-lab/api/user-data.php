<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

$action = $_GET['action'] ?? 'profile';
$user_id = $_GET['user_id'] ?? $_SESSION['user_id'];

try {
    switch ($action) {
        case 'profile':
            // VULNERABILITY: IDOR - can access any user's profile
            $stmt = $pdo->prepare("SELECT id, username, email, role, created_at FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                http_response_code(404);
                echo json_encode(['error' => 'User not found']);
                exit;
            }
            
            // Add flag if accessing another user's data
            if ($user_id != $_SESSION['user_id']) {
                $user['security_flag'] = 'FLAG{4p1_1d0r_vuln3r4b1l1ty}';
                $user['warning'] = 'You are accessing another user\'s data!';
            }
            
            echo json_encode(['success' => true, 'data' => $user]);
            break;
            
        case 'accounts':
            // VULNERABILITY: Can view other users' account information
            $stmt = $pdo->prepare("
                SELECT a.*, u.username 
                FROM accounts a 
                JOIN users u ON a.user_id = u.id 
                WHERE a.user_id = ?
            ");
            $stmt->execute([$user_id]);
            $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $accounts]);
            break;
            
        case 'transactions':
            // VULNERABILITY: Transaction history exposure
            $limit = $_GET['limit'] ?? 10;
            $offset = $_GET['offset'] ?? 0;
            
            $stmt = $pdo->prepare("
                SELECT t.*, 
                       u1.username as from_user, 
                       u2.username as to_user
                FROM transactions t
                JOIN accounts a1 ON t.from_account = a1.id
                JOIN accounts a2 ON t.to_account = a2.id
                JOIN users u1 ON a1.user_id = u1.id
                JOIN users u2 ON a2.user_id = u2.id
                WHERE a1.user_id = ? OR a2.user_id = ?
                ORDER BY t.transaction_date DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$user_id, $user_id, $limit, $offset]);
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $transactions]);
            break;
            
        case 'admin_users':
            // VULNERABILITY: Admin function accessible without proper authorization
            if (!isAdmin() && !isset($_GET['force'])) {
                http_response_code(403);
                echo json_encode(['error' => 'Admin access required']);
                exit;
            }
            
            $stmt = $pdo->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true, 
                'data' => $users,
                'flag' => 'FLAG{4dm1n_4p1_byp4ss3d}',
                'message' => 'Admin data accessed successfully'
            ]);
            break;
            
        case 'session_info':
            // VULNERABILITY: Session information exposure
            $session_data = [
                'user_id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['role'],
                'session_id' => session_id(),
                'session_token' => 'admin_' . date('Y-m-d'), // Predictable token
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT']
            ];
            
            echo json_encode(['success' => true, 'data' => $session_data]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error', 'debug' => $e->getMessage()]);
}
?>
