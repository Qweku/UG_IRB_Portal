<?php
require_once '../includes/auth_check.php';
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

// Require authentication
require_auth();

if (!isset($_GET['id'])) {
    echo json_encode(["error" => "No ID"]);
    exit;
}

$id = $_GET['id'];

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM irb_templates WHERE id = ?");
    $stmt->execute([$id]);
    $template = $stmt->fetch(PDO::FETCH_ASSOC);

    error_log(__FILE__ . ": Fetched template for id " . $id);

    echo json_encode($template ? ['status' => 'success', 'data' => $template] : ['status' => 'error', 'message' => 'Not found']);
} catch (PDOException $e) {
    error_log(__FILE__ . " - Database error: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Database error"]);
}
