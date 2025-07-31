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
                                    <?php if ($video_info): ?>
                                        <div class="mb-4">
                                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-start gap-4">
                                                <i data-lucide="video" class="w-8 h-8 text-blue-600 mt-1"></i>
                                                <div>
                                                    <p class="text-sm text-gray-700">Current video: <span class="font-semibold"><?php echo basename($video_info['video_url']); ?></span></p>
                                                    <p class="text-xs text-gray-500">Size: <?php echo round($video_info['file_size'], 2); ?> MB</p>
                                                    <div class="flex gap-2 mt-3">
                                                        <a href="get_video.php?course_id=<?php echo $course_id; ?>" class="px-3 py-1.5 text-xs font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-1">
                                                            <i data-lucide="play" class="w-3 h-3"></i> Play
                                                        </a>
                                                        <button type="submit" name="remove_video" class="px-3 py-1.5 text-xs font-medium bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition flex items-center gap-1">
                                                            <i data-lucide="trash-2" class="w-3 h-3"></i> Remove
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="space-y-4">
                                        <!-- Toggle between Upload and Paste Link
                                        <div class="flex space-x-2">
                                            <button type="button" id="uploadTab" class="px-4 py-2 bg-blue-500 text-white rounded focus:outline-none">Upload Video</button>
                                            <button type="button" id="linkTab" class="px-4 py-2 bg-gray-200 text-gray-700 rounded focus:outline-none">Paste Video Link</button>
                                        </div> -->

                                        <!-- Upload Section -->
                                        <div id="uploadSection" class="border-2 border-dashed border-gray-300 rounded-lg p-6 flex flex-col items-center justify-center bg-gray-50 hover:bg-gray-100 transition cursor-pointer relative">
                                            <input type="file" 
                                                id="course_video" 
                                                name="course_video" 
                                                accept="video/*, application/pdf" 
                                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                            <i data-lucide="upload-cloud" class="w-10 h-10 text-blue-500 mb-4"></i>
                                            <p class="text-sm font-medium text-gray-700">
                                                Drag and drop a video or pdf file here, or click to select
                                            </p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                Max file size: 250MB
                                            </p>
                                            <div id="file-name" class="text-sm text-blue-600 mt-2 hidden"></div>
                                        </div>

                                        <!-- Link Section -->
                                        <!-- <div id="linkSection" class="hidden">
                                            <label for="video_link" class="block text-sm font-medium text-gray-700 mb-1">Paste Video Link</label>
                                            <input type="url" 
                                                id="video_link" 
                                                name="video_link" 
                                                placeholder="https://example.com/video.mp4" 
                                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300">
                                        </div> -->
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

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
        
        // File upload preview
        document.getElementById('course_video').addEventListener('change', function(e) {
            var fileName = e.target.files[0] ? e.target.files[0].name : '';
            var fileNameDisplay = document.getElementById('file-name');
            
            if (fileName) {
                fileNameDisplay.textContent = 'Selected: ' + fileName;
                fileNameDisplay.classList.remove('hidden');
            } else {
                fileNameDisplay.classList.add('hidden');
            }
        });
        
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

    <!--javascript to switch between hyperlink or video -->

    <!-- <script>
        const uploadTab = document.getElementById('uploadTab');
        const linkTab = document.getElementById('linkTab');
        const uploadSection = document.getElementById('uploadSection');
        const linkSection = document.getElementById('linkSection');

        uploadTab.addEventListener('click', () => {
            uploadSection.classList.remove('hidden');
            linkSection.classList.add('hidden');
            uploadTab.classList.add('bg-blue-500', 'text-white');
            uploadTab.classList.remove('bg-gray-200', 'text-gray-700');
            linkTab.classList.remove('bg-blue-500', 'text-white');
            linkTab.classList.add('bg-gray-200', 'text-gray-700');
        });

        linkTab.addEventListener('click', () => {
            linkSection.classList.remove('hidden');
            uploadSection.classList.add('hidden');
            linkTab.classList.add('bg-blue-500', 'text-white');
            linkTab.classList.remove('bg-gray-200', 'text-gray-700');
            uploadTab.classList.remove('bg-blue-500', 'text-white');
            uploadTab.classList.add('bg-gray-200', 'text-gray-700');
        });
    </script> -->

</body>
</html>
