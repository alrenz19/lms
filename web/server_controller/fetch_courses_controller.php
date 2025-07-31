<?php
session_start();
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$query = "
    SELECT 
        c.id, 
        c.title, 
        c.description, 
        c.created_at,
        COUNT(DISTINCT up.user_id) as enrollment_count,
        COUNT(DISTINCT q.id) as quiz_count
    FROM courses c
    LEFT JOIN quizzes q ON c.id = q.course_id AND q.removed = 0
    LEFT JOIN user_progress up ON q.id = up.quiz_id AND up.removed = 0
    WHERE c.removed = 0
    GROUP BY c.id, c.title, c.description, c.created_at
    ORDER BY c.created_at DESC
";

$result = $conn->query($query);

ob_start();
if ($result->num_rows === 0) {
    ?>
    <div class="col-span-full flex flex-col items-center justify-center p-12 text-center">
        <i data-lucide="book-x" class="h-16 w-16 text-gray-300 mb-4"></i>
        <h3 class="text-xl font-medium text-gray-900 mb-2">No courses available</h3>
        <p class="text-gray-500 mb-6 max-w-md">Start creating courses by clicking the "Add New Course" button above</p>
        <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center shadow-sm" onclick="showModal('addCourseModal')">
            <i data-lucide="plus-circle" class="w-5 h-5 mr-2"></i>
            Create Your First Course
        </button>
    </div>
    <?php
} else {
    while ($course = $result->fetch_assoc()) {
        // you can move this to a partial if preferred
        ?>
        <div class="course-card bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300"
            data-id="<?php echo $course['id']; ?>"
            data-title="<?php echo strtolower(htmlspecialchars($course['title'])); ?>">

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
                        <span class="text-xs text-gray-500 flex items-center">
                            <i data-lucide="calendar" class="w-3 h-3 mr-1"></i>
                            Created: <?php echo (new DateTime($course['created_at']))->format('M j, Y'); ?>
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
        <?php
    }
}

$html = ob_get_clean();

echo json_encode([
    'success' => true,
    'html' => $html
]);
