<?php
require_once '../includes/auth_check.php';
require_once '../../includes/functions/helpers.php';

$benefits = [];

// Fetch all benefits
$benefits = executeAssocQuery("SELECT id, benefit_type FROM benefits ORDER BY id ASC");
echo '<div class="table-responsive" style="height:300px;"><table class="table table-striped">';
echo '<thead><tr><th>Name</th><th>Actions</th></tr></thead><tbody>';
foreach ( $benefits as $row) {
    echo "<tr><td>{$row['benefit_type']}</td><td><button class='btn btn-sm btn-outline-success' onclick='editItem({$row['id']}, \"{$row['benefit_type']}\")'><i class='fas fa-edit'></i></button><button class='btn btn-sm btn-outline-danger' onclick='deleteItem({$row['id']})'><i class='fas fa-trash'></i></button></td></tr>";
}
echo '</tbody></table></div>';