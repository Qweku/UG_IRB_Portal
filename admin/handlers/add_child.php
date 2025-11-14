<?php
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

error_log(print_r($data, true));

if (!$data || !isset($data['age_range'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$age_range = trim($data['age_range']);

if (empty($age_range)) {
    echo json_encode(['success' => false, 'message' => 'Age range cannot be empty']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $stmt = $conn->prepare("INSERT INTO children (age_range) VALUES (?)");
    $stmt->execute([$age_range]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add child']);
    }

} catch (Exception $e) {
    error_log("Error adding child: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>