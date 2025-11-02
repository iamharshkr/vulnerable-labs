<?php
/**
 * Login API Endpoint
 * Handles user authentication and JWT token generation
 */

header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../includes/jwt.php';

// Handle POST request only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST request.'
    ]);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['username']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Username and password are required'
    ]);
    exit;
}

$username = trim($data['username']);
$password = $data['password'];

// Validate input
if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Username and password cannot be empty'
    ]);
    exit;
}

// Connect to database
$conn = getDBConnection();

// Prepare statement to prevent SQL injection
$stmt = $conn->prepare("SELECT id, username, password, role, email FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists
if ($result->num_rows === 0) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid username or password'
    ]);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}

$user = $result->fetch_assoc();

// Verify password
if (!password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid username or password'
    ]);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}

// Create JWT token
$jwt = createJWT($user['id'], $user['username'], $user['role']);

// Set JWT cookie (VULNERABLE - not HttpOnly)
setJWTCookie($jwt);

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'user' => [
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'role' => $user['role']
    ],
    'token' => $jwt
]);

$stmt->close();
closeDBConnection($conn);
?>
