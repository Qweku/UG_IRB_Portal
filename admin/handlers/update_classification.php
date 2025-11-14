<?php
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id']) || !isset($data['classification_type'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$id = $data['id'];
$classification_type = trim($data['classification_type']);

if (empty($classification_type)) {
    echo json_encode(['success' => false, 'message' => 'Classification type cannot be empty']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $stmt = $conn->prepare("UPDATE classifications SET classification_type = ? WHERE id = ?");
    $stmt->execute([$classification_type, $id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or record not found']);
    }

} catch (Exception $e) {
    error_log("Error updating classification: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>