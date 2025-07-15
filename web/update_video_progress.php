<?php
session_start();
require_once '../config.php';

// Security check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if all required parameters are provided
if (!isset($_POST['video_id']) || !isset($_POST['user_id']) || !isset($_POST['course_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$video_id = (int)$_POST['video_id'];
$user_id = (int)$_POST['user_id'];
$course_id = (int)$_POST['course_id'];

// Additional security check - verify user_id from POST matches session user_id
if ($user_id !== $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'User ID mismatch']);
    exit;
}

// Verify the video exists and belongs to the specified course
$stmt = $conn->prepare("SELECT id FROM course_videos WHERE id = ? AND course_id = ?");
$stmt->bind_param("ii", $video_id, $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Video not found']);
    exit;
}

// Check if record already exists
$stmt = $conn->prepare("SELECT * FROM user_video_progress WHERE user_id = ? AND video_id = ?");
$stmt->bind_param("ii", $user_id, $video_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update existing record
    $stmt = $conn->prepare("UPDATE user_video_progress SET watched = 1, updated_at = NOW() WHERE user_id = ? AND video_id = ?");
    $stmt->bind_param("ii", $user_id, $video_id);
} else {
    // Insert new record
    $stmt = $conn->prepare("INSERT INTO user_video_progress (user_id, video_id, watched) VALUES (?, ?, 1)");
    $stmt->bind_param("ii", $user_id, $video_id);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Video progress updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update video progress: ' . $conn->error]);
} 