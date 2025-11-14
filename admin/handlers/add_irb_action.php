<?php
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['irb_action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$irb_action = trim($data['irb_action']);
$study_status = trim($data['study_status']);
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

    $stmt = $conn->prepare("INSERT INTO irb_action_codes (irb_action, study_status, sort_sequence) VALUES (?,?,?)");
    $stmt->execute([$irb_action, $study_status, $sort_sequence]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add IRB action']);
    }

} catch (Exception $e) {
    error_log("Error adding irb action: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>