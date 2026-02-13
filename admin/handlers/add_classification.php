<?php
require_once '../includes/auth_check.php';
require_once '../../includes/functions/helpers.php';
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['classification_type'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$classification_type = trim($data['classification_type']);

if (empty($classification_type)) {
    echo json_encode(['success' => false, 'message' => 'Classification cannot be empty']);
    exit;
}

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO classifications (classification_type) VALUES (?)");
$stmt->execute([$classification_type]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add classification']);
}
?>