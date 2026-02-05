<?php
// require_once '/config.php';
require_once '../../includes/functions/helpers.php';
require_once '../includes/auth_check.php';

if (!isset($_GET['meeting_date'])) {
    echo json_encode(["error" => "No meeting date"]);
    exit;
}

$meeting_date = $_GET['meeting_date'];

$items = executeAssocQuery("SELECT * FROM agenda_items WHERE meeting_date = ?", [$meeting_date]);

echo json_encode($items);
