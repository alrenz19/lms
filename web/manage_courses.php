<?php 
require_once __DIR__ . '/server_controller/manage_course_controller.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - Manage Courses</title>
    <!-- <link rel="stylesheet" href="./public/css/tailwind.min.css" /> -->
     <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Remove Bootstrap CSS and JavaScript as they're not used in manage_users.php -->
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
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="../assets/css/style.css">
<style>
    @keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
    }
    .animate-fade-in {
    animation: fade-in 0.3s ease-out;
    }
    .collapsed .module-body {
    display: none;
    }

    .collapse-toggle {
      cursor: pointer;
      transition: transform 0.3s ease;
    }

    .course-module.collapsed .collapse-toggle svg {
      transform: rotate(180deg);
    }

    .dragover {
        border-color: #3b82f6;
        background-color: #eff6ff;
    }
</style>
</head>
<body class="bg-blue-50">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="p-8 sm:ml-72">
        <div class="container mx-auto">
            <!-- Page header - match exactly with manage_users.php -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl p-6 mb-6 flex justify-between items-center shadow-sm">
                <div class="flex items-center">
                    <div class="bg-white/10 p-3 rounded-lg mr-4">
                        <i data-lucide="book-open" class="h-8 w-8 text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">Course Management</h1>
                        <p class="text-blue-100">Create, edit and manage course content</p>
                    </div>
                </div>
                <button class="px-4 py-2.5 bg-white text-blue-600 rounded-lg flex items-center gap-2 transition-all duration-200 font-medium shadow-sm" onclick="showModal('addCourseModal')">
                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    <span>Add New Course</span>
                </button>
            </div>

            <!-- Error Messages -->
            <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-6 rounded-lg p-4 flex items-center gap-2 bg-red-50 text-red-700 border-l-4 border-red-500" style="display: none;">
                <i data-lucide="alert-circle" class="h-5 w-5 text-red-500"></i>
                <span><?php echo htmlspecialchars($_SESSION['error']); ?></span>
                <button type="button" class="ml-auto text-gray-400 hover:text-gray-600" onclick="this.parentElement.remove()">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </div>
            <?php endif; ?>

            <!-- Success Messages -->
            <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-6 rounded-lg p-4 flex items-center gap-2 bg-green-50 text-green-700 border-l-4 border-green-500" style="display: none;">
                <i data-lucide="check-circle" class="h-5 w-5 text-green-500"></i>
                <span><?php echo htmlspecialchars($_SESSION['success']); ?></span>
                <button type="button" class="ml-auto text-gray-400 hover:text-gray-600" onclick="this.parentElement.remove()">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-gray-700 font-medium">Total Courses</h3>
                        <div class="bg-green-100 text-green-800 p-2 rounded-lg">
                            <i data-lucide="book-open" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo $courses_count; ?></div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-gray-700 font-medium">Total Enrollments</h3>
                        <div class="bg-blue-100 text-blue-800 p-2 rounded-lg">
                            <i data-lucide="users" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo $total_enrollments; ?></div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-gray-700 font-medium">Course Completions</h3>
                        <div class="bg-indigo-100 text-indigo-800 p-2 rounded-lg">
                            <i data-lucide="check-circle" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo $course_completions; ?></div>
                </div>
            </div>

            <!-- Course Management -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
                <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i data-lucide="layout-grid" class="w-5 h-5 text-blue-600 mr-2"></i>
                        Course List
                    </h2>
                    <div class="relative w-full sm:w-auto">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </span>
                        <input type="search" 
                            id="courseSearch" 
                            class="w-full sm:w-64 pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            placeholder="Search courses..." 
                            autocomplete="off">
                    </div>
                </div>

                <!-- Course List -->
                <div class="p-6">
                    <div id="courseContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php if ($courses_result->num_rows === 0): ?>
                        <div class="col-span-full flex flex-col items-center justify-center p-12 text-center">
                            <i data-lucide="book-x" class="h-16 w-16 text-gray-300 mb-4"></i>
                            <h3 class="text-xl font-medium text-gray-900 mb-2">No courses available</h3>
                            <p class="text-gray-500 mb-6 max-w-md">Start creating courses by clicking the "Add New Course" button above</p>
                            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center shadow-sm" onclick="showModal('addCourseModal')">
                                <i data-lucide="plus-circle" class="w-5 h-5 mr-2"></i>
                                Create Your First Course
                            </button>
                        </div>
                        <?php else: ?>
                            <?php while ($course = $courses_result->fetch_assoc()): ?>
                            <div class="course-card bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300"
                                data-id="<?php echo $course['id']; ?>"
                                data-title="<?php echo strtolower(htmlspecialchars($course['title'])); ?>">

                                <div class="h-48 bg-gradient-to-r from-blue-600 to-blue-800 p-6 flex items-end">
                                    <h3 class="text-xl font-semibold text-white"><?php echo htmlspecialchars($course['title']); ?></h3>
                                </div>
                                <div class="p-6">
                                    <p class="text-gray-600 text-sm mb-4 line-clamp-3"><?php echo htmlspecialchars($course['description']); ?></p>
                                    
                                    <div class="flex items-center text-sm text-gray-500 mb-4">
                                        <div class="flex items-center mr-4">
                                            <i data-lucide="users" class="w-4 h-4 mr-1 text-blue-500"></i>
                                            <span><?php echo $course['enrollment_count']; ?> enrolled</span>
                                        </div>
                                        <div class="flex items-center">
                                            <i data-lucide="clipboard-list" class="w-4 h-4 mr-1 text-amber-500"></i>
                                            <span><?php echo $course['question_count']; ?> questions</span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <?php 
                                            $course_date = new DateTime($course['created_at']);
                                            $course_date_str = $course_date->format('M j, Y');
                                            ?>
                                            <span class="text-xs text-gray-500 flex items-center">
                                                <i data-lucide="calendar" class="w-3 h-3 mr-1"></i>
                                                Created: <?php echo $course_date_str; ?>
                                            </span>
                                        </div>
                                        <div class="flex space-x-2">
                                            <a href="edit_course.php?id=<?php echo $course['id']; ?>" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                                <i data-lucide="edit-3" class="w-5 h-5"></i>
                                            </a>
                                            <button class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" onclick="confirmDeleteCourse(<?php echo $course['id']; ?>, '<?php echo htmlspecialchars(addslashes($course['title'])); ?>')">
                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- No search results message -->
                    <div id="noCoursesFound" class="hidden flex-col items-center justify-center p-12 text-center">
                        <i data-lucide="search-x" class="h-12 w-12 text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-700 mb-2">No courses found</h3>
                        <p class="text-gray-500 mb-4">Try adjusting your search term</p>
                        <button class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-sm font-medium" 
                                onclick="clearSearch()">
                            Clear Search
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

     <!-- ADD COURSE MODAL -->
    <div id="addCourseModal" class="fixed inset-0 hidden items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="bg-white rounded-2xl w-full max-w-3xl p-6 relative shadow-lg overflow-y-auto max-h-[90vh]">
            <h2 class="text-2xl font-bold mb-4">Add New Course</h2>
            <form id="addCourseForm" action="server_controller/manage_course_controller.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="course_add" value="1">
                <input type="hidden" name="course_action" value="add">
                <!-- Course Title -->
                <div class="mb-4">
                    <label for="courseTitle" class="block text-sm font-medium text-gray-700">Course Title</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i data-lucide="book-open" class="w-5 h-5"></i>
                        </div>
                        <input type="text" id="courseTitle" name="course_title" required
                        class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>
                </div>

                <!-- Course Description -->
                <div class="mb-4">
                    <label for="courseDescription" class="block text-sm font-medium text-gray-700">Course Description</label>
                    <div class="relative">
                        <div class="absolute top-3 left-3 text-gray-400">
                        <i data-lucide="book-open" class="w-5 h-5"></i>
                        </div>
                        <textarea id="courseDescription" name="course_description" rows="3" placeholder="Enter course description"
                            class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:ring-indigo-500 focus:border-indigo-500 transition"></textarea>
                    </div>
                </div>

                <!-- Module File Upload -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Course Modules</label>

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

                <!-- Hidden Fields -->
                <input type="hidden" id="hiddenCourseId" name="course_id" value="">

                <!-- Form Buttons -->
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" onclick="hideModal('addCourseModal')" 
                        class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-100">Cancel</button>
                    <button type="submit" 
                        class="px-4 py-2 rounded-lg bg-blue-500 text-white hover:bg-blue-600">Save Course</button>
                </div>
            </form>

            <!-- Close Button -->
            <button type="button" onclick="hideModal('addCourseModal')" 
                class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 text-xl font-bold">&times;</button>
        </div>
    </div>


    <!-- Delete Course Confirmation Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center backdrop-blur-sm transition-all duration-300" id="deleteCourseModal" tabindex="-1" aria-labelledby="deleteCourseModalLabel" aria-hidden="true">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md transform transition-all duration-300">
            <div class="bg-gradient-to-r from-red-600 to-red-800 p-5 rounded-t-xl flex items-center justify-between">
                <h5 class="text-lg font-semibold text-white flex items-center">
                    <i data-lucide="alert-triangle" class="h-5 w-5 mr-2"></i>
                    Confirm Deletion
                </h5>
                <button type="button" class="text-white/80 hover:text-white focus:outline-none" onclick="hideModal('deleteCourseModal')">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>
            <div class="p-6">
                <div class="bg-red-50 text-red-800 p-4 rounded-lg mb-4 flex items-start">
                    <i data-lucide="alert-circle" class="w-5 h-5 mr-2 flex-shrink-0 mt-0.5"></i>
                    <div>
                        <p class="font-medium">Warning</p>
                        <p class="text-sm">This action cannot be undone. All course content, quizzes, and student progress data will be permanently deleted.</p>
                    </div>
                </div>
                <p class="mb-2 text-gray-700">Are you sure you want to delete this course?</p>
                <p class="font-semibold mb-0" id="courseNameToDelete"></p>
                
                <form id="deleteCourseForm" action="server_controller/manage_course_controller.php" method="post" class="mt-6" data-show-toast="true" data-toast-message="Course deleted successfully" data-toast-type="warning" data-validate="true">
                    <input type="hidden" name="course_delete" value="delete">
                    <input type="hidden" name="course_id" id="courseIdToDelete">
                    
                    <div class="flex justify-end space-x-3 mt-8">
                        <button type="button" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition text-sm font-medium flex items-center gap-2" onclick="hideModal('deleteCourseModal')">
                            <i data-lucide="x" class="h-4 w-4"></i>
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg transition text-sm font-medium flex items-center gap-2">
                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                            Delete Course
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


