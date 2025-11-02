<?php
/**
 * JWT Helper Functions
 * Handles JWT token creation, verification, and cookie management
 * 
 * SECURITY NOTE: This implementation is intentionally vulnerable for demonstration:
 * - Cookies are NOT HttpOnly (accessible via JavaScript)
 * - This allows XSS attacks to steal JWT tokens
 */

// JWT Configuration
define('JWT_SECRET', 'your-super-secret-key-change-this-in-production-12345');
define('JWT_ALGORITHM', 'HS256');
define('JWT_EXPIRATION', 86400); // 24 hours in seconds

/**
 * Base64 URL-safe encoding
 * @param string $data Data to encode
 * @return string Encoded string
 */
function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * Base64 URL-safe decoding
 * @param string $data Data to decode
 * @return string Decoded string
 */
function base64UrlDecode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

/**
 * Create JWT Token
 * @param int $userId User ID
 * @param string $username Username
 * @param string $role User role (admin/user)
 * @return string JWT token
 */
function createJWT($userId, $username, $role) {
    $issuedAt = time();
    $expirationTime = $issuedAt + JWT_EXPIRATION;
    
    // Header
    $header = json_encode([
        'typ' => 'JWT',
        'alg' => JWT_ALGORITHM
    ]);
    
    // Payload
    $payload = json_encode([
        'user_id' => $userId,
        'username' => $username,
        'role' => $role,
        'iat' => $issuedAt,
        'exp' => $expirationTime
    ]);
    
    // Encode Header and Payload
    $base64UrlHeader = base64UrlEncode($header);
    $base64UrlPayload = base64UrlEncode($payload);
    
    // Create Signature
    $signature = hash_hmac(
        'sha256',
        $base64UrlHeader . "." . $base64UrlPayload,
        JWT_SECRET,
        true
    );
    $base64UrlSignature = base64UrlEncode($signature);
    
    // Create JWT
    $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    
    return $jwt;
}

/**
 * Verify and Decode JWT Token
 * @param string $jwt JWT token to verify
 * @return array|false Decoded payload or false if invalid
 */
function verifyJWT($jwt) {
    if (empty($jwt)) {
        return false;
    }
    
    // Split the JWT into parts
    $tokenParts = explode('.', $jwt);
    
    if (count($tokenParts) !== 3) {
        return false;
    }
    
    list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $tokenParts;
    
    // Decode header and payload
    $header = base64UrlDecode($base64UrlHeader);
    $payload = base64UrlDecode($base64UrlPayload);
    
    // Verify signature
    $signature = hash_hmac(
        'sha256',
        $base64UrlHeader . "." . $base64UrlPayload,
        JWT_SECRET,
        true
    );
    $base64UrlSignatureToVerify = base64UrlEncode($signature);
    
    if ($base64UrlSignature !== $base64UrlSignatureToVerify) {
        return false; // Invalid signature
    }
    
    // Decode payload
    $payloadData = json_decode($payload, true);
    
    if (!$payloadData) {
        return false;
    }
    
    // Check expiration
    if (isset($payloadData['exp']) && $payloadData['exp'] < time()) {
        return false; // Token expired
    }
    
    return $payloadData;
}

/**
 * Get JWT token from cookie
 * @return string|null JWT token or null if not found
 */
function getJWTFromCookie() {
    return isset($_COOKIE['jwt_token']) ? $_COOKIE['jwt_token'] : null;
}

/**
 * Set JWT Cookie
 * VULNERABLE: Cookie is NOT HttpOnly (accessible via JavaScript)
 * This allows XSS attacks to steal the token
 * 
 * @param string $jwt JWT token to store
 */
function setJWTCookie($jwt) {
    $expirationTime = time() + JWT_EXPIRATION;
    
    // VULNERABLE: httponly is set to FALSE
    // This makes the cookie accessible via JavaScript (document.cookie)
    // Allowing XSS attacks to steal authentication tokens
    setcookie('jwt_token', $jwt, [
        'expires' => $expirationTime,
        'path' => '/',
        'secure' => false,      // Set to true in production with HTTPS
        'httponly' => false,    // VULNERABLE: Should be true in production!
        'samesite' => 'Lax'
    ]);
}

/**
 * Delete JWT Cookie (logout)
 */
function deleteJWTCookie() {
    setcookie('jwt_token', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'httponly' => false,
        'samesite' => 'Lax'
    ]);
}

/**
 * Get current authenticated user from JWT
 * @return array|null User data or null if not authenticated
 */
function getCurrentUser() {
    $jwt = getJWTFromCookie();
    
    if (!$jwt) {
        return null;
    }
    
    $userData = verifyJWT($jwt);
    
    return $userData ? $userData : null;
}

/**
 * Check if current user is admin
 * @return bool True if user is admin, false otherwise
 */
function isAdmin() {
    $user = getCurrentUser();
    return $user && isset($user['role']) && $user['role'] === 'admin';
}

/**
 * Require authentication (API endpoint helper)
 * Returns user data if authenticated, exits with 401 if not
 * @return array User data
 */
function requireAuth() {
    $user = getCurrentUser();
    
    if (!$user) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Authentication required. Please login.'
        ]);
        exit;
    }
    
    return $user;
}

/**
 * Require admin role (API endpoint helper)
 * Returns user data if admin, exits with 403 if not
 * @return array Admin user data
 */
function requireAdmin() {
    $user = requireAuth();
    
    if (!isset($user['role']) || $user['role'] !== 'admin') {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Admin access required. Insufficient permissions.'
        ]);
        exit;
    }
    
    return $user;
}

/**
 * Refresh JWT token (extend expiration)
 * @param string $oldJwt Existing JWT token
 * @return string|false New JWT token or false if invalid
 */
function refreshJWT($oldJwt) {
    $userData = verifyJWT($oldJwt);
    
    if (!$userData) {
        return false;
    }
    
    // Create new token with extended expiration
    $newJwt = createJWT(
        $userData['user_id'],
        $userData['username'],
        $userData['role']
    );
    
    setJWTCookie($newJwt);
    
    return $newJwt;
}

/**
 * Decode JWT without verification (for debugging only)
 * DO NOT use for authentication!
 * @param string $jwt JWT token
 * @return array|false Decoded payload or false
 */
function decodeJWT($jwt) {
    $tokenParts = explode('.', $jwt);
    
    if (count($tokenParts) !== 3) {
        return false;
    }
    
    $payload = base64UrlDecode($tokenParts[1]);
    return json_decode($payload, true);
}
?>
