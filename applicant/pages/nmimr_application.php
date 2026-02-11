<?php

/**
 * NMIMR Application Form
 * With draft saving and loading functionality
 * Schema: nmimr_applications, nmimr_co_investigators, nmimr_application_documents, nmimr_declarations
 */

// Use consistent session name across entire application
// defined('CSRF_SESSION_NAME') || define('CSRF_SESSION_NAME', 'ug_irb_session');
// session_name(CSRF_SESSION_NAME);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize draft data
$draft = [];
$copiFields = [];
$declarations = [];

// Field mapping: form field name => database field name
$fieldMapping = [
    'protocol_number' => 'protocol_number',
    'version_number' => 'version_number',
    'submission_date' => 'submission_date',
    'pi_name' => 'pi_name',
    'pi_institution' => 'pi_institution',
    'pi_address' => 'pi_address',
    'pi_phone' => 'pi_phone',
    'pi_email' => 'pi_email',
    'proposal_title' => 'proposal_title',
    'project_duration' => 'project_duration',
    'funding_source' => 'funding_source',
    'prior_irb' => 'prior_irb',
    'abstract' => 'abstract',
    'introduction' => 'introduction',
    'literature_review' => 'literature_review',
    'study_aims' => 'study_aims',
    'methodology' => 'methodology',
    'ethical_considerations' => 'ethical_considerations',
    'expected_outcomes' => 'expected_outcomes',
    'nmimr_references' => 'nmimr_references',
    'work_plan' => 'work_plan',
    'budget' => 'budget',
    'pi_signature' => 'pi_signature',
    'pi_date' => 'pi_date',
    'copi_signature' => 'copi_signature',
    'copi_date' => 'copi_date',
    'final_confirmation' => 'final_confirmation'
];

// Fetch existing draft if user is logged in
$userId = $_SESSION['user_id'] ?? 0;
$currentStep = 1;
if ($userId > 0) {
    try {
        $db = new Database();
        $conn = $db->connect();

        if ($conn) {
            $stmt = $conn->prepare(
                "SELECT * FROM nmimr_applications 
                 WHERE applicant_id = :user_id AND status = 'draft' 
                 ORDER BY id DESC LIMIT 1"
            );
            $stmt->execute(['user_id' => $userId]);
            $draftData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($draftData) {
                $draft = $draftData;
                $currentStep = $draft['current_step'] ?? 1;

                error_log("Draft loaded for user_id $userId: application_id=" . $draft['id']);

                // Decode Co-Investigators if stored as JSON
                if (!empty($draft['co_investigators'])) {
                    $decodedCopi = json_decode($draft['co_investigators'], true);
                    if (is_array($decodedCopi) && !empty($decodedCopi)) {
                        // Convert array of objects to flat array for copiFields
                        foreach ($decodedCopi as $index => $copi) {
                            $copiFields['copi' . ($index + 1) . '_name'] = $copi['name'] ?? '';
                            $copiFields['copi' . ($index + 1) . '_qualification'] = $copi['qualification'] ?? '';
                            $copiFields['copi' . ($index + 1) . '_department_email'] = $copi['department_email'] ?? '';
                        }
                    }
                }

                // Decode declarations if stored as JSON
                // Declarations are now in nmimr_declarations table, so fetch from there
                $declStmt = $conn->prepare(
                    "SELECT declaration_type FROM nmimr_declarations WHERE application_id = :application_id"
                );
                $declStmt->execute(['application_id' => $draft['id']]);
                $declRecords = $declStmt->fetchAll(PDO::FETCH_COLUMN);
                if (!empty($declRecords)) {
                    $declarations = $declRecords;
                }
            }
        }
    } catch (PDOException $e) {
        error_log("Error fetching draft: " . $e->getMessage());
    }
}

/**
 * Helper function to get draft value
 */
function getDraftValue($formField, $default = '')
{
    global $draft, $fieldMapping;

    // Check if there's a mapped database field
    $dbField = $fieldMapping[$formField] ?? '';

    if (!empty($dbField) && isset($draft[$dbField])) {
        return htmlspecialchars($draft[$dbField]);
    }

    // Fallback to direct field name
    if (isset($draft[$formField])) {
        return htmlspecialchars($draft[$formField]);
    }

    return $default;
}

/**
 * Helper function to check if checkbox should be checked
 */
function isChecked($formField, $value = '1')
{
    global $draft, $fieldMapping;

    // Check if there's a mapped database field
    $dbField = $fieldMapping[$formField] ?? '';

    if (!empty($dbField) && isset($draft[$dbField])) {
        if ($draft[$dbField] == $value || $draft[$dbField] === $value) {
            return ' checked';
        }
    }

    // Fallback to direct field name
    if (isset($draft[$formField]) && ($draft[$formField] == $value || $draft[$formField] === $value)) {
        return ' checked';
    }

    return '';
}

/**
 * Helper function to check if declaration is checked
 */
function isDeclarationChecked($value)
{
    global $declarations;
    // Support both old numeric values and new declaration_type values
    $value = str_replace('declaration_', '', $value);
    $numericValue = is_numeric($value) ? (string)(int)$value : $value;

    foreach ($declarations as $decl) {
        $declValue = str_replace('declaration_', '', $decl);
        $declNumeric = is_numeric($declValue) ? (string)(int)$declValue : $declValue;
        if ($decl === $value || $declValue === $value || $declNumeric === $numericValue) {
            return ' checked';
        }
    }
    return '';
}

/**
 * Helper function to get Co-Investigator field value
 */
function getCopiValue($index, $field)
{
    global $copiFields;
    $key = 'copi' . $index . '_' . $field;
    if (isset($copiFields[$key])) {
        return htmlspecialchars($copiFields[$key]);
    }
    return '';
}

/**
 * Helper function to check if research type is selected
 */
