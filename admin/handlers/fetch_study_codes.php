<?php 
require_once '../../includes/config/database.php';

$studyCodes = [];

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

     if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $conn->prepare("SELECT id, study_type, study_status, study_active_code, seq FROM study_status_codes WHERE id = ?");
        $stmt->execute([$id]);
        $studyCode = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($studyCode) {
            header('Content-Type: application/json');
            echo json_encode($studyCode);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
        }
        exit;
    }

    // Fetch benefit options
    $stmt = $conn->prepare("SELECT id, study_type, study_status, study_active_code, seq FROM study_status_codes ORDER BY id ASC");
    $stmt->execute();
    $studyCodes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("Error fetching study status codes: " . $e->getMessage());
}
echo '<div class="table-responsive" style="height:300px;"><table class="table table-striped">';
echo '<thead>
        <tr>
           
            <th>Type</th>
            <th>Study Status</th>
            <th>Study Active Code</th>
            <th>Seq</th>
            <th>Actions</th>
        </tr>
        </thead><tbody>';
foreach ( $studyCodes as $row) {
    echo "<tr>
   
    <td>{$row['study_type']}</td>
    <td>{$row['study_status']}</td>
    <td>{$row['study_active_code']}</td>
    <td>{$row['seq']}</td>
    <td><button class='btn btn-sm btn-outline-success' onclick='editItem({$row['id']}, \"{$row['study_type']}\")'><i class='fas fa-edit'></i></button>
    <button class='btn btn-sm btn-outline-danger' onclick='deleteItem({$row['id']})'><i class='fas fa-trash'></i></button>
    </td>
    </tr>";
}
echo '</tbody></table></div>';