<?php
require_once '../includes/auth_check.php';
require_once '../../includes/functions/helpers.php';
header('Content-Type: application/json');

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

$emails = getPersonnelEmails((int)$study_id);

echo json_encode(['emails' => $emails]);
?>