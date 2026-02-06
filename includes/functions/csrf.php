<?php
/**
 * Session-based CSRF Protection
 * Uses session storage instead of cookies to avoid header issues
 */

function csrf_token() {
    // Generate a new token if none exists in session
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function csrf_validate() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        $postToken = $_POST['csrf_token'] ?? '';
        
        // DEBUG: Log validation details
        error_log("[CSRF VALIDATE] Session token exists: " . (!empty($sessionToken) ? 'YES' : 'NO'));
        error_log("[CSRF VALIDATE] POST token exists: " . (!empty($postToken) ? 'YES' : 'NO'));
        
        // Token is not cleared - it persists for the session
        if (!$sessionToken || !$postToken) {
            error_log("[CSRF VALIDATE] FAILED: Missing token");
            return false;
        }
        
        $match = hash_equals($sessionToken, $postToken);
        error_log("[CSRF VALIDATE] Tokens match: " . ($match ? 'YES' : 'NO'));
        return $match;
    }
    return true;
}
