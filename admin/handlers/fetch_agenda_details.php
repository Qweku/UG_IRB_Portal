<?php
require_once '../../includes/config/database.php';

if (!isset($_GET['id'])) {
    echo json_encode(["error" => "No ID"]);
    exit;
}

$id = $_GET['id'];



try {
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }
    // Assuming a meetings table exists
    $stmt = $conn->prepare("SELECT a.pi, a.irb_number, a.agenda_group, a.reference_number, a.title, a.meeting_date, a.internal_number, 
    s.renewal_cycle, s.expiration_date, s.date_received, s.first_irb_review, s.approval_date, s.last_irb_review, s.last_renewal_date, 
    s.study_status, s.review_type FROM agenda_items a LEFT JOIN studies s ON a.reference_number = s.ref_num 
    WHERE a.id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($item);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
    return [];
}
