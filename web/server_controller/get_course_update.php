<?php
session_start();
require_once '../../config.php'; // Adjust path if needed

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
$course_id = isset($_GET['course_id']) ? (int) $_GET['course_id'] : 0;

if (!$user_id || !$course_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$videos = [];
$video_progress = [];

$stmt = $conn->prepare("
    SELECT cv.*, uvp.watched
    FROM course_videos cv
    LEFT JOIN user_video_progress uvp 
        ON cv.id = uvp.video_id AND uvp.user_id = ? AND uvp.watched = 1
    WHERE cv.course_id = ?
");
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $video_id = $row['id'];
    $videos[] = $row;

    if (!is_null($row['watched'])) {
        $video_progress[$video_id] = 'completed';
    }
}

$totalModules = count($videos);
$completedModules = count($video_progress);
$overall_progress = ($totalModules > 0) ? ($completedModules / $totalModules) * 100 : 0;
$can_access_quiz = round($overall_progress) < 100 ? false : true;

echo json_encode([
    'success' => true,
    'overall_progress' => round($overall_progress),
    'video_progress' => $video_progress,
    'total_modules' => $totalModules,
    'completed_modules' => $completedModules,
    'can_access_quiz' => $can_access_quiz
]);
exit;
