<?php
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id']) || !isset($data['exempt_cite'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$id = $data['id'];
$exempt_cite = trim($data['exempt_cite']);
$exempt_description = trim($data['exempt_description']);

if (empty($exempt_cite)) {
    echo json_encode(['success' => false, 'message' => 'Exempt Cite cannot be empty']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $stmt = $conn->prepare("UPDATE exempt_codes SET exempt_cite = :exempt_cite, exempt_description = :exempt_description WHERE id = :id");

    $stmt->bindParam(':exempt_cite', $exempt_cite, PDO::PARAM_STR);
    $stmt->bindParam(':exempt_description', $exempt_description, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or record not found']);
    }

} catch (Exception $e) {
    error_log("Error updating exempt codes: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>