<?php
require_once '../includes/auth_check.php';
require_once '../../includes/functions/helpers.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['division_name'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$division_name = trim($data['division_name']);

if (empty($division_name)) {
    echo json_encode(['success' => false, 'message' => 'Division name cannot be empty']);
    exit;
}

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO divisions (division_name) VALUES (?)");
$stmt->execute([$division_name]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add division']);
}
?>