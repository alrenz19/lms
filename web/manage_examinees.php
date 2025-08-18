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

// Handle creating a new quiz
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_quiz'])) {
    $quiz_title = $conn->real_escape_string($_POST['quiz_title']);
    
    $stmt = $conn->prepare("INSERT INTO quizzes (course_id, title) VALUES (?, ?)");
    $stmt->bind_param("is", $course_id, $quiz_title);
    
    if ($stmt->execute()) {
        $new_quiz_id = $conn->insert_id;
        $_SESSION['success'] = "Quiz created successfully!";
        header("Location: manage_quiz.php?course_id=" . $course_id . "&quiz_id=" . $new_quiz_id);
        exit;
    } else {
        $error = "Error creating quiz: " . $conn->error;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_question'])) {
    try {
        $conn->begin_transaction();
        
        $question = $conn->real_escape_string($_POST['question_text']);
        $option_a = $conn->real_escape_string($_POST['option_a']);
        $option_b = $conn->real_escape_string($_POST['option_b']);
        $option_c = $conn->real_escape_string($_POST['option_c']);
        $option_d = $conn->real_escape_string($_POST['option_d']);
        $correct = $_POST['correct_answer'];

        // Initialize image variables
        $question_image = "";
        $option_a_image = "";
        $option_b_image = "";
        $option_c_image = "";
        $option_d_image = "";
        
        // Image upload directory
        $upload_dir = "../uploads/question_images/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Process question image
        if (isset($_FILES['question_image']) && $_FILES['question_image']['error'] == 0) {
            $file_name = time() . '_' . basename($_FILES['question_image']['name']);
            $target = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['question_image']['tmp_name'], $target)) {
                $question_image = $file_name;
            }
        } elseif (!empty($_POST['question_image_path'])) {
            // Use existing image from assets folder
            $question_image = 'assets:' . $_POST['question_image_path'];
        }
        
        // Process option A image
        if (isset($_FILES['option_a_image']) && $_FILES['option_a_image']['error'] == 0) {
            $file_name = time() . '_A_' . basename($_FILES['option_a_image']['name']);
            $target = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['option_a_image']['tmp_name'], $target)) {
                $option_a_image = $file_name;
            }
        } elseif (!empty($_POST['option_a_image_path'])) {
            // Use existing image from assets folder
            $option_a_image = 'assets:' . $_POST['option_a_image_path'];
        }
        
        // Process option B image
        if (isset($_FILES['option_b_image']) && $_FILES['option_b_image']['error'] == 0) {
            $file_name = time() . '_B_' . basename($_FILES['option_b_image']['name']);
            $target = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['option_b_image']['tmp_name'], $target)) {
                $option_b_image = $file_name;
            }
        } elseif (!empty($_POST['option_b_image_path'])) {
            // Use existing image from assets folder
            $option_b_image = 'assets:' . $_POST['option_b_image_path'];
        }
        
        // Process option C image
        if (isset($_FILES['option_c_image']) && $_FILES['option_c_image']['error'] == 0) {
            $file_name = time() . '_C_' . basename($_FILES['option_c_image']['name']);
            $target = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['option_c_image']['tmp_name'], $target)) {
                $option_c_image = $file_name;
            }
        } elseif (!empty($_POST['option_c_image_path'])) {
            // Use existing image from assets folder
            $option_c_image = 'assets:' . $_POST['option_c_image_path'];
        }
        
        // Process option D image
        if (isset($_FILES['option_d_image']) && $_FILES['option_d_image']['error'] == 0) {
            $file_name = time() . '_D_' . basename($_FILES['option_d_image']['name']);
            $target = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['option_d_image']['tmp_name'], $target)) {
                $option_d_image = $file_name;
            }
        } elseif (!empty($_POST['option_d_image_path'])) {
            // Use existing image from assets folder
            $option_d_image = 'assets:' . $_POST['option_d_image_path'];
        }

        $stmt = $conn->prepare("INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_answer, question_image, option_a_image, option_b_image, option_c_image, option_d_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssssssss", $current_quiz_id, $question, $option_a, $option_b, $option_c, $option_d, $correct, $question_image, $option_a_image, $option_b_image, $option_c_image, $option_d_image);
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="./public/css/tailwind.min.css" />
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Ensure sidebar consistency with dashboard */
        #sidebar {
            background-color: #1e3a8a !important; /* bg-blue-900 equivalent */
        }
        
        /* Updated styling to match dashboard */
        .quiz-banner {
            background-image: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
        }
        
        /* Card hover effects */
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        /* Question card styling */
        .question-card {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        .question-card:hover {
            transform: translateY(-5px);
            border-left-color: #4f46e5;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .option-container {
            transition: all 0.2s ease;
        }
        .option-container:hover {
            background-color: #eef2ff;
        }
        .correct-badge {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        /* Button effects */
        .btn-primary {
            background-color: #4f46e5;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #4338ca;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: #f3f4f6;
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            background-color: #e5e7eb;
        }
        
        .add-question-btn {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .add-question-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transform: skewX(-30deg);
            transition: all 0.6s ease;
        }
        .add-question-btn:hover::before {
            left: 100%;
        }
        
        /* Animation for newly added question */
        @keyframes highlightNew {
            0% { border-color: #4f46e5; background-color: #eef2ff; }
            50% { border-color: #6366f1; background-color: #e0e7ff; }
            100% { border-color: #4f46e5; background-color: #eef2ff; }
        }
        .highlight-new {
            animation: highlightNew 2s ease infinite;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="p-8 sm:ml-72">
        <div class="container mx-auto">
            <?php if ($quizzes->num_rows == 0): ?>
                <!-- No quizzes message -->
                <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 rounded-xl p-6 mb-6 shadow-lg">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <div class="bg-white/10 p-3 rounded-lg mr-4">
                                <i data-lucide="help-circle" class="h-8 w-8 text-white"></i>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-white">Quiz Management</h1>
                                <p class="text-indigo-100"><?php echo htmlspecialchars($course['title']); ?></p>
                            </div>
                        </div>
                        
                        <a href="manage_courses.php" class="px-4 py-2.5 bg-white text-indigo-600 rounded-lg flex items-center gap-2 transition-all duration-200 font-medium shadow-sm hover:bg-indigo-50">
                            <i data-lucide="arrow-left" class="w-5 h-5"></i>
                            <span>Back to Courses</span>
                        </a>
                    </div>
                    
                    <div class="mt-8 bg-amber-50 text-amber-800 border border-amber-200 rounded-xl p-5 flex gap-4 items-start shadow-sm">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-500 mt-0.5"></i>
                        <div>
                            <h3 class="font-semibold mb-1">Please create a quiz first</h3>
                            <p class="text-amber-700 mb-4">You need to create at least one quiz for this course before adding questions.</p>
                            <button type="button" 
                                    class="px-4 py-2 bg-white text-amber-700 hover:bg-amber-100 border border-amber-200 rounded-lg text-sm font-medium inline-flex items-center gap-2 shadow-sm transition-all duration-200"
                                    onclick="openCreateQuizModal()">
                                <i data-lucide="plus-circle" class="w-4 h-4"></i> Create a Quiz
                            </button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Has quizzes content -->
                <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 rounded-xl p-6 mb-6 shadow-lg">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <div class="bg-white/10 p-3 rounded-lg mr-4">
                                <i data-lucide="help-circle" class="h-8 w-8 text-white"></i>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-white">Quiz Management</h1>
                                <p class="text-indigo-100"><?php echo htmlspecialchars($course['title']); ?></p>
                            </div>
                        </div>
                        
                        <a href="manage_courses.php" class="px-4 py-2.5 bg-white text-indigo-600 rounded-lg flex items-center gap-2 transition-all duration-200 font-medium shadow-sm hover:bg-indigo-50">
                            <i data-lucide="arrow-left" class="w-5 h-5"></i>
                            <span>Back to Courses</span>
                        </a>
                    </div>
                </div>

                <?php if (isset($success) || isset($error)): ?>
                    <div class="mb-6 rounded-xl p-5 flex items-start gap-3 <?php echo isset($success) ? 'bg-green-50 text-green-700 border border-green-100' : 'bg-amber-50 text-amber-700 border border-amber-100'; ?> shadow-sm animate-fade-in" style="display: none;">
                        <i data-lucide="<?php echo isset($success) ? 'check-circle' : 'alert-triangle'; ?>" class="w-5 h-5 text-<?php echo isset($success) ? 'green' : 'amber'; ?>-500 mt-0.5"></i>
                        <div>
                            <h3 class="font-medium mb-1"><?php echo isset($success) ? 'Success!' : 'Warning!'; ?></h3>
                            <p><?php echo isset($success) ? $success : $error; ?></p>
                        </div>
                        <button type="button" class="ml-auto text-gray-400 hover:text-gray-600" onclick="this.parentElement.remove()">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-8 transition hover:shadow-lg">
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-6 items-center">
                        <div class="md:col-span-4">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-indigo-500">
                                    <i data-lucide="search" class="w-5 h-5"></i>
                                </div>
                                <input type="text" class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition shadow-sm"  name="search_user" value="" placeholder="Search examinees...">
                            </div>
                        </div>
                        <div class="md:col-span-2 flex gap-3">
                            <!-- <button class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl flex items-center justify-center gap-2 transition text-sm font-medium shadow-sm hover:shadow-md hover:-translate-y-1" 
                                    onclick="openCreateQuizModal()">
                                <i data-lucide="plus-circle" class="w-4 h-4"></i> New Quiz
                            </button> -->
                            <button class="add-question-btn w-full px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl flex items-center justify-center gap-2 transition text-sm font-medium shadow-sm hover:shadow-md hover:-translate-y-1" 
                                    onclick="openAddQuestionModal()">
                                <i data-lucide="plus-circle" class="w-4 h-4"></i> Add Examinees
                            </button>
                        </div>
                    </div>
                </div>

                <?php if ($current_quiz_id): ?>
                    <!-- Instructions for quiz management -->
                    <?php
                    $result = $conn->query("SELECT COUNT(*) as count FROM questions WHERE quiz_id = $current_quiz_id");
                    $question_count = $result->fetch_assoc()['count'];
                    
                    if ($question_count == 0):
                    ?>
                    <div class="bg-indigo-50 text-indigo-700 border border-indigo-100 rounded-xl p-5 flex gap-4 items-start shadow-sm mb-8">
                        <i data-lucide="info" class="w-5 h-5 text-indigo-500 mt-0.5"></i>
                        <div>
                            <h3 class="font-semibold mb-1">Getting Started</h3>
                            <p class="text-indigo-700 mb-2">This quiz doesn't have any questions yet. Add your first question to get started.</p>
                            <p class="text-indigo-700 mb-0">Best practices:</p>
                            <ul class="list-disc list-inside ml-2 text-indigo-700 text-sm">
                                <li>Create clear, concise questions</li>
                                <li>Provide 4 distinct answer options</li>
                                <li>Make sure one answer is clearly correct</li>
                            </ul>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700">
                                <i data-lucide="list-checks" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-800">List of Examinees</h2>
                                <p class="text-gray-500 text-sm">Below is the list of individuals registered to take the exam.</p>
                            </div>
                        </div>
                        
                        <div class="flex gap-3">
                            <!-- Filter or sort buttons could go here -->
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="space-y-5">
                        <?php
                        $result = $conn->query("SELECT * FROM questions WHERE quiz_id = $current_quiz_id ORDER BY id");
                        $question_num = 1;
                        
                        if ($result->num_rows === 0 && $question_count > 0):
                        ?>
                        <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4 text-gray-400">
                                <i data-lucide="search" class="w-8 h-8"></i>
                            </div>
                            <h3 class="text-xl font-medium text-gray-900 mb-2">No Questions Found</h3>
                            <p class="text-gray-500 mb-4">There are no questions available for this quiz.</p>
                            <button class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl flex items-center justify-center gap-2 transition text-sm font-medium shadow-sm mx-auto hover:-translate-y-1 hover:shadow-md" 
                                    onclick="openAddQuestionModal()">
                                <i data-lucide="plus-circle" class="w-4 h-4"></i> Add Question
                            </button>
                        </div>
                        <?php
                        endif;
                        
                        while ($row = $result->fetch_assoc()): 
                            $is_new_question = ($row['id'] == $new_question_id);
                            $highlight_class = $is_new_question ? 'highlight-new' : '';
                        ?>
                            <div class="question-card bg-white rounded-xl shadow-sm p-6 <?php echo $is_new_question ? 'border-indigo-300 ring-2 ring-indigo-100 ' . $highlight_class : 'border border-gray-100'; ?> hover:border-indigo-300 transition">
                                <div class="flex justify-between items-start mb-5">
                                    <div class="flex items-center gap-3">
                                        <span class="px-3.5 py-1.5 bg-indigo-100 text-indigo-700 rounded-full text-sm font-semibold">Q<?php echo $question_num++; ?></span>
                                        <?php if ($is_new_question): ?>
                                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium animate-pulse">
                                            <i data-lucide="check-circle" class="w-4 h-4 inline mr-1"></i> Newly Added
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex gap-2">
                                        <button class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" onclick="editQuestion(<?php echo $row['id']; ?>)">
                                            <i data-lucide="pencil" class="w-5 h-5"></i>
                                        </button>
                                        <button class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" onclick="deleteQuestion(<?php echo $row['id']; ?>)">
                                            <i data-lucide="trash-2" class="w-5 h-5"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="text-gray-800 font-medium text-lg mb-5">
                                    <?php echo htmlspecialchars($row['question_text']); ?>
                                </div>
                                
                                <?php if (!empty($row['question_image'])): ?>
                                <div class="mb-5">
                                    <?php if (strpos($row['question_image'], 'assets:') === 0): ?>
                                        <img src="../assets/<?php echo htmlspecialchars(substr($row['question_image'], 7)); ?>" 
                                             alt="Question Image" 
                                             class="max-w-full h-auto rounded-lg border border-gray-200 max-h-64">
                                    <?php else: ?>
                                        <img src="../uploads/question_images/<?php echo htmlspecialchars($row['question_image']); ?>" 
                                             alt="Question Image" 
                                             class="max-w-full h-auto rounded-lg border border-gray-200 max-h-64">
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                                    <?php
                                    $options = [
                                        'A' => $row['option_a'],
                                        'B' => $row['option_b'],
                                        'C' => $row['option_c'],
                                        'D' => $row['option_d']
                                    ];
                                    
                                    foreach ($options as $letter => $option):
                                        $is_correct = ($letter === $row['correct_answer']);
                                        $bg_class = $is_correct ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200';
                                        $option_image_field = 'option_' . strtolower($letter) . '_image';
                                        $has_image = !empty($row[$option_image_field]);
                                    ?>
                                    <div class="option-container rounded-lg p-4 flex flex-col <?php echo $bg_class; ?> border hover:shadow-sm transition-all duration-200">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full <?php echo $is_correct ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700'; ?> mr-3 font-medium">
                                                    <?php echo $letter; ?>
                                                </span>
                                                <span class="text-gray-700"><?php echo htmlspecialchars($option); ?></span>
                                            </div>
                                            <?php if ($is_correct): ?>
                                            <span class="correct-badge px-3 py-1.5 rounded-full text-white text-xs font-medium shadow-sm ml-2">
                                                <i data-lucide="check" class="w-3 h-3 inline mr-1"></i> Correct
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if ($has_image): ?>
                                        <div class="mt-3 w-full">
                                            <?php if (strpos($row[$option_image_field], 'assets:') === 0): ?>
                                                <img src="../assets/<?php echo htmlspecialchars(substr($row[$option_image_field], 7)); ?>" 
                                                     alt="Option <?php echo $letter; ?> Image" 
                                                     class="max-w-full h-auto rounded-lg border border-gray-100 max-h-36">
                                            <?php else: ?>
                                                <img src="../uploads/question_images/<?php echo htmlspecialchars($row[$option_image_field]); ?>" 
                                                     alt="Option <?php echo $letter; ?> Image" 
                                                     class="max-w-full h-auto rounded-lg border border-gray-100 max-h-36">
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Question Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-70 z-50 hidden items-center justify-center backdrop-blur-sm transition-all duration-300" id="addQuestionModal">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl mx-4 transform transition-all duration-300 max-h-[90vh] overflow-y-auto">
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 p-5 rounded-t-xl flex items-center justify-between sticky top-0 z-10">
                <h5 class="text-lg font-semibold text-white flex items-center">
                    <i data-lucide="plus-circle" class="h-5 w-5 mr-2"></i>
                    Add New Question
                </h5>
                <button type="button" class="text-white/80 hover:text-white focus:outline-none" onclick="closeAddQuestionModal()">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>
            <form method="POST" id="addQuestionForm" enctype="multipart/form-data">
                <div class="p-6 space-y-6">
                    <input type="hidden" name="add_question" value="1">
                    
                    <!-- Question Section -->
                    <div class="bg-indigo-50 rounded-xl p-6 border border-indigo-100">
                        <h3 class="text-lg font-medium text-indigo-800 mb-4 flex items-center">
                            <i data-lucide="help-circle" class="h-5 w-5 mr-2 text-indigo-600"></i>
                            Question Information
                        </h3>
                        
                        <div class="mb-4">
                            <label for="question_text" class="block text-sm font-medium text-gray-700 mb-2">Question Text</label>
                            <div class="relative">
                                <textarea class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition shadow-sm" 
                                       id="question_text" 
                                       name="question_text" 
                                       rows="3"
                                       required 
                                       placeholder="Enter your question here..."></textarea>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Question should be clear and concise.</p>
                        </div>
                        
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Question Image (Optional)</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-2">
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Upload a new image:</p>
                                    <input type="file" name="question_image" id="question_image" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500" accept="image/*">
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Or use an existing image:</p>
                                    <input type="text" name="question_image_path" placeholder="images/example.jpg" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500">
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">You can either upload a new image or specify the path to an existing image in assets folder.</p>
                        </div>
                    </div>
                    
                    <!-- Answer Options Section -->
                    <div class="bg-indigo-50 rounded-xl p-6 border border-indigo-100">
                        <h3 class="text-lg font-medium text-indigo-800 mb-4 flex items-center">
                            <i data-lucide="check-circle" class="h-5 w-5 mr-2 text-indigo-600"></i>
                            Answer Options
                        </h3>
                        
                        <div class="space-y-5">
                            <!-- Option A -->
                            <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm hover:border-indigo-300 transition">
                                <div class="flex items-center mb-3">
                                    <input type="radio" name="correct_answer" value="A" class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 mr-2" required>
                                    <label class="inline-block w-8 text-center font-medium text-indigo-600 bg-indigo-50 rounded-md py-1 mr-3">A</label>
                                    <span class="text-sm font-medium text-gray-700">Option A</span>
                                    <span class="ml-auto px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full hidden" id="option-a-correct">Correct Answer</span>
                                </div>
                                
                                <div class="mb-3">
                                    <input type="text" name="option_a" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition" 
                                           placeholder="Option A" required>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">Upload a new image:</p>
                                        <input type="file" name="option_a_image" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" accept="image/*">
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">Or use existing image path:</p>
                                        <input type="text" name="option_a_image_path" placeholder="images/example.jpg" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Option B -->
                            <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm hover:border-indigo-300 transition">
                                <div class="flex items-center mb-3">
                                    <input type="radio" name="correct_answer" value="B" class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 mr-2">
                                    <label class="inline-block w-8 text-center font-medium text-indigo-600 bg-indigo-50 rounded-md py-1 mr-3">B</label>
                                    <span class="text-sm font-medium text-gray-700">Option B</span>
                                    <span class="ml-auto px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full hidden" id="option-b-correct">Correct Answer</span>
                                </div>
                                
                                <div class="mb-3">
                                    <input type="text" name="option_b" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition" 
                                           placeholder="Option B" required>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">Upload a new image:</p>
                                        <input type="file" name="option_b_image" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" accept="image/*">
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">Or use existing image path:</p>
                                        <input type="text" name="option_b_image_path" placeholder="images/example.jpg" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Option C -->
                            <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm hover:border-indigo-300 transition">
                                <div class="flex items-center mb-3">
                                    <input type="radio" name="correct_answer" value="C" class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 mr-2">
                                    <label class="inline-block w-8 text-center font-medium text-indigo-600 bg-indigo-50 rounded-md py-1 mr-3">C</label>
                                    <span class="text-sm font-medium text-gray-700">Option C</span>
                                    <span class="ml-auto px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full hidden" id="option-c-correct">Correct Answer</span>
                                </div>
                                
                                <div class="mb-3">
                                    <input type="text" name="option_c" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition" 
                                           placeholder="Option C" required>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">Upload a new image:</p>
                                        <input type="file" name="option_c_image" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" accept="image/*">
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">Or use existing image path:</p>
                                        <input type="text" name="option_c_image_path" placeholder="images/example.jpg" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Option D -->
                            <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm hover:border-indigo-300 transition">
                                <div class="flex items-center mb-3">
                                    <input type="radio" name="correct_answer" value="D" class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 mr-2">
                                    <label class="inline-block w-8 text-center font-medium text-indigo-600 bg-indigo-50 rounded-md py-1 mr-3">D</label>
                                    <span class="text-sm font-medium text-gray-700">Option D</span>
                                    <span class="ml-auto px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full hidden" id="option-d-correct">Correct Answer</span>
                                </div>
                                
                                <div class="mb-3">
                                    <input type="text" name="option_d" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition" 
                                           placeholder="Option D" required>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">Upload a new image:</p>
                                        <input type="file" name="option_d_image" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" accept="image/*">
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">Or use existing image path:</p>
                                        <input type="text" name="option_d_image_path" placeholder="images/example.jpg" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="mt-4 text-xs text-gray-500 flex items-center">
                            <i data-lucide="info" class="w-4 h-4 mr-1 text-indigo-500"></i>
                            Select the radio button next to the correct answer option.
                        </p>
                    </div>
                </div>
                <div class="flex justify-end p-5 border-t border-gray-200 gap-3 sticky bottom-0 bg-white z-10">
                    <button type="button" 
                            class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition text-sm font-medium flex items-center gap-1 shadow-sm hover:-translate-y-1"
                            onclick="closeAddQuestionModal()">
                        <i data-lucide="x" class="h-4 w-4"></i>
                        Cancel
                    </button>
                    <button type="button" 
                            class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition text-sm font-medium flex items-center gap-2 shadow-sm hover:-translate-y-1"
                            onclick="previewQuestion()">
                        <i data-lucide="eye" class="h-4 w-4"></i>
                        Preview
                    </button>
                    <button type="submit" 
                            class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition text-sm font-medium flex items-center gap-2 shadow-sm hover:-translate-y-1">
                        <i data-lucide="plus" class="h-4 w-4"></i>
                        Add Question
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Question Preview Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-70 z-50 hidden items-center justify-center backdrop-blur-sm transition-all duration-300" id="previewQuestionModal">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-4 transform transition-all duration-300">
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 p-5 rounded-t-xl flex items-center justify-between">
                <h5 class="text-lg font-semibold text-white flex items-center">
                    <i data-lucide="eye" class="h-5 w-5 mr-2"></i>
                    Question Preview
                </h5>
                <button type="button" class="text-white/80 hover:text-white focus:outline-none" onclick="closePreviewModal()">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>
            <div class="p-6">
                <div class="bg-gray-50 rounded-xl border border-gray-200 p-6 mb-4">
                    <div class="mb-4">
                        <span class="inline-block bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-sm font-medium mb-3">Question Preview</span>
                        <div class="text-gray-800 text-lg font-medium" id="preview-question-text"></div>
                        <div class="mt-3" id="preview-question-image-container" style="display: none;">
                            <img id="preview-question-image" src="" alt="Question Image" class="max-w-full h-auto rounded-lg border border-gray-200 max-h-64">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="flex flex-col p-4 border-2 border-gray-200 rounded-lg text-gray-800 bg-white hover:border-indigo-300 transition-all duration-200">
                                <div class="flex items-center">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 text-sm font-medium mr-3">A</span>
                                    <span class="text-sm" id="preview-option-a-text"></span>
                                </div>
                                <div class="mt-3 w-full" id="preview-option-a-image-container" style="display: none;">
                                    <img id="preview-option-a-image" src="" alt="Option A Image" class="max-w-full h-auto rounded-lg border border-gray-100 max-h-36">
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="flex flex-col p-4 border-2 border-gray-200 rounded-lg text-gray-800 bg-white hover:border-indigo-300 transition-all duration-200">
                                <div class="flex items-center">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 text-sm font-medium mr-3">B</span>
                                    <span class="text-sm" id="preview-option-b-text"></span>
                                </div>
                                <div class="mt-3 w-full" id="preview-option-b-image-container" style="display: none;">
                                    <img id="preview-option-b-image" src="" alt="Option B Image" class="max-w-full h-auto rounded-lg border border-gray-100 max-h-36">
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="flex flex-col p-4 border-2 border-gray-200 rounded-lg text-gray-800 bg-white hover:border-indigo-300 transition-all duration-200">
                                <div class="flex items-center">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 text-sm font-medium mr-3">C</span>
                                    <span class="text-sm" id="preview-option-c-text"></span>
                                </div>
                                <div class="mt-3 w-full" id="preview-option-c-image-container" style="display: none;">
                                    <img id="preview-option-c-image" src="" alt="Option C Image" class="max-w-full h-auto rounded-lg border border-gray-100 max-h-36">
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="flex flex-col p-4 border-2 border-gray-200 rounded-lg text-gray-800 bg-white hover:border-indigo-300 transition-all duration-200">
                                <div class="flex items-center">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 text-sm font-medium mr-3">D</span>
                                    <span class="text-sm" id="preview-option-d-text"></span>
                                </div>
                                <div class="mt-3 w-full" id="preview-option-d-image-container" style="display: none;">
                                    <img id="preview-option-d-image" src="" alt="Option D Image" class="max-w-full h-auto rounded-lg border border-gray-100 max-h-36">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="button" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition text-sm font-medium hover:-translate-y-1 shadow-sm" onclick="closePreviewModal()">
                        Close Preview
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Quiz Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-70 z-50 hidden items-center justify-center backdrop-blur-sm transition-all duration-300" id="createQuizModal">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 transform transition-all duration-300">
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 p-5 rounded-t-xl flex items-center justify-between">
                <h5 class="text-lg font-semibold text-white flex items-center">
                    <i data-lucide="plus-circle" class="h-5 w-5 mr-2"></i>
                    Create New Quiz
                </h5>
                <button type="button" class="text-white/80 hover:text-white focus:outline-none" onclick="closeCreateQuizModal()">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>
            <form method="POST" id="createQuizForm">
                <div class="p-6">
                    <input type="hidden" name="create_quiz" value="1">
                    <div class="mb-4">
                        <label for="quiz_title" class="block text-sm font-medium text-gray-700 mb-1">Quiz Title</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                                <i data-lucide="file-text" class="w-5 h-5"></i>
                            </div>
                            <input type="text" 
                                   class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition" 
                                   id="quiz_title" 
                                   name="quiz_title" 
                                   required 
                                   minlength="3"
                                   placeholder="Enter quiz title...">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Choose a descriptive title for your quiz.</p>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition text-sm font-medium flex items-center gap-1 hover:-translate-y-1 shadow-sm" onclick="closeCreateQuizModal()">
                            <i data-lucide="x" class="h-4 w-4"></i>
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition text-sm font-medium flex items-center gap-2 hover:-translate-y-1 shadow-sm">
                            <i data-lucide="plus" class="h-4 w-4"></i>
                            Create Quiz
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Initialize toast notifications
        <?php if (isset($_SESSION['success'])): ?>
            showToast('<?php echo addslashes($_SESSION['success']); ?>', 'success');
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            showToast('<?php echo addslashes($success); ?>', 'success');
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            showToast('<?php echo addslashes($error); ?>', 'error');
        <?php endif; ?>
        
        // Highlight newly added question
        const newQuestion = document.querySelector('.highlight-new');
        if (newQuestion) {
            newQuestion.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Remove highlight after a few seconds
            setTimeout(() => {
                newQuestion.classList.remove('border-indigo-300', 'ring-2', 'ring-indigo-100', 'highlight-new');
                newQuestion.classList.add('border', 'border-gray-100');
                const newBadge = newQuestion.querySelector('.animate-pulse');
                if (newBadge) newBadge.remove();
            }, 5000);
        }
        
        // Show animation for alerts
        const alertBoxes = document.querySelectorAll('.animate-fade-in');
        alertBoxes.forEach(box => {
            box.style.display = 'flex';
            setTimeout(() => {
                box.style.opacity = '1';
                box.style.transform = 'translateY(0)';
            }, 100);
        });
    });

    // Modal functions with enhanced animations
    function openAddQuestionModal() {
        showModal('addQuestionModal');
        document.body.classList.add('overflow-hidden');
    }
    
    function closeAddQuestionModal() {
        hideModal('addQuestionModal');
        document.body.classList.remove('overflow-hidden');
    }
    
    function openCreateQuizModal() {
        showModal('createQuizModal');
        document.body.classList.add('overflow-hidden');
    }
    
    function closeCreateQuizModal() {
        hideModal('createQuizModal');
        document.body.classList.remove('overflow-hidden');
    }

    // Improved modal functions with animations
    function showModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Add entry animation
        const modalContent = modal.querySelector('div');
        modalContent.style.opacity = '0';
        modalContent.style.transform = 'scale(0.95) translateY(-10px)';
        
        setTimeout(() => {
            modalContent.style.opacity = '1';
            modalContent.style.transform = 'scale(1) translateY(0)';
        }, 10);
    }

    // Hide modal function with exit animation
    function hideModal(modalId) {
        const modal = document.getElementById(modalId);
        const modalContent = modal.querySelector('div');
        
        modalContent.style.opacity = '0';
        modalContent.style.transform = 'scale(0.95) translateY(-10px)';
        
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            modalContent.style.opacity = '';
            modalContent.style.transform = '';
        }, 200);
    }

    // Question management functions
    function editQuestion(questionId) {
        // Redirect to edit page or open edit modal
        window.location.href = `edit_question.php?course_id=<?php echo $course_id; ?>&quiz_id=<?php echo $current_quiz_id; ?>&question_id=${questionId}`;
    }
    
    function deleteQuestion(questionId) {
        if (confirm('Are you sure you want to delete this question? This action cannot be undone.')) {
            window.location.href = `delete_question.php?course_id=<?php echo $course_id; ?>&quiz_id=<?php echo $current_quiz_id; ?>&question_id=${questionId}`;
        }
    }
    
    // Question preview functions with improved transitions
    function previewQuestion() {
        // Get values from form
        const questionText = document.getElementById('question_text').value;
        const optionA = document.querySelector('input[name="option_a"]').value;
        const optionB = document.querySelector('input[name="option_b"]').value;
        const optionC = document.querySelector('input[name="option_c"]').value;
        const optionD = document.querySelector('input[name="option_d"]').value;
        const correctAnswer = document.querySelector('input[name="correct_answer"]:checked')?.value || '';
        
        // Set text values in preview
        document.getElementById('preview-question-text').textContent = questionText;
        document.getElementById('preview-option-a-text').textContent = optionA;
        document.getElementById('preview-option-b-text').textContent = optionB;
        document.getElementById('preview-option-c-text').textContent = optionC;
        document.getElementById('preview-option-d-text').textContent = optionD;
        
        // Handle question image
        handleImagePreview('question_image', 'question_image_path', 'preview-question-image', 'preview-question-image-container');
        
        // Handle option images
        handleImagePreview('option_a_image', 'option_a_image_path', 'preview-option-a-image', 'preview-option-a-image-container');
        handleImagePreview('option_b_image', 'option_b_image_path', 'preview-option-b-image', 'preview-option-b-image-container');
        handleImagePreview('option_c_image', 'option_c_image_path', 'preview-option-c-image', 'preview-option-c-image-container');
        handleImagePreview('option_d_image', 'option_d_image_path', 'preview-option-d-image', 'preview-option-d-image-container');
        
        // Reset all options to default style
        document.querySelectorAll('#previewQuestionModal .border-2').forEach(option => {
            option.className = 'flex flex-col p-4 border-2 border-gray-200 rounded-lg text-gray-800 bg-white hover:border-indigo-300 transition-all duration-200';
        });
        
        // Highlight correct answer if selected
        if (correctAnswer) {
            const correctOptionElement = document.querySelector(`#preview-option-${correctAnswer.toLowerCase()}-text`).closest('.border-2');
            correctOptionElement.className = 'flex flex-col p-4 border-2 border-green-500 rounded-lg text-gray-800 bg-green-50 transition-all duration-200';
            
            // Add correct badge
            const hasCorrectBadge = correctOptionElement.querySelector('.correct-badge');
            if (!hasCorrectBadge) {
                const correctIndicator = document.createElement('span');
                correctIndicator.className = 'correct-badge px-3 py-1.5 rounded-full text-white text-xs font-medium shadow-sm ml-auto mt-1 bg-gradient-to-r from-green-500 to-green-600';
                correctIndicator.innerHTML = '<i class="bi bi-check-lg mr-1"></i> Correct Answer';
                correctOptionElement.querySelector('.flex.items-center').appendChild(correctIndicator);
            }
        }
        
        // Show preview modal
        showModal('previewQuestionModal');
    }
    
    function handleImagePreview(fileInputId, pathInputId, previewImgId, containerImgId) {
        const fileInput = document.getElementById(fileInputId);
        const pathInput = document.querySelector(`input[name="${pathInputId}"]`);
        const previewImg = document.getElementById(previewImgId);
        const container = document.getElementById(containerImgId);
        
        // Reset display
        container.style.display = 'none';
        
        // Check if a file is selected
        if (fileInput && fileInput.files && fileInput.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                container.style.display = 'block';
            };
            reader.readAsDataURL(fileInput.files[0]);
        } 
        // Check if a path is specified
        else if (pathInput && pathInput.value) {
            previewImg.src = '../assets/' + pathInput.value;
            container.style.display = 'block';
        }
    }
    
    function closePreviewModal() {
        hideModal('previewQuestionModal');
    }
    
    // Handle correct answer selection in form
    document.addEventListener('DOMContentLoaded', function() {
        // Hide all correct answer badges initially
        document.querySelectorAll('#option-a-correct, #option-b-correct, #option-c-correct, #option-d-correct').forEach(badge => {
            badge.classList.add('hidden');
        });
        
        // Add event listeners to radio buttons
        document.querySelectorAll('input[name="correct_answer"]').forEach(radio => {
            radio.addEventListener('change', updateCorrectAnswerBadges);
        });
        
        // Call once to initialize based on any pre-selected radio
        updateCorrectAnswerBadges();
        
        function updateCorrectAnswerBadges() {
            // Hide all badges first
            document.querySelectorAll('#option-a-correct, #option-b-correct, #option-c-correct, #option-d-correct').forEach(badge => {
                badge.classList.add('hidden');
            });
            
            // Show badge for selected answer
            const selectedOption = document.querySelector('input[name="correct_answer"]:checked');
            if (selectedOption) {
                const optionLetter = selectedOption.value.toLowerCase();
                document.getElementById(`option-${optionLetter}-correct`).classList.remove('hidden');
                
                // Reset all containers
                document.querySelectorAll('.bg-white.p-4.rounded-lg.border').forEach(container => {
                    container.classList.remove('border-green-300', 'bg-green-50');
                    container.classList.add('border-gray-200');
                });
                
                // Add highlight to the selected option container with transition
                const targetContainer = selectedOption.closest('.bg-white.p-4.rounded-lg.border');
                targetContainer.classList.remove('border-gray-200');
                targetContainer.classList.add('border-green-300', 'bg-green-50');
            }
        }
    });

    // Enhanced toast notification system with animations
    function showToast(message, type = 'info') {
        // Create toast container if it doesn't exist
        let toastContainer = document.getElementById('toast-container');
        
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'fixed bottom-4 right-4 z-50 flex flex-col gap-2';
            document.body.appendChild(toastContainer);
        }
        
        // Create toast element
        const toast = document.createElement('div');
        toast.style.transition = 'all 0.3s ease';
        toast.style.transform = 'translateX(100%)';
        toast.style.opacity = '0';
        
        // Set background color based on type
        let bgColor, textColor, iconName;
        switch (type) {
            case 'success':
                bgColor = 'bg-green-500';
                textColor = 'text-white';
                iconName = 'check-circle';
                break;
            case 'error':
                bgColor = 'bg-red-500';
                textColor = 'text-white';
                iconName = 'alert-circle';
                break;
            case 'warning':
                bgColor = 'bg-amber-500';
                textColor = 'text-white';
                iconName = 'alert-triangle';
                break;
            default: // info
                bgColor = 'bg-indigo-500';
                textColor = 'text-white';
                iconName = 'info';
        }
        
        // Apply styles
        toast.className = `${bgColor} ${textColor} rounded-lg shadow-lg p-4 mb-2 flex items-center`;
        
        // Add content
        toast.innerHTML = `
            <i data-lucide="${iconName}" class="w-5 h-5 mr-2"></i>
            <span>${message}</span>
            <button class="ml-auto text-white/80 hover:text-white" onclick="this.parentElement.remove()">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        `;
        
        // Add to container
        toastContainer.appendChild(toast);
        
        // Initialize icon
        if (typeof lucide !== 'undefined') {
            lucide.createIcons({
                attrs: {
                    class: ["stroke-current"]
                }
            });
        }
        
        // Animate in
        setTimeout(() => {
            toast.style.transform = 'translateX(0)';
            toast.style.opacity = '1';
        }, 10);
        
        // Remove after 4 seconds
        setTimeout(() => {
            toast.style.transform = 'translateX(100%)';
            toast.style.opacity = '0';
            
            // Remove from DOM after animation completes
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 4000);
    }
    </script>
</body>
</html>
