<?php
require_once '../../includes/functions/helpers.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['condition_name'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$condition_name = trim($data['condition_name']);

if (empty($condition_name)) {
    echo json_encode(['success' => false, 'message' => 'IRB Condition cannot be empty']);
    exit;
}

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO irb_condition (condition_name) VALUES (?)");
$stmt->execute([$condition_name]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add IRB condition']);
}
?>