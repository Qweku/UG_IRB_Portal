<?php
require_once '../../includes/functions/helpers.php';

$irbActions = [];

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $irbAction = executeAssocQuery("SELECT id, irb_action, study_status, user_name, date_modified, sort_sequence FROM irb_action_codes WHERE id = ?", [$id]);
    if ($irbAction) {
        header('Content-Type: application/json');
        echo json_encode($irbAction[0]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
    }
    exit;
}

// Fetch all irb actions
$irbActions = executeAssocQuery("SELECT id, irb_action, study_status, user_name, date_modified, sort_sequence FROM irb_action_codes ORDER BY id ASC");
echo '<div class="table-responsive" style="height:300px;"><table class="table table-striped">';
echo '<thead>
        <tr>
           
            <th>IRB Action</th>
            <th>Study Status</th>
            <th>User name</th>
            <th>Date Modified</th>
            <th>SortSeq</th>
            <th>Actions</th>
        </tr>
        </thead><tbody>';
foreach ( $irbActions as $row) {
    echo "<tr>
    
    <td>{$row['irb_action']}</td>
    <td>{$row['study_status']}</td>
    <td>{$row['user_name']}</td>
    <td>{$row['date_modified']}</td>
    <td>{$row['sort_sequence']}</td>
    <td><button class='btn btn-sm btn-outline-success' onclick='editItem({$row['id']}, \"{$row['irb_action']}\")'><i class='fas fa-edit'></i></button>
    <button class='btn btn-sm btn-outline-danger' onclick='deleteItem({$row['id']})'><i class='fas fa-trash'></i></button>
    </td>
    </tr>";
}
echo '</tbody></table></div>';