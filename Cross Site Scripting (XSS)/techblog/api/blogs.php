<?php
/**
 * Blogs API Endpoint
 * Handles all blog CRUD operations (Create, Read, Update, Delete)
 */

header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../includes/jwt.php';

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDBConnection();

// GET - Fetch all blogs or single blog
if ($method === 'GET') {
    
    // Check if requesting single blog
    if (isset($_GET['id'])) {
        $blogId = intval($_GET['id']);
        
        // Get blog details with author information
        $stmt = $conn->prepare("
            SELECT b.*, u.username as author_name, u.email as author_email
            FROM blogs b 
            JOIN users u ON b.author_id = u.id 
            WHERE b.id = ?
        ");
        $stmt->bind_param("i", $blogId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Blog not found'
            ]);
            $stmt->close();
            closeDBConnection($conn);
            exit;
        }
        
        $blog = $result->fetch_assoc();
        
        // Get comments for this blog (VULNERABLE - returns raw comment text)
        $commentStmt = $conn->prepare("
            SELECT c.*, u.username as author_name 
            FROM comments c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.blog_id = ? 
            ORDER BY c.created_at DESC
        ");
        $commentStmt->bind_param("i", $blogId);
        $commentStmt->execute();
        $commentResult = $commentStmt->get_result();
        
        $comments = [];
        while ($comment = $commentResult->fetch_assoc()) {
            $comments[] = [
                'id' => $comment['id'],
                'author_name' => $comment['author_name'],
                'comment_text' => $comment['comment_text'], // VULNERABLE: No sanitization
                'created_at' => $comment['created_at']
            ];
        }
        
        $blog['comments'] = $comments;
        
        echo json_encode([
            'success' => true,
            'blog' => $blog
        ]);
        
        $commentStmt->close();
        $stmt->close();
        
    } else {
        // Fetch all blogs with comment count
        $sql = "
            SELECT 
                b.id, 
                b.title, 
                b.content, 
                b.created_at, 
                b.updated_at,
                u.username as author_name,
                (SELECT COUNT(*) FROM comments WHERE blog_id = b.id) as comment_count
            FROM blogs b
            JOIN users u ON b.author_id = u.id
            ORDER BY b.created_at DESC
        ";
        
        $result = $conn->query($sql);
        $blogs = [];
        
        while ($row = $result->fetch_assoc()) {
            $blogs[] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'content' => $row['content'],
                'author_name' => $row['author_name'],
                'comment_count' => $row['comment_count'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'blogs' => $blogs,
            'total' => count($blogs)
        ]);
    }
}

// POST - Create new blog (Admin only)
elseif ($method === 'POST') {
    $user = requireAdmin();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!isset($data['title']) || !isset($data['content'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Title and content are required'
        ]);
        closeDBConnection($conn);
        exit;
    }
    
    $title = trim($data['title']);
    $content = trim($data['content']);
    $authorId = $user['user_id'];
    
    // Validate length
    if (empty($title) || empty($content)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Title and content cannot be empty'
        ]);
        closeDBConnection($conn);
        exit;
    }
    
    if (strlen($title) > 255) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Title cannot exceed 255 characters'
        ]);
        closeDBConnection($conn);
        exit;
    }
    
    // Insert blog
    $stmt = $conn->prepare("INSERT INTO blogs (title, content, author_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $title, $content, $authorId);
    
    if ($stmt->execute()) {
        $blogId = $conn->insert_id;
        
        echo json_encode([
            'success' => true,
            'message' => 'Blog created successfully',
            'blog_id' => $blogId
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create blog: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
}

// PUT - Update blog (Admin only)
elseif ($method === 'PUT') {
    $user = requireAdmin();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!isset($data['id']) || !isset($data['title']) || !isset($data['content'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Blog ID, title and content are required'
        ]);
        closeDBConnection($conn);
        exit;
    }
    
    $blogId = intval($data['id']);
    $title = trim($data['title']);
    $content = trim($data['content']);
    
    // Validate
    if (empty($title) || empty($content)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Title and content cannot be empty'
        ]);
        closeDBConnection($conn);
        exit;
    }
    
    // Check if blog exists
    $checkStmt = $conn->prepare("SELECT id FROM blogs WHERE id = ?");
    $checkStmt->bind_param("i", $blogId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Blog not found'
        ]);
        $checkStmt->close();
        closeDBConnection($conn);
        exit;
    }
    $checkStmt->close();
    
    // Update blog
    $stmt = $conn->prepare("UPDATE blogs SET title = ?, content = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->bind_param("ssi", $title, $content, $blogId);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Blog updated successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update blog: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
}

// DELETE - Delete blog (Admin only)
elseif ($method === 'DELETE') {
    $user = requireAdmin();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required field
    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Blog ID is required'
        ]);
        closeDBConnection($conn);
        exit;
    }
    
    $blogId = intval($data['id']);
    
    // Check if blog exists
    $checkStmt = $conn->prepare("SELECT id FROM blogs WHERE id = ?");
    $checkStmt->bind_param("i", $blogId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Blog not found'
        ]);
        $checkStmt->close();
        closeDBConnection($conn);
        exit;
    }
    $checkStmt->close();
    
    // Delete blog (comments will be deleted automatically due to CASCADE)
    $stmt = $conn->prepare("DELETE FROM blogs WHERE id = ?");
    $stmt->bind_param("i", $blogId);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Blog deleted successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete blog: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
}

// Method not allowed
else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}

closeDBConnection($conn);
?>
