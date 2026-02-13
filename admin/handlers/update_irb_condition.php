<?php
require_once '../includes/auth_check.php';
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id']) || !isset($data['condition_name'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$id = $data['id'];
$condition_name = trim($data['condition_name']);

if (!is_numeric($id) || $id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}
if (empty($condition_name)) {
    echo json_encode(['success' => false, 'message' => 'Condition name cannot be empty']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Prepare the SQL statement with named parameters for better readability and maintainability
    $sql = "UPDATE irb_condition SET condition_name = :condition_name WHERE id = :id";
    $stmt = $conn->prepare($sql);

    // Bind parameters explicitly with types for security and performance
    $stmt->bindParam(':condition_name', $condition_name, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Execute the statement
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or record not found']);
    }

} catch (Exception $e) {
    error_log("Error updating irb condition: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>