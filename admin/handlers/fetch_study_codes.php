<?php
require_once '../includes/auth_check.php';
require_once '../../includes/functions/helpers.php';

$studyCodes = [];

if (isset($_GET['id'])) {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $studyCode = executeAssocQuery("SELECT id, study_type, study_status, study_active_code, seq FROM study_status_codes WHERE id = ?", [$id]);
    if ($studyCode) {
        header('Content-Type: application/json');
        echo json_encode($studyCode[0]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
    }
    exit;
}

// Fetch all study codes
$studyCodes = executeAssocQuery("SELECT id, study_type, study_status, study_active_code, seq FROM study_status_codes ORDER BY id ASC");
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
foreach ($studyCodes as $row) {
    $study_type_escaped = htmlspecialchars($row['study_type'], ENT_QUOTES, 'UTF-8');
    $id_escaped = (int)$row['id'];
    echo "<tr>
    <td>" . htmlspecialchars($row['study_type'], ENT_QUOTES, 'UTF-8') . "</td>
    <td>" . htmlspecialchars($row['study_status'], ENT_QUOTES, 'UTF-8') . "</td>
    <td>" . htmlspecialchars($row['study_active_code'], ENT_QUOTES, 'UTF-8') . "</td>
    <td>" . htmlspecialchars($row['seq'], ENT_QUOTES, 'UTF-8') . "</td>
    <td><button class='btn btn-sm btn-outline-success' onclick='editItem({$id_escaped}, \"{$study_type_escaped}\")'><i class='fas fa-edit'></i></button>
    <button class='btn btn-sm btn-outline-danger' onclick='deleteItem({$id_escaped})'><i class='fas fa-trash'></i></button>
    </td>
    </tr>";
}
echo '</tbody></table></div>';
