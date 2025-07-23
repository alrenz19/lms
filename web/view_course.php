<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

// Get course details
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

// Redirect if course doesn't exist
if (!$course) {
    header("Location: dashboard.php");
    exit;
}

// Get user progress for all quizzes in this course
$stmt = $conn->prepare("
    SELECT 
        COALESCE(AVG(CASE WHEN up.quiz_id IS NOT NULL THEN up.progress_percentage ELSE 0 END), 0) as overall_progress,
        COALESCE(SUM(up.score), 0) as total_score,
        COUNT(DISTINCT q.id) as total_quizzes,
        COUNT(DISTINCT up.quiz_id) as completed_quizzes,
        (
            SELECT COUNT(*)
            FROM questions qs
            JOIN quizzes qz ON qs.quiz_id = qz.id
            WHERE qz.course_id = ?
        ) as total_questions
    FROM quizzes q
    LEFT JOIN user_progress up ON q.id = up.quiz_id AND up.user_id = ?
    WHERE q.course_id = ?
");
$stmt->bind_param("iii", $course_id, $user_id, $course_id);
$stmt->execute();
$progress = $stmt->get_result()->fetch_assoc();

// If there are no quizzes, set progress to 0
if ($progress['total_quizzes'] == 0) {
    $progress['overall_progress'] = 0;
    $progress['total_score'] = 0;
}

// Set progress to 100% if all quizzes are completed
if ($progress['total_quizzes'] > 0 && $progress['completed_quizzes'] == $progress['total_quizzes']) {
    $progress['overall_progress'] = 100;
}

// Get video information and progress
$video_info = null;
$video_progress = null;
$stmt = $conn->prepare("SELECT * FROM course_videos WHERE course_id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$video_result = $stmt->get_result();
if ($video_result->num_rows > 0) {
    $video_info = $video_result->fetch_assoc();
    
    // Get user's video progress
    $stmt = $conn->prepare("SELECT * FROM user_video_progress WHERE user_id = ? AND video_id = ? AND watched = 1");
    $stmt->bind_param("ii", $user_id, $video_info['id']);
    $stmt->execute();
    $video_progress = $stmt->get_result()->fetch_assoc();
}

// Get quizzes for this course with completion status
$stmt = $conn->prepare("
    SELECT 
        q.*,
        CASE WHEN up.quiz_id IS NOT NULL THEN 1 ELSE 0 END as is_completed,
        COALESCE(up.score, 0) as score,
        COALESCE(up.progress_percentage, 0) as progress
    FROM quizzes q
    LEFT JOIN user_progress up ON q.id = up.quiz_id AND up.user_id = ?
    WHERE q.course_id = ?
    ORDER BY q.id ASC
");
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$quizzes = $stmt->get_result();

// Calculate if user can access quizzes (only if video is watched)
$can_access_quizzes = !$video_info || ($video_progress !== null);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - <?php echo htmlspecialchars($course['title']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
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
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .course-banner {
            background-image: linear-gradient(to right, var(--tw-gradient-stops));
            --tw-gradient-from: #4f46e5;
            --tw-gradient-to: #3730a3;
            --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to);
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2);
        }
        .quiz-card {
            transition: all 0.3s ease;
        }
        .quiz-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .stat-card {
            backdrop-filter: blur(8px);
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .progress-bar {
            transition: width 1s ease-in-out;
        }
        .video-play-button {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .video-play-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transform: skewX(-30deg);
            transition: all 0.6s ease;
        }
        .video-play-button:hover::before {
            left: 100%;
        }
    </style>
</head>
<body class="bg-blue-50">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="p-8 sm:ml-72">
        <div class="container mx-auto max-w-7xl">
            <!-- Course Header -->
            <div class="course-banner rounded-2xl p-10 shadow-lg mb-8 text-white relative overflow-hidden">
                <!-- Abstract shapes in background -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full -mr-20 -mt-20"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-white opacity-5 rounded-full -ml-10 -mb-10"></div>
                <div class="absolute bottom-20 right-20 w-32 h-32 bg-white opacity-5 rounded-full"></div>
                
                <div class="flex flex-col lg:flex-row justify-between items-start gap-6 relative z-10">
                    <div class="flex-1">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 mb-4">
                            <h1 class="text-3xl font-bold mb-0"><?php echo htmlspecialchars($course['title']); ?></h1>
                            <?php if ($video_info): ?>
                                <button type="button" class="video-play-button px-5 py-2.5 bg-white rounded-full text-blue-700 font-medium flex items-center gap-2 hover:bg-opacity-90 transition-colors shadow-sm" data-modal-target="videoModal" data-modal-toggle="videoModal">
                                    <i class="bi bi-play-fill text-lg"></i> Watch Video
                                </button>
                                <?php if (!$video_progress): ?>
                                    <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-sm font-medium bg-amber-100 text-amber-800 shadow-sm">
                                        <i class="bi bi-exclamation-triangle mr-1.5"></i>
                                        Watch video to unlock quizzes
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <p class="text-blue-100 text-lg mb-8 max-w-3xl leading-relaxed"><?php echo htmlspecialchars($course['description']); ?></p>
                    </div>
                    <a href="dashboard.php" class="px-5 py-2.5 bg-transparent border border-white rounded-full text-white hover:bg-white/10 transition-colors flex items-center gap-2 font-medium shadow-sm">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

                <!-- Progress Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                    <div class="stat-card rounded-2xl p-6">
                        <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center mb-4 text-white">
                            <i class="bi bi-book-fill text-xl"></i>
                        </div>
                        <h3 class="text-sm font-medium text-white/70 mb-1">Overall Progress</h3>
                        <div class="flex items-end gap-2">
                            <h2 class="text-3xl font-bold text-white"><?php echo round($progress['overall_progress']); ?>%</h2>
                            <div class="w-full bg-white/20 h-2 rounded-full mb-1.5 overflow-hidden">
                                <div class="bg-white progress-bar h-2 rounded-full" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card rounded-2xl p-6">
                        <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center mb-4 text-white">
                            <i class="bi bi-check-circle-fill text-xl"></i>
                        </div>
                        <h3 class="text-sm font-medium text-white/70 mb-1">Quizzes Completed</h3>
                        <div class="flex items-end">
                            <h2 class="text-3xl font-bold text-white"><?php echo $progress['completed_quizzes']; ?><span class="text-white/70 text-xl"> / <?php echo $progress['total_quizzes']; ?></span></h2>
                        </div>
                    </div>
                    
                    <div class="stat-card rounded-2xl p-6">
                        <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center mb-4 text-white">
                            <i class="bi bi-trophy-fill text-xl"></i>
                        </div>
                        <h3 class="text-sm font-medium text-white/70 mb-1">Total Score</h3>
                        <div class="flex items-end">
                            <h2 class="text-3xl font-bold text-white"><?php echo $progress['total_score']; ?> <span class="text-white/70 text-xl">pts</span></h2>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($video_info): ?>
            <!-- Video Modal -->
            <div id="videoModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-black/70 backdrop-blur-sm flex items-center justify-center">
                <div class="relative w-full max-w-4xl max-h-full">
                    <!-- Modal content -->
                    <div class="relative bg-gray-900 rounded-2xl shadow-2xl border border-gray-800">
                        <!-- Modal header -->
                        <div class="bg-gradient-to-r from-blue-600 to-blue-800 p-5 rounded-t-2xl flex items-center justify-between">
                            <h3 class="text-xl font-semibold text-white flex items-center gap-2">
                                <i class="bi bi-play-circle-fill"></i>
                                <?php echo htmlspecialchars($course['title']); ?> - Course Video
                            </h3>
                            <button type="button" class="text-white/80 hover:text-white bg-white/10 hover:bg-white/20 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center transition-colors" data-modal-hide="videoModal">
                                <i class="bi bi-x-lg"></i>
                                <span class="sr-only">Close modal</span>
                            </button>
                        </div>
                        <!-- Modal body -->
                        <div class="p-0">
                            <div class="relative w-full pt-[56.25%]">
                                <video id="courseVideo" class="absolute top-0 left-0 w-full h-full object-contain" controls>
                                    <source src="get_video.php?course_id=<?php echo $course_id; ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quiz Section -->
            <div class="mt-12">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center mr-3 text-blue-600">
                        <i class="bi bi-journal-check text-xl"></i>
                    </div>
                    <h5 class="text-2xl font-bold text-gray-800">Course Quizzes</h5>
                </div>
                
                <?php if ($video_info && !$video_progress): ?>
                    <div class="p-5 mb-8 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 flex items-start gap-3 shadow-sm">
                        <i class="bi bi-exclamation-triangle text-xl mt-0.5"></i>
                        <div>
                            <h4 class="font-medium mb-1">Action Required</h4>
                            <p>Please watch the course video completely before accessing the quizzes.</p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <?php if ($quizzes->num_rows == 0): ?>
                        <div class="col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 p-8 text-center">
                            <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center mx-auto mb-4 text-blue-600">
                                <i class="bi bi-clipboard-x text-2xl"></i>
                            </div>
                            <h3 class="text-xl font-medium text-gray-900 mb-2">No Quizzes Available</h3>
                            <p class="text-gray-500 mb-0">This course doesn't have any quizzes yet.</p>
                        </div>
                    <?php else: ?>
                        <?php while ($quiz = $quizzes->fetch_assoc()): ?>
                            <div class="quiz-card bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 overflow-hidden">
                                <div class="p-6">
                                    <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center mb-4 text-blue-600">
                                        <i class="bi bi-question-circle text-xl"></i>
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-900 mb-4"><?php echo htmlspecialchars($quiz['title']); ?></h3>
                                    
                                    <?php if ($quiz['is_completed']): ?>
                                        <div class="mb-5">
                                            <div class="flex justify-between items-center mb-2">
                                                <span class="text-sm text-gray-600 font-medium">Quiz Completed</span>
                                                <span class="text-sm font-bold text-green-600">Score: <?php echo $quiz['score']; ?>%</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                                                <div class="bg-green-500 h-2.5 rounded-full" style="width: 100%"></div>
                                            </div>
                                        </div>
                                        <a href="quiz_review.php?id=<?php echo $quiz['id']; ?>" 
                                           class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 border-2 border-blue-600 text-blue-600 rounded-xl hover:bg-blue-50 transition-colors font-medium">
                                            <i class="bi bi-eye"></i> Review Quiz
                                        </a>
                                    <?php else: ?>
                                        <div class="mb-5">
                                            <div class="flex justify-between items-center mb-2">
                                                <span class="text-sm text-gray-600 font-medium">Not Started</span>
                                                <span class="text-xs px-2.5 py-1 bg-blue-100 text-blue-700 rounded-full">New</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                                                <div class="bg-blue-600 h-2.5 rounded-full" style="width: 0%"></div>
                                            </div>
                                        </div>
                                        <?php if ($can_access_quizzes): ?>
                                            <a href="take_quiz.php?id=<?php echo $quiz['id']; ?>" 
                                               class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium shadow-sm">
                                                <i class="bi bi-play-fill"></i> Start Quiz
                                            </a>
                                        <?php else: ?>
                                            <button disabled 
                                                    class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-gray-300 text-gray-500 rounded-xl cursor-not-allowed">
                                                <i class="bi bi-lock"></i> Watch Video First
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Lucide icons
            lucide.createIcons();
            
            // Animate progress bars
            setTimeout(() => {
                const progressBars = document.querySelectorAll('.progress-bar');
                progressBars.forEach(bar => {
                    const width = bar.parentNode.getAttribute('style');
                    bar.style.width = '<?php echo round($progress['overall_progress']); ?>%';
                });
            }, 300);
            
            // Video modal functionality
            const openButtons = document.querySelectorAll('[data-modal-toggle]');
            const closeButtons = document.querySelectorAll('[data-modal-hide]');
            const videoModal = document.getElementById('videoModal');
            const courseVideo = document.getElementById('courseVideo');
            
            if (videoModal) {
                openButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        videoModal.classList.remove('hidden');
                    });
                });
                
                closeButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        videoModal.classList.add('hidden');
                        if (courseVideo) courseVideo.pause();
                    });
                });
                
                // Close modal when clicking outside
                videoModal.addEventListener('click', (e) => {
                    if (e.target === videoModal) {
                        videoModal.classList.add('hidden');
                        if (courseVideo) courseVideo.pause();
                    }
                });
                
                // Close modal with ESC key
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && !videoModal.classList.contains('hidden')) {
                        videoModal.classList.add('hidden');
                        if (courseVideo) courseVideo.pause();
                    }
                });
            }
            
            // Add video completion tracking
            if (courseVideo) {
                courseVideo.addEventListener('ended', function() {
                    // Send AJAX request to update video progress
                    fetch('update_video_progress.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'video_id=<?php echo $video_info ? $video_info['id'] : 0; ?>&user_id=<?php echo $user_id; ?>&course_id=<?php echo $course_id; ?>'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Reload page to unlock quizzes
                            window.location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                });
            }
        });
    </script>
</body>
</html>
