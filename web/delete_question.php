<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$question_id = $_GET['question_id'] ?? 0;
$course_id = $_GET['course_id'] ?? 0;
$quiz_id = $_GET['quiz_id'] ?? 0;

if ($question_id > 0) {
    try {
        // First, get all image paths for this question
        $stmt = $conn->prepare("SELECT question_image, option_a_image, option_b_image, option_c_image, option_d_image FROM questions WHERE id = ?");
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $question = $result->fetch_assoc();
        
        // Array to store image paths that need to be deleted
        $imagesToDelete = [];
        
        // Add question image and option images to deletion list if they exist
        if ($question) {
            foreach (['question_image', 'option_a_image', 'option_b_image', 'option_c_image', 'option_d_image'] as $imgField) {
                if (!empty($question[$imgField])) {
                    if (strpos($question[$imgField], 'assets:') === 0) {
                        // Skip assets folder images since they're shared resources
                        continue;
                    }
                    $imagesToDelete[] = $question[$imgField];
                }
            }
        }
        
        // Delete the question from the database
        $stmt = $conn->prepare("DELETE FROM questions WHERE id = ?");
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            // After successful database deletion, remove the files
            $uploadDir = "../uploads/question_images/";
            
            // Delete question images
            foreach ($imagesToDelete as $image) {
                $imagePath = $uploadDir . $image;
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            $_SESSION['success'] = "Question deleted successfully!";
        } else {
            $_SESSION['error'] = "Question could not be found or deleted.";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error deleting question: " . $e->getMessage();
    }
}

header("Location: manage_quiz.php?course_id=" . $course_id . "&quiz_id=" . $quiz_id);
exit;
?>
