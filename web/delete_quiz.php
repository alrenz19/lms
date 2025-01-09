<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$quiz_id = $_GET['id'] ?? 0;
$course_id = $_GET['course_id'] ?? 0;

if (!$quiz_id || !$course_id) {
    header("Location: manage_courses.php?error=invalid_request");
    exit;
}

// Verify that the quiz belongs to the course and exists
$stmt = $conn->prepare("SELECT id FROM quizzes WHERE id = ? AND course_id = ?");
$stmt->bind_param("ii", $quiz_id, $course_id);
$stmt->execute();
if (!$stmt->get_result()->fetch_assoc()) {
    header("Location: manage_courses.php?error=invalid_quiz");
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Delete user progress for this quiz
    $stmt = $conn->prepare("DELETE FROM user_progress WHERE quiz_id = ?");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();

    // Delete questions for this quiz
    $stmt = $conn->prepare("DELETE FROM questions WHERE quiz_id = ?");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();

    // Finally delete the quiz
    $stmt = $conn->prepare("DELETE FROM quizzes WHERE id = ?");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();

    // If everything is successful, commit the transaction
    $conn->commit();
    header("Location: manage_quiz.php?course_id=" . $course_id . "&success=quiz_deleted");
    exit;

} catch (Exception $e) {
    // If there's an error, rollback the transaction
    $conn->rollback();
    error_log("Error deleting quiz: " . $e->getMessage());
    header("Location: manage_quiz.php?course_id=" . $course_id . "&error=delete_failed");
    exit;
}
?>
