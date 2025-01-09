<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$course_id = $_GET['id'] ?? 0;

// Get course details
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    header("Location: manage_courses.php");
    exit;
}

// Get video information if exists
$video_info = null;
$stmt = $conn->prepare("SELECT * FROM course_videos WHERE course_id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$video_result = $stmt->get_result();
if ($video_result->num_rows > 0) {
    $video_info = $video_result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_course'])) {
        $title = $conn->real_escape_string($_POST['title']);
        $description = $conn->real_escape_string($_POST['description']);
        
        // Handle video upload
        if (isset($_FILES['course_video']) && $_FILES['course_video']['error'] == 0) {
            $video = $_FILES['course_video'];
            $video_tmp = $video['tmp_name'];
            $file_size = $video['size'] / (1024 * 1024); // Convert to MB
            
            // Validate file size (250MB limit)
            if ($file_size > 250) {
                $_SESSION['error'] = "Video file size must not exceed 250MB. Current size: " . round($file_size, 2) . "MB";
                header("Location: edit_course.php?id=" . $course_id);
                exit;
            }
            
            // Create video directory if it doesn't exist
            $video_dir = "\\\\172.16.81.209\\it\\IT MITCH\\LMS\\VIDEO";
            if (!file_exists($video_dir)) {
                if (!mkdir($video_dir, 0777, true)) {
                    $_SESSION['error'] = "Failed to create video directory. Please contact administrator.";
                    header("Location: edit_course.php?id=" . $course_id);
                    exit;
                }
            }
            
            try {
                $conn->begin_transaction();
                
                // Generate unique filename
                $video_filename = uniqid() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $video['name']);
                $video_path = $video_dir . '\\' . $video_filename;
                
                // First try to delete old video if exists
                if ($video_info) {
                    $old_video_path = $video_info['file_path'];
                    if (file_exists($old_video_path)) {
                        if (!unlink($old_video_path)) {
                            throw new Exception("Failed to delete old video file");
                        }
                    }
                    // Delete old record
                    $stmt = $conn->prepare("DELETE FROM course_videos WHERE course_id = ?");
                    $stmt->bind_param("i", $course_id);
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to delete old video record");
                    }
                }
                
                // Move new video file
                if (!move_uploaded_file($video_tmp, $video_path)) {
                    throw new Exception("Failed to move uploaded file: " . error_get_last()['message']);
                }
                
                // Update course_videos table
                $stmt = $conn->prepare("INSERT INTO course_videos (course_id, file_path, file_size) VALUES (?, ?, ?)");
                $stmt->bind_param("isi", $course_id, $video_path, $file_size);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update database with video information");
                }
                
                // Update has_video in courses table
                $stmt = $conn->prepare("UPDATE courses SET has_video = 1 WHERE id = ?");
                $stmt->bind_param("i", $course_id);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update course video status");
                }
                
                $conn->commit();
                $_SESSION['success'] = "Course updated successfully with new video!";
                
            } catch (Exception $e) {
                $conn->rollback();
                // Clean up new video file if it was uploaded
                if (isset($video_path) && file_exists($video_path)) {
                    unlink($video_path);
                }
                $_SESSION['error'] = "Error updating video: " . $e->getMessage();
                header("Location: edit_course.php?id=" . $course_id);
                exit;
            }
        }
        
        $sql = "UPDATE courses SET title = ?, description = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $title, $description, $course_id);
        
        if ($stmt->execute()) {
            if (!isset($_SESSION['error'])) {
                $_SESSION['success'] = "Course updated successfully!";
            }
            header("Location: edit_course.php?id=" . $course_id);
            exit;
        }
    } elseif (isset($_POST['remove_video'])) {
        try {
            $conn->begin_transaction();

            // Get current video info
            if ($video_info) {
                // Delete physical file
                $old_video_path = $video_info['file_path'];
                if (file_exists($old_video_path)) {
                    if (!unlink($old_video_path)) {
                        throw new Exception("Failed to delete video file");
                    }
                }

                // Delete from course_videos table
                $stmt = $conn->prepare("DELETE FROM course_videos WHERE course_id = ?");
                $stmt->bind_param("i", $course_id);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to delete video record from database");
                }

                // Update has_video in courses table
                $stmt = $conn->prepare("UPDATE courses SET has_video = 0 WHERE id = ?");
                $stmt->bind_param("i", $course_id);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update course video status");
                }

                $conn->commit();
                $_SESSION['success'] = "Video removed successfully!";
            } else {
                throw new Exception("No video found to remove");
            }
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = "Failed to remove video: " . $e->getMessage();
        }
        header("Location: edit_course.php?id=" . $course_id);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course - LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .edit-course-header {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            color: white;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: white;
        }

        .edit-course-form {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .form-section:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-title i {
            color: #6366f1;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #d1d5db;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .video-info-card {
            background: #f9fafb;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .video-info-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .video-info-content {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #6b7280;
            font-size: 0.875rem;
        }

        .video-actions {
            margin-top: 1rem;
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: #6366f1;
            border-color: #6366f1;
        }

        .btn-primary:hover {
            background-color: #4f46e5;
            border-color: #4f46e5;
            transform: translateY(-1px);
        }

        .btn-outline-primary {
            color: #6366f1;
            border-color: #6366f1;
        }

        .btn-outline-primary:hover {
            background-color: #6366f1;
            color: white;
            transform: translateY(-1px);
        }

        .alert {
            border: none;
            border-radius: 8px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-info {
            background-color: #eff6ff;
            color: #1e40af;
        }

        .alert-warning {
            background-color: #fffbeb;
            color: #92400e;
        }

        .video-upload-section {
            display: none;
        }

        .video-upload-section.active {
            display: block;
        }

        .requirements-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .requirements-list li {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6b7280;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .requirements-list li:last-child {
            margin-bottom: 0;
        }

        .requirements-list i {
            color: #6366f1;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="content">
            <div class="container mt-4">
                <div class="edit-course-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="page-title">
                                <i class="bi bi-pencil-square"></i> Edit Course
                            </h1>
                            <p class="mb-0">Update course information and manage course content</p>
                        </div>
                        <a href="manage_courses.php" class="btn btn-outline-light">
                            <i class="bi bi-arrow-left"></i> Back to Courses
                        </a>
                    </div>
                </div>

                <?php if (isset($_SESSION['error']) || isset($_SESSION['success'])): ?>
                    <div class="alert alert-<?php echo isset($_SESSION['error']) ? 'warning' : 'success'; ?> alert-dismissible fade show">
                        <i class="bi bi-<?php echo isset($_SESSION['error']) ? 'exclamation-triangle' : 'check-circle'; ?>-fill"></i>
                        <?php 
                            echo isset($_SESSION['error']) ? $_SESSION['error'] : $_SESSION['success'];
                            unset($_SESSION['error']);
                            unset($_SESSION['success']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="edit-course-form">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-section">
                            <h2 class="section-title">
                                <i class="bi bi-info-circle"></i>
                                Basic Information
                            </h2>
                            <div class="mb-4">
                                <label for="title" class="form-label">Course Title</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo htmlspecialchars($course['title']); ?>" required>
                            </div>
                            <div class="mb-4">
                                <label for="description" class="form-label">Course Description</label>
                                <textarea class="form-control" id="description" name="description" 
                                          rows="5" required><?php echo htmlspecialchars($course['description']); ?></textarea>
                            </div>
                        </div>

                        <div class="form-section">
                            <h2 class="section-title">
                                <i class="bi bi-camera-video"></i>
                                Course Video
                            </h2>

                            <?php if ($video_info): ?>
                                <div class="video-info-card">
                                    <div class="video-info-title">
                                        <i class="bi bi-play-circle"></i>
                                        Current Video
                                    </div>
                                    <div class="video-info-content">
                                        <i class="bi bi-file-earmark-play"></i>
                                        <span>Size: <?php echo round($video_info['file_size'], 2); ?> MB</span>
                                    </div>
                                    <div class="video-actions">
                                        <button type="button" class="btn btn-outline-primary" onclick="toggleVideoUpload()">
                                            <i class="bi bi-arrow-repeat"></i> Replace Video
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" onclick="removeVideo()">
                                            <i class="bi bi-trash"></i> Remove Video
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="video-upload-section <?php echo !$video_info ? 'active' : ''; ?>" id="videoUploadSection">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    <div>
                                        <strong>Video Requirements:</strong>
                                        <ul class="requirements-list mt-2">
                                            <li><i class="bi bi-check-circle"></i> Maximum file size: 250MB</li>
                                            <li><i class="bi bi-check-circle"></i> Accepted formats: MP4, WebM</li>
                                            <li><i class="bi bi-check-circle"></i> Recommended resolution: 1280x720 or higher</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <input type="hidden" name="MAX_FILE_SIZE" value="262144000" />
                                    <input type="file" class="form-control" id="course_video" name="course_video" 
                                           accept="video/mp4,video/webm">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="manage_courses.php" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" name="update_course" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update Course
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleVideoUpload() {
        const uploadSection = document.getElementById('videoUploadSection');
        uploadSection.classList.toggle('active');
    }

    function removeVideo() {
        if (confirm('Are you sure you want to remove the current video? This action cannot be undone.')) {
            // Add form submission logic for video removal
            const form = document.createElement('form');
            form.method = 'POST';
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'remove_video';
            input.value = '1';
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    }

    // File size validation
    document.getElementById('course_video').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const fileSize = file.size / (1024 * 1024); // Convert to MB
            if (fileSize > 250) {
                alert('File size exceeds 250MB limit. Please choose a smaller file.');
                this.value = ''; // Clear the file input
            }
        }
    });
    </script>
</body>
</html>
