<?php
// Session is already started in index.php, no need to start again

// ===========================================================
// SINGLE SESSION PER USER - Clear session token from database
// ===========================================================
if (isset($_SESSION['user_id']) && isset($_SESSION['session_token'])) {
    require_once __DIR__ . '/../includes/config/database.php';
    $db = new Database();
    $conn = $db->connect();
    
    if ($conn) {
        try {
            $stmt = $conn->prepare("UPDATE users SET session_token = NULL, session_expires_at = NULL, last_activity = NULL WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
        } catch (Exception $e) {
            error_log('Failed to clear session token on logout: ' . $e->getMessage());
        }
    }
}

// ===========================================================
// COMPLETE SESSION DESTRUCTION
// ===========================================================
// Use $_SESSION = array() instead of session_unset() for complete cleanup
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login
header("Location: /login");
exit;
