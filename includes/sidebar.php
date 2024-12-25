<div class="sidebar bg-dark text-white d-flex flex-column">
    <div class="p-3">
        <h3 class="text-white">
            <i class="bi bi-book-half me-2"></i>
            LMS Portal
        </h3>
        <hr class="text-white">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link text-white" href="dashboard.php">
                    <i class="bi bi-speedometer2 nav-icon"></i>
                    Dashboard
                </a>
            </li>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link text-white" href="manage_users.php">
                        <i class="bi bi-people-fill nav-icon"></i>
                        Manage Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="manage_courses.php">
                        <i class="bi bi-journal-richtext nav-icon"></i>
                        Manage Courses
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="admin_user_progress.php">
                        <i class="bi bi-graph-up"></i> User Progress
                    </a>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link text-white" href="user_progress.php">
                        <i class="bi bi-graph-up-arrow nav-icon"></i>
                        My Progress
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
    <div class="mt-auto p-3 border-top border-secondary">
        <a class="nav-link text-white" href="logout.php">
            <i class="bi bi-box-arrow-right nav-icon"></i>
            Logout
        </a>
    </div>
</div>
