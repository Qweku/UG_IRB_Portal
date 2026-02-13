<?php
require_once '../includes/auth_check.php';
require_once '../../includes/config/database.php';
require_once '../../includes/functions/notification_functions.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id']) || !isset($data['action_taken'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$id = $data['id'];
$action_taken = trim((string) ($data['action_taken'] ?? ''));
$condition_1 = trim((string) ($data['condition_1'] ?? ''));
$condition_2 = trim((string) ($data['condition_2'] ?? ''));
$action_explanation = trim((string) ($data['action_explanation'] ?? ''));

if (!is_numeric($id) || $id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Prepare the SQL statement
    $sql = "UPDATE agenda_items SET 
    action_taken = :action_taken, 
    condition_1 = :condition_1, 
    condition_2 = :condition_2,
    action_explanation = :action_explanation
     WHERE id = :id";
    $stmt = $conn->prepare($sql);

    // Bind parameters
    $stmt->bindParam(':action_taken', $action_taken, PDO::PARAM_STR);
    $stmt->bindParam(':condition_1', $condition_1, PDO::PARAM_STR);
    $stmt->bindParam(':condition_2', $condition_2, PDO::PARAM_STR);
    $stmt->bindParam(':action_explanation', $action_explanation, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Execute the statement
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Get application details for notification
        $appSql = "SELECT ap.user_id, ap.study_title, ap.applicant_id, u.full_name as applicant_name 
                   FROM agenda_items ai 
                   JOIN applications ap ON ai.application_id = ap.id 
                   JOIN users u ON ap.applicant_id = u.id 
                   WHERE ai.id = :id";
        $appStmt = $conn->prepare($appSql);
        $appStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $appStmt->execute();
        $application = $appStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($application) {
            // Map action_taken to status
            $statusMap = [
                'Approved' => 'approved',
                'Rejected' => 'rejected',
                'Revisions Required' => 'revisions_required',
                'Deferred' => 'pending'
            ];
            
            $status = $statusMap[$action_taken] ?? 'pending';
            $statusMessage = !empty($action_explanation) ? $action_explanation : 
                             (isset($condition_1) && !empty($condition_1) ? $condition_1 : 
                             (isset($condition_2) && !empty($condition_2) ? $condition_2 : 
                             "Your application has been processed by the IRB."));
            
            // Send notification to applicant
            createApplicationStatusNotification(
                $application['applicant_id'],
                $id,
                $application['study_title'],
                $status,
                $statusMessage
            );

            // If revisions are required, send follow-up notification
            if ($action_taken === 'Revisions Required') {
                // Get follow-up date if provided
                $followUpDate = !empty($data['follow_up_date']) ? $data['follow_up_date'] : date('Y-m-d', strtotime('+30 days'));
                
                createFollowUpRequiredNotification(
                    $application['applicant_id'],
                    $id,
                    $application['study_title'],
                    $followUpDate
                );
            }
        }
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or record not found']);
    }

} catch (Exception $e) {
    error_log("Error updating agenda item: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>