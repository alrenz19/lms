<?php
session_start();
require_once '../config.php';

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
        c.description,
        COUNT(DISTINCT q.id) as quiz_count,
        COUNT(DISTINCT up.quiz_id) as completed_quizzes,
        COALESCE(AVG(up.score), 0) as average_score,
        COALESCE((COUNT(DISTINCT up.quiz_id) * 100.0 / NULLIF(COUNT(DISTINCT q.id), 0)), 0) as progress
    FROM courses c
    LEFT JOIN quizzes q ON c.id = q.course_id
    LEFT JOIN user_progress up ON q.id = up.quiz_id AND up.user_id = ?
    GROUP BY c.id, c.title, c.description
";

$stmt = $conn->prepare($progress_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$courses_result = $stmt->get_result();
$courses = [];
$total_progress = 0;
$completed_courses = 0;
$total_score = 0;
$courses_with_score = 0;

while ($course = $courses_result->fetch_assoc()) {
    $courses[] = $course;
    $total_progress += $course['progress'];
    if ($course['progress'] == 100) {
        $completed_courses++;
    }
    if ($course['average_score'] > 0) {
        $total_score += $course['average_score'];
        $courses_with_score++;
    }
}

$course_count = count($courses);
$overall_progress = $course_count > 0 ? $total_progress / $course_count : 0;
$average_score = $courses_with_score > 0 ? $total_score / $courses_with_score : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
    .wrapper {
        display: flex;
        min-height: 100vh;
        width: 100%;
    }

    .content {
        flex: 1;
        margin-left: 260px;
        padding: 2rem;
        background-color: #f9fafb;
    }

    @media (max-width: 768px) {
        .content {
            margin-left: 0;
            padding: 1rem;
        }
    }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="content">
            <div class="container mt-4">
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-4">
                            <div class="admin-card h-100">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon me-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="9" cy="7" r="4"></circle>
                                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="mb-0">User Management</h3>
                                        <p class="text-muted mb-3">Manage system users and roles</p>
                                    </div>
                                </div>
                                <a href="manage_users.php" class="btn btn-primary action-button">
                                    Manage Users
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="admin-card h-100">
                                <div class="d-flex align-items-center">
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
                    <div class="row">
                        <div class="col-12 mb-4">
                            <div class="welcome-banner">
                                <div class="welcome-content">
                                    <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
                                    <p>Continue your learning journey with our courses.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Overview -->
                        <div class="col-md-4 mb-4">
                            <div class="stats-card">
                                <div class="stats-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="20" x2="12" y2="10"></line>
                                        <line x1="18" y1="20" x2="18" y2="4"></line>
                                        <line x1="6" y1="20" x2="6" y2="16"></line>
                                    </svg>
                                </div>
                                <div class="stats-info">
                                    <h3>Overall Progress</h3>
                                    <h2><?php echo number_format($overall_progress, 1); ?>%</h2>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-4">
                            <div class="stats-card">
                                <div class="stats-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                    </svg>
                                </div>
                                <div class="stats-info">
                                    <h3>Completed Courses</h3>
                                    <h2><?php echo $completed_courses; ?></h2>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-4">
                            <div class="stats-card">
                                <div class="stats-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="8" r="7"></circle>
                                        <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline>
                                    </svg>
                                </div>
                                <div class="stats-info">
                                    <h3>Average Score</h3>
                                    <h2><?php echo number_format($average_score, 1); ?>%</h2>
                                </div>
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
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Real-time search functionality
        const searchInput = document.getElementById('searchCourses');
        if (searchInput) {
            const courseCards = document.querySelectorAll('[data-course-card]');
            
            function filterCourses(searchTerm) {
                searchTerm = searchTerm.toLowerCase();
                courseCards.forEach(card => {
                    const title = card.querySelector('.course-title').textContent.toLowerCase();
                    const description = card.querySelector('.course-description').textContent.toLowerCase();
                    const matches = title.includes(searchTerm) || description.includes(searchTerm);
                    card.style.display = matches ? '' : 'none';
                });
            }

            searchInput.addEventListener('input', (e) => {
                filterCourses(e.target.value);
            });

            // Clear search functionality
            window.clearSearch = function() {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input'));
                searchInput.focus();
            };
        }
    });
    </script>
    <style>
    /* Welcome Banner */
    .welcome-banner {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        border-radius: 12px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
    }

    .welcome-content h1 {
        font-size: 2rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .welcome-content p {
        font-size: 1.1rem;
        opacity: 0.9;
        margin: 0;
    }

    /* Stats Cards */
    .stats-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        height: 100%;
    }

    .stats-icon {
        width: 48px;
        height: 48px;
        background-color: rgba(99, 102, 241, 0.1);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
    }

    .stats-icon svg {
        width: 24px;
        height: 24px;
        color: #6366f1;
    }

    .stats-info h3 {
        font-size: 0.875rem;
        font-weight: 500;
        color: #6b7280;
        margin-bottom: 0.5rem;
    }

    .stats-info h2 {
        font-size: 1.5rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
    }

    /* Course Cards */
    .course-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .course-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1);
    }

    .course-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .course-icon {
        width: 48px;
        height: 48px;
        background-color: rgba(99, 102, 241, 0.1);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
    }

    .course-icon svg {
        width: 24px;
        height: 24px;
        color: #6366f1;
    }

    .course-progress {
        margin-top: 1rem;
    }

    .progress {
        height: 8px;
        background-color: #e5e7eb;
        border-radius: 9999px;
        overflow: hidden;
    }

    .progress-bar {
        background-color: #6366f1;
        transition: width 0.3s ease;
    }

    .progress-text {
        font-size: 0.875rem;
        color: #6b7280;
        margin-top: 0.5rem;
        display: block;
    }

    .course-body {
        padding: 1.5rem;
        flex-grow: 1;
    }

    .course-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.5rem;
    }

    .course-description {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 1rem;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .course-meta {
        display: flex;
        align-items: center;
        gap: 1rem;
        font-size: 0.875rem;
        color: #6b7280;
    }

    .quiz-count, .completion-status {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .course-footer {
        padding: 1.5rem;
        border-top: 1px solid #e5e7eb;
    }

    .course-footer .btn {
        width: 100%;
    }

    /* Section Header */
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .section-header h2 {
        font-size: 1.5rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
    }

    /* Admin Cards */
    .admin-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .admin-card h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #111827;
    }

    .admin-card p {
        font-size: 0.875rem;
        color: #6b7280;
    }

    .action-button {
        margin-top: 1rem;
        width: 100%;
    }
    </style>
</body>
</html>
