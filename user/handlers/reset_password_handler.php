<?php
/**
 * Reset Password Handler
 * Handles AJAX requests to reset user passwords
 */

require_once __DIR__ . '/../../includes/config/database.php';
require_once __DIR__ . '/../../includes/functions/csrf.php';
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Verify CSRF token
$csrf_token = $_POST['csrf_token'] ?? '';
if (!validateCsrfToken($csrf_token)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
    exit;
}

// Get and validate inputs
$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate token
if (empty($token)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid token']);
    exit;
}

// Validate password
if (empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Password is required']);
    exit;
}

if (strlen($password) < 8) {
    echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters long']);
    exit;
}

// Validate password confirmation
if ($password !== $confirm_password) {
    echo json_encode(['status' => 'error', 'message' => 'Passwords do not match']);
    exit;
}

// Check password strength
if (!preg_match('/[A-Z]/', $password)) {
    echo json_encode(['status' => 'error', 'message' => 'Password must contain at least one uppercase letter']);
    exit;
}

if (!preg_match('/[a-z]/', $password)) {
    echo json_encode(['status' => 'error', 'message' => 'Password must contain at least one lowercase letter']);
    exit;
}

if (!preg_match('/[0-9]/', $password)) {
    echo json_encode(['status' => 'error', 'message' => 'Password must contain at least one number']);
    exit;
}

if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
    echo json_encode(['status' => 'error', 'message' => 'Password must contain at least one special character']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    }

    // Validate token exists and is not expired
    $stmt = $conn->prepare("
        SELECT id, user_id 
        FROM password_reset_tokens 
        WHERE token = ? 
        AND used_at IS NULL
        AND expires_at > NOW()
    ");
    $stmt->execute([$token]);
    $reset_token = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset_token) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid or expired token. Please request a new password reset.']);
        exit;
    }

    // Hash the new password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Start transaction
    $conn->beginTransaction();

    try {
        // Update user's password
        $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$hashed_password, $reset_token['user_id']]);

        if ($stmt->rowCount() === 0) {
            throw new Exception('Failed to update password');
        }

        // Mark token as used
        $stmt = $conn->prepare("UPDATE password_reset_tokens SET used_at = NOW() WHERE id = ?");
        $stmt->execute([$reset_token['id']]);

        // Invalidate all existing sessions for this user (optional security measure)
        // This would require a sessions table or column to track active sessions
        // For now, we'll just update the session token
        $stmt = $conn->prepare("UPDATE users SET session_token = NULL WHERE id = ?");
        $stmt->execute([$reset_token['user_id']]);

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'Your password has been reset successfully. Redirecting to login...'
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollBack();
        error_log("Reset password transaction error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'An error occurred. Please try again.']);
    }

} catch (PDOException $e) {
    error_log("Database error in reset_password_handler: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'An error occurred. Please try again later.']);
}
