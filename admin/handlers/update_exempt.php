<?php
require_once '../../includes/functions/helpers.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id']) || !isset($data['exempt_cite'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$id = $data['id'];
$exempt_cite = trim($data['exempt_cite']);
$exempt_description = trim($data['exempt_description']);

if (empty($exempt_cite)) {
    echo json_encode(['success' => false, 'message' => 'Exempt Cite cannot be empty']);
    exit;
}

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare("UPDATE exempt_codes SET exempt_cite = ?, exempt_description = ? WHERE id = ?");
$stmt->execute([$exempt_cite, $exempt_description, $id]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'No changes made or record not found']);
}
?>