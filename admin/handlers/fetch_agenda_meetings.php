<?php
require_once '../includes/auth_check.php';
require_once '../../includes/functions/helpers.php';

header('Content-Type: application/json');

// Require authentication
require_auth();

if (!isset($_GET['meeting_date'])) {
    echo json_encode(["error" => "No meeting date"]);
    exit;
}

$meeting_date = $_GET['meeting_date'];

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM agenda_items WHERE meeting_date = ?");
    $stmt->execute([$meeting_date]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log(__FILE__ . ": Fetched " . count($items) . " agenda items for meeting date " . $meeting_date);

    echo json_encode(['status' => 'success', 'data' => $items]);

} catch (PDOException $e) {
    error_log(__FILE__ . " - Database error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
