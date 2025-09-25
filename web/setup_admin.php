<?php
session_start();
require_once '../config.php';

// Check if admin already exists
$stmt = $conn->prepare("SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin'");
$stmt->execute();
$result = $stmt->get_result();
$admin_exists = $result->fetch_assoc()['admin_count'] > 0;

// If admin exists, redirect to login page
if ($admin_exists) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $full_name = trim($_POST['full_name']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate inputs
        if (empty($username) || empty($email) || empty($full_name) || empty($password)) {
            throw new Exception("All fields are required");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        if (strlen($password) < 6) {
            throw new Exception("Password must be at least 6 characters long");
        }

        if ($password !== $confirm_password) {
            throw new Exception("Passwords do not match");
        }

        // Begin transaction
        $conn->begin_transaction();

        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()['count'] > 0) {
            throw new Exception("Username or email already exists");
        }

        // Insert admin user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, full_name, password, role) VALUES (?, ?, ?, ?, 'admin')");
        $stmt->bind_param("ssss", $username, $email, $full_name, $hashed_password);
        
        if (!$stmt->execute()) {
            throw new Exception("Error creating admin account");
        }

        $conn->commit();
        $success = "Admin account created successfully! Redirecting to login...";
        header("refresh:2;url=index.php");

    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Admin Account - LMS</title>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link rel="stylesheet" href="./public/css/tailwind.min.css" />
    <link rel="stylesheet" href="../assets/css/style.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        blue: {
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
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .setup-container {
            background: white;
            border-radius: 1rem;
            padding: 2.5rem;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .setup-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        @media (max-width: 640px) {
            .setup-container {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <?php if (!empty($error)): ?>
            <div class="bg-red-50 border border-red-100 text-red-600 rounded-xl p-4 mb-6 flex items-center gap-2 relative">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="bg-green-50 border border-green-100 text-green-600 rounded-xl p-4 mb-6 flex items-center gap-2 relative">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>

        <div class="setup-header">
            <div class="w-16 h-16 rounded-full bg-blue-600 flex items-center justify-center mx-auto mb-6">
                <i data-lucide="user-check" class="w-8 h-8 text-white"></i>
            </div>
            <h1 class="text-2xl font-semibold text-blue-900 mb-2">Welcome to LMS</h1>
            <p class="text-blue-600">Set up your administrator account to get started</p>
        </div>

        <form method="POST" action="" id="setupForm">
            <div class="mb-4">
                <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <div class="flex border border-gray-200 rounded-xl overflow-hidden shadow-sm focus-within:ring-2 focus-within:ring-blue-100 focus-within:border-blue-500 transition">
                    <span class="bg-blue-50 border-r border-gray-200 text-blue-500 px-3 flex items-center">
                        <i data-lucide="user" class="w-5 h-5"></i>
                    </span>
                    <input type="text" 
                           class="w-full py-2.5 px-4 text-gray-800 bg-white focus:outline-none" 
                           id="full_name" 
                           name="full_name" 
                           required>
                </div>
            </div>

            <div class="mb-4">
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <div class="flex border border-gray-200 rounded-xl overflow-hidden shadow-sm focus-within:ring-2 focus-within:ring-blue-100 focus-within:border-blue-500 transition">
                    <span class="bg-blue-50 border-r border-gray-200 text-blue-500 px-3 flex items-center">
                        <i data-lucide="at-sign" class="w-5 h-5"></i>
                    </span>
                    <input type="text" 
                           class="w-full py-2.5 px-4 text-gray-800 bg-white focus:outline-none" 
                           id="username" 
                           name="username" 
                           required>
                </div>
            </div>

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <div class="flex border border-gray-200 rounded-xl overflow-hidden shadow-sm focus-within:ring-2 focus-within:ring-blue-100 focus-within:border-blue-500 transition">
                    <span class="bg-blue-50 border-r border-gray-200 text-blue-500 px-3 flex items-center">
                        <i data-lucide="mail" class="w-5 h-5"></i>
                    </span>
                    <input type="email" 
                           class="w-full py-2.5 px-4 text-gray-800 bg-white focus:outline-none" 
                           id="email" 
                           name="email" 
                           required>
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <div class="flex border border-gray-200 rounded-xl overflow-hidden shadow-sm focus-within:ring-2 focus-within:ring-blue-100 focus-within:border-blue-500 transition">
                    <span class="bg-blue-50 border-r border-gray-200 text-blue-500 px-3 flex items-center">
                        <i data-lucide="lock" class="w-5 h-5"></i>
                    </span>
                    <input type="password" 
                           class="w-full py-2.5 px-4 text-gray-800 bg-white focus:outline-none" 
                           id="password" 
                           name="password" 
                           required>
                </div>
                <div class="mt-2 password-requirements">
                    <ul>
                        <li class="text-xs text-gray-500 flex items-center">
                            <i data-lucide="check" class="w-3 h-3 text-green-500 mr-1"></i>
                            At least 6 characters long
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mb-5">
                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <div class="flex border border-gray-200 rounded-xl overflow-hidden shadow-sm focus-within:ring-2 focus-within:ring-blue-100 focus-within:border-blue-500 transition">
                    <span class="bg-blue-50 border-r border-gray-200 text-blue-500 px-3 flex items-center">
                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                    </span>
                    <input type="password" 
                           class="w-full py-2.5 px-4 text-gray-800 bg-white focus:outline-none" 
                           id="confirm_password" 
                           name="confirm_password" 
                           required>
                </div>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white py-3 rounded-xl font-medium flex items-center justify-center gap-2 transition transform hover:-translate-y-0.5 hover:shadow-md">
                <i data-lucide="check" class="w-5 h-5"></i>
                Create Admin Account
            </button>
        </form>
    </div>

    <script>
        // Initialize Lucide icons
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
    </script>
</body>
</html> 