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

if (isset($_GET['contact_id']) && is_numeric($_GET['contact_id'])) {
    $contact_id = (int)$_GET['contact_id'];
    try {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT id, contact_id, file_name, file_path, file_size, comments, uploaded_at FROM contact_documents WHERE contact_id = ? ORDER BY uploaded_at DESC");
        $stmt->execute([$contact_id]);
        $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        error_log(__FILE__ . ": Fetched " . count($documents) . " documents for contact_id " . $contact_id);

        echo json_encode(['status' => 'success', 'data' => $documents]);
    } catch (PDOException $e) {
        error_log(__FILE__ . " - Database error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
