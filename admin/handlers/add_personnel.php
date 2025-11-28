<?php
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['study_id']) || !isset($data['name'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit;
}

$study_id = $data['study_id'];
$name = trim($data['name']);
$role = trim($data['staffType']);
$title = trim($data['title']);
$start_date = $data['dateAdded'];
$company_name = trim($data['companyName']);
$email = trim($data['email']);
$phone = trim($data['mainPhone']);
$comments = trim($data['comments']);

if (!is_numeric($study_id) || $study_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid study ID']);
    exit;
}
if (empty($name)) {
    echo json_encode(['status' => 'error', 'message' => 'Name cannot be empty']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $stmt = $conn->prepare("INSERT INTO study_personnel (study_id, name, role, title, start_date, company_name, email, phone, comments) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$study_id, $name, $role, $title, $start_date, $company_name, $email, $phone, $comments]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Personnel added successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add personnel']);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
?>