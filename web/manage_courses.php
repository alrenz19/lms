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
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="content">
            <div class="container mt-4">
                <!-- Stats Cards -->
                <div class="row mb-4">
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
                                    <h2><?php echo $courses_count; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="admin-card">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon me-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                    </svg>
                                </div>
                                <div>
                                    <h3>Total Quizzes</h3>
                                    <h2><?php echo $quizzes_count; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Course Management Section -->
                <div class="admin-card">
                    <div class="page-header">
                        <h1 class="page-title">
                            <i class="bi bi-book"></i>
                            Manage Courses
                        </h1>
                        <div class="header-actions">
                            <div class="search-wrapper">
                                <i class="bi bi-search search-icon"></i>
                                <input type="search" 
                                       class="search-box" 
                                       id="searchCourses" 
                                       placeholder="Search courses by title..." 
                                       required>
                                <button type="button" class="clear-search" onclick="clearSearch()">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                                <i class="bi bi-plus-lg"></i>
                                Add New Course
                            </button>
                        </div>
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
                    <h5 class="modal-title" style="color: #212529;">Add New Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="title" class="form-label" style="color: #212529;">Course Title</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="title" 
                                   name="title" 
                                   required
                                   style="background-color: #ffffff;
                                          border: 1px solid #ced4da;
                                          color: #212529;
                                          padding: 10px 15px;
                                          box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label" style="color: #212529;">Description</label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="3" 
                                      required
                                      style="background-color: #ffffff;
                                             border: 1px solid #ced4da;
                                             color: #212529;
                                             padding: 10px 15px;
                                             box-shadow: 0 1px 3px rgba(0,0,0,0.1);"></textarea>
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

    <style>
    /* Course Icon Styles */
    .course-icon {
        width: 40px;
        height: 40px;
        background-color: rgba(99, 102, 241, 0.1);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        color: #6366f1;
    }

    /* Quiz Count Badge */
    .badge.bg-primary {
        background-color: #6366f1 !important;
        font-weight: 500;
        font-size: 0.75rem;
        padding: 0.5em 0.75em;
    }

    /* Action Buttons */
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }

    .btn-outline-secondary { color: #6b7280; border-color: #6b7280; }
    .btn-outline-success { color: #10b981; border-color: #10b981; }
    .btn-outline-info { color: #3b82f6; border-color: #3b82f6; }
    .btn-outline-primary { color: #6366f1; border-color: #6366f1; }
    .btn-outline-danger { color: #ef4444; border-color: #ef4444; }

    .btn-outline-secondary:hover { background-color: #6b7280; border-color: #6b7280; }
    .btn-outline-success:hover { background-color: #10b981; border-color: #10b981; }
    .btn-outline-info:hover { background-color: #3b82f6; border-color: #3b82f6; }
    .btn-outline-primary:hover { background-color: #6366f1; border-color: #6366f1; }
    .btn-outline-danger:hover { background-color: #ef4444; border-color: #ef4444; }

    /* Alert Styles */
    .alert {
        border: none;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .alert-success {
        background-color: #ecfdf5;
        color: #047857;
    }

    .alert-danger {
        background-color: #fef2f2;
        color: #b91c1c;
    }

    /* Modal Styles */
    .modal-content {
        border: none;
        border-radius: 8px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    .modal-header {
        border-bottom: 1px solid #e9ecef;
        padding: 1.25rem 1.5rem;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        border-top: 1px solid #e9ecef;
        padding: 1.25rem 1.5rem;
    }

    .form-label {
        font-weight: 500;
        margin-bottom: 0.5rem;
        color: #374151;
    }

    .form-control {
        border-radius: 4px;
        border: 1px solid #dee2e6;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }

    .form-control:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.25);
    }

    .form-text {
        font-size: 0.75rem;
        color: #6b7280;
    }
    </style>
</body>
</html>
