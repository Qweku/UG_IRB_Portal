<?php
require_once '../../includes/functions/helpers.php';
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['department_name'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$department_name = trim($data['department_name']);
$zip = trim($data['zip']);
$state = trim($data['state']);
$city = trim($data['city']);
$address_line_1 = trim($data['address_line_1']);
$address_line_2 = trim($data['address_line_2']);
$site = trim($data['site']);

if (empty($department_name)) {
    echo json_encode(['success' => false, 'message' => 'Department name cannot be empty']);
    exit;
}

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO department_groups (department_name, zip, state, city, address_line_1, address_line_2, site) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$department_name, $zip, $state, $city, $address_1, $address_2, $site]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add departments']);
}
?>