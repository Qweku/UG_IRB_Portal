<?php
require_once '../includes/auth_check.php';
require_once '../../includes/functions/helpers.php';

$risks = [];

// Fetch all risks
$risks = executeAssocQuery("SELECT id, category_name FROM risks_category ORDER BY id ASC");
echo '<div class="table-responsive" style="height:300px;"><table class="table table-striped">';
echo '<thead><tr><th>Name</th><th>Actions</th></tr></thead><tbody>';
foreach ( $risks as $row) {
    echo "<tr><td>{$row['category_name']}</td><td><button class='btn btn-sm btn-outline-success' onclick='editItem({$row['id']}, \"{$row['category_name']}\")'><i class='fas fa-edit'></i></button><button class='btn btn-sm btn-outline-danger' onclick='deleteItem({$row['id']})'><i class='fas fa-trash'></i></button></td></tr>";
}
echo '</tbody></table></div>';