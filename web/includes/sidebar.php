<?php
$current_page = basename($_SERVER['PHP_SELF']);
$is_in_includes = strpos($_SERVER['PHP_SELF'], '/includes/') !== false;
$base_path = $is_in_includes ? '..' : '.';
?>
<div class="sidebar">
    <div class="logo">
        <a href="<?php echo $base_path; ?>/index.php">
            <i class="bi bi-book"></i>
            <span>LMS System</span>
        </a>
    </div>
    <nav>
        <ul>
            <li>
                <a href="<?php echo $base_path; ?>/dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <li>
                <a href="<?php echo $base_path; ?>/manage_courses.php" class="<?php echo $current_page == 'manage_courses.php' ? 'active' : ''; ?>">
                    <i class="bi bi-journal-text"></i>
                    <span>Manage Courses</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_path; ?>/manage_users.php" class="<?php echo $current_page == 'manage_users.php' ? 'active' : ''; ?>">
                    <i class="bi bi-people"></i>
                    <span>Manage Users</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_path; ?>/admin_user_progress.php" class="<?php echo $current_page == 'admin_user_progress.php' ? 'active' : ''; ?>">
                    <i class="bi bi-graph-up"></i>
                    <span>User Progress</span>
                </a>
            </li>
            <?php else: ?>
            <li>
                <a href="<?php echo $base_path; ?>/user_progress.php" class="<?php echo $current_page == 'user_progress.php' ? 'active' : ''; ?>">
                    <i class="bi bi-graph-up"></i>
                    <span>My Progress</span>
                </a>
            </li>
            <?php endif; ?>
            <li>
                <a href="<?php echo $base_path; ?>/logout.php">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </nav>
</div>

<style>
.sidebar {
    width: 280px;
    height: 100vh;
    background: white;
    border-right: 1px solid #e5e7eb;
    position: fixed;
    top: 0;
    left: 0;
    display: flex;
    flex-direction: column;
    padding: 1.5rem;
}

.sidebar .logo {
    margin-bottom: 2rem;
}

.sidebar .logo a {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
    color: #111827;
    font-size: 1.25rem;
    font-weight: 600;
}

.sidebar .logo i {
    font-size: 1.5rem;
    color: #6366f1;
}

.sidebar nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar nav li {
    margin-bottom: 0.5rem;
}

.sidebar nav a {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    text-decoration: none;
    color: #6b7280;
    border-radius: 8px;
    transition: all 0.2s;
    font-size: 0.875rem;
    font-weight: 500;
}

.sidebar nav a:hover {
    background: #f3f4f6;
    color: #6366f1;
}

.sidebar nav a.active {
    background: rgba(99, 102, 241, 0.1);
    color: #6366f1;
}

.sidebar nav a i {
    font-size: 1.25rem;
}

.content {
    margin-left: 280px;
    padding: 2rem;
}

@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
        padding: 1rem;
    }
    
    .content {
        margin-left: 0;
        padding: 1rem;
    }
}
</style>
