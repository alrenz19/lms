<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get detailed progress information
$stmt = $conn->prepare("
    SELECT 
        c.id as course_id,
        c.title as course_title,
        c.description as course_description,
        COUNT(DISTINCT q.id) as total_quizzes,
        COUNT(DISTINCT up.quiz_id) as completed_quizzes,
        COALESCE((COUNT(DISTINCT up.quiz_id) * 100 / COUNT(DISTINCT q.id)), 0) as course_progress,
        (
            SELECT COUNT(*) 
            FROM questions qs 
            JOIN quizzes qz ON qs.quiz_id = qz.id
            WHERE qz.course_id = c.id
        ) as total_questions,
        (
            SELECT COUNT(*)
            FROM user_progress up2
            JOIN quizzes q2 ON up2.quiz_id = q2.id
            WHERE q2.course_id = c.id 
            AND up2.user_id = ?
            AND up2.score > 0
        ) as correct_answers,
        COALESCE(MAX(up.updated_at), c.created_at) as last_activity
    FROM courses c
    LEFT JOIN quizzes q ON c.id = q.course_id
    LEFT JOIN user_progress up ON q.id = up.quiz_id AND up.user_id = ?
    GROUP BY c.id
    ORDER BY last_activity DESC, c.title ASC
");

$stmt->bind_param("ii", $user_id, $user_id); // Fixed: removed extra parameter
$stmt->execute();
$courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Remove overall statistics calculation
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Progress - LMS</title>
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
                <!-- Search and Title Section -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-graph-up"></i> My Learning Progress</h2>
                    <div class="col-md-4">
                        <?php 
                        $search_placeholder = "Search courses...";
                        include 'includes/search_bar.php'; 
                        ?>
                    </div>
                </div>

                <!-- Course Progress Cards -->
                <div class="row">
                    <?php foreach ($courses as $course): ?>
                        <div class="col-md-6 mb-4">
                            <div class="admin-card h-100">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4><?php echo htmlspecialchars($course['course_title']); ?></h4>
                                    <span class="badge bg-<?php echo $course['course_progress'] >= 100 ? 'success' : 'primary'; ?>">
                                        <?php echo $course['course_progress'] >= 100 ? 'Completed' : 'In Progress'; ?>
                                    </span>
                                </div>
                                <div class="progress mb-4" style="height: 15px;">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: <?php echo round($course['course_progress']); ?>%">
                                        <?php echo round($course['course_progress']); ?>%
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                                            <div>
                                                <small class="text-muted d-block">Completed Quizzes</small>
                                                <strong><?php echo $course['completed_quizzes']; ?> / <?php echo $course['total_quizzes']; ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-star-fill text-warning me-2"></i>
                                            <div>
                                                <small class="text-muted d-block">Correct Answers</small>
                                                <strong><?php echo $course['correct_answers']; ?> / <?php echo $course['total_questions']; ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($course['last_activity']): ?>
                                    <small class="text-muted d-block mb-3">
                                        <i class="bi bi-clock-history"></i>
                                        Last activity: <?php echo date('F j, Y g:i A', strtotime($course['last_activity'])); ?>
                                    </small>
                                <?php endif; ?>
                                <div class="text-end">
                                    <a href="view_course.php?id=<?php echo $course['course_id']; ?>" 
                                       class="btn btn-primary action-button">
                                        <i class="bi bi-arrow-right-circle"></i> Continue Course
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
