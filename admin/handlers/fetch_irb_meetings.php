<?php 
require_once '../../includes/config/database.php';

$irbMeetings = [];

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

     if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $conn->prepare("SELECT id, meeting_date, irb_code FROM irb_meetings WHERE id = ?");
        $stmt->execute([$id]);
        $irbMeeting = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($irbMeeting) {
            header('Content-Type: application/json');
            echo json_encode($irbMeeting);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
        }
        exit;
    }

    // Fetch benefit options
    $stmt = $conn->prepare("SELECT id, meeting_date, irb_code FROM irb_meetings ORDER BY id ASC");
    $stmt->execute();
    $irbMeetings = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("Error fetching irb meeting dates: " . $e->getMessage());
}
echo '<div class="table-responsive" style="height:300px;"><table class="table table-striped">';
echo '<thead>
        <tr>
            
            <th>Meeting Date</th>
            <th>IRB Code</th>
        </tr>
        </thead><tbody>';
foreach ( $irbMeetings as $row) {
    echo "<tr>
    
    <td>{$row['meeting_date']}</td>
    <td>{$row['irb_code']}</td>
    <td><button class='btn btn-sm btn-outline-success' onclick='editItem({$row['id']}, \"{$row['meeting_date']}\")'><i class='fas fa-edit'></i></button>
    <button class='btn btn-sm btn-outline-danger' onclick='deleteItem({$row['id']})'><i class='fas fa-trash'></i></button>
    </td>
    </tr>";
}
echo '</tbody></table></div>';