<?php
require_once '../includes/auth_check.php';
require_once '../../includes/functions/helpers.php';

header('Content-Type: application/json');

// Admin-only access check
if (!isset($_SESSION['logged_in']) || !isset($_SESSION['role']) || 
    ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'super_admin')) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

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
    $newStatus = $assignedCount > 0 ? 'assigned' : $application['status'];
    $stmt = $conn->prepare("UPDATE applications SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$newStatus, $applicationId]);
    
    $conn->commit();
    
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
