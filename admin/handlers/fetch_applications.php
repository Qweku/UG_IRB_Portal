<?php
require_once '../includes/auth_check.php';
require_once '../../includes/functions/helpers.php';

header('Content-Type: application/json');

// Admin-only access check
if (!isset($_SESSION['logged_in']) || !isset($_SESSION['role']) || 
    ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'super_admin')) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();
    
    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    }
    
    $status = $_POST['status'] ?? '';
    $dateFrom = $_POST['date_from'] ?? '';
    $dateTo = $_POST['date_to'] ?? '';
    $search = $_POST['search'] ?? '';
    $page = (int)($_POST['page'] ?? 1);
    $limit = (int)($_POST['limit'] ?? 10);
    $offset = ($page - 1) * $limit;
    
    // Build WHERE clause
    $whereConditions = [];
    $params = [];
    
    if (!empty($status)) {
        $whereConditions[] = "a.status = ?";
        $params[] = $status;
    }
    
    if (!empty($dateFrom)) {
        $whereConditions[] = "a.submission_date >= ?";
        $params[] = $dateFrom;
    }
    
    if (!empty($dateTo)) {
        $whereConditions[] = "a.submission_date <= ?";
        $params[] = $dateTo;
    }
    
    if (!empty($search)) {
        $whereConditions[] = "(a.protocol_number LIKE ? OR a.study_title LIKE ? OR a.pi_name LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM applications a $whereClause";
    $stmt = $conn->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetchColumn();
    
    // Get applications with assigned reviewers
    $sql = "
        SELECT 
            a.*,
            GROUP_CONCAT(DISTINCT CONCAT(u.full_name, ':', ar.id) SEPARATOR '|') as assigned_reviewers
        FROM applications a
        LEFT JOIN application_reviews ar ON a.id = ar.application_id
        LEFT JOIN users u ON ar.reviewer_id = u.id
        $whereClause
        GROUP BY a.id
        ORDER BY a.updated_at DESC
        LIMIT $limit OFFSET $offset
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process assigned reviewers
    foreach ($applications as &$app) {
        if (!empty($app['assigned_reviewers'])) {
            $reviewers = [];
            foreach (explode('|', $app['assigned_reviewers']) as $reviewer) {
                $parts = explode(':', $reviewer);
                if (count($parts) == 2) {
                    $reviewers[] = ['name' => $parts[0], 'id' => $parts[1]];
                }
            }
            $app['assigned_reviewers'] = $reviewers;
        } else {
            $app['assigned_reviewers'] = [];
        }
    }
    
    // Get stats
    $stats = [
        'total' => $total,
        'pending' => getCountByStatus($conn, 'pending'),
        'assigned' => getCountByStatus($conn, 'assigned'),
        'reviewed' => getCountByStatus($conn, 'reviewed')
    ];
    
    echo json_encode([
        'status' => 'success',
        'data' => $applications,
        'stats' => $stats,
        'total' => $total,
        'page' => $page,
        'limit' => $limit
    ]);
    
} catch (PDOException $e) {
    error_log(__FILE__ . " - Database error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}

function getCountByStatus($conn, $status) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE status = ?");
    $stmt->execute([$status]);
    return $stmt->fetchColumn();
}
