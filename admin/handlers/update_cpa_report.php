<?php
require_once '../includes/auth_check.php';
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

// Debug: Log incoming POST data
error_log("Update CPA Report Handler - POST data: " . json_encode($_POST));

// Check if required ID is provided
if (!isset($_POST['id']) || empty($_POST['id'])) {
    error_log("Update CPA Report Handler - Missing CPA ID");
    echo json_encode(['success' => false, 'message' => 'CPA ID is required']);
    exit;
}

// Validate required CPA fields
if (empty($_POST['cpa_type']) || empty($_POST['pre_action_meeting']) || !isset($_POST['signed']) 
    || empty($_POST['date_of_change']) 
) {
    error_log("Update CPA Report Handler - Missing required fields");
    echo json_encode(['success' => false, 'message' => 'Missing required CPA fields']);
    exit;
}

/**
 * Clean and trim input value
 */
function clean(?string $value, string $default = ''): string
{
    $value = isset($value) ? trim($value) : '';
    return $value === '' ? $default : $value;
}

$id = $_POST['id'];
$studyId = $_POST['protocol_id'] ?? $_POST['study_id'] ?? null;
$referenceNumber = $_POST['reference_number'] ?? $_POST['ref_number'] ?? null;
$cpaNumber = $_POST['cpa_number'] ?? null;

// Handle checkbox fields (convert to 0/1)
$expedited = isset($_POST['expedited']) ? 1 : 0;
$placedOnAgenda = isset($_POST['place_on_agenda']) ? 1 : 0;
$signed = isset($_POST['signed']) ? 1 : 0;

if (!$studyId) {
    error_log("Update CPA Report Handler - Missing study ID");
    echo json_encode(['success' => false, 'message' => 'Missing study ID']);
    exit;
}

// Validate ID is numeric
if (!is_numeric($id) || $id <= 0) {
    error_log("Update CPA Report Handler - Invalid CPA ID");
    echo json_encode(['success' => false, 'message' => 'Invalid CPA ID']);
    exit;
}

// Required CPA fields - map form field names to database column names
$required = [
    'cpa_type' => 'cpa_type',
    'pre_action_meeting' => 'pre_action_meeting',
    'date_of_change' => 'date_of_change'
];

$data = [
    'protocol_id' => $studyId,
    'reference_number' => $referenceNumber,
    'cpa_number' => $cpaNumber,
    'expedited' => $expedited,
    'place_on_agenda' => $placedOnAgenda,
    'signed' => $signed
];

foreach ($required as $postKey => $dbKey) {
    $value = trim($_POST[$postKey] ?? '');
    if ($value === '') {
        error_log("Update CPA Report Handler - Missing required field: $postKey");
        echo json_encode(['success' => false, 'message' => "Missing required CPA field: $postKey"]);
        exit;
    }
    $data[$dbKey] = $value;
}

// Debug: Log the data being updated
error_log("Update CPA Report Handler - Data to update: " . json_encode($data));

// Optional CPA fields
$optionalFields = [
    'date_received',
    'summary',
    'signed_date',
    'signed_by',    
    'remarks'
];

foreach ($optionalFields as $field) {
    $data[$field] = clean($_POST[$field] ?? '', '');
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Build dynamic UPDATE query
    $setParts = [];
    foreach (array_keys($data) as $column) {
        $setParts[] = "`$column` = ?";
    }
    $setParts[] = "`updated_at` = NOW()";

    $sql = "UPDATE cpas SET " . implode(', ', $setParts) . " WHERE id = ?";

    error_log("Update CPA Report Handler - SQL: " . $sql);
    error_log("Update CPA Report Handler - Data values: " . json_encode(array_values($data)));

    $stmt = $conn->prepare($sql);
    $executeData = array_values($data);
    $executeData[] = $id;
    $stmt->execute($executeData);

    error_log("CPA updated for ID: {$id}, Study ID: {$studyId}");
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'CPA report updated successfully']);
    } else {
        // Check if record exists
        $checkStmt = $conn->prepare("SELECT id FROM cpas WHERE id = ?");
        $checkStmt->execute([$id]);
        $exists = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($exists) {
            echo json_encode(['success' => true, 'message' => 'No changes made to CPA report']);
        } else {
            echo json_encode(['success' => false, 'message' => 'CPA report not found']);
        }
    }

} catch (PDOException $e) {
    error_log("Error updating CPA: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("Error updating CPA: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
