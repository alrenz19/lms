<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Include the header component
include 'includes/header.php';

// Get all courses for the user
$query = "
    SELECT 
        c.id,
        c.title,
        c.description,
        COUNT(DISTINCT q.id) as quiz_count,
        COUNT(DISTINCT up.quiz_id) as completed_quizzes,
        COALESCE(AVG(up.score), 0) as average_score,
        COALESCE((COUNT(DISTINCT up.quiz_id) * 100.0 / NULLIF(COUNT(DISTINCT q.id), 0)), 0) as progress
    FROM courses c
    LEFT JOIN quizzes q ON c.id = q.course_id
    LEFT JOIN user_progress up ON q.id = up.quiz_id AND up.user_id = ?
    GROUP BY c.id, c.title, c.description
    ORDER BY c.title
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="p-8 sm:ml-72">
    <div class="container mx-auto">
        <!-- Page header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl p-6 mb-6 flex items-center">
            <i data-lucide="book-open" class="h-8 w-8 text-white mr-4"></i>
            <h1 class="text-2xl font-bold text-white">My Courses</h1>
        </div>
        
        <!-- Search bar and filters -->
        <div class="mb-6 flex flex-col sm:flex-row gap-4 items-center justify-between">
            <div class="relative w-full sm:w-64">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </span>
                <input type="search" 
                       id="searchCourses" 
                       class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Search courses...">
            </div>
            
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-600">Filter:</span>
                <button id="filterAll" class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded-md">All</button>
                <button id="filterInProgress" class="px-3 py-1.5 bg-gray-200 text-gray-700 text-sm rounded-md">In Progress</button>
                <button id="filterCompleted" class="px-3 py-1.5 bg-gray-200 text-gray-700 text-sm rounded-md">Completed</button>
            </div>
        </div>
        
        <?php if (empty($courses)): ?>
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-8 text-center">
            <i data-lucide="book-open" class="w-16 h-16 text-blue-400 mx-auto mb-4"></i>
            <h2 class="text-xl font-bold text-blue-800 mb-2">No courses available</h2>
            <p class="text-blue-600 mb-6">There are no courses available for you at the moment.</p>
        </div>
        <?php else: ?>
        
        <!-- Courses grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="coursesGrid">
            <?php foreach ($courses as $course): ?>
            <div class="course-card bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200 hover:shadow-md transition-shadow duration-200" 
                 data-progress="<?php echo round($course['progress']); ?>"
                 data-title="<?php echo htmlspecialchars($course['title']); ?>"
                 data-description="<?php echo htmlspecialchars($course['description']); ?>">
                <div class="h-2 bg-blue-600"></div>
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($course['title']); ?></h3>
                    <p class="text-sm text-gray-600 mb-4 line-clamp-2"><?php echo htmlspecialchars($course['description']); ?></p>
                    
                    <!-- Progress bar -->
                    <div class="mb-4">
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?php echo round($course['progress']); ?>%"></div>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-xs font-medium text-gray-500"><?php echo round($course['progress']); ?>% complete</span>
                            <span class="text-xs font-medium text-gray-500"><?php echo $course['completed_quizzes']; ?>/<?php echo $course['quiz_count']; ?> quizzes</span>
                        </div>
                    </div>
                    
                    <a href="view_course.php?id=<?php echo $course['id']; ?>" class="block w-full text-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors flex items-center justify-center">
                        <i data-lucide="<?php echo $course['progress'] == 100 ? 'check-circle' : 'play'; ?>" class="w-4 h-4 mr-2"></i>
                        <?php echo $course['progress'] == 100 ? 'Review Course' : 'Continue Learning'; ?>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide icons
    lucide.createIcons();
    
    // Search functionality
    const searchInput = document.getElementById('searchCourses');
    const courseCards = document.querySelectorAll('.course-card');
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        filterCourses();
    });
    
    // Filter buttons
    const filterAllBtn = document.getElementById('filterAll');
    const filterInProgressBtn = document.getElementById('filterInProgress');
    const filterCompletedBtn = document.getElementById('filterCompleted');
    
    let currentFilter = 'all';
    
    filterAllBtn.addEventListener('click', function() {
        setActiveFilter('all');
        filterCourses();
    });
    
    filterInProgressBtn.addEventListener('click', function() {
        setActiveFilter('in-progress');
        filterCourses();
    });
    
    filterCompletedBtn.addEventListener('click', function() {
        setActiveFilter('completed');
        filterCourses();
    });
    
    function setActiveFilter(filter) {
        currentFilter = filter;
        
        // Reset all filter buttons
        filterAllBtn.classList.remove('bg-blue-600', 'text-white');
        filterInProgressBtn.classList.remove('bg-blue-600', 'text-white');
        filterCompletedBtn.classList.remove('bg-blue-600', 'text-white');
        
        filterAllBtn.classList.add('bg-gray-200', 'text-gray-700');
        filterInProgressBtn.classList.add('bg-gray-200', 'text-gray-700');
        filterCompletedBtn.classList.add('bg-gray-200', 'text-gray-700');
        
        // Set active filter button
        if (filter === 'all') {
            filterAllBtn.classList.remove('bg-gray-200', 'text-gray-700');
            filterAllBtn.classList.add('bg-blue-600', 'text-white');
        } else if (filter === 'in-progress') {
            filterInProgressBtn.classList.remove('bg-gray-200', 'text-gray-700');
            filterInProgressBtn.classList.add('bg-blue-600', 'text-white');
        } else if (filter === 'completed') {
            filterCompletedBtn.classList.remove('bg-gray-200', 'text-gray-700');
            filterCompletedBtn.classList.add('bg-blue-600', 'text-white');
        }
    }
    
    function filterCourses() {
        const searchTerm = searchInput.value.toLowerCase();
        
        courseCards.forEach(card => {
            const title = card.getAttribute('data-title').toLowerCase();
            const description = card.getAttribute('data-description').toLowerCase();
            const progress = parseInt(card.getAttribute('data-progress'));
            
            const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
            let matchesFilter = true;
            
            if (currentFilter === 'in-progress') {
                matchesFilter = progress > 0 && progress < 100;
            } else if (currentFilter === 'completed') {
                matchesFilter = progress === 100;
            }
            
            card.style.display = matchesSearch && matchesFilter ? 'block' : 'none';
        });
        
        // Check if any courses are visible
        const visibleCourses = document.querySelectorAll('.course-card[style="display: block"]').length;
        const coursesGrid = document.getElementById('coursesGrid');
        
        if (visibleCourses === 0) {
            // Show "no courses found" message if none are visible
            if (!document.getElementById('noCoursesFound')) {
                const noCoursesMsg = document.createElement('div');
                noCoursesMsg.id = 'noCoursesFound';
                noCoursesMsg.className = 'col-span-full py-8 text-center';
                noCoursesMsg.innerHTML = `
                    <i data-lucide="search-x" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-700">No courses found</h3>
                    <p class="text-gray-500 mt-2">Try adjusting your search or filter criteria</p>
                `;
                coursesGrid.appendChild(noCoursesMsg);
                lucide.createIcons(); // Reinitialize icons for the new element
            }
        } else {
            // Remove "no courses found" message if courses are visible
            const noCoursesMsg = document.getElementById('noCoursesFound');
            if (noCoursesMsg) {
                noCoursesMsg.remove();
            }
        }
    }
});
</script> 