
<?php
require_once '../includes/auth_check.php';
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

// Debug: Log incoming POST data
error_log("CPA Report Handler - POST data: " . json_encode($_POST));

// Check if CPA section was submitted at all
if (empty($_POST['cpa_type']) || empty($_POST['pre_action_meeting']) || !isset($_POST['signed']) 
    || empty($_POST['date_of_change']) 
) {
    error_log("CPA Report Handler - Missing required fields");
    echo json_encode(['success' => false, 'message' => 'Missing required CPA fields']);
    exit;
}

function clean(?string $value, string $default = ''): string
{
    $value = isset($value) ? trim($value) : '';
    return $value === '' ? $default : $value;
}
function generateCPANumber(){
    // Generate a sequential number beginning with 0001 for each new CPA report
    try {
        $db = new Database();
        $conn = $db->connect();

        if (!$conn) {
            throw new Exception("Database connection failed");
        }

        $stmt = $conn->query("SELECT COUNT(*) AS count FROM cpas");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $result['count'] ?? 0;

        // Increment count and format as 4-digit number
        return str_pad($count + 1, 4, '0', STR_PAD_LEFT);

    } catch (Exception $e) {
        error_log("Error generating CPA number: " . $e->getMessage());
        return '0001'; // Fallback to default if error occurs
    }
}

$studyId = $_POST['protocol_id'] ?? $_POST['study_id'] ?? null;
$signedDate = $_POST['signed_date'] ?? date('Y-m-d'); // Default to today's date if not provided
$ref_number = $_POST['ref_number'] ?? null;
$expedited = isset($_POST['expedited']) ? 1 : 0;
$placedOnAgenda = isset($_POST['place_on_agenda']) ? 1 : 0;
$cpa_number =  generateCPANumber();
if (!$studyId) {
    error_log("CPA Report Handler - Missing study ID");
    echo json_encode(['success' => false, 'message' => 'Missing study ID']);
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
    'reference_number' => $ref_number,
    'cpa_number' => $cpa_number,
    'expedited' => $expedited,
    'place_on_agenda' => $placedOnAgenda,
    'signed_date' => $signedDate
];

foreach ($required as $postKey => $dbKey) {
    $value = trim($_POST[$postKey] ?? '');
    if ($value === '') {
        throw new Exception("Missing required CPA field: $postKey");
    }
    $data[$dbKey] = $value;
}

// Debug: Log the data being inserted
error_log("CPA Report Handler - Data to insert: " . json_encode($data));

// Optional CPA fields
$optionalFields = [
    'date_of_change',
    'date_received',
    'summary',
    'signed_by',    
    'remarks'
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
        INSERT INTO cpas (" . implode(',', $columns) . ", created_at, updated_at)
        VALUES (" . implode(',', $placeholders) . ", NOW(), NOW())
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute(array_values($data));



    error_log("CPA added for study ID: {$studyId}");
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add CPA']);
    }
} catch (Exception $e) {
    error_log("Error adding CPA: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
