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

    // Check if fetching single record by ID
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $conn->prepare("SELECT id, meeting_date, irb_code FROM irb_meetings WHERE id = ?");
        $stmt->execute([$id]);
        $irbMeeting = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($irbMeeting) {
            echo json_encode(['status' => 'success', 'data' => $irbMeeting]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Not found']);
        }
        exit;
    }

    // Fetch all irb meetings
    $stmt = $conn->prepare("SELECT id, meeting_date, irb_code FROM irb_meetings ORDER BY meeting_date DESC");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log(__FILE__ . ": Fetched " . count($results) . " records");

    echo json_encode(['status' => 'success', 'data' => $results]);

} catch (PDOException $e) {
    error_log(__FILE__ . " - Database error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
