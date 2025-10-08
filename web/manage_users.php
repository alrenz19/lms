<?php
session_start();
require_once '../config.php';
require_once 'includes/security.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'super_admin')) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_user'])) {
        try {
            $username = clean_input($_POST['username']);
            $email = clean_input($_POST['email']);
            $full_name = clean_input($_POST['full_name']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $role = clean_input($_POST['role']);
            
            // Validate inputs
            if (empty($username) || empty($email) || empty($full_name) || empty($password) || empty($role)) {
                throw new Exception("All fields are required");
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format");
            }
            
            // Begin transaction
            $conn->begin_transaction();
            
            $stmt = $conn->prepare("INSERT INTO users (username, email, full_name, password, role) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("sssss", $username, $email, $full_name, $password, $role);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $conn->commit();
            $success = "User added successfully!";
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error adding user: " . $e->getMessage();
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

// For sidebar fix, include header
include 'includes/header.php';

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>LMS - Manage Users</title>
</head>
<body class="bg-blue-50">    
    <div class="p-8 sm:ml-72">
        <div class="container mx-auto">
            <!-- Page header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl p-6 mb-6 flex justify-between items-center shadow-sm">
                <div class="flex items-center">
                    <div class="bg-white/10 p-3 rounded-lg mr-4">
                        <i data-lucide="users" class="h-8 w-8 text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">User Management</h1>
                        <p class="text-blue-100">Create and manage users with different roles</p>
                    </div>
                </div>
                <button class="px-4 py-2.5 bg-white text-blue-600 rounded-lg flex items-center gap-2 transition-all duration-200 font-medium shadow-sm" 
                        onclick="showModal('addUserModal')">
                    <i data-lucide="user-plus" class="h-5 w-5"></i>
                    <span>Add New User</span>
                </button>
            </div>

            <?php if (!empty($error)): ?>
                <div class="mb-6 rounded-lg p-4 flex items-center gap-2 bg-red-50 text-red-700 border-l-4 border-red-500" style="display: none;">
                    <i data-lucide="alert-circle" class="h-5 w-5 text-red-500"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                    <button type="button" class="ml-auto text-gray-400 hover:text-gray-600" onclick="this.parentElement.remove()">
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="mb-6 rounded-lg p-4 flex items-center gap-2 bg-green-50 text-green-700 border-l-4 border-green-500" style="display: none;">
                    <i data-lucide="check-circle" class="h-5 w-5 text-green-500"></i>
                    <span><?php echo htmlspecialchars($success); ?></span>
                    <button type="button" class="ml-auto text-gray-400 hover:text-gray-600" onclick="this.parentElement.remove()">
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-gray-700 font-medium">Total Users</h3>
                        <div class="bg-blue-100 text-blue-800 p-2 rounded-lg">
                            <i data-lucide="users" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo count($users); ?></div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-gray-700 font-medium">Administrators</h3>
                        <div class="bg-blue-100 text-blue-800 p-2 rounded-lg">
                            <i data-lucide="shield" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-1">
                        <?php 
                        $admin_count = 0;
                        foreach ($users as $user) {
                            if ($user['role'] === 'admin' || $user['role'] === 'super_admin') {
                                $admin_count++;
                            }
                        }
                        echo $admin_count;
                        ?>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-gray-700 font-medium">Regular Users</h3>
                        <div class="bg-blue-100 text-blue-800 p-2 rounded-lg">
                            <i data-lucide="user" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-1">
                        <?php 
                        $user_count = 0;
                        foreach ($users as $user) {
                            if ($user['role'] === 'user') {
                                $user_count++;
                            }
                        }
                        echo $user_count;
                        ?>
                    </div>
                </div>
            </div>

            <!-- User Management Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i data-lucide="users" class="h-5 w-5 text-blue-600 mr-2"></i>
                            User List
                        </h2>
                        <div class="relative max-w-xs w-full">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                                <i data-lucide="search" class="h-4 w-4"></i>
                            </span>
                            <input type="search" 
                                   class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition" 
                                   id="searchUsers" 
                                   placeholder="Search users..." 
                                   required>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 text-left">
                                <th class="px-6 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-500">
                                            <i data-lucide="users-x" class="h-12 w-12 text-gray-300 mb-4"></i>
                                            <h3 class="text-lg font-medium text-gray-900 mb-1">No users found</h3>
                                            <p class="text-gray-500 mb-4">Get started by creating your first user</p>
                                            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg flex items-center gap-2 transition text-sm font-medium" 
                                                    onclick="showModal('addUserModal')">
                                                <i data-lucide="user-plus" class="h-4 w-4"></i> Add New User
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                <tr class="hover:bg-blue-50/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 flex-shrink-0 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                                                <i data-lucide="<?php echo $user['role'] === 'admin' ? 'shield' : 'user'; ?>" class="h-5 w-5 text-blue-600"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                                <div class="text-sm text-gray-500">@<?php echo htmlspecialchars($user['username']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-600"><?php echo htmlspecialchars($user['email']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1.5 text-xs font-medium rounded-full 
                                            <?php echo $user['role'] === 'admin' || $user['role'] === 'super_admin' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800'; ?>">
                                            <?php echo htmlspecialchars($user['role']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex justify-center items-center space-x-2">
                                            <?php if ($_SESSION['role'] == 'super_admin'): ?>
                                                <button class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors" 
                                                        onclick="openEditUserModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>', '<?php echo htmlspecialchars($user['email']); ?>', '<?php echo htmlspecialchars($user['full_name']); ?>', '<?php echo htmlspecialchars($user['role']); ?>')"
                                                        title="Edit User">
                                                    <i data-lucide="pencil" class="h-4 w-4"></i>
                                                </button>
                                                <button class="p-1.5 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors" 
                                                        onclick="openDeleteUserModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>')"
                                                        title="Delete User">
                                                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                                                </button>
                                            <?php elseif ($user['id'] == $_SESSION['user_id']): ?>
                                                <button class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors" 
                                                        onclick="openEditUserModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>', '<?php echo htmlspecialchars($user['email']); ?>', '<?php echo htmlspecialchars($user['full_name']); ?>', '<?php echo htmlspecialchars($user['role']); ?>')"
                                                        title="Edit User">
                                                    <i data-lucide="pencil" class="h-4 w-4"></i>
                                                </button>
                                                <button class="p-1.5 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors" 
                                                        onclick="openDeleteUserModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>')"
                                                        title="Delete User">
                                                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                                                </button>
                                            <?php else: ?>
                                                <span class="text-xs text-gray-400 italic">Not Allowed</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center backdrop-blur-sm transition-all duration-300" id="addUserModal">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 transform transition-all duration-300">
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 p-5 rounded-t-xl flex items-center justify-between">
                <h5 class="text-lg font-semibold text-white flex items-center">
                    <i data-lucide="user-plus" class="h-5 w-5 mr-2"></i>
                    Add New User
                </h5>
                <button type="button" class="text-white/80 hover:text-white focus:outline-none" onclick="hideModal('addUserModal')">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>
            <form method="POST" action="" id="addUserForm" data-show-toast="true" data-toast-message="User added successfully" data-toast-type="success" data-validate="true">
                <div class="p-6 space-y-4">
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="user" class="h-5 w-5 text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition" 
                                   id="full_name" 
                                   name="full_name" 
                                   placeholder="Enter full name"
                                   required>
                        </div>
                    </div>
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="at-sign" class="h-5 w-5 text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition" 
                                   id="username" 
                                   name="username" 
                                   placeholder="Enter username"
                                   required>
                        </div>
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="mail" class="h-5 w-5 text-gray-400"></i>
                            </div>
                            <input type="email" 
                                   class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition" 
                                   id="email" 
                                   name="email" 
                                   placeholder="Enter email address"
                                   required>
                        </div>
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="key" class="h-5 w-5 text-gray-400"></i>
                            </div>
                            <input type="password" 
                                   class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Enter password"
                                   required>
                        </div>
                    </div>
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="shield" class="h-5 w-5 text-gray-400"></i>
                            </div>
                            <?php if ($_SESSION['role'] === 'admin'){?>
                            <select class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition" 
                                    id="role" 
                                    name="role" 
                                    required>
                                <option value="user">User</option>
                            </select>
                          <?php } else { ?>
                            <select class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition" 
                                    id="role" 
                                    name="role" 
                                    required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                            <?php }?>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end p-5 border-t border-gray-200 gap-3">
                    <button type="button" 
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition text-sm font-medium flex items-center gap-1"
                            onclick="hideModal('addUserModal')">
                        <i data-lucide="x" class="h-4 w-4"></i>
                        Cancel
                    </button>
                    <button type="submit" 
                            name="add_user" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition text-sm font-medium flex items-center gap-2">
                        <i data-lucide="save" class="h-4 w-4"></i>
                        Save User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center backdrop-blur-sm transition-all duration-300" id="editUserModal">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 transform transition-all duration-300">
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 p-5 rounded-t-xl flex items-center justify-between">
                <h5 class="text-lg font-semibold text-white flex items-center">
                    <i data-lucide="pencil" class="h-5 w-5 mr-2"></i>
                    Edit User
                </h5>
                <button type="button" class="text-white/80 hover:text-white focus:outline-none" onclick="hideModal('editUserModal')">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>
            <form id="editUserForm" method="POST" data-show-toast="true" data-toast-message="User updated successfully" data-toast-type="success" data-validate="true">
                <input type="hidden" name="edit_user" value="1">
                <input type="hidden" name="user_id" id="editUserId">
                <div class="p-6 space-y-4">
                    <div>
                        <label for="edit_full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="user" class="h-5 w-5 text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition" 
                                   id="edit_full_name" 
                                   name="full_name" 
                                   required>
                        </div>
                    </div>
                    <div>
                        <label for="edit_username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="at-sign" class="h-5 w-5 text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition" 
                                   id="edit_username" 
                                   name="username" 
                                   required>
                        </div>
                    </div>
                    <div>
                        <label for="edit_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="mail" class="h-5 w-5 text-gray-400"></i>
                            </div>
                            <input type="email" 
                                   class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition" 
                                   id="edit_email" 
                                   name="email" 
                                   required>
                        </div>
                    </div>
                    <div>
                        <label for="edit_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="key" class="h-5 w-5 text-gray-400"></i>
                            </div>
                            <input type="password" 
                                   class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition" 
                                   id="edit_password" 
                                   name="password"
                                   placeholder="Leave blank to keep current password">
                        </div>
                    </div>
                    <div>
                        <label for="edit_role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="shield" class="h-5 w-5 text-gray-400"></i>
                            </div>
                            <select class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition" 
                                    id="edit_role" 
                                    name="role" 
                                    required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end p-5 border-t border-gray-200 gap-3">
                    <button type="button" 
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition text-sm font-medium flex items-center gap-1"
                            onclick="hideModal('editUserModal')">
                        <i data-lucide="x" class="h-4 w-4"></i>
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition text-sm font-medium flex items-center gap-2">
                        <i data-lucide="save" class="h-4 w-4"></i>
                        Update User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete User Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center backdrop-blur-sm transition-all duration-300" id="deleteUserModal">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 transform transition-all duration-300">
            <div class="bg-gradient-to-r from-red-600 to-red-800 p-5 rounded-t-xl flex items-center justify-between">
                <h5 class="text-lg font-semibold text-white flex items-center">
                    <i data-lucide="trash-2" class="h-5 w-5 mr-2"></i>
                    Delete User
                </h5>
                <button type="button" class="text-white/80 hover:text-white focus:outline-none" onclick="hideModal('deleteUserModal')">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>
            <div class="p-6">
                <div class="bg-red-50 p-4 rounded-lg mb-4 flex items-start gap-3">
                    <i data-lucide="alert-triangle" class="h-5 w-5 text-red-500 mt-0.5"></i>
                    <div>
                        <p class="text-gray-700">Are you sure you want to delete user <strong id="deleteUserName" class="text-gray-900"></strong>?</p>
                        <p class="text-red-600 mt-2 text-sm">This action cannot be undone and will delete all user progress data.</p>
                    </div>
                </div>
            </div>
            <div class="flex justify-end p-5 border-t border-gray-200 gap-3">
                <button type="button" 
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg transition text-sm font-medium flex items-center gap-1"
                        onclick="hideModal('deleteUserModal')">
                    <i data-lucide="x" class="h-4 w-4"></i>
                    Cancel
                </button>
                <form method="POST" class="inline">
                    <input type="hidden" name="delete_user" value="1">
                    <input type="hidden" name="user_id" id="deleteUserId">
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition text-sm font-medium flex items-center gap-2">
                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                        Delete User
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Initialize toast for PHP messages
        <?php if (!empty($error)): ?>
            showToast('<?php echo addslashes(htmlspecialchars($error)); ?>', 'error');
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            showToast('<?php echo addslashes(htmlspecialchars($success)); ?>', 'success');
        <?php endif; ?>
        
        // Real-time search functionality
        const searchInput = document.getElementById('searchUsers');
        const userRows = document.querySelectorAll('tbody tr');
        
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            userRows.forEach(row => {
                if (row.cells.length > 1) { // Skip rows with colspan (like "no users found")
                    const fullName = row.querySelector('.text-gray-900')?.textContent.toLowerCase() || '';
                    const email = row.querySelector('.text-gray-600')?.textContent.toLowerCase() || '';
                    const username = row.querySelector('.text-gray-500')?.textContent.toLowerCase() || '';
                    
                    const matches = fullName.includes(searchTerm) || 
                                  email.includes(searchTerm) || 
                                  username.includes(searchTerm);
                                  
                    row.style.display = matches ? '' : 'none';
                }
            });
        });
        
        // Modal functions
        function openEditUserModal(id, username, email, fullName, role) {
            document.getElementById('editUserId').value = id;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_full_name').value = fullName;
            document.getElementById('edit_role').value = role;
            
            showModal('editUserModal');
        }
        
        function openDeleteUserModal(id, name) {
            document.getElementById('deleteUserId').value = id;
            document.getElementById('deleteUserName').textContent = name;
            
            showModal('deleteUserModal');
        }
        
        // Show modal function
        function showModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        
        // Hide modal function
        function hideModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }
        
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
</body>
</html>
