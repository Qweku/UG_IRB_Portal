<?php
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['event_type'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$event_type = trim($data['event_type']);
$notify_irb = trim($data['notify_irb']);

if (empty($event_type)) {
    echo json_encode(['success' => false, 'message' => 'Type cannot be empty']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $stmt = $conn->prepare("INSERT INTO sae_event_types (event_type, notify_irb) VALUES (?,?)");
    $stmt->execute([$event_type, $notify_irb]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add sae type']);
    }

} catch (Exception $e) {
    error_log("Error adding sae types: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>