<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$course_id = $_GET['id'] ?? 0;

// Fetch course
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    header("Location: manage_courses.php");
    exit;
}

// Fetch video info
$video_info = null;
$stmt = $conn->prepare("SELECT * FROM course_videos WHERE course_id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$video_result = $stmt->get_result();
if ($video_result->num_rows > 0) {
    $video_info = $video_result->fetch_assoc();
}

// Class for video operations
class VideoHandler {
    private $conn;
    private $course_id;
    private $video_dir = "/volume1/video";
    private $max_size_mb = 250;

    public function __construct($conn, $course_id) {
        $this->conn = $conn;
        $this->course_id = $course_id;
    }

    public function isFileUpload() {
        if (isset($_FILES['course_video']) && $_FILES['course_video']['error'] == 0) {
            return 1; // File
        } elseif (!empty($_POST['video_link']) && filter_var($_POST['video_link'], FILTER_VALIDATE_URL)) {
            return 2; // URL
        }
        return 0; // None
    }

    function startsWithSlash($path) {
        $status = 0;
        if (!$path)
            $status = 0;
        elseif (is_string($path['video_url']) && strncmp($path['video_url'], '/', 1) === 0)
            $status = 1;
        else
            $status = 2;
        return $status;
    }


    public function processFileUpload($old_video_info = null) {
        $video = $_FILES['course_video'];
        $video_tmp = $video['tmp_name'];
        $file_size_mb = $video['size'] / (1024 * 1024);

        if ($file_size_mb > $this->max_size_mb) {
            throw new Exception("Video file size exceeds limit ({$this->max_size_mb}MB). Current: " . round($file_size_mb, 2) . "MB");
        }

        if (!file_exists($this->video_dir) && !mkdir($this->video_dir, 0777, true)) {
            throw new Exception("Failed to create video directory.");
        }

        $video_filename = uniqid() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $video['name']);
        $video_path = $this->video_dir . '/' . $video_filename;

        $this->conn->begin_transaction();

        // Remove old video
        if ($old_video_info) {
            $this->removeOldVideo($old_video_info);
        }

        if (!move_uploaded_file($video_tmp, $video_path)) {
            throw new Exception("Failed to move uploaded file.");
        }

        // Insert new video record
        $stmt = $this->conn->prepare("INSERT INTO course_videos (course_id, video_url, file_size) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $this->course_id, $video_path, $file_size_mb);
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert video record.");
        }

        // Update course table
        $stmt = $this->conn->prepare("UPDATE courses SET has_video = 1 WHERE id = ?");
        $stmt->bind_param("i", $this->course_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update course status.");
        }

        $this->conn->commit();
        return true;
    }

    public function isYouTubeLink() {
        $link = $_POST['video_link'];
        if (!is_string($link)) return false;

        $link = strtolower($link);

        return strpos($link, 'youtube.com') !== false || strpos($link, 'youtu.be') !== false;
    }

    public function processLinkUpload($old_file_info = null) {
        $link_file = $_POST['video_link'];
        $static_file_size = 20;
        
        // Remove old video
        $file_status = $this->startsWithSlash($old_file_info);
        if ($file_status == 1) {
            $this->removeOldVideo($old_video_info);
        } elseif($file_status == 2) {
            // Update when course has an existing data
            $stmt = $this->conn->prepare("
                UPDATE course_videos 
                SET course_id = ?, video_url = ?, file_size = ? 
                WHERE id = ?
            ");
            $stmt->bind_param("isii", $this->course_id, $link_file, $static_file_size, $old_file_info['id']);

            if (!$stmt->execute()) {
                throw new Exception("Failed to update course video status.");
            }
        } else {
            // Insert new video record
            $stmt = $this->conn->prepare("INSERT INTO course_videos (course_id, video_url, file_size) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $this->course_id, $link_file, $static_file_size);
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert video record.");
            }
        }

        // Update course table
        $stmt = $this->conn->prepare("UPDATE courses SET has_video = 1 WHERE id = ?");
        $stmt->bind_param("i", $this->course_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update course status.");
        }

    }

    public function removeOldVideo($video_info) {
        $video_path = $video_info['video_url'];
        if (file_exists($video_path) && !unlink($video_path)) {
            throw new Exception("Failed to delete old video file.");
        }

        $stmt = $this->conn->prepare("DELETE FROM course_videos WHERE course_id = ?");
        $stmt->bind_param("i", $this->course_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete old video record.");
        }
    }

    public function removeVideoAndUpdateCourse($video_info) {
        $this->conn->begin_transaction();
        $this->removeOldVideo($video_info);

        $stmt = $this->conn->prepare("UPDATE courses SET has_video = 0 WHERE id = ?");
        $stmt->bind_param("i", $this->course_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update course video status.");
        }

        $this->conn->commit();
    }
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
