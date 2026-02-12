<?php

/**
 * Non-NMIMR Application Handler (DEPRECATED)
 * 
 * This handler has been deprecated and is now a wrapper for the unified SubmissionEngine.
 * 
 * @deprecated Use SubmissionEngine::submitNonNmimr() directly instead of this handler.
 * @see includes/submission_engine.php
 * 
 * Original Description:
 * Handles form submission from non_nmimr_application.php
 * Validates, sanitizes, and saves data to the database
 * Schema: non_nmimr_applications, non_nmimr_co_investigators, non_nmimr_application_documents, non_nmimr_declarations
 */

// DEPRECATION NOTICE:
// This file is kept for backward compatibility but delegates all processing to SubmissionEngine.
// New code should use SubmissionEngine::submitNonNmimr($_POST, $_FILES) directly.

// Define consistent session name across entire application
defined('CSRF_SESSION_NAME') || define('CSRF_SESSION_NAME', 'ug_irb_session');

// Set custom session name BEFORE starting session
session_name(CSRF_SESSION_NAME);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include helper functions
require_once __DIR__ . '/../../includes/functions/helpers.php';

// Debug logging for session debugging
error_log("Non-NMIMR Handler Session Debug: session_name=" . session_name() .
    ", session_id=" . session_id() .
    ", logged_in=" . (isset($_SESSION['logged_in']) ? $_SESSION['logged_in'] : 'NOT SET') .
    ", user_id=" . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET'));

/**
 * Validate CSRF token
 * @param string $csrfToken Token from form
 * @return bool True if valid
 */
function validateCsrfToken($csrfToken)
{
    $sessionCsrfToken = $_SESSION['csrf_token'] ?? '';
    return !empty($csrfToken) && !empty($sessionCsrfToken) && hash_equals($sessionCsrfToken, $csrfToken);
}

/**
 * Validate required fields for Non-NMIMR application submission
 * @param array $data Form data
 * @return array Array of missing required fields
 */
function validateNonNmimrRequiredFields($data)
{
    $required = [
        // Step 1: Protocol Information
        'protocol_number',
        'version_number',
        'study_title',

        // Step 2: Project Info (PI fields)
        'pi_name',
        'pi_institution',
        'pi_address',
        'pi_phone_number',
        'pi_email',
        'collaborating_institutions',
        'duration',

        // Step 3: Research Content
        'abstract',
        'aims',
        'methodology',
        'ethical_considerations',

        // Step 4: Signatures
        'pi_signature',
        'pi_date'
    ];

    $missing = [];
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $missing[] = $field;
        }
    }

    return $missing;
}

/**
 * Validate email format
 * @param string $email Email to validate
 * @return bool True if valid
 */
function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Sanitize string input
 * @param string $input Input string
 * @return string Sanitized string
 */
function sanitizeString($input)
{
    if ($input === null) {
        return '';
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Handle Non-NMIMR application submission using the unified SubmissionEngine
 * @return array Result with success status and message
 */
function handleNonNmimrSubmission()
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

    if (!validateCsrfToken($csrfToken)) {
        return [
            'success' => false,
            'message' => 'Invalid CSRF token. Please refresh the page and try again.'
        ];
    }

    // Validate required fields before submission
    $missingFields = validateNonNmimrRequiredFields($_POST);
    if (!empty($missingFields)) {
        return [
            'success' => false,
            'message' => 'Please fill in all required fields.',
            'errors' => ['missing_fields' => $missingFields]
        ];
    }

    // Validate version number format
    $versionNumber = $_POST['version_number'] ?? '';
    if (!empty($versionNumber) && !preg_match('/^\d+(\.\d+)?$/', $versionNumber)) {
        return [
            'success' => false,
            'message' => 'Invalid version number format. Use format like 1.0 or 2.1'
        ];
    }

    // Validate PI email
    $piEmail = $_POST['pi_email'] ?? '';
    if (!empty($piEmail) && !isValidEmail($piEmail)) {
        return [
            'success' => false,
            'message' => 'Invalid email address for PI'
        ];
    }

    // Validate Co-PI email if provided
    $coPiEmail = $_POST['co_pi_email'] ?? '';
    if (!empty($coPiEmail) && !isValidEmail($coPiEmail)) {
        return [
            'success' => false,
            'message' => 'Invalid email address for Co-PI'
        ];
    }

    // Validate date format
    $datesToValidate = ['pi_date', 'co_pi_date'];
    foreach ($datesToValidate as $dateField) {
        $dateValue = $_POST[$dateField] ?? '';
        if (!empty($dateValue)) {
            $timestamp = strtotime($dateValue);
            if ($timestamp === false) {
                return [
                    'success' => false,
                    'message' => 'Invalid date format for ' . str_replace('_', ' ', $dateField)
                ];
            }
        }
    }

    // Include the submission engine and delegate to it
    require_once '../../includes/submission_engine.php';

    // Delegate to the unified submission engine
    return SubmissionEngine::submitNonNmimr($_POST, $_FILES);
}

/**
 * Handle save draft request for Non-NMIMR application using the unified SubmissionEngine
 * @return array Result with success status and message
 */
function handleNonNmimrSaveDraft()
{
    // Verify applicant is logged in
    $isLoggedIn = is_applicant_logged_in();

    if (!$isLoggedIn) {
        // Debug logging for authentication failure
        error_log("Non-NMIMR Authentication Debug: logged_in=" . (isset($_SESSION['logged_in']) ? $_SESSION['logged_in'] : 'NOT SET') .
            ", user_id=" . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET') .
            ", role=" . (isset($_SESSION['role']) ? $_SESSION['role'] : 'NOT SET'));

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

    if (!validateCsrfToken($csrfToken)) {
        return [
            'success' => false,
            'message' => 'Invalid CSRF token. Please refresh the page and try again.'
        ];
    }

    // Mark as draft save and delegate to the unified submission engine
    $_POST['save_draft'] = '1';

    // Include the submission engine and delegate to it
    require_once '../../includes/submission_engine.php';

    // Delegate to the unified submission engine (it handles draft mode internally)
    return SubmissionEngine::submitNonNmimr($_POST, $_FILES);
}

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Set JSON response header for AJAX requests
    header('Content-Type: application/json');

    switch ($action) {
        case 'submit':
            $result = handleNonNmimrSubmission();
            break;
        case 'save_draft':
            $result = handleNonNmimrSaveDraft();
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
