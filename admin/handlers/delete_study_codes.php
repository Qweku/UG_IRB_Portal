<?php
require_once '../../includes/config/database.php';
require_once '../includes/auth_check.php';
require_once '../../includes/functions/csrf.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// CSRF validation
if (!isset($data['csrf_token']) || !csrf_validate_token($data['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$id = $data['id'];

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $stmt = $conn->prepare("DELETE FROM study_status_codes WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Record not found']);
    }

} catch (Exception $e) {
    error_log("Error deleting risk: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>