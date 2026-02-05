<?php
require_once '../../includes/functions/helpers.php';
require_once '../includes/auth_check.php';
$studyId = $_GET['study_id'] ?? '';
if (!$studyId) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing study_id']);
    exit;
}

header('Content-Type: application/json');

// Fetch study documents
$documents = executeAssocQuery("SELECT * FROM documents WHERE study_id = ?", [$studyId]);
echo json_encode($documents);
?>