<?php
require_once '../../includes/functions/helpers.php';

$saeTypes = [];

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $saeType = executeAssocQuery("SELECT id, event_type, notify_irb FROM sae_event_types WHERE id = ?", [$id]);
    if ($saeType) {
        header('Content-Type: application/json');
        echo json_encode($saeType[0]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
    }
    exit;
}

// Fetch all sae types
$saeTypes = executeAssocQuery("SELECT id, event_type, notify_irb FROM sae_event_types ORDER BY id ASC");
echo '<div class="table-responsive" style="height:300px;"><table class="table table-striped">';
echo '<thead>
        <tr>
           
            <th>Event Type</th>
            <th>Notify IRB</th>
            <th>Actions</th>
        </tr>
        </thead><tbody>';
foreach ( $saeTypes as $row) {
    echo "<tr>
   
    <td>{$row['event_type']}</td>
    <td>{$row['notify_irb']}</td>
    <td><button class='btn btn-sm btn-outline-success' onclick='editItem({$row['id']}, \"{$row['event_type']}\")'><i class='fas fa-edit'></i></button>
    <button class='btn btn-sm btn-outline-danger' onclick='deleteItem({$row['id']})'><i class='fas fa-trash'></i></button>
    </td>
    </tr>";
}
echo '</tbody></table></div>';