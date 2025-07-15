<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['quiz_results'])) {
    header("Location: dashboard.php");
    exit;
}

$quiz_id = $_GET['id'] ?? 0;
$results = $_SESSION['quiz_results'];

// Verify this is the correct quiz
if ($results['quiz_id'] != $quiz_id) {
    header("Location: dashboard.php");
    exit;
}

// Get quiz details
$stmt = $conn->prepare("SELECT q.*, c.id as course_id, c.title as course_title 
                       FROM quizzes q 
                       JOIN courses c ON q.course_id = c.id 
                       WHERE q.id = ?");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();

// Get all questions with user answers
$questions = [];
$correct_answers = 0;
$total_questions = 0;

foreach ($results['answers'] as $question_id => $user_answer) {
    $stmt = $conn->prepare("SELECT * FROM questions WHERE id = ?");
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $question = $stmt->get_result()->fetch_assoc();
    if ($question) {
        $question['user_answer'] = $user_answer;
        $questions[] = $question;
        $total_questions++;
        if ($user_answer === $question['correct_answer']) {
            $correct_answers++;
        }
    }
}

// Calculate final score as percentage
$final_score = $total_questions > 0 ? round(($correct_answers / $total_questions) * 100) : 0;

// Store the calculated values
$results['correct_answers'] = $correct_answers;
$results['total_questions'] = $total_questions;
$results['final_score'] = $final_score;
$results['time_taken'] = isset($results['time_taken']) ? $results['time_taken'] : 0;

// Initialize variables to avoid undefined variable errors
$user_progress = [
    'score' => $final_score,
    'completed_at' => date('Y-m-d H:i:s')
];

// Set time taken value
$time_taken = isset($results['time_taken']) ? $results['time_taken'] : 'N/A';

// Set text grade based on score
if ($final_score >= 90) {
    $text_grade = 'Excellent';
} elseif ($final_score >= 80) {
    $text_grade = 'Good';
} elseif ($final_score >= 70) {
    $text_grade = 'Satisfactory';
} elseif ($final_score >= 60) {
    $text_grade = 'Fair';
} else {
    $text_grade = 'Needs improvement';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results - <?php echo htmlspecialchars($quiz['title']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-gray-50">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="p-8 sm:ml-72">
        <div class="container mx-auto">
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl p-8 shadow-md mb-8">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-white mb-2"><?php echo htmlspecialchars($quiz['title']); ?> - Results</h1>
                        <p class="text-blue-100 flex items-center gap-2">
                            <i class="bi bi-book"></i>
                            <span><?php echo htmlspecialchars($quiz['course_title']); ?></span>
                            <span class="text-blue-300">•</span>
                            <span>Completed: <?php echo date('M j, Y', strtotime($user_progress['completed_at'])); ?></span>
                        </p>
                    </div>
                    <a href="view_course.php?id=<?php echo $quiz['course_id']; ?>" class="px-5 py-2.5 bg-white/10 hover:bg-white/20 border border-white/30 text-white rounded-xl flex items-center gap-2 transition font-medium">
                        <i class="bi bi-arrow-left"></i> Back to Course
                    </a>
                </div>
            </div>

            <!-- Result Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Score Card -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                    <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center mb-4 text-blue-500">
                        <i class="bi bi-trophy text-2xl"></i>
                    </div>
                    <h3 class="text-gray-700 font-medium mb-1">Your Score</h3>
                    <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo $final_score; ?>%</div>
                    <div class="text-sm text-gray-500"><?php echo $text_grade; ?></div>
                </div>
                
                <!-- Correct Answers -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                    <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center mb-4 text-blue-500">
                        <i class="bi bi-check-circle text-2xl"></i>
                    </div>
                    <h3 class="text-gray-700 font-medium mb-1">Correct Answers</h3>
                    <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo $correct_answers; ?></div>
                    <div class="text-sm text-gray-500">out of <?php echo $total_questions; ?> questions</div>
                </div>
                
                <!-- Time Taken -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                    <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center mb-4 text-blue-500">
                        <i class="bi bi-clock text-2xl"></i>
                    </div>
                    <h3 class="text-gray-700 font-medium mb-1">Time Taken</h3>
                    <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo $time_taken; ?></div>
                    <div class="text-sm text-gray-500">minutes to complete</div>
                </div>
            </div>

            <!-- Detailed Review -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Detailed Review</h2>
                <div class="space-y-6">
                    <?php foreach ($questions as $index => $question): 
                        $isCorrect = $question['correct_answer'] === $question['user_answer'];
                        $options = [
                            'A' => $question['option_a'],
                            'B' => $question['option_b'],
                            'C' => $question['option_c'],
                            'D' => $question['option_d']
                        ];
                    ?>
                        <div class="bg-gray-50 rounded-xl border border-gray-100 overflow-hidden">
                            <div class="border-b border-gray-100 p-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-medium text-blue-600">Question <?php echo $index + 1; ?></span>
                                    <span class="flex items-center gap-1.5 text-xs font-medium <?php echo $isCorrect ? 'text-green-600' : 'text-red-600'; ?>">
                                        <?php if ($isCorrect): ?>
                                            <i class="bi bi-check-circle-fill"></i> Correct
                                        <?php else: ?>
                                            <i class="bi bi-x-circle-fill"></i> Incorrect
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="mt-2 text-sm text-gray-800">
                                    <?php echo htmlspecialchars($question['question_text']); ?>
                                </div>
                                <?php if (!empty($question['question_image'])): ?>
                                <div class="mt-3">
                                    <?php if (strpos($question['question_image'], 'assets:') === 0): ?>
                                        <img src="../assets/<?php echo htmlspecialchars(substr($question['question_image'], 7)); ?>" 
                                             alt="Question Image" 
                                             class="max-w-full h-auto rounded-lg border border-gray-200 max-h-64">
                                    <?php else: ?>
                                        <img src="../uploads/question_images/<?php echo htmlspecialchars($question['question_image']); ?>" 
                                             alt="Question Image" 
                                             class="max-w-full h-auto rounded-lg border border-gray-200 max-h-64">
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="p-4 space-y-3">
                                <?php foreach ($options as $letter => $option_text): 
                                    $optionClass = '';
                                    $optionBg = 'bg-white';
                                    $optionBorder = 'border-gray-200';
                                    $iconColor = '';
                                    
                                    if ($letter === $question['correct_answer']) {
                                        $optionClass = 'border-green-500';
                                        $optionBg = 'bg-green-50';
                                        $iconColor = 'text-green-600';
                                    } elseif ($letter === $question['user_answer'] && !$isCorrect) {
                                        $optionClass = 'border-red-500';
                                        $optionBg = 'bg-red-50';
                                        $iconColor = 'text-red-600';
                                    }
                                    
                                    $option_image_field = 'option_' . strtolower($letter) . '_image';
                                    $has_image = !empty($question[$option_image_field]);
                                ?>
                                    <div class="flex flex-col p-3 rounded-lg border <?php echo $optionClass ?: 'border-gray-200'; ?> <?php echo $optionBg; ?>">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-blue-100 text-blue-700 text-sm font-medium mr-3"><?php echo $letter; ?></span>
                                                <span class="text-sm"><?php echo htmlspecialchars($option_text); ?></span>
                                            </div>
                                            <?php if ($letter === $question['correct_answer']): ?>
                                                <i class="bi bi-check-circle-fill text-green-600"></i>
                                            <?php elseif ($letter === $question['user_answer'] && !$isCorrect): ?>
                                                <i class="bi bi-x-circle-fill text-red-600"></i>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($has_image): ?>
                                        <div class="mt-3 w-full">
                                            <?php if (strpos($question[$option_image_field], 'assets:') === 0): ?>
                                                <img src="../assets/<?php echo htmlspecialchars(substr($question[$option_image_field], 7)); ?>" 
                                                     alt="Option <?php echo $letter; ?> Image" 
                                                     class="max-w-full h-auto rounded-lg border border-gray-100 max-h-36">
                                            <?php else: ?>
                                                <img src="../uploads/question_images/<?php echo htmlspecialchars($question[$option_image_field]); ?>" 
                                                     alt="Option <?php echo $letter; ?> Image" 
                                                     class="max-w-full h-auto rounded-lg border border-gray-100 max-h-36">
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
