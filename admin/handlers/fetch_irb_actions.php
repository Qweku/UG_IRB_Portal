<?php 
require_once '../../includes/config/database.php';

$irbActions = [];

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

     if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $conn->prepare("SELECT id, irb_action, study_status, user_name, date_modified, sort_sequence FROM irb_action_codes WHERE id = ?");
        $stmt->execute([$id]);
        $irbAction = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($irbAction) {
            header('Content-Type: application/json');
            echo json_encode($irbAction);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
        }
        exit;
    }

    // Fetch benefit options
    $stmt = $conn->prepare("SELECT id, irb_action, study_status, user_name, date_modified, sort_sequence FROM irb_action_codes ORDER BY id ASC");
    $stmt->execute();
    $irbActions = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("Error fetching irb actions: " . $e->getMessage());
}
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