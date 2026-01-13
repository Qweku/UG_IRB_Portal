<?php
session_name('admin_session');
session_start();

// Log current session_expire_time before update
error_log("DEBUG: Before extend_session - session_expire_time: " . (isset($_SESSION['session_expire_time']) ? $_SESSION['session_expire_time'] : 'not set'));

// Refresh expiration time
$_SESSION['session_expire_time'] = time() + ini_get('session.gc_maxlifetime');

// Log after setting
error_log("DEBUG: After setting - session_expire_time: " . $_SESSION['session_expire_time']);

// Calculate new remaining time
$new_remaining = $_SESSION['session_expire_time'] - time();

// Log new_remaining
error_log("DEBUG: new_remaining: " . $new_remaining);

// Ensure session is saved
session_write_close();

// Send confirmation with new remaining time
header('Content-Type: application/json');
echo json_encode(['status' => 'ok', 'new_remaining' => $new_remaining]);
