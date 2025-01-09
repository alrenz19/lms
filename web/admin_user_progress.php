<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Update the main query to get comprehensive user progress
$query = "
    SELECT 
        u.id as user_id,
        u.username,
        u.full_name,
        u.email,
        COUNT(DISTINCT c.id) as total_enrolled_courses,
        (
            SELECT COUNT(DISTINCT c.id)
            FROM courses c
            WHERE (
                SELECT COUNT(q.id)
                FROM quizzes q
                WHERE q.course_id = c.id
            ) = (
                SELECT COUNT(DISTINCT up2.quiz_id)
                FROM user_progress up2
                WHERE up2.user_id = u.id
                AND up2.quiz_id IN (
                    SELECT q2.id 
                    FROM quizzes q2 
                    WHERE q2.course_id = c.id
                )
            )
            AND (
                SELECT COUNT(q.id)
                FROM quizzes q
                WHERE q.course_id = c.id
            ) > 0
        ) as completed_courses,
        (
            SELECT COUNT(*)
            FROM user_progress up3
            WHERE up3.user_id = u.id
            AND up3.score > 0
        ) as total_correct_answers,
        (
            SELECT COUNT(*)
            FROM questions qs
            JOIN quizzes qz ON qs.quiz_id = qz.id
            JOIN courses c ON qz.course_id = c.id
        ) as total_questions,
        MAX(up.updated_at) as last_activity
    FROM users u
    LEFT JOIN user_progress up ON u.id = up.user_id
    LEFT JOIN quizzes q ON up.quiz_id = q.id
    LEFT JOIN courses c ON q.course_id = c.id
    WHERE u.role = 'user'
    GROUP BY u.id
    ORDER BY u.full_name";

$result = $conn->query($query);

// Get total courses and active users
$total_courses = $conn->query("SELECT COUNT(*) as total FROM courses")->fetch_assoc()['total'];
$total_users = $result->num_rows;

// Calculate overall platform statistics
$total_completed_courses = 0;
$total_correct_answers = 0;
$active_users_last_week = 0;
$current_time = time();

while ($row = $result->fetch_assoc()) {
    $total_completed_courses += $row['completed_courses'];
    $total_correct_answers += $row['total_correct_answers'];
    
    if ($row['last_activity'] && strtotime($row['last_activity']) > ($current_time - 7 * 24 * 60 * 60)) {
        $active_users_last_week++;
    }
}

$result->data_seek(0); // Reset result pointer
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Progress Overview - LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="content">
            <div class="container mt-4">
                <div class="progress-header">
                    <h1 class="page-title text-white">
                        <i class="bi bi-graph-up text-white"></i>
                        User Progress Overview
                    </h1>
                </div>

                <!-- Platform Stats -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div class="stats-info">
                                <h3>Active Users</h3>
                                <h2><?php echo $total_users; ?></h2>
                                <p class="stats-detail">
                                    <?php echo $active_users_last_week; ?> active this week
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="bi bi-book-fill"></i>
                            </div>
                            <div class="stats-info">
                                <h3>Total Courses</h3>
                                <h2><?php echo $total_courses; ?></h2>
                                <p class="stats-detail">
                                    <?php echo $total_completed_courses; ?> completions
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="stats-info">
                                <h3>Correct Answers</h3>
                                <h2><?php echo $total_correct_answers; ?></h2>
                                <p class="stats-detail">
                                    Platform-wide
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="bi bi-lightning-fill"></i>
                            </div>
                            <div class="stats-info">
                                <h3>Average Completion</h3>
                                <h2><?php 
                                    echo $total_users > 0 ? 
                                        round($total_completed_courses / $total_users, 1) : 
                                        '0';
                                ?></h2>
                                <p class="stats-detail">
                                    Courses per user
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Progress Table -->
                <div class="progress-card">
                    <div class="card-header">
                        <div class="search-wrapper">
                            <i class="bi bi-search"></i>
                            <input type="search" 
                                   class="search-input" 
                                   id="searchProgress" 
                                   placeholder="Search users..." 
                                   autocomplete="off">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Course Progress</th>
                                    <th>Completed</th>
                                    <th>Last Activity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): 
                                    $completion_rate = $row['total_enrolled_courses'] > 0 ? 
                                        ($row['completed_courses'] / $row['total_enrolled_courses']) * 100 : 0;
                                ?>
                                <tr>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <i class="bi bi-person"></i>
                                            </div>
                                            <div>
                                                <div class="user-name">
                                                    <?php echo htmlspecialchars($row['full_name']); ?>
                                                </div>
                                                <div class="user-username">
                                                    @<?php echo htmlspecialchars($row['username']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?php echo round($completion_rate); ?>%">
                                                <?php echo round($completion_rate); ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="completed-courses">
                                            <?php echo $row['completed_courses']; ?> / <?php echo $row['total_enrolled_courses']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="last-activity">
                                            <?php 
                                                if ($row['last_activity']) {
                                                    echo date('M j, Y g:i A', strtotime($row['last_activity']));
                                                } else {
                                                    echo 'No activity';
                                                }
                                            ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchProgress');
            const tableRows = document.querySelectorAll('tbody tr');
            
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                tableRows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        });
    </script>
</body>
</html>

<style>
.progress-header {
    background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 6px rgba(109, 40, 217, 0.1);
}

.page-title {
    font-size: 2rem;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.page-title i {
    font-size: 1.75rem;
}

.text-white {
    color: #fff !important;
}

.stats-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    height: 100%;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.stats-icon {
    width: 48px;
    height: 48px;
    background: rgba(99, 102, 241, 0.1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
}

.stats-icon i {
    font-size: 24px;
    color: #6366f1;
}

.stats-info h3 {
    font-size: 0.875rem;
    font-weight: 500;
    color: #6b7280;
    margin-bottom: 0.5rem;
}

.stats-info h2 {
    font-size: 2rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.25rem;
}

.stats-detail {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
}

.progress-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.card-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.search-wrapper {
    position: relative;
    max-width: 300px;
}

.search-wrapper i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #6b7280;
}

.search-input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    border: 1px solid #e5e7eb;
    border-radius: 9999px;
    font-size: 0.875rem;
    color: #111827;
    transition: all 0.2s;
}

.search-input:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.table {
    margin: 0;
}

.table th {
    padding: 1rem 1.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #6b7280;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}

.table td {
    padding: 1rem 1.5rem;
    vertical-align: middle;
    border-bottom: 1px solid #e5e7eb;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.user-avatar {
    width: 40px;
    height: 40px;
    background: rgba(99, 102, 241, 0.1);
    border-radius: 9999px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.user-avatar i {
    font-size: 1.25rem;
    color: #6366f1;
}

.user-name {
    font-weight: 500;
    color: #111827;
}

.user-username {
    font-size: 0.875rem;
    color: #6b7280;
}

.progress {
    height: 0.5rem;
    border-radius: 9999px;
    background: #f3f4f6;
}

.progress-bar {
    background: #6366f1;
    border-radius: 9999px;
}

.completed-courses {
    font-weight: 500;
    color: #059669;
}

.last-activity {
    font-size: 0.875rem;
    color: #6b7280;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-sm {
    padding: 0.375rem;
    font-size: 0.875rem;
}

.btn-outline-primary {
    color: #6366f1;
    border-color: #6366f1;
}

.btn-outline-primary:hover {
    background: #6366f1;
    color: white;
}

.btn-outline-secondary {
    color: #6b7280;
    border-color: #e5e7eb;
}

.btn-outline-secondary:hover {
    background: #f3f4f6;
    color: #374151;
    border-color: #9ca3af;
}
</style>
