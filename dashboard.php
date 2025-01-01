<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$is_admin = ($_SESSION['role'] === 'admin');
$user_id = $_SESSION['user_id'];

// Add these queries for admin dashboard statistics
if ($is_admin) {
    // Get total users count
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
    $users_count = $result->fetch_assoc()['count'];

    // Get total courses count
    $result = $conn->query("SELECT COUNT(*) as count FROM courses");
    $courses_count = $result->fetch_assoc()['count'];
}

// Calculate overall progress across all courses
$progress_query = "
    SELECT 
        c.id as course_id,
        c.title as course_title,
        COUNT(DISTINCT q.id) as total_quizzes,
        COUNT(DISTINCT up.quiz_id) as completed_quizzes,
        COALESCE((COUNT(DISTINCT up.quiz_id) * 100 / COUNT(DISTINCT q.id)), 0) as course_progress
    FROM courses c
    LEFT JOIN quizzes q ON c.id = q.course_id
    LEFT JOIN user_progress up ON q.id = up.quiz_id AND up.user_id = ?
    GROUP BY c.id
";

$stmt = $conn->prepare($progress_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate overall progress
$total_progress = 0;
$course_count = count($courses);

if ($course_count > 0) {
    foreach ($courses as $course) {
        $total_progress += $course['course_progress'];
    }
    $overall_progress = $total_progress / $course_count;
} else {
    $overall_progress = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - Dashboard</title>
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
                <?php if ($is_admin): ?>
                    <!-- Admin Dashboard -->
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="admin-card h-100">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="stats-icon me-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="9" cy="7" r="4"></circle>
                                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="mb-1">Total Users</h3>
                                        <h2 class="mb-0"><?php echo $users_count; ?></h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="admin-card h-100">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="stats-icon me-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="mb-1">Total Courses</h3>
                                        <h2 class="mb-0"><?php echo $courses_count; ?></h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="admin-card h-100">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="stats-icon me-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="9" cy="7" r="4"></circle>
                                            <path d="M22 12h-4"></path>
                                            <path d="M18 8l4 4-4 4"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="mb-0">User Management</h3>
                                        <p class="text-muted mb-3">Manage user accounts and permissions</p>
                                    </div>
                                </div>
                                <a href="manage_users.php" class="btn btn-primary action-button">
                                    Manage Users
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="admin-card h-100">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="stats-icon me-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="mb-0">Course Management</h3>
                                        <p class="text-muted mb-3">Create and manage course content</p>
                                    </div>
                                </div>
                                <a href="manage_courses.php" class="btn btn-primary action-button">
                                    Manage Courses
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row mb-4">
                        <?php 
                        $search_placeholder = "Search your courses...";
                        include 'includes/search_bar.php'; 
                        ?>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <h2 class="mb-4"><i class="bi bi-collection"></i> My Courses</h2>
                            <div class="course-list">
                                <?php foreach ($courses as $course): ?>
                                    <div class="course-item">
                                        <a href="view_course.php?id=<?php echo $course['course_id']; ?>" 
                                           class="text-decoration-none">
                                            <h5 class="course-title">
                                                <?php echo htmlspecialchars($course['course_title']); ?>
                                            </h5>
                                            <div class="progress course-progress">
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: <?php echo $course['course_progress']; ?>%">
                                                    <?php echo round($course['course_progress']); ?>%
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-muted">
                                                    <i class="bi bi-check-circle-fill text-success"></i>
                                                    Completed: <?php echo $course['completed_quizzes']; ?> / <?php echo $course['total_quizzes']; ?> quizzes
                                                </span>
                                                <button class="btn btn-primary btn-sm action-button">
                                                    Continue Learning <i class="bi bi-arrow-right"></i>
                                                </button>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="liveToast" class="toast hide" role="alert">
            <div class="toast-header">
                <i class="bi bi-info-circle me-2"></i>
                <strong class="me-auto">Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="toastMessage"></div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
