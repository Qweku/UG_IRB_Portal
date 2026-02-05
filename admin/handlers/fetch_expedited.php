<?php
require_once '../includes/auth_check.php';
require_once '../../includes/functions/helpers.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $expedite = executeAssocQuery("SELECT id, expedite_cite, expedite_description FROM expedited_codes WHERE id = ?", [$id]);
    if ($expedite) {
        header('Content-Type: application/json');
        echo json_encode($expedite[0]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
    }
    exit;
}

// Fetch all expedites
$expedites = executeAssocQuery("SELECT id, expedite_cite, expedite_description FROM expedited_codes ORDER BY id ASC");
echo '<div class="table-responsive" style="height:300px;"><table class="table table-striped">';
echo '<thead><tr><th>Cite</th><th>Description</th><th>Actions</th></tr></thead><tbody>';
foreach ( $expedites as $row) {
    echo "<tr><td>{$row['expedite_cite']}</td><td>{$row['expedite_description']}</td><td><button class='btn btn-sm btn-outline-success' onclick='editItem({$row['id']}, \"{$row['expedite_cite']}\")'><i class='fas fa-edit'></i></button><button class='btn btn-sm btn-outline-danger' onclick='deleteItem({$row['id']})'><i class='fas fa-trash'></i></button></td></tr>";
}   
echo '</tbody></table></div>';