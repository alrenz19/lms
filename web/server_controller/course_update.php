<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once 'video_controller.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// -------------------- Handle AJAX --------------------

$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // === Add Course ===
    if ($is_ajax && isset($_POST['update_course'])) {
        handleCourseUpdate($conn);
        exit;
    }

    // === Delete Course ===
    if ($is_ajax && isset($_POST['course_delete'])) {
        handleModuleDelete($conn);
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
function handleCourseUpdate($conn) {
    header('Content-Type: application/json');

    if (empty($_POST['title'])) {
        echo json_encode(['success' => false, 'message' => "Missing required fields"]);
        return;
    }

    // Validate module upload BEFORE inserting the course
    $has_titles = isset($_POST['module_titles']) && is_array($_POST['module_titles']);
    $has_files = isset($_FILES['module_files']) && count($_FILES['module_files']['name']) > 0;

    if ($has_titles && $has_files) {
        try {
            // Use a temporary fake course ID to validate files first
            $tempCourseId = -1; // Not inserted yet
            $handler = new ModuleHandler($conn, $tempCourseId);
            $handler->validateModulesOnly(); // You’ll create this method (explained below)
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
            return;
        }
    }

    // Now insert course
    $title = $conn->real_escape_string(trim($_POST['title']));
    $description = $conn->real_escape_string(trim($_POST['description'] ?? ''));
    $course_id = $_POST['course_id'];

    $stmt = $conn->prepare("UPDATE courses SET title = ?, description = ? WHERE id = ?");
    $stmt->bind_param("ssi", $title, $description, $course_id);

    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => "Course Updated failed: " . $stmt->error]);
        return;
    }

    $updated_course_id = $course_id;

    // Now save modules (real files)
    if ($has_titles && $has_files) {
        try {
            $handler = new ModuleHandler($conn, $updated_course_id);
            $handler->saveModules(); // Now safe to actually move files
        } catch (Exception $e) {
            // Rollback course creation if module saving failed
            $conn->query("DELETE FROM courses WHERE id = {$updated_course_id}");
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            return;
        }
    }

    echo json_encode([
        'success' => true,
        'message' => "Course Updated successfully!",
        'course_id' => $updated_course_id,
        'has_course_module' => $has_titles && $has_files
    ]);
}


function handleModuleDelete($conn) {
    if (empty($_POST['course_id'])) {
        echo json_encode(['success' => false, 'message' => "Missing course ID"]);
        return;
    }

    $course_id = (int)$_POST['course_id'];
    $module_id = (int)$_POST['module_id'];

    try {
        $conn->begin_transaction();

        $updates = [

            "UPDATE course_videos SET removed = 1 WHERE id = ?"
        ];

        foreach ($updates as $query) {
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $module_id);
            $stmt->execute();
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => "Course successfully removed.", 'course_id' => $course_id, 'module_id' => $module_id]);

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

        echo json_encode(['success' => true, 'message' => 'Quiz saved successfully']);
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

