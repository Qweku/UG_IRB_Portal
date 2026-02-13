<?php
require_once '../includes/auth_check.php';
require_once '../../includes/functions/helpers.php';

header('Content-Type: application/json');

// Require authentication
require_auth();

if (!isset($_GET['id'])) {
    echo json_encode(["error" => "No ID"]);
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    }

    $stmt = $conn->prepare("SELECT a.pi, a.irb_number, a.agenda_group, a.reference_number, a.title, a.meeting_date, a.internal_number,
a.action_taken, a.condition_1, a.condition_2, a.action_explanation,
s.renewal_cycle, s.expiration_date, s.date_received, s.first_irb_review, s.approval_date, s.last_irb_review, s.last_renewal_date,
s.study_status, s.review_type FROM agenda_items a LEFT JOIN studies s ON a.reference_number = s.ref_num
WHERE a.id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    error_log(__FILE__ . ": Fetched agenda detail for id " . $id);

    echo json_encode($item ? ['status' => 'success', 'data' => $item] : ['status' => 'error', 'message' => 'Not found']);

} catch (PDOException $e) {
    error_log(__FILE__ . " - Database error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
