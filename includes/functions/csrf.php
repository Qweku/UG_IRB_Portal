<?php
/**
 * CSRF Protection Functions
 */

/**
 * Generate a CSRF token and store in session
 * @return string The generated CSRF token
 */
function csrf_generate_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate a CSRF token against the session token
 * @param string $token The token to validate
 * @return bool True if valid, false otherwise
 */
function csrf_validate_token($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get current CSRF token without generating a new one
 * @return string|null The current CSRF token or null if not set
 */
function csrf_get_token() {
    return isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : null;
}

/**
 * Validate CSRF token from POST request
 * Should be called at the beginning of POST processing
 * @return bool True if valid, false if invalid
 */
function csrf_validate_post() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !csrf_validate_token($_POST['csrf_token'])) {
            http_response_code(403);
            die('CSRF validation failed');
        }
    }
    return true;
}

/**
 * Validate CSRF token from AJAX request (expects token in headers)
 * @return bool True if valid, false if invalid
 */
function csrf_validate_ajax() {
    $headers = getallheaders();
    $token = isset($headers['X-CSRF-Token']) ? $headers['X-CSRF-Token'] : null;
    
    if (empty($token)) {
        $token = isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : null;
    }
    
    if (empty($token) || !csrf_validate_token($token)) {
        http_response_code(403);
        die('CSRF validation failed');
    }
    
    return true;
}

/**
 * Generate HTML hidden input field for CSRF token
 * @return string HTML hidden input element
 */
function csrf_token_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_generate_token(), ENT_QUOTES, 'UTF-8') . '">';
}
