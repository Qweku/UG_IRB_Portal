<?php

/**
 * NonNmimrHandler Class
 *
 * Handles Non-NMIMR (External) application form submissions.
 * Implements type-specific validation and saving for external applications.
 *
 * @package UGIRB\SubmissionEngine\Handlers
 */

namespace UGIRB\SubmissionEngine\Handlers;

class NonNmimrHandler extends BaseAbstractHandler
{
    /** @var string Application type identifier */
    public const TYPE = 'non_nmimr';

    /** @var string Protocol prefix */
    public const PREFIX = 'EXT';

    /** @var bool Enable debug logging */
    private bool $debug = true;

    /**
     * Log debug message
     *
     * @param string $message
     * @param array $data
     */
    // private function debugLog(string $message, array $data = []): void
    // {
    //     if ($this->debug) {
    //         $logMessage = '[NonNmimrHandler] ' . $message;
    //         if (!empty($data)) {
    //             $logMessage .= ': ' . json_encode($data);
    //         }
    //         error_log($logMessage);
    //     }
    // }

    /**
     * Get protocol number prefix
     *
     * @return string Protocol prefix
     */
    public function getProtocolPrefix(): string
    {
        return self::PREFIX;
    }

    /**
     * Get application type
     *
     * @return string Application type
     */
    public function getType(): string
    {
        return self::TYPE;
    }

    /**
     * Get type-specific required fields
     *
     * @return array Required field names
     */
    public function getRequiredFields(): array
    {
        return [
            // PI Info
            'pi_name',
            'pi_institution',
            'pi_address',
            'pi_phone_number',
            'pi_email',

            // Project Info
            'collaborating_institutions',
            'duration',
            'research_type',

            // Signatures
            'pi_signature',
            'pi_date',
            'final_confirmation'
        ];
    }

    /**
     * Get file upload requirements
     *
     * @return array Field requirements
     */
    public function getFileRequirements(): array
    {
        return [
            'consent_form' => [
                'required' => true,
                'label' => 'Consent Form',
                'type' => 'single',
                'allowed_types' => ['pdf', 'doc', 'docx'],
                'max_size' => 10 * 1024 * 1024 // 10MB
            ],
            'consolidatedProposal' => [
                'required' => true,
                'label' => 'Consolidated Proposal Document',
                'type' => 'single',
                'allowed_types' => ['pdf'],
                'max_size' => 20 * 1024 * 1024 // 20MB
            ],
            'assent_form' => [
                'required' => false,
                'label' => 'Assent Form',
                'type' => 'single',
                'allowed_types' => ['pdf', 'doc', 'docx'],
                'max_size' => 10 * 1024 * 1024
            ],
            'approval_letters' => [
                'required' => false,
                'label' => 'Approval Letters',
                'type' => 'multiple',
                'allowed_types' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
                'max_size' => 5 * 1024 * 1024, // 5MB per file
                'max_files' => 10
            ],
            'data_instruments' => [
                'required' => false,
                'label' => 'Data Collection Instruments',
                'type' => 'single',
                'allowed_types' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
                'max_size' => 10 * 1024 * 1024
            ]
        ];
    }

    /**
     * Get email fields to validate
     *
     * @return array Email field names
     */
    protected function getEmailFields(): array
    {
        return [
            'pi_email',
            'co_pi_email'
        ];
    }

