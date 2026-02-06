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
        
        // Clear the token after validation to prevent reuse
        $_SESSION['csrf_token'] = '';
        
        if (!$sessionToken || !$postToken) {
            return false;
        }
        
        return hash_equals($sessionToken, $postToken);
    }
    return true;
}
