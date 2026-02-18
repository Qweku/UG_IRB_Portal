<?php

/**
 * BaseAbstractHandler Class
 *
 * Abstract base class for all application handlers.
 * Contains ~80% of common functionality using the Template Method pattern.
 *
 * @package UGIRB\SubmissionEngine\Handlers
 */

namespace UGIRB\SubmissionEngine\Handlers;

use UGIRB\SubmissionEngine\Interfaces\IApplicationHandler;
use UGIRB\SubmissionEngine\Services\SessionManager;
use UGIRB\SubmissionEngine\Services\ValidationService;
use UGIRB\SubmissionEngine\Services\FileUploadService;
use UGIRB\SubmissionEngine\Services\ProtocolNumberGenerator;
use UGIRB\SubmissionEngine\Services\EmailService;

// Include notification functions for admin/chair notifications
require_once __DIR__ . '/../../functions/notification_functions.php';

abstract class BaseAbstractHandler implements IApplicationHandler
{
    /** @var \PDO Database connection */
    protected \PDO $db;

    /** @var array Configuration options */
    protected array $config;

    /** @var SessionManager Session manager */
    protected SessionManager $session;

    /** @var ValidationService Validator */
    protected ValidationService $validator;

    /** @var FileUploadService File uploader */
    protected FileUploadService $fileService;

    /** @var ProtocolNumberGenerator Protocol generator */
    protected ProtocolNumberGenerator $protocolGenerator;

    /** @var EmailService Email sender */
    protected ?EmailService $emailService;

    /** @var bool Whether submission is a draft */
    protected bool $isDraft = false;

    /** @var array Sanitized form data */
    protected array $sanitizedData = [];

    /** @var array Uploaded file paths */
    protected array $uploadedPaths = [];

    /**
     * Constructor
     *
     * @param \PDO|null $db Database connection (null for read-only operations)
     * @param array $config Configuration options
     */
    public function __construct(?\PDO $db = null, array $config = [])
    {
        $this->db = $db ?? $this->createMockDb();
        $this->config = $config;

        // Initialize services
        $this->initializeServices();
    }

    /**
     * Initialize all services
     */
    protected function initializeServices(): void
    {
        $this->session = new SessionManager();
        $this->validator = new ValidationService();
        $this->fileService = new FileUploadService();
        $this->protocolGenerator = new ProtocolNumberGenerator();

        if ($this->db !== null) {
            $this->emailService = new EmailService();
        }
    }

    /**
     * Create mock PDO for testing
     *
     * @return \PDO|null
     */
    private function createMockDb(): ?\PDO
    {
        return null;
    }

    /**
     * Set handler options after instantiation
     *
     * @param array $options Handler-specific options
     */
    public function setOptions(array $options): void
    {
        // Merge options with existing config
        $this->config = array_merge($this->config, $options);
    }

