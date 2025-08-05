<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 403 Forbidden");
    exit("Access denied");
}

if (!isset($_GET['course_id'])) {
    header("HTTP/1.1 400 Bad Request");
    exit("PDF ID not provided");
}

if (!isset($_GET['id'])) {
    header("HTTP/1.1 400 Bad Request");
    exit("PDF ID not provided");
}

$course_id = $_GET['course_id'];
$id = $_GET['id'];

$stmt = $conn->prepare("SELECT video_url FROM course_videos WHERE course_id = ? AND id = ?");
$stmt->bind_param("is", $course_id, $id);
$stmt->execute();
$video_result = $stmt->get_result();
$video_info = $video_result->fetch_assoc();

if ($video_result->num_rows === 0) {
    header("HTTP/1.1 404 Not Found");
    exit(`"File not found"`.$course_id .$id);
}

$video_file_path = __DIR__ . '/../' . $video_info['video_url'];

if (!file_exists($video_file_path)) {
    header("HTTP/1.1 404 Not Found");
    echo $video_file_path;
    exit("PDF file not found");
}

// Set proper headers
$file_size = filesize($video_file_path);
$file_extension = strtolower(pathinfo($video_file_path, PATHINFO_EXTENSION));

switch ($file_extension) {
    case "mp4":
        header("Content-Type: video/mp4");
        break;
    case "webm":
        header("Content-Type: video/webm");
        break;
    default:
        header("Content-Type: application/octet-stream");
}
header('Content-Type: application/pdf');
readfile($video_file_path);
?>