function isResearchTypeChecked($value)
{
    global $draft;
    if (isset($draft['research_type'])) {
        $researchTypes = json_decode($draft['research_type'], true);
        if (is_array($researchTypes) && in_array($value, $researchTypes)) {
            return ' checked';
        }
    }
    return '';
}
?>
<div class="add-new-protocol container-fluid mt-4 mb-4 p-4">
    <!-- Header -->
    <div class="welcome-header text-white p-4 rounded mb-4 position-relative overflow-hidden"
        style="background: linear-gradient(135deg, #35493d 0%, #445e50 100%);">
        <div class="header-gradient"></div>
        <div class="d-flex align-items-center position-relative z-1">
            <div>
                <h2 class="mb-1 fw-bold">Initial Submission Form A - NMIMR Researchers</h2>
                <p class="mb-0 opacity-75">Complete all sections for ethics review consideration</p>
            </div>
        </div>
        <div class="header-decoration">
            <i class="fas fa-flask"></i>
            <i class="fas fa-file-alt"></i>
            <i class="fas fa-edit"></i>
        </div>
    </div>

    <!-- Instructions Alert -->
    <div class="alert alert-info mb-4">
        <div class="d-flex align-items-start">
            <i class="fas fa-info-circle me-3 mt-1 fs-4"></i>
            <div>
                <h6 class="alert-heading mb-2">Submission Instructions</h6>
                <ol class="mb-0 ps-3">
                    <li class="mb-1">Complete all sections before submission for ethics review</li>
                    <li class="mb-1">Download the NMIMR-IRB Researchers Checklist for further instructions</li>
                    <li class="mb-1">Proposal and consent form should be paged separately</li>
                    <li class="mb-1">Download the NMIMR-IRB Submission guide for further information</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Main Wizard Container -->
    <div class="row">
        <!-- Left Sidebar - Stepper -->
        <div class="col-lg-3">
            <div class="stepper-sidebar card border-0 shadow-sm h-100 sticky-top" style="top: 20px;">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-4 text-dark">
                        <i class="fas fa-list-ol me-2 text-primary"></i>Form Sections
                    </h5>

                    <div class="stepper-vertical">
                        <!-- Step 1: Instructions -->
                        <div class="step active" data-step="1">
                            <div class="step-header d-flex align-items-center mb-2">
                                <div class="step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                    1
                                </div>
                                <div class="step-title ms-3">
                                    <h6 class="fw-semibold mb-0">Instructions & Header</h6>
                                    <small class="text-muted">Protocol details</small>
                                </div>
                            </div>
                            <div class="step-progress ms-4 ps-3">
                                <div class="step-line"></div>
                            </div>
                        </div>

                        <!-- Step 2: Section A -->
                        <div class="step" data-step="2">
                            <div class="step-header d-flex align-items-center mb-2">
                                <div class="step-number bg-light text-muted border rounded-circle d-flex align-items-center justify-content-center">
                                    2
                                </div>
                                <div class="step-title ms-3">
                                    <h6 class="fw-semibold mb-0 text-muted">Section A: Background</h6>
                                    <small class="text-muted">PI details & project info</small>
                                </div>
                            </div>
                            <div class="step-progress ms-4 ps-3">
                                <div class="step-line"></div>
                            </div>
                        </div>

                        <!-- Step 3: Section B Part 1 -->
                        <div class="step" data-step="3">
                            <div class="step-header d-flex align-items-center mb-2">
                                <div class="step-number bg-light text-muted border rounded-circle d-flex align-items-center justify-content-center">
                                    3
                                </div>
                                <div class="step-title ms-3">
                                    <h6 class="fw-semibold mb-0 text-muted">Section B - Part 1</h6>
                                    <small class="text-muted">Abstract to Aims</small>
                                </div>
                            </div>
                            <div class="step-progress ms-4 ps-3">
                                <div class="step-line"></div>
                            </div>
                        </div>

                        <!-- Step 4: Section B Part 2 -->
                        <div class="step" data-step="4">
                            <div class="step-header d-flex align-items-center mb-2">
                                <div class="step-number bg-light text-muted border rounded-circle d-flex align-items-center justify-content-center">
                                    4
                                </div>
                                <div class="step-title ms-3">
                                    <h6 class="fw-semibold mb-0 text-muted">Section B - Part 2</h6>
                                    <small class="text-muted">Methodology & Ethics</small>
                                </div>
                            </div>
                            <div class="step-progress ms-4 ps-3">
                                <div class="step-line"></div>
                            </div>
                        </div>

                        <!-- Step 5: Section B Part 3 -->
                        <div class="step" data-step="5">
                            <div class="step-header d-flex align-items-center mb-2">
                                <div class="step-number bg-light text-muted border rounded-circle d-flex align-items-center justify-content-center">
                                    5
                                </div>
                                <div class="step-title ms-3">
                                    <h6 class="fw-semibold mb-0 text-muted">Section B - Part 3</h6>
                                    <small class="text-muted">Additional Materials</small>
                                </div>
                            </div>
                            <div class="step-progress ms-4 ps-3">
                                <div class="step-line"></div>
                            </div>
                        </div>

                        <!-- Step 6: Section C -->
                        <div class="step" data-step="6">
                            <div class="step-header d-flex align-items-center">
                                <div class="step-number bg-light text-muted border rounded-circle d-flex align-items-center justify-content-center">
                                    6
                                </div>
                                <div class="step-title ms-3">
                                    <h6 class="fw-semibold mb-0 text-muted">Section C: Signatures</h6>
                                    <small class="text-muted">Declarations & submission</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Indicator -->
                    <div class="progress mt-4" style="height: 6px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" id="stepperProgress"></div>
                    </div>
                    <div class="text-center mt-2">
                        <small class="text-muted">Step <span id="currentStep">1</span> of 6</small>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="stepper-navigation mt-4 d-none d-lg-block">
                        <button class="btn btn-outline-secondary w-100 mb-2" id="prevStepBtn" disabled>
                            <i class="fas fa-arrow-left me-2"></i>Previous
                        </button>
                        <button class="btn btn-primary w-100" id="nextStepBtn" type="button">
                            <span class="spinner-container" style="display:none;">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                                <span class="button-text">Saving...</span>
                            </span>
                            <span class="button-text">Next <i class="fas fa-arrow-right ms-2"></i></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Content - Form -->
        <div class="col-lg-9">
            <form id="nmimrProtocolForm" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="submit">
                <input type="hidden" name="application_id" value="<?php echo getDraftValue('id', '0'); ?>">
                <input type="hidden" name="current_step" id="currentStepInput" value="1">
                <input type="hidden" id="initialStep" value="<?php echo $currentStep; ?>">
                <input type="hidden" name="copi_fields_json" id="copiFieldsJson" value='<?php echo json_encode($copiFields); ?>'>

                <!-- Step 1: Instructions & Protocol Information -->
                <div class="step-content active" data-step="1">
                    <div class="card mb-4">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><i class="fas fa-file-signature me-2"></i>Protocol Identification</h5>
                                <p class="text-muted mb-0 small">Step 1 of 6 - Basic study information</p>
                            </div>
                            <span class="badge bg-primary">Required</span>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="protocolNumber" class="form-label fw-semibold">Protocol Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="protocolNumber" name="protocol_number" value="<?php echo getDraftValue('protocol_number'); ?>" required>
                                    <small class="text-muted">Unique identifier for your study (Format: NIRB-YYYY-XXXX)</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="versionNumber" class="form-label fw-semibold">Version Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="versionNumber" name="version_number" value="<?php echo getDraftValue('version_number'); ?>" placeholder="e.g., 1.0" required>
                                    <small class="text-muted">Document version (start with 1.0)</small>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="submissionDate" class="form-label fw-semibold">Submission Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="submissionDate" name="submission_date" value="<?php echo getDraftValue('submission_date'); ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Section A - Background Information -->
                <div class="step-content" data-step="2">
                    <div class="card mb-4 border-primary">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><i class="fas fa-address-card me-2"></i>SECTION A - BACKGROUND INFORMATION</h5>
                                <p class="mb-0 opacity-75 small">Step 2 of 6 - Principal Investigator details</p>
                            </div>
                            <span class="badge bg-white text-primary">Required</span>
                        </div>
                        <div class="card-body">

                            <!-- PI Information -->
                            <div class="pi-info mb-4 p-3 border rounded">
                                <h6 class="fw-semibold mb-3"><i class="fas fa-user me-2"></i>Principal Investigator</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="pi_name" class="form-label fw-semibold">Full Name (Surname First) <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="pi_name" name="pi_name" value="<?php echo getDraftValue('pi_name'); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="pi_institution" class="form-label fw-semibold">Institution & Department <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="pi_institution" name="pi_institution" value="<?php echo getDraftValue('pi_institution'); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="pi_address" class="form-label fw-semibold">Postal Address <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="pi_address" name="pi_address" value="<?php echo getDraftValue('pi_address'); ?>" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="pi_phone" class="form-label fw-semibold">Telephone <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="pi_phone" name="pi_phone" value="<?php echo getDraftValue('pi_phone'); ?>" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="pi_email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="pi_email" name="pi_email" value="<?php echo getDraftValue('pi_email'); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Co-Principal Investigators -->
                            <div class="co-pi-info mb-4">
                                <h6 class="fw-semibold mb-3"><i class="fas fa-users me-2"></i>Co-Principal Investigator(s)</h6>

                                <div class="co-pi-entry mb-3 p-3 border rounded">
                                    <h6 class="fw-semibold mb-3">Co-Principal Investigator 1</h6>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="copi1_name" class="form-label fw-semibold">Name</label>
                                            <input type="text" class="form-control" id="copi1_name" name="copi1_name" value="<?php echo getCopiValue(1, 'name'); ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="copi1_qualification" class="form-label fw-semibold">Qualification</label>
                                            <input type="text" class="form-control" id="copi1_qualification" name="copi1_qualification" value="<?php echo getCopiValue(1, 'qualification'); ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="copi1_department_email" class="form-label fw-semibold">Department & Email</label>
                                            <input type="text" class="form-control" id="copi1_department_email" name="copi1_department_email" value="<?php echo getCopiValue(1, 'department_email'); ?>">
                                        </div>
                                    </div>
                                </div>

                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addCoInvestigator()">
                                    <i class="fas fa-plus me-1"></i>Add Another Co-Principal Investigator
                                </button>
                            </div>

                            <!-- Project Information -->
                            <div class="project-info mb-4 p-3 border rounded">
                                <h6 class="fw-semibold mb-3"><i class="fas fa-clipboard-list me-2"></i>Project Information</h6>
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="proposalTitle" class="form-label fw-semibold">Title of Proposal <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="proposalTitle" name="proposal_title" rows="2" required><?php echo getDraftValue('proposal_title'); ?></textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Type of Research <span class="text-danger">*</span></label>
                                        <div class="mt-2">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="research_type_biomedical" id="typeBiomedical" value="Biomedical" <?php echo isResearchTypeChecked('Biomedical'); ?>>
                                                <label class="form-check-label" for="typeBiomedical">Biomedical</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="research_type_social" id="typeSocial" value="Social/Behavioural" <?php echo isResearchTypeChecked('Social/Behavioural'); ?>>
                                                <label class="form-check-label" for="typeSocial">Social/Behavioural</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="research_type_other" id="typeOther" value="Other" <?php echo isResearchTypeChecked('Other'); ?>>
                                                <label class="form-check-label" for="typeOther">Others</label>
                                            </div>
                                        </div>
                                        <input type="text" class="form-control mt-2" id="typeOtherSpecify" name="research_type_other_specify" value="<?php echo getDraftValue('research_type_other_specify'); ?>" placeholder="Please specify" style="display: none;">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="projectDuration" class="form-label fw-semibold">Duration of Project <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="projectDuration" name="project_duration" value="<?php echo getDraftValue('project_duration'); ?>" placeholder="e.g., 12 months" required>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="fundingSource" class="form-label fw-semibold">Source(s) of Funding</label>
                                        <textarea class="form-control" id="fundingSource" name="funding_source" rows="2" placeholder="Name and address of funding source(s)"><?php echo getDraftValue('funding_source'); ?></textarea>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="priorIRB" class="form-label fw-semibold">Prior IRB Review</label>
                                        <textarea class="form-control" id="priorIRB" name="prior_irb" rows="3" placeholder="Name any other IRB this proposal has been submitted to. Attach approval letter if applicable. In case of rejection, state reasons."><?php echo getDraftValue('prior_irb'); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Section B Part 1 -->
                <div class="step-content" data-step="3">
                    <div class="card mb-4 border-info">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>SECTION B - PROPOSAL OUTLINE - PART 1</h5>
                                <p class="mb-0 opacity-75 small">Step 3 of 6 - Abstract, Introduction, Literature Review & Aims</p>
                            </div>
                            <span class="badge bg-white text-info">Required</span>
                        </div>
                        <div class="card-body">

                            <!-- Abstract/Executive Summary -->
                            <div class="mb-4">
                                <label for="abstract" class="form-label fw-semibold">ABSTRACT/EXECUTIVE SUMMARY <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="abstract" name="abstract" rows="5" maxlength="250" required><?php echo getDraftValue('abstract'); ?></textarea>
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-muted">Not more than 250 words</small>
                                    <small class="text-muted"><span id="abstract-count">0</span>/250 words</small>
                                </div>
                            </div>

                            <!-- Introduction/Rationale -->
                            <div class="mb-4">
                                <label for="introduction" class="form-label fw-semibold">INTRODUCTION/RATIONALE <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="introduction" name="introduction" rows="8" required><?php echo getDraftValue('introduction'); ?></textarea>
                                <small class="text-muted">Not more than 5 pages</small>
                            </div>

                            <!-- Literature Review -->
                            <div class="mb-4">
                                <label for="literatureReview" class="form-label fw-semibold">LITERATURE REVIEW <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="literatureReview" name="literature_review" rows="8" required><?php echo getDraftValue('literature_review'); ?></textarea>
                                <small class="text-muted">Not more than 5 pages</small>
                            </div>

                            <!-- Aims or Objectives -->
                            <div class="mb-4">
                                <label for="studyAims" class="form-label fw-semibold">AIMS OR OBJECTIVES OF STUDY <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="studyAims" name="study_aims" rows="4" required><?php echo getDraftValue('study_aims'); ?></textarea>
                                <small class="text-muted">List the main aims and objectives of your study</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Section B Part 2 -->
                <div class="step-content" data-step="4">
                    <div class="card mb-4 border-info">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><i class="fas fa-microscope me-2"></i>SECTION B - PROPOSAL OUTLINE - PART 2</h5>
                                <p class="mb-0 opacity-75 small">Step 4 of 6 - Methodology & Ethical Considerations</p>
                            </div>
                            <span class="badge bg-white text-info">Required</span>
                        </div>
                        <div class="card-body">

                            <!-- Methodology -->
                            <div class="mb-4">
                                <label for="methodology" class="form-label fw-semibold">METHODOLOGY <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="methodology" name="methodology" rows="8" required><?php echo getDraftValue('methodology'); ?></textarea>
                                <small class="text-muted">Include Inclusion and Exclusion Criteria</small>
                            </div>

                            <!-- Ethical Considerations -->
                            <div class="mb-4">
                                <label for="ethicalConsiderations" class="form-label fw-semibold">ETHICAL CONSIDERATIONS <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="ethicalConsiderations" name="ethical_considerations" rows="6" required><?php echo getDraftValue('ethical_considerations'); ?></textarea>
                                <small class="text-muted">Consent procedures, confidentiality, privacy, risks and benefits, etc.</small>
                            </div>

                            <!-- Expected Outcome/Results -->
                            <div class="mb-4">
                                <label for="expectedOutcomes" class="form-label fw-semibold">EXPECTED OUTCOME/RESULTS <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="expectedOutcomes" name="expected_outcomes" rows="4" required><?php echo getDraftValue('expected_outcomes'); ?></textarea>
                                <small class="text-muted">Describe expected outcomes and results</small>
                            </div>

                            <!-- References -->
                            <div class="mb-4">
                                <label for="references" class="form-label fw-semibold">REFERENCES <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="references" name="nmimr_references" rows="6" required placeholder="List all references in appropriate format"><?php echo getDraftValue('nmimr_references'); ?></textarea>
                                <small class="text-muted">List all references in appropriate format</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Section B Part 3 -->
                <div class="step-content" data-step="5">
                    <div class="card mb-4 border-warning">
                        <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><i class="fas fa-paperclip me-2"></i>SECTION B - PROPOSAL OUTLINE - PART 3</h5>
                                <p class="mb-0 opacity-75 small">Step 5 of 6 - Additional Materials & Documents</p>
                            </div>
                            <span class="badge bg-white text-warning">Required</span>
                        </div>
                        <div class="card-body">

                            <!-- Work Plan -->
                            <div class="mb-4">
                                <label for="workPlan" class="form-label fw-semibold">WORK PLAN</label>
                                <textarea class="form-control" id="workPlan" name="work_plan" rows="4"><?php echo getDraftValue('work_plan'); ?></textarea>
                                <small class="text-muted">Outline your work plan/timeline</small>
                            </div>

                            <!-- Budget -->
                            <div class="mb-4">
                                <label for="budget" class="form-label fw-semibold">BUDGET AND BUDGET JUSTIFICATION</label>
                                <textarea class="form-control" id="budget" name="budget" rows="5"><?php echo getDraftValue('budget'); ?></textarea>
                                <small class="text-muted">Provide detailed budget and justification</small>
                            </div>

                            <!-- Required Attachments -->
                            <div class="attachments-section p-3 border rounded">
                                <h6 class="fw-semibold mb-3"><i class="fas fa-paperclip me-2"></i>Required Attachments</h6>

                                <!-- Consent Form -->
                                <div class="mb-3">
                                    <label for="consentForm" class="form-label fw-semibold">CONSENT FORM <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="consentForm" name="consent_form" accept=".pdf,.doc,.docx" required>
                                    <small class="text-muted">Download NMIMR-IRB Consent form template</small>
                                </div>

                                <!-- Assent Form -->
                                <div class="mb-3">
                                    <label for="assentForm" class="form-label fw-semibold">ASSENT FORM AND PARENTAL CONSENT FORM</label>
                                    <input type="file" class="form-control" id="assentForm" name="assent_form" accept=".pdf,.doc,.docx">
                                    <small class="text-muted">Only applicable where children (12-17) are recruited</small>
                                </div>

                                <!-- Data Collection Instruments -->
                                <div class="mb-3">
                                    <label for="dataInstruments" class="form-label fw-semibold">DATA COLLECTION INSTRUMENTS <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="dataInstruments" name="data_instruments" accept=".pdf,.doc,.docx,.xls,.xlsx" multiple required>
                                    <small class="text-muted">Interview Guide, Questionnaire, etc.</small>
                                </div>

                                <!-- Additional Documents -->
                                <div class="mb-3">
                                    <label for="additionalDocs" class="form-label fw-semibold">Additional Supporting Documents</label>
                                    <input type="file" class="form-control" id="additionalDocs" name="additional_documents[]" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" multiple>
                                    <small class="text-muted">Any other relevant documents (maximum 10 files, 10MB each)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 6: Section C - Signatures -->
                <div class="step-content" data-step="6">
                    <div class="card mb-4 border-success">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><i class="fas fa-signature me-2"></i>SECTION C - SIGNATURES & SUBMISSION</h5>
                                <p class="mb-0 opacity-75 small">Step 6 of 6 - Declarations & final submission</p>
                            </div>
                            <span class="badge bg-white text-success">Required</span>
                        </div>
                        <div class="card-body">

                            <!-- PI Declaration -->
                            <div class="declaration-card mb-4 p-4 border rounded bg-light">
                                <h6 class="fw-bold mb-3">DECLARATION BY PRINCIPAL INVESTIGATOR / CO-INVESTIGATOR</h6>
                                <p class="mb-3">By signing below, I confirm that:</p>

                                <div class="mb-3">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="declaration1" name="declarations[]" value="declaration_1" required<?php echo isDeclarationChecked('declaration_1'); ?>>
                                        <label class="form-check-label" for="declaration1">
                                            I will ensure all procedures comply with relevant policies and regulations
                                        </label>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="declaration2" name="declarations[]" value="declaration_2" required<?php echo isDeclarationChecked('declaration_2'); ?>>
                                        <label class="form-check-label" for="declaration2">
                                            I will submit amendments for review prior to implementation
                                        </label>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="declaration3" name="declarations[]" value="declaration_3" required<?php echo isDeclarationChecked('declaration_3'); ?>>
                                        <label class="form-check-label" for="declaration3">
                                            I will report serious adverse events within specified timelines
                                        </label>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="declaration4" name="declarations[]" value="declaration_4" required<?php echo isDeclarationChecked('declaration_4'); ?>>
                                        <label class="form-check-label" for="declaration4">
                                            I will submit annual progress reports for review and renewal
                                        </label>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="declaration5" name="declarations[]" value="declaration_5" required<?php echo isDeclarationChecked('declaration_5'); ?>>
                                        <label class="form-check-label" for="declaration5">
                                            I will submit a final report at the end of the study
                                        </label>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-6 mb-3">
                                        <label for="piSignature" class="form-label fw-semibold">Name of Principal Investigator <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="piSignature" name="pi_signature" value="<?php echo getDraftValue('pi_signature'); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="piDate" class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="piDate" name="pi_date" value="<?php echo getDraftValue('pi_date'); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Co-PI Declaration -->
                            <div class="declaration-card mb-4 p-4 border rounded bg-light">
                                <h6 class="fw-bold mb-3">DECLARATION BY CO-PRINCIPAL INVESTIGATOR</h6>

                                <div class="row mt-3">
                                    <div class="col-md-6 mb-3">
                                        <label for="coPiSignature" class="form-label fw-semibold">Name of Co-Principal Investigator</label>
                                        <input type="text" class="form-control" id="coPiSignature" name="copi_signature" value="<?php echo getDraftValue('copi_signature'); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="coPiDate" class="form-label fw-semibold">Date</label>
                                        <input type="date" class="form-control" id="coPiDate" name="copi_date" value="<?php echo getDraftValue('copi_date'); ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Final Confirmation -->
                            <div class="alert alert-warning mt-4">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-exclamation-triangle me-3 mt-1"></i>
                                    <div>
                                        <h6 class="alert-heading">Important Notice</h6>
                                        <p class="mb-2">By submitting this form, you certify that all information provided is accurate and complete. Any false information may result in rejection of your application.</p>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="finalConfirmation" name="final_confirmation" value="1" <?php echo isChecked('final_confirmation', '1'); ?>>
                                            <label class="form-check-label fw-semibold" for="finalConfirmation">
                                                I confirm that all information provided is accurate and complete to the best of my knowledge
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons (Mobile) -->
                <div class="stepper-navigation-mobile d-lg-none mt-4">
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-outline-secondary" id="prevStepBtnMobile" disabled>
                            <i class="fas fa-arrow-left me-2"></i>Previous
                        </button>
                        <button class="btn btn-primary" id="nextStepBtnMobile" type="button">
                            <span class="spinner-container" style="display:none;">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                                <span class="button-text">Saving...</span>
                            </span>
                            <span class="button-text">Next <i class="fas fa-arrow-right ms-2"></i></span>
                        </button>
                    </div>
                </div>

                <!-- Final Submission Buttons -->
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top d-none" id="finalActions">
                    <div>
                        <button type="button" class="btn btn-outline-secondary" id="editFormBtn">
                            <i class="fas fa-edit me-2"></i>Edit Form
                        </button>
                    </div>
                    <div class="d-flex gap-3">
                        <button type="button" class="btn btn-outline-secondary" id="saveDraftBtn">
                            <span class="spinner-container" style="display:none;">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                                <span class="button-text">Saving...</span>
                            </span>
                            <span class="button-text"><i class="fas fa-save me-2"></i>Save Draft</span>
                        </button>
                        <button type="button" class="btn btn-light" onclick="window.history.back();">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#submitConfirmationModal">
                            <span class="spinner-container" style="display:none;">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                                <span class="button-text">Submitting...</span>
                            </span>
                            <span class="button-text"><i class="fas fa-paper-plane me-2"></i>Submit Protocol</span>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Submission Confirmation Modal -->
            <div class="modal fade" id="submitConfirmationModal" tabindex="-1" aria-labelledby="submitConfirmationModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="submitConfirmationModalLabel">Confirm Submission</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to submit this application? Once submitted, you cannot make further changes.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="confirmSubmitBtn">Confirm Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'admin/includes/loading_overlay.php' ?>

