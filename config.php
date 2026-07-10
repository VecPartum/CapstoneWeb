<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'if0_42224674');
define('DB_PASS', 'Dv9fASRE7oa7n');
define('DB_NAME', 'if0_42224674_a4th_forum');

// Secret key for JWT tokens (change this in production!)
define('JWT_SECRET', 'e7716d968a67da40b75415ef67b971fe37f6e1a8d22afcdc7f62bfdfe5dc0c5f');

// Enable error reporting (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set response headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['message' => 'Database connection failed']));
}

$conn->set_charset('utf8mb4');
?>
