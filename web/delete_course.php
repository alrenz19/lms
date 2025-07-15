-- Active: 1736124649896@@127.0.0.1@3306@db_lms
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
        // Get all question images for this course to delete them from the filesystem
        $query = "SELECT q.question_image, q.option_a_image, q.option_b_image, q.option_c_image, q.option_d_image 
                 FROM questions q 
                 JOIN quizzes qz ON q.quiz_id = qz.id 
                 WHERE qz.course_id = $course_id";
        
        $result = $conn->query($query);
        
        // Array to store image paths that need to be deleted
        $imagesToDelete = [];
        
        while ($row = $result->fetch_assoc()) {
            // Add question image and option images to deletion list if they exist
            foreach (['question_image', 'option_a_image', 'option_b_image', 'option_c_image', 'option_d_image'] as $imgField) {
                if (!empty($row[$imgField])) {
                    if (strpos($row[$imgField], 'assets:') === 0) {
                        // Skip assets folder images since they're shared resources
                        continue;
                    }
                    $imagesToDelete[] = $row[$imgField];
                }
            }
        }
        
        // Get course videos
        $videoQuery = "SELECT video_url FROM course_videos WHERE course_id = $course_id";
        $videoResult = $conn->query($videoQuery);
        
        $videosToDelete = [];
        while ($row = $videoResult->fetch_assoc()) {
            if (!empty($row['video_url'])) {
                $videosToDelete[] = $row['video_url'];
            }
        }
        
        // Delete related user progress
        $conn->query("DELETE up FROM user_progress up 
                     JOIN quizzes q ON up.quiz_id = q.id 
                     WHERE q.course_id = $course_id");
        
        // Delete related questions
        $conn->query("DELETE q FROM questions q 
                     JOIN quizzes qz ON q.quiz_id = qz.id 
                     WHERE qz.course_id = $course_id");
        
        // Delete quizzes
        $conn->query("DELETE FROM quizzes WHERE course_id = $course_id");
        
        // Delete course videos from database
        $conn->query("DELETE FROM course_videos WHERE course_id = $course_id");
        
        // Finally delete the course
        $conn->query("DELETE FROM courses WHERE id = $course_id");
        
        $conn->commit();
        
        // After successful database deletion, remove the files
        $uploadDir = "../uploads/question_images/";
        
        // Delete question images
        foreach ($imagesToDelete as $image) {
            $imagePath = $uploadDir . $image;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        // Delete course videos
        // Videos are stored at the path specified in the database
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