<style>
    .welcome-header {
        position: relative;
        overflow: hidden;
    }

    .header-decoration {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        opacity: 0.1;
        font-size: 4rem;
    }

    .header-decoration i {
        margin-left: 20px;
    }

    .stepper-sidebar {
        background: #fff;
        border-radius: 12px;
    }

    .stepper-vertical {
        position: relative;
    }

    .step {
        margin-bottom: 1.5rem;
        position: relative;
    }

    .step:last-child {
        margin-bottom: 0;
    }

    .step-number {
        width: 36px;
        height: 36px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .step.active .step-number {
        background-color: #0d6efd !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
    }

    .step.completed .step-number {
        background-color: #198754 !important;
        color: white !important;
    }

    .step.active .step-title h6 {
        color: #0d6efd !important;
    }

    .step.completed .step-title h6 {
        color: #198754 !important;
    }

    .step-progress {
        position: relative;
    }

    .step-line {
        position: absolute;
        left: 0;
        top: 0;
        width: 2px;
        height: calc(100% + 1.5rem);
        background: #e9ecef;
    }

    .step:last-child .step-line {
        display: none;
    }

    .step.active .step-line {
        background: linear-gradient(to bottom, #0d6efd, #e9ecef);
    }

    .step.completed .step-line {
        background: #198754;
    }

    .step-content {
        display: none;
        animation: fadeIn 0.5s ease;
    }

    .step-content.active {
        display: block;
    }

    .declaration-card {
        background-color: #f8f9fa;
        border-left: 4px solid #0d6efd;
    }

    .pi-info,
    .co-pi-entry,
    .project-info {
        border: 1px solid #dee2e6;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<script>
    // Current selected loader
    let currentLoader = 'spinner';
    // Current step tracking
    let currentStep = 1;
    const totalSteps = 6;
    const completedSteps = new Set();
    let coInvestigatorCount = 1;
    let isSubmitting = false; // Flag to prevent multiple submissions

    // Initialize form
    document.addEventListener('DOMContentLoaded', function() {
        // Get initial step from PHP (for resuming drafts)
        const initialStepEl = document.getElementById('initialStep');
        if (initialStepEl && parseInt(initialStepEl.value) > 1) {
            currentStep = parseInt(initialStepEl.value);
            // Mark all previous steps as completed
            for (let i = 1; i < currentStep; i++) {
                completedSteps.add(i);
            }
            // Navigate to the saved step
            goToStep(currentStep);
        }
        updateProgressBar();
        setupEventListeners();
        populateCoInvestigatorsFromDraft();
        updateAbstractCount();

     

    
    });

    // Setup event listeners
    function setupEventListeners() {
        // Step click navigation
        document.querySelectorAll('.step').forEach(step => {
            step.addEventListener('click', function() {
                const stepNum = parseInt(this.dataset.step);
                if (completedSteps.has(stepNum - 1) || stepNum === 1) {
                    goToStep(stepNum);
                }
            });
        });

        // Character counters
        setupCharacterCounters();

        // Other checkbox toggle
        const typeOther = document.getElementById('typeOther');
        if (typeOther && typeOther.checked) {
            document.getElementById('typeOtherSpecify').style.display = 'block';
        }
        typeOther.addEventListener('change', function() {
            document.getElementById('typeOtherSpecify').style.display = this.checked ? 'block' : 'none';
        });

        // Navigation buttons
        document.getElementById('nextStepBtn').addEventListener('click', function(e) {
            e.preventDefault();
            nextStep();
        });
        document.getElementById('prevStepBtn').addEventListener('click', function(e) {
            e.preventDefault();
            prevStep();
        });
        document.getElementById('nextStepBtnMobile').addEventListener('click', function(e) {
            e.preventDefault();
            nextStep();
        });
        document.getElementById('prevStepBtnMobile').addEventListener('click', function(e) {
            e.preventDefault();
            prevStep();
        });

        // Save draft button
        document.getElementById('saveDraftBtn').addEventListener('click', function(e) {
            e.preventDefault();
            saveDraft();
        });

        // Form submission
        document.getElementById('nmimrProtocolForm').addEventListener('submit', function(e) {
            submitForm(e, 'form-submit-event');
        });
        
        // Event listener for the confirm submit button in the modal
        const confirmSubmitBtn = document.getElementById('confirmSubmitBtn');
        if (confirmSubmitBtn) {
            confirmSubmitBtn.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent any form submission
                e.stopPropagation(); // Stop propagation
                if (isSubmitting) {
                    console.log('Click ignored: isSubmitting is already true');
                    return;
                }
                console.log('Submission confirmed. Setting isSubmitting=true');
               
                submitForm({ preventDefault: () => {} }, 'modal-click-handler');
                // Hide modal with fallback
                const modalElement = document.getElementById('submitConfirmationModal');
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                } else {
                    // Fallback: hide manually if Bootstrap instance not found
                    modalElement.classList.remove('show');
                    modalElement.style.display = 'none';
                    document.body.classList.remove('modal-open');
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) backdrop.remove();
                }
            });
        }
    }

       // Function to show loading overlay
    function showLoadingOverlay() {
        // Scroll to top smoothly
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });

        // Hide all loader contents
        document.querySelectorAll('.loader-content').forEach(content => {
            content.style.display = 'none';
        });

        // Show selected loader
        const loaderElement = document.getElementById(`${currentLoader}Loader`);
        if (loaderElement) {
            loaderElement.style.display = 'block';
        }

        // Update loading text with user's name
        const loadingText = document.querySelector('.loading-text');
        if (loadingText) {
            loadingText.textContent = `Processing...`;
        }

        // Show overlay with animation
        const overlay = document.getElementById('loadingOverlay');
        overlay.classList.add('active');

        // Disable body scroll
        document.body.style.overflow = 'hidden';

        // Simulate processing (3 seconds)
        setTimeout(() => {
            hideLoadingOverlay();

            // Show success message
            setTimeout(() => {
                // showToast('success', data.message);
                // Reset form
                // document.getElementById('studyForm').reset();
            }, 300);
        }, 3000);
    }

    // Function to hide loading overlay
    function hideLoadingOverlay() {
        const overlay = document.getElementById('loadingOverlay');
        overlay.classList.remove('active');

        // Re-enable body scroll
        document.body.style.overflow = 'auto';

        // Fade out animation
        setTimeout(() => {
            overlay.style.display = 'none';
        }, 300);
    }

    // Show loading spinner on button
    function showLoading(buttonId) {
        const btn = document.getElementById(buttonId);
        if (btn) {
            const spinnerContainer = btn.querySelector('.spinner-container');
            const buttonText = btn.querySelector('.button-text');
            if (spinnerContainer) {
                spinnerContainer.style.display = 'inline-flex';
            }
            if (buttonText) {
                buttonText.style.display = 'none';
            }
            btn.disabled = true;
        }
    }

    // Hide loading spinner on button
    function hideLoading(buttonId, originalText) {
        const btn = document.getElementById(buttonId);
        if (btn) {
            const spinnerContainer = btn.querySelector('.spinner-container');
            const buttonText = btn.querySelector('.button-text');
            if (spinnerContainer) {
                spinnerContainer.style.display = 'none';
            }
            if (buttonText) {
                buttonText.style.display = 'inline';
                if (originalText) {
                    buttonText.innerHTML = originalText;
                }
            }
            btn.disabled = false;
        }
    }

    // Update abstract word count
    function updateAbstractCount() {
        const abstractTextarea = document.getElementById('abstract');
        if (abstractTextarea) {
            const words = abstractTextarea.value.trim().split(/\s+/).filter(word => word.length > 0);
            document.getElementById('abstract-count').textContent = words.length;
        }
    }

    // Populate Co-Investigators from draft
    function populateCoInvestigatorsFromDraft() {
        const copiFieldsJson = document.getElementById('copiFieldsJson').value;
        if (!copiFieldsJson) return;

        try {
            const copiFields = JSON.parse(copiFieldsJson);
            const copiCount = Object.keys(copiFields).filter(key => key.endsWith('_name')).length;

            for (let i = 1; i <= copiCount; i++) {
                if (i > 1) {
                    addCoInvestigator(false);
                }
                const nameInput = document.querySelector(`input[name="copi${i}_name"]`);
                const qualInput = document.querySelector(`input[name="copi${i}_qualification"]`);
                const emailInput = document.querySelector(`input[name="copi${i}_department_email"]`);

                if (nameInput) nameInput.value = copiFields[`copi${i}_name`] || '';
                if (qualInput) qualInput.value = copiFields[`copi${i}_qualification`] || '';
                if (emailInput) emailInput.value = copiFields[`copi${i}_department_email`] || '';
            }
        } catch (e) {
            console.error('Error parsing Co-Investigator fields:', e);
        }
    }

    // Add Co-Investigator
    function addCoInvestigator(scroll = true) {
        coInvestigatorCount++;
        const container = document.querySelector('.co-pi-info');
        const addButton = container.querySelector('button');
        const newEntry = document.createElement('div');
        newEntry.className = 'co-pi-entry mb-3 p-3 border rounded';
        newEntry.id = `copi${coInvestigatorCount}_entry`;
        newEntry.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-semibold mb-0">Co-Principal Investigator ${coInvestigatorCount}</h6>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeCoInvestigator(${coInvestigatorCount})">
                    <i class="fas fa-times me-1"></i>Remove
                </button>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="copi${coInvestigatorCount}_name" class="form-label fw-semibold">Name</label>
                    <input type="text" class="form-control" id="copi${coInvestigatorCount}_name" name="copi${coInvestigatorCount}_name">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="copi${coInvestigatorCount}_qualification" class="form-label fw-semibold">Qualification</label>
                    <input type="text" class="form-control" id="copi${coInvestigatorCount}_qualification" name="copi${coInvestigatorCount}_qualification">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="copi${coInvestigatorCount}_department_email" class="form-label fw-semibold">Department & Email</label>
                    <input type="text" class="form-control" id="copi${coInvestigatorCount}_department_email" name="copi${coInvestigatorCount}_department_email">
                </div>
            </div>
        `;

        container.insertBefore(newEntry, addButton);

        if (scroll) {
            newEntry.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }
    }

    // Remove Co-Investigator
    function removeCoInvestigator(index) {
        const entry = document.getElementById(`copi${index}_entry`);
        if (entry) {
            entry.remove();
        }
    }

    // Navigation functions
    function goToStep(step) {
        if (step < 1 || step > totalSteps) return;

        currentStep = step;

        // Update step indicators with completed styling
        const steps = document.querySelectorAll('.step');
        steps.forEach((stepEl, index) => {
            const stepNum = index + 1;
            const stepNumber = stepEl.querySelector('.step-number');
            const stepTitle = stepEl.querySelector('.step-title h6');

            if (stepNum === currentStep) {
                // Current active step
                stepEl.classList.add('active');
                stepEl.classList.remove('completed');
                stepNumber.classList.remove('bg-light', 'text-muted', 'border');
                stepNumber.classList.add('bg-primary', 'text-white');
                stepTitle.classList.remove('text-muted');
                stepTitle.classList.add('text-dark');
            } else if (stepNum < currentStep) {
                // Completed steps - green styling
                stepEl.classList.remove('active');
                stepEl.classList.add('completed');
                stepNumber.classList.remove('bg-light', 'text-muted', 'border', 'bg-primary');
                stepNumber.classList.add('bg-success', 'text-white');
                stepTitle.classList.remove('text-muted');
                stepTitle.classList.add('text-success');
            } else {
                // Future steps
                stepEl.classList.remove('active', 'completed');
                stepNumber.classList.remove('bg-primary', 'bg-success', 'text-white');
                stepNumber.classList.add('bg-light', 'text-muted', 'border');
                stepTitle.classList.remove('text-dark', 'text-success');
                stepTitle.classList.add('text-muted');
            }
        });

        // Update content visibility
        const stepContents = document.querySelectorAll('.step-content');
        stepContents.forEach(content => {
            const contentStep = parseInt(content.dataset.step);
            content.classList.toggle('active', contentStep === currentStep);
        });

        updateProgressBar();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    // Handle Next step with draft save
    async function nextStep() {
        if (isSubmitting) return; // Prevent multiple clicks
        isSubmitting = true;

        // Show loading spinner
        showLoading('nextStepBtn');

        // Collect form data and save as draft
        const form = document.getElementById('nmimrProtocolForm');
        const formData = new FormData(form);
        formData.append('action', 'save_draft');
        formData.append('current_step', currentStep);

        try {
            // Send AJAX request to save draft
            const response = await fetch('/applicant/handlers/nmimr_application_handler.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            const data = await response.json();
            hideLoading('nextStepBtn', '<span class="button-text">Next <i class="fas fa-arrow-right ms-2"></i></span>');

            if (data.success) {
                // Update application_id if it's a new draft
                if (data.application_id) {
                    document.querySelector('input[name="application_id"]').value = data.application_id;
                }

                // Mark current step as completed and move to next step
                completedSteps.add(currentStep);

                if (currentStep < totalSteps) {
                    goToStep(currentStep + 1);
                }
            } else {
                alert(data.message || 'Failed to save draft. Please try again.');
            }
        } catch (error) {
            hideLoading('nextStepBtn', '<span class="button-text">Next <i class="fas fa-arrow-right ms-2"></i></span>');
            console.error('Error saving draft:', error);
            alert('An error occurred while saving. Please try again.');
        } finally {
            isSubmitting = false;
        }
    }

    function prevStep() {
        if (currentStep > 1) {
            goToStep(currentStep - 1);
        }
    }

    function updateProgressBar() {
        const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
        document.getElementById('stepperProgress').style.width = `${progress}%`;
        document.getElementById('currentStep').textContent = currentStep;

        // Update navigation buttons
        document.getElementById('prevStepBtn').disabled = currentStep === 1;
        document.getElementById('prevStepBtnMobile').disabled = currentStep === 1;

        // Show/hide final actions
        const finalActions = document.getElementById('finalActions');
        if (currentStep === totalSteps) {
            finalActions.classList.remove('d-none');
            document.getElementById('nextStepBtn').classList.add('d-none');
            document.getElementById('nextStepBtnMobile').classList.add('d-none');
        } else {
            finalActions.classList.add('d-none');
            document.getElementById('nextStepBtn').classList.remove('d-none');
            document.getElementById('nextStepBtnMobile').classList.remove('d-none');
        }
    }

    function setupCharacterCounters() {
        const abstractTextarea = document.getElementById('abstract');
        if (abstractTextarea) {
            abstractTextarea.addEventListener('input', updateAbstractCount);
        }
    }

    // Form submission
    async function submitForm(e, caller) {
        console.log('submitForm called by:', caller, '| Event:', e);
        
        // Wrap entire function in try-catch to catch any errors
        try {
            if (!e || typeof e.preventDefault !== 'function') {
                console.error('Invalid event object passed to submitForm by', caller);
                alert('Error: Invalid event object. Please try again.');
                return;
            }
            
            try {
                e.preventDefault();
            } catch (err) {
                console.error('preventDefault error:', err);
                alert('Error calling preventDefault. Please try again.');
                return;
            }

            if (isSubmitting) {
                 console.warn('Duplicate submission prevented.');
                return; // Prevent multiple clicks
            }
            isSubmitting = true;

            console.log('Starting form submission...');

            showLoading('submitBtn');
            showLoadingOverlay();

            const form = document.getElementById('nmimrProtocolForm');
            console.log('Form element found:', form);
            
            if (!form) {
                console.error('ERROR: Form nmimrProtocolForm not found!');
                alert('Error: Form not found. Please refresh the page.');
                isSubmitting = false;
                return;
            }
            
            const formData = new FormData(form);
            formData.append('action', 'submit');

            

            console.log('Sending fetch request...');
            
            const response = await fetch('/applicant/handlers/nmimr_application_handler.php', {
                method: 'POST',
                credentials: 'same-origin', // Ensure cookies are sent
                body: formData
            });

            // Debug: Log raw response for debugging
            const rawResponse = await response.text();
            console.log('Raw response:', rawResponse);
            console.log('Response status:', response.status);
            console.log('Content-Type:', response.headers.get('Content-Type'));

            // Check if response is JSON
            let result;
            try {
                result = JSON.parse(rawResponse);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                throw new Error(`Server returned non-JSON response (status ${response.status}). Response: ${rawResponse.substring(0, 200)}`);
            }

            hideLoadingOverlay();
            hideLoading('submitBtn', '<i class="fas fa-paper-plane me-2"></i>Submit Protocol');

            if (result.success) {
                // alert(result.message);
                if (result.redirect) {
                    window.location.href = result.redirect;
                }
            } else {
                alert(result.message);
                if (result.errors) {
                    console.error('Validation errors:', result.errors);
                }
            }
        } catch (error) {
            console.error('FULL SUBMIT FORM ERROR:', error);
            alert('An error occurred. Please try again.\nError: ' + error.message);
        } finally {
            hideLoadingOverlay();
            hideLoading('submitBtn', '<i class="fas fa-paper-plane me-2"></i>Submit Protocol');
            isSubmitting = false;
        }
    }

    // Save draft
    async function saveDraft() {
        if (isSubmitting) return; // Prevent multiple clicks
        isSubmitting = true;

        showLoading('saveDraftBtn');

        const form = document.getElementById('nmimrProtocolForm');
        const formData = new FormData(form);
        formData.append('action', 'save_draft');
        formData.append('current_step', currentStep);

        try {
            const response = await fetch('/applicant/handlers/nmimr_application_handler.php', {
                method: 'POST',
                credentials: 'same-origin', // Ensure cookies are sent
                body: formData
            });

            const result = await response.json();
            hideLoading('saveDraftBtn', '<i class="fas fa-save me-2"></i>Save Draft');

            if (result.success) {
                alert(result.message);
                // Update application_id if it's a new draft
                if (result.application_id) {
                    document.querySelector('input[name="application_id"]').value = result.application_id;
                }
            } else {
                alert(result.message);
            }
        } catch (error) {
            hideLoading('saveDraftBtn', '<i class="fas fa-save me-2"></i>Save Draft');
            console.error('Save draft error:', error);
            alert('An error occurred while saving. Please try again.');
        } finally {
            isSubmitting = false;
        }
    }

</script>