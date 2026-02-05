<?php
require_once '../includes/auth_check.php';
require_once '../../includes/functions/helpers.php';

$devices = [];

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $device = executeAssocQuery("SELECT id, device_name FROM device_types WHERE id = ?", [$id]);
    if ($device) {
        header('Content-Type: application/json');
        echo json_encode($device[0]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
    }
    exit;
}

// Fetch all devices
$devices = executeAssocQuery("SELECT id, device_name FROM device_types ORDER BY id ASC");
echo '<div class="table-responsive" style="height:300px;"><table class="table table-striped">';
echo '<thead><tr><th>Name</th><th>Actions</th></tr></thead><tbody>';
foreach ( $devices as $row) {
    echo "<tr><td>{$row['device_name']}</td><td><button class='btn btn-sm btn-outline-success' onclick='editItem({$row['id']}, \"{$row['device_name']}\")'><i class='fas fa-edit'></i></button><button class='btn btn-sm btn-outline-danger' onclick='deleteItem({$row['id']})'><i class='fas fa-trash'></i></button></td></tr>";
}
echo '</tbody></table></div>';