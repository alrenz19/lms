<?php
session_start();
require_once '../config.php';
require_once 'reusable_source_code/get_course_sql_query.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$is_admin = ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'super_admin');
$user_id = $_SESSION['user_id'];

// Add these queries for admin dashboard statistics
if ($is_admin) {
    // Get total users count
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role IN ('user', 'admin')");
    $users_count = $result->fetch_assoc()['count'];

    // Get total courses count
    $result = $conn->query("SELECT COUNT(*) as count FROM courses WHERE removed = 0");
    $courses_count = $result->fetch_assoc()['count'];
    
    // Get admin and student counts
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
    $admin_count = $result->fetch_assoc()['count'];
    $student_count = $users_count;
    
    // Get quiz count and questions count
    $result = $conn->query("
        SELECT COUNT(DISTINCT course_id) AS count 
        FROM questions 
        WHERE removed = 0
    ");
    $quizzes_count = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM questions");
    $questions_count = $result->fetch_assoc()['count'];
    
    // Get course completions
    $result = $conn->query("SELECT COUNT(*) as count FROM user_progress WHERE score = 100");
    $course_completions = $result->fetch_assoc()['count'];
    
    // Get average score
    $result = $conn->query("SELECT AVG(score) as avg_score FROM user_progress WHERE score > 0");
    $avg_row = $result->fetch_assoc();
    
    // Get recent activity
    $activity_query = "
        SELECT 
            u.username,
            u.full_name,
            'quiz_completed' AS activity_type,
            c.title AS course_title,
            up.updated_at AS timestamp
        FROM user_progress up
        JOIN users u ON u.id = up.user_id
        JOIN courses c ON c.id = up.course_id
        WHERE up.completed = 1 AND up.removed = 0
        ORDER BY up.updated_at DESC
        LIMIT 10;

    ";
    $recent_activity = $conn->query($activity_query);
}

// Calculate overall progress across all courses

$stmt = $conn->prepare($course_query);
$stmt->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$courses_result = $stmt->get_result();

$courses = [];
$total_progress = 0;
$total_score_percentage = 0;
$completed_courses = 0;
$courses_with_score = 0;


while ($course = $courses_result->fetch_assoc()) {
    $courses[] = $course;

    $total_progress += $course['course_progress'];

    if ($course['course_progress'] == 100) {
        $completed_courses++;
    }

    // Add only if there is a total_score > 0 to avoid division by zero
    if ($course['total_questions'] > 0) {
        $total_score_percentage += $course['user_score'];
        $courses_with_score++;
    }
}


// Overall progress = avg progress across all courses
$course_count = count($courses);
$overall_progress = $course_count > 0 ? $total_progress / $course_count : 0;

// ✅ Average score = avg % score across all scored courses
$average_score = $courses_with_score > 0 ? $total_score_percentage / $courses_with_score : 0;

// Optional: round final results
$overall_progress = round($overall_progress, 2);
$average_score = round($average_score, 2);


// For student dashboard (non-admin users)
if (!$is_admin) {
    // Get enrolled courses count
    $result = $conn->query("SELECT COUNT(DISTINCT q.course_id) as count FROM questions q JOIN user_progress up ON q.course_id = up.course_id WHERE up.user_id = $user_id AND up.removed = 0");
    $enrolled_courses = count($courses);
    
    // Get completed quizzes count
    $result = $conn->query("SELECT COUNT(*) as count FROM user_progress WHERE user_id = $user_id AND removed = 0");
    $completed_quizzes = $result->fetch_assoc()['count'];
    
    // Calculate course progress and quiz completion percentages
    // Fetch quiz data: total score obtained and possible
    $result = $conn->query("SELECT 
        (SELECT SUM(score) FROM user_progress WHERE user_id = $user_id AND removed = 0) AS score_obtained,
        (SELECT COUNT(*) FROM questions WHERE removed = 0) AS total_quizzes,
        (SELECT SUM(total_score) FROM user_progress WHERE user_id = $user_id AND removed = 0) AS quiz_taken
    ");
    $quiz_data = $result->fetch_assoc();

    // ✅ Fetch module progress only for assigned courses
    $result = $conn->query("
        SELECT 
            (
                SELECT COUNT(*) 
                FROM user_video_progress uvp
                INNER JOIN course_videos cv ON uvp.video_id = cv.id
                INNER JOIN user_courses uc ON cv.course_id = uc.course_id
                WHERE uvp.user_id = $user_id
                AND uc.user_id = $user_id
                AND (uc.removed = 0 OR uc.removed IS NULL)
                AND (cv.removed = 0 OR cv.removed IS NULL)
                AND (uvp.removed = 0 OR uvp.removed IS NULL)
            ) AS completed_course,

            (
                SELECT COUNT(*) 
                FROM course_videos cv
                INNER JOIN user_courses uc ON cv.course_id = uc.course_id
                WHERE uc.user_id = $user_id
                AND (uc.removed = 0 OR uc.removed IS NULL)
                AND (cv.removed = 0 OR cv.removed IS NULL)
            ) AS total_module
    ");
    $progress_data = $result->fetch_assoc();


    // Sanitize and prevent division by zero
    $score_obtained = isset($quiz_data['quiz_taken']) ? (int)$quiz_data['quiz_taken'] : 0;
    $score_possible = isset($quiz_data['total_quizzes']) && $quiz_data['total_quizzes'] > 0 
        ? (int)$quiz_data['total_quizzes'] 
        : 1;

    $completed_course = isset($progress_data['completed_course']) ? (int)$progress_data['completed_course'] : 0;
    $total_modules = isset($progress_data['total_module']) && $progress_data['total_module'] > 0 
        ? (int)$progress_data['total_module'] 
        : 1;

    // Final computed percentages
    $course_progress = round(($completed_course / $total_modules) * 100);
    $quiz_completion = round(($score_obtained / $score_possible) * 100);
}

// Include the header component
include 'includes/header.php';

// Include the dashboard card component
include_once 'components/dashboard_card.php';
?>

<div class="p-8 sm:ml-72">
    <div class="container mx-auto">
        <!-- Page Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl p-6 mb-6 shadow-sm">
            <div class="flex items-center">
                <div class="bg-white/10 p-3 rounded-lg mr-4">
                    <i data-lucide="layout-dashboard" class="h-8 w-8 text-white"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">Dashboard</h1>

                    <p class="text-blue-100"><?php echo $is_admin ? 'Admin Overview' : 'Student Overview'; ?></p>
                </div>
            </div>
        </div>

        <?php if ($is_admin): ?>
        <!-- Admin Dashboard -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-gray-700 font-medium">Total Users</h3>
                    <div class="bg-blue-100 text-blue-800 p-2 rounded-lg">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo $users_count; ?></div>
            </div>
            
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
                    <h3 class="text-gray-700 font-medium">Total Quizzes</h3>
                    <div class="bg-blue-100 text-blue-800 p-2 rounded-lg">
                        <i data-lucide="help-circle" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo $quizzes_count; ?></div>
            </div>

        </div>

        <!-- Quick Links Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i data-lucide="lightning-bolt" class="w-5 h-5 text-blue-600 mr-2"></i>
                    Quick Actions
                </h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="manage_users.php" class="bg-blue-50 hover:bg-blue-100 p-4 rounded-xl flex items-center transition-colors">
                    <div class="bg-blue-100 text-blue-800 p-3 rounded-lg mr-4">
                        <i data-lucide="users" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900">Manage Users</h3>
                        <p class="text-sm text-gray-600">Add, edit or remove users</p>
                    </div>
                </a>
                <a href="manage_courses.php" class="bg-green-50 hover:bg-green-100 p-4 rounded-xl flex items-center transition-colors">
                    <div class="bg-green-100 text-green-800 p-3 rounded-lg mr-4">
                        <i data-lucide="book-open" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900">Manage Courses</h3>
                        <p class="text-sm text-gray-600">Create and edit courses</p>
                    </div>
                </a>
                <a href="admin_user_progress.php" class="bg-amber-50 hover:bg-amber-100 p-4 rounded-xl flex items-center transition-colors">
                    <div class="bg-amber-100 text-amber-800 p-3 rounded-lg mr-4">
                        <i data-lucide="activity" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900">User Progress</h3>
                        <p class="text-sm text-gray-600">Track student performance</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i data-lucide="history" class="w-5 h-5 text-blue-600 mr-2"></i>
                    Recent Activity
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date/Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if ($recent_activity->num_rows === 0): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <i data-lucide="activity-square" class="h-12 w-12 text-gray-300 mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-900 mb-1">No recent activity</h3>
                                    <p class="text-gray-500">Student activity will appear here</p>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php while ($activity = $recent_activity->fetch_assoc()): ?>
                            <tr class="hover:bg-blue-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 flex-shrink-0 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                            <i data-lucide="user" class="w-4 h-4 text-blue-600"></i>
                                        </div>
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($activity['username']); ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 flex-shrink-0 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                            <i data-lucide="user" class="w-4 h-4 text-blue-600"></i>
                                        </div>
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($activity['full_name']); ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        <?php 
                                        if ($activity['activity_type'] === 'quiz_completed') {
                                            echo 'bg-green-100 text-green-800';
                                        } elseif ($activity['activity_type'] === 'course_started') {
                                            echo 'bg-blue-100 text-blue-800';
                                        } elseif ($activity['activity_type'] === 'course_completed') {
                                            echo 'bg-blue-100 text-blue-800';
                                        } else {
                                            echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <?php 
                                        if ($activity['activity_type'] === 'quiz_completed') {
                                            echo 'Completed Quiz';
                                        } elseif ($activity['activity_type'] === 'course_started') {
                                            echo 'Started Course';
                                        } elseif ($activity['activity_type'] === 'course_completed') {
                                            echo 'Completed Course';
                                        } else {
                                            echo htmlspecialchars($activity['activity_type']);
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($activity['course_title']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php 
                                        $activity_date = new DateTime($activity['timestamp']);
                                        $now = new DateTime();
                                        $interval = $activity_date->diff($now);
                                        
                                        if ($interval->d == 0) {
                                            if ($interval->h == 0) {
                                                if ($interval->i == 0) {
                                                    echo "Just now";
                                                } else {
                                                    echo $interval->i . " min ago";
                                                }
                                            } else {
                                                echo $interval->h . " hours ago";
                                            }
                                        } else if ($interval->d == 1) {
                                            echo "Yesterday";
                                        } else {
                                            echo $activity_date->format('M j, Y');
                                        }
                                    ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php else: ?>
        <!-- Student Dashboard -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-gray-700 font-medium">My Courses</h3>
                    <div class="bg-blue-100 text-blue-800 p-2 rounded-lg">
                        <i data-lucide="book-open" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo $enrolled_courses; ?></div>
                <div class="text-sm text-gray-500 flex items-center">
                    <i data-lucide="book" class="w-4 h-4 mr-1 text-blue-500"></i>
                    <span>Enrolled courses</span>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-gray-700 font-medium">Completed</h3>
                    <div class="bg-green-100 text-green-800 p-2 rounded-lg">
                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo $completed_courses; ?></div>
                <div class="text-sm text-gray-500 flex items-center">
                    <i data-lucide="award" class="w-4 h-4 mr-1 text-green-500"></i>
                    <span>Completed courses</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-gray-700 font-medium">Quizzes Taken</h3>
                    <div class="bg-amber-100 text-amber-800 p-2 rounded-lg">
                        <i data-lucide="help-circle" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo $completed_quizzes; ?></div>
                <div class="text-sm text-gray-500 flex items-center">
                    <i data-lucide="clipboard-list" class="w-4 h-4 mr-1 text-amber-500"></i>
                    <span>Quizzes completed</span>
                </div>
            </div>
        </div>

        <!-- Overall Progress -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i data-lucide="trending-up" class="w-5 h-5 text-blue-600 mr-2"></i>
                    Your Progress
                </h2>
            </div>
            <div class="p-6">
                <!-- Progress by Course Type -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-sm font-medium text-gray-700">Module Progress</h3>
                            <span class="text-sm font-medium text-blue-600"><?php echo $course_progress; ?>%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo $course_progress; ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-sm font-medium text-gray-700">Quiz Completion</h3>
                            <span class="text-sm font-medium text-green-600"><?php echo $quiz_completion; ?>%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: <?php echo $quiz_completion; ?>%"></div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-center mt-6">
                    <a href="user_progress.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i data-lucide="bar-chart-2" class="w-4 h-4 mr-2"></i>
                        View Detailed Progress
                    </a>
                </div>
            </div>
        </div>

        <!-- My Courses -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i data-lucide="book-open" class="w-5 h-5 text-blue-600 mr-2"></i>
                    My Courses
                </h2>
                <a href="view_courses.php" class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                    <span>View All</span>
                    <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
                </a>
            </div>

            <div class="p-6">
                <?php if ($enrolled_courses == 0): ?>
                <div class="flex flex-col items-center justify-center py-8 text-center">
                    <i data-lucide="book-x" class="h-16 w-16 text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No courses enrolled yet</h3>
                    <p class="text-gray-500 mb-6">Start your learning journey by exploring available courses</p>
                    <a href="view_courses.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                        <i data-lucide="search" class="w-4 h-4 mr-2"></i>
                        Browse Courses
                    </a>
                </div>
                <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($courses as $index => $course):
                        $course_progress = $course['course_progress'];
                    ?>
                    <div class="border border-gray-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300">
                        <div class="h-32 bg-gradient-to-r from-blue-600 to-blue-800 p-4 flex items-end">
                            <h3 class="text-lg font-semibold text-white"><?php echo htmlspecialchars($course['course_title']); ?></h3>
                        </div>
                        <div class="p-4">
                            <div class="mb-4">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-xs text-gray-500">Progress</span>
                                    <span class="text-xs font-medium text-blue-600"><?php echo round($course_progress); ?>%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: <?php echo round($course_progress); ?>%"></div>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between text-xs text-gray-500 mb-4">
                                <?php if ($course['total_questions'] != 0): ?>
                                <div class="flex items-center mr-3">
                                    <i data-lucide="help-circle" class="w-3 h-3 mr-1 text-blue-500"></i>
                                    <span>Score: <?php echo $course['user_score']; ?>/<?php echo $course['total_questions']; ?></span>
                                </div>
                                <?php else: ?>
                                <div class="flex items-center mr-3">
                                    <i data-lucide="help-circle" class="w-3 h-3 mr-1 text-blue-500"></i>
                                    <span>No Quiz</span>
                                </div>
                                <?php endif; ?>
                                <?php if ($course_progress == 100): ?>
                                    <span class="px-2 py-0.5 bg-green-100 text-green-800 rounded-full text-xs font-medium flex items-center">
                                        <i data-lucide="check" class="w-3 h-3 mr-1"></i>
                                        Completed
                                    </span>
                                <?php elseif ($course_progress == 0): ?>
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-800 rounded-full text-xs font-medium flex items-center">
                                        <i data-lucide="circle-dot-dashed" class="w-3 h-3 mr-1"></i>
                                        Not Started
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium flex items-center">
                                        <i data-lucide="circle-dot-dashed" class="w-3 h-3 mr-1"></i>
                                        In Progress
                                    </span>
                                <?php endif; ?>
                            </div>
                            <a href="view_course.php?id=<?php echo $course['course_id']; ?>" 
                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-100 text-blue-700 
                            rounded-lg hover:bg-blue-200 transition-colors text-sm font-medium">
                                <?php if ($course_progress == 0): ?>
                                <i data-lucide="play" class="w-4 h-4 mr-2"></i>
                                Start Course
                                <?php elseif ($course_progress == 100): ?>
                                <i data-lucide="rotate-ccw" class="w-4 h-4 mr-2"></i>
                                Review Course
                                <?php else: ?>
                                <i data-lucide="arrow-right" class="w-4 h-4 mr-2"></i>
                                Continue Course
                                <?php endif; ?>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide icons
    lucide.createIcons();
    
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
</body>
</html>
