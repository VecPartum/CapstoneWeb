<?php
// Utility Functions

// Simple JWT token generation (for production, use a library like firebase/php-jwt)
function generateToken($userId, $email) {
    $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
    $payload = json_encode([
        'userId' => $userId,
        'email' => $email,
        'iat' => time(),
        'exp' => time() + (7 * 24 * 60 * 60) // 7 days
    ]);
    
    $signature = hash_hmac(
        'sha256',
        base64_encode($header) . '.' . base64_encode($payload),
        JWT_SECRET,
        true
    );
    
    return base64_encode($header) . '.' . base64_encode($payload) . '.' . base64_encode($signature);
}

// Verify JWT token
function verifyToken($token) {
    if (!$token) return null;
    
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    
    list($header, $payload, $signature) = $parts;
    
    $valid_signature = base64_encode(hash_hmac(
        'sha256',
        $header . '.' . $payload,
        JWT_SECRET,
        true
    ));
    
    if ($signature !== $valid_signature) return null;
    
    $decoded = json_decode(base64_decode($payload), true);
    
    if ($decoded['exp'] < time()) return null; // Token expired
    
    return $decoded;
}

// Get authorization token from header
function getAuthToken() {
    // Try getallheaders() first (if available)
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $matches = [];
            if (preg_match('/Bearer (.+)/', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }
    }
    
    // Fallback 1: Check HTTP_AUTHORIZATION
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $matches = [];
        if (preg_match('/Bearer (.+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
            return $matches[1];
        }
    }
    
    // Fallback 2: Check REDIRECT_HTTP_AUTHORIZATION (for some servers)
    if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $matches = [];
        if (preg_match('/Bearer (.+)/', $_SERVER['REDIRECT_HTTP_AUTHORIZATION'], $matches)) {
            return $matches[1];
        }
    }
    
    // Fallback 3: Check if it's in the request body (as backup for shared hosting)
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['token'])) {
        return $input['token'];
    }
    
    return null;
}

// Sanitize input
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}
?>
