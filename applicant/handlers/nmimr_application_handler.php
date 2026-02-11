<?php

/**
 * NMIMR Application Handler
 * Handles form submission from nmimr_application.php
 * Validates, sanitizes, and saves data to the database
 * Schema: nmimr_applications, nmimr_co_investigators, nmimr_application_documents, nmimr_declarations
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
error_log("NMIMR Handler Session Debug: session_name=" . session_name() .
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
 * Generate a unique protocol number for NMIMR applications
 * Format: NIRB-YYYY-XXXX
 * @param PDO $conn Database connection
 * @return string Generated protocol number
 */
function generateProtocolNumber($conn)
{
    $year = date('Y');
    $prefix = 'NIRB';

    // Get the latest protocol number for this year and type
    $stmt = $conn->prepare(
        "SELECT protocol_number FROM nmimr_applications 
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
 * Validate required fields for NMIMR application submission
 * @param array $data Form data
 * @return array Array of missing required fields
 */
function validateRequiredFields($data)
{
    $required = [
        // Step 1: Protocol
        'protocol_number',
        'version_number',
        'submission_date',

        // Step 2: Section A - PI Info
        'pi_name',
        'pi_institution',
        'pi_address',
        'pi_phone',
        'pi_email',

        // Step 2: Project Info
        'proposal_title',
        'project_duration',

        // Step 3: Part 1
        'abstract',
        'introduction',
        'literature_review',
        'study_aims',

        // Step 4: Part 2
        'methodology',
        'ethical_considerations',
        'expected_outcomes',
        'nmimr_references',

        // Step 6: Signatures
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
 * Validate file uploads for NMIMR application
 * @param array $files Uploaded files
 * @param bool $isSubmit Whether this is a final submission (files required) or draft
 * @return array Array of validation errors
 */
function validateFiles($files, $isSubmit = true)
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
        $requiredFiles = ['consent_form', 'data_instruments'];
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
function handleFileUpload($file, $fieldName, $uploadDir)
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
function handleMultipleFileUploads($files, $fieldName, $uploadDir)
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
 * Handle NMIMR application submission
 * @return array Result with success status and message
 */



function handleNmimrSubmission()
{

    $applicationId = $_POST['application_id'] ?? 0;

    
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
    $data['submission_date'] = sanitizeString($_POST['submission_date'] ?? '');

    // Step 2: Section A - PI Info
    $data['pi_name'] = sanitizeString($_POST['pi_name'] ?? '');
    $data['pi_institution'] = sanitizeString($_POST['pi_institution'] ?? '');
    $data['pi_address'] = sanitizeString($_POST['pi_address'] ?? '');
    $data['pi_phone'] = sanitizeString($_POST['pi_phone'] ?? '');
    $data['pi_email'] = sanitizeString($_POST['pi_email'] ?? '');

    // Co-Investigators (dynamic fields) - stored as JSON
    $coInvestigators = [];
    $copiIndex = 1;
    while (isset($_POST['copi' . $copiIndex . '_name'])) {
        $copiName = sanitizeString($_POST['copi' . $copiIndex . '_name'] ?? '');
        if (!empty($copiName)) {
            $coInvestigators[] = [
                'name' => $copiName,
                'qualification' => sanitizeString($_POST['copi' . $copiIndex . '_qualification'] ?? ''),
                'department_email' => sanitizeString($_POST['copi' . $copiIndex . '_department_email'] ?? '')
            ];
        }
        $copiIndex++;
    }
    $data['co_investigators'] = json_encode($coInvestigators);

    // Step 2: Project Info
    $data['proposal_title'] = sanitizeString($_POST['proposal_title'] ?? '');

    // Research type checkboxes - stored as JSON array
    $researchTypes = [];
    if (!empty($_POST['research_type_biomedical'])) {
        $researchTypes[] = sanitizeString($_POST['research_type_biomedical']);
    }
    if (!empty($_POST['research_type_social'])) {
        $researchTypes[] = sanitizeString($_POST['research_type_social']);
    }
    if (!empty($_POST['research_type_other'])) {
        $researchTypes[] = 'Other: ' . sanitizeString($_POST['research_type_other_specify'] ?? '');
    }
    $data['research_type'] = json_encode($researchTypes);

    $data['project_duration'] = sanitizeString($_POST['project_duration'] ?? '');
    $data['funding_source'] = sanitizeString($_POST['funding_source'] ?? '');
    $data['prior_irb'] = sanitizeString($_POST['prior_irb'] ?? '');

    // Step 3: Part 1
    $data['abstract'] = sanitizeString($_POST['abstract'] ?? '');
    $data['introduction'] = sanitizeString($_POST['introduction'] ?? '');
    $data['literature_review'] = sanitizeString($_POST['literature_review'] ?? '');
    $data['study_aims'] = sanitizeString($_POST['study_aims'] ?? '');

    // Step 4: Part 2
    $data['methodology'] = sanitizeString($_POST['methodology'] ?? '');
    $data['ethical_considerations'] = sanitizeString($_POST['ethical_considerations'] ?? '');
    $data['expected_outcomes'] = sanitizeString($_POST['expected_outcomes'] ?? '');
    $data['nmimr_references'] = sanitizeString($_POST['nmimr_references'] ?? '');

    // Step 5: Part 3
    $data['work_plan'] = sanitizeString($_POST['work_plan'] ?? '');
    $data['budget'] = sanitizeString($_POST['budget'] ?? '');

    // Step 6: Signatures
    $data['pi_signature'] = sanitizeString($_POST['pi_signature'] ?? '');
    $data['pi_date'] = sanitizeString($_POST['pi_date'] ?? '');
    $data['copi_signature'] = sanitizeString($_POST['copi_signature'] ?? '');
    $data['copi_date'] = sanitizeString($_POST['copi_date'] ?? '');
    $data['final_confirmation'] = sanitizeString($_POST['final_confirmation'] ?? '');

    // Handle declarations (checkboxes) - stored as array of declaration_type values
    $declarations = $_POST['declarations'] ?? [];
    if (!is_array($declarations)) {
        $declarations = [];
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

    // Validate email formats
    if (!empty($data['pi_email']) && !isValidEmail($data['pi_email'])) {
        return [
            'success' => false,
            'message' => 'Invalid email format for PI email'
        ];
    }

    // Validate date format
    $datesToValidate = ['submission_date', 'pi_date', 'copi_date'];
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

    // Validate version number format (e.g., 1.0, 2.1)
    if (!empty($data['version_number']) && !preg_match('/^\d+(\.\d+)?$/', $data['version_number'])) {
        return [
            'success' => false,
            'message' => 'Invalid version number format. Use format like 1.0 or 2.1'
        ];
    }

    // Validate declarations (all 5 must be checked for submission)
    $requiredDeclarations = ['declaration_1', 'declaration_2', 'declaration_3', 'declaration_4', 'declaration_5'];
    $missingDeclarations = array_diff($requiredDeclarations, $declarations);
    if (!empty($missingDeclarations)) {
        return [
            'success' => false,
            'message' => 'You must agree to all declaration statements.'
        ];
    }

    // Validate final confirmation
    if (empty($data['final_confirmation'])) {
        return [
            'success' => false,
            'message' => 'You must confirm that all information is accurate and complete.'
        ];
    }

    // Handle file uploads
    $uploadBaseDir = '../../uploads/nmimr_applications/' . date('Y/m');
    $uploadResult = [];
    $uploadErrors = [];

    // Single file uploads
    $singleFileFields = [
        'consent_form' => 'Consent Form',
        'assent_form' => 'Assent Form'
    ];

    foreach ($singleFileFields as $field => $label) {
        if (isset($_FILES[$field])) {
            $result = handleFileUpload($_FILES[$field], $field, $uploadBaseDir);
            if (!$result['success']) {
                $uploadErrors[] = $result['error'];
            }
            $uploadResult[$field] = $result['path'];
        } else {
            $uploadResult[$field] = null;
        }
    }

    // Handle data instruments (multiple files)
    if (isset($_FILES['data_instruments'])) {
        $result = handleMultipleFileUploads($_FILES['data_instruments'], 'data_instruments', $uploadBaseDir);
        if (!$result['success']) {
            $uploadErrors[] = $result['error'];
        }
        $uploadResult['data_instruments'] = json_encode($result['paths'] ?? []);
    } else {
        $uploadResult['data_instruments'] = json_encode([]);
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
        $protocolNumber = generateProtocolNumber($conn);
    }

    // Get final confirmation boolean
    $finalConfirmation = !empty($data['final_confirmation']) && $data['final_confirmation'] !== 'false';

    try {
        // Begin transaction
        $conn->beginTransaction();

        if ($applicationId > 0) {

            // Update existing draft → make it submitted
            $sql = "
        UPDATE nmimr_applications SET
            protocol_number = :protocol_number,
            version_number = :version_number,
            submission_date = :submission_date,
            pi_name = :pi_name,
            pi_institution = :pi_institution,
            pi_address = :pi_address,
            pi_phone = :pi_phone,
            pi_email = :pi_email,
            co_investigators = :co_investigators,
            proposal_title = :proposal_title,
            research_type = :research_type,
            project_duration = :project_duration,
            funding_source = :funding_source,
            prior_irb = :prior_irb,
            abstract = :abstract,
            introduction = :introduction,
            literature_review = :literature_review,
            study_aims = :study_aims,
            methodology = :methodology,
            ethical_considerations = :ethical_considerations,
            expected_outcomes = :expected_outcomes,
            nmimr_references = :nmimr_references,
            work_plan = :work_plan,
            budget = :budget,
            pi_signature = :pi_signature,
            pi_date = :pi_date,
            copi_signature = :copi_signature,
            copi_date = :copi_date,
            final_confirmation = :final_confirmation,
            status = 'submitted',
            current_step = 6,
            updated_at = NOW()
        WHERE id = :application_id
        AND applicant_id = :applicant_id
    ";

            $params[':application_id'] = $applicationId;

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
        } else {

            // Insert into nmimr_applications table
            $sql = "
            INSERT INTO nmimr_applications (
                applicant_id, protocol_number, version_number, submission_date,
                pi_name, pi_institution, pi_address, pi_phone, pi_email,
                co_investigators,
                proposal_title, research_type, project_duration, funding_source, prior_irb,
                abstract, introduction, literature_review, study_aims,
                methodology, ethical_considerations, expected_outcomes, nmimr_references,
                work_plan, budget,
                pi_signature, pi_date, copi_signature, copi_date, final_confirmation,
                status, current_step, created_at, updated_at
            ) VALUES (
                :applicant_id, :protocol_number, :version_number, :submission_date,
                :pi_name, :pi_institution, :pi_address, :pi_phone, :pi_email,
                :co_investigators,
                :proposal_title, :research_type, :project_duration, :funding_source, :prior_irb,
                :abstract, :introduction, :literature_review, :study_aims,
                :methodology, :ethical_considerations, :expected_outcomes, :nmimr_references,
                :work_plan, :budget,
                :pi_signature, :pi_date, :copi_signature, :copi_date, :final_confirmation,
                :status, :current_step, NOW(), NOW()
            )
        ";

            $stmt = $conn->prepare($sql);

            // Bind all parameters
            $params = [
                ':applicant_id' => $userId,
                ':protocol_number' => $protocolNumber,
                ':version_number' => $data['version_number'],
                ':submission_date' => $data['submission_date'],
                ':pi_name' => $data['pi_name'],
                ':pi_institution' => $data['pi_institution'],
                ':pi_address' => $data['pi_address'],
                ':pi_phone' => $data['pi_phone'],
                ':pi_email' => $data['pi_email'],
                ':co_investigators' => $data['co_investigators'],
                ':proposal_title' => $data['proposal_title'],
                ':research_type' => $data['research_type'],
                ':project_duration' => $data['project_duration'],
                ':funding_source' => $data['funding_source'],
                ':prior_irb' => $data['prior_irb'],
                ':abstract' => $data['abstract'],
                ':introduction' => $data['introduction'],
                ':literature_review' => $data['literature_review'],
                ':study_aims' => $data['study_aims'],
                ':methodology' => $data['methodology'],
                ':ethical_considerations' => $data['ethical_considerations'],
                ':expected_outcomes' => $data['expected_outcomes'],
                ':nmimr_references' => $data['nmimr_references'],
                ':work_plan' => $data['work_plan'],
                ':budget' => $data['budget'],
                ':pi_signature' => $data['pi_signature'],
                ':pi_date' => $data['pi_date'],
                ':copi_signature' => $data['copi_signature'],
                ':copi_date' => $data['copi_date'],
                ':final_confirmation' => $finalConfirmation,
                ':status' => 'submitted',
                ':current_step' => 6  // All steps completed on submission
            ];

            $stmt->execute($params);

            $applicationId = $conn->lastInsertId();
        }




        // Insert Co-Investigators into nmimr_co_investigators table
        foreach ($coInvestigators as $copi) {
            $copiSql = "
                INSERT INTO nmimr_co_investigators (
                    application_id, name, qualification, department_email
                ) VALUES (
                    :application_id, :name, :qualification, :department_email
                )
            ";
            $copiStmt = $conn->prepare($copiSql);
            $copiStmt->execute([
                ':application_id' => $applicationId,
                ':name' => $copi['name'],
                ':qualification' => $copi['qualification'],
                ':department_email' => $copi['department_email']
            ]);
        }

        // Insert Documents into nmimr_application_documents table
        $documentTypes = [
            'consent_form' => 'consent_form',
            'assent_form' => 'assent_form'
        ];

        foreach ($documentTypes as $field => $docType) {
            if (!empty($uploadResult[$field])) {
                $docSql = "
                    INSERT INTO nmimr_application_documents (
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

        // Handle data instruments (multiple files)
        $dataInstruments = json_decode($uploadResult['data_instruments'] ?? '[]', true);
        foreach ($dataInstruments as $filePath) {
            $docSql = "
                INSERT INTO nmimr_application_documents (
                    application_id, document_type, file_name, file_path
                ) VALUES (
                    :application_id, 'data_instruments', :file_name, :file_path
                )
            ";
            $docStmt = $conn->prepare($docSql);
            $docStmt->execute([
                ':application_id' => $applicationId,
                ':file_name' => basename($filePath),
                ':file_path' => $filePath
            ]);
        }

        // Handle additional documents
        $additionalDocs = json_decode($uploadResult['additional_documents'] ?? '[]', true);
        foreach ($additionalDocs as $filePath) {
            $docSql = "
                INSERT INTO nmimr_application_documents (
                    application_id, document_type, file_name, file_path
                ) VALUES (
                    :application_id, 'additional', :file_name, :file_path
                )
            ";
            $docStmt = $conn->prepare($docSql);
            $docStmt->execute([
                ':application_id' => $applicationId,
                ':file_name' => basename($filePath),
                ':file_path' => $filePath
            ]);
        }

        // Insert Declarations into nmimr_declarations table
        foreach ($declarations as $declarationType) {
            if (in_array($declarationType, $requiredDeclarations)) {
                $declSql = "
                    INSERT INTO nmimr_declarations (
                        application_id, declaration_type, accepted, accepted_at
                    ) VALUES (
                        :application_id, :declaration_type, 1, NOW()
                    )
                ";
                $declStmt = $conn->prepare($declSql);
                $declStmt->execute([
                    ':application_id' => $applicationId,
                    ':declaration_type' => $declarationType
                ]);
            }
        }

        // Commit transaction
        $conn->commit();

        // Log the submission
        error_log("NMIMR application submitted successfully. ID: $applicationId, Protocol: $protocolNumber, User: $userId");

        return [
            'success' => true,
            'message' => 'Your NMIMR application has been submitted successfully!',
            'protocol_number' => $protocolNumber,
            'application_id' => $applicationId,
            'redirect' => '/applicant-dashboard?success=submitted&protocol=' . urlencode($protocolNumber)
        ];
    } catch (PDOException $e) {
        // Rollback transaction
        $conn->rollBack();

        // Log error
        error_log("Database error in nmimr_application_handler.php: " . $e->getMessage());

        return [
            'success' => false,
            'message' => 'An error occurred while saving your application. Please try again.'
        ];
    }
}

/**
 * Handle save draft request for NMIMR application
 * @return array Result with success status and message
 */
function handleSaveDraft()
{
    // Verify applicant is logged in
    $isLoggedIn = is_applicant_logged_in();

    if (!$isLoggedIn) {
        // Debug logging for authentication failure
        error_log("NMIMR Authentication Debug: logged_in=" . (isset($_SESSION['logged_in']) ? $_SESSION['logged_in'] : 'NOT SET') .
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

    // Get application ID and current step
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

    // Step 1: Protocol fields
    $data['protocol_number'] = sanitizeString($_POST['protocol_number'] ?? '');
    $data['version_number'] = sanitizeString($_POST['version_number'] ?? '');
    $data['submission_date'] = sanitizeString($_POST['submission_date'] ?? '');

    // Step 2: Section A - PI Info
    $data['pi_name'] = sanitizeString($_POST['pi_name'] ?? '');
    $data['pi_institution'] = sanitizeString($_POST['pi_institution'] ?? '');
    $data['pi_address'] = sanitizeString($_POST['pi_address'] ?? '');
    $data['pi_phone'] = sanitizeString($_POST['pi_phone'] ?? '');
    $data['pi_email'] = sanitizeString($_POST['pi_email'] ?? '');

    // Co-Investigators (dynamic fields) - stored as JSON
    $coInvestigators = [];
    $copiIndex = 1;
    while (isset($_POST['copi' . $copiIndex . '_name'])) {
        $copiName = sanitizeString($_POST['copi' . $copiIndex . '_name'] ?? '');
        if (!empty($copiName)) {
            $coInvestigators[] = [
                'name' => $copiName,
                'qualification' => sanitizeString($_POST['copi' . $copiIndex . '_qualification'] ?? ''),
                'department_email' => sanitizeString($_POST['copi' . $copiIndex . '_department_email'] ?? '')
            ];
        }
        $copiIndex++;
    }
    $data['co_investigators'] = json_encode($coInvestigators);

    // Step 2: Project Info
    $data['proposal_title'] = sanitizeString($_POST['proposal_title'] ?? '');

    // Research type checkboxes - stored as JSON array
    $researchTypes = [];
    if (!empty($_POST['research_type_biomedical'])) {
        $researchTypes[] = sanitizeString($_POST['research_type_biomedical']);
    }
    if (!empty($_POST['research_type_social'])) {
        $researchTypes[] = sanitizeString($_POST['research_type_social']);
    }
    if (!empty($_POST['research_type_other'])) {
        $researchTypes[] = 'Other: ' . sanitizeString($_POST['research_type_other_specify'] ?? '');
    }
    $data['research_type'] = json_encode($researchTypes);

    $data['project_duration'] = sanitizeString($_POST['project_duration'] ?? '');
    $data['funding_source'] = sanitizeString($_POST['funding_source'] ?? '');
    $data['prior_irb'] = sanitizeString($_POST['prior_irb'] ?? '');

    // Step 3: Part 1
    $data['abstract'] = sanitizeString($_POST['abstract'] ?? '');
    $data['introduction'] = sanitizeString($_POST['introduction'] ?? '');
    $data['literature_review'] = sanitizeString($_POST['literature_review'] ?? '');
    $data['study_aims'] = sanitizeString($_POST['study_aims'] ?? '');

    // Step 4: Part 2
    $data['methodology'] = sanitizeString($_POST['methodology'] ?? '');
    $data['ethical_considerations'] = sanitizeString($_POST['ethical_considerations'] ?? '');
    $data['expected_outcomes'] = sanitizeString($_POST['expected_outcomes'] ?? '');
    $data['nmimr_references'] = sanitizeString($_POST['nmimr_references'] ?? '');

    // Step 5: Part 3
    $data['work_plan'] = sanitizeString($_POST['work_plan'] ?? '');
    $data['budget'] = sanitizeString($_POST['budget'] ?? '');

    // Step 6: Signatures
    $data['pi_signature'] = sanitizeString($_POST['pi_signature'] ?? '');
    $data['pi_date'] = sanitizeString($_POST['pi_date'] ?? '');
    $data['copi_signature'] = sanitizeString($_POST['copi_signature'] ?? '');
    $data['copi_date'] = sanitizeString($_POST['copi_date'] ?? '');
    $data['final_confirmation'] = sanitizeString($_POST['final_confirmation'] ?? '');

    // Handle declarations (checkboxes)
    $declarations = $_POST['declarations'] ?? [];
    if (!is_array($declarations)) {
        $declarations = [];
    }

    // ============================================
    // Provide default values for NOT NULL columns
    // ============================================

    // Numeric fields - use "1.0" as default for version_number
    if (empty($data['version_number'])) {
        $data['version_number'] = '1.0';
    }

    // Date fields - use valid date format for draft saves
    $defaultDate = date('Y-m-d');
    if (empty($data['submission_date'])) {
        $data['submission_date'] = $defaultDate;
    }
    if (empty($data['pi_date'])) {
        $data['pi_date'] = $defaultDate;
    }
    if (empty($data['copi_date'])) {
        $data['copi_date'] = $defaultDate;
    }

    // Provide defaults for empty text fields (for NOT NULL compliance)
    $defaultFields = [
        'pi_name' => 'Not Provided',
        'pi_institution' => 'Not Provided',
        'pi_address' => 'Not Provided',
        'pi_phone' => 'Not Provided',
        'pi_email' => 'notprovided@example.com',
        'proposal_title' => 'Draft - ' . date('Y-m-d H:i:s'),
        'project_duration' => '1 month',
        'abstract' => '',
        'introduction' => '',
        'literature_review' => '',
        'study_aims' => '',
        'methodology' => '',
        'ethical_considerations' => '',
        'expected_outcomes' => '',
        'nmimr_references' => '',
        'work_plan' => '',
        'budget' => '',
        'pi_signature' => '',
        'copi_signature' => '',
        'co_investigators' => '[]',
        'research_type' => '[]',
        'funding_source' => '',
        'prior_irb' => ''
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
            $protocolNumber = generateProtocolNumber($conn);
        }

        // Handle file uploads (optional for draft)
        $uploadBaseDir = '../../uploads/nmimr_applications/' . date('Y/m');
        $uploadResult = [];

        // Single file uploads
        $singleFileFields = [
            'consent_form' => 'Consent Form',
            'assent_form' => 'Assent Form'
        ];

        foreach ($singleFileFields as $field => $label) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                $result = handleFileUpload($_FILES[$field], $field, $uploadBaseDir);
                $uploadResult[$field] = $result['success'] ? $result['path'] : null;
            } else {
                $uploadResult[$field] = null;
            }
        }

        // Handle data instruments (multiple files)
        if (isset($_FILES['data_instruments']) && !empty($_FILES['data_instruments']['name'][0])) {
            $result = handleMultipleFileUploads($_FILES['data_instruments'], 'data_instruments', $uploadBaseDir);
            $uploadResult['data_instruments'] = $result['success'] ? json_encode($result['paths'] ?? []) : json_encode([]);
        } else {
            $uploadResult['data_instruments'] = json_encode([]);
        }

        // Handle additional documents (multiple files)
        if (isset($_FILES['additional_documents']) && !empty($_FILES['additional_documents']['name'][0])) {
            $result = handleMultipleFileUploads($_FILES['additional_documents'], 'additional_documents', $uploadBaseDir);
            $uploadResult['additional_documents'] = $result['success'] ? json_encode($result['paths'] ?? []) : json_encode([]);
        } else {
            $uploadResult['additional_documents'] = json_encode([]);
        }

        $declarationsJson = json_encode($declarations);
        $finalConfirmation = !empty($data['final_confirmation']) && $data['final_confirmation'] !== 'false';

        // Check if this is a new draft or update
        if ($applicationId > 0) {
            // Update existing draft in nmimr_applications table
            $sql = "
                UPDATE nmimr_applications SET
                    protocol_number = :protocol_number,
                    version_number = :version_number,
                    submission_date = :submission_date,
                    pi_name = :pi_name,
                    pi_institution = :pi_institution,
                    pi_address = :pi_address,
                    pi_phone = :pi_phone,
                    pi_email = :pi_email,
                    co_investigators = :co_investigators,
                    proposal_title = :proposal_title,
                    research_type = :research_type,
                    project_duration = :project_duration,
                    funding_source = :funding_source,
                    prior_irb = :prior_irb,
                    abstract = :abstract,
                    introduction = :introduction,
                    literature_review = :literature_review,
                    study_aims = :study_aims,
                    methodology = :methodology,
                    ethical_considerations = :ethical_considerations,
                    expected_outcomes = :expected_outcomes,
                    nmimr_references = :nmimr_references,
                    work_plan = :work_plan,
                    budget = :budget,
                    pi_signature = :pi_signature,
                    pi_date = :pi_date,
                    copi_signature = :copi_signature,
                    copi_date = :copi_date,
                    final_confirmation = :final_confirmation,
                    status = :status,
                    current_step = :current_step,
                    updated_at = NOW()
                WHERE id = :application_id
            ";

            $params = [
                ':application_id' => $applicationId,
                ':protocol_number' => $protocolNumber,
                ':version_number' => $data['version_number'],
                ':submission_date' => $data['submission_date'],
                ':pi_name' => $data['pi_name'],
                ':pi_institution' => $data['pi_institution'],
                ':pi_address' => $data['pi_address'],
                ':pi_phone' => $data['pi_phone'],
                ':pi_email' => $data['pi_email'],
                ':co_investigators' => $data['co_investigators'],
                ':proposal_title' => $data['proposal_title'],
                ':research_type' => $data['research_type'],
                ':project_duration' => $data['project_duration'],
                ':funding_source' => $data['funding_source'],
                ':prior_irb' => $data['prior_irb'],
                ':abstract' => $data['abstract'],
                ':introduction' => $data['introduction'],
                ':literature_review' => $data['literature_review'],
                ':study_aims' => $data['study_aims'],
                ':methodology' => $data['methodology'],
                ':ethical_considerations' => $data['ethical_considerations'],
                ':expected_outcomes' => $data['expected_outcomes'],
                ':nmimr_references' => $data['nmimr_references'],
                ':work_plan' => $data['work_plan'],
                ':budget' => $data['budget'],
                ':pi_signature' => $data['pi_signature'],
                ':pi_date' => $data['pi_date'],
                ':copi_signature' => $data['copi_signature'],
                ':copi_date' => $data['copi_date'],
                ':final_confirmation' => $finalConfirmation,
                ':status' => 'draft',
                ':current_step' => $currentStep
            ];

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
        } else {
            // Insert new draft into nmimr_applications table
            $sql = "
                INSERT INTO nmimr_applications (
                    applicant_id, protocol_number, version_number, submission_date,
                    pi_name, pi_institution, pi_address, pi_phone, pi_email,
                    co_investigators,
                    proposal_title, research_type, project_duration, funding_source, prior_irb,
                    abstract, introduction, literature_review, study_aims,
                    methodology, ethical_considerations, expected_outcomes, nmimr_references,
                    work_plan, budget,
                    pi_signature, pi_date, copi_signature, copi_date, final_confirmation,
                    status, current_step, created_at, updated_at
                ) VALUES (
                    :applicant_id, :protocol_number, :version_number, :submission_date,
                    :pi_name, :pi_institution, :pi_address, :pi_phone, :pi_email,
                    :co_investigators,
                    :proposal_title, :research_type, :project_duration, :funding_source, :prior_irb,
                    :abstract, :introduction, :literature_review, :study_aims,
                    :methodology, :ethical_considerations, :expected_outcomes, :nmimr_references,
                    :work_plan, :budget,
                    :pi_signature, :pi_date, :copi_signature, :copi_date, :final_confirmation,
                    :status, :current_step, NOW(), NOW()
                )
            ";

            $params = [
                ':applicant_id' => $userId,
                ':protocol_number' => $protocolNumber,
                ':version_number' => $data['version_number'],
                ':submission_date' => $data['submission_date'],
                ':pi_name' => $data['pi_name'],
                ':pi_institution' => $data['pi_institution'],
                ':pi_address' => $data['pi_address'],
                ':pi_phone' => $data['pi_phone'],
                ':pi_email' => $data['pi_email'],
                ':co_investigators' => $data['co_investigators'],
                ':proposal_title' => $data['proposal_title'],
                ':research_type' => $data['research_type'],
                ':project_duration' => $data['project_duration'],
                ':funding_source' => $data['funding_source'],
                ':prior_irb' => $data['prior_irb'],
                ':abstract' => $data['abstract'],
                ':introduction' => $data['introduction'],
                ':literature_review' => $data['literature_review'],
                ':study_aims' => $data['study_aims'],
                ':methodology' => $data['methodology'],
                ':ethical_considerations' => $data['ethical_considerations'],
                ':expected_outcomes' => $data['expected_outcomes'],
                ':nmimr_references' => $data['nmimr_references'],
                ':work_plan' => $data['work_plan'],
                ':budget' => $data['budget'],
                ':pi_signature' => $data['pi_signature'],
                ':pi_date' => $data['pi_date'],
                ':copi_signature' => $data['copi_signature'],
                ':copi_date' => $data['copi_date'],
                ':final_confirmation' => $finalConfirmation,
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
        error_log("NMIMR application draft saved. ID: $applicationId, Protocol: $protocolNumber, User: $userId");

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
        error_log("Database error in save draft: " . $e->getMessage());

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
            $result = handleNmimrSubmission();
            break;
        case 'save_draft':
            $result = handleSaveDraft();
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
