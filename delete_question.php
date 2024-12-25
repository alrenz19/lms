<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$question_id = $_GET['id'] ?? 0;
$course_id = $_GET['course_id'] ?? 0;

if ($question_id > 0) {
    $conn->query("DELETE FROM questions WHERE id = $question_id");
}

header("Location: manage_quiz.php?course_id=" . $course_id);
exit;
?>
