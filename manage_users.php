<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_user'])) {
        $username = clean_input($_POST['username']);
        $email = clean_input($_POST['email']);
        $full_name = clean_input($_POST['full_name']);  // Add this line
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = clean_input($_POST['role']);
        
        try {
            $stmt = $conn->prepare("INSERT INTO users (username, email, full_name, password, role) VALUES (?, ?, ?, ?, ?)");  // Modified
            $stmt->bind_param("sssss", $username, $email, $full_name, $password, $role);  // Modified
            if ($stmt->execute()) {
                $success = "User added successfully!";
            }
        } catch (mysqli_sql_exception $e) {
            $error = ($e->getCode() == 1062) ? "Username or email already exists" : "Error adding user";
        }
    }

    // Handle edit user action
    if (isset($_POST['edit_user'])) {
        $user_id = $_POST['user_id'];
        $username = clean_input($_POST['username']);
        $email = clean_input($_POST['email']);
        $full_name = clean_input($_POST['full_name']);  // Add this line
        $role = clean_input($_POST['role']);
        
        if (!empty($_POST['password'])) {
            // If password is provided, hash it and update all fields
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, full_name = ?, password = ?, role = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $username, $email, $full_name, $password, $role, $user_id);
        } else {
            // If no password provided, update other fields only
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, full_name = ?, role = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $username, $email, $full_name, $role, $user_id);
        }
        
        if ($stmt->execute()) {
            $success = "User updated successfully!";
        } else {
            $error = "Error updating user";
        }
    }

    // Handle delete user action
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        if ($user_id != $_SESSION['user_id']) {
            try {
                // Start transaction
                $conn->begin_transaction();

                // First, delete user's progress
                $stmt = $conn->prepare("DELETE FROM user_progress WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();

                // Then delete the user
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();

                // Commit transaction
                $conn->commit();
                $success = "User deleted successfully!";
            } catch (Exception $e) {
                // Rollback on error
                $conn->rollback();
                $error = "Error deleting user: " . $e->getMessage();
            }
        }
    }
}

// Get all users for display with search functionality
$search = $_GET['search'] ?? '';
$query = "SELECT id, username, full_name, email, role, created_at 
          FROM users 
          WHERE (username LIKE ? OR full_name LIKE ? OR email LIKE ?)
          ORDER BY created_at DESC";
$search_param = "%$search%";
$stmt = $conn->prepare($query);
$stmt->bind_param("sss", $search_param, $search_param, $search_param);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="content">
            <div class="container mt-4">
                <!-- Stats Cards -->
                <div class="row admin-stats mb-4">
                    <div class="col-md-4">
                        <div class="stats-card dashboard-card">
                            <div class="card-body text-center">
                                <i class="bi bi-people-fill stats-icon"></i>
                                <h3>Total Users</h3>
                                <h2><?php echo count($users); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search and Add User Section -->
                <div class="admin-card mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="mb-0"><i class="bi bi-people"></i> Manage Users</h2>
                        <button type="button" class="btn btn-primary action-button" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="bi bi-person-plus"></i> Add New User
                        </button>
                    </div>
                    
                    <?php 
                    $search_placeholder = "Search users...";
                    include 'includes/search_bar.php'; 
                    ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Users Table -->
                    <div class="table-responsive mt-3">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $search = $_GET['search'] ?? '';
                                $query = "SELECT * FROM users 
                                        WHERE username LIKE ? OR email LIKE ? 
                                        ORDER BY created_at DESC";
                                $search_param = "%$search%";
                                $stmt = $conn->prepare($query);
                                $stmt->bind_param("ss", $search_param, $search_param);
                                $stmt->execute();
                                $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

                                foreach ($users as $user): 
                                ?>
                                <tr>
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-person-circle me-2"></i>
                                            <div>
                                                <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                                                <small class="text-muted d-block">@<?php echo htmlspecialchars($user['username']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                            <?php echo htmlspecialchars($user['role']); ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <button class="btn btn-sm btn-outline-primary me-2" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editUserModal"
                                                    data-user-id="<?php echo $user['id']; ?>"
                                                    data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                                    data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                                    data-full-name="<?php echo htmlspecialchars($user['full_name']); ?>"
                                                    data-role="<?php echo htmlspecialchars($user['role']); ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteUserModal"
                                                    data-user-id="<?php echo $user['id']; ?>"
                                                    data-username="<?php echo htmlspecialchars($user['username']); ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editUserForm" method="POST">
                    <input type="hidden" name="edit_user" value="1">
                    <input type="hidden" name="user_id" id="editUserId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="edit_full_name" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="edit_username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_password" class="form-label">New Password (leave blank to keep current)</label>
                            <input type="password" class="form-control" id="edit_password" name="password">
                        </div>
                        <div class="mb-3">
                            <label for="edit_role" class="form-label">Role</label>
                            <select class="form-select" id="edit_role" name="role" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete User Modal -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete user <strong id="deleteUserName"></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" id="deleteUserForm">
                        <input type="hidden" name="delete_user" value="1">
                        <input type="hidden" name="user_id" id="deleteUserId">
                        <button type="submit" class="btn btn-danger">Delete User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle Edit User Modal
        document.addEventListener('DOMContentLoaded', function() {
            const editUserModal = document.getElementById('editUserModal');
            editUserModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const userId = button.getAttribute('data-user-id');
                const username = button.getAttribute('data-username');
                const email = button.getAttribute('data-email');
                const fullName = button.getAttribute('data-full-name');
                const role = button.getAttribute('data-role');

                editUserModal.querySelector('#editUserId').value = userId;
                editUserModal.querySelector('#edit_username').value = username;
                editUserModal.querySelector('#edit_email').value = email;
                editUserModal.querySelector('#edit_full_name').value = fullName;
                editUserModal.querySelector('#edit_role').value = role;
            });
        });

        // Handle Delete User Modal
        document.addEventListener('DOMContentLoaded', function() {
            const deleteUserModal = document.getElementById('deleteUserModal');
            deleteUserModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const userId = button.getAttribute('data-user-id');
                const username = button.getAttribute('data-username');

                deleteUserModal.querySelector('#deleteUserId').value = userId;
                deleteUserModal.querySelector('#deleteUserName').textContent = username;
            });
        });

        // Show modal if there was an error adding user
        <?php if ($error && isset($_POST['add_user'])): ?>
            document.addEventListener('DOMContentLoaded', function() {
                var modal = new bootstrap.Modal(document.getElementById('addUserModal'));
                modal.show();
            });
        <?php endif; ?>
    </script>
</body>
</html>
