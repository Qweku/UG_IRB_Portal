<?php

/**
 * Environment Configuration
 * Load environment variables from .env file if available
 */
$env_file = __DIR__ . '/.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if (!getenv($name)) {
                putenv("$name=$value");
            }
        }
    }
}

/**
 * MAINTENANCE MODE CONFIGURATION
 * 
 * To enable maintenance mode:
 * 1. Set MAINTENANCE_MODE=true in the .env file
 * 2. All non-admin requests will be redirected to the maintenance page
 * 
 * The maintenance page (admin/pages/maintenance.php) remains accessible
 * even when maintenance mode is enabled.
 */
$maintenance_mode = getenv('MAINTENANCE_MODE') === 'true';

// Get the current request URI to check if we're on the maintenance page
$current_uri = $_SERVER['REQUEST_URI'] ?? '';
$maintenance_page = '/maintenance'; // Path to the maintenance page

// If maintenance mode is enabled and we're NOT on the maintenance page, redirect
if ($maintenance_mode) {
    // Allow access to the maintenance page itself
    if (strpos($current_uri, $maintenance_page) !== false) {
        // We're on the maintenance page, allow access
        return;
    }

    // Destroy all sessions to log out users
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = array();
        session_destroy();
    }

    // Perform the redirect
    header('Location: ' . $maintenance_page);
    exit;
}

/**
 * Error Handling Configuration
 */
if (getenv('APP_ENV') === 'production') {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

// Base path configuration
define('BASE_URL', getenv('BASE_URL') ?: 'http://localhost/UG_IRB_Portal/');
define('BASE_PATH', dirname(__FILE__));

// Database configuration - use environment variables or fallbacks
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'ug_irb_portal');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Email configuration - use environment variables
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_USER', getenv('SMTP_USER') ?: 'your-email@gmail.com');
define('SMTP_PASS', getenv('SMTP_PASS') ?: '');
define('SMTP_ENCRYPTION', getenv('SMTP_ENCRYPTION') ?: 'tls');
define('FROM_EMAIL', getenv('FROM_EMAIL') ?: 'no-reply@ug.edu.gh');
define('FROM_NAME', getenv('FROM_NAME') ?: 'UG IRB Portal');

// Include required files
require_once 'includes/config/database.php';
require_once 'includes/functions/helpers.php';
