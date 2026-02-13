<?php

/**
 * NMIMR Application Handler (DEPRECATED)
 * 
 * @deprecated This handler is now a wrapper for the unified SubmissionEngine.
 *             The actual submission logic has been moved to includes/submission_engine.php
 *             and the NmimrHandler class in includes/submission_engine/handlers/NmimrHandler.php.
 * 
 * This file handles form submission from nmimr_application.php
 * Schema: nmimr_applications, nmimr_co_investigators, nmimr_application_documents, nmimr_declarations
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
error_log("NMIMR Handler Session Debug: session_name=" . session_name() .
    ", session_id=" . session_id() .
    ", logged_in=" . (isset($_SESSION['logged_in']) ? $_SESSION['logged_in'] : 'NOT SET') .
    ", user_id=" . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET'));

// Include required files
require_once '../../config.php';
require_once '../../includes/config/database.php';
require_once '../../includes/functions/helpers.php';

// Include the unified submission engine
require_once '../../includes/submission_engine.php';

// Set JSON response header for AJAX requests
header('Content-Type: application/json');

/**
 * Validate CSRF token
 * @param string $csrfToken Token to validate
 * @return bool True if valid
 */
function validateCsrfToken($csrfToken)
{
    $sessionCsrfToken = $_SESSION['csrf_token'] ?? '';
    return !empty($csrfToken) && !empty($sessionCsrfToken) && hash_equals($sessionCsrfToken, $csrfToken);
}

/**
 * Validate required authentication
 * @return array|null Returns error array if not authenticated, null if OK
 */
function validateAuthentication()
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

    return null; // No error
}

/**
 * Handle NMIMR application submission using the unified SubmissionEngine
 * @return array Result with success status and message
 */
function handleNmimrSubmission()
{
    // Validate authentication
    $authError = validateAuthentication();
    if ($authError !== null) {
        return $authError;
    }

    // Verify CSRF token
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrfToken)) {
        return [
            'success' => false,
            'message' => 'Invalid CSRF token. Please refresh the page and try again.'
        ];
    }

    // Delegate to the unified SubmissionEngine
    return SubmissionEngine::submitNmimr($_POST, $_FILES);
}

/**
 * Handle save draft request for NMIMR application using the unified SubmissionEngine
 * @return array Result with success status and message
 */
function handleSaveDraft()
{
    // Validate authentication
    $authError = validateAuthentication();
    if ($authError !== null) {
        return $authError;
    }

    // Verify CSRF token
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrfToken)) {
        return [
            'success' => false,
            'message' => 'Invalid CSRF token. Please refresh the page and try again.'
        ];
    }

    // Mark as draft submission
    $_POST['save_draft'] = '1';

    // Delegate to the unified SubmissionEngine
    return SubmissionEngine::submitNmimr($_POST, $_FILES);
}

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'submit':
            $result = handleNmimrSubmission();
            break;
        case 'save_draft':
            $result = handleSaveDraft();
            break;
        default:
            $result = [
                'success' => false,
                'message' => 'Invalid action specified.'
            ];
    }

    // Return JSON response
    echo json_encode($result);
    exit;
}
