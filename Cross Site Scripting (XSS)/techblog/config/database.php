<?php
/**
 * Database Configuration File (Docker Version)
 * Contains database connection settings and helper functions
 */

// Database configuration constants - Use Docker environment variables
define('DB_HOST', getenv('DB_HOST') ?: 'mysql');
define('DB_USER', getenv('DB_USER') ?: 'techblog_user');
define('DB_PASS', getenv('DB_PASS') ?: 'techblog_pass');
define('DB_NAME', getenv('DB_NAME') ?: 'techblog_db');

/**
 * Get database connection
 * @return mysqli Database connection object
 */
function getDBConnection() {
    // Retry logic for initial connection (Docker MySQL may take time to start)
    $maxRetries = 10;
    $retryDelay = 2; // seconds
    
    for ($i = 0; $i < $maxRetries; $i++) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if (!$conn->connect_error) {
            // Set charset to utf8mb4 for proper emoji and special character support
            $conn->set_charset('utf8mb4');
            return $conn;
        }
        
        if ($i < $maxRetries - 1) {
            sleep($retryDelay);
        }
    }
    
    // If all retries failed
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]));
}

/**
 * Close database connection
 * @param mysqli $conn Database connection object
 */
function closeDBConnection($conn) {
    if ($conn) {
        $conn->close();
    }
}
?>
