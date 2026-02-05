<?php
require_once '../includes/auth_check.php';
require_once '../../includes/functions/helpers.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $exempt = executeAssocQuery("SELECT id, exempt_cite, exempt_description FROM exempt_codes WHERE id = ?", [$id]);
    if ($exempt) {
        header('Content-Type: application/json');
        echo json_encode($exempt[0]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
    }
    exit;
}

// Fetch all exempts
$exempts = executeAssocQuery("SELECT id, exempt_cite, exempt_description FROM exempt_codes ORDER BY id ASC");
echo '<div class="table-responsive" style="height:300px;"><table class="table table-striped">';
echo '<thead><tr><th>Cite</th><th>Description</th><th>Actions</th></tr></thead><tbody>';
foreach ( $exempts as $row) {
    echo "<tr><td>{$row['exempt_cite']}</td><td>{$row['exempt_description']}</td><td><button class='btn btn-sm btn-outline-success' onclick='editItem({$row['id']}, \"{$row['exempt_cite']}\")'><i class='fas fa-edit'></i></button><button class='btn btn-sm btn-outline-danger' onclick='deleteItem({$row['id']})'><i class='fas fa-trash'></i></button></td></tr>";
}   
echo '</tbody></table></div>';