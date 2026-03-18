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

    // Check if ID parameter is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'CPA ID is required']);
        exit;
    }

    $id = $_GET['id'];

    // Validate ID is numeric
    if (!is_numeric($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid CPA ID']);
        exit;
    }

    // Fetch CPA details by ID
    $stmt = $conn->prepare("
        SELECT 
          *
        FROM cpas 
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    $cpa = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cpa) {
        echo json_encode(['status' => 'success', 'data' => $cpa]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'CPA not found']);
    }

} catch (PDOException $e) {
    error_log(__FILE__ . " - Database error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
