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

            // Research Content
            'introduction',
            'aims',
            'methodology',
            'expected_outcomes',
            'application_references',

            // Signatures
            'pi_signature',
            'pi_date'
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
                'type' => 'single'
            ],
            'final_pdf' => [
                'required' => true,
                'label' => 'Final Protocol PDF',
                'type' => 'single'
            ],
            'assent_form' => [
                'required' => false,
                'label' => 'Assent Form',
                'type' => 'single'
            ],
            'approval_letters' => [
                'required' => false,
                'label' => 'Approval Letters',
                'type' => 'multiple'
            ],
            'required_forms' => [
                'required' => false,
                'label' => 'Required Forms',
                'type' => 'multiple'
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
        $researchTypes = [];
        if (!empty($_POST['research_type'])) {
            $researchTypes[] = $this->validator->sanitizeString($_POST['research_type']);
        }
        if (!empty($_POST['research_type_other'])) {
            $researchTypes[] = 'Other: ' . $this->validator->sanitizeString($_POST['research_type_other']);
        }
        $this->sanitizedData['research_type'] = $researchTypes;
    }

    /**
     * Validate type-specific fields
     *
     * @param array $data Form data
     * @return array ['success' => bool, 'errors' => array]
     */
    protected function validateTypeSpecific(array $data): array
    {
        $errors = [];

        // Validate PI email
        if (!empty($data['pi_email']) && !$this->validator->validateEmail($data['pi_email'])) {
            $errors[] = 'Invalid PI email format';
        }

        // Validate Co-PI email
        if (!empty($data['co_pi_email']) && !$this->validator->validateEmail($data['co_pi_email'])) {
            $errors[] = 'Invalid Co-PI email format';
        }

        // Validate dates
        $datesToValidate = ['pi_date', 'co_pi_date'];
        foreach ($datesToValidate as $dateField) {
            if (!empty($data[$dateField]) && !$this->validator->validateDate($data[$dateField])) {
                $errors[] = 'Invalid date format for ' . str_replace('_', ' ', $dateField);
            }
        }

        // Validate duration
        if (!empty($data['duration']) &&
            !$this->validator->validateNumber($data['duration'], 1, 120)) {
            $errors[] = 'Duration must be a number between 1 and 120 months';
        }

        // Validate prior scientific review
        $validPriorReview = ['yes', 'no', 'pending'];
        if (!empty($data['prior_scientific_review']) &&
            !in_array($data['prior_scientific_review'], $validPriorReview)) {
            $errors[] = 'Invalid prior scientific review option';
        }

        return [
            'success' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Get upload base directory
     *
     * @return string Upload directory path
     */
    protected function getUploadBaseDir(): string
    {
        return 'non_nmimr_applications/' . date('Y/m');
    }

    /**
     * Handle file uploads (override for multiple file support)
     *
     * @return array ['success' => bool, 'paths' => array, 'error' => string]
     */
    protected function handleFileUploads(): array
    {
        $this->uploadedPaths = [];
        $uploadDir = $this->getUploadBaseDir();

        $requirements = $this->getFileRequirements();

        foreach ($requirements as $field => $config) {
            if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
                // Handle multiple file fields - they can be empty arrays
                if ($config['type'] === 'multiple') {
                    $this->uploadedPaths[$field] = json_encode([]);
                } else {
                    $this->uploadedPaths[$field] = null;
                }
                continue;
            }

            if ($config['type'] === 'multiple') {
                // Multiple file upload
                $result = $this->fileService->uploadMultiple($_FILES[$field], $field, $uploadDir);
            } else {
                // Single file upload
                $result = $this->fileService->upload($_FILES[$field], $field, $uploadDir);
            }

            if (!$result['success']) {
                return [
                    'success' => false,
                    'message' => 'File upload failed: ' . $result['error']
                ];
            }

            if ($config['type'] === 'multiple') {
                $this->uploadedPaths[$field] = json_encode($result['paths'] ?? []);
            } else {
                $this->uploadedPaths[$field] = $result['path'] ?? null;
            }
        }

        return ['success' => true, 'paths' => $this->uploadedPaths];
    }

    /**
     * Save type-specific details
     *
     * @param int $applicationId Application ID
     * @return bool Success
     */
    protected function saveTypeSpecific(int $applicationId): bool
    {
        // Check if a record already exists for this application_id
        $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM non_nmimr_application_details WHERE application_id = :application_id");
        $checkStmt->execute([':application_id' => $applicationId]);
        $recordExists = $checkStmt->fetchColumn() > 0;

        if ($recordExists) {
            // UPDATE existing record
            return $this->updateTypeSpecific($applicationId);
        }

        // INSERT new record
        $stmt = $this->db->prepare("
            INSERT INTO non_nmimr_application_details (
                application_id,
                pi_details,
                co_pi,
                prior_scientific_review,
                prior_irb_review,
                collaborating_institutions,
                funding_source,
                duration,
                introduction,
                literature_review,
                aims,
                methodology,
                expected_outcomes,
                application_references,               
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
                submission_notes,
                final_pdf
            ) VALUES (
                :application_id,
                :pi_details,
                :co_pi,
                :prior_scientific_review,
                :prior_irb_review,
                :collaborating_institutions,
                :funding_source,
                :duration,
                :introduction,
                :literature_review,
                :aims,
                :methodology,
                :expected_outcomes,
                :application_references,                                
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
                :submission_notes,
                :final_pdf
            )
        ");

        return $stmt->execute([
            ':application_id' => $applicationId,
            ':pi_details' => json_encode([
                'name' => $this->sanitizedData['pi_name'] ?? '',
                'institution' => $this->sanitizedData['pi_institution'] ?? '',
                'address' => $this->sanitizedData['pi_address'] ?? '',
                'phone' => $this->sanitizedData['pi_phone_number'] ?? '',
                'fax' => $this->sanitizedData['pi_fax'] ?? '',
                'email' => $this->sanitizedData['pi_email'] ?? ''
            ]),
            ':co_pi' => json_encode([
                'name' => $this->sanitizedData['co_pi_name'] ?? '',
                'qualification' => $this->sanitizedData['co_pi_qualification'] ?? '',
                'department' => $this->sanitizedData['co_pi_department'] ?? '',
                'address' => $this->sanitizedData['co_pi_address'] ?? '',
                'phone' => $this->sanitizedData['co_pi_phone_number'] ?? '',
                'fax' => $this->sanitizedData['co_pi_fax'] ?? '',
                'email' => $this->sanitizedData['co_pi_email'] ?? ''
            ]),
            ':prior_scientific_review' => $this->sanitizedData['prior_scientific_review'] ?? '',
            ':prior_irb_review' => $this->sanitizedData['prior_irb_review'] ?? '',
            ':collaborating_institutions' => $this->sanitizedData['collaborating_institutions'] ?? '',
            ':funding_source' => $this->sanitizedData['funding_source'] ?? '',
            ':duration' => $this->sanitizedData['duration'] ?? '',
            ':introduction' => $this->sanitizedData['introduction'] ?? '',
            ':literature_review' => $this->sanitizedData['literature_review'] ?? '',
            ':aims' => $this->sanitizedData['aims'] ?? '',
            ':methodology' => $this->sanitizedData['methodology'] ?? '',
            ':expected_outcomes' => $this->sanitizedData['expected_outcomes'] ?? '',
            ':application_references' => $this->sanitizedData['application_references'] ?? '',
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
            ':submission_notes' => $this->sanitizedData['submission_notes'] ?? '',
            ':final_pdf' => $this->uploadedPaths['final_pdf'] ?? null
        ]);
    }

    /**
     * Update type-specific details for non-NMIMR applications
     *
     * @param int $applicationId Application ID
     * @return bool Success
     */
    protected function updateTypeSpecific(int $applicationId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE non_nmimr_application_details SET
                pi_details = :pi_details,
                co_pi = :co_pi,
                prior_scientific_review = :prior_scientific_review,
                prior_irb_review = :prior_irb_review,
                collaborating_institutions = :collaborating_institutions,
                funding_source = :funding_source,
                duration = :duration,
                introduction = :introduction,
                literature_review = :literature_review,
                aims = :aims,
                methodology = :methodology,
                expected_outcomes = :expected_outcomes,
                application_references = :application_references,
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
                submission_notes = :submission_notes,
                final_pdf = :final_pdf
            WHERE application_id = :application_id
        ");

        return $stmt->execute([
            ':application_id' => $applicationId,
            ':pi_details' => json_encode([
                'name' => $this->sanitizedData['pi_name'] ?? '',
                'institution' => $this->sanitizedData['pi_institution'] ?? '',
                'address' => $this->sanitizedData['pi_address'] ?? '',
                'phone' => $this->sanitizedData['pi_phone_number'] ?? '',
                'fax' => $this->sanitizedData['pi_fax'] ?? '',
                'email' => $this->sanitizedData['pi_email'] ?? ''
            ]),
            ':co_pi' => json_encode([
                'name' => $this->sanitizedData['co_pi_name'] ?? '',
                'qualification' => $this->sanitizedData['co_pi_qualification'] ?? '',
                'department' => $this->sanitizedData['co_pi_department'] ?? '',
                'address' => $this->sanitizedData['co_pi_address'] ?? '',
                'phone' => $this->sanitizedData['co_pi_phone_number'] ?? '',
                'fax' => $this->sanitizedData['co_pi_fax'] ?? '',
                'email' => $this->sanitizedData['co_pi_email'] ?? ''
            ]),
            ':prior_scientific_review' => $this->sanitizedData['prior_scientific_review'] ?? '',
            ':prior_irb_review' => $this->sanitizedData['prior_irb_review'] ?? '',
            ':collaborating_institutions' => $this->sanitizedData['collaborating_institutions'] ?? '',
            ':funding_source' => $this->sanitizedData['funding_source'] ?? '',
            ':duration' => $this->sanitizedData['duration'] ?? '',
            ':introduction' => $this->sanitizedData['introduction'] ?? '',
            ':literature_review' => $this->sanitizedData['literature_review'] ?? '',
            ':aims' => $this->sanitizedData['aims'] ?? '',
            ':methodology' => $this->sanitizedData['methodology'] ?? '',
            ':expected_outcomes' => $this->sanitizedData['expected_outcomes'] ?? '',
            ':application_references' => $this->sanitizedData['references'] ?? '',
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
            ':submission_notes' => $this->sanitizedData['submission_notes'] ?? '',
            ':final_pdf' => $this->uploadedPaths['final_pdf'] ?? null
        ]);
    }

    /**
     * Save document records (override for multiple file support)
     *
     * @param int $applicationId Application ID
     */
    protected function saveDocuments(int $applicationId): void
    {
        foreach ($this->uploadedPaths as $field => $path) {
            $requirements = $this->getFileRequirements();
            $config = $requirements[$field] ?? null;

            if ($config === null) {
                continue;
            }

            if ($config['type'] === 'multiple') {
                // Handle multiple files
                $paths = json_decode($path ?? '[]', true);
                foreach ($paths as $singlePath) {
                    $this->insertDocument($applicationId, $field, $singlePath);
                }
            } else {
                // Handle single file
                if (!empty($path)) {
                    $this->insertDocument($applicationId, $field, $path);
                }
            }
        }
    }

    /**
     * Insert a single document record
     *
     * @param int $applicationId Application ID
     * @param string $field Field name
     * @param string $path File path
     */
    private function insertDocument(int $applicationId, string $field, string $path): void
    {
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

        $stmt->execute([
            ':application_id' => $applicationId,
            ':document_type' => $documentType,
            ':file_name' => basename($path),
            ':file_path' => $path,
            ':uploaded_by' => $this->session->getUserId()
        ]);
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
            'final_pdf' => 'final_pdf',
            'assent_form' => 'assent_form',
            'approval_letters' => 'approval_letter',
            'required_forms' => 'required_forms',
            default => $field
        };
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
     * Get fields for a specific step
     *
     * @param int $step Step number
     * @return array Field names required for this step
     */
    protected function getFieldsForStep(int $step): array
    {
        $commonFields = $this->getCommonRequiredFields();
        
        switch ($step) {
            case 1: // PI Information
                return array_merge($commonFields, [
                    'pi_name',
                    'pi_institution',
                    'pi_address',
                    'pi_phone_number',
                    'pi_email'
                ]);
            
            case 2: // Co-PI Information
                return [
                    'co_pi_name',
                    'co_pi_qualification',
                    'co_pi_department',
                    'co_pi_address',
                    'co_pi_phone_number',
                    'co_pi_fax',
                    'co_pi_email'
                ];
            
            case 3: // Project Information
                return [
                    'collaborating_institutions',
                    'duration',
                    'funding_source',
                    'prior_scientific_review',
                    'prior_irb_review'
                ];
            
            case 4: // Research Content
                return [
                    'introduction',
                    'literature_review',
                    'aims',
                    'methodology',
                    'expected_outcomes'
                ];
            
            case 5: // References
                return [
                    'application_references'
                ];
            
            case 6: // Signatures
                return [
                    'pi_signature',
                    'pi_date'
                ];
            
            default:
                return $commonFields;
        }
    }
}
