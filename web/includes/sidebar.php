<?php
$current_page = basename($_SERVER['PHP_SELF']);
$is_in_includes = strpos($_SERVER['PHP_SELF'], '/includes/') !== false;
$base_path = $is_in_includes ? '..' : '.';
?>
<!-- Add Lucide icons -->
<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<!-- Sidebar -->
<aside id="sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform bg-white text-gray-600 shadow-sm border border-gray-100">
    <div class="h-full px-3 py-4 overflow-y-auto">
        <div class="flex items-center mb-2 px-2">
            <i data-lucide="book-open" class="h-7 w-7 text-gray-600 mr-3"></i>
            <span class="self-center text-xl font-semibold whitespace-nowrap">LMS System</span>
        </div>
        
        <div class="m-5">
            <img src="asahi.png" alt="Asahi Intecc" class="h-[110px] mx-auto">
        </div>

        <div class="mt-10 mb-6 px-2">
            <div class="text-xs uppercase tracking-wider text-gray-400 mb-1">
                <?php echo $_SESSION['role'] === 'admin' ? 'ADMIN' : 'USER'; ?>
            </div>
            <div class="font-medium">
                <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'System Administrator'); ?>
            </div>
        </div>
        
        <ul class="space-y-2 font-medium">
            <li>
                <a href="dashboard.php" class="flex items-center p-2 rounded-lg hover:bg-gradient-to-r from-blue-600 to-blue-800 group <?php echo $current_page === 'dashboard.php' ? 'bg-gradient-to-r from-blue-600 to-blue-800' : ''; ?>">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 text-gray-600 transition duration-75 group-hover:text-white <?php echo $current_page === 'dashboard.php' ? 'text-white' : ''; ?>"></i>
                    <span class="ml-3 text-gray-600 transition duration-75 group-hover:text-white <?php echo $current_page === 'dashboard.php' ? 'text-white' : ''; ?>">Dashboard</span>
                </a>
            </li>
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <li>
                <a href="manage_courses.php" class="flex items-center p-2 rounded-lg hover:bg-gradient-to-r from-blue-600 to-blue-800 group <?php echo $current_page === 'manage_courses.php' ? 'bg-gradient-to-r from-blue-600 to-blue-800' : ''; ?>">
                    <i data-lucide="book-open" class="w-5 h-5 text-gray-600 transition duration-75 group-hover:text-white <?php echo $current_page === 'manage_courses.php' ? 'text-white' : ''; ?>"></i>
                    <span class="ml-3 text-gray-600 transition duration-75 group-hover:text-white <?php echo $current_page === 'manage_courses.php' ? 'text-white' : ''; ?>">Manage Courses</span>
                </a>
            </li>
            <li>
                <a href="manage_users.php" class="flex items-center p-2 rounded-lg hover:bg-gradient-to-r from-blue-600 to-blue-800 group <?php echo $current_page === 'manage_users.php' ? 'bg-gradient-to-r from-blue-600 to-blue-800' : ''; ?>">
                    <i data-lucide="users" class="w-5 h-5 text-gray-600 transition duration-75 group-hover:text-white <?php echo $current_page === 'manage_users.php' ? 'text-white' : ''; ?>"></i>
                    <span class="ml-3 text-gray-600 transition duration-75 group-hover:text-white <?php echo $current_page === 'manage_users.php' ? 'text-white' : ''; ?>">Manage Users</span>
                </a>
            </li>
            <li>
                <a href="admin_user_progress.php" class="flex items-center p-2 rounded-lg hover:bg-gradient-to-r from-blue-600 to-blue-800 group <?php echo $current_page === 'admin_user_progress.php' ? 'bg-gradient-to-r from-blue-600 to-blue-800' : ''; ?>">
                    <i data-lucide="bar-chart-2" class="w-5 h-5 text-gray-600 transition duration-75 group-hover:text-white <?php echo $current_page === 'admin_user_progress.php' ? 'text-white' : ''; ?>"></i>
                    <span class="ml-3 text-gray-600 transition duration-75 group-hover:text-white <?php echo $current_page === 'admin_user_progress.php' ? 'text-white' : ''; ?>">User Progress</span>
                </a>
            </li>
            <?php else: ?>
            <li>
                <a href="view_courses.php" class="flex items-center p-2 rounded-lg hover:bg-gradient-to-r from-blue-600 to-blue-800 group <?php echo $current_page === 'view_courses.php' ? 'bg-gradient-to-r from-blue-600 to-blue-800' : ''; ?>">
                    <i data-lucide="book-open" class="w-5 h-5 text-gray-600 transition duration-75 group-hover:text-white <?php echo $current_page === 'view_courses.php' ? 'text-white' : ''; ?>"></i>
                    <span class="ml-3 text-gray-600 transition duration-75 group-hover:text-white <?php echo $current_page === 'view_courses.php' ? 'text-white' : ''; ?>">My Courses</span>
                </a>
            </li>
            <li>
                <a href="user_progress.php" class="flex items-center p-2 rounded-lg hover:bg-gradient-to-r from-blue-600 to-blue-800 group <?php echo $current_page === 'user_progress.php' ? 'bg-gradient-to-r from-blue-600 to-blue-800' : ''; ?>">
                    <i data-lucide="activity" class="w-5 h-5 text-gray-600 transition duration-75 group-hover:text-white <?php echo $current_page === 'user_progress.php' ? 'text-white' : ''; ?>"></i>
                    <span class="ml-3 text-gray-600 transition duration-75 group-hover:text-white <?php echo $current_page === 'user_progress.php' ? 'text-white' : ''; ?>">My Progress</span>
                </a>
            </li>
            <?php endif; ?>
            <li>
                <a href="logout.php" class="flex items-center p-2 rounded-lg hover:bg-gradient-to-r from-blue-600 to-blue-800 group text-red-500 hover:text-white">
                    <i data-lucide="log-out" class="w-5 h-5 transition duration-75 group-hover:text-white"></i>
                    <span class="ml-3 text-red-500 transition duration-75 group-hover:text-white">Logout</span>
                </a>
            </li>
        </ul>
    </div>
</aside>

<!-- Mobile menu button -->
<div class="fixed top-4 left-4 z-50 block md:hidden">
    <button type="button" class="toggle-sidebar inline-flex items-center p-2 text-sm text-blue-600 bg-blue-200 rounded-lg">
        <i data-lucide="menu" class="w-4 h-4"></i>
    </button>
</div>

<!-- Script for Mobile menu button -->
<script src="sidebar.js" defer></script>