<?php
require_once '../includes/auth_check.php';
require_once '../../includes/functions/helpers.php';

header('Content-Type: application/json');

// Use centralized role check
require_role('admin');

try {
    $db = new Database();
    $conn = $db->connect();
    
    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    }
    
    // Get all users with reviewer role
    $stmt = $conn->prepare("
        SELECT 
            u.id,
            u.full_name,
            u.email,
           
            (SELECT COUNT(*) FROM application_reviews WHERE reviewer_id = u.id AND status = 'assigned') as active_reviews,
            (SELECT COUNT(*) FROM application_reviews WHERE reviewer_id = u.id AND status = 'completed') as completed_reviews
        FROM users u
        WHERE u.role = 'reviewer' AND u.status = 'active'
        ORDER BY u.full_name ASC
    ");
    $stmt->execute();
    $reviewers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'data' => $reviewers
    ]);
    
} catch (PDOException $e) {
    error_log(__FILE__ . " - Database error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
