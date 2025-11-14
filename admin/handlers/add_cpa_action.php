<?php
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['cpa_action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$cpa_action = trim($data['cpa_action']);
$study_status = trim($data['study_status']);
$sort_sequence = trim($data['sort_sequence']);

if (empty($cpa_action)) {
    echo json_encode(['success' => false, 'message' => 'CPA action cannot be empty']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $stmt = $conn->prepare("INSERT INTO cpa_action_codes (cpa_action, study_status, sort_sequence) VALUES (?,?,?)");
    $stmt->execute([$cpa_action, $study_status, $sort_sequence]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add CPA action']);
    }

} catch (Exception $e) {
    error_log("Error adding cpa action: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>