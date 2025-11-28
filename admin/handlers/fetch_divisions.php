<?php
require_once '../../includes/functions/helpers.php';

$divisions = [];

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $division = executeAssocQuery("SELECT id, division_name FROM divisions WHERE id = ?", [$id]);
    if ($division) {
        header('Content-Type: application/json');
        echo json_encode($division[0]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
    }
    exit;
}

// Fetch all divisions
$divisions = executeAssocQuery("SELECT id, division_name FROM divisions ORDER BY id ASC");
echo '<div class="table-responsive" style="height:300px;"><table class="table table-striped">';
echo '<thead><tr><th>Name</th><th>Actions</th></tr></thead><tbody>';
foreach ( $divisions as $row) {
    echo "<tr><td>{$row['division_name']}</td><td><button class='btn btn-sm btn-outline-success' onclick='editItem({$row['id']}, \"{$row['division_name']}\")'><i class='fas fa-edit'></i></button><button class='btn btn-sm btn-outline-danger' onclick='deleteItem({$row['id']})'><i class='fas fa-trash'></i></button></td></tr>";
}
echo '</tbody></table></div>';

