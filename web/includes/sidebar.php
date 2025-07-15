<?php
$current_page = basename($_SERVER['PHP_SELF']);
$is_in_includes = strpos($_SERVER['PHP_SELF'], '/includes/') !== false;
$base_path = $is_in_includes ? '..' : '.';
?>
<!-- Add Lucide icons -->
<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<!-- Sidebar -->
<aside id="sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform bg-blue-900 text-white">
    <div class="h-full px-3 py-4 overflow-y-auto">
        <div class="flex items-center mb-8 px-2">
            <i data-lucide="book-open" class="h-7 w-7 text-white mr-3"></i>
            <span class="self-center text-xl font-semibold whitespace-nowrap">LMS System</span>
        </div>
        
        <div class="mb-6 px-2">
            <div class="text-xs uppercase tracking-wider text-blue-300 mb-1">
                <?php echo $_SESSION['role'] === 'admin' ? 'ADMIN' : 'USER'; ?>
            </div>
            <div class="font-medium">
                <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'System Administrator'); ?>
            </div>
        </div>
        
        <ul class="space-y-2 font-medium">
            <li>
                <a href="dashboard.php" class="flex items-center p-2 rounded-lg hover:bg-blue-800 group <?php echo $current_page === 'dashboard.php' ? 'bg-blue-800' : ''; ?>">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 text-blue-200 transition duration-75 group-hover:text-white <?php echo $current_page === 'dashboard.php' ? 'text-white' : ''; ?>"></i>
                    <span class="ml-3">Dashboard</span>
                </a>
            </li>
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <li>
                <a href="manage_courses.php" class="flex items-center p-2 rounded-lg hover:bg-blue-800 group <?php echo $current_page === 'manage_courses.php' ? 'bg-blue-800' : ''; ?>">
                    <i data-lucide="book-open" class="w-5 h-5 text-blue-200 transition duration-75 group-hover:text-white <?php echo $current_page === 'manage_courses.php' ? 'text-white' : ''; ?>"></i>
                    <span class="ml-3">Manage Courses</span>
                </a>
            </li>
            <li>
                <a href="manage_users.php" class="flex items-center p-2 rounded-lg hover:bg-blue-800 group <?php echo $current_page === 'manage_users.php' ? 'bg-blue-800' : ''; ?>">
                    <i data-lucide="users" class="w-5 h-5 text-blue-200 transition duration-75 group-hover:text-white <?php echo $current_page === 'manage_users.php' ? 'text-white' : ''; ?>"></i>
                    <span class="ml-3">Manage Users</span>
                </a>
            </li>
            <li>
                <a href="admin_user_progress.php" class="flex items-center p-2 rounded-lg hover:bg-blue-800 group <?php echo $current_page === 'admin_user_progress.php' ? 'bg-blue-800' : ''; ?>">
                    <i data-lucide="bar-chart-2" class="w-5 h-5 text-blue-200 transition duration-75 group-hover:text-white <?php echo $current_page === 'admin_user_progress.php' ? 'text-white' : ''; ?>"></i>
                    <span class="ml-3">User Progress</span>
                </a>
            </li>
            <?php else: ?>
            <li>
                <a href="view_courses.php" class="flex items-center p-2 rounded-lg hover:bg-blue-800 group <?php echo $current_page === 'view_courses.php' ? 'bg-blue-800' : ''; ?>">
                    <i data-lucide="book-open" class="w-5 h-5 text-blue-200 transition duration-75 group-hover:text-white <?php echo $current_page === 'view_courses.php' ? 'text-white' : ''; ?>"></i>
                    <span class="ml-3">My Courses</span>
                </a>
            </li>
            <li>
                <a href="user_progress.php" class="flex items-center p-2 rounded-lg hover:bg-blue-800 group <?php echo $current_page === 'user_progress.php' ? 'bg-blue-800' : ''; ?>">
                    <i data-lucide="activity" class="w-5 h-5 text-blue-200 transition duration-75 group-hover:text-white <?php echo $current_page === 'user_progress.php' ? 'text-white' : ''; ?>"></i>
                    <span class="ml-3">My Progress</span>
                </a>
            </li>
            <?php endif; ?>
            <li>
                <a href="logout.php" class="flex items-center p-2 rounded-lg hover:bg-blue-800 group text-red-300 hover:text-red-200">
                    <i data-lucide="log-out" class="w-5 h-5 transition duration-75 group-hover:text-red-200"></i>
                    <span class="ml-3">Logout</span>
                </a>
            </li>
        </ul>
    </div>
</aside>

<!-- Mobile menu button -->
<div class="fixed top-4 left-4 z-50 block md:hidden">
    <button type="button" class="toggle-sidebar inline-flex items-center p-2 text-sm text-white bg-blue-800 rounded-lg hover:bg-blue-700">
        <i data-lucide="menu" class="w-6 h-6"></i>
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide icons
    lucide.createIcons();
    
    // Toggle sidebar on mobile
    const toggleBtn = document.querySelector('.toggle-sidebar');
    const sidebar = document.getElementById('sidebar');
    
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('-translate-x-full');
        });
    }
});
</script>
