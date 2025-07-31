<?php
session_start();
require_once '../config.php';
require_once 'video_controller.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$course_id = $_GET['id'] ?? 0;

// Fetch course
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ? AND removed = 0");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    header("Location: manage_courses.php");
    exit;
}

// Fetch video info
$video_info = null;
$stmt = $conn->prepare("SELECT * FROM course_videos WHERE course_id = ? AND removed = 0");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$video_result = $stmt->get_result();
if ($video_result->num_rows > 0) {
    $video_info = $video_result->fetch_assoc();
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $videoHandler = new VideoHandler($conn, $course_id);

    if (isset($_POST['update_course'])) {
        $title = $conn->real_escape_string($_POST['title']);
        $description = $conn->real_escape_string($_POST['description']);

        try {
            if($videoHandler->isYouTubeLink()) {
                throw new Exception("YouTube links are not allowed.");
            }
            $videoType = $videoHandler->isFileUpload();
            if ($videoType == 1) {
                $videoHandler->processFileUpload($video_info);
            } elseif ($videoType == 2) {
                $videoHandler->processLinkUpload($video_info);
            }

            $stmt = $conn->prepare("UPDATE courses SET title = ?, description = ? WHERE id = ?");
            $stmt->bind_param("ssi", $title, $description, $course_id);
            $stmt->execute();

            if (!isset($_SESSION['success'])) {
                $_SESSION['success'] = "Course updated successfully!";
            }
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = $e->getMessage();
        }

        header("Location: edit_course.php?id=" . $course_id);
        exit;
    }

    if (isset($_POST['remove_video'])) {
        try {
            if ($video_info) {
                $videoHandler->removeVideoAndUpdateCourse($video_info);
                $_SESSION['success'] = "Video removed successfully!";
            } else {
                throw new Exception("No video found to remove.");
            }
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = $e->getMessage();
        }

        header("Location: edit_course.php?id=" . $course_id);
        exit;
    }
}
?>
