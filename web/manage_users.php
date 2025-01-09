<?php
session_start();
require_once '../config.php';

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
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="content">
            <div class="container mt-4">
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="admin-card">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon me-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3>Total Users</h3>
                                    <h2><?php echo count($users); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Management Section -->
                <div class="admin-card">
                    <div class="page-header">
                        <h1 class="page-title">
                            <i class="bi bi-people"></i>
                            Manage Users
                        </h1>
                        <div class="header-actions">
                            <div class="search-wrapper">
                                <i class="bi bi-search search-icon"></i>
                                <input type="search" 
                                       class="search-box" 
                                       id="searchUsers" 
                                       placeholder="Search users by name, email..." 
                                       required>
                                <button type="button" class="clear-search" onclick="clearSearch()">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                <i class="bi bi-person-plus"></i>
                                Add New User
                            </button>
                        </div>
                    </div>

                    <!-- Users Table -->
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="stats-icon me-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                    <circle cx="12" cy="7" r="4"></circle>
                                                </svg>
                                            </div>
                                            <div>
                                                <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                                                <small class="text-muted d-block">@<?php echo htmlspecialchars($user['username']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td class="align-middle">
                                        <span class="badge <?php echo $user['role'] === 'admin' ? 'bg-danger' : 'bg-primary'; ?>">
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
                                                    data-role="<?php echo htmlspecialchars($user['role']); ?>"
                                                    style="border-color: #0d6efd; color: #0d6efd;">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteUserModal"
                                                    data-user-id="<?php echo $user['id']; ?>"
                                                    data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                                    style="border-color: #dc3545; color: #dc3545;">
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
                    <h5 class="modal-title" style="color: #212529;">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="full_name" class="form-label" style="color: #212529;">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required
                                   style="background-color: white; color: #212529; border: 1px solid #dee2e6;">
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label" style="color: #212529;">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required
                                   style="background-color: white; color: #212529; border: 1px solid #dee2e6;">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label" style="color: #212529;">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required
                                   style="background-color: white; color: #212529; border: 1px solid #dee2e6;">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label" style="color: #212529;">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required
                                   style="background-color: white; color: #212529; border: 1px solid #dee2e6;">
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label" style="color: #212529;">Role</label>
                            <select class="form-select" id="role" name="role" required
                                   style="background-color: white; color: #212529; border: 1px solid #dee2e6;">
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
                    <h5 class="modal-title" style="color: #212529;">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editUserForm" method="POST">
                    <input type="hidden" name="edit_user" value="1">
                    <input type="hidden" name="user_id" id="editUserId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_full_name" class="form-label" style="color: #212529;">Full Name</label>
                            <input type="text" class="form-control" id="edit_full_name" name="full_name" required
                                   style="background-color: white; color: #212529; border: 1px solid #dee2e6;">
                        </div>
                        <div class="mb-3">
                            <label for="edit_username" class="form-label" style="color: #212529;">Username</label>
                            <input type="text" class="form-control" id="edit_username" name="username" required
                                   style="background-color: white; color: #212529; border: 1px solid #dee2e6;">
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label" style="color: #212529;">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required
                                   style="background-color: white; color: #212529; border: 1px solid #dee2e6;">
                        </div>
                        <div class="mb-3">
                            <label for="edit_password" class="form-label" style="color: #212529;">New Password (leave blank to keep current)</label>
                            <input type="password" class="form-control" id="edit_password" name="password"
                                   style="background-color: white; color: #212529; border: 1px solid #dee2e6;">
                        </div>
                        <div class="mb-3">
                            <label for="edit_role" class="form-label" style="color: #212529;">Role</label>
                            <select class="form-select" id="edit_role" name="role" required
                                   style="background-color: white; color: #212529; border: 1px solid #dee2e6;">
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
        document.addEventListener('DOMContentLoaded', function() {
            // Real-time search functionality
            const searchInput = document.getElementById('searchUsers');
            const userRows = document.querySelectorAll('tr[data-user-row]');
            
            function filterUsers(searchTerm) {
                searchTerm = searchTerm.toLowerCase();
                userRows.forEach(row => {
                    const name = row.querySelector('[data-user-name]').textContent.toLowerCase();
                    const email = row.querySelector('[data-user-email]').textContent.toLowerCase();
                    const username = row.querySelector('[data-username]').textContent.toLowerCase();
                    const matches = name.includes(searchTerm) || 
                                  email.includes(searchTerm) || 
                                  username.includes(searchTerm);
                    row.style.display = matches ? '' : 'none';
                });
            }

            searchInput.addEventListener('input', (e) => {
                filterUsers(e.target.value);
            });

            // Clear search functionality
            window.clearSearch = function() {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input'));
                searchInput.focus();
            };

            // Handle Edit User Modal
            const editUserModal = document.getElementById('editUserModal');
            if (editUserModal) {
                editUserModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const userId = button.getAttribute('data-user-id');
                    const username = button.getAttribute('data-username');
                    const email = button.getAttribute('data-email');
                    const fullName = button.getAttribute('data-full-name');
                    const role = button.getAttribute('data-role');

                    this.querySelector('#editUserId').value = userId;
                    this.querySelector('#edit_username').value = username;
                    this.querySelector('#edit_email').value = email;
                    this.querySelector('#edit_full_name').value = fullName;
                    this.querySelector('#edit_role').value = role;
                });
            }

            // Handle Delete User Modal
            const deleteUserModal = document.getElementById('deleteUserModal');
            if (deleteUserModal) {
                deleteUserModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const userId = button.getAttribute('data-user-id');
                    const username = button.getAttribute('data-username');

                    this.querySelector('#deleteUserId').value = userId;
                    this.querySelector('#deleteUserName').textContent = username;
                });
            }

            // Form validation
            const addUserForm = document.querySelector('form[name="addUserForm"]');
            const editUserForm = document.querySelector('form[name="editUserForm"]');

            function validateForm(form) {
                const password = form.querySelector('input[type="password"]');
                const email = form.querySelector('input[type="email"]');
                
                if (password && password.value.length < 6) {
                    alert('Password must be at least 6 characters long');
                    return false;
                }

                if (email && !email.value.includes('@')) {
                    alert('Please enter a valid email address');
                    return false;
                }

                return true;
            }

            if (addUserForm) {
                addUserForm.addEventListener('submit', function(e) {
                    if (!validateForm(this)) {
                        e.preventDefault();
                    }
                });
            }

            if (editUserForm) {
                editUserForm.addEventListener('submit', function(e) {
                    if (!validateForm(this)) {
                        e.preventDefault();
                    }
                });
            }

            // Show success/error messages
            const alertMessage = document.querySelector('.alert');
            if (alertMessage) {
                setTimeout(() => {
                    alertMessage.classList.remove('show');
                    setTimeout(() => alertMessage.remove(), 150);
                }, 3000);
            }
        });
    </script>

    <style>
        /* Table Styles */
        .table th {
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #e9ecef;
            padding: 1rem;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
        }

        /* User Icon Styles */
        .user-icon {
            width: 40px;
            height: 40px;
            background-color: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }

        /* Badge Styles */
        .badge {
            padding: 0.5em 0.75em;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge.bg-primary {
            background-color: #6366f1 !important;
        }

        .badge.bg-danger {
            background-color: #ef4444 !important;
        }

        /* Button Styles */
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .btn-outline-primary {
            color: #6366f1;
            border-color: #6366f1;
        }

        .btn-outline-primary:hover {
            background-color: #6366f1;
            border-color: #6366f1;
        }

        .btn-outline-danger {
            color: #ef4444;
            border-color: #ef4444;
        }

        .btn-outline-danger:hover {
            background-color: #ef4444;
            border-color: #ef4444;
        }

        /* Modal Styles */
        .modal-content {
            border: none;
            border-radius: 8px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            border-bottom: 1px solid #e9ecef;
            padding: 1.25rem 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid #e9ecef;
            padding: 1.25rem 1.5rem;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 4px;
            border: 1px solid #dee2e6;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }

        .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.25);
        }
    </style>
</body>
</html>
