// manage_course.php

    <!-- Add Course Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center backdrop-blur-sm transition-all duration-300" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto transform transition-all duration-300">
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 p-5 rounded-t-xl flex items-center justify-between">
                <h5 class="text-lg font-semibold text-white flex items-center">
                    <i data-lucide="plus-circle" class="h-5 w-5 mr-2"></i>
                    Add New Course
                </h5>
                <button type="button" class="text-white/80 hover:text-white focus:outline-none" onclick="hideModal('addCourseModal')">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>
            <div class="p-6">
                <form id="addCourseForm" action="server_controller/manage_course_controller.php" method="post" data-show-toast="true" enctype="multipart/form-data" data-toast-message="Course added successfully" data-toast-type="success" data-validate="true">
                    <input type="hidden" name="course_add" value="1">
                    <input type="hidden" name="course_action" value="add">
                    
                    <!-- Course Container-->
                    <div class="mb-5">
                        <div class="mb-5">
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Course Title</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                    <i data-lucide="book-open" class="w-5 h-5"></i>
                                </div>
                                <input type="text" class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition" 
                                       id="title" name="title" required placeholder="Enter course title">
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Course Description</label>
                            <div class="relative">
                                <div class="absolute top-3 left-3 text-gray-400">
                                    <i data-lucide="align-left" class="w-5 h-5"></i>
                                </div>
                                <textarea class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition" 
                                          id="description" name="description" rows="4" required placeholder="Enter course description"></textarea>
                            </div>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="p-5 rounded-t-xl flex items-center justify-between">
                        <h5 class="text-lg font-semibold text-black flex items-center">
                            Course Module
                        </h5>
                        <button id="addModuleBtn" type="button" class="text-black/80 hover:text-black focus:outline-none" onclick="addNewModule()">
                            <i data-lucide="square-plus" class="h-5 w-5"></i>
                        </button>
                    </div>

                    <!-- Module Container -->
                    <div id="moduleContainer">
                        <!-- Initial Module M1 -->
                        <div class="course-module bg-white p-5 rounded-lg shadow animate-fade-in transition-all" data-index="1">
                            <div class="flex items-center justify-between mb-2">
                            <div class="question-label font-bold text-lg text-gray-700">M1.</div>
                            <button type="button" class="collapse-toggle text-gray-600 hover:text-black" onclick="toggleCollapse(this)">
                                <i data-lucide="chevron-up" class="w-5 h-5"></i>
                            </button>
                            </div>
                            <div class="module-body">
                            <div class="mb-5">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Course Module</label>
                                <div class="relative">
                                <input type="text" name="module_title[]" class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition" placeholder="Course Module title">
                                </div>
                            </div>
                            <div class="upload-section border-2 border-dashed border-gray-300 rounded-lg p-2 flex flex-col items-center justify-center bg-gray-50 hover:bg-gray-100 transition cursor-pointer relative">
                                <input type="file" name="module_file[]" accept="video/*, application/pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                <i data-lucide="upload-cloud" class="w-10 h-10 text-blue-500 mb-4"></i>
                                <p class="text-sm font-medium text-gray-700">
                                Drag and drop a file here, or click to select
                                </p>
                                <p class="text-xs text-gray-500 mt-1">Max file size: 250MB</p>
                                <div class="file-name text-sm text-blue-600 mt-2 hidden"></div>
                            </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-8">
                        <button type="button" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition text-sm font-medium flex items-center gap-2" onclick="hideModal('addCourseModal')">
                            <i data-lucide="x" class="h-4 w-4"></i>
                            Cancel
                        </button>
                        <button type="submit" name="add_course" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition text-sm font-medium flex items-center gap-2">
                            <i data-lucide="save" class="h-4 w-4"></i>
                            Save Course
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>






