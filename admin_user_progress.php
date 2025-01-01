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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 3v18h18"></path>
                                <path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"></path>
                            </svg>
                        </div>
                        <h2 class="mb-0">User Progress Overview</h2>
                    </div>
                    <div class="search-bar" style="width: 300px;">
                        <input type="text" class="form-control" placeholder="Search users or courses..." 
                               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                               onkeyup="updateSearch(this.value)">
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="admin-card">
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
                                    <h3>Active Students</h3>
                                    <h2><?php echo $result->num_rows; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="admin-card">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon me-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3>Total Courses</h3>
                                    <h2><?php echo $total_courses; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Progress Table -->
                <div class="admin-card">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Completed Courses</th>
                                    <th>Last Activity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="stats-icon me-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                    <circle cx="12" cy="7" r="4"></circle>
                                                </svg>
                                            </div>
                                            <div>
                                                <strong><?php echo htmlspecialchars($row['full_name']); ?></strong>
                                                <small class="text-muted d-block">@<?php echo htmlspecialchars($row['username']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle"><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td class="align-middle"><?php echo $row['completed_courses']; ?></td>
                                    <td class="align-middle"><?php 
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
