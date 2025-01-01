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
        <link rel="stylesheet" href="assets/css/dashboard.css">
        <style>
            /* Print Styles */
            @page {
                size: A4;
                margin: 0.5cm;
            }
            
            @media print {
                body { 
                    margin: 0;
                    padding: 15mm;
                    width: 210mm;
                    height: 297mm;
                    font-size: 12px;
                    line-height: 1.4;
                    background: white !important;
                    color: black !important;
                }
                
                .no-print { 
                    display: none !important;
                }
                
                .header { 
                    text-align: center;
                    margin-bottom: 20px;
                }
                
                .student-info {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    margin-bottom: 20px;
                    font-size: 12px;
                    border-bottom: 1px solid #ddd;
                    padding-bottom: 10px;
                }
                
                .question { 
                    margin-bottom: 15px;
                    page-break-inside: avoid;
                }
                
                .options { 
                    margin-left: 25px;
                    columns: 2;
                    column-gap: 30px;
                }
                
                .option { 
                    margin: 3px 0;
                    break-inside: avoid;
                }
                
                .circle {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    width: 16px;
                    height: 16px;
                    border-radius: 50%;
                    border: 1.5px solid #000;
                    margin-right: 8px;
                    font-size: 11px;
                }
                
                .correct {
                    border: 2px solid #28a745;
                    background-color: #d4edda;
                }
                
                .incorrect {
                    border: 2px solid #dc3545;
                    background-color: #f8d7da;
                }
                
                .quiz-header {
                    background: #f8f9fa !important;
                    padding: 10px;
                    margin: 15px 0;
                    border-left: 4px solid #007bff;
                    page-break-inside: avoid;
                }
                
                h1 { 
                    font-size: 18px;
                    margin-bottom: 10px;
                }
                
                h3 { 
                    font-size: 14px;
                    margin: 0;
                }
            }

            /* Dark Theme Styles */
            .select-user-card {
                background: rgba(42, 47, 69, 0.8);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 20px;
                padding: 2rem;
            }

            .select-user-card .card-header {
                background: transparent;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                padding-bottom: 1rem;
                margin-bottom: 1.5rem;
            }

            .select-user-card h4 {
                color: white;
                font-size: 1.25rem;
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .form-label {
                color: rgba(255, 255, 255, 0.8);
                font-weight: 500;
                margin-bottom: 0.5rem;
            }

            .form-select {
                background-color: rgba(26, 31, 55, 0.5);
                border: 1px solid rgba(255, 255, 255, 0.1);
                color: white;
                border-radius: 12px;
                padding: 0.75rem 1rem;
            }

            .form-select:focus {
                background-color: rgba(26, 31, 55, 0.5);
                border-color: #667eea;
                box-shadow: 0 0 0 2px rgba(102,126,234,0.2);
                color: white;
            }

            .form-select option {
                background-color: #2a2f45;
                color: white;
            }

            .alert-info {
                background: rgba(13, 202, 240, 0.1);
                border: 1px solid rgba(13, 202, 240, 0.2);
                color: #0dcaf0;
                border-radius: 12px;
            }

            /* Print Preview Styles */
            .quiz-section {
                background: rgba(42, 47, 69, 0.8);
                border-radius: 15px;
                margin-bottom: 1.5rem;
                overflow: hidden;
            }

            .quiz-header {
                background: rgba(26, 31, 55, 0.5);
                padding: 1rem;
                border-left: 4px solid #667eea;
            }

            .quiz-header h3 {
                color: white;
                margin-bottom: 0.5rem;
            }

            .question {
                padding: 1rem;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            .question:last-child {
                border-bottom: none;
            }

            .options {
                margin-top: 0.75rem;
            }

            .option {
                color: rgba(255, 255, 255, 0.8);
                margin: 0.5rem 0;
            }
        </style>
    </head>
    <body class="bg-dark">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="select-user-card">
                        <div class="d-flex align-items-center mb-4">
                            <div class="stats-icon me-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                            </div>
                            <h4>Select User for Print Preview</h4>
                        </div>

                        <?php if (empty($users)): ?>
                            <div class="alert alert-info">
                                No users have attempted this course yet.
                            </div>
                        <?php else: ?>
                            <form action="print_course.php" method="GET">
                                <input type="hidden" name="id" value="<?php echo $course_id; ?>">
                                <div class="mb-4">
                                    <select class="form-select" name="user_id" id="user_id" required>
                                        <option value="">Choose a user...</option>
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?php echo $user['id']; ?>">
                                                <?php echo htmlspecialchars($user['full_name']); ?> 
                                                (@<?php echo htmlspecialchars($user['username']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 action-button">
                                    <div class="d-flex align-items-center justify-content-center w-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        Preview Report
                                    </div>
                                </button>
                            </form>
                        <?php endif; ?>
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
        @page {
            size: A4;
            margin: 0.5cm;
        }
        
        @media print {
            body { 
                margin: 0;
                padding: 10mm;
                width: 210mm;
                height: 297mm;
                font-size: 11px;
                line-height: 1.3;
            }
            .no-print { 
                display: none !important;
            }
            .header { 
                text-align: center;
                
            }
            .student-info {
                display: grid;
                grid-template-columns: 1fr 1fr;
                margin-bottom: 15px;
                font-size: 11px;
            }
            .question { 
                margin-bottom: 10px;
                page-break-inside: avoid;
            }
            .options { 
                margin-left: 20px;
                columns: 2;
                column-gap: 20px;
            }
            .option { 
                margin: 2px 0;
                break-inside: avoid;
            }
            .circle {
                display: inline-block;
                width: 14px;
                height: 14px;
                border-radius: 50%;
                border: 1px solid #000;
                text-align: center;
                margin-right: 6px;
                font-size: 10px;
                line-height: 14px;
            }
            .correct {
                border: 1.5px solid #28a745;
                background-color: #d4edda;
            }
            .incorrect {
                border: 1.5px solid #dc3545;
                background-color: #f8d7da;
            }
            .quiz-header {
                background: #f8f9fa;
                padding: 6px;
                margin: 10px 0;
                border-left: 3px solid #007bff;
                page-break-inside: avoid;
                font-size: 11px;
            }
            h1 { 
                font-size: 16px;
                margin: 0;
                padding: 0;
            }
            h3 { 
                font-size: 13px;
                margin: 0;
                padding: 0;
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
                <p><strong>Student:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p>
                <p><strong>Date:</strong> <?php echo date('F d, Y'); ?></p>
            </div>
        </div>

        <?php foreach ($quizzes as $quiz): ?>
            <div class="quiz-section">
                <div class="quiz-header">
                    <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
                    <p>Score: <?php echo $quiz['score']; ?>/<?php echo count($quiz['questions']); ?> 
                       </p>
                </div>

                <?php foreach ($quiz['questions'] as $index => $question): ?>
                    <div class="question">
                        <p><strong><?php echo $index + 1; ?>.</strong> 
                           <?php echo htmlspecialchars($question['text']); ?></p>
                        <div class="options">
                            <?php foreach ($question['options'] as $letter => $option): ?>
                                <div class="option">
                                    <span class="circle <?php 
                                        if ($letter === $question['correct']) {
                                            echo 'correct';
                                        } elseif ($letter === $question['user_answer'] && $letter !== $question['correct']) {
                                            echo 'incorrect';
                                        }
                                    ?>"><?php echo $letter; ?></span>
                                    <?php echo htmlspecialchars($option); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
