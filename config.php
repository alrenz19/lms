<?php
// Remove session_start() from here since it's called in individual files

// Load environment variables
$env_file = '.env';
if (file_exists($env_file)) {
    $env = parse_ini_file($env_file);
}

// Database configuration - check Docker env vars first, fallback to .env file
$db_host = getenv('DB_HOST') ?: ($env['DB_HOST'] ?? 'localhost');
$db_user = getenv('DB_USERNAME') ?: ($env['DB_USER'] ?? 'root');
$db_pass = getenv('DB_PASSWORD') ?: ($env['DB_PASS'] ?? '');
$db_name = getenv('DB_DATABASE') ?: ($env['DB_NAME'] ?? 'db_lms');

// Create database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add security functions
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
