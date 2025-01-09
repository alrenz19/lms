<?php
session_start();
require_once '../config.php';

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
        
        $new_question_id = $conn->insert_id; // Get the ID of the newly inserted question
        $_SESSION['new_question_id'] = $new_question_id; // Store in session
        
        $conn->commit();
        $success = "Question added successfully";
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error adding question: " . $e->getMessage();
    }
}

// Get the new question ID from session and clear it
$new_question_id = $_SESSION['new_question_id'] ?? null;
unset($_SESSION['new_question_id']);
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
    <link rel="stylesheet" href="assets/css/dashboard.css">
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

        .quiz-selector {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .form-select {
            border-radius: 8px;
            border: 1px solid #d1d5db;
            padding: 0.75rem;
            font-size: 0.875rem;
            background-color: #f9fafb;
            transition: all 0.2s;
        }

        .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .action-button {
            background-color: #6366f1;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .action-button:hover {
            background-color: #4f46e5;
            transform: translateY(-1px);
        }

        .question-list {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .question-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.2s;
        }

        .question-card:hover {
            border-color: #6366f1;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .question-number {
            font-size: 0.875rem;
            font-weight: 600;
            color: #6366f1;
            background: rgba(99, 102, 241, 0.1);
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
        }

        .question-text {
            font-weight: 500;
            color: #111827;
            margin-bottom: 1rem;
        }

        .options-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .option-item {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 0.75rem;
        }

        .option-label {
            font-weight: 500;
            color: #6366f1;
            margin-right: 0.5rem;
        }

        .correct-answer {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #ecfdf5;
            color: #059669;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 500;
        }

        .question-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .btn-edit {
            background-color: #fbbf24;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .btn-edit:hover {
            background-color: #f59e0b;
            color: white;
        }

        .btn-delete {
            background-color: #ef4444;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .btn-delete:hover {
            background-color: #dc2626;
            color: white;
        }

        .modal-content {
            border-radius: 12px;
            border: none;
        }

        .modal-header {
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            border-radius: 12px 12px 0 0;
            padding: 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid #e5e7eb;
            padding: 1.5rem;
            border-radius: 0 0 12px 12px;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #d1d5db;
            padding: 0.75rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .input-group-text {
            background-color: #f9fafb;
            border: 1px solid #d1d5db;
            border-radius: 8px 0 0 8px;
        }

        .option-group {
            transition: all 0.3s ease;
        }

        .option-group:hover {
            transform: translateX(4px);
        }

        .alert {
            border: none;
            border-radius: 8px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background-color: #ecfdf5;
            color: #059669;
        }

        .alert-warning {
            background-color: #fffbeb;
            color: #92400e;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="content">
            <div class="container mt-4">
                <?php if ($quizzes->num_rows == 0): ?>
                    <div class="quiz-header">
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            Please create a quiz first before adding questions.
                            <a href="manage_courses.php" class="btn btn-light btn-sm ms-2">Back to Courses</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="quiz-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="page-title">
                                    <i class="bi bi-question-circle"></i> Quiz Management
                                </h1>
                                <p class="mb-0"><?php echo htmlspecialchars($course['title']); ?></p>
                            </div>
                            <a href="manage_courses.php" class="btn btn-outline-light">
                                <i class="bi bi-arrow-left"></i> Back to Courses
                            </a>
                        </div>
                    </div>

                    <?php if (isset($success) || isset($error)): ?>
                        <div class="alert alert-<?php echo isset($success) ? 'success' : 'warning'; ?>">
                            <i class="bi bi-<?php echo isset($success) ? 'check-circle' : 'exclamation-triangle'; ?>-fill"></i>
                            <?php echo isset($success) ? $success : $error; ?>
                        </div>
                    <?php endif; ?>

                    <div class="quiz-selector">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <label class="form-label mb-2">Select Quiz</label>
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
                                <button class="action-button" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
                                    <i class="bi bi-plus-circle"></i> Add Question
                                </button>
                            </div>
                        </div>
                    </div>

                    <?php if ($current_quiz_id): ?>
                        <div class="question-list">
                            <?php
                            $result = $conn->query("SELECT * FROM questions WHERE quiz_id = $current_quiz_id ORDER BY id");
                            $question_num = 1;
                            while ($row = $result->fetch_assoc()): 
                                $is_new_question = ($row['id'] == $new_question_id);
                            ?>
                                <div class="question-card <?php echo $is_new_question ? 'border-primary' : ''; ?>">
                                    <div class="question-header">
                                        <span class="question-number">Question <?php echo $question_num++; ?></span>
                                    </div>
                                    <div class="question-text">
                                        <?php echo htmlspecialchars($row['question_text']); ?>
                                    </div>
                                    <div class="options-grid">
                                        <div class="option-item">
                                            <span class="option-label">A:</span>
                                            <?php echo htmlspecialchars($row['option_a']); ?>
                                        </div>
                                        <div class="option-item">
                                            <span class="option-label">B:</span>
                                            <?php echo htmlspecialchars($row['option_b']); ?>
                                        </div>
                                        <div class="option-item">
                                            <span class="option-label">C:</span>
                                            <?php echo htmlspecialchars($row['option_c']); ?>
                                        </div>
                                        <div class="option-item">
                                            <span class="option-label">D:</span>
                                            <?php echo htmlspecialchars($row['option_d']); ?>
                                        </div>
                                    </div>
                                    <div class="correct-answer">
                                        <i class="bi bi-check-circle-fill"></i>
                                        Correct Answer: <?php echo $row['correct_answer']; ?>
                                    </div>
                                    <div class="question-actions">
                                        <button class="btn-edit" onclick="editQuestion(<?php echo $row['id']; ?>)">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <button class="btn-delete" onclick="deleteQuestion(<?php echo $row['id']; ?>)">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
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
    <div class="modal fade" id="addQuestionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle"></i> Add New Question
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="addQuestionForm">
                    <div class="modal-body">
                        <input type="hidden" name="add_question" value="1">
                        <div class="mb-4">
                            <label for="question_text" class="form-label">Question Text</label>
                            <textarea class="form-control" 
                                    id="question_text" 
                                    name="question" 
                                    rows="3" 
                                    required 
                                    minlength="10"
                                    placeholder="Enter your question here..."></textarea>
                            <div class="form-text">Question should be clear and concise.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Answer Options</label>
                            <div class="option-group mb-3">
                                <div class="input-group mb-3">
                                    <div class="input-group-text">
                                        <input type="radio" name="correct_answer" value="A" required>
                                    </div>
                                    <input type="text" class="form-control" name="option_a" placeholder="Option A" required>
                                </div>
                                <div class="input-group mb-3">
                                    <div class="input-group-text">
                                        <input type="radio" name="correct_answer" value="B" required>
                                    </div>
                                    <input type="text" class="form-control" name="option_b" placeholder="Option B" required>
                                </div>
                                <div class="input-group mb-3">
                                    <div class="input-group-text">
                                        <input type="radio" name="correct_answer" value="C" required>
                                    </div>
                                    <input type="text" class="form-control" name="option_c" placeholder="Option C" required>
                                </div>
                                <div class="input-group mb-3">
                                    <div class="input-group-text">
                                        <input type="radio" name="correct_answer" value="D" required>
                                    </div>
                                    <input type="text" class="form-control" name="option_d" placeholder="Option D" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i> Add Question
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteQuestion(id) {
            if (confirm('Are you sure you want to delete this question? This action cannot be undone.')) {
                window.location.href = 'delete_question.php?id=' + id + '&course_id=<?php echo $course_id; ?>';
            }
        }

        function editQuestion(id) {
            window.location.href = 'edit_question.php?id=' + id + '&course_id=<?php echo $course_id; ?>';
        }

        // Add this to handle modal closing after successful submission
        <?php if (isset($success)): ?>
            var addQuestionModal = document.getElementById('addQuestionModal');
            var modal = bootstrap.Modal.getInstance(addQuestionModal);
            if (modal) {
                modal.hide();
            }
        <?php endif; ?>

        // Form validation
        document.getElementById('addQuestionForm').addEventListener('submit', function(e) {
            const questionText = document.getElementById('question_text').value;
            const correctAnswer = document.querySelector('input[name="correct_answer"]:checked');

            if (questionText.length < 10) {
                e.preventDefault();
                alert('Question text must be at least 10 characters long.');
                return;
            }

            if (!correctAnswer) {
                e.preventDefault();
                alert('Please select a correct answer.');
                return;
            }
        });
    </script>
</body>
</html>
