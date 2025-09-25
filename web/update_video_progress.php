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
    // ✅ Fetch updated progress
    // Get total videos in course
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM course_videos WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $total_videos = $stmt->get_result()->fetch_assoc()['total'];

    // Get watched videos count
    $stmt = $conn->prepare("SELECT COUNT(*) as watched FROM user_video_progress uv 
                            JOIN course_videos cv ON uv.video_id = cv.id 
                            WHERE uv.user_id = ? AND cv.course_id = ? AND uv.watched = 1");
    $stmt->bind_param("ii", $user_id, $course_id);
    $stmt->execute();
    $watched_videos = $stmt->get_result()->fetch_assoc()['watched'];

    $overall_progress = $total_videos > 0 ? round(($watched_videos / $total_videos) * 100) : 0;

    // Get video progress details
    $stmt = $conn->prepare("SELECT video_id, watched FROM user_video_progress WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $video_progress_data = $stmt->get_result();

    $video_progress = [];
    while ($row = $video_progress_data->fetch_assoc()) {
        $video_progress[$row['video_id']] = $row['watched'] ? 'completed' : 'pending';
    }

    // Check if quiz can be accessed (all modules watched)
    $can_access_quiz = ($watched_videos >= $total_videos);

    echo json_encode([
        'success' => true,
        'message' => 'Module progress updated successfully',
        'overall_progress' => $overall_progress,
        'course_id' => $course_id,
        'video_progress' => $video_progress,
        'can_access_quiz' => $can_access_quiz
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update video progress: ' . $conn->error]);
}
