<?php
require_once dirname(__FILE__) . '/config.php';

try {
    $conn->begin_transaction();
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    
    // Clear existing accounts
    $conn->query("DELETE FROM users WHERE username IN ('admin', 'testuser')");
    
    // Define variables for admin account
    $admin_username = 'admin';
    $admin_fullname = 'System Administrator';
    $admin_email = 'admin@lms.com';
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $admin_role = 'admin';
    
    // Create admin account
    $admin_stmt = $conn->prepare("INSERT INTO users (username, full_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
    $admin_stmt->bind_param("sssss", 
        $admin_username,
        $admin_fullname,
        $admin_email,
        $admin_password,
        $admin_role
    );
    $admin_stmt->execute();
    $admin_id = $conn->insert_id;
    
    // Define variables for test user
    $user_username = 'testuser';
    $user_fullname = 'Test User';
    $user_email = 'user@lms.com';
    $user_password = password_hash('user123', PASSWORD_DEFAULT);
    $user_role = 'user';
    
    // Create test user account
    $user_stmt = $conn->prepare("INSERT INTO users (username, full_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
    $user_stmt->bind_param("sssss",
        $user_username,
        $user_fullname,
        $user_email,
        $user_password,
        $user_role
    );
    $user_stmt->execute();
    
    // Define variables for welcome course
    $course_title = 'Welcome to LMS';
    $course_desc = 'Introduction to the Learning Management System';
    
    // Create welcome course
    $course_stmt = $conn->prepare("INSERT INTO courses (title, description, created_by) VALUES (?, ?, ?)");
    $course_stmt->bind_param("ssi", 
        $course_title,
        $course_desc,
        $admin_id
    );
    $course_stmt->execute();
    
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    $conn->commit();
    
    echo "<h3>Default accounts have been created successfully!</h3>";
    echo "<h4>Admin Account:</h4>";
    echo "Username: admin<br>";
    echo "Password: admin123<br><br>";
    echo "<h4>Test User Account:</h4>";
    echo "Username: testuser<br>";
    echo "Password: user123<br>";
    
} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
} finally {
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
}
?>
