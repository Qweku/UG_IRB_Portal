<?php
require_once '../includes/auth_check.php';
require_once '../../includes/functions/helpers.php';

header('Content-Type: application/json');

// Require authentication
require_auth();

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    }

    // Check if fetching single record by ID
    if (isset($_GET['id'])) {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $stmt = $conn->prepare("SELECT id, study_type, study_status, study_active_code, seq FROM study_status_codes WHERE id = ?");
        $stmt->execute([$id]);
        $studyCode = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($studyCode) {
            echo json_encode(['status' => 'success', 'data' => $studyCode]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Not found']);
        }
        exit;
    }

    // Fetch all study codes
    $stmt = $conn->prepare("SELECT id, study_type, study_status, study_active_code, seq FROM study_status_codes ORDER BY id ASC");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log(__FILE__ . ": Fetched " . count($results) . " records");

    echo json_encode(['status' => 'success', 'data' => $results]);

} catch (PDOException $e) {
    error_log(__FILE__ . " - Database error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
