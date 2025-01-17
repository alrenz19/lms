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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .quiz-header {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            color: white;
        }

        .quiz-title {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .course-title {
            font-size: 1rem;
            opacity: 0.9;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
        }

        .quiz-content {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .question-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.2s;
        }

        .question-card:hover {
            border-color: #6366f1;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .question-header {
            margin-bottom: 1.5rem;
        }

        .question-number {
            display: inline-block;
            background: rgba(99, 102, 241, 0.1);
            color: #6366f1;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.75rem;
        }

        .question-text {
            font-size: 1.125rem;
            font-weight: 500;
            color: #111827;
            line-height: 1.5;
        }

        .options-list {
            display: grid;
            gap: 1rem;
        }

        .option-item {
            width: 100%;
        }

        .btn-check:checked + .btn-outline-primary {
            background-color: #6366f1;
            border-color: #6366f1;
            color: white;
        }

        .btn-outline-primary {
            color: #6366f1;
            border-color: #e5e7eb;
            background: white;
            padding: 1rem;
            transition: all 0.2s;
        }

        .btn-outline-primary:hover {
            background-color: #f9fafb;
            border-color: #6366f1;
            color: #6366f1;
            transform: translateY(-1px);
        }

        .option-letter {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            background: rgba(99, 102, 241, 0.1);
            color: #6366f1;
            border-radius: 9999px;
            font-weight: 500;
            margin-right: 0.75rem;
        }

        .btn-check:checked + .btn-outline-primary .option-letter {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .option-text {
            font-weight: 400;
        }

        .quiz-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: #6366f1;
            border-color: #6366f1;
        }

        .btn-primary:hover {
            background-color: #4f46e5;
            border-color: #4f46e5;
            transform: translateY(-1px);
        }

        .btn-outline-secondary {
            color: #6b7280;
            border-color: #e5e7eb;
        }

        .btn-outline-secondary:hover {
            background-color: #f9fafb;
            border-color: #9ca3af;
            color: #4b5563;
            transform: translateY(-1px);
        }

        @media (min-width: 640px) {
            .options-list {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="content">
            <div class="container mt-4">
                <div class="quiz-header">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="quiz-title"><?php echo htmlspecialchars($quiz['title']); ?></h1>
                            <p class="course-title">
                                <i class="bi bi-book"></i>
                                <?php echo htmlspecialchars($quiz['course_title']); ?>
                            </p>
                        </div>
                        <a href="view_course.php?id=<?php echo $quiz['course_id']; ?>" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-left"></i> Back to Course
                        </a>
                    </div>
                </div>

                <div class="quiz-content">
                    <form method="POST" class="quiz-form" id="quizForm">
                        <div class="question-list">
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
                                <div class="question-card mb-4">
                                    <div class="question-header">
                                        <span class="question-number">Question <?php echo $question_num; ?></span>
                                        <div class="question-text">
                                            <?php echo htmlspecialchars($question['question_text']); ?>
                                        </div>
                                    </div>
                                    
                                    <div class="options-list">
                                        <?php foreach ($options as $letter => $option_text): ?>
                                            <div class="option-item">
                                                <input type="radio" 
                                                       class="btn-check" 
                                                       name="answers[<?php echo $question['id']; ?>]" 
                                                       id="q<?php echo $question['id']; ?>_<?php echo $letter; ?>" 
                                                       value="<?php echo $letter; ?>" 
                                                       required>
                                                <label class="btn btn-outline-primary w-100 text-start" 
                                                       for="q<?php echo $question['id']; ?>_<?php echo $letter; ?>">
                                                    <span class="option-letter"><?php echo $letter; ?></span>
                                                    <span class="option-text"><?php echo htmlspecialchars($option_text); ?></span>
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

                        <div class="quiz-actions">
                            <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
                                <i class="bi bi-x-lg"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary" id="submitQuiz">
                                <i class="bi bi-check-lg"></i> Submit Quiz
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('quizForm');
        const submitButton = document.getElementById('submitQuiz');

        // Update progress as user answers questions
        const radioInputs = form.querySelectorAll('input[type="radio"]');
        const totalQuestions = document.querySelectorAll('.question-card').length;
        let answeredQuestions = 0;

        radioInputs.forEach(input => {
            input.addEventListener('change', function() {
                const questionId = this.name.match(/\d+/)[0];
                const questionCard = this.closest('.question-card');
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
            const progress = (answeredQuestions / totalQuestions) * 100;
            document.querySelector('.progress-bar').style.width = progress + '%';
            document.querySelector('.progress-text').textContent = 
                `${answeredQuestions} of ${totalQuestions} questions answered`;
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
