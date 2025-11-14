<?php
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id']) || !isset($data['benefit_type'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$id = $data['id'];
$benefit_type = trim($data['benefit_type']);

if (empty($benefit_type)) {
    echo json_encode(['success' => false, 'message' => 'Benefit type cannot be empty']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $stmt = $conn->prepare("UPDATE benefits SET benefit_type = ? WHERE id = ?");
    $stmt->execute([$benefit_type, $id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or record not found']);
    }

} catch (Exception $e) {
    error_log("Error updating benefit: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>