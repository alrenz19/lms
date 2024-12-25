<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$quiz_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Get quiz details
$stmt = $conn->prepare("SELECT q.*, c.title as course_title FROM quizzes q JOIN courses c ON q.course_id = c.id WHERE q.id = ?");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $score = 0;
    $total_questions = 0;
    $answers = [];
    
    foreach ($_POST['answers'] as $question_id => $answer) {
        $stmt = $conn->prepare("SELECT correct_answer FROM questions WHERE id = ?");
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        $is_correct = ($result['correct_answer'] === $answer);
        if ($is_correct) {
            $score++;
        }
        
        // Insert or update user progress with is_correct flag
        $stmt = $conn->prepare("INSERT INTO user_progress (user_id, quiz_id, score, progress_percentage, is_correct) 
                               VALUES (?, ?, ?, ?, ?) 
                               ON DUPLICATE KEY UPDATE 
                               score = ?, progress_percentage = ?, is_correct = ?");
        $percentage = ($score / count($_POST['answers'])) * 100;
        $stmt->bind_param("iiiddidi", 
            $user_id, 
            $quiz_id, 
            $score, 
            $percentage, 
            $is_correct,
            $score, 
            $percentage,
            $is_correct
        );
        $stmt->execute();
        
        $answers[$question_id] = $answer;
        $total_questions++;
    }
    
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
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="content">
            <div class="container mt-4">
                <div class="admin-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-pencil-square"></i> <?php echo htmlspecialchars($quiz['title']); ?></h2>
                        <a href="view_course.php?id=<?php echo $quiz['course_id']; ?>" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-left"></i> Back to Course
                        </a>
                    </div>

                    <form method="POST" class="quiz-form">
                        <?php
                        $questions = $conn->query("SELECT * FROM questions WHERE quiz_id = $quiz_id");
                        $question_num = 1;
                        while ($question = $questions->fetch_assoc()): ?>
                            <div class="course-item mb-4">
                                <h5 class="mb-3">Question <?php echo $question_num; ?></h5>
                                <p class="mb-4"><?php echo htmlspecialchars($question['question_text']); ?></p>
                                
                                <div class="list-group">
                                    <?php foreach (['a', 'b', 'c', 'd'] as $option): ?>
                                        <label class="list-group-item list-group-item-action">
                                            <input type="radio" 
                                                   name="answers[<?php echo $question['id']; ?>]" 
                                                   value="<?php echo strtoupper($option); ?>" 
                                                   required 
                                                   class="me-2">
                                            <?php echo htmlspecialchars($question['option_' . $option]); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php 
                            $question_num++;
                        endwhile; 
                        ?>
                        
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary action-button">
                                <i class="bi bi-check-circle"></i> Submit Quiz
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
