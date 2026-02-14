<?php
/**
 * Mark Notification Read Handler
 * Handles AJAX requests to mark a notification as read in the database
 */

require_once '../includes/auth_check.php';
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->connect();
    
    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    }
    
    $userId = $_SESSION['user_id'] ?? null;
    $notificationId = (int)($_POST['notification_id'] ?? 0);
    $markAll = (bool)($_POST['mark_all'] ?? false);
    
    if ($markAll) {
        // Mark all notifications as read for this user
        $sql = "UPDATE notifications SET is_read = 1, read_at = NOW() 
                WHERE (user_id = ? OR role = 'admin') AND is_read = 0";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$userId]);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'All notifications marked as read',
            'updated_count' => $stmt->rowCount()
        ]);
    } elseif ($notificationId > 0) {
        // Mark single notification as read
        $sql = "UPDATE notifications SET is_read = 1, read_at = NOW() 
                WHERE id = ? AND (user_id = ? OR role = 'admin') AND is_read = 0";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$notificationId, $userId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Notification marked as read'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Notification not found or already read'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid notification ID'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Error marking notification as read: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to mark notification as read'
    ]);
} catch (Exception $e) {
    error_log("Error in mark_notification_read: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An unexpected error occurred'
    ]);
}
