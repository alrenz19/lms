<?php
require_once 'config.php';

// Hash admin password
$admin_password = 'admin123'; // Default admin password
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

// Update admin password
$sql = "UPDATE users SET password = ? WHERE username = 'admin'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hashed_password);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Admin password updated successfully!";
} else {
    echo "Error updating admin password.";
}
?>