    /**
 * Save document records (override for multiple file support)
 *
 * @param int $applicationId Application ID
 */
protected function saveDocuments(int $applicationId): void
{
    $this->debugLog('Saving documents', ['uploadedPaths' => $this->uploadedPaths]);
    
    foreach ($this->uploadedPaths as $field => $path) {
        $requirements = $this->getFileRequirements();
        $config = $requirements[$field] ?? null;

        if ($config === null) {
            $this->debugLog('No config for field', ['field' => $field]);
            continue;
        }

        if (empty($path)) {
            $this->debugLog('Empty path for field', ['field' => $field]);
            continue;
        }

        if ($config['type'] === 'multiple') {
            // Handle multiple files
            $paths = is_string($path) ? json_decode($path, true) : $path;
            if (is_array($paths)) {
                foreach ($paths as $singlePath) {
                    if (!empty($singlePath)) {
                        $this->insertDocument($applicationId, $field, $singlePath);
                    }
                }
            }
        } else {
            // Handle single file
            if (is_string($path) && !empty($path)) {
                $this->insertDocument($applicationId, $field, $path);
            }
        }
    }
}

/**
 * Insert document record
 *
 * @param int $applicationId Application ID
 * @param string $field Field name
 * @param string $path File path
 */
private function insertDocument(int $applicationId, string $field, string $path): void
{
    $documentType = $this->getDocumentType($field);
    
    $this->debugLog('Inserting document', [
        'application_id' => $applicationId,
        'document_type' => $documentType,
        'file_name' => basename($path),
        'file_path' => $path
    ]);
    
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
        $this->debugLog('Insert document failed', ['error' => $error]);
    } else {
        $this->debugLog('Insert document successful');
    }
}

    /**
     * Sanitize array-type fields
     */
    protected function sanitizeArrays(): void
    {
        $this->debugLog('Sanitizing arrays', ['_POST_keys' => array_keys($_POST)]);

        // Handle PI declarations (checkboxes)
        if (isset($_POST['pi_declarations'])) {
            $this->sanitizedData['pi_declarations'] = is_array($_POST['pi_declarations'])
                ? $_POST['pi_declarations']
                : [];
            $this->debugLog('PI declarations', $this->sanitizedData['pi_declarations']);
        }

        // Handle research type
        if (!empty($_POST['research_type'])) {
            $this->sanitizedData['research_type'] = $this->validator->sanitizeString($_POST['research_type']);
            
            // Handle "Other" research type
            if ($_POST['research_type'] === 'Other' && !empty($_POST['research_type_other'])) {
                $this->sanitizedData['research_type_other'] = $this->validator->sanitizeString($_POST['research_type_other']);
                $this->sanitizedData['research_type'] = 'Other';
            }
        } else {
            $this->sanitizedData['research_type'] = '';
        }

        // Handle final confirmation
        $this->sanitizedData['final_confirmation'] = isset($_POST['final_confirmation']) ? '1' : '0';
    }

    /**
     * Validate type-specific fields
     *
     * @param array $data Form data
     * @return array ['success' => bool, 'errors' => array]
     */
    protected function validateTypeSpecific(array $data): array
    {
        $this->debugLog('Starting type-specific validation', ['data_keys' => array_keys($data)]);
        $errors = [];

        // Validate PI email
        if (empty($data['pi_email'])) {
            $errors[] = 'PI email is required';
        } elseif (!$this->validator->validateEmail($data['pi_email'])) {
            $errors[] = 'Invalid PI email format';
        }

        // Validate Co-PI email if provided
        if (!empty($data['co_pi_email']) && !$this->validator->validateEmail($data['co_pi_email'])) {
            $errors[] = 'Invalid Co-PI email format';
        }

        // Validate PI date
        if (!empty($data['pi_date'])) {
            if (!$this->validator->validateDate($data['pi_date'])) {
                $errors[] = 'Invalid PI date format';
            }
        } else {
            $errors[] = 'PI date is required';
        }

        // Validate Co-PI date if provided
        if (!empty($data['co_pi_date']) && !$this->validator->validateDate($data['co_pi_date'])) {
            $errors[] = 'Invalid Co-PI date format';
        }

        // Validate duration
        if (empty($data['duration'])) {
            $errors[] = 'Project duration is required';
        } elseif (!is_numeric($data['duration']) || $data['duration'] < 1 || $data['duration'] > 120) {
            $errors[] = 'Duration must be a number between 1 and 120 months';
        }

        // Validate research type
        if (empty($data['research_type'])) {
            $errors[] = 'Research type is required';
        }

        // Validate collaborating institutions
        if (empty($data['collaborating_institutions'])) {
            $errors[] = 'Collaborating institutions are required';
        }

        // Validate final confirmation for final submission (not draft)
        if (!$this->isDraft) {
            if (empty($data['final_confirmation']) || $data['final_confirmation'] !== '1') {
                $errors[] = 'You must confirm that all information is accurate and complete';
            }

            // Validate PI declarations for final submission
            if (empty($data['pi_declarations']) || count($data['pi_declarations']) < 5) {
                $errors[] = 'You must agree to all PI declaration statements';
            }
        }

        $this->debugLog('Validation complete', ['has_errors' => !empty($errors), 'errors' => $errors]);

        return [
            'success' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Validate files before upload
     *
     * @return array ['success' => bool, 'errors' => array]
     */
    protected function validateFiles(): array
    {
        $this->debugLog('Starting file validation', ['_FILES_keys' => array_keys($_FILES)]);
        
        $requirements = $this->getFileRequirements();
        $errors = [];

        foreach ($requirements as $field => $config) {
            // Skip if not required and no file uploaded
            if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
                if ($config['required'] && !$this->isDraft) {
                    $errors[] = $config['label'] . ' is required';
                }
                continue;
            }

            $file = $_FILES[$field];
            
            // Handle multiple file uploads
            if ($config['type'] === 'multiple') {
                $fileCount = is_array($file['name']) ? count($file['name']) : 1;
                
                if ($fileCount > ($config['max_files'] ?? 99)) {
                    $errors[] = $config['label'] . ' exceeds maximum of ' . ($config['max_files'] ?? 99) . ' files';
                }
                
                // Validate each file in multiple upload
                for ($i = 0; $i < $fileCount; $i++) {
                    $singleFile = [
                        'name' => $file['name'][$i],
                        'type' => $file['type'][$i],
                        'tmp_name' => $file['tmp_name'][$i],
                        'error' => $file['error'][$i],
                        'size' => $file['size'][$i]
                    ];
                    
                    $fileError = $this->validateSingleFile($singleFile, $config, $field);
                    if ($fileError) {
                        $errors[] = $fileError;
                    }
                }
            } else {
                // Validate single file
                $fileError = $this->validateSingleFile($file, $config, $field);
                if ($fileError) {
                    $errors[] = $fileError;
                }
            }
        }

        $this->debugLog('File validation complete', ['has_errors' => !empty($errors), 'errors' => $errors]);

        return [
            'success' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Validate a single file
     *
     * @param array $file File array from $_FILES
     * @param array $config File configuration
     * @param string $field Field name
     * @return string|null Error message or null if valid
     */
    private function validateSingleFile(array $file, array $config, string $field): ?string
    {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds server upload limit',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds form upload limit',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
            ];
            return $uploadErrors[$file['error']] ?? 'Unknown upload error';
        }

        // Check file size
        if ($file['size'] > ($config['max_size'] ?? 10 * 1024 * 1024)) {
            $maxSizeMB = ($config['max_size'] ?? 10 * 1024 * 1024) / 1024 / 1024;
            return $config['label'] . ' exceeds maximum size of ' . $maxSizeMB . 'MB';
        }

        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $config['allowed_types'] ?? ['pdf', 'doc', 'docx'])) {
            return $config['label'] . ' has invalid file type. Allowed: ' . implode(', ', $config['allowed_types'] ?? ['pdf', 'doc', 'docx']);
        }

        return null;
    }

    /**
     * Get upload base directory
     *
     * @return string Upload directory path
     */
    protected function getUploadBaseDir(): string
    {
        $dir = 'non_nmimr_applications/' . date('Y/m');
        $this->debugLog('Upload directory', ['dir' => $dir]);
        return $dir;
    }

    /**
     * Save type-specific details
     *
     * @param int $applicationId Application ID
     * @return bool Success
     */
    protected function saveTypeSpecific(int $applicationId): bool
    {
        $this->debugLog('Saving type-specific details', ['application_id' => $applicationId, 'is_draft' => $this->isDraft]);
        
        // Check if a record already exists for this application_id
        $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM non_nmimr_application_details WHERE application_id = :application_id");
        $checkStmt->execute([':application_id' => $applicationId]);
        $recordExists = $checkStmt->fetchColumn() > 0;

        $this->debugLog('Record exists check', ['exists' => $recordExists]);

        if ($recordExists) {
            return $this->updateNonTypeSpecific($applicationId);
        }

        return $this->insertNonTypeSpecific($applicationId);
    }

    /**
     * Insert new non-NMIMR application details
     *
     * @param int $applicationId Application ID
     * @return bool Success
     */
    private function insertNonTypeSpecific(int $applicationId): bool
    {
        $this->debugLog('Inserting new non-NMIMR details', ['application_id' => $applicationId]);

        $stmt = $this->db->prepare("
            INSERT INTO non_nmimr_application_details (
                application_id,
                pi_name,
                pi_institution,
                pi_address,
                pi_phone_number,
                pi_fax,
                pi_email,
                pi_signature,
                pi_date,
                co_pi_name,
                co_pi_qualification,
                co_pi_department,
                co_pi_address,
                co_pi_phone_number,
                co_pi_fax,
                co_pi_email,
                co_pi_signature,
                co_pi_date,
                prior_scientific_review,
                prior_irb_review,
                collaborating_institutions,
                funding_source,
                duration
               
            ) VALUES (
                :application_id,
                 :pi_name,
                :pi_institution,
                :pi_address,
                :pi_phone_number,
                :pi_fax,
                :pi_email,
                :pi_signature,
                :pi_date,
                :co_pi_name,
                :co_pi_qualification,
                :co_pi_department,
                :co_pi_address,
                :co_pi_phone_number,
                :co_pi_fax,
                :co_pi_email,
                :co_pi_signature,
                :co_pi_date,
                :prior_scientific_review,
                :prior_irb_review,
                :collaborating_institutions,
                :funding_source,
                :duration
                
            )
        ");

        $params = [
            ':application_id' => $applicationId,
            ':pi_name' => $this->sanitizedData['pi_name'] ?? '',
            ':pi_institution' => $this->sanitizedData['pi_institution'] ?? '',
            ':pi_address' => $this->sanitizedData['pi_address'] ?? '',
            ':pi_phone_number' => $this->sanitizedData['pi_phone_number'] ?? '',
            ':pi_fax' => $this->sanitizedData['pi_fax'] ?? '',
            ':pi_email' => $this->sanitizedData['pi_email'] ?? '',
            ':pi_signature' => $this->sanitizedData['pi_signature'] ?? '',
            ':pi_date' => $this->sanitizedData['pi_date'] ?? null,
            ':co_pi_name' => $this->sanitizedData['co_pi_name'] ?? '',
            ':co_pi_qualification' => $this->sanitizedData['co_pi_qualification'] ?? '',
            ':co_pi_department' => $this->sanitizedData['co_pi_department'] ?? '',
            ':co_pi_address' => $this->sanitizedData['co_pi_address'] ?? '',
            ':co_pi_phone_number' => $this->sanitizedData['co_pi_phone_number'] ?? '',
            ':co_pi_fax' => $this->sanitizedData['co_pi_fax'] ?? '',
            ':co_pi_email' => $this->sanitizedData['co_pi_email'] ?? '',
            ':co_pi_signature' => $this->sanitizedData['co_pi_signature'] ?? '',
            ':co_pi_date' => $this->sanitizedData['co_pi_date'] ?? null,
            ':prior_scientific_review' => $this->sanitizedData['prior_scientific_review'] ?? '',
            ':prior_irb_review' => $this->sanitizedData['prior_irb_review'] ?? '',
            ':collaborating_institutions' => $this->sanitizedData['collaborating_institutions'] ?? '',
            ':funding_source' => $this->sanitizedData['funding_source'] ?? '',
            ':duration' => $this->sanitizedData['duration'] ?? ''
            
        ];

        $this->debugLog('Insert params', array_keys($params));
        
        $result = $stmt->execute($params);
        
        if (!$result) {
            $error = $stmt->errorInfo();
            $this->debugLog('Insert failed', ['error' => $error]);
        } else {
            $this->debugLog('Insert successful');
        }
        
        return $result;
    }

    /**
     * Update existing non-NMIMR application details
     *
     * @param int $applicationId Application ID
     * @return bool Success
     */
    private function updateNonTypeSpecific(int $applicationId): bool
    {
        $this->debugLog('Updating non-NMIMR details', ['application_id' => $applicationId]);

        $stmt = $this->db->prepare("
            UPDATE non_nmimr_application_details SET
                pi_name = :pi_name,
                pi_institution = :pi_institution,
                pi_address = :pi_address,
                pi_phone_number = :pi_phone_number,
                pi_fax = :pi_fax,
                pi_email = :pi_email,
                pi_signature = :pi_signature,
                pi_date = :pi_date,
                co_pi_name = :co_pi_name,
                co_pi_qualification = :co_pi_qualification,
                co_pi_department = :co_pi_department,
                co_pi_address = :co_pi_address,
                co_pi_phone_number = :co_pi_phone_number,
                co_pi_fax = :co_pi_fax,
                co_pi_email = :co_pi_email,
                co_pi_signature = :co_pi_signature,
                co_pi_date = :co_pi_date,
                prior_scientific_review = :prior_scientific_review,
                prior_irb_review = :prior_irb_review,
                collaborating_institutions = :collaborating_institutions,
                funding_source = :funding_source,
                duration = :duration               
            WHERE application_id = :application_id
        ");

        $params = [
            ':application_id' => $applicationId,
            ':pi_name' => $this->sanitizedData['pi_name'] ?? '',
            ':pi_institution' => $this->sanitizedData['pi_institution'] ?? '',
            ':pi_address' => $this->sanitizedData['pi_address'] ?? '',
            ':pi_phone_number' => $this->sanitizedData['pi_phone_number'] ?? '',
            ':pi_fax' => $this->sanitizedData['pi_fax'] ?? '',
            ':pi_email' => $this->sanitizedData['pi_email'] ?? '',
            ':pi_signature' => $this->sanitizedData['pi_signature'] ?? '',
            ':pi_date' => $this->sanitizedData['pi_date'] ?? null,
            ':co_pi_name' => $this->sanitizedData['co_pi_name'] ?? '',
            ':co_pi_qualification' => $this->sanitizedData['co_pi_qualification'] ?? '',
            ':co_pi_department' => $this->sanitizedData['co_pi_department'] ?? '',
            ':co_pi_address' => $this->sanitizedData['co_pi_address'] ?? '',
            ':co_pi_phone_number' => $this->sanitizedData['co_pi_phone_number'] ?? '',
            ':co_pi_fax' => $this->sanitizedData['co_pi_fax'] ?? '',
            ':co_pi_email' => $this->sanitizedData['co_pi_email'] ?? '',
            ':co_pi_signature' => $this->sanitizedData['co_pi_signature'] ?? '',
            ':co_pi_date' => $this->sanitizedData['co_pi_date'] ?? null,
            ':prior_scientific_review' => $this->sanitizedData['prior_scientific_review'] ?? '',
            ':prior_irb_review' => $this->sanitizedData['prior_irb_review'] ?? '',
            ':collaborating_institutions' => $this->sanitizedData['collaborating_institutions'] ?? '',
            ':funding_source' => $this->sanitizedData['funding_source'] ?? '',
            ':duration' => $this->sanitizedData['duration'] ?? ''
        //    ':final_pdf' => $this->sanitizedData['final_pdf'] ?? '0'
        ];

        $this->debugLog('Update params', array_keys($params));
        
        $result = $stmt->execute($params);
        
        if (!$result) {
            $error = $stmt->errorInfo();
            $this->debugLog('Update failed', ['error' => $error]);
        } else {
            $this->debugLog('Update successful');
        }
        
        return $result;
    }

    /**
     * Get document type for field
     *
     * @param string $field Field name
     * @return string Document type
     */
    protected function getDocumentType(string $field): string
    {
        $mapping = [
            'consent_form' => 'consent_form',
            'consolidatedProposal' => 'consolidated_proposal',
            'assent_form' => 'assent_form',
            'approval_letters' => 'approval_letter',
            'data_instruments' => 'data_instruments'
        ];
        
        return $mapping[$field] ?? $field;
    }

    /**
     * Get email from form data
     *
     * @return string|null Email
     */
    protected function getEmailFromData(): ?string
    {
        return $this->sanitizedData['pi_email'] ?? null;
    }

    /**
     * Get PI name from form data
     *
     * @return string PI name
     */
    protected function getPiName(): string
    {
        return $this->sanitizedData['pi_name'] ?? 'Principal Investigator';
    }

    /**
     * Handle submission - override for additional logging
     *
     * @return array Result with success status and message
     */
    public function handleSubmission(): array
    {
        $this->debugLog('Starting handleSubmission', [
            'is_draft' => $this->isDraft,
            'post_keys' => array_keys($_POST),
            'files_keys' => array_keys($_FILES)
        ]);
        
        $result = parent::handleSubmission();
        
        $this->debugLog('handleSubmission result', ['success' => $result['success'], 'message' => $result['message'] ?? '']);
        
        return $result;
    }

    /**
     * Get fields for a specific step
     *
     * @param int $step Step number
     * @return array Field names required for this step
     */
    protected function getFieldsForStep(int $step): array
    {
        switch ($step) {
            case 1: // Basic Information
                return [
                    'pi_name',
                    'pi_institution',
                    'pi_address',
                    'pi_phone_number',
                    'pi_email',
                    'research_type'
                ];
            
            case 2: // Co-PI and Project Information
                return [
                    'co_pi_name',
                    'co_pi_qualification',
                    'co_pi_department',
                    'co_pi_address',
                    'co_pi_phone_number',
                    'co_pi_email',
                    'collaborating_institutions',
                    'duration',
                    'funding_source'
                ];
            
            case 3: // Declarations
                return [
                    'pi_signature',
                    'pi_date',
                    'pi_declarations',
                    'final_confirmation'
                ];
            
            default:
                return [];
        }
    }
}