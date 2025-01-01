<div class="sidebar bg-dark text-white">
    <div class="sidebar-header">
        <div class="user-info">
            <i class="bi bi-person-circle"></i>
            <div>
                <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong>
                <small class="text-muted"><?php echo ucfirst($_SESSION['role']); ?></small>
            </div>
        </div>
    </div>

    <div>
        <h3>
            <i class="bi bi-book-half me-2"></i>
            LMS Portal
        </h3>

        <h3>Main Menu</h3>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
                    <i class="bi bi-speedometer2 nav-icon"></i>
                    Dashboard
                </a>
            </li>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="manage_users.php">
                        <i class="bi bi-people-fill nav-icon"></i>
                        Manage Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_courses.php">
                        <i class="bi bi-journal-richtext nav-icon"></i>
                        Manage Courses
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_user_progress.php">
                        <i class="bi bi-graph-up nav-icon"></i>
                        User Progress
                    </a>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="user_progress.php">
                        <i class="bi bi-graph-up-arrow nav-icon"></i>
                        My Progress
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="mt-auto">
        <h3>Account</h3>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="profile.php">
                    <i class="bi bi-person nav-icon"></i>
                    Profile
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="bi bi-box-arrow-right nav-icon"></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>
</div>
