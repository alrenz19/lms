<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$video_id = isset($_POST['video_id']) ? intval($_POST['video_id']) : 0;

if (!$video_id) {
    echo json_encode(['success' => false, 'message' => 'Video ID not provided']);
    exit;
}

// First, check if a record exists
$stmt = $conn->prepare("SELECT id FROM user_video_progress WHERE user_id = ? AND video_id = ?");
$stmt->bind_param("ii", $_SESSION['user_id'], $video_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update existing record
    $stmt = $conn->prepare("UPDATE user_video_progress SET watched = 1, updated_at = CURRENT_TIMESTAMP WHERE user_id = ? AND video_id = ?");
} else {
    // Create new record
    $stmt = $conn->prepare("INSERT INTO user_video_progress (user_id, video_id, watched) VALUES (?, ?, 1)");
}

$stmt->bind_param("ii", $_SESSION['user_id'], $video_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update progress']);
} 