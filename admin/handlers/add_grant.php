<?php
require_once '../../includes/functions/helpers.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['grant_name'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$grant_name = trim($data['grant_name']);

if (empty($grant_name)) {
    echo json_encode(['success' => false, 'message' => 'Grant name cannot be empty']);
    exit;
}

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO grant_projects (grant_name) VALUES (?)");
$stmt->execute([$grant_name]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add grant']);
}
?>