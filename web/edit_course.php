<?php 
require_once __DIR__ . '/server_controller/course_update.php';

$course_id = $_GET['id'] ?? 0;

// Fetch course
$stmt = $conn->prepare("SELECT id, title, description, created_by, has_video, department, section, division, group_id FROM courses WHERE id = ? AND removed = 0");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    header("Location: manage_courses.php");
    exit;
}

// Fetch video info
$video_info = null;
$stmt = $conn->prepare("SELECT id, course_id, file_size, module_name, module_description, video_url FROM course_videos WHERE course_id = ? AND removed = 0 ORDER BY id DESC");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$video_result = $stmt->get_result();
if ($video_result->num_rows > 0) {
    while ($row = $video_result->fetch_assoc()) {
        $video_info[] = $row;
    }
}

// Check if current user is a collaborator
$is_collaborator = false;
$check_collab = $conn->prepare("SELECT 1 FROM course_collab WHERE course_id = ? AND admin_id = ?");
$check_collab->bind_param("ii", $course_id, $_SESSION['user_id']);
$check_collab->execute();
$is_collaborator = $check_collab->get_result()->num_rows > 0;
$check_collab->close();

// Check if current user is the course creator
$is_owner = ($course['created_by'] ?? 0) == $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course - LMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'blue': {
                            50: '#f0f5ff',
                            100: '#e0eaff',
                            200: '#c7d7fe',
                            300: '#a5b9fc',
                            400: '#8193f7',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                            950: '#1e1b4b',
                        },
                        'green': {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857'
                        },
                        'red': {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#b91c1c'
                        },
                        'amber': {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309'
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-blue-50">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="p-8 sm:ml-72">
        <div class="container mx-auto">
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl p-6 mb-8 shadow-md">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                            <i data-lucide="pencil" class="w-6 h-6"></i> Edit Course
                        </h1>
                        <p class="text-blue-100">Update course information and manage course content</p>
                    </div>
                    <a href="manage_courses.php" class="px-4 py-2.5 bg-white text-blue-600 rounded-lg flex items-center gap-2 transition-all duration-200 font-medium shadow-sm">
                        <i data-lucide="arrow-left" class="w-5 h-5"></i>
                        <span>Back to Courses</span>
                    </a>
                </div>
            </div>

            <!-- Alert messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg flex items-center gap-2" role="alert" style="display: none;">
                    <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
                    <span><?php echo $_SESSION['success']; ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg flex items-center gap-2" role="alert" style="display: none;">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-red-500"></i>
                    <span><?php echo $_SESSION['error']; ?></span>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Course Details Card -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                                <i data-lucide="book-open" class="w-5 h-5 text-blue-600"></i>
                                Course Details
                            </h2>
                        </div>
                        
                        <form id="updateCourseForm"  action="server_controller/course_update.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="course_id" id="courseIdToUpdate" value=<?php echo $course_id; ?>>
                            <div class="p-6 space-y-6">
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Course Title</label>
                                    <input type="text" 
                                           class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition" 
                                           id="title" 
                                           name="title" 
                                           value="<?php echo htmlspecialchars($course['title']); ?>" 
                                           required>
                                </div>
                                
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Course Description</label>
                                    <textarea 
                                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition" 
                                        id="description" 
                                        name="description" 
                                        rows="5" 
                                        required><?php echo htmlspecialchars($course['description']); ?></textarea>
                                </div>
                                
                                <div>
                                    <label for="course_video" class="block text-sm font-medium text-gray-700 mb-1">Course Module</label>
                                    <?php if (!empty($video_info)): ?>
                                        <?php foreach ($video_info as $index => $video): ?>
                                            <input type="hidden" name="module_id" id="moduleIdToDelete" value=<?php echo $video['id']; ?>>
                                            <div class="mb-4">
                                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                                    <div class="flex items-start gap-4">
                                                        <i data-lucide="book" class="w-8 h-8 text-blue-600 mt-1"></i>
                                                        <div class="w-full">
                                                            <div class="flex items-center justify-between">
                                                                <p class="text-sm text-gray-700">
                                                                    Module Title: <span class="font-semibold"><?php echo basename($video['module_name']); ?></span>
                                                                </p>
                                                                <button type="button"
                                                                    class="toggle-details text-blue-600 hover:text-blue-800 transition"
                                                                    data-target="details-<?php echo $index; ?>">
                                                                    <i data-lucide="chevron-down" class="w-5 h-5"></i>
                                                                </button>
                                                            </div>

                                                            <div id="details-<?php echo $index; ?>" class="hidden mt-2 text-xs text-gray-500">
                                                                <p>Module Description: <?php echo $video['module_description']; ?></p>
                                                                <div class="flex gap-2 mt-3">
                                                                    <button 
                                                                        type="button"
                                                                       onclick="previewFile('<?php echo $video['video_url']; ?>', '<?php echo $course_id; ?>', '<?php echo $video['id']; ?>')" 
                                                                        class="px-3 py-1.5 text-xs font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-1">
                                                                        <i data-lucide="eye" class="w-3 h-3"></i> View
                                                                    </button>
                                                                    <button type="submit" name="module_id" value="<?php echo $video['id']; ?>" class="px-3 py-1.5 text-xs font-medium bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition flex items-center gap-1">
                                                                        <i data-lucide="trash-2" class="w-3 h-3"></i> Remove
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Course Modules</label>
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Screen Recording</label>
                                            <button type="button" id="startRecordingBtn" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">Start Recording</button>
                                            <button type="button" id="stopRecordingBtn" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 ml-2" disabled>Stop Recording</button>
                                            <p id="recordingStatus" class="text-sm text-gray-500 mt-1">Not recording</p>
                                        </div>

                                        <!-- Drag & Drop Area -->
                                        <div id="dropArea" 
                                            class="w-full p-4 border-2 border-dashed border-gray-300 rounded text-center cursor-pointer hover:border-blue-400 transition-colors"
                                            ondragover="event.preventDefault()" 
                                            ondrop="handleDrop(event)">
                                            Drag & Drop files here or <span class="text-blue-500 underline">click to select</span>
                                            <input type="file" id="filePicker" multiple class="hidden" onchange="handleFiles(this.files)">
                                        </div>

                                        <!-- Module List -->
                                        <div id="moduleList" class="mt-3 flex flex-col gap-3"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                                <button type="submit" name="update_course" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center gap-2 text-sm font-medium">
                                    <i data-lucide="save" class="w-4 h-4"></i> Update Course
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Course Management Sidebar -->
                <div>
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden mb-6">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                                <i data-lucide="list-checks" class="w-5 h-5 text-blue-600"></i>
                                Course Management
                            </h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <a href="manage_all_users.php?course_id=<?php echo $course_id; ?>" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-all group">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition-all">
                                    <i data-lucide="help-circle" class="w-5 h-5 text-blue-600"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900">Manage Users/Section</h3>
                                    <p class="text-xs text-gray-500">Enroll users and assign a section for this course</p>
                                </div>
                            </a>

                            <a href="course_collab.php?course_id=<?php echo $course_id; ?>" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-all group">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition-all">
                                    <i data-lucide="help-circle" class="w-5 h-5 text-blue-600"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900">Course Collaborators</h3>
                                    <p class="text-xs text-gray-500">Assign admins to collaborate on this course</p>
                                </div>
                            </a>

                            <a href="manage_quiz.php?course_id=<?php echo $course_id; ?>" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-all group">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition-all">
                                    <i data-lucide="help-circle" class="w-5 h-5 text-blue-600"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900">Manage Quiz</h3>
                                    <p class="text-xs text-gray-500">Add or edit quiz for this course</p>
                                </div>
                            </a>
                            
                            <a href="print_course.php?id=<?php echo $course_id; ?>" target="_blank" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-all group">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition-all">
                                    <i data-lucide="printer" class="w-5 h-5 text-blue-600"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900">Print Progress</h3>
                                    <p class="text-xs text-gray-500">Print user progress details</p>
                                </div>
                            </a>
                            
                            <!-- <a href="view_course.php?id=<?php echo $course_id; ?>" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-all group">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition-all">
                                    <i data-lucide="eye" class="w-5 h-5 text-blue-600"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900">View Course</h3>
                                    <p class="text-xs text-gray-500">Preview the course as a student</p>
                                </div>
                            </a> -->
                        </div>
                    </div>
                    
                    <?php if ($is_owner || $_SESSION['role'] === 'super_admin'): ?>
                    <!-- Danger Zone -->
                    <div class="bg-red-50 rounded-xl border border-red-200 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-red-200">
                            <h2 class="text-lg font-semibold text-red-800 flex items-center gap-2">
                                <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>
                                Danger Zone
                            </h2>
                        </div>
                        <div class="p-6">
                            <a href="delete_course.php?id=<?php echo $course_id; ?>" onclick="return confirm('Are you sure you want to delete this course? This action cannot be undone.')" 
                               class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition text-sm font-medium">
                                <i data-lucide="trash-2" class="w-4 h-4"></i> Delete Course
                            </a>
                            <p class="text-xs text-red-500 mt-2">
                                This will permanently delete the course and all associated quizzes and student progress.
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- File Preview Modal -->
<div id="filePreviewModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 hidden">
    <div class="bg-white w-full max-w-4xl rounded-xl shadow-xl overflow-hidden relative">
        <button onclick="closePreview()" class="absolute top-2 right-2 text-gray-600 hover:text-red-600 text-xl font-bold z-50">×</button>
        <div id="fileContent" class="w-full h-[80vh] flex items-center justify-center bg-gray-100">
            <!-- Content (iframe or video) will be injected here -->
        </div>
    </div>
</div>

<script>
    // Initialize Lucide icons
    lucide.createIcons();

    // Initialize toast notifications
    <?php if (isset($_SESSION['success'])): ?>
        showToast('<?php echo addslashes($_SESSION['success']); ?>', 'success');
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        showToast('<?php echo addslashes($_SESSION['error']); ?>', 'error');
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    // Toast notification system
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
        toast.className = 'transform transition-all duration-300 ease-in-out translate-x-full';
        
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
                bgColor = 'bg-blue-500';
                textColor = 'text-white';
                iconName = 'info';
        }
        
        // Apply styles
        toast.className += ` ${bgColor} ${textColor} rounded-lg shadow-lg p-4 mb-2 flex items-center`;
        
        // Add content
        toast.innerHTML = `
            <i data-lucide="${iconName}" class="w-5 h-5 mr-2"></i>
            <span>${message}</span>
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
            toast.classList.remove('translate-x-full');
            toast.classList.add('translate-x-0');
        }, 10);
        
        // Remove after 3 seconds
        setTimeout(() => {
            toast.classList.remove('translate-x-0');
            toast.classList.add('translate-x-full');
            
            // Remove from DOM after animation completes
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }
</script>

<script>
    // Open file picker when clicking drag & drop area
    const dropArea = document.getElementById('dropArea');
    const filePicker = document.getElementById('filePicker');

    if(dropArea && filePicker){
        dropArea.addEventListener('click', () => filePicker.click());
    }
</script>

<script>
    let uploadedFiles = [];
    let screenStream;
    let micStream;
    let mediaStream;
    let mediaRecorder;
    let recordedChunks = [];
    let audioContext;
    let destination;
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('updateCourseForm');
    if (!form) return;
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
                const clickedBtn = e.submitter; // Which button triggered submit
            const btnName = clickedBtn.name;
            const btnValue = clickedBtn.value;

            const formData = new FormData(form);

            // Append the clicked button data (important for PHP)
            formData.append(btnName, btnValue);

            // If it's "Update Course", include uploaded files
            if (btnName === 'update_course') {
                uploadedFiles.forEach(file => {
                    formData.append('module_files[]', file);
                });
            }

            try {
                const response = await fetch(form.action, {
                    method: form.method,
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();
                if (!response.ok || !result.success)
                    throw new Error(result.message || 'Submission failed');

                uploadedFiles = [];
                showToast(result.message || 'Successfully submitted', 'success');
            } catch (err) {
                // Access the actual `error` field from the JSON response if available
                const message = err.detail?.error ||  err.message;
                showToast(message, 'error');
            } finally {
                location.reload();
            }
        });

    document.querySelectorAll('.toggle-details').forEach(button => {
        button.addEventListener('click', function () {
            const targetId = this.dataset.target;
            const target = document.getElementById(targetId);

            if (target.classList.contains('hidden')) {
                target.classList.remove('hidden');
                this.innerHTML = `<i data-lucide="chevron-up" class="w-5 h-5"></i>`;
            } else {
                target.classList.add('hidden');
                this.innerHTML = `<i data-lucide="chevron-down" class="w-5 h-5"></i>`;
            }

            // Re-render lucide icons if needed
            if (window.lucide) lucide.createIcons();
        });
    });

    window.handleFiles = function(files) {
        [...files].forEach(file => {
            if (!uploadedFiles.some(f => f.name === file.name && f.size === file.size)) {
                uploadedFiles.push(file);
                addModuleRow(file);
            }
        });
        document.getElementById('filePicker').value = '';
    };

    window.handleDrop = function(e) {
        e.preventDefault();
        handleFiles(e.dataTransfer.files);
    }

    function addModuleRow(file, index) {
            
        const moduleList = document.getElementById('moduleList');
        const fileURL = URL.createObjectURL(file);


        const row = document.createElement('div');
        row.className = 'group flex flex-wrap items-start gap-3 p-4 border border-gray-300 rounded relative';

        row.innerHTML = `
            <div class="w-full sm:w-1/4 text-sm text-gray-700 truncate">📄 ${file.name}</div>

            <input type="text" name="module_titles[]" required placeholder="Module Title"
                class="w-full sm:w-1/4 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-blue-300">

            <input type="text" name="module_descriptions[]" placeholder="Module Description (optional)"
                class="w-full sm:w-1/3 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-blue-300">

            <input type="hidden" name="module_file_names[]" value="${file.name}">

            <video class="w-full sm:w-1/4 mt-2 sm:mt-0 rounded border" controls>
                <source src="${fileURL}" type="${file.type}">
                Your browser does not support the video tag.
            </video>

            <button type="button"
                class="absolute top-2 right-2 text-red-500 hover:text-red-700 text-sm font-bold opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                title="Remove">❌</button>
        `;

        // Remove row on ❌ click
        const removeBtn = row.querySelector('button');
        removeBtn.addEventListener('click', () => {
            row.remove();
        });

        uploadedFiles.forEach(file => {
                moduleList.append('module_files[]', file);
        });

        moduleList.appendChild(row);
    }

    // === SCREEN RECORDING ===
    const startBtn = document.getElementById('startRecordingBtn');
    const stopBtn = document.getElementById('stopRecordingBtn');
    const statusText = document.getElementById('recordingStatus');

    if (startBtn && stopBtn) {
        startBtn.addEventListener('click', async () => {
            try {
                // Capture screen (video + optional system audio)
                screenStream = await navigator.mediaDevices.getDisplayMedia({
                    video: true,
                    audio: true
                });

                // Capture microphone audio
                micStream = await navigator.mediaDevices.getUserMedia({ audio: true });

                // Mix audio
                audioContext = new AudioContext();
                destination = audioContext.createMediaStreamDestination();

                if (screenStream.getAudioTracks().length > 0) {
                    const screenSource = audioContext.createMediaStreamSource(new MediaStream(screenStream.getAudioTracks()));
                    screenSource.connect(destination);
                }

                const micSource = audioContext.createMediaStreamSource(new MediaStream(micStream.getAudioTracks()));
                micSource.connect(destination);

                // Combine video + mixed audio
                mediaStream = new MediaStream([
                    ...screenStream.getVideoTracks(),
                    ...destination.stream.getAudioTracks()
                ]);

                // Setup MediaRecorder
                mediaRecorder = new MediaRecorder(mediaStream);
                recordedChunks = [];

                mediaRecorder.ondataavailable = e => {
                    if (e.data.size > 0) recordedChunks.push(e.data);
                };

                mediaRecorder.onstop = () => {
                    // Save recording
                    const blob = new Blob(recordedChunks, { type: 'video/mp4' });
                    const file = new File([blob], `screen_recording_${Date.now()}.mp4`, { type: 'video/mp4' });

                    uploadedFiles.push(file);
                    addModuleRow(file);

                    statusText.textContent = 'Recording added to module list';
                    showToast('Screen + mic recording added to module list', 'success');

                    // Stop all tracks
                    screenStream.getTracks().forEach(track => track.stop());
                    micStream.getTracks().forEach(track => track.stop());

                    // Disconnect and close AudioContext
                    destination.disconnect();
                    audioContext.close();
                };

                mediaRecorder.start();
                statusText.textContent = 'Recording...';
                startBtn.disabled = true;
                stopBtn.disabled = false;

            } catch (err) {
                console.error(err);
                showToast('Cannot start screen recording with mic', 'error');
            }
        });

        stopBtn.addEventListener('click', () => {
            mediaRecorder?.stop();
            startBtn.disabled = false;
            stopBtn.disabled = true;
            statusText.textContent = 'Processing recording...';
        });
    }
});

function previewFile(fileUrl, courseId = null, fileId = null) {
    const modal = document.getElementById('filePreviewModal');
    const content = document.getElementById('fileContent');
    content.innerHTML = ''; // Clear previous content

    const fileExtension = fileUrl.split('.').pop().toLowerCase();

    if (fileExtension === 'mp4' || fileExtension === 'webm' || fileExtension === 'ogg') {
        // --- VIDEO PREVIEW ---
        const video = document.createElement('video');
        video.id = 'courseVideo';
        video.className = 'absolute top-0 left-0 w-full h-full object-contain';
        video.controls = true;

        const source = document.createElement('source');
        if (courseId && fileId) {
            source.src = `get_video.php?course_id=${courseId}&id=${fileId}`;
            source.type = 'video/mp4';
        } else {
            source.src = fileUrl;
            source.type = `video/${fileExtension}`;
        }

        video.appendChild(source);
        video.append('Your browser does not support the video tag.');
        content.appendChild(video);

    } else if (fileExtension === 'pdf') {
        // --- PDF PREVIEW ---
        const embed = document.createElement('embed');
        if (courseId && fileId) {
            embed.src = `get_pdf.php?course_id=${courseId}&id=${fileId}`;
        } else {
            embed.src = fileUrl;
        }
        embed.type = 'application/pdf';
        embed.width = '100%';
        embed.height = '500px';
        content.appendChild(embed);

    } else {
        content.innerHTML = '<p class="text-gray-500 text-center">Unsupported file format</p>';
    }

    modal.classList.remove('hidden');
}


function closePreview() {
    const modal = document.getElementById('filePreviewModal');
    modal.classList.add('hidden');
}

</script>


</body>
</html>
