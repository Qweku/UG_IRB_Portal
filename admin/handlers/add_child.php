<?php
require_once '../../includes/functions/helpers.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['age_range'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$age_range = trim($data['age_range']);

if (empty($age_range)) {
    echo json_encode(['success' => false, 'message' => 'Age range cannot be empty']);
    exit;
}

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO children (age_range) VALUES (?)");
$stmt->execute([$age_range]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add child']);
}
?>