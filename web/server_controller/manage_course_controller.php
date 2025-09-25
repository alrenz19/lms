<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once 'video_controller.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'super_admin')) {
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    } else {
        header("Location: index.php");
        exit;
    }
}

// -------------------- Handle AJAX GET for filtering --------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['filter'])) {
    header('Content-Type: application/json');

    $filter = $_GET['filter'];
    $userId = $_SESSION['user_id'];

    if ($filter === 'mine') {
        $stmt = $conn->prepare("
            SELECT c.id, c.title, c.description, c.created_at, c.created_by,
                   (SELECT COUNT(*) FROM user_progress up WHERE up.course_id = c.id) AS enrollment_count,
                   (SELECT COUNT(*) FROM questions q WHERE q.course_id = c.id) AS question_count
            FROM courses c
            WHERE c.created_by = ? AND c.removed = 0
            ORDER BY c.created_at DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $courses_result = $stmt->get_result();
    } else {
        $courses_result = $conn->query("
            SELECT c.id, c.title, c.description, c.created_at, c.created_by,
                   (SELECT COUNT(*) FROM user_progress up WHERE up.course_id = c.id) AS enrollment_count,
                   (SELECT COUNT(*) FROM questions q WHERE q.course_id = c.id) AS question_count
            FROM courses c
            WHERE c.removed = 0
            ORDER BY c.created_at DESC
        ");
    }

    $courses = $courses_result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($courses);
    exit;
}



// -------------------- Dashboard Summary Queries --------------------

function getCount($conn, $query) {
    $result = $conn->query($query);
    return $result ? $result->fetch_assoc()['count'] : 0;
}

$courses_count = getCount($conn, "SELECT COUNT(*) as count FROM courses WHERE removed = 0");
$total_enrollments = getCount($conn, "SELECT COUNT(*) as count FROM user_progress WHERE removed = 0");
$course_completions = getCount($conn, "SELECT COUNT(*) as count FROM user_progress WHERE score = 100 AND removed = 0");

// -------------------- Fetch Courses --------------------

$courses_result = $conn->query("
    SELECT 
        c.id, 
        c.title, 
        c.description, 
        c.created_at,
        COUNT(DISTINCT up.user_id) AS enrollment_count,
        COUNT(DISTINCT q.id) AS question_count
    FROM courses c
    LEFT JOIN questions q ON c.id = q.course_id AND q.removed = 0
    LEFT JOIN user_progress up ON q.course_id = up.course_id AND up.removed = 0
    WHERE c.removed = 0
    GROUP BY c.id
    ORDER BY c.created_at DESC
");

// -------------------- Handle AJAX --------------------

$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // === Add Course ===
    if ($is_ajax && isset($_POST['course_add'])) {
        handleCourseAdd($conn);
        exit;
    }

    // === Delete Course ===
    if ($is_ajax && isset($_POST['course_delete'])) {
        handleCourseDelete($conn);
        exit;
    }

    // === Add Quiz ===
    if ($is_ajax && isset($_POST['add_question'])) {
        handleQuizAdd($conn);
        exit;
    }

    // === 404 Fallback ===
    http_response_code(404);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}


// -------------------- Handlers --------------------
function handleCourseAdd($conn) {
    global $is_ajax;

    header('Content-Type: application/json');

    // Validate required fields
    if (empty($_POST['course_title'])) {
        echo json_encode(['success' => false, 'message' => "Missing course title"]);
        return;
    }

    $title = $conn->real_escape_string(trim($_POST['course_title']));
    $description = $conn->real_escape_string(trim($_POST['course_description'] ?? ''));
    $created_by = $_SESSION['user_id'];

    // Check if module data exists
    $has_titles = isset($_POST['module_titles']) && is_array($_POST['module_titles']);
    $has_files = isset($_FILES['module_files']) && count($_FILES['module_files']['name']) > 0;

    // ✅ Pre-validate modules if provided
    if ($has_titles && $has_files) {
        try {
            $tempCourseId = -1; // fake course id for validation only
            $handler = new ModuleHandler($conn, $tempCourseId);
            $handler->validateModulesOnly();
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
            return;
        }
    }

    // ✅ Insert the new course
    $stmt = $conn->prepare("INSERT INTO courses (title, description, created_by) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $title, $description, $created_by);

    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => "Course creation failed: " . $stmt->error]);
        return;
    }

    $new_course_id = $conn->insert_id;

    // ✅ Now save modules if uploaded
    if ($has_titles && $has_files) {
        try {
            $handler = new ModuleHandler($conn, $new_course_id);
            $handler->saveModules();
        } catch (Exception $e) {
            // Rollback course if modules failed
            $conn->query("DELETE FROM courses WHERE id = {$new_course_id}");
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            return;
        }
    }

    // ✅ Success response
    echo json_encode([
        'success' => true,
        'message' => "Course created successfully!",
        'course_id' => $new_course_id,
        'has_course_module' => $has_titles && $has_files
    ]);
}

