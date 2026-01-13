<?php
require_once '../../includes/functions/helpers.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$name = $_GET['name'] ?? null;

if (!$name) {
    http_response_code(400);
    echo json_encode(['error' => 'Name parameter is required']);
    exit;
}

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

try {
    // Split the name into last and first
    $nameParts = explode(' ', trim($name), 2);
    if (count($nameParts) < 2) {
        echo json_encode(['email' => '']);
        exit;
    }
    $lastName = $nameParts[0];
    $firstName = $nameParts[1];

    $stmt = $conn->prepare("SELECT email, title FROM contacts WHERE last_name = ? AND first_name = ? LIMIT 1");
    $stmt->execute([$lastName, $firstName]);
    $contact = $stmt->fetch(PDO::FETCH_ASSOC);

    $email = $contact ? $contact['email'] : '';
    $title = $contact ? $contact['title'] : '';

    echo json_encode(['email' => $email, 'title' => $title]);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>