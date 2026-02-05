<?php
require_once '../includes/auth_check.php';
require_once '../../includes/config/database.php';

if (!isset($_GET['id'])) {
    echo json_encode(["error" => "No ID"]);
    exit;
}

$id = $_GET['id'];

try {
    $db = new Database();
    $conn = $db->connect();

    $stmt = $conn->prepare("SELECT * FROM irb_templates WHERE id = ?");
    $stmt->execute([$id]);
    $template = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($template ? $template : null);
} catch (Exception $e) {
    error_log("Error fetching template: " . $e->getMessage());
    echo json_encode(["error" => "Database error"]);
}
?>