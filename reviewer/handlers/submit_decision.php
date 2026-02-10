<?php

/**
 * Submit Review Decision Handler
 * Handles AJAX requests to submit review decisions (approve, reject, request changes)
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

// Get input data
$applicationId = $_POST['application_id'] ?? 0;
$decision = $_POST['decision'] ?? '';
$decisionNotes = $_POST['decision_notes'] ?? '';

// Validate input
if (!$applicationId) {
    echo json_encode([
        'success' => false,
        'message' => 'Application ID is required'
    ]);
    exit;
}

if (!$decision) {
    echo json_encode([
        'success' => false,
        'message' => 'Decision is required'
    ]);
    exit;
}

if (!in_array($decision, ['approved', 'rejected', 'changes_requested'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid decision type'
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

// Get review details
$reviewDetails = getReviewDetails($applicationId, $reviewerId);

if (!$reviewDetails) {
    // Try to find any review for this application
    $db = new Database();
    $conn = $db->connect();
    
    if ($conn) {
        try {
            $stmt = $conn->prepare("SELECT id FROM application_reviews WHERE application_id = ?");
            $stmt->execute([$applicationId]);
            $review = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($review) {
                $reviewId = $review['id'];
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No review found for this application'
                ]);
                exit;
            }
        } catch (PDOException $e) {
            error_log('Error fetching review: ' . $e->getMessage());
        }
    }
} else {
    $reviewId = $reviewDetails['id'];
}

// Prepare decision data
$decisionData = [
    'review_id' => $reviewId,
    'application_id' => $applicationId,
    'decision' => $decision,
    'decision_notes' => $decisionNotes
];

// Submit the decision
if (submitReviewDecision($decisionData)) {
    // Send notification to applicant
    sendReviewNotification($applicationId, $decision, $decisionNotes);
    
    // Log the decision
    $logMessage = 'Review decision submitted for application ' . $applicationId . ': ' . $decision . ' by reviewer ' . $reviewerId;
    error_log($logMessage);
    
    echo json_encode([
        'success' => true,
        'message' => 'Decision submitted successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to submit decision. Please try again.'
    ]);
}
