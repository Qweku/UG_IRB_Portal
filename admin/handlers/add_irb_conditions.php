<?php
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['condition_name'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$condition_name = trim($data['condition_name']);

if (empty($condition_name)) {
    echo json_encode(['success' => false, 'message' => 'Condition name cannot be empty']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $stmt = $conn->prepare("INSERT INTO irb_condition (condition_name) VALUES (?)");
    $stmt->execute([$condition_name]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add IRB condition']);
    }

} catch (Exception $e) {
    error_log("Error adding irb condition: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>