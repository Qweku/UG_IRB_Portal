<?php
require_once '../includes/auth_check.php';
require_once '../../includes/functions/helpers.php';

header('Content-Type: application/json');

// Validate input
if (!isset($_GET['table']) || !isset($_GET['column'])) {
    echo json_encode(["error" => "Missing table or column"]);
    exit;
}

$table  = $_GET['table'];
$column = $_GET['column'];

/*
-----------------------------------------------------
 SAFETY: Allow only specific tables and columns
 You SHOULD NOT allow raw table/column names from user.
-----------------------------------------------------
*/
$allowedTables = ["studies", "contacts", "saes", "cpas"]; // update as needed
// $allowedColumns = ["meeting_date", "agenda_group", "status", "role"]; // update as needed

if (!in_array($table, $allowedTables)) {
    echo json_encode(["error" => "Invalid table"]);
    exit;
}

// if (!in_array($column, $allowedColumns)) {
//     echo json_encode(["error" => "Invalid column"]);
//     exit;
// }

try {
    $db = new Database();
    $conn = $db->connect();

    // Build query: select distinct column values
    $sql = "SELECT DISTINCT `$column` FROM `$table` WHERE `$column` IS NOT NULL ORDER BY `$column` ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode($results);

} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
