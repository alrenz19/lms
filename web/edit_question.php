<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$question_id = $_GET['question_id'] ?? 0;
$course_id = $_GET['course_id'] ?? 0;

if (!$question_id || !$course_id) {
    header("Location: manage_courses.php");
    exit;
}

// Get question details
$stmt = $conn->prepare("SELECT * FROM questions WHERE id = ?");
$stmt->bind_param("i", $question_id);
$stmt->execute();
$question = $stmt->get_result()->fetch_assoc();

if (!$question) {
    $_SESSION['error'] = "Question not found.";
    header("Location: manage_quiz.php?course_id=" . $course_id . "&quiz_id=" . $quiz_id);
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_question'])) {
    try {
        $conn->begin_transaction();
        
        $question_text = $conn->real_escape_string($_POST['question_text']);
        $option_a = $conn->real_escape_string($_POST['option_a']);
        $option_b = $conn->real_escape_string($_POST['option_b']);
        $option_c = $conn->real_escape_string($_POST['option_c']);
        $option_d = $conn->real_escape_string($_POST['option_d']);
        $correct_answer = $_POST['correct_answer'];
        
        // Initialize with existing values
        $question_image = $question['question_image'];
        $option_a_image = $question['option_a_image'];
        $option_b_image = $question['option_b_image'];
        $option_c_image = $question['option_c_image'];
        $option_d_image = $question['option_d_image'];
        
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
            $question_image = 'assets:' . $_POST['question_image_path'];
        } elseif (isset($_POST['remove_question_image']) && $_POST['remove_question_image'] == 1) {
            $question_image = "";
        }
        
        // Process option A image
        if (isset($_FILES['option_a_image']) && $_FILES['option_a_image']['error'] == 0) {
            $file_name = time() . '_A_' . basename($_FILES['option_a_image']['name']);
            $target = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['option_a_image']['tmp_name'], $target)) {
                $option_a_image = $file_name;
            }
        } elseif (!empty($_POST['option_a_image_path'])) {
            $option_a_image = 'assets:' . $_POST['option_a_image_path'];
        } elseif (isset($_POST['remove_option_a_image']) && $_POST['remove_option_a_image'] == 1) {
            $option_a_image = "";
        }
        
        // Process option B image
        if (isset($_FILES['option_b_image']) && $_FILES['option_b_image']['error'] == 0) {
            $file_name = time() . '_B_' . basename($_FILES['option_b_image']['name']);
            $target = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['option_b_image']['tmp_name'], $target)) {
                $option_b_image = $file_name;
            }
        } elseif (!empty($_POST['option_b_image_path'])) {
            $option_b_image = 'assets:' . $_POST['option_b_image_path'];
        } elseif (isset($_POST['remove_option_b_image']) && $_POST['remove_option_b_image'] == 1) {
            $option_b_image = "";
        }
        
        // Process option C image
        if (isset($_FILES['option_c_image']) && $_FILES['option_c_image']['error'] == 0) {
            $file_name = time() . '_C_' . basename($_FILES['option_c_image']['name']);
            $target = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['option_c_image']['tmp_name'], $target)) {
                $option_c_image = $file_name;
            }
        } elseif (!empty($_POST['option_c_image_path'])) {
            $option_c_image = 'assets:' . $_POST['option_c_image_path'];
        } elseif (isset($_POST['remove_option_c_image']) && $_POST['remove_option_c_image'] == 1) {
            $option_c_image = "";
        }
        
        // Process option D image
        if (isset($_FILES['option_d_image']) && $_FILES['option_d_image']['error'] == 0) {
            $file_name = time() . '_D_' . basename($_FILES['option_d_image']['name']);
            $target = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['option_d_image']['tmp_name'], $target)) {
                $option_d_image = $file_name;
            }
        } elseif (!empty($_POST['option_d_image_path'])) {
            $option_d_image = 'assets:' . $_POST['option_d_image_path'];
        } elseif (isset($_POST['remove_option_d_image']) && $_POST['remove_option_d_image'] == 1) {
            $option_d_image = "";
        }
        
        // Update question in database
        $stmt = $conn->prepare("UPDATE questions SET question_text = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, correct_answer = ?, question_image = ?, option_a_image = ?, option_b_image = ?, option_c_image = ?, option_d_image = ? WHERE id = ?");
        $stmt->bind_param("sssssssssssi", $question_text, $option_a, $option_b, $option_c, $option_d, $correct_answer, $question_image, $option_a_image, $option_b_image, $option_c_image, $option_d_image, $question_id);
        $stmt->execute();
        
        $conn->commit();
        $_SESSION['success'] = "Question updated successfully!";
        header("Location: manage_quiz.php?course_id=" . $course_id);
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error updating question: " . $e->getMessage();
    }
}

