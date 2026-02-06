<?php
/**
 * Authentication Middleware
 * Checks if user is authenticated before allowing access to protected resources
 */

// Use consistent session name across entire application
defined('CSRF_SESSION_NAME') || define('CSRF_SESSION_NAME', 'ug_irb_session');

function require_auth() {
    // Start session if not already started with consistent session name
    if (session_status() === PHP_SESSION_NONE) {
        session_name(CSRF_SESSION_NAME);
        session_start();
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['logged_in'])) {
        // User is not authenticated, redirect to login
        header('Location: ../login.php');
        exit;
    }
    
    return true;
}

/**
 * Check if user is authenticated (returns boolean without redirect)
 * @return bool
 */
function is_authenticated() {
    // Start session if not already started with consistent session name
    if (session_status() === PHP_SESSION_NONE) {
        session_name(CSRF_SESSION_NAME);
        session_start();
    }
    
    return isset($_SESSION['user_id']) || isset($_SESSION['logged_in']);
}

/**
 * Get current user ID if authenticated
 * @return int|null
 */
function get_current_user_id() {
    // Start session if not already started with consistent session name
    if (session_status() === PHP_SESSION_NONE) {
        session_name(CSRF_SESSION_NAME);
        session_start();
    }
    
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

/**
 * Require admin role for access
 * @param string $required_role The role required for access
 * @return bool
 */
function require_role($required_role) {
    require_auth();
    
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $required_role) {
        header('Location: ../unauthorized.php');
        exit;
    }
    
    return true;
}
