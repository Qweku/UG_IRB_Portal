<?php
require_once '../../includes/functions/helpers.php';

if (!isset($_GET['id'])) {
    echo json_encode(["error" => "No ID"]);
    exit;
}

$id = $_GET['id'];

$item = executeAssocQuery("SELECT a.pi, a.irb_number, a.agenda_group, a.reference_number, a.title, a.meeting_date, a.internal_number,
a.action_taken, a.condition_1, a.condition_2, a.action_explanation,
s.renewal_cycle, s.expiration_date, s.date_received, s.first_irb_review, s.approval_date, s.last_irb_review, s.last_renewal_date,
s.study_status, s.review_type FROM agenda_items a LEFT JOIN studies s ON a.reference_number = s.ref_num
WHERE a.id = ?", [$id]);

echo json_encode($item ? $item[0] : null);
