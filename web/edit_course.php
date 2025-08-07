<?php 
require_once __DIR__ . '/server_controller/course_update.php';
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
                        
                        <form method="POST" enctype="multipart/form-data">
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
                                                                       onclick="previewFile('<?php echo "/uploads/modules/" . urlencode($video['module_name']); ?>')" 
                                                                        class="px-3 py-1.5 text-xs font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-1">
                                                                        <i data-lucide="eye" class="w-3 h-3"></i> View
                                                                    </button>
                                                                    <button type="submit" name="remove_video" value="<?php echo $video['id']; ?>" class="px-3 py-1.5 text-xs font-medium bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition flex items-center gap-1">
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
                                        <label class="block text-lg font-semibold mb-2">Course Modules</label>
                                        <div id="dropZone"
                                            class="relative border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:bg-gray-50 transition cursor-pointer"
                                            ondragover="event.preventDefault()"
                                            ondrop="handleDrop(event)">
                                            
                                            <!-- Flex container to center contents -->
                                            <div class="flex flex-col items-center justify-center" onclick="document.getElementById('filePicker').click()">
                                                <i data-lucide="upload-cloud" class="w-10 h-10 text-blue-500 mb-4"></i>
                                                <strong>Drop files here</strong> or <span class="text-black-500">click to upload</span>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    Max file size: 250MB
                                                </p>
                                            </div>

                                            <input type="file" id="filePicker" multiple accept="video/*,application/pdf"
                                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="handleFiles(this.files)">
                                        </div>
                                    </div>
                                                    <!-- MODULE LIST PREVIEW -->
                                    <div id="moduleList" class="space-y-3 mt-4"></div>
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
                            <a href="manage_quiz.php?course_id=<?php echo $course_id; ?>" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-all group">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition-all">
                                    <i data-lucide="help-circle" class="w-5 h-5 text-blue-600"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900">Manage Quizzes</h3>
                                    <p class="text-xs text-gray-500">Add or edit quizzes for this course</p>
                                </div>
                            </a>

                            <a href="manage_quiz.php?course_id=<?php echo $course_id; ?>" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-all group">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition-all">
                                    <i data-lucide="users" class="w-5 h-5 text-blue-600"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900">Manage examinees</h3>
                                    <p class="text-xs text-gray-500">Add or edit examinees for this course</p>
                                </div>
                            </a>
                            
                            <a href="print_course.php?id=<?php echo $course_id; ?>" target="_blank" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-all group">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition-all">
                                    <i data-lucide="printer" class="w-5 h-5 text-blue-600"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900">Print Course</h3>
                                    <p class="text-xs text-gray-500">Print course details and quizzes</p>
                                </div>
                            </a>
                            
                            <a href="view_course.php?id=<?php echo $course_id; ?>" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-all group">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition-all">
                                    <i data-lucide="eye" class="w-5 h-5 text-blue-600"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900">View Course</h3>
                                    <p class="text-xs text-gray-500">Preview the course as a student</p>
                                </div>
                            </a>
                        </div>
                    </div>
                    
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
    let uploadedFiles = [];
document.addEventListener('DOMContentLoaded', function () {
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

    window.handleFiles = function (files) {
        [...files].forEach((file, i) => {
            uploadedFiles.push(file); 
            addModuleRow(file); 
        });

        document.getElementById('filePicker').value = ''; 
    };

    window.handleDrop = function(e) {
        e.preventDefault();
        const files = e.dataTransfer.files;
    }

        function addModuleRow(file, index) {
        const moduleList = document.getElementById('moduleList');

        const row = document.createElement('div');
        row.className = 'group flex flex-wrap items-start gap-3 p-4 border border-gray-300 rounded relative';

        row.innerHTML = `
            <div class="w-full sm:w-1/4 text-sm text-gray-700 truncate">📄 ${file.name}</div>

            <input type="text" name="module_titles[]" required placeholder="Module Title"
                class="w-full sm:w-1/4 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-blue-300">

            <input type="text" name="module_descriptions[]" placeholder="Module Description (optional)"
                class="w-full sm:w-1/3 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-blue-300">

            <input type="hidden" name="module_file_names[]" value="${file.name}">

            <button type="button"
                class="absolute top-2 right-2 text-red-500 hover:text-red-700 text-sm font-bold opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                title="Remove">❌</button>
        `;

        // Remove row on ❌ click
        const removeBtn = row.querySelector('button');
        removeBtn.addEventListener('click', () => {
            row.remove();
        });

        moduleList.appendChild(row);
    }
        

});

function previewFile(fileUrl) {
    const modal = document.getElementById('filePreviewModal');
    const content = document.getElementById('fileContent');
    content.innerHTML = ''; // Clear previous content

    const fileExtension = fileUrl.split('.').pop().toLowerCase();
    if (fileExtension === 'mp4' || fileExtension === 'webm' || fileExtension === 'ogg') {
        const video = document.createElement('video');
        video.src = fileUrl;
        video.controls = true;
        video.className = 'w-full h-full object-contain';
        content.appendChild(video);
    } else if (fileExtension === 'pdf') {
        const iframe = document.createElement('iframe');
        iframe.src = fileUrl;
        iframe.className = 'w-full h-full';
        content.appendChild(iframe);
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
