<?php
// Add diagnostic logging at the start to catch any early errors
error_log("[fetch_institutions] Starting request at " . date('Y-m-d H:i:s'));
error_log("[fetch_institutions] REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'unknown'));
error_log("[fetch_institutions] HTTP_X_REQUESTED_WITH: " . ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? 'not set'));

require_once '../includes/auth_check.php';
require_once '../../includes/config/database.php';

error_log("[fetch_institutions] After includes, checking buffer...");

// Check if there's any output in the buffer already
if (ob_get_level() > 0) {
    $buffer_content = ob_get_contents();
    if (!empty($buffer_content)) {
        error_log("[fetch_institutions] WARNING: Buffer not empty before JSON output: " . substr($buffer_content, 0, 200));
    }
}

header('Content-Type: application/json');

// Require authentication - this starts session and validates token
require_auth();

try {
    error_log("[fetch_institutions] Creating Database instance...");
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        error_log("[fetch_institutions] Database connection returned null/false");
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, institution_name FROM institutions ORDER BY institution_name ASC");
    $stmt->execute();
    $institutions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log("Institutions: " . var_export($institutions, true));

    echo json_encode(['status' => 'success', 'institutions' => $institutions]);

} catch (PDOException $e) {
    error_log("PDOException in fetch_institutions: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("General Exception in fetch_institutions: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'An unexpected error occurred']);
}
