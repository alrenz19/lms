<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Update the main query to remove avg_progress
$query = "
    SELECT 
        u.id as user_id,
        u.username,
        u.full_name,
        u.email,
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
        MAX(up.updated_at) as last_completion
    FROM users u
    LEFT JOIN user_progress up ON u.id = up.user_id
    WHERE u.role = 'user'
    GROUP BY u.id
    ORDER BY u.full_name";

$result = $conn->query($query);

// Add this query to get total courses
$courses_query = "SELECT COUNT(*) as total FROM courses";
$courses_result = $conn->query($courses_query);
$total_courses = $courses_result->fetch_assoc()['total'];

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
                                    <h2><?php echo $result->num_rows; ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stats-card dashboard-card">
                                <div class="card-body text-center">
                                    <i class="bi bi-book-fill stats-icon"></i>
                                    <h3>Total Courses</h3>
                                    <h2><?php echo $total_courses; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Table -->
                    <div class="table-responsive mt-4">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Completed Courses</th>
                                    <th>Last Completion</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($row['full_name']); ?></strong>
                                            <small class="text-muted d-block">@<?php echo htmlspecialchars($row['username']); ?></small>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo $row['completed_courses']; ?></td>
                                    <td><?php 
                                        if ($row['last_completion']) {
                                            echo date('M j, Y g:i A', strtotime($row['last_completion']));
                                        } else {
                                            echo 'No activity';
                                        }
                                    ?></td>
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
</body>
</html>
