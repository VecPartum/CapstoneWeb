<?php
require_once 'config.php';
require_once 'utils.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['message' => 'Method not allowed']));
}

// Verify authentication
$token = getAuthToken();

// If no token in header, check the request body
if (!$token) {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['token'])) {
        $token = $input['token'];
    }
}

$userPayload = verifyToken($token);

if (!$userPayload) {
    http_response_code(401);
    die(json_encode(['message' => 'Unauthorized']));
}

$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['title'], $input['preview'], $input['category'])) {
    http_response_code(400);
    die(json_encode(['message' => 'Missing required fields']));
}

$title = sanitize($input['title']);
$preview = sanitize($input['preview']);
$category = sanitize($input['category']);
$userId = $userPayload['userId'];

// Validate inputs
if (strlen($title) < 3) {
    http_response_code(400);
    die(json_encode(['message' => 'Title must be at least 3 characters']));
}

if (strlen($preview) < 10) {
    http_response_code(400);
    die(json_encode(['message' => 'Preview must be at least 10 characters']));
}

$validCategories = ['Dev Updates', 'Lore & Story', 'Game Discussion', 'Fan Art', 'General'];
if (!in_array($category, $validCategories)) {
    http_response_code(400);
    die(json_encode(['message' => 'Invalid category']));
}

// Insert thread
$stmt = $conn->prepare("INSERT INTO threads (user_id, title, preview, category, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("isss", $userId, $title, $preview, $category);

if ($stmt->execute()) {
    $threadId = $conn->insert_id;
    
    http_response_code(201);
    echo json_encode([
        'message' => 'Thread created successfully',
        'threadId' => $threadId,
        'thread' => [
            'id' => $threadId,
            'title' => $title,
            'preview' => $preview,
            'category' => $category
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Error creating thread']);
}

$stmt->close();
$conn->close();
?>
