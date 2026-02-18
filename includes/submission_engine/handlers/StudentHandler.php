<?php

/**
 * StudentHandler Class
 *
 * Handles student application form submissions.
 * Implements type-specific validation and saving for student applications.
 *
 * @package UGIRB\SubmissionEngine\Handlers
 */

namespace UGIRB\SubmissionEngine\Handlers;

class StudentHandler extends BaseAbstractHandler
{
    /** @var string Application type identifier */
    public const TYPE = 'student';

    /** @var string Protocol prefix */
    public const PREFIX = 'STU';

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
            // Student Info
            'student_department',
            'student_number',
            'student_status',

            // Supervisors
            'supervisor1_name',
            'supervisor1_institution',
            'supervisor1_email',

            // Study Details
            'study_duration_years',
            'study_start_date',
            'study_end_date',

            // Research Content
            'background',
            'methods',
            'expected_outcome',

            // Declarations
            'student_declaration_name',
            'student_declaration_date',
            'student_declaration_signature',
            'supervisor_declaration_name',
            'supervisor_declaration_date',
            'supervisor_declaration_signature'
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
            'data_instruments' => [
                'required' => true,
                'label' => 'Data Collection Instruments',
                'type' => 'single'
            ],
            'approval_letter' => [
                'required' => true,
                'label' => 'Approval Letter',
                'type' => 'single'
            ],
            'assent_form' => [
                'required' => false,
                'label' => 'Assent Form',
                'type' => 'single'
            ],
            'collaboration_letter' => [
                'required' => false,
                'label' => 'Collaboration Letter',
                'type' => 'single'
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
            'student_email',
            'supervisor1_email',
            'supervisor2_email'
        ];
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

        // Validate supervisor 1 email
        if (!empty($data['supervisor1_email']) &&
            !$this->validator->validateEmail($data['supervisor1_email'])) {
            $errors[] = 'Invalid supervisor1 email format';
        }

        // Validate supervisor 2 email if provided
        if (!empty($data['supervisor2_email']) &&
            !$this->validator->validateEmail($data['supervisor2_email'])) {
            $errors[] = 'Invalid supervisor2 email format';
        }

        // Validate date range
        if (!empty($data['study_start_date']) && !empty($data['study_end_date'])) {
            if (!$this->validator->validateDateRange($data['study_start_date'], $data['study_end_date'])) {
                $errors[] = 'Study end date must be after start date';
            }
        }

        // Validate student status
        $validStatuses = ['undergraduate', 'masters', 'phd', 'postdoctoral', 'other'];
        if (!empty($data['student_status']) &&
            !in_array(strtolower($data['student_status']), $validStatuses)) {
            $errors[] = 'Invalid student status';
        }

        // Validate study duration is numeric
        if (!empty($data['study_duration_years']) &&
            !$this->validator->validateNumber($data['study_duration_years'], 0.1, 10)) {
            $errors[] = 'Study duration must be a number between 0.1 and 10 years';
        }

        // Validate declarations if not a draft
        if (!$this->isDraft) {
            $declarationValidation = $this->validateDeclarations($data);
            if (!$declarationValidation['success']) {
                $errors = array_merge($errors, $declarationValidation['errors']);
            }
        }

