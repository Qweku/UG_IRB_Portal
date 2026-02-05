<?php
require_once '../../includes/functions/helpers.php';
require_once '../includes/auth_check.php';
header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->connect();

    $query = isset($_GET['q']) ? trim($_GET['q']) : '';
    if (!empty($query)) {
        $stmt = $conn->prepare("
            SELECT
                id,
                CONCAT_WS(' ', title, first_name, last_name, suffix, company_dept_name) AS name,
                title,
                email,
                main_phone,
                contact_type
            FROM contacts
            WHERE CONCAT_WS(' ', title, first_name, last_name, suffix, company_dept_name) LIKE ?
            ORDER BY first_name ASC
            LIMIT 20
        ");
        $stmt->execute(['%' . $query . '%']);
    } else {
        $stmt = $conn->prepare("
            SELECT
                id,
                CONCAT_WS(' ', title, first_name, last_name, suffix, company_dept_name) AS name,
                title,
                email,
                main_phone,
                contact_type
            FROM contacts
            ORDER BY first_name ASC
            LIMIT 20
        ");
        $stmt->execute();
    }

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
