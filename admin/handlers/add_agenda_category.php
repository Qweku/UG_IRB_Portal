<?php
require_once '../includes/auth_check.php';
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['category_name'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$agenda_category = trim($data['category_name']);
$class_code = trim($data['class_code']);
$agenda_print = trim($data['agenda_print']);

if (empty($agenda_category)) {
    echo json_encode(['success' => false, 'message' => 'Agenda category cannot be empty']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $stmt = $conn->prepare("INSERT INTO agenda_category (category_name, agenda_class_code, agenda_print) VALUES (?,?,?)");
    $stmt->execute([$agenda_category, $class_code, $agenda_print]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add agenda category']);
    }

} catch (Exception $e) {
    error_log("Error adding agenda category: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>