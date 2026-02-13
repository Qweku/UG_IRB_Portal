<?php
require_once '../includes/auth_check.php';
require_once '../../includes/functions/helpers.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['expedite_cite'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$expedite_cite = trim($data['expedite_cite']);
$expedite_description = trim($data['expedite_description']);

if (empty($expedite_cite)) {
    echo json_encode(['success' => false, 'message' => 'Expedited Cite cannot be empty']);
    exit;
}

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO expedited_codes (expedite_cite, expedite_description) VALUES (?, ?)");
$stmt->execute([$expedite_cite, $expedite_description]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add expedited']);
}
?>