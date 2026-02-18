<?php
/**
 * Get Draft Data Handler
 * Returns draft application data as JSON for auto-fill functionality
 */

// Define consistent session name across entire application
defined('CSRF_SESSION_NAME') || define('CSRF_SESSION_NAME', 'ug_irb_session');

// Set custom session name BEFORE starting session
session_name(CSRF_SESSION_NAME);

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

// Get application type from query string (optional)
$applicationType = $_GET['type'] ?? 'student';

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
    // Fetch the latest draft for this user
    // Order by created_at DESC to get the most recent draft
    $stmt = $conn->prepare("
        SELECT * FROM applications 
        WHERE applicant_id = ? 
        AND application_type = ?
        AND status = 'draft'
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$userId, $applicationType]);
    $draft = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$draft) {
        // Check if there are any submitted applications (don't auto-fill if already submitted)
        $stmt = $conn->prepare("
            SELECT COUNT(*) FROM applications 
            WHERE applicant_id = ? 
            AND status != 'draft'
        ");
        $stmt->execute([$userId]);
        $submittedCount = $stmt->fetchColumn();
        
        if ($submittedCount > 0) {
            // User has already submitted applications
            echo json_encode([
                'success' => true,
                'has_draft' => false,
                'message' => 'No draft found. User may have already submitted applications.'
            ]);
        } else {
            // No draft and no submissions - first time user
            echo json_encode([
                'success' => true,
                'has_draft' => false,
                'message' => 'No saved draft found.'
            ]);
        }
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
        $path = $draft[$field] ?? '';
        if (!empty($path)) {
            $fileNames[$field] = basename($path);
        }
    }
    
    // Handle additional_documents (JSON array)
    $additionalDocs = json_decode($draft['additional_documents'] ?? '[]', true);
    $additionalDocNames = [];
    foreach ($additionalDocs as $path) {
        if (!empty($path)) {
            $additionalDocNames[] = basename($path);
        }
    }
    
    // Decode declarations JSON
    $declarations = json_decode($draft['declarations'] ?? '[]', true);
    
    echo json_encode([
        'success' => true,
        'has_draft' => true,
        'draft' => [
            'id' => $draft['id'],
            'application_type' => $draft['application_type'],
            'status' => $draft['status'],
            'current_step' => $draft['current_step'],
            'protocol_number' => $draft['protocol_number'],
            'version_number' => $draft['version_number'],
            'study_title' => $draft['study_title'],
            'student_department' => $draft['student_department'],
            'student_address' => $draft['student_address'],
            'student_number' => $draft['student_number'],
            'supervisor1_name' => $draft['supervisor1_name'],
            'supervisor1_institution' => $draft['supervisor1_institution'],
            'supervisor1_address' => $draft['supervisor1_address'],
            'supervisor1_phone' => $draft['supervisor1_phone'],
            'supervisor1_email' => $draft['supervisor1_email'],
            'supervisor2_name' => $draft['supervisor2_name'],
            'supervisor2_institution' => $draft['supervisor2_institution'],
            'supervisor2_address' => $draft['supervisor2_address'],
            'supervisor2_phone' => $draft['supervisor2_phone'],
            'supervisor2_email' => $draft['supervisor2_email'],
            'research_type' => $draft['research_type'],
            'research_type_other' => $draft['research_type_other'],
            'student_status' => $draft['student_status'],
            'study_duration_years' => $draft['study_duration_years'],
            'study_start_date' => $draft['study_start_date'],
            'study_end_date' => $draft['study_end_date'],
            'funding_sources' => $draft['funding_sources'],
            'prior_irb_review' => $draft['prior_irb_review'],
            'collaborating_institutions' => $draft['collaborating_institutions'],
            'abstract' => $draft['abstract'],
            'background' => $draft['background'],
            'methods' => $draft['methods'],
            'ethical_considerations' => $draft['ethical_considerations'],
            'expected_outcome' => $draft['expected_outcome'],
            'key_references' => $draft['key_references'],
            'work_plan' => $draft['work_plan'],
            'budget' => $draft['budget'],
            'student_declaration_name' => $draft['student_declaration_name'],
            'student_declaration_date' => $draft['student_declaration_date'],
            'student_declaration_signature' => $draft['student_declaration_signature'],
            'supervisor_declaration_name' => $draft['supervisor_declaration_name'],
            'supervisor_declaration_date' => $draft['supervisor_declaration_date'],
            'supervisor_declaration_signature' => $draft['supervisor_declaration_signature'],
            'declarations' => $declarations
        ],
        'file_names' => $fileNames,
        'additional_documents' => $additionalDocNames,
        'created_at' => $draft['created_at'],
        'updated_at' => $draft['updated_at']
    ]);
    
} catch (PDOException $e) {
    error_log("Error fetching draft data: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching draft data.'
    ]);
}