// Get course and quiz details for breadcrumbs
$stmt = $conn->prepare("SELECT title FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("SELECT title FROM quizzes WHERE id = ?");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - Edit Question</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <!--<link rel="stylesheet" href="./public/css/tailwind.min.css" />-->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .option-container {
            transition: all 0.2s ease;
        }
        .option-container:hover {
            background-color: #eef2ff;
        }
    </style>
</head>
<body class="bg-blue-50">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="p-8 sm:ml-72">
        <div class="container mx-auto">
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 rounded-xl p-6 mb-6 shadow-sm">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="bg-white/10 p-3 rounded-lg mr-4">
                            <i data-lucide="help-circle" class="h-8 w-8 text-white"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-white">Edit Question</h1>
                            <p class="text-indigo-100">
                                <a href="manage_courses.php" class="hover:underline">Courses</a> &raquo; 
                                <a href="manage_quiz.php?course_id=<?php echo $course_id; ?>" class="hover:underline"><?php echo htmlspecialchars($course['title']); ?></a>
                            </p>
                        </div>
                    </div>
                    
                    <a href="manage_quiz.php?course_id=<?php echo $course_id; ?>&quiz_id=<?php echo $quiz_id; ?>" class="px-4 py-2.5 bg-white text-indigo-600 rounded-lg flex items-center gap-2 transition-all duration-200 font-medium shadow-sm">
                        <i data-lucide="arrow-left" class="w-5 h-5"></i>
                        <span>Back to Quiz</span>
                    </a>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="mb-6 rounded-xl p-5 flex items-start gap-3 bg-amber-50 text-amber-700 border border-amber-100 shadow-sm">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-500 mt-0.5"></i>
                    <div>
                        <h3 class="font-medium mb-1">Warning!</h3>
                        <p><?php echo $error; ?></p>
                    </div>
                    <button type="button" class="ml-auto text-gray-400 hover:text-gray-600" onclick="this.parentElement.remove()">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="update_question" value="1">
                    
                    <div class="mb-6">
                        <label for="question_text" class="block text-sm font-medium text-gray-700 mb-2">Question Text</label>
                        <div class="relative">
                            <textarea class="w-full pl-4 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition shadow-sm" 
                                    id="question_text" 
                                    name="question_text" 
                                    rows="3"
                                    required><?php echo htmlspecialchars($question['question_text']); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Question Image (Optional)</label>
                        <?php if (!empty($question['question_image'])): ?>
                            <div class="mb-3 p-4 border border-gray-200 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-gray-600 font-medium">Current Image:</span>
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="remove_question_image" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-600">Remove image</span>
                                    </label>
                                </div>
                                <div class="w-full">
                                    <?php if (strpos($question['question_image'], 'assets:') === 0): ?>
                                        <img src="../assets/<?php echo htmlspecialchars(substr($question['question_image'], 7)); ?>" 
                                            alt="Question Image" 
                                            class="max-w-full h-auto rounded-lg border border-gray-200 max-h-48">
                                        <p class="mt-1 text-xs text-gray-500">Path: <?php echo htmlspecialchars(substr($question['question_image'], 7)); ?></p>
                                    <?php else: ?>
                                        <img src="../<?php echo htmlspecialchars($question['question_image']); ?>" 
                                            alt="Question Image" 
                                            class="max-w-full h-auto rounded-lg border border-gray-200 max-h-48">
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Upload new image:</label>
                                <input type="file" name="question_image" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500" accept="image/*">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Or use existing image path:</label>
                                <input type="text" name="question_image_path" placeholder="images/example.jpg" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500">
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Upload a new image or specify a path from the assets folder.</p>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Answer Options</label>
                        
                        <div class="space-y-6">
                            <!-- Option A -->
                            <div class="p-4 border border-gray-200 rounded-lg option-container">
                                <div class="flex items-center mb-3">
                                    <input type="radio" name="correct_answer" value="A" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 mr-2"
                                           <?php echo ($question['correct_answer'] === 'A') ? 'checked' : 'disabled'; ?>>
                                    <label class="inline-block w-8 text-center font-medium text-indigo-600 bg-indigo-50 rounded-md py-1 mr-3">A</label>
                                    <p class="text-sm text-gray-700 font-medium">Option A</p>
                                </div>
                                
                                <div class="mb-3">
                                    <input type="text" name="option_a" value="<?php echo htmlspecialchars($question['option_a']); ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500" 
                                           required>
                                </div>
                                
                                <?php if (!empty($question['option_a_image'])): ?>
                                    <div class="mb-3 p-4 border border-gray-200 rounded-lg">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm text-gray-600 font-medium">Current Image:</span>
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" name="remove_option_a_image" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                <span class="ml-2 text-sm text-gray-600">Remove image</span>
                                            </label>
                                        </div>
                                        <div class="w-full">
                                            <?php if (strpos($question['option_a_image'], 'assets:') === 0): ?>
                                                <img src="../assets/<?php echo htmlspecialchars(substr($question['option_a_image'], 7)); ?>" 
                                                    alt="Option A Image" 
                                                    class="max-w-full h-auto rounded-lg border border-gray-200 max-h-36">
                                                <p class="mt-1 text-xs text-gray-500">Path: <?php echo htmlspecialchars(substr($question['option_a_image'], 7)); ?></p>
                                            <?php else: ?>
                                                <img src="../uploads/question_images/<?php echo htmlspecialchars($question['option_a_image']); ?>" 
                                                    alt="Option A Image" 
                                                    class="max-w-full h-auto rounded-lg border border-gray-200 max-h-36">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm text-gray-600 mb-1">Upload new image:</label>
                                        <input type="file" name="option_a_image" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500" accept="image/*">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-600 mb-1">Or use existing image path:</label>
                                        <input type="text" name="option_a_image_path" placeholder="images/example.jpg" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Option B -->
                            <div class="p-4 border border-gray-200 rounded-lg option-container">
                                <div class="flex items-center mb-3">
                                    <input type="radio" name="correct_answer" value="B" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 mr-2"
                                           <?php echo ($question['correct_answer'] === 'B') ? 'checked' : 'disabled'; ?>>
                                    <label class="inline-block w-8 text-center font-medium text-indigo-600 bg-indigo-50 rounded-md py-1 mr-3">B</label>
                                    <p class="text-sm text-gray-700 font-medium">Option B</p>
                                </div>
                                
                                <div class="mb-3">
                                    <input type="text" name="option_b" value="<?php echo htmlspecialchars($question['option_b']); ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500" 
                                           required>
                                </div>
                                
                                <?php if (!empty($question['option_b_image'])): ?>
                                    <div class="mb-3 p-4 border border-gray-200 rounded-lg">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm text-gray-600 font-medium">Current Image:</span>
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" name="remove_option_b_image" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                <span class="ml-2 text-sm text-gray-600">Remove image</span>
                                            </label>
                                        </div>
                                        <div class="w-full">
                                            <?php if (strpos($question['option_b_image'], 'assets:') === 0): ?>
                                                <img src="../assets/<?php echo htmlspecialchars(substr($question['option_b_image'], 7)); ?>" 
                                                    alt="Option B Image" 
                                                    class="max-w-full h-auto rounded-lg border border-gray-200 max-h-36">
                                                <p class="mt-1 text-xs text-gray-500">Path: <?php echo htmlspecialchars(substr($question['option_b_image'], 7)); ?></p>
                                            <?php else: ?>
                                                <img src="../uploads/question_images/<?php echo htmlspecialchars($question['option_b_image']); ?>" 
                                                    alt="Option B Image" 
                                                    class="max-w-full h-auto rounded-lg border border-gray-200 max-h-36">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm text-gray-600 mb-1">Upload new image:</label>
                                        <input type="file" name="option_b_image" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500" accept="image/*">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-600 mb-1">Or use existing image path:</label>
                                        <input type="text" name="option_b_image_path" placeholder="images/example.jpg" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Option C -->
                            <div class="p-4 border border-gray-200 rounded-lg option-container">
                                <div class="flex items-center mb-3">
                                    <input type="radio" name="correct_answer" value="C" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 mr-2"
                                           <?php echo ($question['correct_answer'] === 'C') ? 'checked' : 'disabled'; ?>>
                                    <label class="inline-block w-8 text-center font-medium text-indigo-600 bg-indigo-50 rounded-md py-1 mr-3">C</label>
                                    <p class="text-sm text-gray-700 font-medium">Option C</p>
                                </div>
                                
                                <div class="mb-3">
                                    <input type="text" name="option_c" value="<?php echo htmlspecialchars($question['option_c']); ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500" 
                                           required>
                                </div>
                                
                                <?php if (!empty($question['option_c_image'])): ?>
                                    <div class="mb-3 p-4 border border-gray-200 rounded-lg">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm text-gray-600 font-medium">Current Image:</span>
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" name="remove_option_c_image" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                <span class="ml-2 text-sm text-gray-600">Remove image</span>
                                            </label>
                                        </div>
                                        <div class="w-full">
                                            <?php if (strpos($question['option_c_image'], 'assets:') === 0): ?>
                                                <img src="../assets/<?php echo htmlspecialchars(substr($question['option_c_image'], 7)); ?>" 
                                                    alt="Option C Image" 
                                                    class="max-w-full h-auto rounded-lg border border-gray-200 max-h-36">
                                                <p class="mt-1 text-xs text-gray-500">Path: <?php echo htmlspecialchars(substr($question['option_c_image'], 7)); ?></p>
                                            <?php else: ?>
                                                <img src="../uploads/question_images/<?php echo htmlspecialchars($question['option_c_image']); ?>" 
                                                    alt="Option C Image" 
                                                    class="max-w-full h-auto rounded-lg border border-gray-200 max-h-36">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm text-gray-600 mb-1">Upload new image:</label>
                                        <input type="file" name="option_c_image" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500" accept="image/*">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-600 mb-1">Or use existing image path:</label>
                                        <input type="text" name="option_c_image_path" placeholder="images/example.jpg" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Option D -->
                            <div class="p-4 border border-gray-200 rounded-lg option-container">
                                <div class="flex items-center mb-3">
                                    <input type="radio" name="correct_answer" value="D" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 mr-2"
                                           <?php echo ($question['correct_answer'] === 'D') ? 'checked' : 'disabled'; ?>>
                                    <label class="inline-block w-8 text-center font-medium text-indigo-600 bg-indigo-50 rounded-md py-1 mr-3">D</label>
                                    <p class="text-sm text-gray-700 font-medium">Option D</p>
                                </div>
                                
                                <div class="mb-3">
                                    <input type="text" name="option_d" value="<?php echo htmlspecialchars($question['option_d']); ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500" 
                                           required>
                                </div>
                                
                                <?php if (!empty($question['option_d_image'])): ?>
                                    <div class="mb-3 p-4 border border-gray-200 rounded-lg">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm text-gray-600 font-medium">Current Image:</span>
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" name="remove_option_d_image" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                <span class="ml-2 text-sm text-gray-600">Remove image</span>
                                            </label>
                                        </div>
                                        <div class="w-full">
                                            <?php if (strpos($question['option_d_image'], 'assets:') === 0): ?>
                                                <img src="../assets/<?php echo htmlspecialchars(substr($question['option_d_image'], 7)); ?>" 
                                                    alt="Option D Image" 
                                                    class="max-w-full h-auto rounded-lg border border-gray-200 max-h-36">
                                                <p class="mt-1 text-xs text-gray-500">Path: <?php echo htmlspecialchars(substr($question['option_d_image'], 7)); ?></p>
                                            <?php else: ?>
                                                <img src="../uploads/question_images/<?php echo htmlspecialchars($question['option_d_image']); ?>" 
                                                    alt="Option D Image" 
                                                    class="max-w-full h-auto rounded-lg border border-gray-200 max-h-36">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm text-gray-600 mb-1">Upload new image:</label>
                                        <input type="file" name="option_d_image" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500" accept="image/*">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-600 mb-1">Or use existing image path:</label>
                                        <input type="text" name="option_d_image_path" placeholder="images/example.jpg" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-3">
                        <a href="manage_quiz.php?course_id=<?php echo $course_id; ?>&quiz_id=<?php echo $quiz_id; ?>" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-xl transition text-sm font-medium">
                            Cancel
                        </a>
                        <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition text-sm font-medium">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Lucide icons
        lucide.createIcons();
    });
    </script>
</body>
</html> 