
<?php
require_once '../includes/auth_check.php';
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

// Debug: Log incoming POST data
error_log("SAE Report Handler - POST data: " . json_encode($_POST));

// Check if SAE section was submitted at all
if (empty($_POST['description']) || empty($_POST['type_of_event'])) {
    error_log("SAE Report Handler - Missing required fields");
    echo json_encode(['success' => false, 'message' => 'Missing required SAE fields']);
    exit;
}

function clean(?string $value, string $default = ''): string
{
    $value = isset($value) ? trim($value) : '';
    return $value === '' ? $default : $value;
}

$secondarySAE = isset($_POST['secondary_sae']) ? 1 : 0;
$medwatchReportFiled = isset($_POST['medwatch_report_filed']) ? 1 : 0;
$studyRelated = isset($_POST['study_related']) ? 1 : 0;
$localEvent = isset($_POST['local_event']) ? 1 : 0;
$risksAltered = isset($_POST['risks_altered']) ? 1 : 0;
$newConsentRequired = isset($_POST['new_consent_required']) ? 1 : 0;
$referenceNumber = $_POST['reference_number'] ?? null;
$studyId = $_POST['protocol_id'] ?? $_POST['study_id'] ?? null;
if (!$studyId) {
    error_log("SAE Report Handler - Missing study ID");
    echo json_encode(['success' => false, 'message' => 'Missing study ID']);
    exit;
}

// Required SAE fields - map form field names to database column names
$required = [
    'description' => 'description',
    'type_of_event' => 'type_of_event',
];

$data = [
    'protocol_id' => $studyId,
    'secondary_sae' => $secondarySAE,
    'medwatch_report_filed' => $medwatchReportFiled,
    'study_related' => $studyRelated,
    'local_event' => $localEvent,
    'risks_altered' => $risksAltered,
    'new_consent_required' => $newConsentRequired,
    'reference_number' => $referenceNumber,
];

foreach ($required as $postKey => $dbKey) {
    $value = trim($_POST[$postKey] ?? '');
    if ($value === '') {
        throw new Exception("Missing required SAE field: $postKey");
    }
    $data[$dbKey] = $value;
}

// Debug: Log the data being inserted
error_log("SAE Report Handler - Data to insert: " . json_encode($data));

// Optional SAE fields
$optionalFields = [
    'follow_up_report',
    'original_sae_number',
    'internal_sae_number',
    'ind_report_number',
    'medwatch_number',
    'location',
    'patient_status',
    'age',
    'sex',
    'patient_identifier',
    'date_of_event',
    'date_received',
    'date_pi_aware',
    'signed_by_pi',
    'date_signed',
];

foreach ($optionalFields as $field) {
    $data[$field] = clean($_POST[$field] ?? '', '');
}

// Build dynamic insert
$columns = array_keys($data);
$placeholders = array_fill(0, count($columns), '?');


try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $sql = "
        INSERT INTO saes (" . implode(',', $columns) . ", created_at, updated_at)
        VALUES (" . implode(',', $placeholders) . ", NOW(), NOW())
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute(array_values($data));



    error_log("SAE added for study ID: {$studyId}");
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add SAE']);
    }
} catch (Exception $e) {
    error_log("Error adding SAE: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

?>
