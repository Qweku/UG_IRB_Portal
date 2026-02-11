<?php

/**
 * Non-NMIMR Application Handler
 * Handles form submission from non_nmimr_application.php
 * Validates, sanitizes, and saves data to the database
 * Schema: non_nmimr_applications, non_nmimr_co_investigators, non_nmimr_application_documents, non_nmimr_declarations
 */

// Define consistent session name across entire application
defined('CSRF_SESSION_NAME') || define('CSRF_SESSION_NAME', 'ug_irb_session');

// Set custom session name BEFORE starting session
session_name(CSRF_SESSION_NAME);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug logging for session debugging
error_log("Non-NMIMR Handler Session Debug: session_name=" . session_name() .
    ", session_id=" . session_id() .
    ", logged_in=" . (isset($_SESSION['logged_in']) ? $_SESSION['logged_in'] : 'NOT SET') .
    ", user_id=" . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET'));

// Include required files
require_once '../../config.php';
require_once '../../includes/config/database.php';
require_once '../../includes/functions/helpers.php';

// Set JSON response header for AJAX requests
header('Content-Type: application/json');

/**
 * Generate a unique protocol number for Non-NMIMR applications
 * Format: NIRB-YYYY-XXXX
 * @param PDO $conn Database connection
 * @return string Generated protocol number
 */
function generateNonNmimrProtocolNumber($conn)
{
    $year = date('Y');
    $prefix = 'NIRB';

    // Get the latest protocol number for this year and type
    $stmt = $conn->prepare(
        "SELECT protocol_number FROM non_nmimr_applications 
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
function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Sanitize string input
 * @param string $input Input string
 * @return string Sanitized string
 */
function sanitizeString($input)
{
    if ($input === null) {
        return '';
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate required fields for Non-NMIMR application submission
 * @param array $data Form data
 * @return array Array of missing required fields
 */
function validateNonNmimrRequiredFields($data)
{
    $required = [
        // Step 1: Protocol
        'protocol_number',
        'version_number',
        'study_title',

        // Step 2: Project Info (new individual PI fields)
        'pi_name',
        'pi_institution',
        'pi_address',
        'pi_phone_number',
        'pi_email',
        'funding_source',
        'research_type',
        'duration',

        // Step 3: Research Content
        'abstract',
        'introduction',
        'literature_review',
        'aims',
        'methodology',
        'ethical_considerations',
        'expected_outcomes',
        'references',

        // Step 4: Signatures
        'pi_name',
        'pi_signature',
        'pi_date'
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
 * Validate file uploads for Non-NMIMR application
 * @param array $files Uploaded files
 * @param bool $isSubmit Whether this is a final submission (files required) or draft
 * @return array Array of validation errors
 */
function validateNonNmimrFiles($files, $isSubmit = true)
{
    $errors = [];
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

    // For final submission, these files are required
    if ($isSubmit) {
        $requiredFiles = ['consent_form', 'final_pdf'];
        foreach ($requiredFiles as $field) {
            if (!isset($files[$field]) || $files[$field]['error'] === UPLOAD_ERR_NO_FILE) {
                $errors[] = "Required file missing: " . str_replace('_', ' ', ucfirst($field));
            }
        }
    }

    // Check file types and size for uploaded files
    foreach ($files as $field => $file) {
        if (isset($file['error']) && $file['error'] !== UPLOAD_ERR_NO_FILE && $file['error'] !== UPLOAD_ERR_OK) {
            if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
                $errors[] = "File too large: " . str_replace('_', ' ', ucfirst($field)) . " (max 10MB)";
            } else {
                $errors[] = "Upload error for: " . str_replace('_', ' ', ucfirst($field));
            }
            continue;
        }

        if (isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {
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
function handleNonNmimrFileUpload($file, $fieldName, $uploadDir)
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
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
function handleNonNmimrMultipleFileUploads($files, $fieldName, $uploadDir)
{
    // Normalize $files to always be an array of files
    if (!is_array($files['name'])) {
        // Single file upload - convert to array format
        $normalizedFiles = [[
            'name' => $files['name'],
            'type' => $files['type'],
            'tmp_name' => $files['tmp_name'],
            'error' => $files['error'],
            'size' => $files['size']
        ]];
    } else {
        // Multiple file upload - normalize each file
        $fileCount = count($files['name']);
        $normalizedFiles = [];
        for ($i = 0; $i < $fileCount; $i++) {
            $normalizedFiles[] = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];
        }
    }

    // Check if there are any files to upload
    if (empty($normalizedFiles) || empty($normalizedFiles[0]['name'])) {
        return ['success' => true, 'paths' => [], 'error' => null];
    }

    $uploadedPaths = [];

    // Create upload directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    foreach ($normalizedFiles as $i => $singleFile) {
        $result = handleNonNmimrFileUpload($singleFile, $fieldName . '_' . $i, $uploadDir);

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
 * Handle Non-NMIMR application final submission
 * @return array Result with success status and message
 */
function handleNonNmimrSubmission()
{
    $applicationId = $_POST['application_id'] ?? 0;
    $applicationType = $_POST['application_type'] ?? 'non_nmimr';
    
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

    // Step 1: Protocol fields
    $data['protocol_number'] = sanitizeString($_POST['protocol_number'] ?? '');
    $data['version_number'] = sanitizeString($_POST['version_number'] ?? '');
    $data['study_title'] = sanitizeString($_POST['study_title'] ?? '');

    // Step 2: Project Info (new individual PI fields)
    $data['pi_name'] = sanitizeString($_POST['pi_name'] ?? '');
    $data['pi_institution'] = sanitizeString($_POST['pi_institution'] ?? '');
    $data['pi_address'] = sanitizeString($_POST['pi_address'] ?? '');
    $data['pi_phone_number'] = sanitizeString($_POST['pi_phone_number'] ?? '');
    $data['pi_fax'] = sanitizeString($_POST['pi_fax'] ?? '');
    $data['pi_email'] = sanitizeString($_POST['pi_email'] ?? '');
    $data['co_pi_name'] = sanitizeString($_POST['co_pi_name'] ?? '');
    $data['co_pi_qualification'] = sanitizeString($_POST['co_pi_qualification'] ?? '');
    $data['co_pi_department'] = sanitizeString($_POST['co_pi_department'] ?? '');
    $data['co_pi_address'] = sanitizeString($_POST['co_pi_address'] ?? '');
    $data['co_pi_phone_number'] = sanitizeString($_POST['co_pi_phone_number'] ?? '');
    $data['co_pi_fax'] = sanitizeString($_POST['co_pi_fax'] ?? '');
    $data['co_pi_email'] = sanitizeString($_POST['co_pi_email'] ?? '');
    $data['prior_scientific_review'] = sanitizeString($_POST['prior_scientific_review'] ?? '');
    $data['prior_irb_review'] = sanitizeString($_POST['prior_irb_review'] ?? '');
    $data['collaborating_institutions'] = sanitizeString($_POST['collaborating_institutions'] ?? '');
    $data['funding_source'] = sanitizeString($_POST['funding_source'] ?? '');
    
    // Research type - stored as JSON array
    $researchTypes = [];
    if (!empty($_POST['research_type'])) {
        $researchTypes[] = sanitizeString($_POST['research_type']);
    }
    if (!empty($_POST['research_type_other'])) {
        $researchTypes[] = 'Other: ' . sanitizeString($_POST['research_type_other']);
    }
    $data['research_type'] = json_encode($researchTypes);
    
    $data['duration'] = sanitizeString($_POST['duration'] ?? '');

    // Step 3: Research Content
    $data['abstract'] = sanitizeString($_POST['abstract'] ?? '');
    $data['introduction'] = sanitizeString($_POST['introduction'] ?? '');
    $data['literature_review'] = sanitizeString($_POST['literature_review'] ?? '');
    $data['aims'] = sanitizeString($_POST['aims'] ?? '');
    $data['methodology'] = sanitizeString($_POST['methodology'] ?? '');
    $data['ethical_considerations'] = sanitizeString($_POST['ethical_considerations'] ?? '');
    $data['expected_outcomes'] = sanitizeString($_POST['expected_outcomes'] ?? '');
    $data['references'] = sanitizeString($_POST['references'] ?? '');
    $data['work_plan'] = sanitizeString($_POST['work_plan'] ?? '');
    $data['budget'] = sanitizeString($_POST['budget'] ?? '');

    // Step 4: Signatures
    $data['pi_name'] = sanitizeString($_POST['pi_name'] ?? '');
    $data['pi_signature'] = sanitizeString($_POST['pi_signature'] ?? '');
    $data['pi_date'] = sanitizeString($_POST['pi_date'] ?? '');
    $data['co_pi_name'] = sanitizeString($_POST['co_pi_name'] ?? '');
    $data['co_pi_signature'] = sanitizeString($_POST['co_pi_signature'] ?? '');
    $data['co_pi_date'] = sanitizeString($_POST['co_pi_date'] ?? '');

    // Step 5: Checklist and submission notes
    $data['check_complete'] = sanitizeString($_POST['check_complete'] ?? '');
    $data['check_font'] = sanitizeString($_POST['check_font'] ?? '');
    $data['check_consent'] = sanitizeString($_POST['check_consent'] ?? '');
    $data['check_pdf'] = sanitizeString($_POST['check_pdf'] ?? '');
    $data['check_signed'] = sanitizeString($_POST['check_signed'] ?? '');
    $data['check_checklist'] = sanitizeString($_POST['check_checklist'] ?? '');
    $data['submission_notes'] = sanitizeString($_POST['submission_notes'] ?? '');

    // Validate required fields
    $missingFields = validateNonNmimrRequiredFields($data);
    if (!empty($missingFields)) {
        return [
            'success' => false,
            'message' => 'Please fill in all required fields.',
            'errors' => ['missing_fields' => $missingFields]
        ];
    }

    // Validate version number format (e.g., 1.0, 2.1)
    if (!empty($data['version_number']) && !preg_match('/^\d+(\.\d+)?$/', $data['version_number'])) {
        return [
            'success' => false,
            'message' => 'Invalid version number format. Use format like 1.0 or 2.1'
        ];
    }

    // Validate checklist completion (all checks required for submission)
    $requiredChecks = ['check_complete', 'check_font', 'check_consent', 'check_pdf', 'check_signed', 'check_checklist'];
    $missingChecks = [];
    foreach ($requiredChecks as $check) {
        if (empty($data[$check]) || $data[$check] !== 'on') {
            $missingChecks[] = $check;
        }
    }
    if (!empty($missingChecks)) {
        return [
            'success' => false,
            'message' => 'You must complete all checklist items before submitting.'
        ];
    }

    // Validate date format
    $datesToValidate = ['pi_date', 'co_pi_date'];
    foreach ($datesToValidate as $dateField) {
        if (!empty($data[$dateField])) {
            $timestamp = strtotime($data[$dateField]);
            if ($timestamp === false) {
                return [
                    'success' => false,
                    'message' => 'Invalid date format for ' . str_replace('_', ' ', $dateField)
                ];
            }
        }
    }

    // Handle file uploads
    $uploadBaseDir = '../../uploads/non_nmimr_applications/' . date('Y/m');
    $uploadResult = [];
    $uploadErrors = [];

    // Single file uploads
    $singleFileFields = [
        'consent_form' => 'Consent Form',
        'assent_form' => 'Assent Form',
        'final_pdf' => 'Final PDF'
    ];

    foreach ($singleFileFields as $field => $label) {
        if (isset($_FILES[$field])) {
            $result = handleNonNmimrFileUpload($_FILES[$field], $field, $uploadBaseDir);
            if (!$result['success']) {
                $uploadErrors[] = $result['error'];
            }
            $uploadResult[$field] = $result['path'];
        } else {
            $uploadResult[$field] = null;
        }
    }

    // Handle approval_letters (multiple files)
    if (isset($_FILES['approval_letters'])) {
        $result = handleNonNmimrMultipleFileUploads($_FILES['approval_letters'], 'approval_letters', $uploadBaseDir);
        if (!$result['success']) {
            $uploadErrors[] = $result['error'];
        }
        $uploadResult['approval_letters'] = json_encode($result['paths'] ?? []);
    } else {
        $uploadResult['approval_letters'] = json_encode([]);
    }

    // Handle required_forms (multiple files)
    if (isset($_FILES['required_forms'])) {
        $result = handleNonNmimrMultipleFileUploads($_FILES['required_forms'], 'required_forms', $uploadBaseDir);
        if (!$result['success']) {
            $uploadErrors[] = $result['error'];
        }
        $uploadResult['required_forms'] = json_encode($result['paths'] ?? []);
    } else {
        $uploadResult['required_forms'] = json_encode([]);
    }

    // Handle data_instruments (multiple files)
    if (isset($_FILES['data_instruments'])) {
        $result = handleNonNmimrMultipleFileUploads($_FILES['data_instruments'], 'data_instruments', $uploadBaseDir);
        if (!$result['success']) {
            $uploadErrors[] = $result['error'];
        }
        $uploadResult['data_instruments'] = json_encode($result['paths'] ?? []);
    } else {
        $uploadResult['data_instruments'] = json_encode([]);
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
        $protocolNumber = generateNonNmimrProtocolNumber($conn);
    }

    try {
        // Begin transaction
        $conn->beginTransaction();

        if ($applicationId > 0) {
            // Update existing application → make it submitted
            $sql = "
                UPDATE non_nmimr_applications SET
                    protocol_number = :protocol_number,
                    version_number = :version_number,
                    study_title = :study_title,
                    pi_name = :pi_name,
                    pi_institution = :pi_institution,
                    pi_address = :pi_address,
                    pi_phone_number = :pi_phone_number,
                    pi_fax = :pi_fax,
                    pi_email = :pi_email,
                    co_pi_name = :co_pi_name,
                    co_pi_qualification = :co_pi_qualification,
                    co_pi_department = :co_pi_department,
                    co_pi_address = :co_pi_address,
                    co_pi_phone_number = :co_pi_phone_number,
                    co_pi_fax = :co_pi_fax,
                    co_pi_email = :co_pi_email,
                    prior_scientific_review = :prior_scientific_review,
                    prior_irb_review = :prior_irb_review,
                    collaborating_institutions = :collaborating_institutions,
                    funding_source = :funding_source,
                    research_type = :research_type,
                    duration = :duration,
                    abstract = :abstract,
                    introduction = :introduction,
                    literature_review = :literature_review,
                    aims = :aims,
                    methodology = :methodology,
                    ethical_considerations = :ethical_considerations,
                    expected_outcomes = :expected_outcomes,
                    application_references = :application_references,
                    work_plan = :work_plan,
                    budget = :budget,
                    pi_name = :pi_name,
                    pi_signature = :pi_signature,
                    pi_date = :pi_date,
                    co_pi_name = :co_pi_name,
                    co_pi_signature = :co_pi_signature,
                    co_pi_date = :co_pi_date,
                    check_complete = :check_complete,
                    check_font = :check_font,
                    check_consent = :check_consent,
                    check_pdf = :check_pdf,
                    check_signed = :check_signed,
                    check_checklist = :check_checklist,
                    final_pdf = :final_pdf,
                    submission_notes = :submission_notes,
                    status = 'submitted',
                    current_step = 5,
                    updated_at = NOW()
                WHERE id = :application_id
                AND applicant_id = :applicant_id
            ";

            $params = [
                ':application_id' => $applicationId,
                ':applicant_id' => $userId,
                ':protocol_number' => $protocolNumber,
                ':version_number' => $data['version_number'],
                ':study_title' => $data['study_title'],
                ':pi_name' => $data['pi_name'],
                ':pi_institution' => $data['pi_institution'],
                ':pi_address' => $data['pi_address'],
                ':pi_phone_number' => $data['pi_phone_number'],
                ':pi_fax' => $data['pi_fax'],
                ':pi_email' => $data['pi_email'],
                ':co_pi_name' => $data['co_pi_name'],
                ':co_pi_qualification' => $data['co_pi_qualification'],
                ':co_pi_department' => $data['co_pi_department'],
                ':co_pi_address' => $data['co_pi_address'],
                ':co_pi_phone_number' => $data['co_pi_phone_number'],
                ':co_pi_fax' => $data['co_pi_fax'],
                ':co_pi_email' => $data['co_pi_email'],
                ':prior_scientific_review' => $data['prior_scientific_review'],
                ':prior_irb_review' => $data['prior_irb_review'],
                ':collaborating_institutions' => $data['collaborating_institutions'],
                ':funding_source' => $data['funding_source'],
                ':research_type' => $data['research_type'],
                ':duration' => $data['duration'],
                ':abstract' => $data['abstract'],
                ':introduction' => $data['introduction'],
                ':literature_review' => $data['literature_review'],
                ':aims' => $data['aims'],
                ':methodology' => $data['methodology'],
                ':ethical_considerations' => $data['ethical_considerations'],
                ':expected_outcomes' => $data['expected_outcomes'],
                ':application_references' => $data['references'],
                ':work_plan' => $data['work_plan'],
                ':budget' => $data['budget'],
                ':pi_name' => $data['pi_name'],
                ':pi_signature' => $data['pi_signature'],
                ':pi_date' => $data['pi_date'],
                ':co_pi_name' => $data['co_pi_name'],
                ':co_pi_signature' => $data['co_pi_signature'],
                ':co_pi_date' => $data['co_pi_date'],
                ':check_complete' => $data['check_complete'],
                ':check_font' => $data['check_font'],
                ':check_consent' => $data['check_consent'],
                ':check_pdf' => $data['check_pdf'],
                ':check_signed' => $data['check_signed'],
                ':check_checklist' => $data['check_checklist'],
                ':final_pdf' => $uploadResult['final_pdf'] ?? null,
                ':submission_notes' => $data['submission_notes']
            ];

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
        } else {
            // Insert into non_nmimr_applications table
            $sql = "
                INSERT INTO non_nmimr_applications (
                    applicant_id, application_type, protocol_number, version_number, study_title,
                    pi_name, pi_institution, pi_address, pi_phone_number, pi_fax, pi_email,
                    co_pi_name, co_pi_qualification, co_pi_department, co_pi_address, co_pi_phone_number, co_pi_fax, co_pi_email,
                    prior_scientific_review, prior_irb_review, collaborating_institutions,
                    funding_source, research_type, duration,
                    abstract, introduction, literature_review, aims,
                    methodology, ethical_considerations, expected_outcomes, application_references,
                    work_plan, budget,
                    pi_name, pi_signature, pi_date, co_pi_name, co_pi_signature, co_pi_date,
                    check_complete, check_font, check_consent, check_pdf, check_signed, check_checklist,
                    final_pdf, submission_notes,
                    status, current_step, created_at, updated_at
                ) VALUES (
                    :applicant_id, :application_type, :protocol_number, :version_number, :study_title,
                    :pi_name, :pi_institution, :pi_address, :pi_phone_number, :pi_fax, :pi_email,
                    :co_pi_name, :co_pi_qualification, :co_pi_department, :co_pi_address, :co_pi_phone_number, :co_pi_fax, :co_pi_email,
                    :prior_scientific_review, :prior_irb_review, :collaborating_institutions,
                    :funding_source, :research_type, :duration,
                    :abstract, :introduction, :literature_review, :aims,
                    :methodology, :ethical_considerations, :expected_outcomes, :application_references,
                    :work_plan, :budget,
                    :pi_name, :pi_signature, :pi_date, :co_pi_name, :co_pi_signature, :co_pi_date,
                    :check_complete, :check_font, :check_consent, :check_pdf, :check_signed, :check_checklist,
                    :final_pdf, :submission_notes,
                    :status, :current_step, NOW(), NOW()
                )
            ";

            $stmt = $conn->prepare($sql);

            // Bind all parameters
            $params = [
                ':applicant_id' => $userId,
                ':application_type' => $applicationType,
                ':protocol_number' => $protocolNumber,
                ':version_number' => $data['version_number'],
                ':study_title' => $data['study_title'],
                ':pi_name' => $data['pi_name'],
                ':pi_institution' => $data['pi_institution'],
                ':pi_address' => $data['pi_address'],
                ':pi_phone_number' => $data['pi_phone_number'],
                ':pi_fax' => $data['pi_fax'],
                ':pi_email' => $data['pi_email'],
                ':co_pi_name' => $data['co_pi_name'],
                ':co_pi_qualification' => $data['co_pi_qualification'],
                ':co_pi_department' => $data['co_pi_department'],
                ':co_pi_address' => $data['co_pi_address'],
                ':co_pi_phone_number' => $data['co_pi_phone_number'],
                ':co_pi_fax' => $data['co_pi_fax'],
                ':co_pi_email' => $data['co_pi_email'],
                ':prior_scientific_review' => $data['prior_scientific_review'],
                ':prior_irb_review' => $data['prior_irb_review'],
                ':collaborating_institutions' => $data['collaborating_institutions'],
                ':funding_source' => $data['funding_source'],
                ':research_type' => $data['research_type'],
                ':duration' => $data['duration'],
                ':abstract' => $data['abstract'],
                ':introduction' => $data['introduction'],
                ':literature_review' => $data['literature_review'],
                ':aims' => $data['aims'],
                ':methodology' => $data['methodology'],
                ':ethical_considerations' => $data['ethical_considerations'],
                ':expected_outcomes' => $data['expected_outcomes'],
                ':application_references' => $data['references'],
                ':work_plan' => $data['work_plan'],
                ':budget' => $data['budget'],
                ':pi_name' => $data['pi_name'],
                ':pi_signature' => $data['pi_signature'],
                ':pi_date' => $data['pi_date'],
                ':co_pi_name' => $data['co_pi_name'],
                ':co_pi_signature' => $data['co_pi_signature'],
                ':co_pi_date' => $data['co_pi_date'],
                ':check_complete' => $data['check_complete'],
                ':check_font' => $data['check_font'],
                ':check_consent' => $data['check_consent'],
                ':check_pdf' => $data['check_pdf'],
                ':check_signed' => $data['check_signed'],
                ':check_checklist' => $data['check_checklist'],
                ':final_pdf' => $uploadResult['final_pdf'] ?? null,
                ':submission_notes' => $data['submission_notes'],
                ':status' => 'submitted',
                ':current_step' => 5  // All steps completed on submission
            ];

            $stmt->execute($params);

            $applicationId = $conn->lastInsertId();
        }

        // Insert Documents into non_nmimr_application_documents table
        $documentTypes = [
            'consent_form' => 'consent_form',
            'assent_form' => 'assent_form'
        ];

        foreach ($documentTypes as $field => $docType) {
            if (!empty($uploadResult[$field])) {
                $docSql = "
                    INSERT INTO non_nmimr_application_documents (
                        application_id, document_type, file_name, file_path
                    ) VALUES (
                        :application_id, :document_type, :file_name, :file_path
                    )
                ";
                $docStmt = $conn->prepare($docSql);
                $docStmt->execute([
                    ':application_id' => $applicationId,
                    ':document_type' => $docType,
                    ':file_name' => basename($uploadResult[$field]),
                    ':file_path' => $uploadResult[$field]
                ]);
            }
        }

        // Handle approval_letters (multiple files)
        $approvalLetters = json_decode($uploadResult['approval_letters'] ?? '[]', true);
        foreach ($approvalLetters as $filePath) {
            $docSql = "
                INSERT INTO non_nmimr_application_documents (
                    application_id, document_type, file_name, file_path
                ) VALUES (
                    :application_id, 'approval_letter', :file_name, :file_path
                )
            ";
            $docStmt = $conn->prepare($docSql);
            $docStmt->execute([
                ':application_id' => $applicationId,
                ':file_name' => basename($filePath),
                ':file_path' => $filePath
            ]);
        }

        // Handle required_forms (multiple files)
        $requiredForms = json_decode($uploadResult['required_forms'] ?? '[]', true);
        foreach ($requiredForms as $filePath) {
            $docSql = "
                INSERT INTO non_nmimr_application_documents (
                    application_id, document_type, file_name, file_path
                ) VALUES (
                    :application_id, 'required_form', :file_name, :file_path
                )
            ";
            $docStmt = $conn->prepare($docSql);
            $docStmt->execute([
                ':application_id' => $applicationId,
                ':file_name' => basename($filePath),
                ':file_path' => $filePath
            ]);
        }

        // Handle data_instruments (multiple files)
        $dataInstruments = json_decode($uploadResult['data_instruments'] ?? '[]', true);
        foreach ($dataInstruments as $filePath) {
            $docSql = "
                INSERT INTO non_nmimr_application_documents (
                    application_id, document_type, file_name, file_path
                ) VALUES (
                    :application_id, 'data_instrument', :file_name, :file_path
                )
            ";
            $docStmt = $conn->prepare($docSql);
            $docStmt->execute([
                ':application_id' => $applicationId,
                ':file_name' => basename($filePath),
                ':file_path' => $filePath
            ]);
        }

        // Handle final_pdf
        if (!empty($uploadResult['final_pdf'])) {
            $docSql = "
                INSERT INTO non_nmimr_application_documents (
                    application_id, document_type, file_name, file_path
                ) VALUES (
                    :application_id, 'final_pdf', :file_name, :file_path
                )
            ";
            $docStmt = $conn->prepare($docSql);
            $docStmt->execute([
                ':application_id' => $applicationId,
                ':file_name' => basename($uploadResult['final_pdf']),
                ':file_path' => $uploadResult['final_pdf']
            ]);
        }

        // Commit transaction
        $conn->commit();

        // Log the submission
        error_log("Non-NMIMR application submitted successfully. ID: $applicationId, Protocol: $protocolNumber, User: $userId");

        return [
            'success' => true,
            'message' => 'Your Non-NMIMR application has been submitted successfully!',
            'protocol_number' => $protocolNumber,
            'application_id' => $applicationId,
            'redirect' => '/applicant-dashboard?success=submitted&protocol=' . urlencode($protocolNumber)
        ];
    } catch (PDOException $e) {
        // Rollback transaction
        $conn->rollBack();

        // Log error
        error_log("Database error in non_nmimr_application_handler.php: " . $e->getMessage());

        return [
            'success' => false,
            'message' => 'An error occurred while saving your application. Please try again.'
        ];
    }
}

/**
 * Handle save draft request for Non-NMIMR application
 * @return array Result with success status and message
 */
function handleNonNmimrSaveDraft()
{
    // Verify applicant is logged in
    $isLoggedIn = is_applicant_logged_in();

    if (!$isLoggedIn) {
        // Debug logging for authentication failure
        error_log("Non-NMIMR Authentication Debug: logged_in=" . (isset($_SESSION['logged_in']) ? $_SESSION['logged_in'] : 'NOT SET') .
            ", user_id=" . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET') .
            ", role=" . (isset($_SESSION['role']) ? $_SESSION['role'] : 'NOT SET'));

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

    // Get application ID, current step, and application type
    $applicationId = $_POST['application_id'] ?? 0;
    $currentStep = $_POST['current_step'] ?? 1;
    $applicationType = $_POST['application_type'] ?? 'non_nmimr';

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

    // Step 1: Protocol fields
    $data['protocol_number'] = sanitizeString($_POST['protocol_number'] ?? '');
    $data['version_number'] = sanitizeString($_POST['version_number'] ?? '');
    $data['study_title'] = sanitizeString($_POST['study_title'] ?? '');

    // Step 2: Project Info (new individual PI fields)
    $data['pi_name'] = sanitizeString($_POST['pi_name'] ?? '');
    $data['pi_institution'] = sanitizeString($_POST['pi_institution'] ?? '');
    $data['pi_address'] = sanitizeString($_POST['pi_address'] ?? '');
    $data['pi_phone_number'] = sanitizeString($_POST['pi_phone_number'] ?? '');
    $data['pi_fax'] = sanitizeString($_POST['pi_fax'] ?? '');
    $data['pi_email'] = sanitizeString($_POST['pi_email'] ?? '');
    $data['co_pi_name'] = sanitizeString($_POST['co_pi_name'] ?? '');
    $data['co_pi_qualification'] = sanitizeString($_POST['co_pi_qualification'] ?? '');
    $data['co_pi_department'] = sanitizeString($_POST['co_pi_department'] ?? '');
    $data['co_pi_address'] = sanitizeString($_POST['co_pi_address'] ?? '');
    $data['co_pi_phone_number'] = sanitizeString($_POST['co_pi_phone_number'] ?? '');
    $data['co_pi_fax'] = sanitizeString($_POST['co_pi_fax'] ?? '');
    $data['co_pi_email'] = sanitizeString($_POST['co_pi_email'] ?? '');
    $data['prior_scientific_review'] = sanitizeString($_POST['prior_scientific_review'] ?? '');
    $data['prior_irb_review'] = sanitizeString($_POST['prior_irb_review'] ?? '');
    $data['collaborating_institutions'] = sanitizeString($_POST['collaborating_institutions'] ?? '');
    $data['funding_source'] = sanitizeString($_POST['funding_source'] ?? '');
    
    // Research type - stored as JSON array
    $researchTypes = [];
    if (!empty($_POST['research_type'])) {
        $researchTypes[] = sanitizeString($_POST['research_type']);
    }
    if (!empty($_POST['research_type_other'])) {
        $researchTypes[] = 'Other: ' . sanitizeString($_POST['research_type_other']);
    }
    $data['research_type'] = json_encode($researchTypes);
    
    $data['duration'] = sanitizeString($_POST['duration'] ?? '');

    // Step 3: Research Content
    $data['abstract'] = sanitizeString($_POST['abstract'] ?? '');
    $data['introduction'] = sanitizeString($_POST['introduction'] ?? '');
    $data['literature_review'] = sanitizeString($_POST['literature_review'] ?? '');
    $data['aims'] = sanitizeString($_POST['aims'] ?? '');
    $data['methodology'] = sanitizeString($_POST['methodology'] ?? '');
    $data['ethical_considerations'] = sanitizeString($_POST['ethical_considerations'] ?? '');
    $data['expected_outcomes'] = sanitizeString($_POST['expected_outcomes'] ?? '');
    $data['references'] = sanitizeString($_POST['references'] ?? '');
    $data['work_plan'] = sanitizeString($_POST['work_plan'] ?? '');
    $data['budget'] = sanitizeString($_POST['budget'] ?? '');

    // Step 4: Signatures
    $data['pi_name'] = sanitizeString($_POST['pi_name'] ?? '');
    $data['pi_signature'] = sanitizeString($_POST['pi_signature'] ?? '');
    $data['pi_date'] = sanitizeString($_POST['pi_date'] ?? '');
    $data['co_pi_name'] = sanitizeString($_POST['co_pi_name'] ?? '');
    $data['co_pi_signature'] = sanitizeString($_POST['co_pi_signature'] ?? '');
    $data['co_pi_date'] = sanitizeString($_POST['co_pi_date'] ?? '');

    // Step 5: Checklist and submission notes
    $data['check_complete'] = sanitizeString($_POST['check_complete'] ?? '');
    $data['check_font'] = sanitizeString($_POST['check_font'] ?? '');
    $data['check_consent'] = sanitizeString($_POST['check_consent'] ?? '');
    $data['check_pdf'] = sanitizeString($_POST['check_pdf'] ?? '');
    $data['check_signed'] = sanitizeString($_POST['check_signed'] ?? '');
    $data['check_checklist'] = sanitizeString($_POST['check_checklist'] ?? '');
    $data['submission_notes'] = sanitizeString($_POST['submission_notes'] ?? '');

    // ============================================
    // Provide default values for NOT NULL columns
    // ============================================

    // Numeric fields - use "1.0" as default for version_number
    if (empty($data['version_number'])) {
        $data['version_number'] = '1.0';
    }

    // Date fields - use valid date format for draft saves
    $defaultDate = date('Y-m-d');
    if (empty($data['pi_date'])) {
        $data['pi_date'] = $defaultDate;
    }
    if (empty($data['co_pi_date'])) {
        $data['co_pi_date'] = $defaultDate;
    }

    // Provide defaults for empty text fields (for NOT NULL compliance)
    $defaultFields = [
        'study_title' => 'Draft - ' . date('Y-m-d H:i:s'),
        'pi_name' => '',
        'pi_institution' => '',
        'pi_address' => '',
        'pi_phone_number' => '',
        'pi_fax' => '',
        'pi_email' => '',
        'co_pi_name' => '',
        'co_pi_qualification' => '',
        'co_pi_department' => '',
        'co_pi_address' => '',
        'co_pi_phone_number' => '',
        'co_pi_fax' => '',
        'co_pi_email' => '',
        'prior_scientific_review' => '',
        'prior_irb_review' => '',
        'collaborating_institutions' => '',
        'funding_source' => '',
        'research_type' => '[]',
        'duration' => '1 month',
        'abstract' => '',
        'introduction' => '',
        'literature_review' => '',
        'aims' => '',
        'methodology' => '',
        'ethical_considerations' => '',
        'expected_outcomes' => '',
        'application_references' => '',
        'work_plan' => '',
        'budget' => '',
        'pi_signature' => '',
        'co_pi_signature' => ''
    ];

    foreach ($defaultFields as $field => $defaultValue) {
        if (empty($data[$field])) {
            $data[$field] = $defaultValue;
        }
    }

    try {
        // Begin transaction
        $conn->beginTransaction();

        // Generate protocol number if not provided
        $protocolNumber = $data['protocol_number'];
        if (empty($protocolNumber)) {
            $protocolNumber = generateNonNmimrProtocolNumber($conn);
        }

        // Handle file uploads (optional for draft)
        $uploadBaseDir = '../../uploads/non_nmimr_applications/' . date('Y/m');
        $uploadResult = [];

        // Single file uploads
        $singleFileFields = [
            'consent_form' => 'Consent Form',
            'assent_form' => 'Assent Form',
            'final_pdf' => 'Final PDF'
        ];

        foreach ($singleFileFields as $field => $label) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                $result = handleNonNmimrFileUpload($_FILES[$field], $field, $uploadBaseDir);
                $uploadResult[$field] = $result['success'] ? $result['path'] : null;
            } else {
                $uploadResult[$field] = null;
            }
        }

        // Handle approval_letters (multiple files)
        if (isset($_FILES['approval_letters']) && !empty($_FILES['approval_letters']['name'][0])) {
            $result = handleNonNmimrMultipleFileUploads($_FILES['approval_letters'], 'approval_letters', $uploadBaseDir);
            $uploadResult['approval_letters'] = $result['success'] ? json_encode($result['paths'] ?? []) : json_encode([]);
        } else {
            $uploadResult['approval_letters'] = json_encode([]);
        }

        // Handle required_forms (multiple files)
        if (isset($_FILES['required_forms']) && !empty($_FILES['required_forms']['name'][0])) {
            $result = handleNonNmimrMultipleFileUploads($_FILES['required_forms'], 'required_forms', $uploadBaseDir);
            $uploadResult['required_forms'] = $result['success'] ? json_encode($result['paths'] ?? []) : json_encode([]);
        } else {
            $uploadResult['required_forms'] = json_encode([]);
        }

        // Handle data_instruments (multiple files)
        if (isset($_FILES['data_instruments']) && !empty($_FILES['data_instruments']['name'][0])) {
            $result = handleNonNmimrMultipleFileUploads($_FILES['data_instruments'], 'data_instruments', $uploadBaseDir);
            $uploadResult['data_instruments'] = $result['success'] ? json_encode($result['paths'] ?? []) : json_encode([]);
        } else {
            $uploadResult['data_instruments'] = json_encode([]);
        }

        // Check if this is a new draft or update
        if ($applicationId > 0) {
            // Update existing draft in non_nmimr_applications table
            $sql = "
                UPDATE non_nmimr_applications SET
                    protocol_number = :protocol_number,
                    version_number = :version_number,
                    study_title = :study_title,
                    pi_name = :pi_name,
                    pi_institution = :pi_institution,
                    pi_address = :pi_address,
                    pi_phone_number = :pi_phone_number,
                    pi_fax = :pi_fax,
                    pi_email = :pi_email,
                    co_pi_name = :co_pi_name,
                    co_pi_qualification = :co_pi_qualification,
                    co_pi_department = :co_pi_department,
                    co_pi_address = :co_pi_address,
                    co_pi_phone_number = :co_pi_phone_number,
                    co_pi_fax = :co_pi_fax,
                    co_pi_email = :co_pi_email,
                    prior_scientific_review = :prior_scientific_review,
                    prior_irb_review = :prior_irb_review,
                    collaborating_institutions = :collaborating_institutions,
                    funding_source = :funding_source,
                    research_type = :research_type,
                    duration = :duration,
                    abstract = :abstract,
                    introduction = :introduction,
                    literature_review = :literature_review,
                    aims = :aims,
                    methodology = :methodology,
                    ethical_considerations = :ethical_considerations,
                    expected_outcomes = :expected_outcomes,
                    application_references = :application_references,
                    work_plan = :work_plan,
                    budget = :budget,
                    pi_name = :pi_name,
                    pi_signature = :pi_signature,
                    pi_date = :pi_date,
                    co_pi_name = :co_pi_name,
                    co_pi_signature = :co_pi_signature,
                    co_pi_date = :co_pi_date,
                    check_complete = :check_complete,
                    check_font = :check_font,
                    check_consent = :check_consent,
                    check_pdf = :check_pdf,
                    check_signed = :check_signed,
                    check_checklist = :check_checklist,
                    final_pdf = :final_pdf,
                    submission_notes = :submission_notes,
                    status = :status,
                    current_step = :current_step,
                    updated_at = NOW()
                WHERE id = :application_id
            ";

            $params = [
                ':application_id' => $applicationId,
                ':protocol_number' => $protocolNumber,
                ':version_number' => $data['version_number'],
                ':study_title' => $data['study_title'],
                ':pi_name' => $data['pi_name'],
                ':pi_institution' => $data['pi_institution'],
                ':pi_address' => $data['pi_address'],
                ':pi_phone_number' => $data['pi_phone_number'],
                ':pi_fax' => $data['pi_fax'],
                ':pi_email' => $data['pi_email'],
                ':co_pi_name' => $data['co_pi_name'],
                ':co_pi_qualification' => $data['co_pi_qualification'],
                ':co_pi_department' => $data['co_pi_department'],
                ':co_pi_address' => $data['co_pi_address'],
                ':co_pi_phone_number' => $data['co_pi_phone_number'],
                ':co_pi_fax' => $data['co_pi_fax'],
                ':co_pi_email' => $data['co_pi_email'],
                ':prior_scientific_review' => $data['prior_scientific_review'],
                ':prior_irb_review' => $data['prior_irb_review'],
                ':collaborating_institutions' => $data['collaborating_institutions'],
                ':funding_source' => $data['funding_source'],
                ':research_type' => $data['research_type'],
                ':duration' => $data['duration'],
                ':abstract' => $data['abstract'],
                ':introduction' => $data['introduction'],
                ':literature_review' => $data['literature_review'],
                ':aims' => $data['aims'],
                ':methodology' => $data['methodology'],
                ':ethical_considerations' => $data['ethical_considerations'],
                ':expected_outcomes' => $data['expected_outcomes'],
                ':application_references' => $data['references'],
                ':work_plan' => $data['work_plan'],
                ':budget' => $data['budget'],
                ':pi_name' => $data['pi_name'],
                ':pi_signature' => $data['pi_signature'],
                ':pi_date' => $data['pi_date'],
                ':co_pi_name' => $data['co_pi_name'],
                ':co_pi_signature' => $data['co_pi_signature'],
                ':co_pi_date' => $data['co_pi_date'],
                ':check_complete' => $data['check_complete'],
                ':check_font' => $data['check_font'],
                ':check_consent' => $data['check_consent'],
                ':check_pdf' => $data['check_pdf'],
                ':check_signed' => $data['check_signed'],
                ':check_checklist' => $data['check_checklist'],
                ':final_pdf' => $uploadResult['final_pdf'] ?? null,
                ':submission_notes' => $data['submission_notes'],
                ':status' => 'draft',
                ':current_step' => $currentStep
            ];

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
        } else {
            // Insert new draft into non_nmimr_applications table
            $sql = "
                INSERT INTO non_nmimr_applications (
                    applicant_id, application_type, protocol_number, version_number, study_title,
                    pi_name, pi_institution, pi_address, pi_phone_number, pi_fax, pi_email,
                    co_pi_name, co_pi_qualification, co_pi_department, co_pi_address, co_pi_phone_number, co_pi_fax, co_pi_email,
                    prior_scientific_review, prior_irb_review, collaborating_institutions,
                    funding_source, research_type, duration,
                    abstract, introduction, literature_review, aims,
                    methodology, ethical_considerations, expected_outcomes, application_references,
                    work_plan, budget,
                    pi_name, pi_signature, pi_date, co_pi_name, co_pi_signature, co_pi_date,
                    check_complete, check_font, check_consent, check_pdf, check_signed, check_checklist,
                    final_pdf, submission_notes,
                    status, current_step, created_at, updated_at
                ) VALUES (
                    :applicant_id, :application_type, :protocol_number, :version_number, :study_title,
                    :pi_name, :pi_institution, :pi_address, :pi_phone_number, :pi_fax, :pi_email,
                    :co_pi_name, :co_pi_qualification, :co_pi_department, :co_pi_address, :co_pi_phone_number, :co_pi_fax, :co_pi_email,
                    :prior_scientific_review, :prior_irb_review, :collaborating_institutions,
                    :funding_source, :research_type, :duration,
                    :abstract, :introduction, :literature_review, :aims,
                    :methodology, :ethical_considerations, :expected_outcomes, :application_references,
                    :work_plan, :budget,
                    :pi_name, :pi_signature, :pi_date, :co_pi_name, :co_pi_signature, :co_pi_date,
                    :check_complete, :check_font, :check_consent, :check_pdf, :check_signed, :check_checklist,
                    :final_pdf, :submission_notes,
                    :status, :current_step, NOW(), NOW()
                )
            ";

            $params = [
                ':applicant_id' => $userId,
                ':application_type' => $applicationType,
                ':protocol_number' => $protocolNumber,
                ':version_number' => $data['version_number'],
                ':study_title' => $data['study_title'],
                ':pi_name' => $data['pi_name'],
                ':pi_institution' => $data['pi_institution'],
                ':pi_address' => $data['pi_address'],
                ':pi_phone_number' => $data['pi_phone_number'],
                ':pi_fax' => $data['pi_fax'],
                ':pi_email' => $data['pi_email'],
                ':co_pi_name' => $data['co_pi_name'],
                ':co_pi_qualification' => $data['co_pi_qualification'],
                ':co_pi_department' => $data['co_pi_department'],
                ':co_pi_address' => $data['co_pi_address'],
                ':co_pi_phone_number' => $data['co_pi_phone_number'],
                ':co_pi_fax' => $data['co_pi_fax'],
                ':co_pi_email' => $data['co_pi_email'],
                ':prior_scientific_review' => $data['prior_scientific_review'],
                ':prior_irb_review' => $data['prior_irb_review'],
                ':collaborating_institutions' => $data['collaborating_institutions'],
                ':funding_source' => $data['funding_source'],
                ':research_type' => $data['research_type'],
                ':duration' => $data['duration'],
                ':abstract' => $data['abstract'],
                ':introduction' => $data['introduction'],
                ':literature_review' => $data['literature_review'],
                ':aims' => $data['aims'],
                ':methodology' => $data['methodology'],
                ':ethical_considerations' => $data['ethical_considerations'],
                ':expected_outcomes' => $data['expected_outcomes'],
                ':application_references' => $data['references'],
                ':work_plan' => $data['work_plan'],
                ':budget' => $data['budget'],
                ':pi_name' => $data['pi_name'],
                ':pi_signature' => $data['pi_signature'],
                ':pi_date' => $data['pi_date'],
                ':co_pi_name' => $data['co_pi_name'],
                ':co_pi_signature' => $data['co_pi_signature'],
                ':co_pi_date' => $data['co_pi_date'],
                ':check_complete' => $data['check_complete'],
                ':check_font' => $data['check_font'],
                ':check_consent' => $data['check_consent'],
                ':check_pdf' => $data['check_pdf'],
                ':check_signed' => $data['check_signed'],
                ':check_checklist' => $data['check_checklist'],
                ':final_pdf' => $uploadResult['final_pdf'] ?? null,
                ':submission_notes' => $data['submission_notes'],
                ':status' => 'draft',
                ':current_step' => $currentStep
            ];

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            $applicationId = $conn->lastInsertId();
        }

        // Commit transaction
        $conn->commit();

        // Log the draft save
        error_log("Non-NMIMR application draft saved. ID: $applicationId, Protocol: $protocolNumber, User: $userId");

        return [
            'success' => true,
            'message' => 'Your draft has been saved successfully!',
            'protocol_number' => $protocolNumber,
            'application_id' => $applicationId,
            'current_step' => $currentStep
        ];
    } catch (PDOException $e) {
        // Rollback transaction
        $conn->rollBack();

        // Log error
        error_log("Database error in save draft (non_nmimr): " . $e->getMessage());

        return [
            'success' => false,
            'message' => 'An error occurred while saving your draft. Please try again.'
        ];
    }
}

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'submit':
            $result = handleNonNmimrSubmission();
            break;
        case 'save_draft':
            $result = handleNonNmimrSaveDraft();
            break;
        default:
            $result = [
                'success' => false,
                'message' => 'Invalid action specified.'
            ];
    }

    // Return JSON response
    echo json_encode($result);
    exit;
}
