<?php
require_once '../../includes/functions/helpers.php';
header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->connect();

    $stmt = $conn->prepare("SELECT * FROM follow_ups ORDER BY id ASC");
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Unable to fetch follow-ups'
    ]);
}
?>