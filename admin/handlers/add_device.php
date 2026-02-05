<?php
require_once '../includes/auth_check.php';
require_once '../../includes/functions/helpers.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['device_name'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$device_name = trim($data['device_name']);

if (empty($device_name)) {
    echo json_encode(['success' => false, 'message' => 'Device name cannot be empty']);
    exit;
}

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO device_types (device_name) VALUES (?)");
$stmt->execute([$device_name]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add device']);
}
?>