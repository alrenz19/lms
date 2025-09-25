<?php
session_start();
require_once '../config.php';

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
        WHERE up.course_id = ? 
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
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Select User - Print Preview</title>
        <link rel="stylesheet" href="./public/css/tailwind.min.css" />
    </head>
    <body class="bg-gray-50">
        <div class="container mx-auto max-w-2xl py-6 px-4">
            <div class="flex justify-end mb-4">
                <a href="manage_courses.php" class="inline-flex items-center px-3 py-2 bg-gray-600 text-white text-sm rounded-md hover:bg-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Back
                </a>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <h2 class="text-lg font-semibold text-gray-800">Select Student</h2>
                </div>

                <?php if (empty($users)): ?>
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-3 text-sm">
                        <p class="text-blue-700">No users have attempted this course yet.</p>
                    </div>
                <?php else: ?>
                    <form action="print_course.php" method="GET">
                        <input type="hidden" name="id" value="<?php echo $course_id; ?>">
                        <div class="mb-4">
                            <select name="user_id" id="user_id" required class="w-full px-3 py-2 rounded-md border border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <option value="">Select a student...</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo $user['id']; ?>">
                                        <?php echo htmlspecialchars($user['full_name']); ?> 
                                        (@<?php echo htmlspecialchars($user['username']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="w-full flex justify-center items-center px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                            </svg>
                            Preview Report
                        </button>
                    </form>
                <?php endif; ?>
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
        c.title as course_title,
        up.progress_percentage,
        up.score,
        up.total_score,
        up.completed,
        up.updated_at as completion_date,
        up.user_answers,
        q.id as question_id,
        q.question_text,
        q.option_a,
        q.option_b,
        q.option_c,
        q.option_d,
        q.correct_answer
    FROM courses c
    LEFT JOIN user_progress up ON c.id = up.course_id AND up.user_id = ?
    LEFT JOIN questions q ON c.id = q.course_id
    WHERE c.id = ?
    ORDER BY q.id
");
$stmt->bind_param("ii", $selected_user_id, $course_id);
$stmt->execute();
$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Check if course has questions
$has_questions = !empty($results) && !is_null($results[0]['question_id']);

// Organize results by course with user answers
$course_data = [
    'title' => $course['title'],
    'score' => 0,
    'total_score' => 0,
    'progress' => 0,
    'completion_date' => null,
    'questions' => []
];

foreach ($results as $row) {
    if ($row['score'] !== null) {
        $course_data['score'] = $row['score'];
        $course_data['total_score'] = $row['total_score'];
        $course_data['progress'] = $row['progress_percentage'];
        $course_data['completion_date'] = $row['completion_date'];
    }
    
    if ($row['question_text']) {
        $user_answers = json_decode($row['user_answers'] ?? '{}', true);
        $question_id = $row['question_id']; 
        $user_answer = $user_answers[$question_id] ?? null;
        
        $course_data['questions'][] = [
            'id' => $question_id,
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Progress Report - <?php echo htmlspecialchars($course['title']); ?></title>
    <link rel="stylesheet" href="./public/css/tailwind.min.css" />
    <style>
        /* Base styles */
        body {
            font-size: 11pt;
            line-height: 1.3;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        
        /* Print-specific styles */
        @media print {
            html, body {
                width: 100%;
                height: auto;
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
            }
            
            * {
                margin-top: 0 !important;
                margin-bottom: 0 !important;
                padding-top: 0 !important;
                padding-bottom: 0 !important;
            }
            
            .container {
                width: 100% !important;
                max-width: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            /* Layout for main sections */
            .course-section {
                page-break-after: always;
                page-break-inside: avoid;
                break-after: page;
                break-inside: avoid;
                margin: 0 !important;
                padding: 10pt 0 !important;
                display: block;
            }
            
            .course-section:last-child {
                page-break-after: auto;
                break-after: auto;
            }
            
            .no-print {
                display: none !important;
            }
            
            .question {
                page-break-inside: avoid;
                break-inside: avoid;
                padding: 5pt 0;
                margin-bottom: 5pt;
            }

            .report-header {
                margin-bottom: 10pt !important;
                padding-bottom: 5pt !important;
                border-bottom: 1pt solid #e5e7eb;
            }
            
            .correct-answer {
                background-color: #d1fae5 !important;
                border-color: #10b981 !important;
            }
            
            .incorrect-answer {
                background-color: #fee2e2 !important;
                border-color: #ef4444 !important;
            }
            
            @page {
                size: A4;
                margin: 1.27cm;
            }
        }
        
        .answer-circle {
            width: 22px;
            height: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-right: 6px;
            font-weight: 500;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Compact controls header -->
    <div class="no-print bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-5xl mx-auto px-4 py-2">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <!-- Left side - buttons -->
                <div class="flex items-center gap-2">
                    <button id="printButton" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd" />
                        </svg>
                        Print Report
                    </button>
                    <a href="print_course.php?id=<?php echo $course_id; ?>" class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-700 text-sm rounded hover:bg-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.707-10.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L9.414 11H13a1 1 0 100-2H9.414l1.293-1.293z" clip-rule="evenodd" />
                        </svg>
                        Select Different User
                    </a>
                    <a href="manage_courses.php" class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-600 text-sm rounded hover:bg-red-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Cancel
                    </a>
                </div>
                
                <!-- Right side - compact course options -->
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <input class="h-3.5 w-3.5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" 
                               type="checkbox" id="printExam" <?php echo $has_questions ? 'checked' : 'disabled'; ?>>
                        <label class="ml-1.5 text-xs text-gray-700" for="printExam">
                            Print exam questions
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input class="h-3.5 w-3.5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" 
                               type="checkbox" id="pageBreaksAfterCourse" checked>
                        <label class="ml-1.5 text-xs text-gray-700" for="pageBreaksAfterCourse">
                            Add page break
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Printable content with max width for better screen viewing -->
    <div class="max-w-4xl mx-auto bg-white my-4 shadow-sm">
        <div class="course-section" data-course-title="<?php echo htmlspecialchars($course_data['title']); ?>">
            <!-- Report Header -->
            <div class="report-header px-6 pt-6 pb-4">
                <h1 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($course_data['title']); ?> - Progress Report</h1>
                <div class="flex flex-wrap gap-x-6 mt-1 text-sm text-gray-600">
                    <p><span class="font-semibold">Student:</span> <?php echo htmlspecialchars($user['full_name']); ?></p>
                    <p><span class="font-semibold">Date:</span> <?php echo date('F d, Y'); ?></p>
                </div>
                <?php if ($course_data['completion_date']): ?>
                <div class="mt-2 text-sm">
                    <p><span class="font-semibold">Completed:</span> <?php echo date('F d, Y', strtotime($course_data['completion_date'])); ?></p>
                    <p><span class="font-semibold">Score:</span> <?php echo $course_data['score']; ?>/<?php echo $course_data['total_score']; ?> (<?php echo round($course_data['progress']); ?>%)</p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Course Content -->
            <div class="px-6 pb-6">
                <!-- Course Header -->
                <div class="bg-blue-50 px-3 py-2 border-l-4 border-blue-500 mb-4">
                    <h2 class="text-md font-semibold text-gray-800">Course Exam</h2>
                </div>
                
                <?php if (!$has_questions): ?>
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                        <p class="text-yellow-700">This course does not have an exam or the user has not attempted it yet.</p>
                    </div>
                <?php else: ?>
                    <!-- Course Questions -->
                    <div class="space-y-4">
                        <?php foreach ($course_data['questions'] as $qIndex => $question): ?>
                            <div class="question">
                                <p class="font-medium text-gray-800 text-sm mb-2">
                                    <span class="mr-1"><?php echo $qIndex + 1; ?>.</span>
                                    <?php echo htmlspecialchars($question['text']); ?>
                                </p>
                                
                                <div class="ml-5 grid grid-cols-2 gap-x-4 gap-y-1">
                                    <?php foreach ($question['options'] as $letter => $option): ?>
                                        <div class="flex items-center text-sm">
                                            <span class="answer-circle border <?php 
                                                if ($letter === $question['correct']) {
                                                    echo 'bg-green-100 border-green-500 text-green-800 correct-answer';
                                                } elseif ($letter === $question['user_answer'] && $letter !== $question['correct']) {
                                                    echo 'bg-red-100 border-red-500 text-red-800 incorrect-answer';
                                                } else {
                                                    echo 'bg-gray-100 border-gray-300 text-gray-600';
                                                }
                                            ?>"><?php echo $letter; ?></span>
                                            <?php echo htmlspecialchars($option); ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const printExamCheckbox = document.getElementById('printExam');
            const pageBreaksCheckbox = document.getElementById('pageBreaksAfterCourse');
            const printButton = document.getElementById('printButton');
            
            // Ensure checkboxes are properly initialized
            pageBreaksCheckbox.checked = true;
            
            // Apply page break styles
            function applyPageBreaks() {
                const usePageBreaks = pageBreaksCheckbox.checked;
                const courseSections = document.querySelectorAll('.course-section');
                
                courseSections.forEach(function(section, index) {
                    if (usePageBreaks && index < courseSections.length - 1) {
                        section.style.pageBreakAfter = 'always';
                        section.style.breakAfter = 'page';
                    } else {
                        section.style.pageBreakAfter = 'auto';
                        section.style.breakAfter = 'auto';
                    }
                });
            }
            
            // Apply page breaks initially
            applyPageBreaks();
            
            // Toggle page breaks
            pageBreaksCheckbox.addEventListener('change', function() {
                applyPageBreaks();
            });
            
            // Toggle exam questions visibility
            printExamCheckbox.addEventListener('change', function() {
                const questionsContainer = document.querySelector('.space-y-4');
                if (questionsContainer) {
                    questionsContainer.style.display = this.checked ? 'block' : 'none';
                }
            });
            
            // Fix the print functionality to prevent double dialogs
            printButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation(); // Prevent event bubbling
                
                // Reset all margins to prevent blank space
                document.body.style.margin = '0';
                document.body.style.padding = '0';
                
                // Force all elements to have no extra margins
                document.querySelectorAll('.course-section').forEach(function(el) {
                    el.style.paddingTop = '0';
                    el.style.paddingBottom = '10pt';
                    el.style.marginTop = '0';
                    el.style.marginBottom = '0';
                });
                
                applyPageBreaks();
                
                // Use a single print call with timeout
                setTimeout(window.print, 100);
            });
        });
    </script>
</body>
</html>