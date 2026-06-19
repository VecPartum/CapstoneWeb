<?php
require_once 'config.php';
require_once 'utils.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['message' => 'Method not allowed']));
}

$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['email'], $input['password'], $input['username'])) {
    http_response_code(400);
    die(json_encode(['message' => 'Missing required fields']));
}

$email = sanitize($input['email']);
$password = $input['password'];
$username = sanitize($input['username']);
$confirm_password = $input['confirm_password'] ?? '';

// Validate email format
if (!isValidEmail($email)) {
    http_response_code(400);
    die(json_encode(['message' => 'Invalid email format']));
}

// Check password length
if (strlen($password) < 6) {
    http_response_code(400);
    die(json_encode(['message' => 'Password must be at least 6 characters']));
}

// Check if passwords match
if ($password !== $confirm_password) {
    http_response_code(400);
    die(json_encode(['message' => 'Passwords do not match']));
}

// Check if user already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    http_response_code(400);
    die(json_encode(['message' => 'Email already registered']));
}

// Hash password
$password_hash = password_hash($password, PASSWORD_BCRYPT);

// Insert user into database
$stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("sss", $username, $email, $password_hash);

if ($stmt->execute()) {
    $userId = $conn->insert_id;
    $token = generateToken($userId, $email);
    
    http_response_code(201);
    echo json_encode([
        'message' => 'Account created successfully',
        'token' => $token,
        'user' => [
            'id' => $userId,
            'username' => $username,
            'email' => $email
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Error creating account']);
}

$stmt->close();
$conn->close();
?>
