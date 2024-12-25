<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Get all courses
$courses = $conn->query("SELECT id, title FROM courses")->fetch_all(MYSQLI_ASSOC);

// Get user progress data
$progress_query = "
    SELECT 
        u.id as user_id,
        u.username,
        u.email,
        c.id as course_id,
        c.title as course_title,
        COUNT(DISTINCT q.id) as total_quizzes,
        COUNT(DISTINCT up.quiz_id) as completed_quizzes,
        COALESCE(AVG(up.progress_percentage), 0) as course_progress,
        COALESCE(SUM(up.score), 0) as total_score,
        MAX(up.updated_at) as last_activity
    FROM users u
    CROSS JOIN courses c
    LEFT JOIN quizzes q ON c.id = q.course_id
    LEFT JOIN user_progress up ON q.id = up.quiz_id AND u.id = up.user_id
    WHERE u.role = 'user'
    GROUP BY u.id, c.id
    ORDER BY u.username, c.title";

$progress_data = $conn->query($progress_query)->fetch_all(MYSQLI_ASSOC);

// Organize data by user
$users_progress = [];
foreach ($progress_data as $row) {
    if (!isset($users_progress[$row['user_id']])) {
        $users_progress[$row['user_id']] = [
            'username' => $row['username'],
            'email' => $row['email'],
            'courses' => []
        ];
    }
    $users_progress[$row['user_id']]['courses'][$row['course_id']] = [
        'title' => $row['course_title'],
        'progress' => $row['course_progress'],
        'completed_quizzes' => $row['completed_quizzes'],
        'total_quizzes' => $row['total_quizzes'],
        'total_score' => $row['total_score'],
        'last_activity' => $row['last_activity']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Progress Overview - LMS</title>
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
                <div class="admin-card mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2><i class="bi bi-graph-up"></i> User Progress Overview</h2>
                        <div class="col-md-4">
                            <?php 
                            $search_placeholder = "Search users or courses...";
                            include 'includes/search_bar.php'; 
                            ?>
                        </div>
                    </div>
                
                    <!-- Summary Cards -->
                    <div class="row admin-stats mb-4">
                        <div class="col-md-4">
                            <div class="stats-card dashboard-card">
                                <div class="card-body text-center">
                                    <i class="bi bi-people-fill stats-icon"></i>
                                    <h3>Active Students</h3>
                                    <h2><?php echo count($users_progress); ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stats-card dashboard-card">
                                <div class="card-body text-center">
                                    <i class="bi bi-book-fill stats-icon"></i>
                                    <h3>Total Courses</h3>
                                    <h2><?php echo count($courses); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Table -->
                    <div class="table-responsive mt-4">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Student</th>
                                    <?php foreach ($courses as $course): ?>
                                        <th><?php echo htmlspecialchars($course['title']); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users_progress as $user_id => $user): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-person-circle me-2 text-primary"></i>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <?php foreach ($courses as $course): ?>
                                            <td>
                                                <?php if (isset($user['courses'][$course['id']])): ?>
                                                    <?php $course_data = $user['courses'][$course['id']]; ?>
                                                    <div class="progress mb-2" style="height: 15px;">
                                                        <div class="progress-bar <?php echo $course_data['progress'] >= 100 ? 'bg-success' : 'bg-primary'; ?>" 
                                                             role="progressbar" 
                                                             style="width: <?php echo round($course_data['progress']); ?>%">
                                                            <?php echo round($course_data['progress']); ?>%
                                                        </div>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <small>
                                                            <i class="bi bi-check-circle-fill text-success"></i>
                                                            <?php echo $course_data['completed_quizzes']; ?>/<?php echo $course_data['total_quizzes']; ?>
                                                        </small>
                                                        <small>
                                                            <i class="bi bi-star-fill text-warning"></i>
                                                            <?php echo $course_data['total_score']; ?>
                                                        </small>
                                                    </div>
                                                    <?php if ($course_data['last_activity']): ?>
                                                        <div class="text-end">
                                                            <small class="text-muted">
                                                                <i class="bi bi-clock"></i>
                                                                <?php echo date('M j, Y', strtotime($course_data['last_activity'])); ?>
                                                            </small>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <div class="text-center text-muted">
                                                        <i class="bi bi-dash-circle"></i> Not started
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
