<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

// Get course details
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

// Redirect if course doesn't exist
if (!$course) {
    header("Location: dashboard.php");
    exit;
}

// Get user progress for all quizzes in this course
$stmt = $conn->prepare("
    SELECT 
        COALESCE(AVG(CASE WHEN up.quiz_id IS NOT NULL THEN up.progress_percentage ELSE 0 END), 0) as overall_progress,
        COALESCE(SUM(up.score), 0) as total_score,
        COUNT(DISTINCT q.id) as total_quizzes,
        COUNT(DISTINCT up.quiz_id) as completed_quizzes,
        (
            SELECT COUNT(*)
            FROM questions qs
            JOIN quizzes qz ON qs.quiz_id = qz.id
            WHERE qz.course_id = ?
        ) as total_questions
    FROM quizzes q
    LEFT JOIN user_progress up ON q.id = up.quiz_id AND up.user_id = ?
    WHERE q.course_id = ?
");
$stmt->bind_param("iii", $course_id, $user_id, $course_id);
$stmt->execute();
$progress = $stmt->get_result()->fetch_assoc();

// If there are no quizzes, set progress to 0
if ($progress['total_quizzes'] == 0) {
    $progress['overall_progress'] = 0;
    $progress['total_score'] = 0;
}

// Get video information and progress
$video_info = null;
$video_progress = null;
$stmt = $conn->prepare("SELECT * FROM course_videos WHERE course_id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$video_result = $stmt->get_result();
if ($video_result->num_rows > 0) {
    $video_info = $video_result->fetch_assoc();
    
    // Get user's video progress
    $stmt = $conn->prepare("SELECT * FROM user_video_progress WHERE user_id = ? AND video_id = ? AND watched = 1");
    $stmt->bind_param("ii", $user_id, $video_info['id']);
    $stmt->execute();
    $video_progress = $stmt->get_result()->fetch_assoc();
}

// Get quizzes for this course with completion status
$stmt = $conn->prepare("
    SELECT 
        q.*,
        CASE WHEN up.quiz_id IS NOT NULL THEN 1 ELSE 0 END as is_completed,
        COALESCE(up.score, 0) as score,
        COALESCE(up.progress_percentage, 0) as progress
    FROM quizzes q
    LEFT JOIN user_progress up ON q.id = up.quiz_id AND up.user_id = ?
    WHERE q.course_id = ?
    ORDER BY q.id ASC
");
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$quizzes = $stmt->get_result();

// Calculate if user can access quizzes (only if video is watched)
$can_access_quizzes = !$video_info || ($video_progress !== null);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - <?php echo htmlspecialchars($course['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .course-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .course-header {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            color: white;
        }

        .course-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: white;
        }

        .course-description {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 1.5rem;
            max-width: 800px;
        }

        .progress-stats {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .stats-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 1.5rem;
            height: 100%;
            transition: transform 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-icon {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .stats-icon svg {
            width: 24px;
            height: 24px;
            color: white;
        }

        .stats-info h3 {
            font-size: 0.875rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 0.5rem;
        }

        .stats-info h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: white;
            margin: 0;
        }

        .video-section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .video-wrapper {
            position: relative;
            width: 100%;
            background: #000;
            overflow: hidden;
        }

        .course-video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .video-progress {
            padding: 1rem;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }

        .quiz-section {
            margin-top: 2rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e5e7eb;
        }

        .quiz-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
            height: 100%;
        }

        .quiz-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1);
        }

        .quiz-card-body {
            padding: 1.5rem;
        }

        .quiz-icon {
            width: 48px;
            height: 48px;
            background-color: rgba(99, 102, 241, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .quiz-icon svg {
            width: 24px;
            height: 24px;
            color: #6366f1;
        }

        .quiz-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 1rem;
        }

        .quiz-progress {
            margin-bottom: 1rem;
        }

        .quiz-progress.completed .progress-bar {
            background-color: #059669;
        }

        .quiz-score {
            font-weight: 600;
            color: #059669;
        }

        .quiz-actions {
            margin-top: 1rem;
        }

        .quiz-actions .btn {
            padding: 0.75rem 1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-outline-primary {
            color: #6366f1;
            border-color: #6366f1;
        }

        .btn-outline-primary:hover {
            background-color: #6366f1;
            border-color: #6366f1;
            color: white;
        }

        .btn-primary {
            background-color: #6366f1;
            border-color: #6366f1;
        }

        .btn-primary:hover {
            background-color: #4f46e5;
            border-color: #4f46e5;
        }

        .btn-secondary {
            background-color: #9ca3af;
            border-color: #9ca3af;
        }

        .btn-secondary:hover {
            background-color: #6b7280;
            border-color: #6b7280;
        }

        .video-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .video-status i {
            font-size: 1.25rem;
        }

        .video-duration {
            color: #6b7280;
        }

        .modal-content.bg-dark {
            background-color: #1a1a1a !important;
        }

        .modal-header .btn-close-white {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        #videoModal .modal-dialog {
            max-width: 90vw;
            margin: 1.75rem auto;
        }

        @media (min-width: 992px) {
            #videoModal .modal-dialog {
                max-width: 80vw;
            }
        }

        #videoModal .modal-content {
            background-color: #1a1a1a;
            border: none;
        }

        #videoModal .modal-header {
            padding: 1rem 1.5rem;
        }

        #videoModal .modal-body {
            padding: 0;
        }

        #videoModal .btn-close {
            opacity: 0.8;
        }

        #videoModal .btn-close:hover {
            opacity: 1;
        }

        .card {
            border: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
        }

        .card-body {
            padding: 1.5rem;
        }

        .btn-primary.rounded-pill {
            background-color: #6366f1;
            border-color: #6366f1;
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }

        .btn-primary.rounded-pill:hover {
            background-color: #4f46e5;
            border-color: #4f46e5;
        }

        .alert {
            border: none;
            border-radius: 8px;
        }

        .alert-success {
            background-color: #ecfdf5;
            color: #065f46;
        }

        .alert-warning {
            background-color: #fffbeb;
            color: #92400e;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #111827;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            font-size: 1.5rem;
            color: #6366f1;
        }

        .badge {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            font-weight: 500;
        }

        .badge.bg-warning {
            background-color: #fef3c7 !important;
            color: #92400e !important;
        }

        .btn-light {
            background-color: rgba(255, 255, 255, 0.9);
            border: none;
            color: #4f46e5;
            font-weight: 500;
        }

        .btn-light:hover {
            background-color: white;
            color: #4338ca;
        }

        .gap-3 {
            gap: 1rem !important;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="content">
            <div class="container mt-4">
                <div class="course-header">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <h1 class="course-title mb-0"><?php echo htmlspecialchars($course['title']); ?></h1>
                                <?php if ($video_info): ?>
                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#videoModal">
                                        <i class="bi bi-play-fill me-1"></i> Watch Video
                                    </button>
                                    <?php if (!$video_progress): ?>
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                            Watch video to unlock quizzes
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <p class="course-description"><?php echo htmlspecialchars($course['description']); ?></p>
                        </div>
                        <a href="dashboard.php" class="btn btn-outline-light">
                            <i class="bi bi-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>

                    <div class="progress-stats">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="stats-card">
                                    <div class="stats-icon">
                                        <i class="bi bi-book-fill"></i>
                                    </div>
                                    <div class="stats-info">
                                        <h3>Overall Progress</h3>
                                        <h2><?php echo round($progress['overall_progress']); ?>%</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="stats-card">
                                    <div class="stats-icon">
                                        <i class="bi bi-check-circle-fill"></i>
                                    </div>
                                    <div class="stats-info">
                                        <h3>Quizzes Completed</h3>
                                        <h2><?php echo $progress['completed_quizzes']; ?> / <?php echo $progress['total_quizzes']; ?></h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="stats-card">
                                    <div class="stats-icon">
                                        <i class="bi bi-trophy-fill"></i>
                                    </div>
                                    <div class="stats-info">
                                        <h3>Total Score</h3>
                                        <h2><?php echo $progress['total_score']; ?> pts</h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($video_info): ?>
                <!-- Video Modal -->
                <div class="modal fade" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content bg-dark">
                            <div class="modal-header border-0">
                                <h5 class="modal-title text-white" id="videoModalLabel">
                                    <?php echo htmlspecialchars($course['title']); ?> - Course Video
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-0">
                                <div class="video-wrapper" style="padding-top: 56.25%;">
                                    <video id="courseVideo" class="course-video" controls>
                                        <source src="get_video.php?id=<?php echo $video_info['id']; ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="quiz-section">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-journal-check me-2"></i>
                        <h5 class="mb-0">Course Quizzes</h5>
                    </div>
                    
                    <?php if ($video_info && !$video_progress): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Please watch the course video completely before accessing the quizzes.
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <?php while ($quiz = $quizzes->fetch_assoc()): ?>
                            <div class="col-md-6 mb-4">
                                <div class="quiz-card">
                                    <div class="quiz-card-body">
                                        <div class="quiz-icon">
                                            <i class="bi bi-question-circle"></i>
                                        </div>
                                        <h3 class="quiz-title"><?php echo htmlspecialchars($quiz['title']); ?></h3>
                                        
                                        <?php if ($quiz['is_completed']): ?>
                                            <div class="quiz-progress completed">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="progress-label">Completed</span>
                                                    <span class="quiz-score">Score: <?php echo $quiz['score']; ?>%</span>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                                                </div>
                                            </div>
                                            <div class="quiz-actions">
                                                <a href="quiz_review.php?id=<?php echo $quiz['id']; ?>" class="btn btn-outline-primary w-100">
                                                    <i class="bi bi-eye"></i> Review Quiz
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <div class="quiz-progress">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="progress-label">Not Started</span>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                                </div>
                                            </div>
                                            <div class="quiz-actions">
                                                <?php if ($can_access_quizzes): ?>
                                                    <a href="take_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-primary w-100">
                                                        <i class="bi bi-play-fill"></i> Start Quiz
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn btn-secondary w-100" disabled>
                                                        <i class="bi bi-lock"></i> Watch Video First
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const video = document.getElementById('courseVideo');
        const videoModal = document.getElementById('videoModal');
        
        if (video && videoModal) {
            let hasWatched = false;
            let currentTime = 0;
            
            // Save video position when modal is closed
            videoModal.addEventListener('hide.bs.modal', function () {
                currentTime = video.currentTime;
                video.pause();
            });
            
            // Restore video position when modal is opened
            videoModal.addEventListener('shown.bs.modal', function () {
                video.currentTime = currentTime;
            });
            
            // Track video completion
            video.addEventListener('timeupdate', function() {
                if (!hasWatched && video.currentTime >= video.duration * 0.9) { // 90% watched
                    hasWatched = true;
                    updateVideoProgress();
                }
            });

            // Prevent seeking forward
            video.addEventListener('seeking', function() {
                if (!hasWatched && video.currentTime > video.duration * 0.9) {
                    video.currentTime = Math.min(video.currentTime, video.duration * 0.9);
                }
            });

            function updateVideoProgress() {
                fetch('update_video_progress.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'video_id=<?php echo $video_info ? $video_info['id'] : '0'; ?>'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload(); // Refresh to update UI
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }
    });
    </script>
</body>
</html>
