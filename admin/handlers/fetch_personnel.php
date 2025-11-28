<?php
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

if (isset($_GET['study_id']) && is_numeric($_GET['study_id'])) {
    $study_id = (int)$_GET['study_id'];
    try {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT * FROM study_personnel WHERE study_id = ?");
        $stmt->execute([$study_id]);
        $personnel = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'personnel' => $personnel]);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
} elseif (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $personnel_id = (int)$_GET['id'];

    try {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT * FROM study_personnel WHERE id = ?");
        $stmt->execute([$personnel_id]);
        $personnel = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($personnel) {
            echo json_encode(['status' => 'success', 'personnel' => $personnel]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Personnel not found']);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>