function handleCourseDelete($conn) {
    if (empty($_POST['course_id'])) {
        echo json_encode(['success' => false, 'message' => "Missing course ID"]);
        return;
    }

    $course_id = (int)$_POST['course_id'];

    try {
        $conn->begin_transaction();

        $updates = [
            // Soft delete user progress
            "UPDATE user_progress up 
             JOIN questions q ON up.course_id = q.course_id 
             SET up.removed = 1 
             WHERE q.course_id = ?",

            // Soft delete questions
            "UPDATE questions
             SET removed = 1 
             WHERE course_id = ?",

            // Soft delete quizzes
            "UPDATE quizzes SET removed = 1 WHERE course_id = ?",

            // Soft delete course
            "UPDATE courses SET removed = 1 WHERE id = ?",

            // Soft delete the modules
            "UPDATE course_videos SET removed = 1 WHERE course_id = ?"
        ];

        foreach ($updates as $query) {
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $course_id);
            $stmt->execute();
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => "Course successfully removed.", 'course_id' => $course_id]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => "Deletion failed.", 'error' => $e->getMessage()]);
    }
}


function handleQuizAdd($conn) {
    header('Content-Type: application/json');
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    try {
        if (empty($_POST['questions']) || empty($_POST['course_id'])) {
            throw new Exception("Missing questions or course_id in the request.");
        }

        $questions = $_POST['questions'];
        $files = $_FILES['questions'];
        $course_id = $_POST['course_id'];

        $upload_dir = __DIR__ . '/../../uploads/question_images';
        $upload_url_path = '/uploads/question_images';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        foreach ($questions as $qIndex => $qData) {
            // Upload question image
            $questionImagePath = null;
            $qImage = normalizeFilesArray($files, $qIndex, 'image');
            if (!empty($qImage['name'])) {
                $name = preg_replace("/[^a-zA-Z0-9.\-_]/", "_", basename($qImage['name']));
                $uniqueName = uniqid('mod_', true) . '_' . $name;
                $destination = $upload_dir . DIRECTORY_SEPARATOR . $uniqueName;

                if (!move_uploaded_file($qImage['tmp_name'], $destination)) {
                    throw new Exception("Failed to upload question image at index $qIndex.");
                }
                $questionImagePath = $upload_url_path . '/' . $uniqueName;
            }

            // Option texts
            $optionA = $qData['options']['a'] ?? null;
            $optionB = $qData['options']['b'] ?? null;
            $optionC = $qData['options']['c'] ?? null;
            $optionD = $qData['options']['d'] ?? null;

            // Option images
            $optionAImage = $optionBImage = $optionCImage = $optionDImage = null;

            foreach (['a', 'b', 'c', 'd'] as $letter) {
                $fileInfo = normalizeFilesArray($files, $qIndex, 'option_images', $letter);
                if (!empty($fileInfo['name'])) {
                    $fileName = preg_replace("/[^a-zA-Z0-9.\-_]/", "_", basename($fileInfo['name']));
                    $uniqueName = uniqid('mod_', true) . '_' . $fileName;
                    $destination = $upload_dir . DIRECTORY_SEPARATOR . $uniqueName;

                    if (!move_uploaded_file($fileInfo['tmp_name'], $destination)) {
                        throw new Exception("Failed to upload option '$letter' image for question index $qIndex.");
                    }

                    ${"option" . strtoupper($letter) . "Image"} = $upload_url_path . '/' . $uniqueName;
                }
            }

            // Insert into DB
            $stmt = $conn->prepare("INSERT INTO questions (
                course_id, question_text, option_a, option_b, option_c, option_d,
                correct_answer, question_image,
                option_a_image, option_b_image, option_c_image, option_d_image
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            $stmt->bind_param(
                "isssssssssss",
                $course_id,
                $qData['text'],
                $optionA,
                $optionB,
                $optionC,
                $optionD,
                $qData['correct_answer'],
                $questionImagePath,
                $optionAImage,
                $optionBImage,
                $optionCImage,
                $optionDImage
            );

            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
        }

        echo json_encode(['success' => true, 'message' => 'Quiz saved successfully hng admh']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}


function normalizeFilesArray($files, $qIndex, $field, $subKey = null) {
    $result = [];

    if (!isset($files['name'][$qIndex][$field])) return null;

    if ($subKey !== null) {
        // For option_images[a|b|c|d]
        if (!isset($files['name'][$qIndex][$field][$subKey])) return null;

        $result['name'] = $files['name'][$qIndex][$field][$subKey];
        $result['type'] = $files['type'][$qIndex][$field][$subKey];
        $result['tmp_name'] = $files['tmp_name'][$qIndex][$field][$subKey];
        $result['error'] = $files['error'][$qIndex][$field][$subKey];
        $result['size'] = $files['size'][$qIndex][$field][$subKey];
    } else {
        // For image (question image)
        $result['name'] = $files['name'][$qIndex][$field];
        $result['type'] = $files['type'][$qIndex][$field];
        $result['tmp_name'] = $files['tmp_name'][$qIndex][$field];
        $result['error'] = $files['error'][$qIndex][$field];
        $result['size'] = $files['size'][$qIndex][$field];
    }

    return $result;
}

