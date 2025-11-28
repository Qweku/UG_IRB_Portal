<?php
require_once '../../includes/functions/helpers.php';

$irbConditions = [];

// Fetch all irb conditions
$irbConditions = executeAssocQuery("SELECT id, condition_name FROM irb_condition ORDER BY id ASC");
echo '<div class="table-responsive" style="height:300px;"><table class="table table-striped">';
echo '<thead>
        <tr>
            
            <th>Name</th>
            <th>Actions</th>
        </tr>
        </thead><tbody>';
foreach ( $irbConditions as $row) {
    echo "<tr>
    
    <td>{$row['condition_name']}</td>
    <td><button class='btn btn-sm btn-outline-success' onclick='editItem({$row['id']}, \"{$row['condition_name']}\")'><i class='fas fa-edit'></i></button>
    <button class='btn btn-sm btn-outline-danger' onclick='deleteItem({$row['id']})'><i class='fas fa-trash'></i></button>
    </td>
    </tr>";
}
echo '</tbody></table></div>';