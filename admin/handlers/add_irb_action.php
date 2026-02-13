<?php
require_once '../includes/auth_check.php';
require_once '../../includes/config/database.php';
require_once '../../includes/functions/helpers.php';
require_once '../../includes/functions/notification_functions.php';

header('Content-Type: application/json');

// Use centralized auth check (any logged-in user can access this)
require_auth();

// Verify user has admin or super_admin role
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'super_admin')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['irb_action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$irb_action = trim($data['irb_action']);
$study_status = trim($data['study_status']);
$user_name = $_SESSION['role']; // Use logged-in user's role instead of data
$sort_sequence = trim($data['sort_sequence']);

if (empty($irb_action)) {
    echo json_encode(['success' => false, 'message' => 'IRB action cannot be empty']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $stmt = $conn->prepare("INSERT INTO irb_action_codes (irb_action, study_status, user_name, sort_sequence) VALUES (?,?,?,?)");
    $stmt->execute([$irb_action, $study_status, $user_name, $sort_sequence]);

    if ($stmt->rowCount() > 0) {
        // Note: This file manages IRB action codes (lookup table).
        // For actual IRB decisions on applications (approved, rejected, revisions_required),
        // notifications should be added to the handler that updates application status.
        // See: update_agenda_item.php for IRB decision recording.
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add IRB action']);
    }

} catch (Exception $e) {
    error_log("Error adding irb action: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>