<?php
// Initialize session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the current page
$current_page = basename($_SERVER['PHP_SELF']);

// Check if we're in includes directory or main directory
$is_in_includes = strpos($_SERVER['PHP_SELF'], '/includes/') !== false;
$base_path = $is_in_includes ? '..' : '.';
$page_title = isset($page_title) ? $page_title : 'LMS System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    
    <!-- Bootstrap Icons (for backwards compatibility) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>/../assets/css/style.css">
    
    <!-- Custom JavaScript -->
    <script src="<?php echo $base_path; ?>/../assets/js/main.js"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'blue': {
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
                        },
                        'green': {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857'
                        },
                        'red': {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#b91c1c'
                        },
                        'amber': {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309'
                        }
                    },
                    typography: {
                        DEFAULT: {
                            css: {
                                color: '#374151',
                                a: {
                                    color: '#3b82f6',
                                    '&:hover': {
                                        color: '#2563eb',
                                    },
                                },
                                h1: {
                                    color: '#1e40af',
                                },
                                h2: {
                                    color: '#1e40af',
                                },
                                h3: {
                                    color: '#1e3a8a',
                                },
                            },
                        },
                    },
                }
            }
        }
    </script>
</head>
<body class="bg-blue-50">
    <!-- Wrapper for sidebar + content -->
    <div class="flex">
        <!-- Include sidebar -->
        <?php include_once($base_path . '/includes/sidebar.php'); ?>
        
        <!-- Main Content - will be filled by each page -->
        <!-- Each page should wrap content in <div class="p-8 sm:ml-72"> -->
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Lucide icons
            lucide.createIcons();
            
            <?php if (isset($_SESSION['toast'])): ?>
            // Display toast from PHP session
            if (typeof showToast === 'function') {
                showToast('<?php echo addslashes($_SESSION['toast']['message']); ?>', '<?php echo addslashes($_SESSION['toast']['type']); ?>');
            }
            <?php 
            // Clear the toast message from session
            unset($_SESSION['toast']);
            endif; ?>
        });
    </script>
</body>
</html> 