<?php
/**
 * Fetch Notifications Handler
 * Handles AJAX requests to fetch notifications from the database
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
    $role = $_SESSION['role'] ?? 'admin';
    
    // Get notifications for the current user
    // For admins, get notifications sent directly to them OR notifications for admin role
    $limit = (int)($_GET['limit'] ?? 50);
    $offset = (int)($_GET['offset'] ?? 0);
    
    $sql = "
        SELECT 
            id,
            title,
            message,
            details,
            type,
            is_read,
            action_url,
            action_text,
            action_type,
            actions_json,
            priority,
            created_at
        FROM notifications 
        WHERE user_id = ? 
           OR role = 'admin' 
           OR (role IS NULL AND user_id IN (SELECT id FROM users WHERE role IN ('admin', 'super_admin')))
        ORDER BY created_at DESC, priority DESC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(1, $userId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format notifications for the JavaScript
    $formattedNotifications = [];
    foreach ($notifications as $notif) {
        // Calculate relative time
        $createdAt = new DateTime($notif['created_at']);
        $now = new DateTime();
        $interval = $createdAt->diff($now);
        
        if ($interval->days > 0) {
            $timeAgo = $interval->days == 1 ? 'Yesterday' : $interval->days . ' days ago';
        } elseif ($interval->h > 0) {
            $timeAgo = $interval->h == 1 ? '1 hour ago' : $interval->h . ' hours ago';
        } elseif ($interval->i > 0) {
            $timeAgo = $interval->i == 1 ? '1 minute ago' : $interval->i . ' minutes ago';
        } else {
            $timeAgo = 'Just now';
        }
        
        // Parse actions from JSON or use action fields
        $actions = [];
        if (!empty($notif['actions_json'])) {
            $actions = json_decode($notif['actions_json'], true) ?? [];
        } elseif (!empty($notif['action_text'])) {
            $actions[] = [
                'text' => $notif['action_text'],
                'primary' => true,
                'action' => $notif['action_type'] ?? 'view'
            ];
        }
        
        $formattedNotifications[] = [
            'id' => (int)$notif['id'],
            'title' => htmlspecialchars($notif['title'] ?? ''),
            'message' => htmlspecialchars($notif['message'] ?? ''),
            'time' => $timeAgo,
            'read' => (bool)$notif['is_read'],
            'type' => $notif['type'] ?? 'general',
            'details' => htmlspecialchars($notif['details'] ?? ''),
            'actions' => $actions,
            'action_url' => $notif['action_url'] ?? null,
            'priority' => $notif['priority'] ?? 'normal'
        ];
    }
    
    // Get unread count
    $countSql = "
        SELECT COUNT(*) as unread_count
        FROM notifications 
        WHERE (user_id = ? OR role = 'admin')
          AND is_read = 0
    ";
    $countStmt = $conn->prepare($countSql);
    $countStmt->execute([$userId]);
    $unreadCount = $countStmt->fetch(PDO::FETCH_ASSOC)['unread_count'] ?? 0;
    
    echo json_encode([
        'status' => 'success',
        'notifications' => $formattedNotifications,
        'unread_count' => (int)$unreadCount,
        'total' => count($formattedNotifications)
    ]);
    
} catch (PDOException $e) {
    error_log("Error fetching notifications: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch notifications'
    ]);
} catch (Exception $e) {
    error_log("Error in fetch_notifications: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An unexpected error occurred'
    ]);
}
