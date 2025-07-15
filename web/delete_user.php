<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$user_id = $_GET['id'] ?? 0;

// Prevent deleting your own account
if ($user_id == $_SESSION['user_id']) {
    // Store toast info in session to display after redirect
    $_SESSION['toast'] = [
        'message' => 'You cannot delete your own account',
        'type' => 'error'
    ];
    header("Location: manage_users.php");
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Delete user progress first
    $stmt = $conn->prepare("DELETE FROM user_progress WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Then delete the user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Commit transaction
    $conn->commit();
    
    // Store toast info in session to display after redirect
    $_SESSION['toast'] = [
        'message' => 'User deleted successfully',
        'type' => 'success'
    ];
    header("Location: manage_users.php");
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    
    // Store toast info in session to display after redirect
    $_SESSION['toast'] = [
        'message' => 'Failed to delete user',
        'type' => 'error'
    ];
    header("Location: manage_users.php");
}
exit;
?>
