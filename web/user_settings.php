<?php
session_start();
require_once '../config.php';
include 'includes/header.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Fetch dropdown data
$divisions = $conn->query("SELECT Id AS id, name FROM division ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$sections = $conn->query("SELECT Id AS id, name FROM section ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$departments = $conn->query("SELECT id AS id, Name AS name FROM department ORDER BY Name")->fetch_all(MYSQLI_ASSOC);

// Fetch current user data
$stmt = $conn->prepare("SELECT username, full_name, email, division, section, department FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $division = !empty($_POST['division']) ? (int)$_POST['division'] : null;
    $section = !empty($_POST['section']) ? (int)$_POST['section'] : null;
    $department = !empty($_POST['department']) ? (int)$_POST['department'] : null;
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($full_name) || empty($email)) {
        $error = "Username, full name, and email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username=?, full_name=?, email=?, password=?, division=?, section=?, department=? WHERE id=?");
            $stmt->bind_param("ssssiiii", $username, $full_name, $email, $hashed, $division, $section, $department, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username=?, full_name=?, email=?, division=?, section=?, department=? WHERE id=?");
            $stmt->bind_param("sssiiii", $username, $full_name, $email, $division, $section, $department, $user_id);
        }

        if ($stmt->execute()) {
            $success = "Your information has been updated successfully!";
            // Refresh data
            $stmt = $conn->prepare("SELECT username, full_name, email, division, section, department FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
        } else {
            $error = "Error updating settings: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Settings</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-blue-50">
    <?php include 'includes/sidebar.php'; ?>

    <div class="p-8 sm:ml-72">
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl p-6 mb-6 flex items-center">
            <i data-lucide="activity" class="h-8 w-8 text-white mr-4"></i>
            <div>
                <h1 class="text-2xl font-bold text-white">Settings</h1>
                <p class="text-blue-100">Manage your account settings</p>
            </div>
        </div>
        <div class="mx-auto bg-white shadow-md rounded-xl border border-gray-100 p-8">
            <?php if ($error): ?>
                <div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php elseif ($success): ?>
                <div class="mb-4 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <input type="password" name="password" placeholder="Leave blank to keep current password"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="division" class="block text-sm font-medium text-gray-700 mb-1">Division</label>
                        <select id="division" name="division"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition">
                            <option value="">-- Select Division --</option>
                            <?php foreach ($divisions as $d): ?>
                                <option value="<?php echo (int)$d['id']; ?>" 
                                    <?php echo ($user['division'] == $d['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($d['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="section" class="block text-sm font-medium text-gray-700 mb-1">Section</label>
                        <select id="section" name="section"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition">
                            <option value="">-- Select Section --</option>
                            <?php foreach ($sections as $s): ?>
                                <option value="<?php echo (int)$s['id']; ?>" 
                                    <?php echo ($user['section'] == $s['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($s['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                        <select id="department" name="department"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition">
                            <option value="">-- Select Department --</option>
                            <?php foreach ($departments as $dep): ?>
                                <option value="<?php echo (int)$dep['id']; ?>" 
                                    <?php echo ($user['department'] == $dep['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dep['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-200">
                    <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
