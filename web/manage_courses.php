<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Get total courses count
$result = $conn->query("SELECT COUNT(*) as count FROM courses");
$courses_count = $result->fetch_assoc()['count'];

// Get total quizzes count
$result = $conn->query("SELECT COUNT(*) as count FROM quizzes");
$quizzes_count = $result->fetch_assoc()['count'];

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
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
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
            color: #fff;
        }

        .page-title i {
            font-size: 1.75rem;
            color: #fff;
        }

        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            height: 100%;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
            transition: all 0.2s ease;
        }

        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1);
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

        .admin-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
        }

        .search-wrapper {
            position: relative;
            max-width: 300px;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
        }

        .search-box {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid #e5e7eb;
            border-radius: 9999px;
            font-size: 0.875rem;
            color: #111827;
            transition: all 0.2s;
        }

        .search-box:focus {
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

        .badge {
            padding: 0.5em 0.75em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge.bg-primary {
            background-color: #6366f1 !important;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .btn-outline-primary {
            color: #6366f1;
            border-color: #6366f1;
        }

        .btn-outline-primary:hover {
            background: #6366f1;
            color: white;
        }

        .btn-outline-danger {
            color: #ef4444;
            border-color: #ef4444;
        }

        .btn-outline-danger:hover {
            background: #ef4444;
            color: white;
        }

        .btn-outline-success {
            color: #10b981;
            border-color: #10b981;
        }

        .btn-outline-success:hover {
            background: #10b981;
            color: white;
        }

        .btn-outline-info {
            color: #3b82f6;
            border-color: #3b82f6;
        }

        .btn-outline-info:hover {
            background: #3b82f6;
            color: white;
        }

        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            border-radius: 12px 12px 0 0;
            padding: 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
            background: #ffffff;
        }

        .modal-footer {
            border-top: 1px solid #e5e7eb;
            padding: 1.5rem;
            border-radius: 0 0 12px 12px;
            background: #f9fafb;
        }

        .form-label {
            font-weight: 500;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            padding: 0.75rem;
            font-size: 0.875rem;
            color: #111827;
            background-color: #ffffff;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            background-color: #ffffff;
        }

        .alert {
            border: none;
            border-radius: 8px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background-color: #ecfdf5;
            color: #059669;
            border: 1px solid #d1fae5;
        }

        .alert-danger {
            background-color: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fee2e2;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        @media (max-width: 768px) {
            .header-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .search-wrapper {
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="content">
            <div class="container mt-4">
                <div class="progress-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h1 class="page-title">
                            <i class="bi bi-book"></i> Course Management
                        </h1>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="stats-card">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon me-3">
                                    <i class="bi bi-book"></i>
                                </div>
                                <div class="stats-info">
                                    <h3>Total Courses</h3>
                                    <h2><?php echo $courses_count; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="stats-card">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon me-3">
                                    <i class="bi bi-question-circle"></i>
                                </div>
                                <div class="stats-info">
                                    <h3>Total Quizzes</h3>
                                    <h2><?php echo $quizzes_count; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Course Management Section -->
                <div class="admin-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="search-wrapper">
                            <i class="bi bi-search search-icon"></i>
                            <input type="search" 
                                   class="search-box" 
                                   id="searchCourses" 
                                   placeholder="Search courses..." 
                                   required>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                            <i class="bi bi-plus-lg"></i> Add New Course
                        </button>
                    </div>

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
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Course Title</th>
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
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="stats-icon me-3">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($row['title']); ?></strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle"><?php echo htmlspecialchars($row['description']); ?></td>
                                        <td class="align-middle">
                                            <span class="badge bg-primary">
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
                                                <i class="bi bi-pencil"></i> Edit
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
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_course" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i> Add Course
                        </button>
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
                    <h5 class="modal-title" style="color: #212529;">Add New Quiz</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="course_id" id="courseIdInput">
                        <div class="mb-3">
                            <label for="quiz_title" class="form-label" style="color: #212529;">Quiz Title</label>
                            <input type="text" class="form-control" id="quiz_title" name="quiz_title" required 
                                   minlength="3" placeholder="Enter quiz title"
                                   style="background-color: white; color: #212529; border: 1px solid #dee2e6;">
                            <div class="form-text" style="color: #6c757d;">Quiz title is required before adding questions.</div>
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
    document.addEventListener('DOMContentLoaded', function() {
        // Real-time search functionality
        const searchInput = document.getElementById('searchCourses');
        const courseRows = document.querySelectorAll('tr[data-course-row]');
        
        function filterCourses(searchTerm) {
            searchTerm = searchTerm.toLowerCase();
            courseRows.forEach(row => {
                const title = row.querySelector('[data-course-title]').textContent.toLowerCase();
                const description = row.querySelector('[data-course-description]').textContent.toLowerCase();
                const matches = title.includes(searchTerm) || description.includes(searchTerm);
                row.style.display = matches ? '' : 'none';
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

        // Handle Add Quiz Modal
        const addQuizModal = document.getElementById('addQuizModal');
        if (addQuizModal) {
            addQuizModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const courseId = button.getAttribute('data-course-id');
                this.querySelector('#courseIdInput').value = courseId;
            });
        }

        // Form validation
        const addCourseForm = document.querySelector('form[name="addCourseForm"]');
        const addQuizForm = document.querySelector('form[name="addQuizForm"]');

        function validateCourseForm(form) {
            const title = form.querySelector('input[name="title"]');
            const description = form.querySelector('textarea[name="description"]');
            
            if (title.value.trim().length < 3) {
                alert('Course title must be at least 3 characters long');
                return false;
            }

            if (description.value.trim().length < 10) {
                alert('Course description must be at least 10 characters long');
                return false;
            }

            return true;
        }

        if (addCourseForm) {
            addCourseForm.addEventListener('submit', function(e) {
                if (!validateCourseForm(this)) {
                    e.preventDefault();
                }
            });
        }

        if (addQuizForm) {
            addQuizForm.addEventListener('submit', function(e) {
                const title = this.querySelector('input[name="quiz_title"]');
                if (title.value.trim().length < 3) {
                    alert('Quiz title must be at least 3 characters long');
                    e.preventDefault();
                }
            });
        }

        // Delete course confirmation
        window.deleteCourse = function(id) {
            if (confirm('Are you sure you want to delete this course? This action cannot be undone and will delete all related quizzes and progress data.')) {
                window.location.href = 'delete_course.php?id=' + id;
            }
        };

        // Show success/error messages
        const alertMessage = document.querySelector('.alert');
        if (alertMessage) {
            setTimeout(() => {
                alertMessage.classList.remove('show');
                setTimeout(() => alertMessage.remove(), 150);
            }, 3000);
        }
    });
    </script>
</body>
</html>
