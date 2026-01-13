<?php
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id']) || !isset($data['action_taken'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$id = $data['id'];
$action_taken = trim((string) ($data['action_taken'] ?? ''));
$condition_1 = trim((string) ($data['condition_1'] ?? ''));
$condition_2 = trim((string) ($data['condition_2'] ?? ''));
$action_explanation = trim((string) ($data['action_explanation'] ?? ''));

if (!is_numeric($id) || $id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Prepare the SQL statement
    $sql = "UPDATE agenda_items SET 
    action_taken = :action_taken, 
    condition_1 = :condition_1, 
    condition_2 = :condition_2,
    action_explanation = :action_explanation
     WHERE id = :id";
    $stmt = $conn->prepare($sql);

    // Bind parameters
    $stmt->bindParam(':action_taken', $action_taken, PDO::PARAM_STR);
    $stmt->bindParam(':condition_1', $condition_1, PDO::PARAM_STR);
    $stmt->bindParam(':condition_2', $condition_2, PDO::PARAM_STR);
    $stmt->bindParam(':action_explanation', $action_explanation, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Execute the statement
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or record not found']);
    }

} catch (Exception $e) {
    error_log("Error updating agenda item: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>