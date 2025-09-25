<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

// Get quizzes for this course with completion status
    $stmt = $conn->prepare("
        SELECT 
            c.id AS course_id, c.title, c.description,
            COALESCE(up.completed, 0) AS is_completed,
            COALESCE(up.score, 0) AS score,
            COALESCE(up.total_score, 0) AS total_score,
            COALESCE(up.progress_percentage, 0) AS progress,
            EXISTS (
                SELECT 1 FROM questions q WHERE q.course_id = c.id AND q.removed = 0
            ) AS has_questions
        FROM courses c
        LEFT JOIN user_progress up 
            ON c.id = up.course_id AND up.user_id = ?
        WHERE c.id = ? AND c.removed = 0
        LIMIT 1
    ");
    $stmt->bind_param("ii", $user_id, $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $questions = $result->fetch_assoc();

    if (!$questions) {
        header("Location: dashboard.php");
        exit;
    }

    // Get video information and progress
    $videos = [];
    $video_progress = [];

    $stmt = $conn->prepare("
        SELECT cv.*, uvp.watched
        FROM course_videos cv
        LEFT JOIN user_video_progress uvp 
            ON cv.id = uvp.video_id AND uvp.user_id = ? AND uvp.watched = 1
        WHERE cv.course_id = ? AND cv.removed = 0
    ");
    $stmt->bind_param("ii", $user_id, $course_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $video_id = $row['id'];
        $videos[] = $row;

        // Store only if watched progress exists
        if (!is_null($row['watched'])) {
            $video_progress[$video_id] = [
                'video_id' => $video_id,
                'watched' => $row['watched']
            ];
        }
    }

    $totalModules = count($videos);
    $completedModules = 0;

    foreach ($videos as $module) {
        $video_id = $module['id'];
        if (isset($video_progress[$video_id])) {
            $completedModules++;
        }
    }

    $overall_progress = ($totalModules > 0) ? ($completedModules / $totalModules) * 100 : 0;

    // Calculate if user can access quizzes (only if video is watched)
    $can_access_quiz = round($overall_progress) < 100 ? false : true;
?>
