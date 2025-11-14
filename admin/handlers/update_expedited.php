<?php
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id']) || !isset($data['expedite_cite'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$id = $data['id'];
$expedite_cite = trim($data['expedite_cite']);
$expedite_description = trim($data['expedite_description']);

if (empty($expedite_cite)) {
    echo json_encode(['success' => false, 'message' => 'Expedite Cite cannot be empty']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $stmt = $conn->prepare("UPDATE expedited_codes SET expedite_cite = :expedite_cite, expedite_description = :expedite_description WHERE id = :id");

    $stmt->bindParam(':expedite_cite', $expedite_cite, PDO::PARAM_STR);
    $stmt->bindParam(':expedite_description', $expedite_description, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or record not found']);
    }

} catch (Exception $e) {
    error_log("Error updating expedited codes: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>