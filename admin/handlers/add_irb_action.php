<?php
require_once '../includes/auth_check.php';
require_once '../../includes/config/database.php';
require_once '../../includes/functions/helpers.php';

header('Content-Type: application/json');

// Start session with consistent session name
if (session_status() === PHP_SESSION_NONE) {
    defined('CSRF_SESSION_NAME') || define('CSRF_SESSION_NAME', 'ug_irb_session');
    session_name(CSRF_SESSION_NAME);
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['irb_action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$irb_action = trim($data['irb_action']);
$study_status = trim($data['study_status']);
$user_name = $_SESSION['role']; // Use logged-in user's role instead of data
$sort_sequence = trim($data['sort_sequence']);

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

    $stmt = $conn->prepare("INSERT INTO irb_action_codes (irb_action, study_status, user_name, sort_sequence) VALUES (?,?,?,?)");
    $stmt->execute([$irb_action, $study_status, $user_name, $sort_sequence]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add IRB action']);
    }

} catch (Exception $e) {
    error_log("Error adding irb action: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>