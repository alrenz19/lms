<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$course_id = $_GET['id'] ?? 0;

if ($course_id > 0) {
    // Start transaction to ensure all related data is deleted
    $conn->begin_transaction();
    
    try {
        // 🔹 Get all question images for this course
        $query = "SELECT question_image, option_a_image, option_b_image, option_c_image, option_d_image 
                  FROM questions 
                  WHERE course_id = $course_id";
        
        $result = $conn->query($query);
        $imagesToDelete = [];
        
        while ($row = $result->fetch_assoc()) {
            foreach (['question_image', 'option_a_image', 'option_b_image', 'option_c_image', 'option_d_image'] as $imgField) {
                if (!empty($row[$imgField])) {
                    if (strpos($row[$imgField], 'assets:') === 0) {
                        continue; // skip shared assets
                    }
                    $imagesToDelete[] = $row[$imgField];
                }
            }
        }
        
        // 🔹 Get course videos to delete from filesystem
        $videoQuery = "SELECT video_url FROM course_videos WHERE course_id = $course_id";
        $videoResult = $conn->query($videoQuery);
        $videosToDelete = [];
        
        while ($row = $videoResult->fetch_assoc()) {
            if (!empty($row['video_url'])) {
                $videosToDelete[] = $row['video_url'];
            }
        }

        // 🔹 Delete user progress (quiz progress)
        $conn->query("DELETE FROM user_progress WHERE course_id = $course_id");

        // 🔹 Delete user video progress
        $conn->query("DELETE uvp FROM user_video_progress uvp 
                      JOIN course_videos cv ON uvp.video_id = cv.id
                      WHERE cv.course_id = $course_id");

        // 🔹 Delete questions
        $conn->query("DELETE FROM questions WHERE course_id = $course_id");
        
        // 🔹 Delete course videos
        $conn->query("DELETE FROM course_videos WHERE course_id = $course_id");
        
        // 🔹 Finally delete the course
        $conn->query("DELETE FROM courses WHERE id = $course_id");
        
        $conn->commit();
        
        // === Filesystem cleanup ===
        $uploadDir = "../uploads/question_images/";

        // Delete question images
        foreach ($imagesToDelete as $image) {
            $imagePath = $uploadDir . basename($image);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        // Delete course videos (full path already stored in DB)
        foreach ($videosToDelete as $videoPath) {
            if (file_exists($videoPath)) {
                unlink($videoPath);
            }
        }
        
        header("Location: manage_courses.php?success=deleted");
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: manage_courses.php?error=delete_failed");
    }
} else {
    header("Location: manage_courses.php");
}
exit;
?>