    /**
     * Handle the complete submission process
     *
     * Template method that defines the workflow for all submissions.
     *
     * @return array Result with success status and message
     */
    public function handleSubmission(): array
    {
        try {
            // Step 1: Initialize and authenticate
            $authResult = $this->authenticate();
            if (!$authResult['success']) {
                return $authResult;
            }

            // Step 2: Verify CSRF token
            $csrfResult = $this->verifyCsrf();
            if (!$csrfResult['success']) {
                return $csrfResult;
            }

            // Step 3: Sanitize input
            $sanitizeResult = $this->sanitizeInput();
            if (!$sanitizeResult['success']) {
                return $sanitizeResult;
            }

            // If draft mode, skip full validation and use simplified workflow
            if ($this->isDraft) {
                return $this->handleDraftSubmission();
            }

            // Step 4: Validate common fields
            $commonValidation = $this->validateCommonFields();
            if (!$commonValidation['success']) {
                return $commonValidation;
            }

            // Step 5: Validate type-specific fields
            $typeValidation = $this->validateTypeSpecific($this->sanitizedData);
            if (!$typeValidation['success']) {
                return $typeValidation;
            }

            // Step 6: Validate files
            $fileValidation = $this->validateFiles();
            if (!$fileValidation['success']) {
                return $fileValidation;
            }

            // Step 7: Handle file uploads
            $uploadResult = $this->handleFileUploads();
            if (!$uploadResult['success']) {
                return $uploadResult;
            }

            // Step 8: Generate protocol number if new application
            if (empty($this->sanitizedData['protocol_number'])) {
                $this->sanitizedData['protocol_number'] = $this->generateProtocolNumber();
            }

            // Step 9: Save to database
            $saveResult = $this->saveToDatabase();
            if (!$saveResult['success']) {
                return $saveResult;
            }

            // Step 10: Send confirmation email
            $this->sendConfirmation();

            // Step 11: Send notification to admin/chair about new application
            $this->sendNewApplicationNotification($saveResult);

            return $saveResult;

        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Handle draft submission
     *
     * Simplified workflow for draft saves - skips full validation,
     * file uploads, protocol generation, and emails.
     *
     * @return array Result with success status and message
     */
    protected function handleDraftSubmission(): array
    {
        // For drafts, we only do minimal validation - just check that basic fields are present
        // This allows saving partial progress without requiring all fields to be filled
        
        // Skip the strict field-by-field validation for drafts
        // Just save whatever data is available
        
        // Skip file uploads for drafts - just save the data
        $this->uploadedPaths = [];

        // Generate a temporary protocol number for drafts (will be finalized on submit)
        if (empty($this->sanitizedData['protocol_number'])) {
            $this->sanitizedData['protocol_number'] = $this->generateProtocolNumber();
        }

        // Save to database (will check for existing draft)
        $saveResult = $this->saveDraftToDatabase();

        if ($saveResult['success']) {
            return [
                'success' => true,
                'message' => 'Draft saved successfully.',
                'application_id' => $saveResult['application_id'],
                'protocol_number' => $saveResult['protocol_number'],
                'is_draft' => true
            ];
        }

        return $saveResult;
    }

    /**
     * Save draft to database - checks for existing draft first
     *
     * @return array Result with success status and message
     */
    protected function saveDraftToDatabase(): array
    {
        if ($this->db === null) {
            return [
                'success' => false,
                'message' => 'Database connection not available'
            ];
        }

        try {
            // Check if there's an existing draft for this applicant
            $existingDraftId = $this->findExistingDraft();

            $this->db->beginTransaction();

            if ($existingDraftId) {
                // Update existing draft
                $this->updateDraft($existingDraftId);
                $applicationId = $existingDraftId;
            } else {
                // Insert new draft
                $applicationId = $this->insertApplication();
                if (!$applicationId) {
                    throw new \Exception('Failed to insert draft application');
                }
                // Save type-specific details for new draft
                $this->saveTypeSpecific($applicationId);
                // Save documents for new draft
                $this->saveDocuments($applicationId);
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Draft saved successfully',
                'application_id' => $applicationId,
                'protocol_number' => $this->sanitizedData['protocol_number']
            ];

        } catch (\Exception $e) {
            $this->db->rollBack();
            return $this->handleError($e);
        }
    }

    /**
     * Find existing draft for current applicant
     *
     * @return int|null Existing draft ID or null if not found
     */
    protected function findExistingDraft(): ?int
    {
        $userId = $this->session->getUserId();
        $applicationType = $this->getType();

        $stmt = $this->db->prepare("
            SELECT id FROM applications
            WHERE applicant_id = :applicant_id
            AND application_type = :application_type
            AND status = 'draft'
            LIMIT 1
        ");

        $stmt->execute([
            ':applicant_id' => $userId,
            ':application_type' => $applicationType
        ]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ? (int) $result['id'] : null;
    }

    /**
     * Update existing draft
     *
     * @param int $applicationId Draft application ID
     */
    protected function updateDraft(int $applicationId): void
    {
        // Update main applications table
        $stmt = $this->db->prepare("
            UPDATE applications SET
                protocol_number = :protocol_number,
                version_number = :version_number,
                study_title = :study_title,
                research_type = :research_type,
                abstract = :abstract,
                ethical_considerations = :ethical_considerations,
                work_plan = :work_plan,
                budget = :budget,
                current_step = :current_step,
                updated_at = NOW()
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $applicationId,
            ':protocol_number' => $this->sanitizedData['protocol_number'] ?? '',
            ':version_number' => $this->sanitizedData['version_number'] ?? '1.0',
            ':study_title' => $this->sanitizedData['study_title'] ?? '',
            ':research_type' => json_encode($this->sanitizedData['research_type'] ?? []),
            ':abstract' => $this->sanitizedData['abstract'] ?? '',
            ':ethical_considerations' => $this->sanitizedData['ethical_considerations'] ?? '',
            ':work_plan' => $this->sanitizedData['work_plan'] ?? '',
            ':budget' => $this->sanitizedData['budget'] ?? '',
            ':current_step' => $this->sanitizedData['current_step'] ?? 1
        ]);

        // Update type-specific details
        $this->updateTypeSpecific($applicationId);
    }

    /**
     * Update type-specific details
     *
     * Override in subclass for type-specific update logic
     *
     * @param int $applicationId Application ID
     * @return bool Success
     */
    protected function updateTypeSpecific(int $applicationId): bool
    {
        // Default: call saveTypeSpecific which handles both insert and update
        // Subclasses should override if they have separate update logic
        return $this->saveTypeSpecific($applicationId);
    }

    /**
     * Get fields for a specific step
     *
     * @param int $step Step number
     * @return array Field names required for this step
     */
    protected function getFieldsForStep(int $step): array
    {
        // Default: return common required fields for step validation
        // Override in subclass for type-specific step fields
        return $this->getCommonRequiredFields();
    }

    /**
     * Validate form data
     *
     * @param array $data Form data
     * @return array ['success' => bool, 'errors' => array]
     */
    public function validate(array $data): array
    {
        return $this->validateCommonFields($data);
    }

    /**
     * Save application to database
     *
     * @param array $data Form data
     * @param array $files Uploaded files
     * @return array ['success' => bool, 'message' => string, 'application_id' => int]
     */
    public function save(array $data, array $files = []): array
    {
        // This is handled by handleSubmission()
        return [
            'success' => false,
            'message' => 'Use handleSubmission() method'
        ];
    }

    /**
     * Authenticate user
     *
     * @return array ['success' => bool, 'message' => string]
     */
    protected function authenticate(): array
    {
        if (!$this->session->isLoggedIn()) {
            return [
                'success' => false,
                'message' => 'You must be logged in to submit an application.',
                'redirect' => '/login'
            ];
        }

        $userId = $this->session->getUserId();
        if ($userId === 0) {
            return [
                'success' => false,
                'message' => 'User session not found. Please log in again.'
            ];
        }

        return ['success' => true];
    }

    /**
     * Verify CSRF token
     *
     * @return array ['success' => bool, 'message' => string]
     */
    protected function verifyCsrf(): array
    {
        $csrfToken = $_POST['csrf_token'] ?? '';
        $sessionCsrfToken = $this->session->get('csrf_token', '');

        if (empty($sessionCsrfToken) || !$this->session->validateCsrfToken($csrfToken)) {
            return [
                'success' => false,
                'message' => 'Invalid CSRF token. Please refresh the page and try again.'
            ];
        }

        return ['success' => true];
    }

    /**
     * Sanitize input data
     *
     * @return array ['success' => bool, 'message' => string]
     */
    protected function sanitizeInput(): array
    {
        // Sanitize POST data
        $this->sanitizedData = $this->validator->sanitizeArray($_POST);

        // Handle checkboxes and arrays
        $this->sanitizeArrays();

        // Set application type
        $this->sanitizedData['application_type'] = $this->getType();

        return ['success' => true];
    }

    /**
     * Sanitize array-type fields
     */
    protected function sanitizeArrays(): void
    {
        // Handle declarations (checkboxes)
        if (isset($_POST['declarations'])) {
            $this->sanitizedData['declarations'] = is_array($_POST['declarations'])
                ? $_POST['declarations']
                : [];
        }

        // Handle research types (checkboxes)
        if (isset($_POST['research_type'])) {
            $this->sanitizedData['research_type'] = is_array($_POST['research_type'])
                ? $_POST['research_type']
                : [$_POST['research_type']];
        }
    }

    /**
     * Validate common fields
     *
     * @param array|null $data Data to validate (uses sanitizedData if null)
     * @return array ['success' => bool, 'errors' => array]
     */
    protected function validateCommonFields(?array $data = null): array
    {
        $data = $data ?? $this->sanitizedData;

        // Get common required fields
        $commonFields = $this->getCommonRequiredFields();
        $requiredFields = array_merge($commonFields, $this->getRequiredFields());

        $validation = $this->validator->validateRequired($data, $requiredFields);

        if (!$validation['success']) {
            // Log missing fields for debugging
            error_log('Validation failed. Missing fields: ' . json_encode($validation['missing']));
            
            return [
                'success' => false,
                'message' => 'Please fill in all required fields.',
                'errors' => ['missing_fields' => $validation['missing']],
                'debug_missing' => $validation['missing']
            ];
        }

        // Validate version number format
        if (!empty($data['version_number']) &&
            !$this->validator->validateVersionNumber($data['version_number'])) {
            return [
                'success' => false,
                'message' => 'Invalid version number format. Use format like 1.0 or 2.1'
            ];
        }

        // Validate common emails
        $emailValidation = $this->validateEmails($data);
        if (!$emailValidation['success']) {
            return $emailValidation;
        }

        return ['success' => true];
    }

    /**
     * Get common required fields for all application types
     *
     * @return array Common required fields
     */
    protected function getCommonRequiredFields(): array
    {
        return [
            'version_number',
            'study_title',
            'abstract'
        ];
    }

    /**
     * Validate email fields
     *
     * @param array $data Form data
     * @return array ['success' => bool, 'message' => string]
     */
    protected function validateEmails(array $data): array
    {
        $emailFields = $this->getEmailFields();

        foreach ($emailFields as $field) {
            if (!empty($data[$field]) && !$this->validator->validateEmail($data[$field])) {
                return [
                    'success' => false,
                    'message' => 'Invalid email format for ' . str_replace('_', ' ', $field)
                ];
            }
        }

        return ['success' => true];
    }

    /**
     * Get email fields to validate
     *
     * @return array Email field names
     */
    protected function getEmailFields(): array
    {
        return ['student_email', 'supervisor1_email', 'supervisor2_email'];
    }

    /**
     * Validate files
     *
     * @return array ['success' => bool, 'errors' => array]
     */
    protected function validateFiles(): array
    {
        if (empty($_FILES) || (count($_FILES) === 1 && isset($_FILES['csrf_token']))) {
            // No files to validate
            return ['success' => true];
        }

        $requirements = $this->getFileRequirements();
        if (empty($requirements)) {
            return ['success' => true];
        }

        return $this->validator->validateFiles($_FILES, $requirements, !$this->isDraft);
    }

    /**
     * Handle file uploads
     *
     * @return array ['success' => bool, 'paths' => array, 'error' => string]
     */
    protected function handleFileUploads(): array
    {
        $this->uploadedPaths = [];
        $uploadDir = $this->getUploadBaseDir();

        foreach ($this->getFileRequirements() as $field => $config) {
            // Skip if no file uploaded
            if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
                $this->uploadedPaths[$field] = null;
                continue;
            }
            
            // Check if this is a multiple file upload
            $isMultiple = isset($config['type']) && $config['type'] === 'multiple';
            
            if ($isMultiple) {
                // Use uploadMultiple for multiple file uploads
                $result = $this->fileService->uploadMultiple($_FILES[$field], $field, $uploadDir);
                
                if (!$result['success']) {
                    return [
                        'success' => false,
                        'message' => 'File upload failed: ' . $result['error']
                    ];
                }
                
                $this->uploadedPaths[$field] = json_encode($result['paths'] ?? []);
            } else {
                // Single file upload
                $result = $this->fileService->upload($_FILES[$field], $field, $uploadDir);
                
                if (!$result['success']) {
                    return [
                        'success' => false,
                        'message' => 'File upload failed: ' . $result['error']
                    ];
                }
                
                $this->uploadedPaths[$field] = $result['path'] ?? null;
            }
        }

        return ['success' => true, 'paths' => $this->uploadedPaths];
    }

    /**
     * Generate protocol number
     *
     * @return string Protocol number
     */
    protected function generateProtocolNumber(): string
    {
        return $this->protocolGenerator->generate($this->db, $this->getType());
    }

    /**
     * Save to database
     *
     * @return array ['success' => bool, 'message' => string, 'application_id' => int, 'protocol_number' => string]
     */
    protected function saveToDatabase(): array
    {
        if ($this->db === null) {
            return [
                'success' => false,
                'message' => 'Database connection not available'
            ];
        }

        try {
            $this->db->beginTransaction();

            // For non-draft submissions, check if there's an existing draft to convert
            // If draft exists, update it to submitted status
            $existingDraftId = $this->findExistingDraft();

            if ($existingDraftId) {
                // Convert existing draft to submitted
                $this->convertDraftToSubmitted($existingDraftId);
                $applicationId = $existingDraftId;
            } else {
                // Insert new application
                $applicationId = $this->insertApplication();
                if (!$applicationId) {
                    throw new \Exception('Failed to insert application');
                }
                // Insert type-specific details
                $this->saveTypeSpecific($applicationId);
                // Save documents for new application
                $this->saveDocuments($applicationId);
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Application submitted successfully',
                'application_id' => $applicationId,
                'protocol_number' => $this->sanitizedData['protocol_number']
            ];

        } catch (\Exception $e) {
            $this->db->rollBack();
            return $this->handleError($e);
        }
    }

    /**
     * Convert existing draft to submitted status
     *
     * @param int $applicationId Draft application ID
     */
    protected function convertDraftToSubmitted(int $applicationId): void
    {
        // Update main applications table - change status from draft to submitted
        $stmt = $this->db->prepare("
            UPDATE applications SET
                protocol_number = :protocol_number,
                version_number = :version_number,
                study_title = :study_title,
                research_type = :research_type,
                abstract = :abstract,
                ethical_considerations = :ethical_considerations,
                work_plan = :work_plan,
                budget = :budget,
                status = 'submitted',
                current_step = :current_step,
                updated_at = NOW()
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $applicationId,
            ':protocol_number' => $this->sanitizedData['protocol_number'] ?? '',
            ':version_number' => $this->sanitizedData['version_number'] ?? '1.0',
            ':study_title' => $this->sanitizedData['study_title'] ?? '',
            ':research_type' => json_encode($this->sanitizedData['research_type'] ?? []),
            ':abstract' => $this->sanitizedData['abstract'] ?? '',
            ':ethical_considerations' => $this->sanitizedData['ethical_considerations'] ?? '',
            ':work_plan' => $this->sanitizedData['work_plan'] ?? '',
            ':budget' => $this->sanitizedData['budget'] ?? '',
            ':current_step' => $this->sanitizedData['current_step'] ?? 1
        ]);

        // Update type-specific details
        $this->updateTypeSpecific($applicationId);

        // Save documents - uploadedPaths already populated from initial handleFileUploads() call
        // Note: Don't call handleFileUploads() again as $_FILES is cleared after first upload
        if (!empty($this->uploadedPaths)) {
            $this->saveDocuments($applicationId);
        }
    }

    /**
     * Insert main application record
     *
     * @return int Application ID
     */
    protected function insertApplication(): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO applications (
                applicant_id, application_type, protocol_number,
                version_number, study_title, research_type,
                abstract, ethical_considerations, work_plan,
                budget, status, current_step,
                created_at, updated_at
            ) VALUES (
                :applicant_id, :application_type, :protocol_number,
                :version_number, :study_title, :research_type,
                :abstract, :ethical_considerations, :work_plan,
                :budget, :status, :current_step,
                NOW(), NOW()
            )
        ");

        $stmt->execute([
            ':applicant_id' => $this->session->getUserId(),
            ':application_type' => $this->getType(),
            ':protocol_number' => $this->sanitizedData['protocol_number'] ?? '',
            ':version_number' => $this->sanitizedData['version_number'] ?? '1.0',
            ':study_title' => $this->sanitizedData['study_title'] ?? '',
            ':research_type' => json_encode($this->sanitizedData['research_type'] ?? []),
            ':abstract' => $this->sanitizedData['abstract'] ?? '',
            ':ethical_considerations' => $this->sanitizedData['ethical_considerations'] ?? '',
            ':work_plan' => $this->sanitizedData['work_plan'] ?? '',
            ':budget' => $this->sanitizedData['budget'] ?? '',
            ':status' => $this->isDraft ? 'draft' : 'submitted',
            ':current_step' => 1
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Save type-specific details
     *
     * @param int $applicationId Application ID
     * @return bool Success
     */
    abstract protected function saveTypeSpecific(int $applicationId): bool;

    /**
     * Save document records
     *
     * @param int $applicationId Application ID
     */
    protected function saveDocuments(int $applicationId): void
    {
        foreach ($this->uploadedPaths as $field => $path) {
            if ($path === null || $path === '') {
                continue;
            }

            $documentType = $this->getDocumentType($field);
            $stmt = $this->db->prepare("
                INSERT INTO application_documents (
                    application_id, document_type, file_name, file_path,
                    uploaded_at, uploaded_by
                ) VALUES (
                    :application_id, :document_type, :file_name, :file_path,
                    NOW(), :uploaded_by
                )
            ");

            $result = $stmt->execute([
                ':application_id' => $applicationId,
                ':document_type' => $documentType,
                ':file_name' => basename($path),
                ':file_path' => $path,
                ':uploaded_by' => $this->session->getUserId()
            ]);
            
            if (!$result) {
                $error = $stmt->errorInfo();
                error_log("[saveDocuments] INSERT FAILED for field: $field - Error: " . print_r($error, true));
            }
        }
    }

    /**
     * Get document type for field
     *
     * @param string $field Field name
     * @return string Document type
     */
    protected function getDocumentType(string $field): string
    {
        return match ($field) {
            'consent_form' => 'consent_form',
            'data_instruments' => 'data_instruments',
            'approval_letter' => 'approval_letter',
            'assent_form' => 'assent_form',
            'collaboration_letter' => 'collaboration_letter',
            default => $field
        };
    }

    /**
     * Send confirmation email
     */
    protected function sendConfirmation(): void
    {
        if ($this->emailService === null) {
            return;
        }

        $email = $this->session->get('email', '') ?: $this->getEmailFromData();
        if (empty($email)) {
            return;
        }

        $this->emailService->sendSubmissionConfirmation(
            $email,
            $this->sanitizedData['protocol_number'],
            $this->getType()
        );
    }

    /**
     * Send notification to admin/chair about new application submission
     *
     * @param array $saveResult Result from saveToDatabase containing application_id and study_title
     */
    protected function sendNewApplicationNotification(array $saveResult): void
    {
        $applicationId = $saveResult['application_id'] ?? 0;
        $studyTitle = $this->sanitizedData['study_title'] ?? 'Untitled Study';
        $piName = $this->getPiName();

        if ($applicationId > 0) {
            createChairNewApplicationNotification($applicationId, $studyTitle, $piName);
        }
    }

    /**
     * Get PI name from form data
     *
     * @return string PI name
     */
    protected function getPiName(): string
    {
        // Try various PI name fields
        return $this->sanitizedData['pi_name'] 
            ?? $this->sanitizedData['supervisor1_name'] 
            ?? $this->session->get('full_name', 'Unknown PI')
            ?? 'Principal Investigator';
    }

    /**
     * Get email from form data
     *
     * @return string|null Email
     */
    protected function getEmailFromData(): ?string
    {
        return $this->sanitizedData['student_email']
            ?? $this->sanitizedData['pi_email']
            ?? null;
    }

    /**
     * Handle errors
     *
     * @param \Exception $e Exception
     * @return array Error response
     */
    protected function handleError(\Exception $e): array
    {
        error_log('Submission Error: ' . $e->getMessage());

        return [
            'success' => false,
            'message' => 'An error occurred while processing your application. Please try again.',
            'errors' => [
                'system' => $e->getMessage()
            ]
        ];
    }

    /**
     * Check if this is a final submission
     *
     * @param array|null $data Form data
     * @return bool True if final submission
     */
    protected function isFinalSubmission(?array $data = null): bool
    {
        $data = $data ?? $this->sanitizedData;
        return !($data['save_draft'] ?? false);
    }

    /**
     * Get submission mode
     *
     * @return string 'draft' or 'submit'
     */
    public function getSubmissionMode(): string
    {
        return $this->isDraft ? 'draft' : 'submit';
    }

    /**
     * Set submission mode
     *
     * @param bool $isDraft Whether this is a draft
     */
    public function setDraftMode(bool $isDraft): void
    {
        $this->isDraft = $isDraft;
    }

    /**
     * Set database connection
     *
     * @param \PDO $db Database connection
     */
    public function setDb(\PDO $db): void
    {
        $this->db = $db;
    }

    /**
     * Get database connection
     *
     * @return \PDO|null
     */
    public function getDb(): ?\PDO
    {
        return $this->db;
    }

    /**
     * Set configuration
     *
     * @param array $config Configuration
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * Get configuration
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get protocol number prefix
     *
     * @return string
     */
    abstract public function getProtocolPrefix(): string;

    /**
     * Get application type
     *
     * @return string
     */
    abstract public function getType(): string;

    /**
     * Get type-specific required fields
     *
     * @return array
     */
    abstract public function getRequiredFields(): array;

    /**
     * Get file upload requirements
     *
     * @return array
     */
    abstract public function getFileRequirements(): array;

    /**
     * Validate type-specific fields
     *
     * @param array $data Form data
     * @return array ['success' => bool, 'errors' => array]
     */
    abstract protected function validateTypeSpecific(array $data): array;

    /**
     * Get upload base directory
     *
     * @return string
     */
    abstract protected function getUploadBaseDir(): string;
}
