<?php
session_start();
require_once 'config.php';

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
                                <div>
                                    <a href="dashboard.php" class="btn btn-outline-primary">
                                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                                    </a>
                                </div>
                            </div>
                            <p class="lead mb-4"><?php echo htmlspecialchars($course['description']); ?></p>
                        </div>

                        <!-- Quizzes Section -->
                        <div class="admin-card">
                            <h3 class="mb-4"><i class="bi bi-question-circle"></i> Available Quizzes</h3>
                            <div class="course-list">
                                <?php
                                // Use prepared statement for quiz query
                                $stmt = $conn->prepare("
                                    SELECT q.*, up.completed, up.progress_percentage, up.score 
                                    FROM quizzes q 
                                    LEFT JOIN user_progress up ON up.quiz_id = q.id AND up.user_id = ? 
                                    WHERE q.course_id = ?
                                ");
                                $stmt->bind_param("ii", $user_id, $course_id);
                                $stmt->execute();
                                $quiz_result = $stmt->get_result();
                                
                                while ($quiz = $quiz_result->fetch_assoc()): ?>
                                    <div class="course-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0"><?php echo htmlspecialchars($quiz['title']); ?></h5>
                                                <?php if ($quiz['completed']): ?>
                                                    <div class="progress mt-2" style="height: 5px; width: 200px;">
                                                        <div class="progress-bar <?php echo $quiz['completed'] ? 'bg-success' : ''; ?>" 
                                                             role="progressbar" 
                                                             style="width: <?php echo $quiz['progress_percentage']; ?>%">
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">
                                                        Score: <?php echo $quiz['score']; ?> points
                                                        <?php if ($quiz['completed']): ?>
                                                            <span class="badge bg-success ms-2">Completed</span>
                                                        <?php endif; ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($quiz['completed']): ?>
                                                <div>
                                                    <a href="quiz_review.php?id=<?php echo $quiz['id']; ?>" 
                                                       class="btn btn-info action-button">
                                                        <i class="bi bi-eye"></i> Review Quiz
                                                    </a>
                                                </div>
                                            <?php else: ?>
                                                <div>
                                                    <a href="take_quiz.php?id=<?php echo $quiz['id']; ?>" 
                                                       class="btn btn-primary action-button">
                                                        <i class="bi bi-play-fill"></i> Start Quiz
                                                    </a>
                                                </div>
                                            <?php endif; ?>
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
