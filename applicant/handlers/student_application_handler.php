<?php
/**
 * Student Application Handler
 * Handles form submission from student_application.php
 * Validates, sanitizes, and saves data to the database
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

// Set JSON response header for AJAX requests
header('Content-Type: application/json');

/**
 * Generate a unique protocol number
 * @param PDO $conn Database connection
 * @param string $applicationType Type of application (student, nmimr, non_nmimr)
 * @return string Generated protocol number
 */
function generateProtocolNumber($conn, $applicationType = 'student') {
    $year = date('Y');
    $prefix = 'STU'; // Default for student
    
    switch ($applicationType) {
        case 'nmimr':
            $prefix = 'NIRB';
            break;
        case 'non_nmimr':
            $prefix = 'EXT';
            break;
        default:
            $prefix = 'STU';
    }
    
    // Get the latest protocol number for this year and type
    $stmt = $conn->prepare(
        "SELECT protocol_number FROM student_applications 
         WHERE protocol_number LIKE :prefix 
         ORDER BY id DESC LIMIT 1"
    );
    $stmt->execute(['prefix' => $prefix . '-' . $year . '%']);
    $lastProtocol = $stmt->fetchColumn();
    
    if ($lastProtocol) {
        // Extract the sequence number and increment
        $parts = explode('-', $lastProtocol);
        $sequence = (int) end($parts);
        $newSequence = str_pad($sequence + 1, 4, '0', STR_PAD_LEFT);
    } else {
        $newSequence = '0001';
    }
    
    return $prefix . '-' . $year . '-' . $newSequence;
}

/**
 * Validate email format
 * @param string $email Email to validate
 * @return bool True if valid
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Sanitize string input
 * @param string $input Input string
 * @return string Sanitized string
 */
function sanitizeString($input) {
    if ($input === null) {
        return '';
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate required fields
 * @param array $data Form data
 * @return array Array of missing required fields
 */
function validateRequiredFields($data) {
    $required = [
        'study_title',
        'version_number',
        'student_department',
        'student_number',
        'supervisor1_name',
        'supervisor1_institution',
        'supervisor1_email',
        'research_type',
        'student_status',
        'study_duration_years',
        'study_start_date',
        'study_end_date',
        'abstract',
        'background',
        'methods',
        'ethical_considerations',
        'expected_outcome',
        'student_declaration_name',
        'student_declaration_date',
        'student_declaration_signature',
        'supervisor_declaration_name',
        'supervisor_declaration_date',
        'supervisor_declaration_signature'
    ];
    
    $missing = [];
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $missing[] = $field;
        }
    }
    
    return $missing;
}

/**
 * Validate file uploads
 * @param array $files Uploaded files
 * @return array Array of validation errors
 */
function validateFiles($files) {
    $errors = [];
    $requiredFiles = ['consent_form', 'data_instruments', 'approval_letter'];
    $allowedTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'image/jpeg',
        'image/png'
    ];
    $maxFileSize = 10 * 1024 * 1024; // 10MB
    
    foreach ($requiredFiles as $field) {
        if (!isset($files[$field]) || $files[$field]['error'] === UPLOAD_ERR_NO_FILE) {
            $errors[] = "Required file missing: " . str_replace('_', ' ', ucfirst($field));
        }
    }
    
    // Check file types and size for uploaded files
    foreach ($files as $field => $file) {
        if ($file['error'] !== UPLOAD_ERR_NO_FILE && $file['error'] !== UPLOAD_ERR_OK) {
            if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
                $errors[] = "File too large: " . str_replace('_', ' ', ucfirst($field)) . " (max 10MB)";
            } else {
                $errors[] = "Upload error for: " . str_replace('_', ' ', ucfirst($field));
            }
            continue;
        }
        
        if ($file['error'] === UPLOAD_ERR_OK) {
            // Check file size
            if ($file['size'] > $maxFileSize) {
                $errors[] = "File too large: " . str_replace('_', ' ', ucfirst($field)) . " (max 10MB)";
            }
            
            // Check file type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                $errors[] = "Invalid file type: " . str_replace('_', ' ', ucfirst($field)) . " (PDF, DOC, DOCX, XLS, XLSX, JPG, PNG only)";
            }
        }
    }
    
    return $errors;
}

/**
 * Handle file upload
 * @param array $file Uploaded file array
 * @param string $fieldName Field name for naming
 * @param string $uploadDir Upload directory
 * @return array ['success' => bool, 'path' => string or null, 'error' => string or null]
 */
