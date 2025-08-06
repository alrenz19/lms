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
/* $stmt = $conn->prepare("
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
} */
$stmt = $conn->prepare("SELECT * FROM user_progress WHERE user_id = ? AND course_id = ? AND completed = 1 ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$result = $stmt->get_result();
$progress_data = $result->fetch_assoc();

$quiz_completed = $progress_data ? true : false;
$average_score = $progress_data['score'] ?? 0;




    // Fetch all questions for this course
$stmt = $conn->prepare("
    SELECT q.*, up.quiz_id, up.score 
    FROM questions q
    LEFT JOIN user_progress up ON q.id = up.quiz_id AND up.user_id = ?
    WHERE q.course_id = ? AND q.removed = 0
    ORDER BY q.id ASC
");
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$result = $stmt->get_result();

$questions = [];
$answered_count = 0;
$total_score = 0;

while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
    if ($row['quiz_id']) {
        $answered_count++;
        $total_score += $row['score'];
    }
}

$total_questions = count($questions);

// Quiz is completed if all questions have user_progress entries
$quiz_completed = ($answered_count === $total_questions && $total_questions > 0);
$average_score = $total_questions > 0 ? round($total_score / $total_questions, 2) : 0;



    // Get video information and progress
    $videos = [];
    $video_progress = [];

    // Get all modules (videos or PDFs) for the course
    $stmt = $conn->prepare("SELECT * FROM course_videos WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $video_result = $stmt->get_result();

    while ($row = $video_result->fetch_assoc()) {
        $videos[] = $row;
        $video_id = $row['id'];

        // Get progress per module (watched = 1)
        $stmt_progress = $conn->prepare("SELECT * FROM user_video_progress WHERE user_id = ? AND video_id = ? AND watched = 1");
        $stmt_progress->bind_param("ii", $user_id, $video_id);
        $stmt_progress->execute();
        $progress_result = $stmt_progress->get_result()->fetch_assoc();

        // Store progress status per video_id
        if ($progress_result) {
            $video_progress[$video_id] = $progress_result;
        }
    }

    $totalModules = count($videos);
    $completedModules = 0;

    foreach ($videos as $module) {
        $video_id = $module['id'];
        if (isset($video_progress[$video_id])) {
            $completedModules++;
        }
    }

    $overall_progress = ($totalModules > 0) ? ($completedModules / $totalModules) * 100 : 0;

    // Calculate if user can access quizzes (only if video is watched)
    $can_access_quiz = round($overall_progress) < 100 ? false : true;
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
        .tab-btn:hover{
            background-color: #f6f9ff;
        }
        .active-tab{
            background-color: #eff4ff;
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
                        </div>
                        <p class="text-blue-100 text-lg mb-8 max-w-3xl leading-relaxed"><?php echo htmlspecialchars($course['description']); ?></p>
                    </div>
                    <a href="dashboard.php" class="px-5 py-2.5 bg-transparent border border-white rounded-full text-white hover:bg-white/10 transition-colors flex items-center gap-2 font-medium shadow-sm">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Course Content Section -->
            <div class="mt-12">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center mr-3 text-blue-600">
                        <i class="bi bi-journal-check text-xl"></i>
                    </div>
                    <h5 class="text-2xl font-bold text-gray-800">Course Content</h5>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="col-span-1">
                        <div class="quiz-card bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 overflow-hidden">
                            <div class="p-6">
                                <h3 class="text-xl font-bold text-gray-900 mb-4"><?php echo htmlspecialchars($course['title']); ?></h3>
                                    <div class="col-span-3 place-content-center">
                                        <div class="flex justify-between items-center mt-2 mb-2">
                                            <span class="text-xs font-medium text-gray-500">
                                                <?php echo round($overall_progress); ?>% complete
                                            </span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-4 mb-7">
                                            <div class="bg-blue-600 h-4 rounded-full" style="width: <?php echo round($overall_progress); ?>%"></div>
                                        </div>
                                    </div>

                                    
                                    <?php $first = true; foreach ($videos as $module): 
                                        $dataTab = 'module' . $module['id'];
                                    ?>
                                        <button type="button" class="tab-btn <?= ($first) ? 'active-tab' : '' ?> w-full py-2.5 rounded-sm font-medium flex items-center gap-2" data-tab="<?= $dataTab ?>">
                                            <?= htmlspecialchars($module['module_name']) ?>
                                            <?php if (!empty($video_progress[$module['id']])): ?>
                                                <i class="bi bi-check-lg text-green-500"></i>
                                            <?php endif; ?>
                                        </button>
                                    <?php 
                                        $first = false;
                                    endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Course Content Videos/PDF -->
                    <div class="col-span-2">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 p-6">
                            <?php 
                            $previous_completed = true;
                            foreach ($videos as $index => $video): 
                                $video_id = $video['id'];
                                $tabId = 'module' . $video_id;
                                $file_extension = pathinfo($video['video_url'], PATHINFO_EXTENSION);

                                $isFirst = ($index === 0);
                                $current_progress = !empty($video_progress[$video_id]);

                                $canAccess = $isFirst || $previous_completed;
                            ?>
                                <div id="<?= $tabId ?>" class="tab-content <?= $isFirst ? '' : 'hidden' ?>">
                                    <div class="p-0">
                                        <h4 class="text-xl font-medium text-gray-900 mb-4"><?= $video['module_description']; ?></h4>

                                        <?php if ($canAccess): ?>
                                            <?php if ($file_extension === 'mp4'): ?>
                                                <div class="relative w-full pt-[56.25%]">
                                                    <video  id="courseVideo" class="absolute top-0 left-0 w-full h-full object-contain" controls>
                                                        <source src="get_video.php?course_id=<?= $course_id ?>&id=<?= $video_id ?>" type="video/mp4">
                                                        Your browser does not support the video tag.
                                                    </video>
                                                    <script>
                                                        document.addEventListener('DOMContentLoaded', function () {
                                                            const video = document.getElementById('courseVideo');
                                                            if (video) {
                                                                video.addEventListener('ended', function () {
                                                                    // Send progress via AJAX when video ends
                                                                    fetch('save_progress.php', {
                                                                        method: 'POST',
                                                                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                                                        body: 'video_id=<?= $video_id ?>&course_id=<?= $course_id ?>'
                                                                    })
                                                                    .then(response => response.text())
                                                                    .then(data => console.log('Progress saved:', data))
                                                                    .catch(error => console.error('Error:', error));
                                                                });
                                                            }
                                                        });
                                                    </script>
                                                </div>
                                            <?php elseif ($file_extension === 'pdf'): ?>
                                                <div class="relative w-full">
                                                    <embed src="get_pdf.php?course_id=<?= $course_id ?>&id=<?= $video_id ?>" type="application/pdf" width="100%" height="500px">
                                                    <?php if (!isset($video_progress[$video_id])): ?>
                                                        <form method="post" action="save_progress.php" class="text-right">
                                                            <input type="hidden" name="video_id" value="<?= $video_id ?>">
                                                            <input type="hidden" name="course_id" value="<?= $course_id ?>">
                                                            <button type="submit" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                                                Mark as Complete
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <p class="text-right text-green-600 font-medium mt-4">✔ Completed</p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="relative w-full pt-[24.25%] pb-[24%] place-content-center">
                                                <h3 class="text-base text-center font-semibold text-gray-900 gap-2">
                                                    The order of the course content is determined. <br />
                                                    You must complete the previous module before proceeding.
                                                </h3>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php $previous_completed = $current_progress;
                        endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quiz Section -->
            <div class="mt-12">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center mr-3 text-blue-600">
                        <i class="bi bi-journal-check text-xl"></i>
                    </div>
                    <h5 class="text-2xl font-bold text-gray-800">Course Quiz</h5>
                </div>
                
                <?php if (round($overall_progress) < 100): ?>
                    <div class="p-5 mb-8 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 flex items-start gap-3 shadow-sm">
                        <i class="bi bi-exclamation-triangle text-xl mt-0.5"></i>
                        <div>
                            <h4 class="font-medium mb-1">Action Required</h4>
                            <p>Please complete course modules before accessing the quiz.</p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <?php if (empty($questions)): ?>
                        <div class="col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 p-8 text-center">
                            <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center mx-auto mb-4 text-blue-600">
                                <i class="bi bi-clipboard-x text-2xl"></i>
                            </div>
                            <h3 class="text-xl font-medium text-gray-900 mb-2">No Exam Available</h3>
                            <p class="text-gray-500 mb-0">This course doesn't have any exam yet.</p>
                        </div>
                    <?php else: ?>
                        <?php if (!empty($questions)): ?>
                            <div class="quiz-card bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 overflow-hidden">
                                <div class="p-6">
                                    <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center mb-4 text-blue-600">
                                        <i class="bi bi-question-circle text-xl"></i>
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-900 mb-4">Quiz</h3>
                                    
                                    <?php if ($quiz_completed): ?>
                                        <div class="mb-5">
                                            <div class="flex justify-between items-center mb-2">
                                                <span class="text-sm text-gray-600 font-medium">Quiz Completed</span>
                                                <span class="text-sm font-bold text-green-600">Score: <?php echo $average_score; ?>%</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                                                <div class="bg-green-500 h-2.5 rounded-full" style="width: 100%"></div>
                                            </div>
                                        </div>
                                        <a href="quiz_review.php?id=<?php echo $course_id; ?>" 
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
                                        <?php if ($can_access_quiz): ?>
                                            <a href="take_quiz.php?id=<?php echo $course_id; ?>" 
                                               class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium shadow-sm">
                                                <i class="bi bi-play-fill"></i> Start Quiz
                                            </a>
                                        <?php else: ?>
                                            <button disabled 
                                                    class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-gray-300 text-gray-500 rounded-xl cursor-not-allowed">
                                                <i class="bi bi-lock"></i> Finish the module first
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.tab-btn');
    const tabs = document.querySelectorAll('.tab-content');

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            console.log("Button clicked:", button); // LOGGING BUTTON CLICK

            const target = button.getAttribute('data-tab');

            // Remove active styles from all buttons
            buttons.forEach(btn => btn.classList.remove('active-tab'));

            // Hide all tabs
            tabs.forEach(tab => tab.classList.add('hidden'));

            // Show target tab
            document.getElementById(target).classList.remove('hidden');

            // Add active style to clicked button
            button.classList.add('active-tab');
        });
    });

    const videos = document.querySelectorAll('video');
            videos.forEach(video => {
                video.addEventListener('ended', function () {
                    const videoId = video.closest('.tab-content').id.replace('module', '');
                    markModuleAsWatched(videoId);
                });
            });

            function markModuleAsWatched(videoId) {
        fetch('update_video_progress.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `video_id=${videoId}&user_id=<?php echo $_SESSION['user_id']; ?>&course_id=<?php echo $course_id; ?>`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Progress updated for module ' + videoId);
                // Optionally reload or update the checkmark UI here
                location.reload(); // temporary: reload to update checkmark and progress bar
            } else {
                console.error('Progress update failed: ' + data.message);
            }
        })
        .catch(err => console.error('Error:', err));
    }
        });
    </script>
</body>
</html>
