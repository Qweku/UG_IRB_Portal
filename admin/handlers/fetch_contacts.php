<?php
require_once '../includes/auth_check.php';
require_once '../../includes/functions/helpers.php';

header('Content-Type: application/json');

// Require authentication
require_auth();

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    }

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

    error_log(__FILE__ . ": Fetched " . $stmt->rowCount() . " contacts from database.");

    echo json_encode([
        'success' => true,
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
} catch (PDOException $e) {
    error_log(__FILE__ . " - Database error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Unable to fetch contacts'
    ]);
}
