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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/login.css">
    <style>
        .login-page {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 15px;
        }
        
        .login-card {
            background: #ffffff;
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
            border: 1px solid #dee2e6;
        }
        
        .stats-icon {
            width: 64px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            background: #f1f5ff;
            border-radius: 12px;
            color: #0d6efd;
            border: 1px solid #e6effd;
        }
        
        .stats-icon svg {
            stroke: #0d6efd;
        }
        
        .system-title {
            color: #212529;
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }
        
        .text-muted {
            color: #6c757d !important;
            font-size: 0.95rem;
        }
        
        .input-group {
            margin-bottom: 1.25rem;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            overflow: hidden;
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        
        .input-group-text {
            background-color: #f8f9fa;
            border: none;
            border-right: 1px solid #dee2e6;
            color: #0d6efd;
            padding: 0.75rem 1.25rem;
        }
        
        .form-control {
            border: none;
            padding: 0.75rem 1.25rem;
            color: #212529;
            background-color: #ffffff;
            font-size: 0.95rem;
        }
        
        .form-control::placeholder {
            color: #adb5bd;
            font-size: 0.95rem;
        }
        
        .form-control:focus {
            box-shadow: none;
            background-color: #ffffff;
        }
        
        .input-group:focus-within {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
        }
        
        .action-button {
            padding: 0.875rem;
            font-weight: 500;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: #0d6efd;
            border: none;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .action-button:hover {
            background: #0b5ed7;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
        }
        
        .alert {
            background-color: #fff4f4;
            border: 1px solid #ffebeb;
            color: #dc3545;
            border-radius: 10px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="text-center mb-4">
                <div class="stats-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                    </svg>
                </div>
                <h1 class="system-title">Learning Management System</h1>
                <p class="text-muted">Sign in to continue</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </span>
                        <input type="text" 
                               class="form-control" 
                               name="username" 
                               placeholder="Username"
                               required
                               style="background-color: #ffffff; color: #212529;">
                    </div>
                </div>
                <div class="mb-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </span>
                        <input type="password" 
                               class="form-control" 
                               name="password" 
                               placeholder="Password"
                               required
                               style="background-color: #ffffff; color: #212529;">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 action-button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                        <polyline points="10 17 15 12 10 7"></polyline>
                        <line x1="15" y1="12" x2="3" y2="12"></line>
                    </svg>
                    Sign In
                </button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
