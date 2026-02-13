<?php
/**
 * Authentication Middleware
 * Checks if user is authenticated before allowing access to protected resources
 * Includes session token validation for single-session-per-user security
 */

// Use consistent session name across entire application
defined('CSRF_SESSION_NAME') || define('CSRF_SESSION_NAME', 'ug_irb_session');

// DEBUG: Log at entry point
error_log("=== AUTH_CHECK.PHP ENTRY ===");
error_log("Session status at entry: " . session_status());
error_log("Current session_name: " . session_name());

// Include database connection
require_once __DIR__ . '/../../includes/config/database.php';

// Start session if not already started with consistent session name
$session_started = false;
if (session_status() === PHP_SESSION_NONE) {
    session_name(CSRF_SESSION_NAME);
    $session_started = session_start();
    error_log("SESSION START RESULT: " . ($session_started ? 'SUCCESS' : 'FAILED'));
    error_log("SESSION STATUS: " . session_status());
    error_log("SESSION ID: " . session_id());
} else {
    error_log("Session already active - checking if session_name matches");
    error_log("Active session_name: " . session_name());
    error_log("Expected session_name: " . CSRF_SESSION_NAME);
    if (session_name() !== CSRF_SESSION_NAME) {
        error_log("WARNING: Session name mismatch! Session was started with different name.");
    }
}

$db = new Database();
$conn = $db->connect();

/**
 * Check if request is AJAX (XMLHttpRequest)
 */
function is_ajax_request(): bool {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

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
    $session_started = false;
    if (session_status() === PHP_SESSION_NONE) {
        session_name(CSRF_SESSION_NAME);
        $session_started = session_start();
        error_log("SESSION START RESULT: " . ($session_started ? 'SUCCESS' : 'FAILED'));
        error_log("SESSION STATUS: " . session_status());
        error_log("SESSION ID: " . session_id());
    } else {
        error_log("SESSION ALREADY STARTED - STATUS: " . session_status());
        error_log("SESSION ID: " . session_id());
    }
    
    // DEBUG: Log session info
    error_log("=== AUTH DEBUG ===");
    error_log("Session logged_in: " . (isset($_SESSION['logged_in']) ? $_SESSION['logged_in'] : 'NOT SET'));
    error_log("Session user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET'));
    error_log("Session role: " . (isset($_SESSION['role']) ? $_SESSION['role'] : 'NOT SET'));
    error_log("Is AJAX: " . (is_ajax_request() ? 'YES' : 'NO'));
    
    // Check if user is logged in with session validation
    if (!is_authenticated()) {
        error_log("AUTH FAILED - Not authenticated");
        // User is not authenticated
        if (is_ajax_request()) {
            // Return JSON for AJAX requests
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error', 
                'message' => 'Unauthorized access. Please log in.',
                'redirect' => '/login'
            ]);
            exit;
        }
        // Redirect for regular page requests
        header('Location: /login');
        exit;
    }
    
    error_log("AUTH SUCCESS");
    return true;
}

/**
 * Check if user is authenticated (returns boolean without redirect)
 * Note: This function should be called AFTER session is started
 * @return bool
 */
function is_authenticated() {
    // Basic session check first
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        return false;
    }
    
    // Basic check for required session variables
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Try to validate session token against database
    // If DB connection fails, fall back to basic session check
    global $conn;
    if ($conn) {
        try {
            return validate_admin_session_token($conn);
        } catch (Exception $e) {
            error_log("Database error in is_authenticated: " . $e->getMessage());
            return true; // Fallback to session-based auth
        }
    }
    
    // If no DB connection, trust the session
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
        if (is_ajax_request()) {
            header('HTTP/1.1 403 Forbidden');
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error', 
                'message' => 'Access denied. Insufficient permissions.'
            ]);
            exit;
        }
        header('HTTP/1.0 403 Forbidden');
        include __DIR__ . '/../../admin/403.php';
        exit;
    }
    
    return true;
}
