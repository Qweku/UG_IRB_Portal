<?php
require_once '../../includes/functions/helpers.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['drug_name'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$drug_name = trim($data['drug_name']);

if (empty($drug_name)) {
    echo json_encode(['success' => false, 'message' => 'Drug name cannot be empty']);
    exit;
}

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO drugs (drug_name) VALUES (?)");
$stmt->execute([$drug_name]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add drug']);
}
?>