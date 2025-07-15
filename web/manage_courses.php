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

// Get total enrollments
$result = $conn->query("SELECT COUNT(*) as count FROM user_progress");
$total_enrollments = $result->fetch_assoc()['count'];

// Get course completions
$result = $conn->query("SELECT COUNT(*) as count FROM user_progress WHERE score = 100");
$course_completions = $result->fetch_assoc()['count'];

// Get courses for display
$courses_query = "
    SELECT 
        c.id, 
        c.title, 
        c.description, 
        c.created_at,
        COUNT(DISTINCT up.user_id) as enrollment_count,
        COUNT(DISTINCT q.id) as quiz_count
    FROM courses c
    LEFT JOIN quizzes q ON c.id = q.course_id
    LEFT JOIN user_progress up ON q.id = up.quiz_id
    GROUP BY c.id, c.title, c.description, c.created_at
    ORDER BY c.created_at DESC
";
$courses_result = $conn->query($courses_query);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_course'])) {
        $title = $conn->real_escape_string($_POST['title']);
        $description = $conn->real_escape_string($_POST['description']);
        $created_by = $_SESSION['user_id'];
        
        $sql = "INSERT INTO courses (title, description, created_by) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $title, $description, $created_by);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Course has been created successfully!";
        } else {
            $_SESSION['error'] = "Failed to create course: " . $conn->error;
        }
        header("Location: manage_courses.php");
        exit;
    }

    // Handle course deletion
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        if (isset($_POST['course_id'])) {
            $course_id = (int)$_POST['course_id'];
            
            // Begin transaction to ensure all related data is deleted properly
            $conn->begin_transaction();
            
            try {
                // First delete related user progress
                $sql = "DELETE up FROM user_progress up 
                        JOIN quizzes q ON up.quiz_id = q.id 
                        WHERE q.course_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $course_id);
                $stmt->execute();
                
                // Then delete related questions
                $sql = "DELETE qs FROM questions qs 
                        JOIN quizzes q ON qs.quiz_id = q.id 
                        WHERE q.course_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $course_id);
                $stmt->execute();
                
                // Delete quizzes
                $sql = "DELETE FROM quizzes WHERE course_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $course_id);
                $stmt->execute();
                
                // Finally delete the course
                $sql = "DELETE FROM courses WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $course_id);
                $stmt->execute();
                
                // Commit transaction
                $conn->commit();
                $_SESSION['success'] = "Course and all related content has been deleted successfully!";
            } catch (Exception $e) {
                // Roll back if there was an error
                $conn->rollback();
                $_SESSION['error'] = "Failed to delete course: " . $e->getMessage();
            }
            
            header("Location: manage_courses.php");
            exit;
        }
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
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Remove Bootstrap CSS and JavaScript as they're not used in manage_users.php -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'blue': {
                            50: '#f0f5ff',
                            100: '#e0eaff',
                            200: '#c7d7fe',
                            300: '#a5b9fc',
                            400: '#8193f7',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                            950: '#1e1b4b',
                        },
                        'green': {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857'
                        },
                        'red': {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#b91c1c'
                        },
                        'amber': {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309'
                        }
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-blue-50">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="p-8 sm:ml-72">
        <div class="container mx-auto">
            <!-- Page header - match exactly with manage_users.php -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl p-6 mb-6 flex justify-between items-center shadow-sm">
                <div class="flex items-center">
                    <div class="bg-white/10 p-3 rounded-lg mr-4">
                        <i data-lucide="book-open" class="h-8 w-8 text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">Course Management</h1>
                        <p class="text-blue-100">Create, edit and manage course content</p>
                    </div>
                </div>
                <button class="px-4 py-2.5 bg-white text-blue-600 rounded-lg flex items-center gap-2 transition-all duration-200 font-medium shadow-sm" onclick="showModal('addCourseModal')">
                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    <span>Add New Course</span>
                </button>
            </div>

            <!-- Error Messages -->
            <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-6 rounded-lg p-4 flex items-center gap-2 bg-red-50 text-red-700 border-l-4 border-red-500" style="display: none;">
                <i data-lucide="alert-circle" class="h-5 w-5 text-red-500"></i>
                <span><?php echo htmlspecialchars($_SESSION['error']); ?></span>
                <button type="button" class="ml-auto text-gray-400 hover:text-gray-600" onclick="this.parentElement.remove()">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </div>
            <?php endif; ?>

            <!-- Success Messages -->
            <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-6 rounded-lg p-4 flex items-center gap-2 bg-green-50 text-green-700 border-l-4 border-green-500" style="display: none;">
                <i data-lucide="check-circle" class="h-5 w-5 text-green-500"></i>
                <span><?php echo htmlspecialchars($_SESSION['success']); ?></span>
                <button type="button" class="ml-auto text-gray-400 hover:text-gray-600" onclick="this.parentElement.remove()">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-gray-700 font-medium">Total Courses</h3>
                        <div class="bg-green-100 text-green-800 p-2 rounded-lg">
                            <i data-lucide="book-open" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo $courses_count; ?></div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-gray-700 font-medium">Total Enrollments</h3>
                        <div class="bg-blue-100 text-blue-800 p-2 rounded-lg">
                            <i data-lucide="users" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo $total_enrollments; ?></div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-gray-700 font-medium">Quizzes Created</h3>
                        <div class="bg-blue-100 text-blue-800 p-2 rounded-lg">
                            <i data-lucide="help-circle" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo $quizzes_count; ?></div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-gray-700 font-medium">Course Completions</h3>
                        <div class="bg-indigo-100 text-indigo-800 p-2 rounded-lg">
                            <i data-lucide="check-circle" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo $course_completions; ?></div>
                </div>
            </div>

            <!-- Course Management -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
                <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i data-lucide="layout-grid" class="w-5 h-5 text-blue-600 mr-2"></i>
                        Course List
                    </h2>
                    <div class="relative w-full sm:w-auto">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </span>
                        <input type="search" 
                            id="courseSearch" 
                            class="w-full sm:w-64 pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            placeholder="Search courses..." 
                            autocomplete="off">
                    </div>
                </div>

                <!-- Course List -->
                <div class="p-6">
                    <div id="courseContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php if ($courses_result->num_rows === 0): ?>
                        <div class="col-span-full flex flex-col items-center justify-center p-12 text-center">
                            <i data-lucide="book-x" class="h-16 w-16 text-gray-300 mb-4"></i>
                            <h3 class="text-xl font-medium text-gray-900 mb-2">No courses available</h3>
                            <p class="text-gray-500 mb-6 max-w-md">Start creating courses by clicking the "Add New Course" button above</p>
                            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center shadow-sm" onclick="showModal('addCourseModal')">
                                <i data-lucide="plus-circle" class="w-5 h-5 mr-2"></i>
                                Create Your First Course
                            </button>
                        </div>
                        <?php else: ?>
                            <?php while ($course = $courses_result->fetch_assoc()): ?>
                            <div class="course-card bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300" data-title="<?php echo strtolower(htmlspecialchars($course['title'])); ?>">
                                <div class="h-48 bg-gradient-to-r from-blue-600 to-blue-800 p-6 flex items-end">
                                    <h3 class="text-xl font-semibold text-white"><?php echo htmlspecialchars($course['title']); ?></h3>
                                </div>
                                <div class="p-6">
                                    <p class="text-gray-600 text-sm mb-4 line-clamp-3"><?php echo htmlspecialchars($course['description']); ?></p>
                                    
                                    <div class="flex items-center text-sm text-gray-500 mb-4">
                                        <div class="flex items-center mr-4">
                                            <i data-lucide="users" class="w-4 h-4 mr-1 text-blue-500"></i>
                                            <span><?php echo $course['enrollment_count']; ?> enrolled</span>
                                        </div>
                                        <div class="flex items-center">
                                            <i data-lucide="clipboard-list" class="w-4 h-4 mr-1 text-amber-500"></i>
                                            <span><?php echo $course['quiz_count']; ?> quizzes</span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <?php 
                                            $course_date = new DateTime($course['created_at']);
                                            $course_date_str = $course_date->format('M j, Y');
                                            ?>
                                            <span class="text-xs text-gray-500 flex items-center">
                                                <i data-lucide="calendar" class="w-3 h-3 mr-1"></i>
                                                Created: <?php echo $course_date_str; ?>
                                            </span>
                                        </div>
                                        <div class="flex space-x-2">
                                            <a href="edit_course.php?id=<?php echo $course['id']; ?>" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                                <i data-lucide="edit-3" class="w-5 h-5"></i>
                                            </a>
                                            <button class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" onclick="confirmDeleteCourse(<?php echo $course['id']; ?>, '<?php echo htmlspecialchars(addslashes($course['title'])); ?>')">
                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- No search results message -->
                    <div id="noCoursesFound" class="hidden flex-col items-center justify-center p-12 text-center">
                        <i data-lucide="search-x" class="h-12 w-12 text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-700 mb-2">No courses found</h3>
                        <p class="text-gray-500 mb-4">Try adjusting your search term</p>
                        <button class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-sm font-medium" 
                                onclick="clearSearch()">
                            Clear Search
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Course Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center backdrop-blur-sm transition-all duration-300" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md transform transition-all duration-300">
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 p-5 rounded-t-xl flex items-center justify-between">
                <h5 class="text-lg font-semibold text-white flex items-center">
                    <i data-lucide="plus-circle" class="h-5 w-5 mr-2"></i>
                    Add New Course
                </h5>
                <button type="button" class="text-white/80 hover:text-white focus:outline-none" onclick="hideModal('addCourseModal')">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>
            <div class="p-6">
                <form id="addCourseForm" action="manage_courses.php" method="post" data-show-toast="true" data-toast-message="Course added successfully" data-toast-type="success" data-validate="true">
                    <input type="hidden" name="add_course" value="1">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-5">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Course Title</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                <i data-lucide="book-open" class="w-5 h-5"></i>
                            </div>
                            <input type="text" class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition" 
                                   id="title" name="title" required placeholder="Enter course title">
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Course Description</label>
                        <div class="relative">
                            <div class="absolute top-3 left-3 text-gray-400">
                                <i data-lucide="align-left" class="w-5 h-5"></i>
                            </div>
                            <textarea class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition" 
                                      id="description" name="description" rows="4" required placeholder="Enter course description"></textarea>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-8">
                        <button type="button" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition text-sm font-medium flex items-center gap-2" onclick="hideModal('addCourseModal')">
                            <i data-lucide="x" class="h-4 w-4"></i>
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition text-sm font-medium flex items-center gap-2">
                            <i data-lucide="save" class="h-4 w-4"></i>
                            Save Course
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Course Confirmation Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center backdrop-blur-sm transition-all duration-300" id="deleteCourseModal" tabindex="-1" aria-labelledby="deleteCourseModalLabel" aria-hidden="true">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md transform transition-all duration-300">
            <div class="bg-gradient-to-r from-red-600 to-red-800 p-5 rounded-t-xl flex items-center justify-between">
                <h5 class="text-lg font-semibold text-white flex items-center">
                    <i data-lucide="alert-triangle" class="h-5 w-5 mr-2"></i>
                    Confirm Deletion
                </h5>
                <button type="button" class="text-white/80 hover:text-white focus:outline-none" onclick="hideModal('deleteCourseModal')">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>
            <div class="p-6">
                <div class="bg-red-50 text-red-800 p-4 rounded-lg mb-4 flex items-start">
                    <i data-lucide="alert-circle" class="w-5 h-5 mr-2 flex-shrink-0 mt-0.5"></i>
                    <div>
                        <p class="font-medium">Warning</p>
                        <p class="text-sm">This action cannot be undone. All course content, quizzes, and student progress data will be permanently deleted.</p>
                    </div>
                </div>
                <p class="mb-2 text-gray-700">Are you sure you want to delete this course?</p>
                <p class="font-semibold mb-0" id="courseNameToDelete"></p>
                
                <form id="deleteCourseForm" action="manage_courses.php" method="post" class="mt-6" data-show-toast="true" data-toast-message="Course deleted successfully" data-toast-type="warning" data-validate="true">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="course_id" id="courseIdToDelete">
                    
                    <div class="flex justify-end space-x-3 mt-8">
                        <button type="button" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition text-sm font-medium flex items-center gap-2" onclick="hideModal('deleteCourseModal')">
                            <i data-lucide="x" class="h-4 w-4"></i>
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg transition text-sm font-medium flex items-center gap-2">
                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                            Delete Course
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Initialize toast notifications for session messages
        <?php if (isset($_SESSION['success'])): ?>
            showToast('<?php echo addslashes($_SESSION['success']); ?>', 'success');
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            showToast('<?php echo addslashes($_SESSION['error']); ?>', 'error');
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        // Search functionality
        const searchInput = document.getElementById('courseSearch');
        const courseCards = document.querySelectorAll('.course-card');
        const courseContainer = document.getElementById('courseContainer');
        const noCoursesFound = document.getElementById('noCoursesFound');
        
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            let hasResults = false;
            
            courseCards.forEach(card => {
                const title = card.getAttribute('data-title');
                
                if (title.includes(searchTerm)) {
                    card.classList.remove('hidden');
                    hasResults = true;
                } else {
                    card.classList.add('hidden');
                }
            });
            
            // Show/hide no results message
            if (!hasResults && searchTerm.length > 0) {
                noCoursesFound.classList.remove('hidden');
                noCoursesFound.classList.add('flex');
                // If there's a "no courses available" message, hide it during search
                const noCoursesAvailable = courseContainer.querySelector('[data-lucide="book-x"]')?.closest('.col-span-full');
                if (noCoursesAvailable) noCoursesAvailable.classList.add('hidden');
            } else {
                noCoursesFound.classList.remove('flex');
                noCoursesFound.classList.add('hidden');
                // Restore "no courses available" message if it exists and search is cleared
                if (searchTerm === '') {
                    const noCoursesAvailable = courseContainer.querySelector('[data-lucide="book-x"]')?.closest('.col-span-full');
                    if (noCoursesAvailable) noCoursesAvailable.classList.remove('hidden');
                }
            }
        });
        
        // Clear search function
        window.clearSearch = function() {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
            searchInput.focus();
        };
    });

    // Course deletion confirmation
    function confirmDeleteCourse(courseId, courseTitle) {
        document.getElementById('courseIdToDelete').value = courseId;
        document.getElementById('courseNameToDelete').textContent = courseTitle;
        
        // Show the modal with our enhanced function
        showModal('deleteCourseModal');
    }
    
    // Show modal function
    function showModal(modalId) {
        const modal = document.getElementById(modalId);
        document.body.classList.add('overflow-hidden');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Focus on first input after a short delay
        setTimeout(() => {
            const firstInput = modal.querySelector('input:not([type="hidden"])');
            if (firstInput) firstInput.focus();
        }, 50);
    }

    // Hide modal function
    function hideModal(modalId) {
        const modal = document.getElementById(modalId);
        document.body.classList.remove('overflow-hidden');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }

    // Toast notification system
    function showToast(message, type = 'info') {
        // Create toast container if it doesn't exist
        let toastContainer = document.getElementById('toast-container');
        
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'fixed bottom-4 right-4 z-50 flex flex-col gap-2';
            document.body.appendChild(toastContainer);
        }
        
        // Create toast element
        const toast = document.createElement('div');
        toast.className = 'transform transition-all duration-300 ease-in-out translate-x-full';
        
        // Set background color based on type
        let bgColor, textColor, iconName;
        switch (type) {
            case 'success':
                bgColor = 'bg-green-500';
                textColor = 'text-white';
                iconName = 'check-circle';
                break;
            case 'error':
                bgColor = 'bg-red-500';
                textColor = 'text-white';
                iconName = 'alert-circle';
                break;
            case 'warning':
                bgColor = 'bg-amber-500';
                textColor = 'text-white';
                iconName = 'alert-triangle';
                break;
            default: // info
                bgColor = 'bg-blue-500';
                textColor = 'text-white';
                iconName = 'info';
        }
        
        // Apply styles
        toast.className += ` ${bgColor} ${textColor} rounded-lg shadow-lg p-4 mb-2 flex items-center`;
        
        // Add content
        toast.innerHTML = `
            <i data-lucide="${iconName}" class="w-5 h-5 mr-2"></i>
            <span>${message}</span>
        `;
        
        // Add to container
        toastContainer.appendChild(toast);
        
        // Initialize icon
        if (typeof lucide !== 'undefined') {
            lucide.createIcons({
                attrs: {
                    class: ["stroke-current"]
                }
            });
        }
        
        // Animate in
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
            toast.classList.add('translate-x-0');
        }, 10);
        
        // Remove after 3 seconds
        setTimeout(() => {
            toast.classList.remove('translate-x-0');
            toast.classList.add('translate-x-full');
            
            // Remove from DOM after animation completes
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }
    </script>
</body>
</html>
