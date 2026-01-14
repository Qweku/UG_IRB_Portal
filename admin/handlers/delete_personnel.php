<?php
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

// Fallback for FormData
if (!$data) {
    $data = $_POST;
}

if (
    empty($data['personnel_id']) ||
    empty($data['study_id'])
) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
    exit;
}

$personnelId = (int)$data['personnel_id'];
$studyId     = (int)$data['study_id'];

try {
    $db   = new Database();
    $conn = $db->connect();

    $conn->beginTransaction();

    // Delete personnel only
    $stmt = $conn->prepare(
        "DELETE FROM study_personnel WHERE id = ? AND study_id = ?"
    );
    $stmt->execute([$personnelId, $studyId]);

    if ($stmt->rowCount() === 0) {
        $conn->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Personnel not found'
        ]);
        exit;
    }

    // Optional update to studies table
    $conn->prepare(
        "UPDATE studies SET updated_at = NOW() WHERE id = ?"
    )->execute([$studyId]);

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Personnel removed successfully'
    ]);
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    error_log('Delete personnel error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error'
    ]);
}
