<?php
/**
 * Get Application Data Handler
 * Returns application data as JSON for editing existing applications
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once '../../config.php';
require_once '../../includes/config/database.php';
require_once '../../includes/functions/helpers.php';

// Set JSON response header
header('Content-Type: application/json');

// Check if applicant is logged in
if (!is_applicant_logged_in()) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in.'
    ]);
    exit;
}

// Get user ID from session
$userId = $_SESSION['user_id'] ?? 0;

if ($userId === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'User session not found.'
    ]);
    exit;
}

// Get application ID from query string
$applicationId = $_GET['id'] ?? 0;

if ($applicationId === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Application ID not provided.'
    ]);
    exit;
}

// Connect to database
$db = new Database();
$conn = $db->connect();

if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed.'
    ]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM applications WHERE id = ? AND applicant_id = ?");
    $stmt->execute([$applicationId, $userId]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$application) {
        echo json_encode([
            'success' => false,
            'message' => 'Application not found or access denied.'
        ]);
        exit;
    }
    
    // Get file names from paths for display
    $fileFields = [
        'approval_letter',
        'collaboration_letter',
        'consent_form',
        'assent_form',
        'data_instruments'
    ];
    
    $fileNames = [];
    foreach ($fileFields as $field) {
        $path = $application[$field] ?? '';
        if (!empty($path)) {
            $fileNames[$field] = basename($path);
        }
    }
    
    echo json_encode([
        'success' => true,
        'application' => $application,
        'file_names' => $fileNames
    ]);
    
} catch (PDOException $e) {
    error_log("Error fetching application data: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching application data.'
    ]);
}
