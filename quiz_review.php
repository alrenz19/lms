<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$quiz_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Get quiz details and verify completion
$stmt = $conn->prepare("
    SELECT q.*, c.id as course_id, up.user_answers, up.score, up.completed
    FROM quizzes q
    JOIN courses c ON q.course_id = c.id
    LEFT JOIN user_progress up ON q.id = up.quiz_id AND up.user_id = ?
    WHERE q.id = ?
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
$stmt = $conn->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY id");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Properly decode user answers with null check
$user_answers = !empty($quiz['user_answers']) ? json_decode($quiz['user_answers'], true) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz Review - <?php echo htmlspecialchars($quiz['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4><?php echo htmlspecialchars($quiz['title']); ?> - Review</h4>
                <a href="view_course.php?id=<?php echo $quiz['course_id']; ?>" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Back to Course
                </a>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    Your Score: <?php echo $quiz['score']; ?> out of <?php echo count($questions); ?>
                </div>
                
                <?php foreach ($questions as $index => $question): ?>
                    <?php 
                    $user_answer = isset($user_answers[$question['id']]) ? $user_answers[$question['id']] : '';
                    $is_correct = $user_answer === $question['correct_answer'];
                    ?>
                    <div class="mb-4 p-3 border rounded <?php echo !empty($user_answer) ? ($is_correct ? 'border-success' : 'border-danger') : ''; ?>">
                        <h5>Question <?php echo $index + 1; ?></h5>
                        <p><?php echo htmlspecialchars($question['question_text']); ?></p>
                        
                        <?php
                        $options = [
                            'A' => $question['option_a'],
                            'B' => $question['option_b'],
                            'C' => $question['option_c'],
                            'D' => $question['option_d']
                        ];
                        
                        foreach ($options as $letter => $option): ?>
                            <div class="p-2 rounded mb-2 <?php 
                                if ($letter === $user_answer && $letter === $question['correct_answer']) {
                                    echo 'bg-success text-white';
                                } elseif ($letter === $user_answer) {
                                    echo 'bg-danger text-white';
                                } elseif ($letter === $question['correct_answer']) {
                                    echo 'bg-success bg-opacity-25';
                                }
                            ?>">
                                <?php echo $letter; ?>. <?php echo htmlspecialchars($option); ?>
                                <?php if ($letter === $user_answer): ?>
                                    <span class="badge bg-light text-dark ms-2">Your Answer</span>
                                <?php endif; ?>
                                <?php if ($letter === $question['correct_answer']): ?>
                                    <i class="bi bi-check-circle-fill ms-2"></i>
                                <?php elseif ($letter === $user_answer): ?>
                                    <i class="bi bi-x-circle-fill ms-2"></i>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>

                        <?php if (!empty($user_answer) && !$is_correct): ?>
                            <div class="mt-3 p-3 bg-light rounded">
                                <div class="text-danger mb-2">
                                    <i class="bi bi-x-circle-fill"></i>
                                    <strong>Your incorrect answer: </strong>
                                    <?php echo $user_answer; ?> - <?php echo htmlspecialchars($options[$user_answer]); ?>
                                </div>
                                <div class="text-success">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <strong>Correct answer: </strong>
                                    <?php echo $question['correct_answer']; ?> - <?php echo htmlspecialchars($options[$question['correct_answer']]); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
