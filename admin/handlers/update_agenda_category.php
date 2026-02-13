<?php
require_once '../includes/auth_check.php';
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id']) || !isset($data['agenda_category'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$id = $data['id'];
$agenda_category = trim($data['agenda_category']);
$class_code = trim($data['class_code']);
$agenda_print = trim($data['agenda_print']);

if (!is_numeric($id) || $id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}
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

    // Prepare the SQL statement with named parameters for better readability and maintainability
    $sql = "UPDATE agenda_category SET category_name = :agenda_category, agenda_class_code = :class_code, agenda_print = :agenda_print WHERE id = :id";
    $stmt = $conn->prepare($sql);

    // Bind parameters explicitly with types for security and performance
    $stmt->bindParam(':agenda_category', $agenda_category, PDO::PARAM_STR);
    $stmt->bindParam(':class_code', $class_code, PDO::PARAM_STR);
    $stmt->bindParam(':agenda_print', $agenda_print, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Execute the statement
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or record not found']);
    }

} catch (Exception $e) {
    error_log("Error updating agenda category: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>