<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 403 Forbidden");
    exit("Access denied");
}

if (!isset($_GET['course_id'])) {
    header("HTTP/1.1 400 Bad Request");
    exit("Video ID not provided");
}

if (!isset($_GET['id'])) {
    header("HTTP/1.1 400 Bad Request");
    exit("Video ID not provided");
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
    exit(`"Video not found"`.$course_id .$id);
}

$video_file_path = $video_info['video_url'];

if (!file_exists($video_file_path)) {
    header("HTTP/1.1 404 Not Found");
    echo $video_file_path;
    exit("Video file not found");
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
header('Content-Type: video/mp4');
readfile($video_file_path);
header("Content-Length: " . $file_size);
header("Accept-Ranges: bytes");

// Handle range requests for video seeking
if (isset($_SERVER['HTTP_RANGE'])) {
    $ranges = array_map('trim', explode(',', $_SERVER['HTTP_RANGE']));
    $ranges = array_filter($ranges);
    
    if (empty($ranges)) {
        header("HTTP/1.1 416 Requested Range Not Satisfiable");
        header("Content-Range: bytes */" . $file_size);
        exit;
    }
    
    $range = explode('-', substr($ranges[0], 6));
    $start = intval($range[0]);
    $end = (isset($range[1]) && $range[1] !== '') ? intval($range[1]) : $file_size - 1;
    
    if ($start > $end || $start >= $file_size || $end >= $file_size) {
        header("HTTP/1.1 416 Requested Range Not Satisfiable");
        header("Content-Range: bytes */" . $file_size);
        exit;
    }
    
    header("HTTP/1.1 206 Partial Content");
    header("Content-Length: " . ($end - $start + 1));
    header("Content-Range: bytes " . $start . "-" . $end . "/" . $file_size);
    
    $fp = fopen($file_path, 'rb');
    fseek($fp, $start);
    $chunk_size = 8192;
    $remaining = $end - $start + 1;
    
    while ($remaining > 0 && !feof($fp)) {
        $read_size = min($chunk_size, $remaining);
        echo fread($fp, $read_size);
        $remaining -= $read_size;
        flush();
    }
    
    fclose($fp);
} else {
    // Output the whole file
    readfile($file_path);
}
exit; 