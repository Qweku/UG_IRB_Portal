<?php
require_once '../../includes/functions/helpers.php';
header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->connect();

    $stmt = $conn->prepare("
        SELECT 
            id,
            CONCAT_WS(' ', title, first_name, last_name, suffix, company_dept_name) AS name,
            email,
            main_phone,
            contact_type
        FROM contacts
        ORDER BY first_name ASC
    ");
    $stmt->execute();

    error_log("Fetched " . $stmt->rowCount() . " contacts from database.");

    echo json_encode([
        'success' => true,
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Unable to fetch contacts'
    ]);
}
