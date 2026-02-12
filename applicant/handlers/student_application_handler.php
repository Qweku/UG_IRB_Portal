<?php

/**
 * Student Application Handler
 * 
 * DEPRECATION NOTICE:
 * This handler is now a wrapper for the unified SubmissionEngine.
 * The main submission logic has been moved to SubmissionEngine::submitStudent().
 * 
 * Original submission logic has been deprecated as of 2024.
 * Please use SubmissionEngine directly for new implementations.
 * 
 * This wrapper maintains backward compatibility with existing code.
 */

// Define consistent session name across entire application
defined('CSRF_SESSION_NAME') || define('CSRF_SESSION_NAME', 'ug_irb_session');

// Set custom session name BEFORE starting session
session_name(CSRF_SESSION_NAME);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug logging for session debugging
error_log("Student Handler Session Debug: session_name=" . session_name() .
    ", session_id=" . session_id() .
    ", logged_in=" . (isset($_SESSION['logged_in']) ? $_SESSION['logged_in'] : 'NOT SET') .
    ", user_id=" . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET'));

// Include required files
require_once '../../config.php';
require_once '../../includes/submission_engine.php';

// Set JSON response header for AJAX requests
header('Content-Type: application/json');

/**
 * Handle the form submission using the unified SubmissionEngine
 * 
 * This function is kept for backward compatibility.
 * It now delegates to SubmissionEngine::submitStudent().
 * 
 * @return array Result with success status and message
 */
function handleSubmission(): array
{
    // Verify applicant is logged in
    if (!is_applicant_logged_in()) {
        return [
            'success' => false,
            'message' => 'You must be logged in to submit an application.',
            'redirect' => '/login'
        ];
    }

    // Get user ID from session
    $userId = $_SESSION['user_id'] ?? 0;

    if ($userId === 0) {
        return [
            'success' => false,
            'message' => 'User session not found. Please log in again.'
        ];
    }

    // Verify CSRF token
    $csrfToken = $_POST['csrf_token'] ?? '';
    $sessionCsrfToken = $_SESSION['csrf_token'] ?? '';

    if (!hash_equals($sessionCsrfToken, $csrfToken)) {
        return [
            'success' => false,
            'message' => 'Invalid CSRF token. Please refresh the page and try again.'
        ];
    }

    // Delegate to SubmissionEngine
    $result = SubmissionEngine::submitStudent($_POST, $_FILES);
    
    return $result;
}

/**
 * Handle save draft request using the unified SubmissionEngine
 * 
 * @return array Result with success status and message
 */
function handleSaveDraft(): array
{
    // Verify applicant is logged in
    $isLoggedIn = is_applicant_logged_in();

    if (!$isLoggedIn) {
        return [
            'success' => false,
            'message' => 'You must be logged in to save a draft.',
            'redirect' => '/login'
        ];
    }

    // Get user ID from session
    $userId = $_SESSION['user_id'] ?? 0;

    if ($userId === 0) {
        return [
            'success' => false,
            'message' => 'User session not found. Please log in again.'
        ];
    }

    // Verify CSRF token
    $csrfToken = $_POST['csrf_token'] ?? '';
    $sessionCsrfToken = $_SESSION['csrf_token'] ?? '';

    $csrfMatch = !empty($csrfToken) && !empty($sessionCsrfToken) && hash_equals($sessionCsrfToken, $csrfToken);

    if (!$csrfMatch) {
        return [
            'success' => false,
            'message' => 'Invalid CSRF token. Please refresh the page and try again.'
        ];
    }

    // Set save_draft flag for SubmissionEngine
    $_POST['save_draft'] = '1';

    // Delegate to SubmissionEngine
    $result = SubmissionEngine::submitStudent($_POST, $_FILES);
    
    return $result;
}

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'submit';

    if ($action === 'save_draft') {
        $result = handleSaveDraft();
    } else {
        $result = handleSubmission();
    }

    echo json_encode($result);
    exit;
}
