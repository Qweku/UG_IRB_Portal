<?php
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['study_status'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$type = trim($data['type']);
$study_status = trim($data['study_status']);
$study_active_code = trim($data['study_active_code']);
$sort_sequence = trim($data['sort_sequence']);

if (empty($study_status)) {
    echo json_encode(['success' => false, 'message' => 'Study status cannot be empty']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $stmt = $conn->prepare("INSERT INTO study_status_codes (study_type, study_status, study_active_code, seq) VALUES (?,?,?,?)");
    $stmt->execute([$type, $study_status, $study_active_code, $sort_sequence]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add study code']);
    }

} catch (Exception $e) {
    error_log("Error adding study code: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>