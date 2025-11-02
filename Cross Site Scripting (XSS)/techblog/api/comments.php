<?php
// api/comments.php - Comment Operations (VULNERABLE TO STORED XSS)

header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../includes/jwt.php';

$method = $_SERVER['REQUEST_METHOD'];

// POST - Add comment (VULNERABLE - No sanitization)
if ($method === 'POST') {
    $user = requireAuth();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['blog_id']) || !isset($data['comment_text'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Blog ID and comment text required']);
        exit;
    }
    
    $blogId = intval($data['blog_id']);
    $commentText = $data['comment_text']; // VULNERABLE: No sanitization or escaping!
    $userId = $user['user_id'];
    
    // Validate blog exists
    $conn = getDBConnection();
    $checkStmt = $conn->prepare("SELECT id FROM blogs WHERE id = ?");
    $checkStmt->bind_param("i", $blogId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Blog not found']);
        $checkStmt->close();
        closeDBConnection($conn);
        exit;
    }
    $checkStmt->close();
    
    // VULNERABLE: Storing raw user input without any sanitization
    // This allows stored XSS attacks
    $stmt = $conn->prepare("INSERT INTO comments (blog_id, user_id, comment_text) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $blogId, $userId, $commentText);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Comment added successfully',
            'comment_id' => $conn->insert_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to add comment']);
    }
    
    $stmt->close();
    closeDBConnection($conn);
}

// DELETE - Delete comment (User can delete own comments, admin can delete any)
elseif ($method === 'DELETE') {
    $user = requireAuth();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Comment ID required']);
        exit;
    }
    
    $commentId = intval($data['id']);
    $conn = getDBConnection();
    
    // Check if user owns the comment or is admin
    if ($user['role'] === 'admin') {
        $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->bind_param("i", $commentId);
    } else {
        $stmt = $conn->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $commentId, $user['user_id']);
    }
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Comment deleted successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Comment not found or unauthorized']);
    }
    
    $stmt->close();
    closeDBConnection($conn);
}

else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>