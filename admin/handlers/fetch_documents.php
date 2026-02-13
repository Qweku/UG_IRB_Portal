<?php
require_once '../includes/auth_check.php';
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

// Require authentication
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

if (isset($_GET['study_id']) && is_numeric($_GET['study_id'])) {
    $study_id = (int)$_GET['study_id'];
    try {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT * FROM documents WHERE study_id = ?");
        $stmt->execute([$study_id]);
        $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        error_log(__FILE__ . ": Fetched " . count($documents) . " documents for study_id " . $study_id);

        echo json_encode(['status' => 'success', 'data' => $documents]);
    } catch (PDOException $e) {
        error_log(__FILE__ . " - Database error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