</script>
    <!-- Module Container -->
    <div id="moduleContainer">
    <!-- Initial Module M1 -->
    <div class="course-module bg-white p-5 rounded-lg shadow animate-fade-in transition-all" data-index="1">
        <div class="flex items-center justify-between mb-2">
        <div class="question-label font-bold text-lg text-gray-700">M1.</div>
        <button type="button" class="collapse-toggle text-gray-600 hover:text-black" onclick="toggleCollapse(this)">
            <i data-lucide="chevron-up" class="w-5 h-5"></i>
        </button>
        </div>
        <div class="module-body">
        <div class="mb-5">
            <label class="block text-sm font-medium text-gray-700 mb-2">Course Module</label>
            <div class="relative">
            <input type="text" name="module_title[]" class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition" placeholder="Course Module title">
            </div>
        </div>
        <div class="upload-section border-2 border-dashed border-gray-300 rounded-lg p-2 flex flex-col items-center justify-center bg-gray-50 hover:bg-gray-100 transition cursor-pointer relative">
            <input type="file" name="module_file[]" accept="video/*, application/pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
            <i data-lucide="upload-cloud" class="w-10 h-10 text-blue-500 mb-4"></i>
            <p class="text-sm font-medium text-gray-700">
            Drag and drop a file here, or click to select
            </p>
            <p class="text-xs text-gray-500 mt-1">Max file size: 250MB</p>
            <div class="file-name text-sm text-blue-600 mt-2 hidden"></div>
        </div>
        </div>
    </div>
    </div>

    <!-- Add Module Button -->
    <div class="mt-4">
    <button type="button" onclick="addNewModule()" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
        Add New Module
    </button>
    </div>

    <!-- Scripts -->
    <script>
    let moduleIndex = 1;

    function addNewModule() {
    const container = document.getElementById('moduleContainer');
    const topModule = container.querySelector('.course-module');
    const titleInput = topModule.querySelector('input[name="module_title[]"]');
    const fileInput = topModule.querySelector('input[name="module_file[]"]');

    // Remove previous validation messages
    topModule.querySelectorAll('.error-message').forEach(e => e.remove());

    let isValid = true;

    // Validate title input
    if (titleInput.value.trim() === '') {
        showError(titleInput, 'Please enter a module title.');
        isValid = false;
    }

    // Validate file input
    if (fileInput.files.length === 0) {
        showError(fileInput, 'Please upload a file.');
        isValid = false;
    }

    if (!isValid) return;

    // Collapse previous module
    topModule.classList.add('collapsed');
    moduleIndex++;

    const newModule = document.createElement('div');
    newModule.className = "course-module bg-white p-5 rounded-lg shadow animate-fade-in transition-all";
    newModule.setAttribute('data-index', moduleIndex);

    newModule.innerHTML = `
        <div class="flex items-center justify-between mb-2">
        <div class="question-label font-bold text-lg text-gray-700">M${moduleIndex}.</div>
        <button type="button" class="collapse-toggle text-gray-600 hover:text-black" onclick="toggleCollapse(this)">
            <i data-lucide="chevron-up" class="w-5 h-5"></i>
        </button>
        </div>
        <div class="module-body">
        <div class="mb-5">
            <label class="block text-sm font-medium text-gray-700 mb-2">Course Module</label>
            <div class="relative">
            <input type="text" name="module_title[]" class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition" placeholder="Course Module title">
            </div>
        </div>
        <div class="upload-section border-2 border-dashed border-gray-300 rounded-lg p-2 flex flex-col items-center justify-center bg-gray-50 hover:bg-gray-100 transition cursor-pointer relative">
            <input type="file" name="module_file[]" accept="video/*, application/pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
            <i data-lucide="upload-cloud" class="w-10 h-10 text-blue-500 mb-4"></i>
            <p class="text-sm font-medium text-gray-700">
            Drag and drop a file here, or click to select
            </p>
            <p class="text-xs text-gray-500 mt-1">Max file size: 250MB</p>
            <div class="file-name text-sm text-blue-600 mt-2 hidden"></div>
        </div>
        </div>
    `;

    container.insertBefore(newModule, container.firstChild);
    if (window.lucide) lucide.createIcons();
    }

    function showError(inputElement, message) {
    const error = document.createElement('p');
    error.className = 'error-message text-sm text-red-600 mt-1';
    error.textContent = message;
    inputElement.parentElement.appendChild(error);
    }

    function toggleCollapse(button) {
    const module = button.closest('.course-module');
    module.classList.toggle('collapsed');
    }

    // Handle file input changes (even in dynamically added modules)
    document.addEventListener('change', function (e) {
    if (e.target.matches('input[type="file"][name="module_file[]"]')) {
        const fileInput = e.target;
        const file = fileInput.files[0];
        const uploadSection = fileInput.closest('.upload-section');
        const fileNameDisplay = uploadSection.querySelector('.file-name');

        if (file) {
        fileNameDisplay.textContent = 'Selected: ' + file.name;
        fileNameDisplay.classList.remove('hidden');
        } else {
        fileNameDisplay.classList.add('hidden');
        }
    }
    });
</script>

// end manage_course.php

