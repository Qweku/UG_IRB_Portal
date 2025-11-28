<?php
require_once '../../includes/functions/helpers.php';

$drugs = [];

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $drug = executeAssocQuery("SELECT id, drug_name FROM drugs WHERE id = ?", [$id]);
    if ($drug) {
        header('Content-Type: application/json');
        echo json_encode($drug[0]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
    }
    exit;
}

// Fetch all drugs
$drugs = executeAssocQuery("SELECT id, drug_name FROM drugs ORDER BY id ASC");
echo '<div class="table-responsive" style="height:300px;"><table class="table table-striped">';
echo '<thead><tr><th>Name</th><th>Actions</th></tr></thead><tbody>';
foreach ( $drugs as $row) {
    echo "<tr><td>{$row['drug_name']}</td><td><button class='btn btn-sm btn-outline-success' onclick='editItem({$row['id']}, \"{$row['drug_name']}\")'><i class='fas fa-edit'></i></button><button class='btn btn-sm btn-outline-danger' onclick='deleteItem({$row['id']})'><i class='fas fa-trash'></i></button></td></tr>";
}
echo '</tbody></table></div>';