function handleFileUpload($file, $fieldName, $uploadDir) {
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => true, 'path' => null, 'error' => null];
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'path' => null, 'error' => 'Upload error: ' . $file['error']];
    }
    
    // Create upload directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate safe filename
    $originalName = pathinfo($file['name'], PATHINFO_FILENAME);
    $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName);
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $timestamp = time();
    $newFilename = $safeName . '_' . $timestamp . '.' . $extension;
    $destination = $uploadDir . DIRECTORY_SEPARATOR . $newFilename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'path' => $destination, 'error' => null];
    } else {
        return ['success' => false, 'path' => null, 'error' => 'Failed to move uploaded file'];
    }
}

/**
 * Handle multiple file uploads
 * @param array $files Array of uploaded files
 * @param string $fieldName Field name
 * @param string $uploadDir Upload directory
 * @return array ['success' => bool, 'paths' => array or null, 'error' => string or null]
 */
function handleMultipleFileUploads($files, $fieldName, $uploadDir) {
    if (!isset($files['name']) || empty($files['name'][0])) {
        return ['success' => true, 'paths' => [], 'error' => null];
    }
    
    $uploadedPaths = [];
    
    // Create upload directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $fileCount = count($files['name']);
    for ($i = 0; $i < $fileCount; $i++) {
        $singleFile = [
            'name' => $files['name'][$i],
            'type' => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error' => $files['error'][$i],
            'size' => $files['size'][$i]
        ];
        
        $result = handleFileUpload($singleFile, $fieldName . '_' . $i, $uploadDir);
        
        if (!$result['success']) {
            return ['success' => false, 'paths' => null, 'error' => $result['error']];
        }
        
        if ($result['path'] !== null) {
            $uploadedPaths[] = $result['path'];
        }
    }
    
    return ['success' => true, 'paths' => $uploadedPaths, 'error' => null];
}

/**
 * Main submission handler
 */
