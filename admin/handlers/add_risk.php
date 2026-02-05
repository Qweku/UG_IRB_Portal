<?php
require_once '../includes/auth_check.php';
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['category_name'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$category_name = trim($data['category_name']);

if (empty($category_name)) {
    echo json_encode(['success' => false, 'message' => 'Category name cannot be empty']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $stmt = $conn->prepare("INSERT INTO risks_category (category_name) VALUES (?)");
    $stmt->execute([$category_name]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add risk']);
    }

} catch (Exception $e) {
    error_log("Error adding risk: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>