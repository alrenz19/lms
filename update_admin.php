<?php
require_once 'config.php';

try {
    // Start transaction
    $conn->begin_transaction();

    // Get the current admin ID
    $result = $conn->query("SELECT id FROM users WHERE username = 'admin'");
    $old_admin = $result->fetch_assoc();
    $old_admin_id = $old_admin['id'] ?? null;

    // Update courses to temporarily remove foreign key constraint
    if ($old_admin_id) {
        $stmt = $conn->prepare("UPDATE courses SET created_by = NULL WHERE created_by = ?");
        $stmt->bind_param("i", $old_admin_id);
        $stmt->execute();
    }

    // Delete existing admin
    $conn->query("DELETE FROM users WHERE username = 'admin'");

    // Insert new admin with hashed password and full name
    $password = 'admin123';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (username, full_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", 'admin', 'System Administrator', 'admin@lms.com', $hashed_password, 'admin');
    $stmt->execute();
    
    // Get new admin ID
    $new_admin_id = $conn->insert_id;

    // Update courses to use new admin ID
    $stmt = $conn->prepare("UPDATE courses SET created_by = ? WHERE created_by IS NULL");
    $stmt->bind_param("i", $new_admin_id);
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    echo "Admin password updated successfully. You can now login with:<br>";
    echo "Username: admin<br>";
    echo "Password: admin123";

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo "Error updating admin: " . $e->getMessage();
}
