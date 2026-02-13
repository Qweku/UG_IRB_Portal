<?php
require_once '../includes/auth_check.php';
require_once '../../includes/functions/helpers.php';

header('Content-Type: application/json');

// Require authentication
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$study_id = $_GET['study_id'] ?? null;

if (!$study_id || !is_numeric($study_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid study ID']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        exit;
    }

    $emails = getPersonnelEmails((int)$study_id);

    error_log(__FILE__ . ": Fetched " . count($emails) . " emails for study_id " . $study_id);

    echo json_encode(['status' => 'success', 'data' => $emails]);
} catch (PDOException $e) {
    error_log(__FILE__ . " - Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
