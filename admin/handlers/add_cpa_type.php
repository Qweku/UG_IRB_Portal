<?php
require_once '../includes/auth_check.php';
require_once '../../includes/functions/helpers.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['type_name'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$type_name = trim($data['type_name']);
$category = trim($data['category']);
$agenda = trim($data['agenda']);

if (empty($type_name)) {
    echo json_encode(['success' => false, 'message' => 'Type cannot be empty']);
    exit;
}

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO cpa_types (type_name, category, agenda) VALUES (?,?,?)");
$stmt->execute([$type_name, $category, $agenda]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add CPA type']);
}
?>