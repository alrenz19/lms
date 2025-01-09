-- Active: 1735001374986@@127.0.0.1@3306@db_lms
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
        
        // Finally delete the course
        $conn->query("DELETE FROM courses WHERE id = $course_id");
        
        $conn->commit();
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
