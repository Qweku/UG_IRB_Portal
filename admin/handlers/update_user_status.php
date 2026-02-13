<?php
/**
 * Update User Status Handler
 * Handles AJAX requests to toggle user active/inactive status
 */

// Include required files - auth_check.php handles session start with consistent session name
require_once __DIR__ . '/../../includes/config/database.php';
require_once __DIR__ . '/../../includes/functions/csrf.php';
require_once __DIR__ . '/../../includes/functions/notification_functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Set JSON header
header('Content-Type: application/json');

// Use centralized role check
require_role('admin');

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

// Validate user_id
if (!isset($_POST['user_id']) || empty($_POST['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit;
}

$user_id = (int)$_POST['user_id'];

// Validate new_status
if (!isset($_POST['new_status']) || !in_array($_POST['new_status'], ['active', 'inactive'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status value']);
    exit;
}

$new_status = $_POST['new_status'];

// Connect to database
$db = new Database();
$conn = $db->connect();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    // Check if user exists
    $stmt = $conn->prepare("SELECT id, status FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    // Don't allow deactivating yourself
    if ($user_id === $_SESSION['user_id'] && $new_status === 'inactive') {
        echo json_encode(['success' => false, 'message' => 'You cannot deactivate your own account']);
        exit;
    }
    
    // Update user status
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $user_id]);
    
    // Get user details for notification
    $stmt = $conn->prepare("SELECT full_name, role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $userDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Create notification for the user about their account status change
    $isActive = ($new_status === 'active');
    $reason = $_POST['reason'] ?? '';
    createAccountStatusNotification(
        $user_id,
        $userDetails['role'] ?? 'user',
        $isActive,
        $reason
    );
    
    echo json_encode([
        'success' => true, 
        'message' => 'User status updated successfully',
        'new_status' => $new_status
    ]);
    
} catch (PDOException $e) {
    error_log("Error updating user status: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
