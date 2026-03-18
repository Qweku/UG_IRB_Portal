<?php
/**
 * Delete User Handler
 * Handles AJAX requests to delete a user
 */

// Include required files
require_once __DIR__ . '/../../includes/config/database.php';
require_once __DIR__ . '/../../includes/functions/csrf.php';
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

// Connect to database
$db = new Database();
$conn = $db->connect();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    // Check if user exists
    $stmt = $conn->prepare("SELECT id, email, role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    // Don't allow deleting your own account
    if ($user_id === $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'You cannot delete your own account']);
        exit;
    }
    
    // Delete user (this will cascade delete related records due to foreign key constraints)
    // Alternatively, you could soft-delete by setting a deleted_at timestamp
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
        exit;
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'User deleted successfully'
    ]);
    
} catch (PDOException $e) {
    error_log("Error deleting user: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
