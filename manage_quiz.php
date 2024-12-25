<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$course_id = $_GET['course_id'] ?? 0;
$current_quiz_id = $_GET['quiz_id'] ?? 0;

// Get course details
$stmt = $conn->prepare("SELECT title FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    header("Location: manage_courses.php");
    exit;
}

// Get available quizzes for the course
$stmt = $conn->prepare("SELECT * FROM quizzes WHERE course_id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$quizzes = $stmt->get_result();

// If no quiz_id is specified, use the first quiz
if (!$current_quiz_id && $quizzes->num_rows > 0) {
    $first_quiz = $quizzes->fetch_assoc();
    $current_quiz_id = $first_quiz['id'];
    $quizzes->data_seek(0);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_question'])) {
    try {
        $conn->begin_transaction();
        
        $question = $conn->real_escape_string($_POST['question']);
        $option_a = $conn->real_escape_string($_POST['option_a']);
        $option_b = $conn->real_escape_string($_POST['option_b']);
        $option_c = $conn->real_escape_string($_POST['option_c']);
        $option_d = $conn->real_escape_string($_POST['option_d']);
        $correct = $_POST['correct_answer'];

        $stmt = $conn->prepare("INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_answer) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $current_quiz_id, $question, $option_a, $option_b, $option_c, $option_d, $correct);
        $stmt->execute();
        
        $conn->commit();
        $success = "Question added successfully";
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error adding question: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - Manage Quiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="content">
            <div class="container mt-4">
                <?php if ($quizzes->num_rows == 0): ?>
                    <div class="alert alert-warning">
                        Please create a quiz first before adding questions.
                        <a href="manage_courses.php" class="btn btn-primary btn-sm ms-2">Back to Courses</a>
                    </div>
                <?php else: ?>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <a href="manage_courses.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Courses
                        </a>
                        <h2><?php echo htmlspecialchars($course['title']); ?> - Quiz Management</h2>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <select class="form-select" onchange="window.location.href='manage_quiz.php?course_id=<?php echo $course_id; ?>&quiz_id=' + this.value">
                                <?php
                                $quizzes->data_seek(0);
                                while ($quiz = $quizzes->fetch_assoc()) {
                                    $selected = $quiz['id'] == $current_quiz_id ? 'selected' : '';
                                    echo "<option value='{$quiz['id']}' {$selected}>{$quiz['title']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 text-end">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
                                <i class="bi bi-plus-circle"></i> Add Question
                            </button>
                        </div>
                    </div>

                    <?php if ($current_quiz_id): ?>
                    <div class="accordion" id="quizAccordion">
                        <?php
                        $result = $conn->query("SELECT * FROM questions WHERE quiz_id = $current_quiz_id ORDER BY id");
                        $question_num = 1;
                        while ($row = $result->fetch_assoc()): 
                        ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#question<?php echo $row['id']; ?>">
                                    Question <?php echo $question_num++; ?>: <?php echo htmlspecialchars($row['question_text']); ?>
                                </button>
                            </h2>
                            <div id="question<?php echo $row['id']; ?>" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>A:</strong> <?php echo htmlspecialchars($row['option_a']); ?></p>
                                            <p><strong>B:</strong> <?php echo htmlspecialchars($row['option_b']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>C:</strong> <?php echo htmlspecialchars($row['option_c']); ?></p>
                                            <p><strong>D:</strong> <?php echo htmlspecialchars($row['option_d']); ?></p>
                                        </div>
                                    </div>
                                    <p class="text-success">
                                        <strong>Correct Answer:</strong> <?php echo $row['correct_answer']; ?>
                                    </p>
                                    <div class="mt-2">
                                        <button class="btn btn-warning btn-sm" onclick="editQuestion(<?php echo $row['id']; ?>)">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="deleteQuestion(<?php echo $row['id']; ?>)">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Question Modal -->
    <div class="modal fade" id="addQuestionModal" tabindex="-1" aria-labelledby="addQuestionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addQuestionModalLabel">Add New Question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="question" class="form-label">Question</label>
                            <textarea class="form-control" id="question" name="question" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="option_a" class="form-label">Option A</label>
                            <input type="text" class="form-control" id="option_a" name="option_a" required>
                        </div>
                        <div class="mb-3">
                            <label for="option_b" class="form-label">Option B</label>
                            <input type="text" class="form-control" id="option_b" name="option_b" required>
                        </div>
                        <div class="mb-3">
                            <label for="option_c" class="form-label">Option C</label>
                            <input type="text" class="form-control" id="option_c" name="option_c" required>
                        </div>
                        <div class="mb-3">
                            <label for="option_d" class="form-label">Option D</label>
                            <input type="text" class="form-control" id="option_d" name="option_d" required>
                        </div>
                        <div class="mb-3">
                            <label for="correct_answer" class="form-label">Correct Answer</label>
                            <select class="form-select" id="correct_answer" name="correct_answer" required>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                        </div>
                        <button type="submit" name="add_question" class="btn btn-primary">Add Question</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteQuestion(id) {
            if (confirm('Are you sure you want to delete this question?')) {
                window.location.href = 'delete_question.php?id=' + id + '&course_id=<?php echo $course_id; ?>';
            }
        }
    </script>
</body>
</html>
