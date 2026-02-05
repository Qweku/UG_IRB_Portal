<?php
require_once '../../includes/functions/helpers.php';
require_once '../includes/auth_check.php';
require_once '../../includes/functions/csrf.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

// CSRF validation
if (!isset($data['csrf_token']) || !csrf_validate_token($data['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

if (!$data || !isset($data['institution_name'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}
$institution_name = trim($data['institution_name']);
$email = trim($data['email'] ?? '');
if (empty($institution_name)) {
    echo json_encode(['success' => false, 'message' => 'Institution name cannot be empty']);
    exit;
}
$db = new Database();
$conn = $db->connect();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}
$stmt = $conn->prepare("INSERT INTO institutions (institution_name, email) VALUES (?, ?)");
$stmt->execute([$institution_name, $email]);
if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add institution']);
}
