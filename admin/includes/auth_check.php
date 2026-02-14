<?php
/**
 * Authentication Middleware
 * Checks if user is authenticated before allowing access to protected resources
 * Includes session token validation for single-session-per-user security
 */

// Use consistent session name across entire application
defined('CSRF_SESSION_NAME') || define('CSRF_SESSION_NAME', 'ug_irb_session');

// Include database connection
require_once __DIR__ . '/../../includes/config/database.php';

// DEBUG: Add session diagnostic logging
error_log("[AUTH_CHECK] Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'unknown'));
error_log("[AUTH_CHECK] HTTP_X_REQUESTED_WITH: " . ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? 'not set'));

// Start session with consistent session name
// This must be done BEFORE any output and with the correct session name
session_name(CSRF_SESSION_NAME);

// Check if session is already active
if (session_status() === PHP_SESSION_NONE) {
    // Configure session cookie for better AJAX compatibility
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
} elseif (session_status() === PHP_SESSION_ACTIVE) {
    // Session already started - ensure we're using the correct session name
    if (session_name() !== CSRF_SESSION_NAME) {
        // Session was started with a different name, try to preserve it
        $previous_session_data = $_SESSION;
        session_write_close();
        session_name(CSRF_SESSION_NAME);
        session_start();
        $_SESSION = array_merge($_SESSION, $previous_session_data);
    }
}

error_log("[AUTH_CHECK] session_status after start: " . session_status());
error_log("[AUTH_CHECK] session_id: " . session_id());
error_log("[AUTH_CHECK] session_name: " . session_name());
error_log("[AUTH_CHECK] logged_in: " . (isset($_SESSION['logged_in']) ? $_SESSION['logged_in'] : 'NOT SET'));
error_log("[AUTH_CHECK] user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET'));
error_log("[AUTH_CHECK] role: " . (isset($_SESSION['role']) ? $_SESSION['role'] : 'NOT SET'));

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
    // Ensure session is started with consistent session name (idempotent)
    // Only call session_name() if session hasn't been started yet
    if (session_status() === PHP_SESSION_NONE) {
        session_name(CSRF_SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start();
    } elseif (session_status() === PHP_SESSION_ACTIVE && session_name() !== CSRF_SESSION_NAME) {
        $previous_session_data = $_SESSION;
        session_write_close();
        session_name(CSRF_SESSION_NAME);
        session_start();
        $_SESSION = array_merge($_SESSION, $previous_session_data);
    }
    
    // Check if user is logged in with session validation
    if (!is_authenticated()) {
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
    // Ensure session is started with consistent session name (idempotent)
    // Only call session_name() if session hasn't been started yet
    if (session_status() === PHP_SESSION_NONE) {
        session_name(CSRF_SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start();
    } elseif (session_status() === PHP_SESSION_ACTIVE && session_name() !== CSRF_SESSION_NAME) {
        $previous_session_data = $_SESSION;
        session_write_close();
        session_name(CSRF_SESSION_NAME);
        session_start();
        $_SESSION = array_merge($_SESSION, $previous_session_data);
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
