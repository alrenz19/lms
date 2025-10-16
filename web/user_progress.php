<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get detailed progress information
$stmt = $conn->prepare("
SELECT 
    c.id AS course_id,
    c.title AS course_title,
    c.description AS course_description,

    -- Total questions per course
    COALESCE(qs.total_questions, 0) AS total_questions,

    -- Completed status by user (0 or 1 only)
    COALESCE(uqs.has_completed_exam, 0) AS completed_questions,

    -- Total videos per course
    COALESCE(cv_data.total_videos, 0) AS total_videos,

    -- Watched videos by user
    COALESCE(uvp_data.watched_videos, 0) AS watched_videos,

    -- Final course progress logic
    CASE
        WHEN COALESCE(cv_data.total_videos, 0) = 0 AND COALESCE(qs.total_questions, 0) = 0 THEN 0
        WHEN COALESCE(qs.total_questions, 0) = 0 THEN
            ROUND(COALESCE(uvp_data.watched_videos, 0) * 100.0 / NULLIF(cv_data.total_videos, 0), 2)
        WHEN COALESCE(cv_data.total_videos, 0) = 0 THEN
            ROUND(COALESCE(uqs.has_completed_exam, 0) * 100.0, 2)
        ELSE
            ROUND((
                (COALESCE(uqs.has_completed_exam, 0) * 100.0) + 
                (COALESCE(uvp_data.watched_videos, 0) * 100.0 / NULLIF(cv_data.total_videos, 0))
            ) / 2, 2)
    END AS course_progress,

    -- Correct score fallback logic
    COALESCE(score_data.user_score, 0) AS user_score,

    -- Last activity
    COALESCE(uqs.last_activity, uvp_data.last_video_activity, c.created_at) AS last_activity

FROM user_courses uc
INNER JOIN courses c 
    ON uc.course_id = c.id
    AND uc.user_id = ?
    AND uc.removed = 0
    AND c.removed = 0

-- Total questions per course
LEFT JOIN (
    SELECT course_id, COUNT(*) AS total_questions
    FROM questions
    WHERE removed = 0
    GROUP BY course_id
) AS qs ON c.id = qs.course_id

-- Whether the user completed the exam
LEFT JOIN (
    SELECT course_id,
           MAX(CASE WHEN completed = 1 THEN 1 ELSE 0 END) AS has_completed_exam,
           MAX(updated_at) AS last_activity
    FROM user_progress
    WHERE user_id = ?
      AND removed = 0
    GROUP BY course_id
) AS uqs ON c.id = uqs.course_id

-- Sum of scores per course
LEFT JOIN (
    SELECT course_id, SUM(score) AS user_score
    FROM user_progress
    WHERE user_id = ?
      AND removed = 0
    GROUP BY course_id
) AS score_data ON c.id = score_data.course_id

-- Total videos per course
LEFT JOIN (
    SELECT course_id, COUNT(*) AS total_videos
    FROM course_videos
    WHERE removed = 0
    GROUP BY course_id
) AS cv_data ON c.id = cv_data.course_id

-- Watched videos per user
LEFT JOIN (
    SELECT cv.course_id,
           COUNT(*) AS watched_videos,
           MAX(uvp.updated_at) AS last_video_activity
    FROM course_videos cv
    INNER JOIN user_video_progress uvp ON uvp.video_id = cv.id
    WHERE uvp.user_id = ?
      AND uvp.watched = 1
      AND uvp.removed = 0
      AND cv.removed = 0
    GROUP BY cv.course_id
) AS uvp_data ON c.id = uvp_data.course_id

ORDER BY last_activity DESC, c.title ASC;
");


$stmt->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate overall statistics
$total_courses = count($courses);
$completed_courses = 0;
$total_correct_answers = 0;
$total_questions = 0;

foreach ($courses as $course) {
    if ($course['course_progress'] == 100) {
        $completed_courses++;
    }
    $total_correct_answers += $course['user_score'];
    $total_questions += $course['total_questions'];
}

$overall_progress = $total_courses > 0 ? ($completed_courses / $total_courses) * 100 : 0;
$overall_score = $total_questions > 0 ? ($total_correct_answers / $total_questions) * 100 : 0;

// Include the header component
include 'includes/header.php';
?>
    
<div class="p-8 sm:ml-72">
    <div class="container mx-auto">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl p-6 mb-6 flex items-center">
            <i data-lucide="activity" class="h-8 w-8 text-white mr-4"></i>
            <div>
                <h1 class="text-2xl font-bold text-white">My Learning Progress</h1>
                <p class="text-blue-100">Track your course completion and achievements</p>
            </div>
        </div>

        <!-- Overall Progress Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Course Progress -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-all">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center mr-4">
                        <i data-lucide="book-open" class="h-6 w-6 text-blue-600"></i>
                    </div>
                    <h3 class="text-sm font-medium text-gray-700">Course Progress</h3>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5 mb-3">
                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?php echo round($overall_progress); ?>%"></div>
                </div>
                <p class="text-sm text-gray-600 flex items-center gap-2">
                    <i data-lucide="check-circle" class="h-4 w-4 text-green-500"></i>
                    <span><?php echo $completed_courses; ?> of <?php echo $total_courses; ?> courses completed</span>
                </p>
            </div>
            
            <!-- Overall Score -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-all">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center mr-4">
                        <i data-lucide="award" class="h-6 w-6 text-green-600"></i>
                    </div>
                    <h3 class="text-sm font-medium text-gray-700">Overall Score</h3>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5 mb-3">
                    <div class="bg-green-500 h-2.5 rounded-full" style="width: <?php echo round($overall_score); ?>%"></div>
                </div>
                <p class="text-sm text-gray-600 flex items-center gap-2">
                    <i data-lucide="check" class="h-4 w-4 text-amber-500"></i>
                    <span><?php echo $total_correct_answers; ?> of <?php echo $total_questions; ?> questions correct</span>
                </p>
            </div>
            
            <!-- Active Learning -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-all">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 rounded-lg bg-amber-100 flex items-center justify-center mr-4">
                        <i data-lucide="zap" class="h-6 w-6 text-amber-600"></i>
                    </div>
                    <h3 class="text-sm font-medium text-gray-700">Active Learning</h3>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-2"><?php echo count($courses); ?></h2>
                <p class="text-sm text-gray-600 flex items-center gap-2">
                    <i data-lucide="clock" class="h-4 w-4 text-blue-500"></i>
                    <span>Courses in progress</span>
                </p>
            </div>
        </div>

        <!-- Course Progress Cards -->
        <div>
            <!-- Section Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                <h2 class="text-xl font-semibold text-blue-900 flex items-center gap-2 mb-4 md:mb-0">
                    <i data-lucide="layers" class="h-5 w-5 text-blue-600"></i>
                    <span>Your Courses</span>
                </h2>
                <div class="relative w-full md:w-80">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"></i>
                    <input type="search" 
                           id="searchCourses" 
                           placeholder="Search your courses..." 
                           class="w-full pl-10 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" onclick="clearSearch()">
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>
            </div>

            <!-- Course Cards -->
            <div class="gap-6">
                <?php foreach ($courses as $course): ?>
                <div data-course-card class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-all duration-200">
                    <div class="h-2 bg-blue-600"></div>
                    <div class="p-6 grid md:grid-cols-3 max-md:grid-rows-2 lg:grid-cols-4">
                        <div class="col-span-1 max-md:col-span-2 row-span-1 lg:col-span-1 max-md:text-center place-content-center">
                            <img src="book image.jpg" alt="Open Book" class="h-[150px] hx-auto mx-auto">
                        </div>
                        <div class="col-span-2 lg:col-span-3">
                            <div class="flex items-start gap-4 mb-4">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="book" class="h-5 w-5 text-blue-600"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900 mb-1"><?php echo htmlspecialchars($course['course_title']); ?></h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $course['course_progress'] >= 100 ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?>">
                                        <?php echo $course['course_progress'] >= 100 ? 'Completed' : 'In Progress'; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="text-sm text-gray-600 mb-4 line-clamp-2">
                                <?php echo htmlspecialchars($course['course_description']); ?>
                            </div>

                            <div class="bg-gray-50 rounded-lg p-4 mb-4 grid grid-cols-2 gap-4">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="check-circle" class="h-4 w-4 text-green-500"></i>
                                    <div>
                                        <div class="text-xs text-gray-500">Completed Modules</div>
                                        <div class="text-sm font-medium text-gray-900"><?php echo $course['watched_videos']; ?> / <?php echo $course['total_videos']; ?></div>
                                    </div>
                                </div>
                                <?php if($course['total_questions'] > 0): ?>
                                <div class="flex items-center gap-2">
                                    <i data-lucide="award" class="h-4 w-4 text-amber-500"></i>
                                    <div>
                                        <div class="text-xs text-gray-500">Correct Answers</div>
                                        <div class="text-sm font-medium text-gray-900"><?php echo $course['user_score']; ?> / <?php echo $course['total_questions']; ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="grid grid-cols-4 pt-4 border-t border-gray-200 gap-5">
                                <div class="col-span-3">
                                    <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
                                        <div class="bg-blue-600 h-3 rounded-full" style="width: <?php echo round($course['course_progress']); ?>%"></div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs text-gray-500 flex items-center gap-1">
                                            <i data-lucide="clock" class="h-3 w-3"></i>
                                            <span>Last activity: <?php echo date('F j, Y g:i A', strtotime($course['last_activity'])); ?></span>
                                        </div>
                                        <div class="text-xs text-gray-500"><?php echo round($course['course_progress']); ?>% complete</div>
                                    </div>
                                </div>
                                
                                <a href="view_course.php?id=<?php echo $course['course_id']; ?>" 
                                class="col-span-1 px-3 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 flex place-content-center gap-2 transition-colors">
                                <?php if ($course['course_progress'] == 0): ?>
                                    <i data-lucide="play" class="w-4 h-4 mr-2"></i>
                                    Start Course
                                    <?php elseif ($course['course_progress'] == 100): ?>
                                    <i data-lucide="rotate-ccw" class="w-4 h-4 mr-2"></i>
                                    Review Course
                                    <?php else: ?>
                                    <i data-lucide="arrow-right" class="w-4 h-4 mr-2"></i>
                                    Continue Course
                                <?php endif; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
    
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Lucide icons
        lucide.createIcons();
        
        const searchInput = document.getElementById('searchCourses');
        const courseCards = document.querySelectorAll('[data-course-card]');
        
        function filterCourses(searchTerm) {
            searchTerm = searchTerm.toLowerCase();
            let hasResults = false;
            
            courseCards.forEach(card => {
                const title = card.querySelector('h4').textContent.toLowerCase();
                const description = card.querySelector('.text-gray-600').textContent.toLowerCase();
                const isVisible = (title.includes(searchTerm) || description.includes(searchTerm));
                card.style.display = isVisible ? '' : 'none';
                if (isVisible) hasResults = true;
            });
            
            // Show "no results" message if needed
            let noResultsEl = document.getElementById('noSearchResults');
            if (!hasResults && searchTerm.length > 0) {
                if (!noResultsEl) {
                    const container = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-3');
                    noResultsEl = document.createElement('div');
                    noResultsEl.id = 'noSearchResults';
                    noResultsEl.className = 'col-span-1 md:col-span-2 lg:col-span-3 py-10 text-center';
                    noResultsEl.innerHTML = `
                        <i data-lucide="search-x" class="h-12 w-12 text-gray-400 mx-auto mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-700 mb-1">No courses found</h3>
                        <p class="text-gray-500 mb-4">Try adjusting your search term</p>
                        <button class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-sm font-medium" onclick="clearSearch()">
                            Clear Search
                        </button>
                    `;
                    container.appendChild(noResultsEl);
                    lucide.createIcons();
                }
            } else if (noResultsEl) {
                noResultsEl.remove();
            }
        }

        searchInput.addEventListener('input', (e) => {
            filterCourses(e.target.value);
        });

        window.clearSearch = function() {
            searchInput.value = '';
            filterCourses('');
            searchInput.focus();
        };
    });
</script>
