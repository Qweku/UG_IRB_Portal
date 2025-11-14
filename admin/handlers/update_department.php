<?php
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id']) || !isset($data['department_name'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$id = $data['id'];
$department_name = trim($data['department_name']);
$address_line_1 = trim($data['address_line_1']);
$address_line_2 = trim($data['address_line_2']);
$site = trim($data['site']);
$department_id = trim($data['department_id']);
$city = trim($data['city']);
$state = trim($data['state']);
$zip = trim($data['zip']);


if (!is_numeric($id) || $id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}
if (empty($department_name)) {
    echo json_encode(['success' => false, 'message' => 'Department name cannot be empty']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Prepare the SQL statement with named parameters for better readability and maintainability
    $sql = "UPDATE department_groups SET department_name = :department_name, address_line_1 = :address_line_1, address_line_2 = :address_line_2, site = :site, department_id = :department_id, city = :city, state = :state, zip = :zip WHERE id = :id";
    $stmt = $conn->prepare($sql);

    // Bind parameters explicitly with types for security and performance
    $stmt->bindParam(':department_name', $department_name, PDO::PARAM_STR);
    $stmt->bindParam(':address_line_1', $address_line_1, PDO::PARAM_STR);
    $stmt->bindParam(':address_line_2', $address_line_2, PDO::PARAM_STR);
    $stmt->bindParam(':site', $site, PDO::PARAM_STR);
    $stmt->bindParam(':department_id', $department_id, PDO::PARAM_STR);
    $stmt->bindParam(':city', $city, PDO::PARAM_STR);
    $stmt->bindParam(':state', $state, PDO::PARAM_STR);
    $stmt->bindParam(':zip', $zip, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Execute the statement
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or record not found']);
    }

} catch (Exception $e) {
    error_log("Error updating cpa type: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>