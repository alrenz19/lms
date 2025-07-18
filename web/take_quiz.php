<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$quiz_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Check if quiz exists and if it's already completed
$stmt = $conn->prepare("
    SELECT q.course_id, up.completed 
    FROM quizzes q 
    LEFT JOIN user_progress up ON up.quiz_id = q.id AND up.user_id = ? 
    WHERE q.id = ?
");
$stmt->bind_param("ii", $user_id, $quiz_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

// Redirect if quiz doesn't exist or is already completed
if (!$result || $result['completed']) {
    header("Location: view_course.php?id=" . ($result['course_id'] ?? 0));
    exit;
}

// Get quiz details
$stmt = $conn->prepare("SELECT q.*, c.title as course_title FROM quizzes q JOIN courses c ON q.course_id = c.id WHERE q.id = ?");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $score = 0;
    $total_questions = count($_POST['answers']);
    $answers = [];
    
    // Calculate score and store answers
    foreach ($_POST['answers'] as $question_id => $answer) {
        $stmt = $conn->prepare("SELECT correct_answer FROM questions WHERE id = ?");
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result['correct_answer'] === $answer) {
            $score++;
        }
        
        $answers[$question_id] = $answer;
    }
    
    // Calculate percentage
    $percentage = ($score / $total_questions) * 100;
    $score = min($score, $total_questions);
    
    // Store answers as JSON
    $answers_json = json_encode($answers);
    
    // Update user progress including the answers
    $stmt = $conn->prepare("
        INSERT INTO user_progress (user_id, quiz_id, score, progress_percentage, completed, user_answers) 
        VALUES (?, ?, ?, ?, TRUE, ?) 
        ON DUPLICATE KEY UPDATE 
        score = VALUES(score), 
        progress_percentage = VALUES(progress_percentage),
        completed = TRUE,
        user_answers = VALUES(user_answers)"
    );
    
    $stmt->bind_param("iidds", $user_id, $quiz_id, $score, $percentage, $answers_json);
    $stmt->execute();
    
    $_SESSION['quiz_results'] = [
        'quiz_id' => $quiz_id,
        'score' => $score,
        'total' => $total_questions,
        'answers' => $answers
    ];
    
    header("Location: quiz_results.php?id=" . $quiz_id);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Quiz - <?php echo htmlspecialchars($quiz['title']); ?></title>
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
                        <h1 class="text-2xl font-bold text-white mb-2"><?php echo htmlspecialchars($quiz['title']); ?></h1>
                        <p class="text-blue-100 flex items-center gap-2">
                            <i class="bi bi-book"></i>
                            <span><?php echo htmlspecialchars($quiz['course_title']); ?></span>
                        </p>
                    </div>
                    <a href="view_course.php?id=<?php echo $quiz['course_id']; ?>" 
                       class="px-4 py-2 bg-transparent border border-white text-white rounded-lg hover:bg-white/10 flex items-center gap-2 transition text-sm font-medium">
                        <i class="bi bi-arrow-left"></i> Back to Course
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <form method="POST" class="quiz-form" id="quizForm">
                    <div class="space-y-6">
                        <?php
                        $questions = $conn->query("SELECT * FROM questions WHERE quiz_id = $quiz_id ORDER BY RAND()");
                        $question_num = 1;
                        while ($question = $questions->fetch_assoc()): 
                            // Create options array from individual fields
                            $options = [
                                'A' => $question['option_a'],
                                'B' => $question['option_b'],
                                'C' => $question['option_c'],
                                'D' => $question['option_d']
                            ];
                        ?>
                            <div class="bg-gray-50 rounded-xl border border-gray-200 p-6 hover:border-blue-300 transition">
                                <div class="mb-4">
                                    <span class="inline-block bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-medium mb-3">Question <?php echo $question_num; ?></span>
                                    <div class="text-gray-800 text-lg font-medium">
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
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <?php foreach ($options as $letter => $option_text): 
                                        $option_image_field = 'option_' . strtolower($letter) . '_image';
                                        $has_image = !empty($question[$option_image_field]);
                                    ?>
                                        <div>
                                            <input type="radio" 
                                                   class="hidden peer" 
                                                   name="answers[<?php echo $question['id']; ?>]" 
                                                   id="q<?php echo $question['id']; ?>_<?php echo $letter; ?>" 
                                                   value="<?php echo $letter; ?>" 
                                                   required>
                                            <label class="flex flex-col p-4 border-2 border-gray-200 rounded-lg cursor-pointer text-gray-800 bg-white hover:bg-gray-50 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition" 
                                                   for="q<?php echo $question['id']; ?>_<?php echo $letter; ?>">
                                                <div class="flex items-center">
                                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-blue-100 text-blue-700 text-sm font-medium mr-3"><?php echo $letter; ?></span>
                                                    <span class="text-sm"><?php echo htmlspecialchars($option_text); ?></span>
                                                </div>
                                                <?php if ($has_image): ?>
                                                <div class="mt-3 w-full">
                                                    <?php if (strpos($question[$option_image_field], 'assets:') === 0): ?>
                                                        <img src="../assets/<?php echo htmlspecialchars(substr($question[$option_image_field], 7)); ?>" 
                                                             alt="Option <?php echo $letter; ?> Image" 
                                                             class="max-w-full h-auto rounded-lg border border-gray-100 max-h-48">
                                                    <?php else: ?>
                                                        <img src="../uploads/question_images/<?php echo htmlspecialchars($question[$option_image_field]); ?>" 
                                                             alt="Option <?php echo $letter; ?> Image" 
                                                             class="max-w-full h-auto rounded-lg border border-gray-100 max-h-48">
                                                    <?php endif; ?>
                                                </div>
                                                <?php endif; ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php 
                            $question_num++;
                        endwhile; 
                        ?>
                    </div>

                    <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
                        <button type="button" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition text-sm font-medium flex items-center gap-2" onclick="history.back()">
                            <i class="bi bi-x-lg"></i> Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition text-sm font-medium flex items-center gap-2" id="submitQuiz">
                            <i class="bi bi-check-lg"></i> Submit Quiz
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('quizForm');
        const submitButton = document.getElementById('submitQuiz');

        // Check all questions
        const radioInputs = form.querySelectorAll('input[type="radio"]');
        const totalQuestions = document.querySelectorAll('.bg-gray-50.rounded-xl').length;
        let answeredQuestions = 0;

        radioInputs.forEach(input => {
            input.addEventListener('change', function() {
                const questionId = this.name.match(/\d+/)[0];
                const questionCard = this.closest('.bg-gray-50.rounded-xl');
                const allOptionsInQuestion = questionCard.querySelectorAll('input[type="radio"]');
                let isFirstAnswer = true;

                allOptionsInQuestion.forEach(option => {
                    if (option.checked && isFirstAnswer) {
                        answeredQuestions++;
                        isFirstAnswer = false;
                    }
                });

                updateProgress();
            });
        });

        function updateProgress() {
            console.log(`${answeredQuestions} of ${totalQuestions} questions answered`);
        }

        // Confirm before submitting
        form.addEventListener('submit', function(e) {
            if (answeredQuestions < totalQuestions) {
                e.preventDefault();
                alert('Please answer all questions before submitting.');
                return;
            }

            if (!confirm('Are you sure you want to submit your quiz? You cannot change your answers after submission.')) {
                e.preventDefault();
            } else {
                form.submitted = true;
            }
        });

        // Prevent accidental navigation
        window.addEventListener('beforeunload', function(e) {
            if (answeredQuestions > 0 && !form.submitted) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    });
    </script>
</body>
</html>
