<?php
/**
 * Autoload file for LMS project
 * Include this at the beginning of each PHP file to load common dependencies
 */

// Initialize session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if we're in includes directory or main directory
$is_in_includes = strpos($_SERVER['PHP_SELF'], '/includes/') !== false;
$base_path = $is_in_includes ? '..' : '.';

// Define common paths
define('BASE_PATH', $base_path);
define('COMPONENTS_PATH', $base_path . '/components');
define('INCLUDES_PATH', $base_path . '/includes');
define('ASSETS_PATH', $base_path . '/../assets');

// Common includes
require_once $base_path . '/../config.php';

// Include common components
include_once COMPONENTS_PATH . '/dashboard_card.php';

// Function to include the header
function include_header($title = 'LMS System') {
    global $base_path;
    include_once $base_path . '/includes/header.php';
}

// Function to get the current page name
function get_current_page() {
    return basename($_SERVER['PHP_SELF']);
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Function to redirect with alert message
function redirect_with_message($url, $message, $type = 'success') {
    $_SESSION['alert_message'] = $message;
    $_SESSION['alert_type'] = $type;
    header("Location: $url");
    exit;
}

// Function to display alert messages
function display_alert() {
    if (isset($_SESSION['alert_message'])) {
        $type = $_SESSION['alert_type'] ?? 'success';
        $message = $_SESSION['alert_message'];
        
        // Clear the session variables
        unset($_SESSION['alert_message']);
        unset($_SESSION['alert_type']);
        
        // Determine classes based on alert type
        $bg_class = $type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
        $icon = $type === 'success' ? 'check-circle' : 'alert-circle';
        
        echo <<<HTML
        <div class="mb-4 p-4 rounded-lg {$bg_class} flex items-start">
            <i data-lucide="{$icon}" class="w-5 h-5 mr-3 mt-0.5"></i>
            <div>{$message}</div>
        </div>
        HTML;
    }
} 