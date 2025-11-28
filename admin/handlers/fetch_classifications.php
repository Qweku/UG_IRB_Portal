<?php
require_once '../../includes/functions/helpers.php';

$classifications = [];

// Fetch all classifications
$classifications = executeAssocQuery("SELECT id, classification_type FROM classifications ORDER BY id ASC");

echo '<div class="table-responsive" style="height:300px;"><table class="table table-striped">';
echo '<thead><tr><th>Name</th><th>Actions</th></tr></thead><tbody>';
foreach ( $classifications as $row) {
    echo "<tr><td>{$row['classification_type']}</td><td><button class='btn btn-sm btn-outline-success' onclick='editItem({$row['id']}, \"{$row['classification_type']}\")'><i class='fas fa-edit'></i></button><button class='btn btn-sm btn-outline-danger' onclick='deleteItem({$row['id']})'><i class='fas fa-trash'></i></button></td></tr>";
}
echo '</tbody></table></div>';

?>


