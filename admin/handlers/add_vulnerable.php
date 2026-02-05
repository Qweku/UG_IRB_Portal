<?php
require_once '../includes/auth_check.php';
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['population_type'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$population_type = trim($data['population_type']);

if (empty($population_type)) {
    echo json_encode(['success' => false, 'message' => 'Population type cannot be empty']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $stmt = $conn->prepare("INSERT INTO vulnerable_populations (population_type) VALUES (?)");
    $stmt->execute([$population_type]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add vulnerable']);
    }

} catch (Exception $e) {
    error_log("Error adding vulnerable: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>