<?php
session_start();
require_once '../config.php';

// Check if admin exists
$stmt = $conn->prepare("SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin'");
$stmt->execute();
$result = $stmt->get_result();
$admin_exists = $result->fetch_assoc()['admin_count'] > 0;

// If no admin exists, redirect to setup page
if (!$admin_exists) {
    header("Location: setup_admin.php");
    exit;
}

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    
    // Change the query to include full_name
    $sql = "SELECT id, username, full_name, password, role FROM users WHERE username = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            // Verify the hashed password
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name']; // Add full_name to session
                $_SESSION['role'] = $user['role'];
                
                header("Location: dashboard.php");
                exit;
            }
        }
        $error = 'Invalid username or password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - Login</title>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link rel="stylesheet" href="./public/css/tailwind.min.css" />
    <!-- <script src="https://cdn.tailwindcss.com"></script> -->
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
</head>
<body class="bg-blue-50 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md px-4">
        <div class="bg-white rounded-2xl p-10 shadow-md border border-gray-200">
            <div class="text-center mb-8">
                <div class="mx-auto mb-8">
                    <img src="http://172.16.131.209/lms_project/assets/asahi_intecc.jpg" alt="Asahi Intecc" class="h-8 w-auto mx-auto">
                </div>
                <h1 class="text-2xl font-semibold text-blue-900 mb-2">Learning Management System</h1>
                <p class="text-blue-600 text-[15px]">Sign in to continue</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-100 text-red-600 rounded-xl p-4 mb-6 flex items-center gap-2 relative" style="display: none;">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                    <button type="button" class="absolute top-4 right-4 text-red-400 hover:text-red-600" onclick="this.parentElement.remove()">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-5">
                    <div class="flex border border-gray-200 rounded-xl overflow-hidden shadow-sm focus-within:ring-2 focus-within:ring-blue-100 focus-within:border-blue-500 transition">
                        <span class="bg-blue-50 border-r border-gray-200 text-blue-500 px-5 flex items-center">
                            <i data-lucide="user" class="w-5 h-5"></i>
                        </span>
                        <input type="text" 
                               class="w-full py-3 px-4 text-gray-800 bg-white focus:outline-none" 
                               name="username" 
                               placeholder="Username"
                               required>
                    </div>
                </div>
                <div class="mb-6">
                    <div class="flex border border-gray-200 rounded-xl overflow-hidden shadow-sm focus-within:ring-2 focus-within:ring-blue-100 focus-within:border-blue-500 transition">
                        <span class="bg-blue-50 border-r border-gray-200 text-blue-500 px-5 flex items-center">
                            <i data-lucide="lock" class="w-5 h-5"></i>
                        </span>
                        <input type="password" 
                               class="w-full py-3 px-4 text-gray-800 bg-white focus:outline-none" 
                               name="password" 
                               placeholder="Password"
                               required>
                    </div>
                </div>
                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white py-3.5 rounded-xl font-medium flex items-center justify-center gap-2 transition transform hover:-translate-y-0.5 hover:shadow-md">
                    <i data-lucide="log-in" class="w-5 h-5"></i>
                    Sign In
                </button>
            </form>
            <div class="text-center mt-6">
                <p class="text-sm text-gray-500 bg-blue-50 p-3 rounded-lg border border-blue-100">
                    <i data-lucide="info" class="w-4 h-4 inline-block mr-1 text-blue-500"></i>
                    If you don't have an account, please contact the IT administrator
                </p>
            </div>
        </div>
    </div>

    <script>
        // Initialize Lucide icons
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
            
            // Initialize toast functionality
            <?php if ($error): ?>
                // Show error toast on login failure
                showToast('<?php echo htmlspecialchars($error); ?>', 'error');
            <?php endif; ?>
        });
        
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
