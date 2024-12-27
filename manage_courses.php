<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_course'])) {
        $title = $conn->real_escape_string($_POST['title']);
        $description = $conn->real_escape_string($_POST['description']);
        $created_by = $_SESSION['user_id'];
        
        $sql = "INSERT INTO courses (title, description, created_by) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $title, $description, $created_by);
        $stmt->execute();
    }

    // Add quiz creation functionality
    if (isset($_POST['add_quiz'])) {
        $course_id = $conn->real_escape_string($_POST['course_id']);
        $quiz_title = $conn->real_escape_string($_POST['quiz_title']);
        
        $sql = "INSERT INTO quizzes (course_id, title) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $course_id, $quiz_title);
        $stmt->execute();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - Manage Courses</title>
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
                <!-- Stats Cards -->
                <div class="row admin-stats mb-4">
                    <div class="col-md-4">
                        <div class="stats-card dashboard-card">
                            <div class="card-body text-center">
                                <i class="bi bi-book-fill stats-icon"></i>
                                <h3>Total Courses</h3>
                                <?php
                                $result = $conn->query("SELECT COUNT(*) as count FROM courses");
                                $courses_count = $result->fetch_assoc()['count'];
                                ?>
                                <h2><?php echo $courses_count; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card dashboard-card">
                            <div class="card-body text-center">
                                <i class="bi bi-question-circle-fill stats-icon"></i>
                                <h3>Total Quizzes</h3>
                                <?php
                                $result = $conn->query("SELECT COUNT(*) as count FROM quizzes");
                                $quizzes_count = $result->fetch_assoc()['count'];
                                ?>
                                <h2><?php echo $quizzes_count; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Course Management Section -->
                <div class="admin-card mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="mb-0"><i class="bi bi-book"></i> Manage Courses</h2>
                        <button class="btn btn-primary action-button" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                            <i class="bi bi-plus-circle"></i> Add New Course
                        </button>
                    </div>

                    <?php 
                    $search_placeholder = "Search courses...";
                    include 'includes/search_bar.php'; 
                    ?>

                    <!-- Alert Messages -->
                    <?php if (isset($_GET['error']) || isset($_GET['success'])): ?>
                        <div class="alert alert-<?php echo isset($_GET['error']) ? 'danger' : 'success'; ?> alert-dismissible fade show">
                            <i class="bi bi-<?php echo isset($_GET['error']) ? 'exclamation-triangle' : 'check-circle'; ?>-fill me-2"></i>
                            <?php
                            if (isset($_GET['error'])) {
                                echo $_GET['error'] == 'no_quiz' ? 'You need to create a quiz before adding questions.' : 'Failed to delete course.';
                            } else {
                                echo $_GET['success'] == 'updated' ? 'Course updated successfully!' : 'Course deleted successfully!';
                            }
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Courses Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Quizzes</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $search = $_GET['search'] ?? '';
                                $query = "SELECT c.*, COUNT(q.id) as quiz_count, 
                                        u.full_name as created_by_name 
                                        FROM courses c 
                                        LEFT JOIN quizzes q ON c.id = q.course_id 
                                        LEFT JOIN users u ON c.created_by = u.id
                                        WHERE c.title LIKE ? OR c.description LIKE ?
                                        GROUP BY c.id 
                                        ORDER BY c.created_at DESC";
                                $search_param = "%$search%";
                                $stmt = $conn->prepare($query);
                                $stmt->bind_param("ss", $search_param, $search_param);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="align-middle"><?php echo htmlspecialchars($row['title']); ?></td>
                                        <td class="align-middle"><?php echo htmlspecialchars($row['description']); ?></td>
                                        <td class="align-middle">
                                            <span class="badge bg-info">
                                                <?php echo $row['quiz_count']; ?> Quizzes
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="print_course.php?id=<?php echo $row['id']; ?>" 
                                               class="btn btn-sm btn-outline-secondary me-2" 
                                               title="Print Progress Report">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-success me-2" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#addQuizModal" 
                                                    data-course-id="<?php echo $row['id']; ?>">
                                                <i class="bi bi-plus-circle"></i> Quiz
                                            </button>
                                            <a href="manage_quiz.php?course_id=<?php echo $row['id']; ?>" 
                                               class="btn btn-sm btn-outline-info me-2">
                                                <i class="bi bi-pencil-square"></i> Quizzes
                                            </a>
                                            <a href="edit_course.php?id=<?php echo $row['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary me-2">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteCourse(<?php echo $row['id']; ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
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

    <!-- Add Course Modal -->
    <div class="modal fade" id="addCourseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Course Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_course" class="btn btn-primary">Add Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Quiz Modal -->
    <div class="modal fade" id="addQuizModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Quiz</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="course_id" id="courseIdInput">
                        <div class="mb-3">
                            <label for="quiz_title" class="form-label">Quiz Title</label>
                            <input type="text" class="form-control" id="quiz_title" name="quiz_title" required minlength="3" placeholder="Enter quiz title">
                            <div class="form-text">Quiz title is required before adding questions.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_quiz" class="btn btn-primary">Create Quiz</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.querySelectorAll('[data-bs-target="#addQuizModal"]').forEach(button => {
        button.addEventListener('click', () => {
            document.getElementById('courseIdInput').value = button.dataset.courseId;
        });
    });

    function deleteCourse(id) {
        if (confirm('Are you sure you want to delete this course? This will also delete all related quizzes and progress data.')) {
            window.location.href = 'delete_course.php?id=' + id;
        }
    }
    </script>
</body>
</html>
