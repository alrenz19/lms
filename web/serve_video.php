<?php
// serve_video.php
$file = $_GET['file'];   // full path from DB
$path = $file;

if (!file_exists($path)) {
    http_response_code(404);
    exit('File not found');
}

$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$mime = match($ext) {
    'png' => 'image/png',
    'jpg','jpeg' => 'image/jpeg',
    'gif' => 'image/gif',
    'mp4' => 'video/mp4',
    'pdf' => 'application/pdf',
    default => 'application/octet-stream'
};

header("Content-Type: $mime");
readfile($path);
exit;
