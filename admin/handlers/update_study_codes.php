<?php
require_once '../../includes/config/database.php';
require_once '../includes/auth_check.php';
require_once '../../includes/functions/csrf.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id']) || !isset($data['study_status'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// CSRF validation
if (!isset($data['csrf_token']) || !csrf_validate_token($data['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$id = $data['id'];
$type = trim($data['type']);
$study_status = trim($data['study_status']);
$study_active_code = trim($data['study_active_code']);
$seq = trim($data['seq']);

if (!is_numeric($id) || $id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}
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

    // Prepare the SQL statement with named parameters for better readability and maintainability
    $sql = "UPDATE study_status_codes SET study_type = :type, study_status = :study_status, study_active_code = :study_active_code, seq = :seq WHERE id = :id";
    $stmt = $conn->prepare($sql);

    // Bind parameters explicitly with types for security and performance
    $stmt->bindParam(':type', $type, PDO::PARAM_STR);
    $stmt->bindParam(':study_status', $study_status, PDO::PARAM_STR);
    $stmt->bindParam(':study_active_code', $study_active_code, PDO::PARAM_STR);
    $stmt->bindParam(':seq', $seq, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Execute the statement
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or record not found']);
    }

} catch (Exception $e) {
    error_log("Error updating study code: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>