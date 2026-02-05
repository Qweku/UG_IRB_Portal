<?php
require_once '../includes/auth_check.php';
require_once '../../includes/functions/helpers.php';

$cpaTypes = [];

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $cpaType = executeAssocQuery("SELECT id, type_name, category, agenda FROM cpa_types WHERE id = ?", [$id]);
    if ($cpaType) {
        header('Content-Type: application/json');
        echo json_encode($cpaType[0]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
    }
    exit;
}

// Fetch all cpa types
$cpaTypes = executeAssocQuery("SELECT id, type_name, category, agenda FROM cpa_types ORDER BY id ASC");
echo '<div class="table-responsive" style="height:300px;"><table class="table table-striped">';
echo '<thead>
        <tr>
            
            <th>Name</th>
            <th>Category</th>
            <th>Agenda?</th>
            <th>Actions</th>
        </tr>
        </thead><tbody>';
foreach ( $cpaTypes as $row) {
    echo "<tr>
    
    <td>{$row['type_name']}</td>
    <td>{$row['category']}</td>
    <td>{$row['agenda']}</td>
    <td><button class='btn btn-sm btn-outline-success' onclick='editItem({$row['id']}, \"{$row['type_name']}\",\"{$row['category']}\", \"{$row['agenda']}\")'><i class='fas fa-edit'></i></button>
    <button class='btn btn-sm btn-outline-danger' onclick='deleteItem({$row['id']})'><i class='fas fa-trash'></i></button>
    </td>
    </tr>";
}
echo '</tbody></table></div>';