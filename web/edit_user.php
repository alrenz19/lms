<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$user_id = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $full_name = $conn->real_escape_string($_POST['full_name']); // Add full_name
    $email = $conn->real_escape_string($_POST['email']);
    $role = $conn->real_escape_string($_POST['role']);
    
    $sql = "UPDATE users SET username = ?, full_name = ?, email = ?, role = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $username, $full_name, $email, $role, $user_id);
    $stmt->execute();
    
    if (!empty($_POST['password'])) {
        $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);
        $stmt->execute();
    }
    
    header("Location: manage_users.php?success=updated");
    exit;
}

$result = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - LMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-blue-50">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="p-8 sm:ml-72">
        <div class="container mx-auto">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                        <i data-lucide="user-cog" class="w-5 h-5 text-blue-600"></i> Edit User
                    </h2>
                    <a href="manage_users.php" class="px-4 py-2 border border-blue-500 text-blue-600 rounded-lg hover:bg-blue-50 transition flex items-center gap-2 text-sm font-medium">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Users
                    </a>
                </div>

                <form method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="user" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" 
                                       class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition" 
                                       id="username" 
                                       name="username" 
                                       value="<?php echo htmlspecialchars($user['username']); ?>" 
                                       required>
                            </div>
                        </div>
                        <div>
                            <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="id-card" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" 
                                       class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition" 
                                       id="full_name" 
                                       name="full_name" 
                                       value="<?php echo htmlspecialchars($user['full_name']); ?>" 
                                       required>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="mail" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="email" 
                                       class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" 
                                       required>
                            </div>
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="key" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="password" 
                                       class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition" 
                                       id="password" 
                                       name="password"
                                       placeholder="Leave blank to keep current password">
                            </div>
                        </div>
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="shield" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <select class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition" 
                                        id="role" 
                                        name="role" 
                                        required>
                                    <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                                    <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <a href="manage_users.php" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition text-sm font-medium flex items-center">
                            <i data-lucide="x" class="w-4 h-4 mr-1"></i> Cancel
                        </a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center gap-2 text-sm font-medium">
                            <i data-lucide="save" class="w-4 h-4"></i> Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>
</body>
</html>
