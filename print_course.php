<?php
session_start();
require_once 'config.php';

// Restrict access to admin users only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$course_id = $_GET['id'] ?? 0;
$selected_user_id = $_GET['user_id'] ?? 0;

// If no user is selected, show user selection form
if (!$selected_user_id) {
    // Get all users with progress in this course
    $stmt = $conn->prepare("
        SELECT DISTINCT u.id, u.username, u.full_name
        FROM users u
        JOIN user_progress up ON u.id = up.user_id
        JOIN quizzes q ON up.quiz_id = q.id
        WHERE q.course_id = ? 
        AND u.role = 'user'
        ORDER BY u.full_name
    ");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Display user selection form
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Select User - Print Preview</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    </head>
    <body>
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="bi bi-printer"></i> Select User for Print Preview</h4>
                        </div>
                        <div class="card-body">
                            <?php if (empty($users)): ?>
                                <div class="alert alert-info">
                                    No users have attempted this course yet.
                                </div>
                            <?php else: ?>
                                <form action="print_course.php" method="GET">
                                    <input type="hidden" name="id" value="<?php echo $course_id; ?>">
                                    <div class="mb-3">
                                        <label for="user_id" class="form-label">Select User</label>
                                        <select class="form-select" name="user_id" id="user_id" required>
                                            <option value="">Choose a user...</option>
                                            <?php foreach ($users as $user): ?>
                                                <option value="<?php echo $user['id']; ?>">
                                                    <?php echo htmlspecialchars($user['full_name']); ?> 
                                                    (<?php echo htmlspecialchars($user['username']); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-eye"></i> Preview Report
                                        </button>
                                    </div>
                                </form>
                            <?php endif; ?>
                            <div class="mt-3">
                                <a href="manage_courses.php" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-arrow-left"></i> Back to Courses
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Get user details for selected user
$stmt = $conn->prepare("SELECT username, full_name FROM users WHERE id = ?");
$stmt->bind_param("i", $selected_user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get course details
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

// Get progress information with questions, answers, and user's answers
$stmt = $conn->prepare("
    SELECT 
        q.title as quiz_title,
        q.id as quiz_id,
        up.progress_percentage,
        up.score,
        up.completed,
        up.updated_at as completion_date,
        up.user_answers,
        qs.id as question_id,
        qs.question_text,
        qs.option_a,
        qs.option_b,
        qs.option_c,
        qs.option_d,
        qs.correct_answer
    FROM quizzes q
    LEFT JOIN user_progress up ON q.id = up.quiz_id AND up.user_id = ?
    LEFT JOIN questions qs ON q.id = qs.quiz_id
    WHERE q.course_id = ?
    ORDER BY q.id, qs.id
");
$stmt->bind_param("ii", $selected_user_id, $course_id);
$stmt->execute();
$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Organize results by quiz with user answers
$quizzes = [];
foreach ($results as $row) {
    if (!isset($quizzes[$row['quiz_title']])) {
        $quizzes[$row['quiz_title']] = [
            'title' => $row['quiz_title'],
            'score' => $row['score'],
            'progress' => $row['progress_percentage'],
            'completion_date' => $row['completion_date'],
            'questions' => []
        ];
    }
    if ($row['question_text']) {
        $user_answers = json_decode($row['user_answers'] ?? '{}', true);
        $question_id = $row['question_id']; // Use the correct key name
        $user_answer = $user_answers[$question_id] ?? null;
        
        $quizzes[$row['quiz_title']]['questions'][] = [
            'id' => $question_id, // Store the question ID
            'text' => $row['question_text'],
            'options' => [
                'A' => $row['option_a'],
                'B' => $row['option_b'],
                'C' => $row['option_c'],
                'D' => $row['option_d']
            ],
            'correct' => $row['correct_answer'],
            'user_answer' => $user_answer
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Progress Report - <?php echo htmlspecialchars($course['title']); ?></title>
    <style>
        /* A4 paper size specifications */
        @page {
            size: A4;
            margin: 1cm;
        }
        
        @media print {
            body { 
                margin: 0;
                padding: 15mm;
                width: 210mm;
                height: 297mm;
                font-size: 12px;
            }
            .no-print { 
                display: none; 
            }
            .header { 
                margin-bottom: 20px;
            }
            .student-info { 
                margin-bottom: 15px;
            }
            .question { 
                margin-bottom: 15px;
                page-break-inside: avoid;
            }
            .options { 
                margin-left: 15px;
            }
            .option { 
                margin: 3px 0;
            }
            .circle {
                display: inline-block;
                width: 16px;
                height: 16px;
                border-radius: 50%;
                border: 1px solid #000;
                text-align: center;
                margin-right: 8px;
                font-size: 11px;
                line-height: 16px;
            }
            .correct {
                border: 2px solid #28a745;
                background-color: #d4edda;
                color: #28a745;
                font-weight: bold;
            }
            .incorrect {
                border: 2px solid #dc3545;
                background-color: #f8d7da;
                color: #dc3545;
                font-weight: bold;
            }
            .quiz-header {
                background: #f8f9fa;
                padding: 8px;
                margin: 15px 0;
                border-left: 3px solid #007bff;
                page-break-inside: avoid;
            }
            .quiz-score {
                color: #28a745;
                font-weight: bold;
            }
            table { 
                font-size: 11px;
            }
            h1 { 
                font-size: 18px;
            }
            h3 { 
                font-size: 14px;
            }
            /* Ensure content fits on page */
            .container {
                max-width: 100%;
                width: 100%;
                margin: 0;
                padding: 0;
            }
            /* Force page breaks between quizzes if needed */
            .quiz-section {
                page-break-inside: avoid;
            }
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-4">
        <div class="no-print mb-4">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Print Report
            </button>
            <a href="print_course.php?id=<?php echo $course_id; ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Select Different User
            </a>
        </div>

        <div class="header">
            <h1><?php echo htmlspecialchars($course['title']); ?> - Progress Report</h1>
            <div class="student-info">
                <p><strong>Student Name:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p>
                <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                <p><strong>Date Generated:</strong> <?php echo date('F d, Y'); ?></p>
                <p><strong>Generated by:</strong> <?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
            </div>
        </div>

        <?php foreach ($quizzes as $quiz): ?>
            <div class="quiz-section">
                <div class="quiz-header">
                    <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
                    <p>Score: <span class="quiz-score"><?php echo $quiz['score']; ?>/<?php echo count($quiz['questions']); ?></span></p>
                    <p>Completion Date: <?php echo date('F d, Y', strtotime($quiz['completion_date'])); ?></p>
                </div>

                <?php foreach ($quiz['questions'] as $index => $question): ?>
                    <div class="question">
                        <p><strong><?php echo $index + 1; ?>.</strong> <?php echo htmlspecialchars($question['text']); ?></p>
                        <div class="options">
                            <?php foreach ($question['options'] as $letter => $option): ?>
                                <div class="option">
                                    <span class="circle <?php 
                                        if ($letter === $question['correct']) {
                                            echo 'correct';
                                        } elseif ($letter === $question['user_answer'] && $letter !== $question['correct']) {
                                            echo 'incorrect';
                                        }
                                    ?>">
                                        <?php echo $letter; ?>
                                    </span>
                                    <?php echo htmlspecialchars($option); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <div class="mt-4">
            <p><strong>Course Description:</strong></p>
            <p><?php echo htmlspecialchars($course['description']); ?></p>
        </div>
    </div>
</body>
</html>
