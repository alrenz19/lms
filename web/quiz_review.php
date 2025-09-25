<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$quiz_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Get quiz details and verify completion
$stmt = $conn->prepare("
    SELECT q.*, c.id as course_id, c.title, up.user_answers, up.score, up.completed
    FROM questions q
    JOIN courses c ON q.course_id = c.id
    LEFT JOIN user_progress up ON q.course_id = up.course_id AND up.user_id = ?
    WHERE q.course_id = ? AND q.removed = 0
");
$stmt->bind_param("ii", $user_id, $quiz_id);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();

// Redirect if quiz not found or not completed
if (!$quiz || !$quiz['completed']) {
    header("Location: view_course.php?id=" . $quiz['course_id']);
    exit;
}

// Get quiz questions with answers
$stmt = $conn->prepare("SELECT * FROM questions WHERE course_id = ? AND removed=0 ORDER BY id");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Properly decode user answers with null check
$user_answers = !empty($quiz['user_answers']) ? json_decode($quiz['user_answers'], true) : [];

// Calculate the correct answer count
$correct_count = 0;
foreach ($questions as $question) {
    if (isset($user_answers[$question['id']]) && strtolower($user_answers[$question['id']]) === strtolower($question['correct_answer'])) {
        $correct_count++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz Review - <?php echo htmlspecialchars($quiz['title']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <!--<link rel="stylesheet" href="./public/css/tailwind.min.css" />-->
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
        .answer-option {
            transition: all 0.2s ease;
        }
        .question-card {
            border-left-width: 4px;
            transition: transform 0.2s ease;
        }
        .question-card:hover {
            transform: translateX(4px);
        }
        .correct-answer {
            border-left-color: #10b981;
        }
        .incorrect-answer {
            border-left-color: #ef4444;
        }
        .neutral-answer {
            border-left-color: #e5e7eb;
        }
        .score-badge {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        }
    </style>
</head>
<body class="bg-blue-50">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="p-8 sm:ml-72">
        <div class="container mx-auto max-w-4xl">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-2xl shadow-lg mb-8 p-8 text-white">
                <div class="flex flex-col md:flex-row gap-6 justify-between">
                    <div>
                        <h1 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($quiz['title']); ?> - Results</h1>
                        <p class="text-blue-100">Review your answers and see what you got right</p>
                    </div>
                    <div class="flex flex-col items-end justify-center">
                        <div class="text-5xl font-bold mb-2"><?php echo $quiz['score']; ?>%</div>
                        <div class="text-lg"><?php echo $correct_count; ?> of <?php echo count($questions); ?> correct</div>
                    </div>
                </div>
            </div>
            
            <!-- Questions Review -->
            <div class="space-y-6 mb-8">
                <?php foreach ($questions as $index => $question): ?>
                    <?php 
                    $user_answer = isset($user_answers[$question['id']]) ? $user_answers[$question['id']] : '';
                    $is_correct = strtolower($user_answer) === strtolower($question['correct_answer']);
                    $card_class = !empty($user_answer) ? 
                        ($is_correct ? 'correct-answer' : 'incorrect-answer') : 
                        'neutral-answer';
                    $correct_answer = strtoupper($question['correct_answer']);
                    $has_question_image = !empty($question['question_image']);
                    ?>
                    <div class="question-card bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 p-6 <?php echo $card_class; ?>">
                        <div class="flex items-start gap-3 mb-4">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 text-blue-600 font-bold">
                                <?php echo $index + 1; ?>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-800 mb-1">
                                    <?php 
                                        echo !$has_question_image 
                                        ? htmlspecialchars($question['question_text']) 
                                        : '<img src="../' . htmlspecialchars($question['question_image']) . 
                                            '" alt="Option ' . htmlspecialchars($question['question_text']) . ' Image" class="max-w-full h-auto rounded-lg border border-gray-100 max-h-48">';
                                    ?>
                                    
                                <?php echo htmlspecialchars($question['question_text']); ?>
                            
                                </h3>
                                <?php if (!empty($user_answer)): ?>
                                    <?php if ($is_correct): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="bi bi-check-circle-fill mr-1"></i> Correct
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="bi bi-x-circle-fill mr-1"></i> Incorrect
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <i class="bi bi-dash-circle-fill mr-1"></i> Not Answered 
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-6">
                            <?php
                            $options = [
                                'A' => $question['option_a'],
                                'B' => $question['option_b'],
                                'C' => $question['option_c'],
                                'D' => $question['option_d']
                            ];
                            foreach ($options as $letter => $option): 
                                // Style for different answer states
                                $option_image_field = 'option_' . strtolower($letter) . '_image';
                                $has_image = !empty($question[$option_image_field]);
                                $bgClass = 'bg-gray-50 border border-gray-200';
                                $textClass = 'text-gray-700';
                                $iconClass = '';
                                
                                if ($letter === $user_answer && $letter === strtoupper($question['correct_answer'])) {
                                    $bgClass = 'bg-green-50 border border-green-200';
                                    $textClass = 'text-green-800';
                                    $iconClass = '<i class="bi bi-check-circle-fill text-green-500 ml-2"></i>';
                                } elseif ($letter === $user_answer) {
                                    $bgClass = 'bg-red-50 border border-red-200';
                                    $textClass = 'text-red-800';
                                    $iconClass = '<i class="bi bi-x-circle-fill text-red-500 ml-2"></i>';
                                } elseif ($letter === $question['correct_answer']) {
                                    $bgClass = 'bg-blue-50 border border-blue-200';
                                    $textClass = 'text-blue-700';
                                    $iconClass = '<i class="bi bi-check-circle-fill text-blue-500 ml-2"></i>';
                                }
                                ?>
                                <div class="<?php echo $bgClass; ?> p-3 rounded-lg flex items-center">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full mr-2 text-sm font-medium <?php echo $letter === strtoupper($question['correct_answer']) ? 'bg-green-100 text-green-800' : ($letter === $user_answer ? 'bg-red-100 text-red-800' : 'bg-gray-200 text-gray-800'); ?>">
                                        <?php echo $letter; ?>
                                    </span>
                                    <span class="<?php echo $textClass; ?>">
                                        <?php 
                                            echo !$has_image 
                                            ? htmlspecialchars($option) 
                                            : '<img src="../' . htmlspecialchars($question[$option_image_field]) . 
                                                '" alt="Option ' . htmlspecialchars($letter) . ' Image" class="max-w-full h-auto rounded-lg border border-gray-100 max-h-48">';
                                        ?>
                                    </span>
                                    <?php echo $iconClass; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if (!empty($user_answer) && !$is_correct): ?>
                            <?php 
                                $option_field = 'option_'; 
                                $option_image = '_image';
                            ?>
                            <div class="mt-6 p-5 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="mb-3">
                                    <h4 class="font-medium text-gray-700 mb-2">Explanation</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="bg-red-50 text-red-800 p-4 rounded-lg flex items-start gap-2 border border-red-200">
                                            <i class="bi bi-x-circle-fill mt-0.5"></i>
                                            <div>
                                                <p class="font-medium mb-1">Your answer</p>
                                                <p>
                                                    <?php echo htmlspecialchars($user_answer); ?>  - 
                                                    <?php 
                                                    echo $options[$user_answer]
                                                    ? htmlspecialchars($options[$user_answer])
                                                    : '<img src="../' . htmlspecialchars($question[$option_field.strtolower($user_answer).$option_image]) . 
                                                        '" alt="Option ' . htmlspecialchars($user_answer) . ' Image" class="max-w-full h-auto rounded-lg border border-gray-100 max-h-48">';
                                                    ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="bg-green-50 text-green-800 p-4 rounded-lg flex items-start gap-2 border border-green-200">
                                            <i class="bi bi-check-circle-fill mt-0.5"></i>
                                            <div>
                                                <p class="font-medium mb-1">Correct answer <?php echo $question[$option_image_field]?></p>
                                                <p><?php echo strtoupper($question['correct_answer']); ?> -
                                                    <?php 
                                                        echo $options[$correct_answer] 
                                                        ? htmlspecialchars($options[$correct_answer]) 
                                                        : '<img src="../' . htmlspecialchars($question[$option_field.strtolower($correct_answer).$option_image]) . 
                                                            '" alt="Option ' . htmlspecialchars($user_answer) . ' Image" class="max-w-full h-auto rounded-lg border border-gray-100 max-h-48">';
                                                    ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Action buttons -->
            <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-all duration-300 mb-8 border border-gray-200">
                <a href="view_course.php?id=<?php echo $quiz['course_id']; ?>" 
                   class="px-5 py-2.5 bg-blue-100 hover:bg-blue-200 text-blue-800 rounded-xl flex items-center gap-2 transition font-medium">
                    <i class="bi bi-arrow-left"></i> Back to Course
                </a>
                
                <?php 
                // Get next quiz in the course
                $stmt = $conn->prepare("SELECT id FROM quizzes WHERE course_id = ? AND id > ? ORDER BY id ASC LIMIT 1");
                $stmt->bind_param("ii", $quiz['course_id'], $quiz_id);
                $stmt->execute();
                $next_quiz = $stmt->get_result()->fetch_assoc();
                
                if ($next_quiz): 
                ?>
                <a href="take_quiz.php?id=<?php echo $next_quiz['id']; ?>" 
                   class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl flex items-center gap-2 transition font-medium shadow-sm">
                    Next Quiz <i class="bi bi-arrow-right"></i>
                </a>
                <?php else: ?>
                <a href="view_course.php?id=<?php echo $quiz['course_id']; ?>#completed" 
                   class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-xl flex items-center gap-2 transition font-medium shadow-sm">
                    Course Completed <i class="bi bi-check-circle"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Lucide icons
            lucide.createIcons();
        });
    </script>
</body>
</html>
