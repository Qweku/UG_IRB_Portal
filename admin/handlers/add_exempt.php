<?php 
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['exempt_cite'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$exempt_cite = trim($data['exempt_cite']);

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

    $stmt = $conn->prepare("INSERT INTO exempt_codes (exempt_cite) VALUES (?)");
    $stmt->execute([$exempt_cite]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add drug']);
    }

} catch (Exception $e) {
    error_log("Error adding benefit: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>