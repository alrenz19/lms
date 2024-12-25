<?php
require_once 'config.php';

try {
    // Disable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    
    // Delete existing admin
    $conn->query("DELETE FROM users WHERE username = 'admin'");
    
    // Create new admin with hashed password
    $username = 'admin';
    $email = 'admin@lms.com';
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $role = 'admin';
    
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $password, $role);
    
    if ($stmt->execute()) {
        // Re-enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        
        echo "<h3>Admin account has been reset successfully!</h3>";
        echo "<p>You can now login with:</p>";
        echo "<p>Username: admin<br>";
        echo "Password: admin123</p>";
        
    } else {
        throw new Exception("Failed to create admin account");
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    // Make sure foreign key checks are re-enabled
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
}
