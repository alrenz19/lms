<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$course_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Validate course
$stmt = $conn->prepare("SELECT title FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    header("Location: view_courses.php");
    exit;
}

// Check progress
$stmt = $conn->prepare("SELECT completed FROM user_progress WHERE user_id = ? AND course_id = ?");
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$progress = $stmt->get_result()->fetch_assoc();

if ($progress) {
    $stmt = $conn->prepare("SELECT attempts, progress_percentage FROM user_progress WHERE user_id = ? AND course_id = ?");
    $stmt->bind_param("ii", $user_id, $course_id);
    $stmt->execute();
    $quiz_data = $stmt->get_result()->fetch_assoc();

    if ($quiz_data) {
        if ($quiz_data['progress_percentage'] >= 70) {
            // Already passed the quiz
            header("Location: view_course.php?id={$course_id}&passed");
            exit;
        } elseif ($quiz_data['attempts'] >= 3) {
            // Used all attempts
            header("Location: view_course.php?id={$course_id}&max_attempts");
            exit;
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score = 0;
    $answers = $_POST['answers'] ?? [];
    $total_questions = count($answers);
    $submitted_answers = [];
    $questions_id = [];

    if ($total_questions > 0) {
        $question_ids = array_keys($answers);
        $placeholders = implode(',', array_fill(0, count($question_ids), '?'));
        $types = str_repeat('i', count($question_ids)) . 'i';
        $params = [...$question_ids, $course_id];

        $stmt = $conn->prepare("SELECT id, correct_answer FROM questions WHERE id IN ($placeholders) AND course_id = ?");
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $correct_answers = [];
        while ($row = $result->fetch_assoc()) {
            $correct_answers[$row['id']] = $row['correct_answer'];
        }

        foreach ($answers as $question_id => $user_answer) {
            $submitted_answers[$question_id] = $user_answer;
            $questions_id[] = $question_id;
            if (isset($correct_answers[$question_id]) && strtolower($correct_answers[$question_id]) === strtolower($user_answer)) {
                $score++;
            }
        }
    }

    $percentage = $total_questions > 0 ? ($score / $total_questions) * 100 : 0;
    $answers_json = json_encode($submitted_answers);

    // 🔹 Check existing progress for attempts
    $stmt = $conn->prepare("SELECT id, attempts FROM user_progress WHERE user_id = ? AND course_id = ?");
    $stmt->bind_param("ii", $user_id, $course_id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();

    $attempts = $existing ? $existing['attempts'] + 1 : 1;
    $can_retake = $attempts < 3 && $percentage < 70;
    $completed = ($percentage >= 70) || ($attempts >= 3);

    if ($existing) {
    $stmt = $conn->prepare("
        UPDATE user_progress 
        SET question_id = ?, 
            score = ?, 
            total_score = ?, 
            progress_percentage = ?, 
            completed = ?, 
            user_answers = ?, 
            attempts = ? 
        WHERE user_id = ? AND course_id = ?
    ");
    $stmt->bind_param(
        "iddidssii", 
        $question_id,      // we'll define this below
        $score, 
        $total_questions, 
        $percentage, 
        $completed, 
        $answers_json, 
        $attempts, 
        $user_id, 
        $course_id
    );
} else {
    $question_id = !empty($questions_id[0]) ? $questions_id[0] : null; // ✅ pick the first question_id
    $stmt = $conn->prepare("
        INSERT INTO user_progress 
        (user_id, course_id, question_id, score, total_score, progress_percentage, completed, user_answers, attempts) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "iiiiddssi", 
        $user_id, 
        $course_id, 
        $question_id, 
        $score, 
        $total_questions, 
        $percentage, 
        $completed, 
        $answers_json, 
        $attempts
    );
}



    $stmt->execute();

    // Save session for review
    $_SESSION['quiz_results'] = [
        'course_id' => $course_id,
        'score' => $score,
        'total' => $total_questions,
        'percentage' => $percentage,
        'attempts' => $attempts,
        'can_retake' => $can_retake,
        'completed' => $completed
    ];

    // 🔸 Redirect to review page with query string
    header("Location: quiz_review.php?id={$course_id}&attempts={$attempts}&score={$percentage}");
    exit;
}



$questions = $conn->query("SELECT * FROM questions WHERE course_id = $course_id ORDER BY RAND()");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Exam - <?php echo htmlspecialchars($course['title']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <!--<link rel="stylesheet" href="./public/css/tailwind.min.css" />-->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<style>
/* Radio Input Styling */
.quiz-option {
    position: relative;
    margin-bottom: 10px;
}

.quiz-option input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.quiz-option label {
    display: flex;
    flex-direction: column;
    padding: 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    cursor: pointer;
    background: white;
    transition: all 0.2s ease;
}

.quiz-option label:hover {
    background: #f9fafb;
}

.quiz-option input[type="radio"]:checked + label {
    border-color: #3b82f6;
    background: #eff6ff;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

.option-letter {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.75rem;
    height: 1.75rem;
    border-radius: 9999px;
    background: #dbeafe;
    color: #1d4ed8;
    font-size: 0.875rem;
    font-weight: 500;
    margin-right: 0.75rem;
}

/* Submit Button Styling */
#submitQuiz {
    background: #3b82f6;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-weight: 500;
    transition: background 0.2s ease;
}

#submitQuiz:hover {
    background: #2563eb;
}

#submitQuiz:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background: #9ca3af;
}
</style>
<body class="bg-gray-50">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="p-8 sm:ml-72">
        <div class="container mx-auto">
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl p-8 shadow-md mb-8">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-white mb-2">Course Exam</h1>
                        <p class="text-blue-100 flex items-center gap-2">
                            <i class="bi bi-book"></i>
                            <span><?php echo htmlspecialchars($course['title']); ?></span>
                        </p>
                    </div>
                    <a href="view_course.php?id=<?php echo $course_id; ?>" 
                       class="px-4 py-2 bg-transparent border border-white text-white rounded-lg hover:bg-white/10 flex items-center gap-2 transition text-sm font-medium">
                        <i class="bi bi-arrow-left"></i> Back to Course
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <form method="POST" class="quiz-form" id="quizForm">
                    <div class="space-y-6">
                        <?php
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
                                            <img src="serve_video.php?file=<?php echo htmlspecialchars(substr($question['question_image'], 7)); ?>" 
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
                                        <div class="quiz-option">
                                            <input type="radio" 
                                                name="answers[<?php echo $question['id']; ?>]" 
                                                id="q<?php echo $question['id']; ?>_<?php echo $letter; ?>" 
                                                value="<?php echo $letter; ?>" 
                                                required>
                                            <label for="q<?php echo $question['id']; ?>_<?php echo $letter; ?>">
                                                <div class="flex items-center">
                                                    <span class="option-letter"><?php echo $letter; ?></span>
                                                    <span class="text-sm"><?php echo htmlspecialchars($option_text); ?></span>
                                                </div>
                                                <?php if ($has_image): ?>
                                                    <div class="mt-3 w-full">
                                                        <img src="../uploads/question_images/<?php echo htmlspecialchars($question[$option_image_field]); ?>" 
                                                            alt="Option <?php echo $letter; ?> Image" 
                                                            class="max-w-full h-auto rounded-lg border border-gray-100 max-h-48">
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
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('quizForm');
        const submitButton = document.getElementById('submitQuiz');
        const totalQuestions = document.querySelectorAll('.bg-gray-50.rounded-xl').length;

        // Disable submit by default
        submitButton.disabled = true;
        submitButton.classList.add('opacity-50', 'cursor-not-allowed');

        function countAnsweredQuestions() {
            const answered = new Set();
            const radioInputs = form.querySelectorAll('input[type="radio"]:checked');

            radioInputs.forEach(input => {
                const match = input.name.match(/\d+/); // extract question ID
                if (match) {
                    answered.add(match[0]);
                }
            });

            return answered.size;
        }

        function updateSubmitState() {
            const answeredCount = countAnsweredQuestions();
            if (answeredCount === totalQuestions) {
                submitButton.disabled = false;
                submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                submitButton.disabled = true;
                submitButton.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        // Monitor changes
        form.querySelectorAll('input[type="radio"]').forEach(input => {
            input.addEventListener('change', updateSubmitState);
        });

        // Recheck state in case browser autofills
        updateSubmitState();

        // Confirm on submit
        form.addEventListener('submit', function (e) {
            if (countAnsweredQuestions() < totalQuestions) {
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

        // Warn on accidental navigation
        window.addEventListener('beforeunload', function (e) {
            if (!form.submitted && countAnsweredQuestions() > 0) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    });
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Lucide icons
        lucide.createIcons();
    });
    </script>

</body>
</html>
