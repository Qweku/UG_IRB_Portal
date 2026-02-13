<?php
require_once '../../includes/functions/helpers.php';
require_once '../includes/auth_check.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$role = $_GET['role'] ?? null;
$study_id = $_GET['study_id'] ?? null;

error_log("Requested role: " . var_export($role, true));
error_log("Requested study_id: " . var_export($study_id, true));

if (!$role || !$study_id || !is_numeric($study_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid role or study ID']);
    exit;
}

$role = strtolower(trim($role));
$study_id = (int)$study_id;

error_log("Sanitized role: " . $role);
error_log("Sanitized study_id: " . $study_id);

// Map role to column name
$roleColumns = [
    'pi' => 'pi',
    'reviewers' => 'reviewers',
    'admins' => 'admins',
    'coordinators' => 'cols'
];

if (!array_key_exists($role, $roleColumns)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid role']);
    exit;
}

$column = $roleColumns[$role];

error_log("Fetching names from column: " . $column);

$db = new Database();
$conn = $db->connect();
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT $column FROM studies WHERE id = ?");
    $stmt->execute([$study_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        http_response_code(404);
        echo json_encode(['error' => 'Study not found']);
        exit;
    }

    $namesString = $result[$column] ?? '';

    if ($role === 'pi') {
        $names = $namesString ? [$namesString] : [];
    } else {
        $names = array_filter(array_map('trim', explode(',', $namesString)));
    }

    echo json_encode(['names' => array_values($names)]);
} catch (PDOException $e) {
    error_log("Error fetching staff names: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>