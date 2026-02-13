<?php

/**
 * NmimrHandler Class
 *
 * Handles NMIMR application form submissions.
 * Implements type-specific validation and saving for NMIMR applications.
 *
 * @package UGIRB\SubmissionEngine\Handlers
 */

namespace UGIRB\SubmissionEngine\Handlers;

class NmimrHandler extends BaseAbstractHandler
{
    /** @var string Application type identifier */
    public const TYPE = 'nmimr';

    /** @var string Protocol prefix */
    public const PREFIX = 'NIRB';

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
            // Protocol
            'submission_date',

            // PI Info
            'pi_name',
            'pi_institution',
            'pi_address',
            'pi_phone',
            'pi_email',

            // Project Info
            'study_title',
            'project_duration',

            // Research Content
            'introduction',
            'literature_review',
            'study_aims',
            'methodology',
            'expected_outcomes',
            'nmimr_references',

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
            'assent_form' => [
                'required' => false,
                'label' => 'Assent Form',
                'type' => 'single'
            ],
            'data_instruments' => [
                'required' => true,
                'label' => 'Data Collection Instruments',
                'type' => 'single'
            ],
            'additional_documents' => [
                'required' => false,
                'label' => 'Additional Supporting Documents',
                'type' => 'multiple',
                'max_files' => 10,
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
            'pi_email'
        ];
    }

    /**
     * Sanitize array-type fields (override for Co-Investigators)
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
        if (!empty($_POST['research_type_biomedical'])) {
            $researchTypes[] = 'biomedical';
        }
        if (!empty($_POST['research_type_social'])) {
            $researchTypes[] = 'social';
        }
        if (!empty($_POST['research_type_other'])) {
            $researchTypes[] = 'other: ' . $this->validator->sanitizeString($_POST['research_type_other_specify'] ?? '');
        }
        $this->sanitizedData['research_type'] = $researchTypes;

        // Handle Co-Investigators (dynamic fields)
        $coInvestigators = [];
        $copiIndex = 1;
        while (isset($_POST['copi' . $copiIndex . '_name'])) {
            $copiName = $this->validator->sanitizeString($_POST['copi' . $copiIndex . '_name'] ?? '');
            if (!empty($copiName)) {
                $coInvestigators[] = [
                    'name' => $copiName,
                    'qualification' => $this->validator->sanitizeString($_POST['copi' . $copiIndex . '_qualification'] ?? ''),
                    'department_email' => $this->validator->sanitizeString($_POST['copi' . $copiIndex . '_department_email'] ?? '')
                ];
            }
            $copiIndex++;
        }
        $this->sanitizedData['co_investigators'] = $coInvestigators;
    }

    /**
     * Extract email from "Department, email" format
     *
     * @param string $departmentEmail Combined department and email string
     * @return string|null Extracted email or null if not found
     */
    private function extractEmailFromDepartmentField(string $departmentEmail): ?string
    {
        // Look for email pattern in the string (handles "Department, email@domain.com" or "email@domain.com")
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $departmentEmail, $matches)) {
            return $matches[0];
        }
        return null;
    }

    /**
     * Extract numeric value from duration string
     *
     * @param string $duration Duration string (e.g., "12 months", "1 year", "24")
     * @return float|null Numeric value in months or null if not extractable
     */
    private function extractDurationMonths(string $duration): ?float
    {
        // Look for numeric value at the beginning
        if (preg_match('/^(\d+(?:\.\d+)?)/', trim($duration), $matches)) {
            $value = (float) $matches[1];
            
            // Convert years to months if "year" is mentioned
            if (preg_match('/year/i', $duration)) {
                $value = $value * 12;
            }
            
            return $value;
        }
        return null;
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

        // Validate Co-Investigator emails (extracted from "Department, email" format)
        $coInvestigators = $data['co_investigators'] ?? [];
        if (!empty($coInvestigators) && is_array($coInvestigators)) {
            foreach ($coInvestigators as $index => $copi) {
                if (!empty($copi['department_email'])) {
                    $email = $this->extractEmailFromDepartmentField($copi['department_email']);
                    if ($email === null) {
                        $errors[] = 'Invalid Co-Investigator ' . ($index + 1) . ' email format';
                    }
                }
            }
        }

        // Validate dates
        $datesToValidate = ['submission_date', 'pi_date', 'copi_date'];
        foreach ($datesToValidate as $dateField) {
            if (!empty($data[$dateField]) && !$this->validator->validateDate($data[$dateField])) {
                $errors[] = 'Invalid date format for ' . str_replace('_', ' ', $dateField);
            }
        }

        // Validate final confirmation for submission
        if (!$this->isDraft && empty($data['final_confirmation'])) {
            $errors[] = 'You must confirm that all information is accurate and complete';
        }

        // Validate declarations if not a draft
        if (!$this->isDraft) {
            $declarationValidation = $this->validateDeclarations($data);
            if (!$declarationValidation['success']) {
                $errors = array_merge($errors, $declarationValidation['errors']);
            }
        }

        // Validate project duration (handles "12 months", "1 year", "24" formats)
        if (!empty($data['project_duration'])) {
            $durationMonths = $this->extractDurationMonths($data['project_duration']);
            if ($durationMonths === null || $durationMonths < 1 || $durationMonths > 120) {
                $errors[] = 'Project duration must be a number between 1 and 120 months';
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
        $requiredDeclarations = ['declaration_1', 'declaration_2', 'declaration_3', 'declaration_4', 'declaration_5'];
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
        return 'nmimr_applications/' . date('Y/m');
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
        $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM nmimr_application_details WHERE application_id = :application_id");
        $checkStmt->execute([':application_id' => $applicationId]);
        $recordExists = $checkStmt->fetchColumn() > 0;

        if ($recordExists) {
            // UPDATE existing record
            return $this->updateTypeSpecific($applicationId);
        }

        // INSERT new record
        $stmt = $this->db->prepare("
            INSERT INTO nmimr_application_details (
                application_id,
                submission_date,
                pi_name,
                pi_institution,
                pi_address,
                pi_phone,
                pi_email,
                co_investigators,
                project_duration,
                funding_source,
                prior_irb,
                introduction,
                literature_review,
                study_aims,
                methodology,
                expected_outcomes,
                nmimr_references,
                work_plan,
                budget,
                pi_signature,
                pi_date,
                copi_signature,
                copi_date,
                final_confirmation,
                submitted_at
            ) VALUES (
                :application_id,
                :submission_date,
                :pi_name,
                :pi_institution,
                :pi_address,
                :pi_phone,
                :pi_email,
                :co_investigators,
                :project_duration,
                :funding_source,
                :prior_irb,
                :introduction,
                :literature_review,
                :study_aims,
                :methodology,
                :expected_outcomes,
                :nmimr_references,
                :work_plan,
                :budget,
                :pi_signature,
                :pi_date,
                :copi_signature,
                :copi_date,
                :final_confirmation,
                NOW()
            )
        ");

        return $stmt->execute([
            ':application_id' => $applicationId,
            ':submission_date' => $this->sanitizedData['submission_date'] ?? null,
            ':pi_name' => $this->sanitizedData['pi_name'] ?? '',
            ':pi_institution' => $this->sanitizedData['pi_institution'] ?? '',
            ':pi_address' => $this->sanitizedData['pi_address'] ?? '',
            ':pi_phone' => $this->sanitizedData['pi_phone'] ?? '',
            ':pi_email' => $this->sanitizedData['pi_email'] ?? '',
            ':co_investigators' => json_encode($this->sanitizedData['co_investigators'] ?? []),
            ':project_duration' => $this->sanitizedData['project_duration'] ?? '',
            ':funding_source' => $this->sanitizedData['funding_source'] ?? '',
            ':prior_irb' => $this->sanitizedData['prior_irb'] ?? '',
            ':introduction' => $this->sanitizedData['introduction'] ?? '',
            ':literature_review' => $this->sanitizedData['literature_review'] ?? '',
            ':study_aims' => $this->sanitizedData['study_aims'] ?? '',
            ':methodology' => $this->sanitizedData['methodology'] ?? '',
            ':expected_outcomes' => $this->sanitizedData['expected_outcomes'] ?? '',
            ':nmimr_references' => $this->sanitizedData['nmimr_references'] ?? '',
            ':work_plan' => $this->sanitizedData['work_plan'] ?? '',
            ':budget' => $this->sanitizedData['budget'] ?? '',
            ':pi_signature' => $this->sanitizedData['pi_signature'] ?? '',
            ':pi_date' => $this->sanitizedData['pi_date'] ?? null,
            ':copi_signature' => $this->sanitizedData['copi_signature'] ?? '',
            ':copi_date' => $this->sanitizedData['copi_date'] ?? null,
            ':final_confirmation' => !empty($this->sanitizedData['final_confirmation']) ? 1 : 0
        ]);
    }

    /**
     * Update type-specific details for NMIMR applications
     *
     * @param int $applicationId Application ID
     * @return bool Success
     */
    protected function updateTypeSpecific(int $applicationId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE nmimr_application_details SET
                submission_date = :submission_date,
                pi_name = :pi_name,
                pi_institution = :pi_institution,
                pi_address = :pi_address,
                pi_phone = :pi_phone,
                pi_email = :pi_email,
                co_investigators = :co_investigators,
                project_duration = :project_duration,
                funding_source = :funding_source,
                prior_irb = :prior_irb,
                introduction = :introduction,
                literature_review = :literature_review,
                study_aims = :study_aims,
                methodology = :methodology,
                expected_outcomes = :expected_outcomes,
                nmimr_references = :nmimr_references,
                work_plan = :work_plan,
                budget = :budget,
                pi_signature = :pi_signature,
                pi_date = :pi_date,
                copi_signature = :copi_signature,
                copi_date = :copi_date,
                final_confirmation = :final_confirmation,
                submitted_at = NOW()
            WHERE application_id = :application_id
        ");

        return $stmt->execute([
            ':application_id' => $applicationId,
            ':submission_date' => $this->sanitizedData['submission_date'] ?? null,
            ':pi_name' => $this->sanitizedData['pi_name'] ?? '',
            ':pi_institution' => $this->sanitizedData['pi_institution'] ?? '',
            ':pi_address' => $this->sanitizedData['pi_address'] ?? '',
            ':pi_phone' => $this->sanitizedData['pi_phone'] ?? '',
            ':pi_email' => $this->sanitizedData['pi_email'] ?? '',
            ':co_investigators' => json_encode($this->sanitizedData['co_investigators'] ?? []),
            ':project_duration' => $this->sanitizedData['project_duration'] ?? '',
            ':funding_source' => $this->sanitizedData['funding_source'] ?? '',
            ':prior_irb' => $this->sanitizedData['prior_irb'] ?? '',
            ':introduction' => $this->sanitizedData['introduction'] ?? '',
            ':literature_review' => $this->sanitizedData['literature_review'] ?? '',
            ':study_aims' => $this->sanitizedData['study_aims'] ?? '',
            ':methodology' => $this->sanitizedData['methodology'] ?? '',
            ':expected_outcomes' => $this->sanitizedData['expected_outcomes'] ?? '',
            ':nmimr_references' => $this->sanitizedData['nmimr_references'] ?? '',
            ':work_plan' => $this->sanitizedData['work_plan'] ?? '',
            ':budget' => $this->sanitizedData['budget'] ?? '',
            ':pi_signature' => $this->sanitizedData['pi_signature'] ?? '',
            ':pi_date' => $this->sanitizedData['pi_date'] ?? null,
            ':copi_signature' => $this->sanitizedData['copi_signature'] ?? '',
            ':copi_date' => $this->sanitizedData['copi_date'] ?? null,
            ':final_confirmation' => !empty($this->sanitizedData['final_confirmation']) ? 1 : 0
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
            'nmimr_pdf' => 'nmimr_pdf',
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
     * Insert document record
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
                    'pi_phone',
                    'pi_email'
                ]);
            
            case 2: // Project Information
                return [
                    'study_title',
                    'project_duration',
                    'funding_source',
                    'prior_irb'
                ];
            
            case 3: // Research Content
                return [
                    'introduction',
                    'literature_review'
                ];
            
            case 4: // Methodology
                return [
                    'study_aims',
                    'methodology',
                    'expected_outcomes'
                ];
            
            case 5: // References and Work Plan
                return [
                    'nmimr_references',
                    'work_plan',
                    'budget'
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
