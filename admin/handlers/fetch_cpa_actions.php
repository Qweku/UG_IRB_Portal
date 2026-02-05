<?php
require_once '../includes/auth_check.php';
require_once '../../includes/functions/helpers.php';

$cpaActions = [];

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $cpaAction = executeAssocQuery("SELECT id, cpa_action, study_status, sort_sequence FROM cpa_action_codes WHERE id = ?", [$id]);
    if ($cpaAction) {
        header('Content-Type: application/json');
        echo json_encode($cpaAction[0]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
    }
    exit;
}

// Fetch all cpa actions
$cpaActions = executeAssocQuery("SELECT id, cpa_action, study_status, sort_sequence FROM cpa_action_codes ORDER BY id ASC");
echo '<div class="table-responsive" style="height:300px;"><table class="table table-striped">';
echo '<thead>
        <tr>
            
            <th>CPA Action</th>
            <th>Study Status</th>
            <th>SortSeq</th>
            <th>Actions</th>
        </tr>
        </thead><tbody>';
foreach ( $cpaActions as $row) {
    echo "<tr>
    
    <td>{$row['cpa_action']}</td>
    <td>{$row['study_status']}</td>
    <td>{$row['sort_sequence']}</td>
    <td><button class='btn btn-sm btn-outline-success' onclick='editItem({$row['id']}, \"{$row['cpa_action']}\")'><i class='fas fa-edit'></i></button>
    <button class='btn btn-sm btn-outline-danger' onclick='deleteItem({$row['id']})'><i class='fas fa-trash'></i></button>
    </td>
    </tr>";
}
echo '</tbody></table></div>';