<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$video_id = $_POST['video_id'] ?? null;

if ($user_id && $video_id) {
    $stmt = $conn->prepare("SELECT * FROM user_video_progress WHERE user_id = ? AND video_id = ?");
    $stmt->bind_param("ii", $user_id, $video_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt = $conn->prepare("INSERT INTO user_video_progress (user_id, video_id, watched) VALUES (?, ?, 1)");
        $stmt->bind_param("ii", $user_id, $video_id);
        $stmt->execute();
    }
    
    // ✅ Redirect back to the previous page
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
} else {
    // Optional: redirect back with an error message
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
?>
