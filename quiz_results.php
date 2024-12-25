<?php
session_start();
require_once 'config.php';

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
foreach ($results['answers'] as $question_id => $user_answer) {
    $stmt = $conn->prepare("SELECT * FROM questions WHERE id = ?");
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $question = $stmt->get_result()->fetch_assoc();
    $question['user_answer'] = $user_answer;
    $questions[] = $question;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results - <?php echo htmlspecialchars($quiz['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="content">
            <div class="container mt-4">
                <div class="admin-card mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-check-circle"></i> Quiz Results</h2>
                        <a href="view_course.php?id=<?php echo $quiz['course_id']; ?>" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-left"></i> Back to Course
                        </a>
                    </div>

                    <div class="stats-card dashboard-card mb-4">
                        <div class="card-body text-center">
                            <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
                            <div class="mt-4">
                                <div class="display-4 text-white mb-3">
                                    <?php echo $results['score']; ?> / <?php echo $results['total']; ?>
                                </div>
                                <div class="progress mb-3" style="height: 25px;">
                                    <div class="progress-bar <?php echo ($results['score']/$results['total']) >= 0.7 ? 'bg-success' : 'bg-primary'; ?>" 
                                         role="progressbar" 
                                         style="width: <?php echo ($results['score']/$results['total'])*100; ?>%">
                                        <?php echo round(($results['score']/$results['total'])*100); ?>%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Questions Review -->
                    <div class="course-list">
                        <?php foreach ($questions as $index => $question): ?>
                            <div class="course-item">
                                <h5 class="mb-3">Question <?php echo $index + 1; ?></h5>
                                <p class="mb-4"><?php echo htmlspecialchars($question['question_text']); ?></p>
                                
                                <div class="list-group">
                                    <?php foreach (['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d'] as $letter => $option): ?>
                                        <div class="list-group-item <?php 
                                            if ($letter === $question['correct_answer']) echo 'list-group-item-success';
                                            else if ($letter === $question['user_answer'] && $letter !== $question['correct_answer']) echo 'list-group-item-danger';
                                        ?>">
                                            <?php echo htmlspecialchars($question[$option]); ?>
                                            <?php if ($letter === $question['correct_answer']): ?>
                                                <i class="bi bi-check-circle-fill text-success float-end"></i>
                                            <?php endif; ?>
                                            <?php if ($letter === $question['user_answer'] && $letter !== $question['correct_answer']): ?>
                                                <i class="bi bi-x-circle-fill text-danger float-end"></i>
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
