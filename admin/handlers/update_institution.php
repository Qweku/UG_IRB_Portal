<?php
require_once '../../includes/functions/helpers.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}
$id = trim($data['id']);
$institution_name = trim($data['institution_name'] ?? '');
$email = trim($data['email'] ?? '');
if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'Institution ID cannot be empty']);
    exit;
}
$db = new Database();
$conn = $db->connect();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}
$stmt = $conn->prepare("UPDATE institutions SET institution_name = ?, email = ? WHERE id = ?");
$stmt->execute([$institution_name, $email, $id]);
if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update institution or no changes made']);
}
?>