<?php
// api/register.php - User Registration Endpoint

header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../includes/jwt.php';

// Handle POST request only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['username']) || !isset($data['email']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username, email and password required']);
    exit;
}

$username = trim($data['username']);
$email = trim($data['email']);
$password = $data['password'];

// Validate username length
if (strlen($username) < 3 || strlen($username) > 50) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username must be between 3 and 50 characters']);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Validate password length
if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

// Connect to database
$conn = getDBConnection();

// Check if username already exists
$checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$checkStmt->bind_param("ss", $username, $email);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
    $checkStmt->close();
    closeDBConnection($conn);
    exit;
}
$checkStmt->close();

// Hash password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Insert new user
$stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
$stmt->bind_param("sss", $username, $email, $hashedPassword);

if ($stmt->execute()) {
    $userId = $conn->insert_id;
    
    // Create JWT token and log user in automatically
    $jwt = createJWT($userId, $username, 'user');
    setJWTCookie($jwt);
    
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful',
        'user' => [
            'id' => $userId,
            'username' => $username,
            'role' => 'user'
        ],
        'token' => $jwt
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Registration failed']);
}

$stmt->close();
closeDBConnection($conn);
?>