        return [
            'success' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Validate declarations
     *
     * @param array $data Form data
     * @return array ['success' => bool, 'errors' => array]
     */
    protected function validateDeclarations(array $data): array
    {
        $requiredDeclarations = ['1', '2', '3', '4', '5'];
        $declarations = $data['declarations'] ?? [];

        if (!is_array($declarations)) {
            $declarations = [];
        }

        $missingDeclarations = array_diff($requiredDeclarations, $declarations);

        if (!empty($missingDeclarations)) {
            return [
                'success' => false,
                'errors' => ['You must agree to all declaration statements.']
            ];
        }

        return ['success' => true, 'errors' => []];
    }

    /**
     * Get upload base directory
     *
     * @return string Upload directory path
     */
    protected function getUploadBaseDir(): string
    {
        return 'student_applications/' . date('Y/m');
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
        $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM student_application_details WHERE application_id = :application_id");
        $checkStmt->execute([':application_id' => $applicationId]);
        $recordExists = $checkStmt->fetchColumn() > 0;

        if ($recordExists) {
            // UPDATE existing record
            error_log("StudentHandler: UPDATE operation reached for student application ID: " . $applicationId);
            return $this->updateTypeSpecific($applicationId);
        }

        // INSERT new record
        error_log("StudentHandler: INSERT operation reached for student application ID: " . $applicationId);
        $stmt = $this->db->prepare("
            INSERT INTO student_application_details (
                application_id,
                student_name,
                student_institution,
                student_department,
                student_address,
                student_number,
                student_phone,
                student_email,
                supervisor1_name,
                supervisor1_institution,
                supervisor1_address,
                supervisor1_phone,
                supervisor1_email,
                supervisor2_name,
                supervisor2_institution,
                supervisor2_address,
                supervisor2_phone,
                supervisor2_email,
                student_status,
                study_duration_years,
                study_start_date,
                study_end_date,
                funding_sources,
                approval_letter,
                prior_irb_review,
                collaborating_institutions,
                collaboration_letter,
                background,
                methods,
                expected_outcome,
                key_references,
                consent_form,
                assent_form,
                data_instruments,
                additional_documents,
                declarations,
                student_declaration_name,
                student_declaration_date,
                student_declaration_signature,
                supervisor_declaration_name,
                supervisor_declaration_date,
                supervisor_declaration_signature
            ) VALUES (
                :application_id,
                :student_name,
                :student_institution,
                :student_department,
                :student_address,
                :student_number,
                :student_phone,
                :student_email,
                :supervisor1_name,
                :supervisor1_institution,
                :supervisor1_address,
                :supervisor1_phone,
                :supervisor1_email,
                :supervisor2_name,
                :supervisor2_institution,
                :supervisor2_address,
                :supervisor2_phone,
                :supervisor2_email,
                :student_status,
                :study_duration_years,
                :study_start_date,
                :study_end_date,
                :funding_sources,
                :approval_letter,
                :prior_irb_review,
                :collaborating_institutions,
                :collaboration_letter,
                :background,
                :methods,
                :expected_outcome,
                :key_references,
                :consent_form,
                :assent_form,
                :data_instruments,
                :additional_documents,
                :declarations,
                :student_declaration_name,
                :student_declaration_date,
                :student_declaration_signature,
                :supervisor_declaration_name,
                :supervisor_declaration_date,
                :supervisor_declaration_signature
            )
        ");

        return $stmt->execute([
            ':application_id' => $applicationId,
            ':student_name' => $this->sanitizedData['student_name'] ?? '',
            ':student_institution' => $this->sanitizedData['student_institution'] ?? '',
            ':student_department' => $this->sanitizedData['student_department'] ?? '',
            ':student_address' => $this->sanitizedData['student_address'] ?? '',
            ':student_number' => $this->sanitizedData['student_number'] ?? '',
            ':student_phone' => $this->sanitizedData['student_phone'] ?? '',
            ':student_email' => $this->sanitizedData['student_email'] ?? '',
            ':supervisor1_name' => $this->sanitizedData['supervisor1_name'] ?? '',
            ':supervisor1_institution' => $this->sanitizedData['supervisor1_institution'] ?? '',
            ':supervisor1_address' => $this->sanitizedData['supervisor1_address'] ?? '',
            ':supervisor1_phone' => $this->sanitizedData['supervisor1_phone'] ?? '',
            ':supervisor1_email' => $this->sanitizedData['supervisor1_email'] ?? '',
            ':supervisor2_name' => $this->sanitizedData['supervisor2_name'] ?? '',
            ':supervisor2_institution' => $this->sanitizedData['supervisor2_institution'] ?? '',
            ':supervisor2_address' => $this->sanitizedData['supervisor2_address'] ?? '',
            ':supervisor2_phone' => $this->sanitizedData['supervisor2_phone'] ?? '',
            ':supervisor2_email' => $this->sanitizedData['supervisor2_email'] ?? '',
            ':student_status' => $this->sanitizedData['student_status'] ?? '',
            ':study_duration_years' => $this->sanitizedData['study_duration_years'] ?? '',
            ':study_start_date' => $this->sanitizedData['study_start_date'] ?? null,
            ':study_end_date' => $this->sanitizedData['study_end_date'] ?? null,
            ':funding_sources' => $this->sanitizedData['funding_sources'] ?? '',
            ':approval_letter' => $this->uploadedPaths['approval_letter'] ?? null,
            ':prior_irb_review' => $this->sanitizedData['prior_irb_review'] ?? '',
            ':collaborating_institutions' => $this->sanitizedData['collaborating_institutions'] ?? '',
            ':collaboration_letter' => $this->uploadedPaths['collaboration_letter'] ?? null,
            ':background' => $this->sanitizedData['background'] ?? '',
            ':methods' => $this->sanitizedData['methods'] ?? '',
            ':expected_outcome' => $this->sanitizedData['expected_outcome'] ?? '',
            ':key_references' => $this->sanitizedData['key_references'] ?? '',
            ':consent_form' => $this->uploadedPaths['consent_form'] ?? null,
            ':assent_form' => $this->uploadedPaths['assent_form'] ?? null,
            ':data_instruments' => $this->uploadedPaths['data_instruments'] ?? null,
            ':additional_documents' => $this->sanitizedData['additional_documents'] ?? '',
            ':declarations' => json_encode($this->sanitizedData['declarations'] ?? []),
            ':student_declaration_name' => $this->sanitizedData['student_declaration_name'] ?? '',
            ':student_declaration_date' => $this->sanitizedData['student_declaration_date'] ?? null,
            ':student_declaration_signature' => $this->sanitizedData['student_declaration_signature'] ?? '',
            ':supervisor_declaration_name' => $this->sanitizedData['supervisor_declaration_name'] ?? '',
            ':supervisor_declaration_date' => $this->sanitizedData['supervisor_declaration_date'] ?? null,
            ':supervisor_declaration_signature' => $this->sanitizedData['supervisor_declaration_signature'] ?? ''
        ]);
    }

    /**
     * Update type-specific details for student applications
     *
     * @param int $applicationId Application ID
     * @return bool Success
     */
    protected function updateTypeSpecific(int $applicationId): bool
    {
        error_log("StudentHandler: UPDATE operation executing for student application ID: " . $applicationId);
        $stmt = $this->db->prepare("
            UPDATE student_application_details SET
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
                student_status = :student_status,
                study_duration_years = :study_duration_years,
                study_start_date = :study_start_date,
                study_end_date = :study_end_date,
                funding_sources = :funding_sources,
                approval_letter = :approval_letter,
                prior_irb_review = :prior_irb_review,
                collaborating_institutions = :collaborating_institutions,
                collaboration_letter = :collaboration_letter,
                background = :background,
                methods = :methods,
                expected_outcome = :expected_outcome,
                key_references = :key_references,
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
                supervisor_declaration_signature = :supervisor_declaration_signature
            WHERE application_id = :application_id
        ");

        return $stmt->execute([
            ':application_id' => $applicationId,
            ':student_name' => $this->sanitizedData['student_name'] ?? '',
            ':student_institution' => $this->sanitizedData['student_institution'] ?? '',
            ':student_department' => $this->sanitizedData['student_department'] ?? '',
            ':student_address' => $this->sanitizedData['student_address'] ?? '',
            ':student_number' => $this->sanitizedData['student_number'] ?? '',
            ':student_phone' => $this->sanitizedData['student_phone'] ?? '',
            ':student_email' => $this->sanitizedData['student_email'] ?? '',
            ':supervisor1_name' => $this->sanitizedData['supervisor1_name'] ?? '',
            ':supervisor1_institution' => $this->sanitizedData['supervisor1_institution'] ?? '',
            ':supervisor1_address' => $this->sanitizedData['supervisor1_address'] ?? '',
            ':supervisor1_phone' => $this->sanitizedData['supervisor1_phone'] ?? '',
            ':supervisor1_email' => $this->sanitizedData['supervisor1_email'] ?? '',
            ':supervisor2_name' => $this->sanitizedData['supervisor2_name'] ?? '',
            ':supervisor2_institution' => $this->sanitizedData['supervisor2_institution'] ?? '',
            ':supervisor2_address' => $this->sanitizedData['supervisor2_address'] ?? '',
            ':supervisor2_phone' => $this->sanitizedData['supervisor2_phone'] ?? '',
            ':supervisor2_email' => $this->sanitizedData['supervisor2_email'] ?? '',
            ':student_status' => $this->sanitizedData['student_status'] ?? '',
            ':study_duration_years' => $this->sanitizedData['study_duration_years'] ?? '',
            ':study_start_date' => $this->sanitizedData['study_start_date'] ?? null,
            ':study_end_date' => $this->sanitizedData['study_end_date'] ?? null,
            ':funding_sources' => $this->sanitizedData['funding_sources'] ?? '',
            ':approval_letter' => $this->uploadedPaths['approval_letter'] ?? null,
            ':prior_irb_review' => $this->sanitizedData['prior_irb_review'] ?? '',
            ':collaborating_institutions' => $this->sanitizedData['collaborating_institutions'] ?? '',
            ':collaboration_letter' => $this->uploadedPaths['collaboration_letter'] ?? null,
            ':background' => $this->sanitizedData['background'] ?? '',
            ':methods' => $this->sanitizedData['methods'] ?? '',
            ':expected_outcome' => $this->sanitizedData['expected_outcome'] ?? '',
            ':key_references' => $this->sanitizedData['key_references'] ?? '',
            ':consent_form' => $this->uploadedPaths['consent_form'] ?? null,
            ':assent_form' => $this->uploadedPaths['assent_form'] ?? null,
            ':data_instruments' => $this->uploadedPaths['data_instruments'] ?? null,
            ':additional_documents' => $this->sanitizedData['additional_documents'] ?? '',
            ':declarations' => json_encode($this->sanitizedData['declarations'] ?? []),
            ':student_declaration_name' => $this->sanitizedData['student_declaration_name'] ?? '',
            ':student_declaration_date' => $this->sanitizedData['student_declaration_date'] ?? null,
            ':student_declaration_signature' => $this->sanitizedData['student_declaration_signature'] ?? '',
            ':supervisor_declaration_name' => $this->sanitizedData['supervisor_declaration_name'] ?? '',
            ':supervisor_declaration_date' => $this->sanitizedData['supervisor_declaration_date'] ?? null,
            ':supervisor_declaration_signature' => $this->sanitizedData['supervisor_declaration_signature'] ?? ''
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
            'data_instruments' => 'data_instruments',
            'approval_letter' => 'approval_letter',
            'assent_form' => 'assent_form',
            'collaboration_letter' => 'collaboration_letter',
            default => $field
        };
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
            case 1: // Student Information
                return array_merge($commonFields, [
                    'student_name',
                    'student_institution',
                    'student_department',
                    'student_address',
                    'student_number',
                    'student_phone',
                    'student_email'
                ]);
            
            case 2: // Supervisor Information
                return [
                    'supervisor1_name',
                    'supervisor1_institution',
                    'supervisor1_address',
                    'supervisor1_phone',
                    'supervisor1_email'
                ];
            
            case 3: // Study Details
                return [
                    'student_status',
                    'study_duration_years',
                    'study_start_date',
                    'study_end_date',
                    'funding_sources',
                    'prior_irb_review'
                ];
            
            case 4: // Research Content
                return [
                    'background',
                    'methods',
                    'expected_outcome'
                ];
            
            case 5: // Collaborations
                return [
                    'collaborating_institutions'
                ];
            
            case 6: // Signatures
                return [
                    'student_declaration_name',
                    'student_declaration_date',
                    'student_declaration_signature',
                    'supervisor_declaration_name',
                    'supervisor_declaration_date',
                    'supervisor_declaration_signature'
                ];
            
            default:
                return $commonFields;
        }
    }
}
