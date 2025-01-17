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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results - <?php echo htmlspecialchars($quiz['title']); ?></title>
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

        .page-title {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .quiz-info {
            font-size: 1rem;
            opacity: 0.9;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .separator {
            color: rgba(255, 255, 255, 0.5);
        }

        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            height: 100%;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.2s;
        }

        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.1);
        }

        .stats-icon {
            width: 48px;
            height: 48px;
            background: rgba(99, 102, 241, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .stats-icon i {
            font-size: 24px;
            color: #6366f1;
        }

        .stats-info h3 {
            font-size: 0.875rem;
            font-weight: 500;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .stats-info h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #111827;
            margin: 0;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 1.5rem;
        }

        .question-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .question-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .question-number {
            font-size: 0.875rem;
            font-weight: 500;
            color: #6366f1;
        }

        .question-result {
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .question-result.correct {
            color: #059669;
        }

        .question-result.incorrect {
            color: #dc2626;
        }

        .question-text {
            font-size: 1rem;
            color: #111827;
            line-height: 1.5;
        }

        .answer-option {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            margin-bottom: 0.75rem;
            transition: all 0.2s;
        }

        .answer-option:last-child {
            margin-bottom: 0;
        }

        .answer-option.correct {
            background: #ecfdf5;
            border-color: #059669;
        }

        .answer-option.incorrect {
            background: #fef2f2;
            border-color: #dc2626;
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

        .answer-option.correct .option-letter {
            background: rgba(5, 150, 105, 0.1);
            color: #059669;
        }

        .answer-option.incorrect .option-letter {
            background: rgba(220, 38, 38, 0.1);
            color: #dc2626;
        }

        .option-text {
            font-weight: 400;
        }

        .results-content {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .btn-outline-light {
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
        }

        .btn-outline-light:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: white;
            color: white;
        }

        @media (min-width: 768px) {
            .stats-card {
                margin-bottom: 0;
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
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="page-title">Quiz Results</h1>
                            <p class="quiz-info">
                                <span class="quiz-name"><?php echo htmlspecialchars($quiz['title']); ?></span>
                                <span class="separator">•</span>
                                <span class="course-name"><?php echo htmlspecialchars($quiz['course_title']); ?></span>
                            </p>
                        </div>
                        <a href="view_course.php?id=<?php echo $quiz['course_id']; ?>" class="btn btn-outline-light">
                            <i class="bi bi-arrow-left"></i> Back to Course
                        </a>
                    </div>
                </div>

                <div class="results-overview">
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="stats-card">
                                <div class="stats-icon">
                                    <i class="bi bi-trophy-fill"></i>
                                </div>
                                <div class="stats-info">
                                    <h3>Final Score</h3>
                                    <h2><?php echo $final_score; ?>%</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="stats-card">
                                <div class="stats-icon">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                                <div class="stats-info">
                                    <h3>Correct Answers</h3>
                                    <h2><?php echo $correct_answers; ?> / <?php echo $total_questions; ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="stats-card">
                                <div class="stats-icon">
                                    <i class="bi bi-clock-fill"></i>
                                </div>
                                <div class="stats-info">
                                    <h3>Time Taken</h3>
                                    <h2><?php echo gmdate("i:s", $results['time_taken']); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="results-content">
                    <h2 class="section-title">Detailed Review</h2>
                    <div class="question-list">
                        <?php foreach ($questions as $index => $question): 
                            $isCorrect = $question['correct_answer'] === $question['user_answer'];
                            $options = [
                                'A' => $question['option_a'],
                                'B' => $question['option_b'],
                                'C' => $question['option_c'],
                                'D' => $question['option_d']
                            ];
                        ?>
                            <div class="question-card mb-4">
                                <div class="question-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="question-number">Question <?php echo $index + 1; ?></span>
                                        <span class="question-result <?php echo $isCorrect ? 'correct' : 'incorrect'; ?>">
                                            <?php if ($isCorrect): ?>
                                                <i class="bi bi-check-circle-fill"></i> Correct
                                            <?php else: ?>
                                                <i class="bi bi-x-circle-fill"></i> Incorrect
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="question-text mt-2">
                                        <?php echo htmlspecialchars($question['question_text']); ?>
                                    </div>
                                </div>
                                <div class="options-list p-3">
                                    <?php foreach ($options as $letter => $option_text): 
                                        $optionClass = '';
                                        if ($letter === $question['correct_answer']) {
                                            $optionClass = 'correct';
                                        } elseif ($letter === $question['user_answer'] && !$isCorrect) {
                                            $optionClass = 'incorrect';
                                        }
                                    ?>
                                        <div class="answer-option <?php echo $optionClass; ?>">
                                            <div>
                                                <span class="option-letter"><?php echo $letter; ?></span>
                                                <span class="option-text"><?php echo htmlspecialchars($option_text); ?></span>
                                            </div>
                                            <?php if ($optionClass === 'correct'): ?>
                                                <i class="bi bi-check-circle-fill text-success"></i>
                                            <?php elseif ($optionClass === 'incorrect'): ?>
                                                <i class="bi bi-x-circle-fill text-danger"></i>
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
