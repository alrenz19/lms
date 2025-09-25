<?php
// Remove session_start() from here since it's called in individual files

// Load environment variables

// Database configuration - check Docker env vars first, fallback to .env file
$db_host = 'localhost';
$db_user =  'root';
// $db_pass = '6981_$oaQooIj7';
$db_pass = '';
//$db_pass = getenv('DB_PASSWORD') ?: ($env['DB_PASS'] ?? '');
$db_name = 'db_lms';

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
