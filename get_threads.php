<?php
require_once 'config.php';
require_once 'utils.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    die(json_encode(['message' => 'Method not allowed']));
}

// Get filter parameters
$category = isset($_GET['category']) ? sanitize($_GET['category']) : null;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : null;

// Build query
$query = "
    SELECT 
        t.id, t.title, t.preview, t.category, t.replies, t.views, 
        t.pinned, t.hot, t.created_at, u.username, u.avatar
    FROM threads t
    JOIN users u ON t.user_id = u.id
    WHERE 1=1
";

$params = [];
$types = '';

if ($category && $category !== 'All') {
    $query .= " AND t.category = ?";
    $params[] = $category;
    $types .= 's';
}

if ($search) {
    $query .= " AND (t.title LIKE ? OR t.preview LIKE ?)";
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'ss';
}

$query .= " ORDER BY t.pinned DESC, t.created_at DESC LIMIT 100";

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$threads = [];
while ($row = $result->fetch_assoc()) {
    $threads[] = [
        'id' => (int)$row['id'],
        'title' => $row['title'],
        'author' => $row['username'],
        'avatar' => $row['avatar'] ?? '🌿',
        'replies' => (int)$row['replies'],
        'views' => (int)$row['views'],
        'category' => $row['category'],
        'pinned' => (bool)$row['pinned'],
        'hot' => (bool)$row['hot'],
        'preview' => $row['preview'],
        'time' => formatTime(strtotime($row['created_at']))
    ];
}

http_response_code(200);
echo json_encode($threads);

$stmt->close();
$conn->close();

// Helper function to format time
function formatTime($timestamp) {
    $now = time();
    $diff = $now - $timestamp;
    
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return floor($diff / 604800) . ' weeks ago';
}
?>
