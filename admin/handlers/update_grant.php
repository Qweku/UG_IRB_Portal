<?php
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id']) || !isset($data['grant_name'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$id = $data['id'];
$grant_name = trim($data['grant_name']);

if (empty($grant_name)) {
    echo json_encode(['success' => false, 'message' => 'Grant name cannot be empty']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $stmt = $conn->prepare("UPDATE grant_projects SET grant_name = ? WHERE id = ?");
    $stmt->execute([$grant_name, $id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or record not found']);
    }

} catch (Exception $e) {
    error_log("Error updating grant: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>