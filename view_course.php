<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$course_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Get course details
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

// Get user progress for all quizzes in this course
$stmt = $conn->prepare("
    SELECT 
        COALESCE(AVG(up.progress_percentage), 0) as overall_progress,
        SUM(up.score) as total_score,  -- Changed from MAX to SUM
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - <?php echo htmlspecialchars($course['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="content">
            <div class="container mt-4">
                <!-- Main Content Area -->
                <div class="row">
                    <!-- Course Content Column -->
                    <div class="col-md-8">
                        <div class="admin-card mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h2><i class="bi bi-book"></i> <?php echo htmlspecialchars($course['title']); ?></h2>
                                <a href="dashboard.php" class="btn btn-outline-primary">
                                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                                </a>
                            </div>
                            <p class="lead mb-4"><?php echo htmlspecialchars($course['description']); ?></p>
                        </div>

                        <!-- Quizzes Section -->
                        <div class="admin-card">
                            <h3 class="mb-4"><i class="bi bi-question-circle"></i> Available Quizzes</h3>
                            <div class="course-list">
                                <?php
                                $result = $conn->query("SELECT * FROM quizzes WHERE course_id = $course_id");
                                while ($quiz = $result->fetch_assoc()): ?>
                                    <div class="course-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0"><?php echo htmlspecialchars($quiz['title']); ?></h5>
                                                <?php
                                                // Get quiz progress
                                                $stmt = $conn->prepare("SELECT progress_percentage FROM user_progress WHERE user_id = ? AND quiz_id = ?");
                                                $stmt->bind_param("ii", $user_id, $quiz['id']);
                                                $stmt->execute();
                                                $quiz_progress = $stmt->get_result()->fetch_assoc();
                                                if ($quiz_progress): ?>
                                                    <div class="progress mt-2" style="height: 5px; width: 200px;">
                                                        <div class="progress-bar" role="progressbar" 
                                                             style="width: <?php echo $quiz_progress['progress_percentage']; ?>%"></div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <a href="take_quiz.php?id=<?php echo $quiz['id']; ?>" 
                                               class="btn btn-primary action-button <?php echo ($quiz_progress && $quiz_progress['progress_percentage'] == 100) ? 'disabled' : ''; ?>">
                                                <?php if ($quiz_progress && $quiz_progress['progress_percentage'] == 100): ?>
                                                    <i class="bi bi-check-circle"></i> Completed
                                                <?php else: ?>
                                                    <i class="bi bi-play-fill"></i> Start Quiz
                                                <?php endif; ?>
                                            </a>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Sidebar -->
                    <div class="col-md-4">
                        <div class="stats-card dashboard-card">
                            <div class="card-body">
                                <h4 class="text-white mb-4">Your Progress</h4>
                                <div class="progress mb-4" style="height: 20px;">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: <?php echo round($progress['overall_progress']); ?>%">
                                        <?php echo round($progress['overall_progress']); ?>%
                                    </div>
                                </div>
                                <div class="text-white">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi bi-check-circle-fill me-2"></i>
                                        <div>
                                            <small>Completed Quizzes</small>
                                            <h6 class="mb-0"><?php echo $progress['completed_quizzes']; ?> / <?php echo $progress['total_quizzes']; ?></h6>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-trophy-fill me-2"></i>
                                        <div>
                                            <small>Total Score</small>
                                            <h6 class="mb-0"><?php echo $progress['total_score']; ?> points</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
