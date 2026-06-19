<?php
require_once 'config.php';
require_once 'utils.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['message' => 'Method not allowed']));
}

$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['email'], $input['password'])) {
    http_response_code(400);
    die(json_encode(['message' => 'Email and password required']));
}

$email = sanitize($input['email']);
$password = $input['password'];

// Find user by email
$stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(401);
    die(json_encode(['message' => 'Invalid email or password']));
}

$user = $result->fetch_assoc();

// Verify password
if (!password_verify($password, $user['password_hash'])) {
    http_response_code(401);
    die(json_encode(['message' => 'Invalid email or password']));
}

// Generate token
$token = generateToken($user['id'], $email);

// Update last login
$stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();

http_response_code(200);
echo json_encode([
    'message' => 'Logged in successfully',
    'token' => $token,
    'user' => [
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $email
    ]
]);

$stmt->close();
$conn->close();
?>
