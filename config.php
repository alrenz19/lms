<?php
// Remove session_start() from here since it's called in individual files

// Load environment variables
$env_file = '.env';
if (file_exists($env_file)) {
    $env = parse_ini_file($env_file);
} else {
    die("Error: .env file not found.");
}

// Database configuration
$db_host = $env['DB_HOST'] ?? 'localhost';
$db_user = $env['DB_USER'] ?? 'root';
$db_pass = $env['DB_PASS'] ?? '';
$db_name = $env['DB_NAME'] ?? 'db_lms';

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