<?php require_once  __DIR__ . '/view_controller/quiz_modal.php';?>

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
let currentCourseId = null;
let hasModule = false;

document.addEventListener('DOMContentLoaded', function () {

    // === FORM SUBMISSION HANDLER ===
    function handleFormSubmission(formId, onSuccessCallback) {
        const form = document.getElementById(formId);
        if (!form) return;

        form.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(form);

            uploadedFiles.forEach((file) => {
                formData.append("module_files[]", file);
            });

            try {
                const response = await fetch(form.action, {
                    method: form.method,
                    body: formData,
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                });

                const text = await response.text(); // get raw response first
                let result;

                try {
                    result = JSON.parse(text); // try to parse as JSON
                } catch (parseError) {
                    console.error("❌ Response is not JSON:", text);
                    showToast("Server error: response was not JSON. Check console.", "error");
                    return;
                }

                if (!response.ok || !result.success) {
                    throw new Error(result.message || "Submission failed");
                }

                // ✅ normal flow
                if (formId === "addCourseForm") {
                    currentCourseId = result.course_id;
                    hasModule = result.has_course_module;
                    document.getElementById("hiddenCourseId").value = currentCourseId;
                    if (hasModule) {
                        showModal("addQuestionModal");
                    } else {
                        window.location.href = "edit_course.php?id=" + currentCourseId;
                    }
                }

                uploadedFiles = [];
                showToast(result.message || "Successfully submitted", "success");
                onSuccessCallback?.(form, result);

            } catch (err) {
                const message = err.detail?.error || err.message;
                console.error("Form submission failed:", message);
                showToast(message, "error");
            }
        });
    }

    // === MODULE ROW ===
    function addModuleRow(file) {
        const moduleList = document.getElementById('moduleList');
        if (!moduleList) return;

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

        const removeBtn = row.querySelector('button');
        removeBtn.addEventListener('click', () => {
            const index = uploadedFiles.findIndex(f => f.name === file.name && f.size === file.size);
            if (index > -1) uploadedFiles.splice(index, 1);
            row.remove();
        });

        moduleList.appendChild(row);
    }

    // === HANDLE FILES ===
    window.handleFiles = function(files) {
        [...files].forEach(file => {
            if (!uploadedFiles.some(f => f.name === file.name && f.size === file.size)) {
                uploadedFiles.push(file);
                addModuleRow(file);
            }
        });
        document.getElementById('filePicker').value = '';
    };

    // === HANDLE DROP ===
    window.handleDrop = function(e) {
        e.preventDefault();
        handleFiles(e.dataTransfer.files);
    }

    // === REFRESH COURSE LIST ===
    async function refreshCourseList() {
        try {
            const res = await fetch('server_controller/fetch_courses_controller.php', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const result = await res.json();
            if (!result.success) throw new Error(result.message || 'Failed to load course list.');

            const container = document.getElementById('courseContainer');
            if (container) {
                container.innerHTML = result.html;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        } catch (err) {
            console.error('Course refresh failed:', err);
        }
    }

    // === ADD COURSE FORM ===
    handleFormSubmission('addCourseForm', (form) => {
        form.reset();
        hideModal('addCourseModal');
    });

    // === DELETE COURSE FORM ===
    handleFormSubmission('deleteCourseForm', (form, result) => {
        hideModal('deleteCourseModal');
        const deletedCard = document.querySelector(`.course-card[data-id="${result.course_id}"]`);
        if (deletedCard) deletedCard.remove();
        if (typeof clearSearch === 'function') {
            document.getElementById('courseSearch')?.dispatchEvent(new Event('input'));
        }
    });

    // === ICON INITIALIZATION ===
    if (typeof lucide !== 'undefined') lucide.createIcons();

    // === SESSION TOAST MESSAGES ===
    <?php if (isset($_SESSION['success'])): ?>
        showToast('<?php echo addslashes($_SESSION['success']); ?>', 'success');
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        showToast('<?php echo addslashes($_SESSION['error']); ?>', 'error');
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    // === SEARCH FUNCTIONALITY ===
    const searchInput = document.getElementById('courseSearch');
    const courseCards = document.querySelectorAll('.course-card');
    const courseContainer = document.getElementById('courseContainer');
    const noCoursesFound = document.getElementById('noCoursesFound');

    if (searchInput && courseCards.length) {
        searchInput.addEventListener('input', () => {
            const searchTerm = searchInput.value.toLowerCase();
            let hasResults = false;

            courseCards.forEach(card => {
                const title = card.getAttribute('data-title')?.toLowerCase() || '';
                const match = title.includes(searchTerm);
                card.classList.toggle('hidden', !match);
                if (match) hasResults = true;
            });

            noCoursesFound?.classList.toggle('hidden', hasResults || searchTerm.length === 0);
            noCoursesFound?.classList.toggle('flex', !hasResults && searchTerm.length > 0);

            const noCoursesAvailable = courseContainer?.querySelector('[data-lucide="book-x"]')?.closest('.col-span-full');
            if (noCoursesAvailable) {
                noCoursesAvailable.classList.toggle('hidden', searchTerm.length > 0);
            }
        });

        window.clearSearch = () => {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
            searchInput.focus();
        };
    }

});

