<?php
require_once '../../includes/config/database.php';

if (!isset($_GET['meeting_date'])) {
    echo json_encode(["error" => "No meeting date"]);
    exit;
}

$meeting_date = $_GET['meeting_date'];



try {
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }
    // Assuming a meetings table exists
    $stmt = $conn->prepare("SELECT * FROM agenda_items WHERE meeting_date = ?");
    $stmt->execute([$meeting_date]);
    $item = $stmt->fetchAll();

    echo json_encode($item);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
    return [];
}
