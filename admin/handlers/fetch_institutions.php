<?php
require_once '../includes/auth_check.php';
require_once '../../includes/functions/helpers.php';

header('Content-Type: application/json');

// Require authentication - this starts session and validates token
require_auth();

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, institution_name FROM institutions ORDER BY institution_name ASC");
    $stmt->execute();
    $institutions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log("Institutions: " . var_export($institutions, true));

    echo json_encode(['status' => 'success', 'institutions' => $institutions]);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
