<?php
require_once '../includes/auth_check.php';
require_once '../../includes/config/database.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'];
$meetingDate = $data['meetingDate'];

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $stmt = $conn->prepare("UPDATE agenda_items SET meeting_date = :meeting_date WHERE id = :id");
    $success = $stmt->execute([
        ':meeting_date' => $meetingDate,
        ':id' => $id
    ]);
    echo json_encode(['success' => $success]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
