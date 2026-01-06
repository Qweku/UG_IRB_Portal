<?php
// Session will be started in individual pages with appropriate names

// Configure session settings for better persistence
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
ini_set('session.gc_maxlifetime', 1800); // 30 minutes
ini_set('session.cookie_lifetime', 1800); // 30 minutes

// Set session save path if needed
if (!is_writable(session_save_path())) {
    // Try to create a custom session directory
    $custom_session_path = __DIR__ . '/sessions';
    if (!is_dir($custom_session_path)) {
        mkdir($custom_session_path, 0755, true);
    }
    if (is_writable($custom_session_path)) {
        session_save_path($custom_session_path);
    }
}

// Base path configuration
define('BASE_URL', 'http://localhost/UG_IRB_Portal/');
define('BASE_PATH', dirname(__FILE__));

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'ug_irb_portal');
define('DB_USER', 'root');
define('DB_PASS', '');

// Include required files
require_once 'includes/config/database.php';
require_once 'includes/functions/helpers.php';
?>