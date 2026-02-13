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

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    }

    if (isset($_GET['study_id']) && is_numeric($_GET['study_id'])) {
        $study_id = (int)$_GET['study_id'];
        $stmt = $conn->prepare("SELECT * FROM study_personnel WHERE study_id = ?");
        $stmt->execute([$study_id]);
        $personnel = $stmt->fetchAll(PDO::FETCH_ASSOC);

        error_log(__FILE__ . ": Fetched " . count($personnel) . " personnel for study_id " . $study_id);

        echo json_encode(['status' => 'success', 'data' => $personnel]);
    } elseif (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $personnel_id = (int)$_GET['id'];

        $stmt = $conn->prepare("SELECT * FROM study_personnel WHERE id = ?");
        $stmt->execute([$personnel_id]);
        $personnel = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($personnel) {
            error_log(__FILE__ . ": Fetched personnel with id " . $personnel_id);
            echo json_encode(['status' => 'success', 'data' => $personnel]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Personnel not found']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    }
} catch (PDOException $e) {
    error_log(__FILE__ . " - Database error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
