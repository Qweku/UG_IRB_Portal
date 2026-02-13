<?php
require_once '../includes/auth_check.php';
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id']) || !isset($data['age_range'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$id = $data['id'];
$age_range = trim($data['age_range']);

if (empty($age_range)) {
    echo json_encode(['success' => false, 'message' => 'Age range cannot be empty']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $stmt = $conn->prepare("UPDATE children SET age_range = ? WHERE id = ?");
    $stmt->execute([$age_range, $id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or record not found']);
    }

} catch (Exception $e) {
    error_log("Error updating child: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>