// === MODAL HANDLING ===
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    if (modalId === 'addCourseModal') {
        const form = document.getElementById('addCourseForm');
        if (form) form.reset();
        uploadedFiles = [];
        const moduleList = document.getElementById('moduleList');
        if (moduleList) moduleList.innerHTML = '';
        const filePicker = document.getElementById('filePicker');
        if (filePicker) filePicker.value = '';
    }

    document.body.classList.add('overflow-hidden');
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    setTimeout(() => {
        const firstInput = modal.querySelector('input:not([type="hidden"])');
        if (firstInput) firstInput.focus();
    }, 50);
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    if (modalId === 'addCourseModal') {
        const form = document.getElementById('addCourseForm');
        if (form && typeof form.reset === 'function') form.reset();
        uploadedFiles = [];
        const moduleList = document.getElementById('moduleList');
        if (moduleList) moduleList.innerHTML = '';
        const filePicker = document.getElementById('filePicker');
        if (filePicker) filePicker.value = '';
    }

    document.body.classList.remove('overflow-hidden');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
}

// === DELETE CONFIRMATION ===
function confirmDeleteCourse(courseId, courseTitle) {
    const idInput = document.getElementById('courseIdToDelete');
    const nameDisplay = document.getElementById('courseNameToDelete');
    if (idInput) idInput.value = courseId;
    if (nameDisplay) nameDisplay.textContent = courseTitle;
    showModal('deleteCourseModal');
}

