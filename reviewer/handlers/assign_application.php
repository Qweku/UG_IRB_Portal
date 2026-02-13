<?php

/**
 * Assign Application to Reviewer Handler
 * Handles AJAX requests to assign an application to the current reviewer
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once __DIR__ . '/../../includes/functions/helpers.php';
require_once __DIR__ . '/../../includes/functions/csrf.php';

// Set response headers
header('Content-Type: application/json');

// Check if user is logged in as reviewer
if (!is_reviewer_logged_in()) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Validate CSRF token
$submittedToken = $_POST['csrf_token'] ?? '';
if (!csrf_validate($submittedToken)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid CSRF token'
    ]);
    exit;
}

// Get application ID
$applicationId = $_POST['application_id'] ?? 0;

if (!$applicationId) {
    echo json_encode([
        'success' => false,
        'message' => 'Application ID is required'
    ]);
    exit;
}

// Get reviewer ID
$reviewerId = $_SESSION['user_id'] ?? 0;

if (!$reviewerId) {
    echo json_encode([
        'success' => false,
        'message' => 'Reviewer ID not found'
    ]);
    exit;
}

// Check if application exists
$application = getApplicationForReview($applicationId);
if (!$application) {
    echo json_encode([
        'success' => false,
        'message' => 'Application not found'
    ]);
    exit;
}

// Check if already assigned to this reviewer
$existingReview = getReviewDetails($applicationId, $reviewerId);
if ($existingReview) {
    echo json_encode([
        'success' => false,
        'message' => 'This application is already assigned to you'
    ]);
    exit;
}

// Assign application to reviewer
if (assignApplicationToReviewer($applicationId, $reviewerId)) {
    // Log the assignment
    error_log("Application {$applicationId} assigned to reviewer {$reviewerId}");
    
    echo json_encode([
        'success' => true,
        'message' => 'Application assigned successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to assign application'
    ]);
}
