<?php

class ModuleHandler {
    private $conn;
    private $course_id;
    private $upload_dir;

    public function __construct($conn, $course_id) {
        $this->conn = $conn;
        $this->course_id = $course_id;
        $this->upload_dir = __DIR__ . '/../../uploads/modules';

        $this->ensureUploadDirExists();
    }

    public function saveModules() {
        $this->configureErrorHandling();

        try {
            if (empty($_FILES['module_files']['name']) || empty($_POST['module_titles'])) {
                throw new Exception("Missing module files or titles.");
            }

            $titles = $_POST['module_titles'];
            $des = $_POST['module_descriptions'];
            $files = $_FILES['module_files'];
            $file_count = count($files['name']);

            if (count($titles) !== $file_count) {
                throw new Exception("Mismatch between titles and files.");
            }

            $this->conn->begin_transaction();

            for ($i = 0; $i < $file_count; $i++) {
                $this->saveSingleModule(
                    trim($titles[$i]),
                    trim($des[$i]),
                    $files['name'][$i],
                    $files['tmp_name'][$i],
                    $files['size'][$i],
                    $files['error'][$i]
                );
            }

            $this->updateCourseModuleInfo($file_count);
            $this->conn->commit();

            return true;

        } catch (Exception $e) {
            $this->conn->rollback();
            $this->respondWithError("Module upload failed.", $e->getMessage());
        }
    }

    private function saveSingleModule($title, $des, $originalName, $tmpPath, $size, $error) {
        if ($error !== UPLOAD_ERR_OK) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE   => 'File exceeds `upload_max_filesize` in php.ini',
                UPLOAD_ERR_FORM_SIZE  => 'File exceeds form MAX_FILE_SIZE',
                UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE    => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION  => 'File upload stopped by extension',
            ];

            $errorMessage = $uploadErrors[$error] ?? 'Unknown error';
            throw new Exception("Error uploading '{$title}': {$errorMessage} (code {$error})");
        }

        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($ext, ['mp4', 'mov', 'avi', 'mkv', 'pdf', 'webm'])) {
            throw new Exception("Invalid file type for: {$originalName}");
        }

        $safeFileName = preg_replace("/[^a-zA-Z0-9.\-_]/", "_", basename($originalName));
        $uniqueName = uniqid('mod_', true) . '_' . $safeFileName;
        $destination = $this->upload_dir . '/' . $uniqueName;

        if (!move_uploaded_file($tmpPath, $destination)) {
            throw new Exception("Failed to move uploaded file: {$originalName}");
        }

        $relativePath = '/uploads/modules/' . $uniqueName;

        $stmt = $this->conn->prepare("INSERT INTO course_videos (course_id, module_name, module_description, video_url, file_size)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isssi", $this->course_id, $title, $des, $relativePath, $size);

        if (!$stmt->execute()) {
            throw new Exception("DB insert failed for: {$title}");
        }
    }

    private function updateCourseModuleInfo($module_count) {
        $stmt = $this->conn->prepare("UPDATE courses 
            SET has_video = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("ii", $module_count, $this->course_id);

        if (!$stmt->execute()) {
            throw new Exception("Failed to update course module info.");
        }
    }

    private function ensureUploadDirExists() {
        if (!is_dir($this->upload_dir)) {
            if (!mkdir($this->upload_dir, 0777, true) && !is_dir($this->upload_dir)) {
                throw new Exception("Unable to create upload directory: {$this->upload_dir}");
            }
        }
    }

    private function respondWithError($msg, $detail = '') {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $msg,
            'error' => $detail
        ]);
        exit;
    }

    private function configureErrorHandling() {
        ini_set('display_errors', 0);
        ini_set('log_errors', 1);
        ini_set('error_log', __DIR__ . '/../../php_error.log');
        error_reporting(E_ALL);
    }

    public function validateModulesOnly() {
        if (empty($_FILES['module_files']['name']) || empty($_POST['module_titles'])) {
            throw new Exception("Missing module files or titles.");
        }

        $titles = $_POST['module_titles'];
        $files = $_FILES['module_files'];
        $file_count = count($files['name']);

        if (count($titles) !== $file_count) {
            throw new Exception("Mismatch between titles and files.");
        }

        for ($i = 0; $i < $file_count; $i++) {
            $error = $files['error'][$i];
            $name = $files['name'][$i];

            if ($error !== UPLOAD_ERR_OK) {
                $uploadErrors = [
                    UPLOAD_ERR_INI_SIZE   => 'File exceeds upload_max_filesize in php.ini',
                    UPLOAD_ERR_FORM_SIZE  => 'File exceeds MAX_FILE_SIZE',
                    UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded',
                    UPLOAD_ERR_NO_FILE    => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION  => 'File upload stopped by extension',
                ];

                $errorMessage = $uploadErrors[$error] ?? 'Unknown error';
                throw new Exception("Error uploading '{$name}': {$errorMessage}");
            }

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, ['mp4', 'mov', 'avi', 'mkv', 'pdf', 'webm'])) {
                throw new Exception("Invalid file type for: {$name}");
            }
        }
    }

}
