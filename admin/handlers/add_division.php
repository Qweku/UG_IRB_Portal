<?php
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

error_log(print_r($data, true));

if (!$data || !isset($data['division_name'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$division_name = trim($data['division_name']);

if (empty($division_name)) {
    echo json_encode(['success' => false, 'message' => 'Division name cannot be empty']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $stmt = $conn->prepare("INSERT INTO divisions (division_name) VALUES (?)");
    $stmt->execute([$division_name]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add division']);
    }

} catch (Exception $e) {
    error_log("Error adding division: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>