// === TOAST NOTIFICATIONS ===
function showToast(message, type = 'info') {
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'fixed bottom-4 right-4 z-50 flex flex-col gap-2';
        document.body.appendChild(toastContainer);
    }

    const toast = document.createElement('div');
    toast.className = 'transform transition-all duration-300 ease-in-out translate-x-full';

    const config = {
        success: ['bg-green-500', 'text-white', 'check-circle'],
        error: ['bg-red-500', 'text-white', 'alert-circle'],
        warning: ['bg-amber-500', 'text-white', 'alert-triangle'],
        info: ['bg-blue-500', 'text-white', 'info']
    };

    const [bgColor, textColor, icon] = config[type] || config.info;

    toast.className += ` ${bgColor} ${textColor} rounded-lg shadow-lg p-4 mb-2 flex items-center gap-2`;

    toast.innerHTML = `
        <i data-lucide="${icon}" class="w-5 h-5"></i>
        <span class="flex-1">${message}</span>
        <button class="ml-2 focus:outline-none hover:opacity-80" aria-label="Close">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    `;

    toastContainer.appendChild(toast);

    // Animate in
    requestAnimationFrame(() => {
        toast.classList.remove('translate-x-full');
        toast.classList.add('translate-x-0');
    });

    // Auto-remove after 5s
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => toast.remove(), 300);
    }, 5000);

    // Close button
    const closeBtn = toast.querySelector('button');
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            toast.classList.add('translate-x-full');
            setTimeout(() => toast.remove(), 300);
        });
    }

    // Re-init lucide icons
    if (typeof lucide !== 'undefined') lucide.createIcons();
}
</script>
</body>
</html>