function handleSubmission() {
    // Verify applicant is logged in
    if (!is_applicant_logged_in()) {
        return [
            'success' => false,
            'message' => 'You must be logged in to submit an application.',
            'redirect' => '/login'
        ];
    }
    
    // Get user ID from session
    $userId = $_SESSION['user_id'] ?? 0;
    
    if ($userId === 0) {
        return [
            'success' => false,
            'message' => 'User session not found. Please log in again.'
        ];
    }
    
    // Verify CSRF token
    $csrfToken = $_POST['csrf_token'] ?? '';
    $sessionCsrfToken = $_SESSION['csrf_token'] ?? '';
    
    if (!hash_equals($sessionCsrfToken, $csrfToken)) {
        return [
            'success' => false,
            'message' => 'Invalid CSRF token. Please refresh the page and try again.'
        ];
    }
    
    // Get application type
    $applicationType = $_POST['application_type'] ?? 'student';
    
    // Connect to database
    $db = new Database();
    $conn = $db->connect();
    
    if (!$conn) {
        return [
            'success' => false,
            'message' => 'Database connection failed. Please try again later.'
        ];
    }
    
    // Sanitize and collect form data
    $data = [];
    $textFields = [
        'protocol_number', 'version_number', 'study_title',
        'student_department', 'student_address', 'student_number',
        'supervisor1_name', 'supervisor1_institution', 'supervisor1_address', 'supervisor1_phone', 'supervisor1_email',
        'supervisor2_name', 'supervisor2_institution', 'supervisor2_address', 'supervisor2_phone', 'supervisor2_email',
        'research_type', 'research_type_other', 'student_status',
        'study_duration_years', 'study_start_date', 'study_end_date',
        'funding_sources', 'prior_irb_review', 'collaborating_institutions',
        'abstract', 'background', 'methods', 'ethical_considerations',
        'expected_outcome', 'key_references', 'work_plan', 'budget',
        'student_declaration_name', 'student_declaration_date', 'student_declaration_signature',
        'supervisor_declaration_name', 'supervisor_declaration_date', 'supervisor_declaration_signature'
    ];
    
    foreach ($textFields as $field) {
        $data[$field] = sanitizeString($_POST[$field] ?? '');
    }
    
    // Handle declarations (checkboxes)
    $data['declarations'] = $_POST['declarations'] ?? [];
    if (!is_array($data['declarations'])) {
        $data['declarations'] = [];
    }
    
    // Validate required fields
    $missingFields = validateRequiredFields($data);
    if (!empty($missingFields)) {
        return [
            'success' => false,
            'message' => 'Please fill in all required fields.',
            'errors' => ['missing_fields' => $missingFields]
        ];
    }
    
    // Validate declarations (all 5 must be checked)
    $requiredDeclarations = ['1', '2', '3', '4', '5'];
    $missingDeclarations = array_diff($requiredDeclarations, $data['declarations']);
    if (!empty($missingDeclarations)) {
        return [
            'success' => false,
            'message' => 'You must agree to all declaration statements.'
        ];
    }
    
    // Validate email formats
    $emails = [
        'supervisor1_email' => $data['supervisor1_email'],
        'student_email' => $_POST['student_email'] ?? '',
        'supervisor2_email' => $data['supervisor2_email']
    ];
    
    foreach ($emails as $field => $email) {
        if (!empty($email) && !isValidEmail($email)) {
            return [
                'success' => false,
                'message' => 'Invalid email format for ' . str_replace('_', ' ', $field)
            ];
        }
    }
    
    // Validate date format and logical consistency
    $startDate = strtotime($data['study_start_date']);
    $endDate = strtotime($data['study_end_date']);
    
    if ($startDate === false || $endDate === false) {
        return [
            'success' => false,
            'message' => 'Invalid date format for study dates.'
        ];
    }
    
    if ($endDate <= $startDate) {
        return [
            'success' => false,
            'message' => 'End date must be after start date.'
        ];
    }
    
    // Validate version number format (e.g., 1.0, 2.1)
    if (!preg_match('/^\d+(\.\d+)?$/', $data['version_number'])) {
        return [
            'success' => false,
            'message' => 'Invalid version number format. Use format like 1.0 or 2.1'
        ];
    }
    
    // Handle file uploads
    $uploadBaseDir = '../../uploads/student_applications/' . date('Y/m');
    $uploadResult = [];
    $uploadErrors = [];
    
    // Required single file uploads
    $singleFileFields = [
        'consent_form' => 'Consent Form',
        'data_instruments' => 'Data Collection Instruments',
        'approval_letter' => 'Approval Letter',
        'assent_form' => 'Assent Form',
        'collaboration_letter' => 'Collaboration Letter'
    ];
    
    foreach ($singleFileFields as $field => $label) {
        if (isset($_FILES[$field])) {
            $result = handleFileUpload($_FILES[$field], $field, $uploadBaseDir);
            if (!$result['success']) {
                if (in_array($field, ['consent_form', 'data_instruments', 'approval_letter']) || 
                    ($result['error'] && strpos($result['error'], 'required') === false)) {
                    $uploadErrors[] = $result['error'];
                }
            }
            $uploadResult[$field] = $result['path'];
        } else {
            $uploadResult[$field] = null;
        }
    }
    
    // Handle additional documents (multiple files)
    if (isset($_FILES['additional_documents'])) {
        $result = handleMultipleFileUploads($_FILES['additional_documents'], 'additional_documents', $uploadBaseDir);
        if (!$result['success']) {
            $uploadErrors[] = $result['error'];
        }
        $uploadResult['additional_documents'] = json_encode($result['paths'] ?? []);
    } else {
        $uploadResult['additional_documents'] = json_encode([]);
    }
    
    if (!empty($uploadErrors)) {
        return [
            'success' => false,
            'message' => 'File upload errors: ' . implode('; ', $uploadErrors)
        ];
    }
    
    // Generate protocol number if not provided or empty
    $protocolNumber = $data['protocol_number'];
    if (empty($protocolNumber)) {
        $protocolNumber = generateProtocolNumber($conn, $applicationType);
    }
    
    // Prepare data for database insertion
    $declarationsJson = json_encode($data['declarations']);
    
    // Get student info from session
    $studentName = sanitizeString($_POST['student_name'] ?? '');
    $studentEmail = sanitizeString($_POST['student_email'] ?? '');
    $studentInstitution = sanitizeString($_POST['student_institution'] ?? '');
    $studentPhone = sanitizeString($_POST['student_phone'] ?? '');
    
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        // Insert into student_applications table
        $sql = "
            INSERT INTO student_applications (
                applicant_id, protocol_number, version_number, study_title,
                student_name, student_institution, student_department, student_address,
                student_number, student_phone, student_email,
                supervisor1_name, supervisor1_institution, supervisor1_address,
                supervisor1_phone, supervisor1_email,
                supervisor2_name, supervisor2_institution, supervisor2_address,
                supervisor2_phone, supervisor2_email,
                research_type, research_type_other, student_status,
                study_duration_years, study_start_date, study_end_date,
                funding_sources, prior_irb_review, collaborating_institutions,
                approval_letter, collaboration_letter,
                abstract, background, methods, ethical_considerations,
                expected_outcome, key_references, work_plan, budget,
                consent_form, assent_form, data_instruments,
                additional_documents, declarations,
                student_declaration_name, student_declaration_date, student_declaration_signature,
                supervisor_declaration_name, supervisor_declaration_date, supervisor_declaration_signature,
                application_type, status, created_at, updated_at
            ) VALUES (
                :applicant_id, :protocol_number, :version_number, :study_title,
                :student_name, :student_institution, :student_department, :student_address,
                :student_number, :student_phone, :student_email,
                :supervisor1_name, :supervisor1_institution, :supervisor1_address,
                :supervisor1_phone, :supervisor1_email,
                :supervisor2_name, :supervisor2_institution, :supervisor2_address,
                :supervisor2_phone, :supervisor2_email,
                :research_type, :research_type_other, :student_status,
                :study_duration_years, :study_start_date, :study_end_date,
                :funding_sources, :prior_irb_review, :collaborating_institutions,
                :approval_letter, :collaboration_letter,
                :abstract, :background, :methods, :ethical_considerations,
                :expected_outcome, :key_references, :work_plan, :budget,
                :consent_form, :assent_form, :data_instruments,
                :additional_documents, :declarations,
                :student_declaration_name, :student_declaration_date, :student_declaration_signature,
                :supervisor_declaration_name, :supervisor_declaration_date, :supervisor_declaration_signature,
                :application_type, :status, NOW(), NOW()
            )
        ";
        
        $stmt = $conn->prepare($sql);
        
        // Bind all parameters
        $params = [
            ':applicant_id' => $userId,
            ':protocol_number' => $protocolNumber,
            ':version_number' => $data['version_number'],
            ':study_title' => $data['study_title'],
            ':student_name' => $studentName,
            ':student_institution' => $studentInstitution,
            ':student_department' => $data['student_department'],
            ':student_address' => $data['student_address'],
            ':student_number' => $data['student_number'],
            ':student_phone' => $studentPhone,
            ':student_email' => $studentEmail,
            ':supervisor1_name' => $data['supervisor1_name'],
            ':supervisor1_institution' => $data['supervisor1_institution'],
            ':supervisor1_address' => $data['supervisor1_address'],
            ':supervisor1_phone' => $data['supervisor1_phone'],
            ':supervisor1_email' => $data['supervisor1_email'],
            ':supervisor2_name' => $data['supervisor2_name'],
            ':supervisor2_institution' => $data['supervisor2_institution'],
            ':supervisor2_address' => $data['supervisor2_address'],
            ':supervisor2_phone' => $data['supervisor2_phone'],
            ':supervisor2_email' => $data['supervisor2_email'],
            ':research_type' => $data['research_type'],
            ':research_type_other' => $data['research_type_other'],
            ':student_status' => $data['student_status'],
            ':study_duration_years' => $data['study_duration_years'],
            ':study_start_date' => $data['study_start_date'],
            ':study_end_date' => $data['study_end_date'],
            ':funding_sources' => $data['funding_sources'],
            ':prior_irb_review' => $data['prior_irb_review'],
            ':collaborating_institutions' => $data['collaborating_institutions'],
            ':approval_letter' => $uploadResult['approval_letter'],
            ':collaboration_letter' => $uploadResult['collaboration_letter'],
            ':abstract' => $data['abstract'],
            ':background' => $data['background'],
            ':methods' => $data['methods'],
            ':ethical_considerations' => $data['ethical_considerations'],
            ':expected_outcome' => $data['expected_outcome'],
            ':key_references' => $data['key_references'],
            ':work_plan' => $data['work_plan'],
            ':budget' => $data['budget'],
            ':consent_form' => $uploadResult['consent_form'],
            ':assent_form' => $uploadResult['assent_form'],
            ':data_instruments' => $uploadResult['data_instruments'],
            ':additional_documents' => $uploadResult['additional_documents'],
            ':declarations' => $declarationsJson,
            ':student_declaration_name' => $data['student_declaration_name'],
            ':student_declaration_date' => $data['student_declaration_date'],
            ':student_declaration_signature' => $data['student_declaration_signature'],
            ':supervisor_declaration_name' => $data['supervisor_declaration_name'],
            ':supervisor_declaration_date' => $data['supervisor_declaration_date'],
            ':supervisor_declaration_signature' => $data['supervisor_declaration_signature'],
            ':application_type' => $applicationType,
            ':status' => 'submitted'
        ];
        
        $stmt->execute($params);
        
        $applicationId = $conn->lastInsertId();
        
        // Commit transaction
        $conn->commit();
        
        // Log the submission
        error_log("Student application submitted successfully. ID: $applicationId, Protocol: $protocolNumber, User: $userId");
        
        return [
            'success' => true,
            'message' => 'Your application has been submitted successfully!',
            'protocol_number' => $protocolNumber,
            'application_id' => $applicationId,
            'redirect' => '/applicant-dashboard?success=submitted&protocol=' . urlencode($protocolNumber)
        ];
        
    } catch (PDOException $e) {
        // Rollback transaction
        $conn->rollBack();
        
        // Log error
        error_log("Database error in student_application_handler.php: " . $e->getMessage());
        
        return [
            'success' => false,
            'message' => 'An error occurred while saving your application. Please try again.'
        ];
    }
}

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'submit';
    
    if ($action === 'save_draft') {
        $result = handleSaveDraft();
    } else {
        $result = handleSubmission();
    }
    
    echo json_encode($result);
    exit;
}

