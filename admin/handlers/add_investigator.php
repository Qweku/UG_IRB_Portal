<?php
require_once '../includes/auth_check.php';
require_once '../../includes/functions/helpers.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['specialty_name'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$specialty_name = trim($data['specialty_name']);

if (empty($specialty_name)) {
    echo json_encode(['success' => false, 'message' => 'Specialty Name cannot be empty']);
    exit;
}

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO investigator (specialty_name) VALUES (?)");
$stmt->execute([$specialty_name]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add investigator']);
}
?>