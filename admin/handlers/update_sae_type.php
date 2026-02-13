<?php
require_once '../includes/auth_check.php';
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id']) || !isset($data['event_type'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$id = $data['id'];
$event_type = trim($data['event_type']);
$notify_irb = trim($data['notify_irb']);

if (!is_numeric($id) || $id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}
if (empty($event_type)) {
    echo json_encode(['success' => false, 'message' => 'Type name cannot be empty']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Prepare the SQL statement with named parameters for better readability and maintainability
    $sql = "UPDATE sae_event_types SET event_type = :event_type, notify_irb = :notify_irb WHERE id = :id";
    $stmt = $conn->prepare($sql);

    // Bind parameters explicitly with types for security and performance
    $stmt->bindParam(':event_type', $event_type, PDO::PARAM_STR);
    $stmt->bindParam(':notify_irb', $notify_irb, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Execute the statement
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or record not found']);
    }

} catch (Exception $e) {
    error_log("Error updating sae type: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>