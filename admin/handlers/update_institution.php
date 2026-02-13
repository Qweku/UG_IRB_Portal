<?php
require_once '../../includes/functions/helpers.php';
require_once '../includes/auth_check.php';
require_once '../../includes/functions/csrf.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

// CSRF validation
if (!isset($data['csrf_token']) || !csrf_validate()) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

if (!$data || !isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}
$id = isset($data['id']) ? (int)$data['id'] : 0;
$institution_name = trim($data['institution_name'] ?? '');
$email = trim($data['email'] ?? '');
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Institution ID']);
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
