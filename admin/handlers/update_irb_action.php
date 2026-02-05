<?php
require_once '../includes/auth_check.php';
require_once '../../includes/config/database.php';
require_once '../../includes/functions/helpers.php';

header('Content-Type: application/json');

// Check if user is logged in
// if (!is_admin_logged_in()) {
//     error_log("Unauthorized access attempt to update_irb_action.php");
//     error_log("Session data: " . print_r($_SESSION, true));
//     error_log("Is admin logged in: " . (is_admin_logged_in() ? 'true' : 'false'));
//     echo json_encode(['success' => false, 'message' => 'Unauthorized']);
//     exit;
// }

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id']) || !isset($data['irb_action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$id = $data['id'];
$irb_action = trim($data['irb_action']);
$user_name = trim($data['user_name'] ?? $_SESSION['role'] ?? 'unknown');
$study_status = trim($data['study_status']);
$sort_sequence = trim($data['sort_sequence']);

if (!is_numeric($id) || $id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}
if (empty($irb_action)) {
    echo json_encode(['success' => false, 'message' => 'IRB action cannot be empty']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Prepare the SQL statement with named parameters for better readability and maintainability
    $sql = "UPDATE irb_action_codes SET irb_action = :irb_action, user_name = :user_name, study_status = :study_status, sort_sequence = :sort_sequence WHERE id = :id";
    $stmt = $conn->prepare($sql);

    // Bind parameters explicitly with types for security and performance
    $stmt->bindParam(':irb_action', $irb_action, PDO::PARAM_STR);
    $stmt->bindParam(':user_name', $user_name, PDO::PARAM_STR);
    $stmt->bindParam(':study_status', $study_status, PDO::PARAM_STR);
    $stmt->bindParam(':sort_sequence', $sort_sequence, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Execute the statement
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or record not found']);
    }

} catch (Exception $e) {
    error_log("Error updating irb action: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>