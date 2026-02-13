<?php
require_once '../includes/auth_check.php';
require_once '../../includes/config/database.php';
require_once '../../includes/functions/notification_functions.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['meeting_date'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$meeting_date = trim($data['meeting_date']);
$irb_code = trim($data['irb_code']);

if (empty($meeting_date)) {
    echo json_encode(['success' => false, 'message' => 'Meeting date cannot be empty']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $stmt = $conn->prepare("INSERT INTO irb_meetings (meeting_date, irb_code) VALUES (?,?)");
    $stmt->execute([$meeting_date, $irb_code]);

    $meetingId = $conn->lastInsertId();

    if ($stmt->rowCount() > 0) {
        // Notify admins, chairs, and reviewers about the scheduled meeting
        notifyMeetingScheduled($conn, $meetingId, $meeting_date);
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add IRB meeting']);
    }

} catch (Exception $e) {
    error_log("Error adding irb meeting: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>