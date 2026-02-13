<?php
require_once '../includes/auth_check.php';
require_once '../../includes/functions/helpers.php';
require_once '../../includes/functions/notification_functions.php';

header('Content-Type: application/json');

// Use the centralized role check from auth_check.php
require_role('admin');

/**
 * Log activity function
 */
function logActivity($message) {
    error_log("ACTIVITY: " . date('Y-m-d H:i:s') . " - " . $message);
}

try {
    $db = new Database();
    $conn = $db->connect();
    
    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    }
    
    // Validate input
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
        exit;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $applicationId = $data['application_id'] ?? null;
    $reviewers = $data['reviewers'] ?? [];
    $dueDate = $data['due_date'] ?? null;
    $notes = $data['notes'] ?? '';
    
    if (empty($applicationId) || empty($reviewers)) {
        echo json_encode(['status' => 'error', 'message' => 'Application ID and at least one reviewer are required']);
        exit;
    }
    
    // Verify application exists
    $stmt = $conn->prepare("SELECT id, status FROM applications WHERE id = ?");
    $stmt->execute([$applicationId]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$application) {
        echo json_encode(['status' => 'error', 'message' => 'Application not found']);
        exit;
    }
    
    $conn->beginTransaction();
    
    // Assign each reviewer
    $assignedCount = 0;
    foreach ($reviewers as $reviewerId) {
        // Check if already assigned
        $stmt = $conn->prepare("
            SELECT id FROM application_reviews 
            WHERE application_id = ? AND reviewer_id = ?
        ");
        $stmt->execute([$applicationId, $reviewerId]);
        
        if (!$stmt->fetch()) {
            $stmt = $conn->prepare("
                INSERT INTO application_reviews (application_id, reviewer_id, review_status, review_deadline, notes, assigned_by, created_at)
                VALUES (?, ?, 'assigned', ?, ?, ?, NOW())
            ");
            $stmt->execute([$applicationId, $reviewerId, $dueDate, $notes, $_SESSION['user_id']]);
            $assignedCount++;
        }
    }
    
    // Update application status
    $newStatus = $assignedCount > 0 ? 'under_review' : $application['status'];
    $stmt = $conn->prepare("UPDATE applications SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$newStatus, $applicationId]);
    
    $conn->commit();
    
    // Create notifications for assigned reviewers
    $assignedReviewerNames = [];
    foreach ($reviewers as $reviewerId) {
        // Get reviewer name
        $stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
        $stmt->execute([$reviewerId]);
        $reviewer = $stmt->fetch(PDO::FETCH_ASSOC);
        $reviewerName = $reviewer['full_name'] ?? 'Unknown Reviewer';
        $assignedReviewerNames[] = $reviewerName;
        
        // Create notification for reviewer
        createReviewerAssignmentNotification(
            $reviewerId,
            $applicationId,
            $application['study_title'] ?? 'Unknown Study',
            $_SESSION['full_name'] ?? 'Admin'
        );
    }
    
    // Create notification for admins about the assignment
    $reviewerNamesStr = implode(', ', $assignedReviewerNames);
    createAdminAssignmentNotification(
        $applicationId,
        $application['study_title'] ?? 'Unknown Study',
        $reviewerNamesStr,
        $_SESSION['user_id'] ?? 0
    );
    
    // Create notification for the applicant that their application is under review
    // First, get the applicant_id from the application record
    $stmt = $conn->prepare("SELECT user_id, study_title FROM applications WHERE id = ?");
    $stmt->execute([$applicationId]);
    $applicationDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($applicationDetails && !empty($applicationDetails['user_id'])) {
        createApplicationUnderReviewNotification(
            $applicationDetails['user_id'],
            $applicationId,
            $applicationDetails['study_title'] ?? 'Unknown Study'
        );
    }
    
    // Log activity
    logActivity("Assigned $assignedCount reviewer(s) to application ID: $applicationId");
    
    echo json_encode([
        'status' => 'success',
        'message' => "Successfully assigned $assignedCount reviewer(s)",
        'assigned_count' => $assignedCount
    ]);
    
} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log(__FILE__ . " - Database error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
