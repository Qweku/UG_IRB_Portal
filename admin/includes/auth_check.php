<?php
/**
 * Authentication Middleware
 * Checks if user is authenticated before allowing access to protected resources
 * Includes session token validation for single-session-per-user security
 */

// Use consistent session name across entire application
defined('CSRF_SESSION_NAME') || define('CSRF_SESSION_NAME', 'ug_irb_session');

// Include database connection
require_once __DIR__ . '/../../../includes/config/database.php';

$db = new Database();
$conn = $db->connect();

/**
 * Validates session token against database
 * @param PDO $conn Database connection
 * @return bool
 */
function validate_admin_session_token($conn): bool {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_token'])) {
        return false;
    }
    
    try {
        $stmt = $conn->prepare("SELECT session_token, session_expires_at FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || empty($user['session_token'])) {
            return false;
        }
        
        // Check if token matches
        if ($user['session_token'] !== $_SESSION['session_token']) {
            return false;
        }
        
        // Check if expired
        if ($user['session_expires_at'] && strtotime($user['session_expires_at']) < time()) {
            return false;
        }
        
        return true;
    } catch (Exception $e) {
        // Log error but don't fail auth for DB errors
        error_log("Session validation error: " . $e->getMessage());
        return true; // Fallback to basic auth on DB error
    }
}

function require_auth() {
    // Start session if not already started with consistent session name
    if (session_status() === PHP_SESSION_NONE) {
        session_name(CSRF_SESSION_NAME);
        session_start();
    }
    
    // Check if user is logged in with session validation
    if (!is_authenticated()) {
        // User is not authenticated, redirect to login
        header('Location: /login.php');
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
    
    // Basic session check
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        return false;
    }
    
    // Validate session token against database
    global $conn;
    if ($conn) {
        return validate_admin_session_token($conn);
    }
    
    return true;
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
    
    if (!isset($_SESSION['role']) || ($_SESSION['role'] !== $required_role && $_SESSION['role'] !== 'super_admin')) {
        header('Location: ../unauthorized.php');
        exit;
    }
    
    return true;
}