/**
 * Handle save draft request
 * @return array Result with success status and message
 */
function handleSaveDraft() {
    // Verify applicant is logged in
    $isLoggedIn = is_applicant_logged_in();
    
    if (!$isLoggedIn) {
        return [
            'success' => false,
            'message' => 'You must be logged in to save a draft.',
            'redirect' => '/login'
        ];
    }
    
    // Get user ID from session
    $userId = $_SESSION['user_id'] ?? 0;
    
    if ($userId === 0) {
        return [
            'success' => false,
            'message' => 'User session not found. Please log in again.'
        ];
    }
    
    // Verify CSRF token
    $csrfToken = $_POST['csrf_token'] ?? '';
    $sessionCsrfToken = $_SESSION['csrf_token'] ?? '';
    
    $csrfMatch = !empty($csrfToken) && !empty($sessionCsrfToken) && hash_equals($sessionCsrfToken, $csrfToken);
    
    if (!$csrfMatch) {
        return [
            'success' => false,
            'message' => 'Invalid CSRF token. Please refresh the page and try again.'
        ];
    }
    
    // Get application type
    $applicationType = $_POST['application_type'] ?? 'student';
    $applicationId = $_POST['application_id'] ?? 0;
    $currentStep = $_POST['current_step'] ?? 1;
    
    // Connect to database
    $db = new Database();
    $conn = $db->connect();
    
    if (!$conn) {
        return [
            'success' => false,
            'message' => 'Database connection failed. Please try again later.'
        ];
    }
    
    // Sanitize and collect form data
    $data = [];
    $textFields = [
        'protocol_number', 'version_number', 'study_title',
        'student_department', 'student_address', 'student_number',
        'supervisor1_name', 'supervisor1_institution', 'supervisor1_address', 'supervisor1_phone', 'supervisor1_email',
        'supervisor2_name', 'supervisor2_institution', 'supervisor2_address', 'supervisor2_phone', 'supervisor2_email',
        'research_type', 'research_type_other', 'student_status',
        'study_duration_years', 'study_start_date', 'study_end_date',
        'funding_sources', 'prior_irb_review', 'collaborating_institutions',
        'abstract', 'background', 'methods', 'ethical_considerations',
        'expected_outcome', 'key_references', 'work_plan', 'budget',
        'student_declaration_name', 'student_declaration_date', 'student_declaration_signature',
        'supervisor_declaration_name', 'supervisor_declaration_date', 'supervisor_declaration_signature'
    ];
    
    foreach ($textFields as $field) {
        $data[$field] = sanitizeString($_POST[$field] ?? '');
    }
    
    // ============================================
    // Provide default values for NOT NULL columns
    // ============================================
    
    // Numeric fields - use "1.0" as default for version_number
    if (empty($data['version_number'])) {
        $data['version_number'] = '1.0';
    }
    
    // Study duration - default to "1" if empty
    if (empty($data['study_duration_years'])) {
        $data['study_duration_years'] = '1';
    }
    
    // Date fields - use valid date format for draft saves
    $defaultDate = '2024-01-01';
    if (empty($data['study_start_date'])) {
        $data['study_start_date'] = $defaultDate;
    }
    if (empty($data['study_end_date'])) {
        $data['study_end_date'] = $defaultDate;
    }
    if (empty($data['student_declaration_date'])) {
        $data['student_declaration_date'] = $defaultDate;
    }
    if (empty($data['supervisor_declaration_date'])) {
        $data['supervisor_declaration_date'] = $defaultDate;
    }
    
    // Research type - default to "observational" if empty
    if (empty($data['research_type'])) {
        $data['research_type'] = 'observational';
    }
    
    // Student status - default to "active" if empty
    if (empty($data['student_status'])) {
        $data['student_status'] = 'active';
    }
    
    // Prior IRB review - default to "No" if empty
    if (empty($data['prior_irb_review'])) {
        $data['prior_irb_review'] = 'No';
    }
    
    // Provide defaults for supervisor fields that might have NOT NULL constraints
    $supervisorDefaults = [
        'supervisor1_name', 'supervisor1_institution', 'supervisor1_address', 'supervisor1_phone', 'supervisor1_email',
        'supervisor2_name', 'supervisor2_institution', 'supervisor2_address', 'supervisor2_phone', 'supervisor2_email',
        'funding_sources', 'collaborating_institutions', 'research_type_other',
        'abstract', 'background', 'methods', 'ethical_considerations',
        'expected_outcome', 'key_references', 'work_plan', 'budget',
        'student_declaration_name', 'student_declaration_signature',
        'supervisor_declaration_name', 'supervisor_declaration_signature'
    ];
    
    foreach ($supervisorDefaults as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            $data[$field] = '';
        }
    }
    
    // Handle declarations (checkboxes)
    $data['declarations'] = $_POST['declarations'] ?? [];
    if (!is_array($data['declarations'])) {
        $data['declarations'] = [];
    }
    
    // ============================================
    // Provide defaults for student info fields
    // ============================================
    $studentName = sanitizeString($_POST['student_name'] ?? '');
    $studentEmail = sanitizeString($_POST['student_email'] ?? '');
    $studentInstitution = sanitizeString($_POST['student_institution'] ?? '');
    $studentPhone = sanitizeString($_POST['student_phone'] ?? '');
    
    // Provide defaults for student info if empty (for NOT NULL compliance)
    if (empty($studentName)) {
        $studentName = 'Not Provided';
    }
    if (empty($studentEmail)) {
        $studentEmail = 'notprovided@example.com';
    }
    if (empty($studentInstitution)) {
        $studentInstitution = 'Not Provided';
    }
    if (empty($studentPhone)) {
        $studentPhone = 'Not Provided';
    }
    
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        // Generate protocol number if not provided
        $protocolNumber = $data['protocol_number'];
        if (empty($protocolNumber)) {
            $protocolNumber = generateProtocolNumber($conn, $applicationType);
        }
        
        // Handle file uploads (optional for draft)
        $uploadBaseDir = '../../uploads/student_applications/' . date('Y/m');
        $uploadResult = [];
        
        $singleFileFields = [
            'consent_form' => 'Consent Form',
            'data_instruments' => 'Data Collection Instruments',
            'approval_letter' => 'Approval Letter',
            'assent_form' => 'Assent Form',
            'collaboration_letter' => 'Collaboration Letter'
        ];
        
        foreach ($singleFileFields as $field => $label) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                $result = handleFileUpload($_FILES[$field], $field, $uploadBaseDir);
                $uploadResult[$field] = $result['success'] ? $result['path'] : null;
            } else {
                $uploadResult[$field] = null;
            }
        }
        
        // Provide default empty strings for ALL file upload fields to prevent NULL constraint violations
        $fileUploadFields = ['consent_form', 'data_instruments', 'approval_letter', 'assent_form', 'collaboration_letter'];
        foreach ($fileUploadFields as $field) {
            if (!isset($uploadResult[$field]) || $uploadResult[$field] === null) {
                $uploadResult[$field] = '';
            }
        }
        
        // Handle additional documents (multiple files)
        if (isset($_FILES['additional_documents']) && !empty($_FILES['additional_documents']['name'][0])) {
            $result = handleMultipleFileUploads($_FILES['additional_documents'], 'additional_documents', $uploadBaseDir);
            $uploadResult['additional_documents'] = $result['success'] ? json_encode($result['paths'] ?? []) : json_encode([]);
        } else {
            $uploadResult['additional_documents'] = json_encode([]);
        }
        
        $declarationsJson = json_encode($data['declarations']);
        
        // Prepare data for database
        $params = [
            ':applicant_id' => $userId,
            ':protocol_number' => $protocolNumber,
            ':version_number' => $data['version_number'],
            ':study_title' => $data['study_title'],
            ':student_name' => $studentName,
            ':student_institution' => $studentInstitution,
            ':student_department' => $data['student_department'],
            ':student_address' => $data['student_address'],
            ':student_number' => $data['student_number'],
            ':student_phone' => $studentPhone,
            ':student_email' => $studentEmail,
            ':supervisor1_name' => $data['supervisor1_name'],
            ':supervisor1_institution' => $data['supervisor1_institution'],
            ':supervisor1_address' => $data['supervisor1_address'],
            ':supervisor1_phone' => $data['supervisor1_phone'],
            ':supervisor1_email' => $data['supervisor1_email'],
            ':supervisor2_name' => $data['supervisor2_name'],
            ':supervisor2_institution' => $data['supervisor2_institution'],
            ':supervisor2_address' => $data['supervisor2_address'],
            ':supervisor2_phone' => $data['supervisor2_phone'],
            ':supervisor2_email' => $data['supervisor2_email'],
            ':research_type' => $data['research_type'],
            ':research_type_other' => $data['research_type_other'],
            ':student_status' => $data['student_status'],
            ':study_duration_years' => $data['study_duration_years'],
            ':study_start_date' => $data['study_start_date'],
            ':study_end_date' => $data['study_end_date'],
            ':funding_sources' => $data['funding_sources'],
            ':prior_irb_review' => $data['prior_irb_review'],
            ':collaborating_institutions' => $data['collaborating_institutions'],
            ':approval_letter' => $uploadResult['approval_letter'],
            ':collaboration_letter' => $uploadResult['collaboration_letter'],
            ':abstract' => $data['abstract'],
            ':background' => $data['background'],
            ':methods' => $data['methods'],
            ':ethical_considerations' => $data['ethical_considerations'],
            ':expected_outcome' => $data['expected_outcome'],
            ':key_references' => $data['key_references'],
            ':work_plan' => $data['work_plan'],
            ':budget' => $data['budget'],
            ':consent_form' => $uploadResult['consent_form'],
            ':assent_form' => $uploadResult['assent_form'],
            ':data_instruments' => $uploadResult['data_instruments'],
            ':additional_documents' => $uploadResult['additional_documents'],
            ':declarations' => $declarationsJson,
            ':student_declaration_name' => $data['student_declaration_name'],
            ':student_declaration_date' => $data['student_declaration_date'],
            ':student_declaration_signature' => $data['student_declaration_signature'],
            ':supervisor_declaration_name' => $data['supervisor_declaration_name'],
            ':supervisor_declaration_date' => $data['supervisor_declaration_date'],
            ':supervisor_declaration_signature' => $data['supervisor_declaration_signature'],
            ':application_type' => $applicationType,
            ':status' => 'draft',
            ':current_step' => $currentStep
        ];
        
        if ($applicationId > 0) {
            // Update existing draft
            $sql = "
                UPDATE student_applications SET
                    protocol_number = :protocol_number,
                    version_number = :version_number,
                    study_title = :study_title,
                    student_name = :student_name,
                    student_institution = :student_institution,
                    student_department = :student_department,
                    student_address = :student_address,
                    student_number = :student_number,
                    student_phone = :student_phone,
                    student_email = :student_email,
                    supervisor1_name = :supervisor1_name,
                    supervisor1_institution = :supervisor1_institution,
                    supervisor1_address = :supervisor1_address,
                    supervisor1_phone = :supervisor1_phone,
                    supervisor1_email = :supervisor1_email,
                    supervisor2_name = :supervisor2_name,
                    supervisor2_institution = :supervisor2_institution,
                    supervisor2_address = :supervisor2_address,
                    supervisor2_phone = :supervisor2_phone,
                    supervisor2_email = :supervisor2_email,
                    research_type = :research_type,
                    research_type_other = :research_type_other,
                    student_status = :student_status,
                    study_duration_years = :study_duration_years,
                    study_start_date = :study_start_date,
                    study_end_date = :study_end_date,
                    funding_sources = :funding_sources,
                    prior_irb_review = :prior_irb_review,
                    collaborating_institutions = :collaborating_institutions,
                    approval_letter = :approval_letter,
                    collaboration_letter = :collaboration_letter,
                    abstract = :abstract,
                    background = :background,
                    methods = :methods,
                    ethical_considerations = :ethical_considerations,
                    expected_outcome = :expected_outcome,
                    key_references = :key_references,
                    work_plan = :work_plan,
                    budget = :budget,
                    consent_form = :consent_form,
                    assent_form = :assent_form,
                    data_instruments = :data_instruments,
                    additional_documents = :additional_documents,
                    declarations = :declarations,
                    student_declaration_name = :student_declaration_name,
                    student_declaration_date = :student_declaration_date,
                    student_declaration_signature = :student_declaration_signature,
                    supervisor_declaration_name = :supervisor_declaration_name,
                    supervisor_declaration_date = :supervisor_declaration_date,
                    supervisor_declaration_signature = :supervisor_declaration_signature,
                    application_type = :application_type,
                    status = :status,
                    current_step = :current_step,
                    updated_at = NOW()
                WHERE id = :id AND applicant_id = :applicant_id
            ";
            
            $stmt = $conn->prepare($sql);
            $params[':id'] = $applicationId;
            
            $stmt->execute($params);
            
            $rowsAffected = $stmt->rowCount();
            
            $newApplicationId = $applicationId;
        } else {
            // Insert new draft
            $sql = "
                INSERT INTO student_applications (
                    applicant_id, protocol_number, version_number, study_title,
                    student_name, student_institution, student_department, student_address,
                    student_number, student_phone, student_email,
                    supervisor1_name, supervisor1_institution, supervisor1_address,
                    supervisor1_phone, supervisor1_email,
                    supervisor2_name, supervisor2_institution, supervisor2_address,
                    supervisor2_phone, supervisor2_email,
                    research_type, research_type_other, student_status,
                    study_duration_years, study_start_date, study_end_date,
                    funding_sources, prior_irb_review, collaborating_institutions,
                    approval_letter, collaboration_letter,
                    abstract, background, methods, ethical_considerations,
                    expected_outcome, key_references, work_plan, budget,
                    consent_form, assent_form, data_instruments,
                    additional_documents, declarations,
                    student_declaration_name, student_declaration_date, student_declaration_signature,
                    supervisor_declaration_name, supervisor_declaration_date, supervisor_declaration_signature,
                    application_type, status, current_step, created_at, updated_at
                ) VALUES (
                    :applicant_id, :protocol_number, :version_number, :study_title,
                    :student_name, :student_institution, :student_department, :student_address,
                    :student_number, :student_phone, :student_email,
                    :supervisor1_name, :supervisor1_institution, :supervisor1_address,
                    :supervisor1_phone, :supervisor1_email,
                    :supervisor2_name, :supervisor2_institution, :supervisor2_address,
                    :supervisor2_phone, :supervisor2_email,
                    :research_type, :research_type_other, :student_status,
                    :study_duration_years, :study_start_date, :study_end_date,
                    :funding_sources, :prior_irb_review, :collaborating_institutions,
                    :approval_letter, :collaboration_letter,
                    :abstract, :background, :methods, :ethical_considerations,
                    :expected_outcome, :key_references, :work_plan, :budget,
                    :consent_form, :assent_form, :data_instruments,
                    :additional_documents, :declarations,
                    :student_declaration_name, :student_declaration_date, :student_declaration_signature,
                    :supervisor_declaration_name, :supervisor_declaration_date, :supervisor_declaration_signature,
                    :application_type, :status, :current_step, NOW(), NOW()
                )
            ";
            
            $stmt = $conn->prepare($sql);
            
            $stmt->execute($params);
            
            $newApplicationId = $conn->lastInsertId();
        }
        
        // Commit transaction
        $conn->commit();
        
        // Log the draft save
        error_log("Student application draft saved. ID: $newApplicationId, Protocol: $protocolNumber, User: $userId");
        
        return [
            'success' => true,
            'message' => 'Draft saved successfully!',
            'protocol_number' => $protocolNumber,
            'application_id' => $newApplicationId,
            'current_step' => $currentStep
        ];
        
    } catch (PDOException $e) {
        // Rollback transaction
        $conn->rollBack();
        
        // Log error
        error_log("Database error in saveDraft(): " . $e->getMessage());
        
        return [
            'success' => false,
            'message' => 'An error occurred while saving your draft. Please try again.'
        ];
    }
}
