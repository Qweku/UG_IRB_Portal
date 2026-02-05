<?php
require_once '../includes/auth_check.php';
require_once '../../includes/functions/helpers.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['benefit_type'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$benefit_type = trim($data['benefit_type']);

if (empty($benefit_type)) {
    echo json_encode(['success' => false, 'message' => 'Benefit type cannot be empty']);
    exit;
}

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO benefits (benefit_type) VALUES (?)");
$stmt->execute([$benefit_type]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add benefit']);
}
?>