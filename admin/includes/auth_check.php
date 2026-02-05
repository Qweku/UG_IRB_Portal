<?php
/**
 * Authentication Middleware
 * Checks if user is authenticated before allowing access to protected resources
 */

function require_auth() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
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
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['user_id']) || isset($_SESSION['logged_in']);
}

/**
 * Get current user ID if authenticated
 * @return int|null
 */
function get_current_user_id() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
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
