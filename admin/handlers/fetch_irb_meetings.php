<?php
require_once '../../includes/functions/helpers.php';

$irbMeetings = [];

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $irbMeeting = executeAssocQuery("SELECT id, meeting_date, irb_code FROM irb_meetings WHERE id = ?", [$id]);
    if ($irbMeeting) {
        header('Content-Type: application/json');
        echo json_encode($irbMeeting[0]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
    }
    exit;
}

// Fetch all irb meetings
$irbMeetings = executeAssocQuery("SELECT id, meeting_date, irb_code FROM irb_meetings ORDER BY meeting_date DESC");
echo '<div class="table-responsive" style="height:300px;"><table class="table table-striped">';
echo '<thead>
        <tr>
            
            <th>Meeting Date</th>
            <th>IRB Code</th>
            <th>Actions</th>
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