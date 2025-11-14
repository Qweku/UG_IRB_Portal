<?php 
require_once '../../includes/config/database.php';

$saeTypes = [];

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

     if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $conn->prepare("SELECT id, event_type, notify_irb FROM sae_event_types WHERE id = ?");
        $stmt->execute([$id]);
        $saeType = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($saeType) {
            header('Content-Type: application/json');
            echo json_encode($saeType);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
        }
        exit;
    }

    // Fetch benefit options
    $stmt = $conn->prepare("SELECT id, event_type, notify_irb FROM sae_event_types ORDER BY id ASC");
    $stmt->execute();
    $saeTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("Error fetching sae event types: " . $e->getMessage());
}
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