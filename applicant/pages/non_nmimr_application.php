<?php
// Start session if not already started (for direct access)
if (session_status() === PHP_SESSION_NONE) {
    // Set consistent session name before starting session
    if (!defined('CSRF_SESSION_NAME')) {
        define('CSRF_SESSION_NAME', 'ug_irb_session');
    }
    session_name(CSRF_SESSION_NAME);
    session_start();
}

// Check if applicant is logged in
$userId = $_SESSION['user_id'] ?? 0;

// Fetch applicant data from database
$applicantData = getApplicantProfile($userId);
$applicantName = '';
$applicantPhone = '';
$applicantEmail = '';
$institutionName = '';

if ($applicantData) {
    // Construct full name (Surname First format)
    $applicantName = trim(($applicantData['last_name'] ?? '') . ', ' . ($applicantData['first_name'] ?? '') . ' ' . ($applicantData['middle_name'] ?? ''));
    $applicantName = preg_replace('/^,\s*/', '', $applicantName);
    $applicantName = preg_replace('/\s+/', ' ', $applicantName);
    $applicantPhone = $applicantData['phone_number'] ?? '';
    $applicantEmail = $applicantData['email'] ?? '';

    // Get institution name from session or database
    $institutionId = $_SESSION['institution_id'] ?? ($applicantData['institution_id'] ?? null);
    if ($institutionId) {
        $institution = getInstitutionById($institutionId);
        $institutionName = $institution['institution_name'] ?? '';
    }
}

// Get application type
$type = $_GET['type'] ?? 'non_nmimr';

$draftData = [];
// Check if loading existing application
$existingApplicationId = $_GET['application_id'] ?? 0;
$existingApplication = null;
$existingApplicationDetails = null;
$currentStep = 1;

if ($existingApplicationId > 0) {
    $db = new Database();
    $conn = $db->connect();
    if ($conn) {
        try {
            $stmt = $conn->prepare("SELECT * FROM applications WHERE id = ? AND applicant_id = ?");
            $stmt->execute([$existingApplicationId, $userId]);
            $existingApplication = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmtDetails = $conn->prepare("SELECT * FROM non_nmimr_application_details WHERE application_id = ?");
            $stmtDetails->execute([$existingApplicationId]);
            $existingApplicationDetails = $stmtDetails->fetch(PDO::FETCH_ASSOC);

            $stmtDocs = $conn->prepare("SELECT document_type, file_path, file_name FROM application_documents WHERE application_id = ?");
            $stmtDocs->execute([$existingApplicationId]);
            $existingApplicationDocuments = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);

            if ($existingApplication) {
                $currentStep = $existingApplication['current_step'] ?? 1;
                $type = $existingApplication['application_type'] ?? $type;
                $draftData = array_merge($existingApplication, $existingApplicationDetails ?? [], ['documents' => $existingApplicationDocuments]);
            }
        } catch (PDOException $e) {
            error_log("Error loading existing application: " . $e->getMessage());
        }
    }
}

// Set title based on type
$applicationTypes = [
    'non_nmimr' => [
        'title' => 'Initial Submission - Non-UG Researchers',
        'icon' => 'fa-university',
        'description' => 'For external researchers - Complete all sections for ethics review consideration'
    ],
    'nmimr' => [
        'title' => 'Initial Submission - UG Researchers',
        'icon' => 'fa-flask',
        'description' => 'For UG staff and researchers'
    ]
];

$currentType = $applicationTypes[$type] ?? $applicationTypes['non_nmimr'];

function isResearchTypeChecked($rType): bool
{
    global $draftData;

    if (empty($draftData['research_type'])) {
        return false;
    }



    if ($draftData['research_type'] === $rType) {
        return true;
    }

    return false;
}

error_log("Research type from draft data: " . trim($draftData['research_type'] ?? 'NOT SET'));
error_log("Research type check for 'Biomedical': " . (isResearchTypeChecked('"Biomedical"') ? 'true' : 'false'));
?>

<style>
    /* Readonly input field styling */
    input[readonly] {
        background-color: #f8f9fa !important;
        border-color: #dee2e6 !important;
        color: #6c757d !important;
        cursor: not-allowed;
    }

    input[readonly]:focus {
        box-shadow: none !important;
    }
</style>

<!-- Link Applicant Sidebar CSS -->
<link href="/applicant/assets/css/sidebar.css" rel="stylesheet">

<div class="container-fluid dashboard-container">
    <div class="row">

        <!-- Main Content Area -->
        <div class="content-section col-lg-12 col-md-9 ms-sm-auto px-4 py-3">

            <!-- Mobile Sidebar Toggle Button -->
            <!-- <button class="mobile-sidebar-toggle mb-3" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i> Menu
            </button> -->

            <div class="add-new-protocol container-fluid mt-4 mb-4 p-4">
                <!-- Header -->
                <div class="welcome-header text-white p-4 rounded mb-4 position-relative overflow-hidden"
                    style="background:linear-gradient(135deg, var(--applicant-primary-dark) 0%, var(--applicant-primary) 100%);">
                    <div class="header-gradient"></div>
                    <div class="d-flex align-items-center position-relative z-1">
                        <div>
                            <h2 class="mb-1 fw-bold"><?php echo htmlspecialchars($currentType['title']); ?></h2>
                            <p class="mb-0 opacity-75"><?php echo htmlspecialchars($currentType['description']); ?></p>
                        </div>
                    </div>
                    <div class="header-decoration">
                        <i class="fas <?php echo $currentType['icon']; ?>"></i>
                        <i class="fas fa-file-alt"></i>
                        <i class="fas fa-edit"></i>
                    </div>
                </div>



                <!-- Main Wizard Container -->
                <div class="row">
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
                                        <!-- Step 1: Basic Information -->
                                        <div class="step active" data-step="1">
                                            <div class="step-header d-flex align-items-center mb-2">
                                                <div class="step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                                    1
                                                </div>
                                                <div class="step-title ms-3">
                                                    <h6 class="fw-semibold mb-0">Basic Information</h6>
                                                    <small class="text-muted">Personal & project details</small>
                                                </div>
                                            </div>
                                            <div class="step-progress ms-4 ps-3">
                                                <div class="step-line"></div>
                                            </div>
                                        </div>

                                        <!-- Step 2: Document Uploads -->
                                        <div class="step" data-step="2">
                                            <div class="step-header d-flex align-items-center mb-2">
                                                <div class="step-number bg-light text-muted border rounded-circle d-flex align-items-center justify-content-center">
                                                    2
                                                </div>
                                                <div class="step-title ms-3">
                                                    <h6 class="fw-semibold mb-0 text-muted">Document Uploads</h6>
                                                    <small class="text-muted">Required documents</small>
                                                </div>
                                            </div>
                                            <div class="step-progress ms-4 ps-3">
                                                <div class="step-line"></div>
                                            </div>
                                        </div>

                                        <!-- Step 3: Summary and Declarations -->
                                        <div class="step" data-step="3">
                                            <div class="step-header d-flex align-items-center">
                                                <div class="step-number bg-light text-muted border rounded-circle d-flex align-items-center justify-content-center">
                                                    3
                                                </div>
                                                <div class="step-title ms-3">
                                                    <h6 class="fw-semibold mb-0 text-muted">Summary and Declarations</h6>
                                                    <small class="text-muted">Review & submit</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Progress Indicator -->
                                    <div class="progress mt-4" style="height: 6px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 33%" id="stepperProgress"></div>
                                    </div>
                                    <div class="text-center mt-2">
                                        <small class="text-muted">Step <span id="currentStep">1</span> of 3</small>
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
                            <form id="nonNmimrProtocolForm" enctype="multipart/form-data">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="application_type" value="<?php echo htmlspecialchars($type); ?>">
                                <input type="hidden" name="application_id" id="applicationId" value="<?php echo $existingApplicationId; ?>">
                                <input type="hidden" name="initial_step" id="initialStep" value="<?php echo $currentStep; ?>">

                                <!-- Step 1: Protocol Information -->
                                <div class="step-content active" data-step="1">
                                    <div class="card mb-4">
                                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0"><i class="fas fa-file-signature me-2"></i>Basic Information</h5>
                                                <p class="mb-0 opacity-75 small">Step 1 of 3 - Personal & project details</p>
                                            </div>
                                            <span class="badge bg-primary">Required</span>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">

                                                <div class="col-12 mb-3">
                                                    <label for="study_title" class="form-label fw-semibold">Title of Study <span class="text-danger">*</span></label>
                                                    <textarea class="form-control" id="study_title" name="study_title" rows="2" required><?php echo htmlspecialchars($draftData['study_title'] ?? ''); ?></textarea>
                                                    <small class="text-muted">Clear and concise study title</small>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <label class="form-label fw-semibold">PRINCIPAL INVESTIGATOR DETAILS <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="row g-3 mb-3">
                                                    <div class="col-md-6">
                                                        <label for="pi_name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" id="pi_name" name="pi_name" value="<?php echo htmlspecialchars($draftData['pi_name'] ?? ''); ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="pi_institution" class="form-label fw-semibold">Institution <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" id="pi_institution" name="pi_institution" value="<?php echo htmlspecialchars($draftData['pi_institution'] ?? ''); ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="pi_address" class="form-label fw-semibold">Postal Address <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" id="pi_address" name="pi_address" value="<?php echo htmlspecialchars($draftData['pi_address'] ?? ''); ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="pi_phone_number" class="form-label fw-semibold">Telephone <span class="text-danger">*</span></label>
                                                        <input type="tel" class="form-control" id="pi_phone_number" name="pi_phone_number" value="<?php echo htmlspecialchars($draftData['pi_phone_number'] ?? ''); ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="pi_fax" class="form-label fw-semibold">Fax Number</label>
                                                        <input type="tel" class="form-control" id="pi_fax" name="pi_fax" value="<?php echo htmlspecialchars($draftData['pi_fax'] ?? ''); ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="pi_email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                                        <input type="email" class="form-control" id="pi_email" name="pi_email" value="<?php echo htmlspecialchars($draftData['pi_email'] ?? ''); ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <label class="form-label fw-semibold">CO-PRINCIPAL INVESTIGATOR(S)</label>
                                                </div>
                                                <div class="row g-3 mb-3">
                                                    <div class="col-md-6">
                                                        <label for="co_pi_name" class="form-label fw-semibold">Full Name</label>
                                                        <input type="text" class="form-control" id="co_pi_name" name="co_pi_name" value="<?php echo htmlspecialchars($draftData['co_pi_name'] ?? ''); ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="co_pi_qualification" class="form-label fw-semibold">Qualification (Specialty)</label>
                                                        <input type="text" class="form-control" id="co_pi_qualification" name="co_pi_qualification" value="<?php echo htmlspecialchars($draftData['co_pi_qualification'] ?? ''); ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="co_pi_department" class="form-label fw-semibold">Department</label>
                                                        <input type="text" class="form-control" id="co_pi_department" name="co_pi_department" value="<?php echo htmlspecialchars($draftData['co_pi_department'] ?? ''); ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="co_pi_address" class="form-label fw-semibold">Postal Address</label>
                                                        <input type="text" class="form-control" id="co_pi_address" name="co_pi_address" value="<?php echo htmlspecialchars($draftData['co_pi_address'] ?? ''); ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="co_pi_phone_number" class="form-label fw-semibold">Telephone</label>
                                                        <input type="tel" class="form-control" id="co_pi_phone_number" name="co_pi_phone_number" value="<?php echo htmlspecialchars($draftData['co_pi_phone_number'] ?? ''); ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="co_pi_fax" class="form-label fw-semibold">Fax Number</label>
                                                        <input type="tel" class="form-control" id="co_pi_fax" name="co_pi_fax" value="<?php echo htmlspecialchars($draftData['co_pi_fax'] ?? ''); ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="co_pi_email" class="form-label fw-semibold">Email Address</label>
                                                        <input type="email" class="form-control" id="co_pi_email" name="co_pi_email" value="<?php echo htmlspecialchars($draftData['co_pi_email'] ?? ''); ?>">
                                                    </div>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <label for="prior_scientific_review" class="form-label fw-semibold">Prior Scientific Review</label>
                                                    <textarea class="form-control" id="prior_scientific_review" name="prior_scientific_review" rows="3" placeholder="Provide details of any prior scientific review this proposal has undergone"><?php echo htmlspecialchars($draftData['prior_scientific_review'] ?? ''); ?></textarea>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <label for="prior_irb_review" class="form-label fw-semibold">Prior IRB Review</label>
                                                    <textarea class="form-control" id="prior_irb_review" name="prior_irb_review" rows="3" placeholder="Name any other IRB this proposal has been submitted to and attach approval letter if applicable. In case of rejection, state reasons"><?php echo htmlspecialchars($draftData['prior_irb_review'] ?? ''); ?></textarea>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">Type of Research <span class="text-danger">*</span></label>
                                                    <div class="mt-2">
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" id="type_biomedical" name="research_type" value="Biomedical" <?php echo isResearchTypeChecked('"Biomedical"') ? " checked" : ""; ?>>
                                                            <label class="form-check-label" for="type_biomedical">Biomedical</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" id="type_social" name="research_type" value="Social/Behavioural" <?php echo isResearchTypeChecked('"Social/Behavioural"') ? " checked" : ""; ?>>
                                                            <label class="form-check-label" for="type_social">Social/Behavioural</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" id="type_other" name="research_type" value="Other" <?php echo isResearchTypeChecked('"Other"') ? " checked" : ""; ?>>
                                                            <label class="form-check-label" for="type_other">Others</label>
                                                        </div>
                                                    </div>
                                                    <input type="text" class="form-control mt-2" id="research_type_other" name="research_type_other" placeholder="Specify other research type" style="display: <?php echo isResearchTypeChecked('"Other"') ? 'block' : 'none'; ?>;" value="<?php echo htmlspecialchars($draftData['research_type_other'] ?? ''); ?>">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="duration" class="form-label fw-semibold">Duration of Project <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="duration" name="duration" placeholder="e.g. 12 months, 24 months" value="<?php echo htmlspecialchars($draftData['duration'] ?? ''); ?>" required>
                                                    <small class="text-muted">Expected duration of the research project</small>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <label for="funding_source" class="form-label fw-semibold">Source(s) of Funding</label>
                                                    <textarea class="form-control" id="funding_source" name="funding_source" rows="2" placeholder="Name and Address of funding source(s)"><?php echo htmlspecialchars($draftData['funding_source'] ?? ''); ?></textarea>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 2: Section A - Background Information -->
                                <div class="step-content" data-step="2">
                                    <div class="card mb-4 border-primary">
                                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0"><i class="fas fa-upload me-2"></i>DOCUMENT UPLOADS</h5>
                                                <p class="mb-0 opacity-75 small">Step 2 of 3 - Required documents</p>
                                            </div>
                                            <span class="badge bg-white text-info">Required</span>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="alert alert-info mb-4">
                                                    <div class="d-flex align-items-start">
                                                        <i class="fas fa-info-circle me-3 mt-1 fs-4"></i>
                                                        <div>
                                                            <h6 class="alert-heading mb-2">Upload the following in ONE pdf document file for the Consolidated Document Upload</h6>
                                                            <ol class="mb-0 ps-3">
                                                                <li class="mb-1">ABSTRACT/EXECUTIVE SUMMARY (Not more than 250 words)
                                                                </li>
                                                                <li class="mb-1">BACKGROUND OR RATIONALE OF STUDY
                                                                    (This should include the aims and objectives, literature review; not more than 1500)
                                                                </li>
                                                                <li class="mb-1">METHODS
                                                                    (This should include the study site, population, study design, sampling, data collection, data analysis, inclusion and exclusion criteria)
                                                                </li>
                                                                <li class="mb-1">ETHICAL CONSIDERATIONS
                                                                    (Provide a description of the likely ethical issues and how it would be resolved. i.e. consent procedures, confidentiality, privacy, risks and benefits, etc.)
                                                                </li>
                                                                <li class="mb-1">EXPECTED OUTCOME/RESULTS</li>
                                                                <li class="mb-1">KEY REFERENCES</li>
                                                                <li class="mb-1">WORK PLAN</li>
                                                                <li class="mb-1">BUDGET AND BUDGET JUSTIFICATION</li>
                                                            </ol>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Consolidated Proposal Document -->
                                                <div class="mb-4">
                                                    <label for="consolidatedProposal" class="form-label fw-semibold">CONSOLIDATED PROPOSAL DOCUMENT <span class="text-danger">*</span></label>
                                                    <input type="file" class="form-control" id="consolidatedProposal" name="consolidatedProposal" accept=".pdf" required>
                                                    <small class="text-muted">Upload a single PDF document containing the list of items in the instructions above</small>
                                                    <?php if (!empty($draftData['consolidated_proposal'])): ?>
                                                        <small class="text-muted">Current file: <?php echo htmlspecialchars($draftData['consolidated_proposal']); ?></small>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="col-12 mb-3">
                                                    <label for="collaborating_institutions" class="form-label fw-semibold">Collaborating Institutions <span class="text-danger">*</span></label>
                                                    <textarea class="form-control" id="collaborating_institutions" name="collaborating_institutions" rows="2" placeholder="List all collaborating institutions" required><?php echo htmlspecialchars($draftData['collaborating_institutions'] ?? ''); ?></textarea>
                                                    <small class="text-muted">Attach Letter of Approval for each institution</small>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <label for="approval_letters" class="form-label fw-semibold">Upload Approval Letters</label>
                                                    <input type="file" class="form-control" id="approval_letters" name="approval_letters[]" multiple>
                                                    <small class="text-muted">Upload approval letters from collaborating institutions</small>
                                                    <?php if (!empty($draftData['approval_letters'])): ?>
                                                        <small class="text-muted">Current file: <?php echo htmlspecialchars($draftData['approval_letters']); ?></small>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="mb-4">
                                                    <label class="form-label fw-semibold">Required Forms</label>
                                                    <div class="mb-3">
                                                        <label for="consent_form" class="form-label">Consent Form (Download Consent form template) <span class="text-danger">*</span></label>
                                                        <input type="file" class="form-control" id="consent_form" name="consent_form" accept=".pdf,.doc,.docx" required>
                                                        <?php if (!empty($draftData['consent_form'])): ?>
                                                            <small class="text-muted">Current file: <?php echo htmlspecialchars($draftData['consent_form']); ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="assent_form" class="form-label">Assent Form and Parental Consent Form (Only applicable where children of ages 12 to 17 would be recruited as research participants)</label>
                                                        <input type="file" class="form-control" id="assent_form" name="assent_form" accept=".pdf,.doc,.docx">
                                                        <?php if (!empty($draftData['assent_form'])): ?>
                                                            <small class="text-muted">Current file: <?php echo htmlspecialchars($draftData['assent_form']); ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="data_instruments" class="form-label">Data Collection Instruments (i.e. Interview Guide, Questionnaire, etc.)</label>
                                                        <input type="file" class="form-control" id="data_instruments" name="data_instruments" accept=".pdf,.doc,.docx">
                                                        <?php if (!empty($draftData['data_instruments'])): ?>
                                                            <small class="text-muted">Current file: <?php echo htmlspecialchars($draftData['data_instruments']); ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <div class="mb-4">
                                                    <label for="required_forms" class="form-label fw-semibold">Upload Required Forms</label>
                                                    <input type="file" class="form-control" id="required_forms" name="required_forms[]" multiple>
                                                    <small class="text-muted">Upload consent forms, assent forms, and data collection instruments</small>
                                                </div>



                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 3: Summary and Declarations -->
                                <div class="step-content" data-step="3">
                                    <div class="card mb-4 border-success">
                                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>SUMMARY AND DECLARATIONS</h5>
                                                <p class="mb-0 opacity-75 small">Step 3 of 3 - Review & submit</p>
                                            </div>
                                            <span class="badge bg-white text-success">Final Step</span>
                                        </div>
                                        <div class="card-body">
                                            <div class="review-summary">
                                                <h6 class="fw-semibold mb-4 text-center">Please review your submission before finalizing</h6>

                                                <!-- Application Summary -->
                                                <div class="review-section mb-4">
                                                    <h6 class="fw-semibold border-bottom pb-2 mb-3">Application Summary</h6>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-2">
                                                            <small class="text-muted">Protocol Number:</small>
                                                            <div class="fw-medium" id="review_protocol_number">-</div>
                                                        </div>
                                                        <div class="col-md-6 mb-2">
                                                            <small class="text-muted">Version:</small>
                                                            <div class="fw-medium" id="review_version_number">-</div>
                                                        </div>
                                                        <div class="col-12 mb-2">
                                                            <small class="text-muted">Study Title:</small>
                                                            <div class="fw-medium" id="review_study_title">-</div>
                                                        </div>
                                                        <div class="col-md-6 mb-2">
                                                            <small class="text-muted">PI Name:</small>
                                                            <div class="fw-medium" id="review_pi_name">-</div>
                                                        </div>
                                                        <div class="col-md-6 mb-2">
                                                            <small class="text-muted">Research Type:</small>
                                                            <div class="fw-medium" id="review_research_type">-</div>
                                                        </div>
                                                        <div class="col-md-6 mb-2">
                                                            <small class="text-muted">Duration:</small>
                                                            <div class="fw-medium" id="review_duration">-</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Files Summary -->
                                                <div class="review-section mb-4">
                                                    <h6 class="fw-semibold border-bottom pb-2 mb-3">Uploaded Files</h6>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-2">
                                                            <small class="text-muted">Consolidated Proposal:</small>
                                                            <div class="fw-medium" id="review_consolidated_proposal">-</div>
                                                        </div>
                                                        <div class="col-md-6 mb-2">
                                                            <small class="text-muted">Consent Form:</small>
                                                            <div class="fw-medium" id="review_consent_form">-</div>
                                                        </div>
                                                        <div class="col-md-6 mb-2">
                                                            <small class="text-muted">Assent Form:</small>
                                                            <div class="fw-medium" id="review_assent_form">-</div>
                                                        </div>
                                                        <div class="col-md-6 mb-2">
                                                            <small class="text-muted">Data Instruments:</small>
                                                            <div class="fw-medium" id="review_data_instruments">-</div>
                                                        </div>
                                                        <div class="col-md-6 mb-2">
                                                            <small class="text-muted">Approval Letters:</small>
                                                            <div class="fw-medium" id="review_approval_letters">-</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Principal Investigator Declaration -->
                                                <div class="declaration-card mb-4 p-4 border rounded bg-light">
                                                    <h6 class="fw-bold mb-3">DECLARATION BY PRINCIPAL INVESTIGATOR</h6>
                                                    <p class="mb-3">As the <strong>Principal Investigator</strong> on this project, my signature confirms that:</p>

                                                    <div class="mb-3">
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox" id="pi_declaration_1" name="pi_declarations[]" value="1" required>
                                                            <label class="form-check-label" for="pi_declaration_1">
                                                                I will ensure that all procedures performed under the study will be conducted in accordance with all relevant policies and regulations that govern research involving human participants.
                                                            </label>
                                                        </div>

                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox" id="pi_declaration_2" name="pi_declarations[]" value="2" required>
                                                            <label class="form-check-label" for="pi_declaration_2">
                                                                I understand that if there is any change from the project as originally approved I must submit an amendment to the IRB for review and approval prior to its implementation. Where I fail to do so, the amended aspect of the study is invalid.
                                                            </label>
                                                        </div>

                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox" id="pi_declaration_3" name="pi_declarations[]" value="3" required>
                                                            <label class="form-check-label" for="pi_declaration_3">
                                                                I understand that I will report all serious adverse events associated with the study within seven days verbally and fourteen days in writing.
                                                            </label>
                                                        </div>

                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox" id="pi_declaration_4" name="pi_declarations[]" value="4" required>
                                                            <label class="form-check-label" for="pi_declaration_4">
                                                                I understand that I will submit progress reports each year for review and renewal. Where I fail to do so, the IRB is mandated to terminate the study upon expiry.
                                                            </label>
                                                        </div>

                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox" id="pi_declaration_5" name="pi_declarations[]" value="5" required>
                                                            <label class="form-check-label" for="pi_declaration_5">
                                                                I agree that I will submit a final report to the IRB at the end of the study.
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="row mt-4">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="pi_signature" class="form-label fw-semibold">Name of Principal Investigator <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" id="pi_signature" name="pi_signature" value="<?php echo htmlspecialchars($draftData['pi_signature'] ?? ''); ?>" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="pi_date" class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                                                            <input type="date" class="form-control" id="pi_date" name="pi_date" value="<?php echo htmlspecialchars($draftData['pi_date'] ?? ''); ?>" required>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Co-Principal Investigator Declaration -->
                                                <div class="declaration-card mb-4 p-4 border rounded bg-light">
                                                    <h6 class="fw-bold mb-3">DECLARATION BY CO-PRINCIPAL INVESTIGATOR</h6>

                                                    <div class="row mt-3">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="co_pi_signature" class="form-label fw-semibold">Name of Co-Principal Investigator</label>
                                                            <input type="text" class="form-control" id="co_pi_signature" name="co_pi_signature" value="<?php echo htmlspecialchars($draftData['co_pi_signature'] ?? ''); ?>">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="co_pi_date" class="form-label fw-semibold">Date</label>
                                                            <input type="date" class="form-control" id="co_pi_date" name="co_pi_date" value="<?php echo htmlspecialchars($draftData['co_pi_date'] ?? ''); ?>">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="alert alert-warning mt-4">
                                                    <div class="d-flex align-items-start">
                                                        <i class="fas fa-exclamation-triangle me-3 mt-1"></i>
                                                        <div>
                                                            <h6 class="alert-heading">Important Notice</h6>
                                                            <p class="mb-2">By submitting this form, you certify that all information provided is accurate and complete. Any false information may result in rejection of your application.</p>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" id="final_confirmation" name="final_confirmation" value="1" required>
                                                                <label class="form-check-label fw-semibold" for="final_confirmation">
                                                                    I confirm that all information provided is accurate and complete to the best of my knowledge.
                                                                </label>
                                                            </div>
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
                                            <i class="fas fa-save me-2"></i>Save Draft
                                        </button>
                                        <button type="button" class="btn btn-light" onclick="window.history.back();">
                                            <i class="fas fa-times me-2"></i>Cancel
                                        </button>
                                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#submitConfirmationModal">
                                            <i class="fas fa-paper-plane me-2"></i>Submit Protocol
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>


                </div>



            </div>

        </div>
    </div>




</div>

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


    .stepper-vertical .step-progress {
        padding-left: 2rem;
    }

    .stepper-vertical .step-line::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 2px;
        height: var(--progress, 0%);
        background-color: #0d6efd;
        transition: height 0.3s ease;
    }

    .step.active .step-line::before {
        height: calc(100% - 40px);
    }

    .step.completed .step-line::before {
        height: 100%;
        background-color: #198754;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Current selected loader
        let currentLoader = 'spinner';
        // Step management
        const steps = document.querySelectorAll('.stepper-vertical .step');
        const stepContents = document.querySelectorAll('.step-content');
        const prevBtn = document.getElementById('prevStepBtn');
        const nextBtn = document.getElementById('nextStepBtn');
        const prevBtnMobile = document.getElementById('prevStepBtnMobile');
        const nextBtnMobile = document.getElementById('nextStepBtnMobile');
        const progressBar = document.getElementById('stepperProgress');
        const currentStepDisplay = document.getElementById('currentStep');
        const finalActions = document.getElementById('finalActions');

        let currentStep = 1;
        const totalSteps = 3;
        const completedSteps = new Set();

        let isSubmitting = false; // Flag to prevent multiple submissions

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

        // Initialize steps
        updateSteps();
        updateProgressBar();
        updateReviewSummary();

        const today = new Date().toISOString().split('T')[0];
        document.getElementById('pi_date').value = today;
        if (document.getElementById('co_pi_name').value != '') {
            document.getElementById('co_pi_date').value = today;
        }



        // Handle "Other" research type visibility
        const typeOther = document.getElementById('type_other');
        const researchTypeOther = document.getElementById('research_type_other');

        if (typeOther && researchTypeOther) {
            typeOther.addEventListener('change', function() {
                researchTypeOther.style.display = this.checked ? 'block' : 'none';
            });

            // Initial state check
            if (typeOther.checked) {
                researchTypeOther.style.display = 'block';
            }
        }

        // Also check other radio buttons to hide the "Other" field
        const typeBiomedical = document.getElementById('type_biomedical');
        const typeSocial = document.getElementById('type_social');

        if ((typeBiomedical || typeSocial) && researchTypeOther) {
            const hideOtherField = function() {
                if (!typeOther.checked) {
                    researchTypeOther.style.display = 'none';
                }
            };

            if (typeBiomedical) typeBiomedical.addEventListener('change', hideOtherField);
            if (typeSocial) typeSocial.addEventListener('change', hideOtherField);
        }

        // File input change handlers
        const fileInputs = document.querySelectorAll('input[type="file"]');
        fileInputs.forEach(input => {
            input.addEventListener('change', function() {
                if (this.files.length > 0) {
                    this.classList.add('is-valid');
                }
                updateReviewSummary();
            });
        });

        // Update review summary on input changes
        const formInputs = document.querySelectorAll('input, textarea, select');
        formInputs.forEach(input => {
            input.addEventListener('input', updateReviewSummary);
            input.addEventListener('change', updateReviewSummary);
        });

        // Next button click - Save draft and navigate to next step
        if (nextBtn) {
            nextBtn.addEventListener('click', async function(e) {
                e.preventDefault();

                if (currentStep < totalSteps) {
                    // Validate current step before proceeding
                    if (!validateStep(currentStep)) {
                        return;
                    }

                    // Save draft and move to next step
                    await nextStep();
                }
            });
        }

        // Next button mobile click - Save draft and navigate to next step
        if (nextBtnMobile) {
            nextBtnMobile.addEventListener('click', async function(e) {
                e.preventDefault();

                if (currentStep < totalSteps) {
                    // Validate current step before proceeding
                    if (!validateStep(currentStep)) {
                        return;
                    }

                    // Save draft and move to next step
                    await nextStep();
                }
            });
        }

        // Update review summary
        function updateReviewSummary() {
            // Protocol info
            document.getElementById('review_protocol_number').textContent =
                '-';
            document.getElementById('review_version_number').textContent =
                '-';
            document.getElementById('review_study_title').textContent =
                document.getElementById('study_title').value || '-';

            // PI info
            document.getElementById('review_pi_name').textContent =
                document.getElementById('pi_name').value || '-';

            // Research type
            const researchType = document.querySelector('input[name="research_type"]:checked');
            document.getElementById('review_research_type').textContent =
                researchType ? researchType.value : '-';

            // Duration
            document.getElementById('review_duration').textContent =
                document.getElementById('duration').value || '-';

            // Files
            const consolidatedFile = document.getElementById('consolidatedProposal').files[0];
            document.getElementById('review_consolidated_proposal').textContent =
                consolidatedFile ? consolidatedFile.name : 'No file selected';

            const consentFile = document.getElementById('consent_form').files[0];
            document.getElementById('review_consent_form').textContent =
                consentFile ? consentFile.name : 'No file selected';

            const assentFile = document.getElementById('assent_form').files[0];
            document.getElementById('review_assent_form').textContent =
                assentFile ? assentFile.name : 'No file selected';

            const dataFiles = document.getElementById('data_instruments').files;
            document.getElementById('review_data_instruments').textContent =
                dataFiles.length > 0 ? `${dataFiles.length} file(s)` : 'No files selected';

            const approvalFiles = document.getElementById('approval_letters').files;
            document.getElementById('review_approval_letters').textContent =
                approvalFiles.length > 0 ? `${approvalFiles.length} file(s)` : 'No files selected';
        }

        // Navigation functions
        function goToStep(step) {
            if (step < 1 || step > totalSteps) return;

            currentStep = step;

            // Update step indicators
            const steps = document.querySelectorAll('.step');
            steps.forEach((stepEl, index) => {
                const stepNum = index + 1;
                const stepNumber = stepEl.querySelector('.step-number');
                const stepTitle = stepEl.querySelector('.step-title h6');

                if (stepNum === currentStep) {
                    stepEl.classList.add('active');
                    stepEl.classList.remove('completed');
                    if (stepNumber) {
                        stepNumber.classList.remove('bg-light', 'text-muted', 'border');
                        stepNumber.classList.add('bg-primary', 'text-white');
                    }
                    if (stepTitle) {
                        stepTitle.classList.remove('text-muted');
                        stepTitle.classList.add('text-dark');
                    }
                } else if (stepNum < currentStep) {
                    stepEl.classList.remove('active');
                    stepEl.classList.add('completed');
                    if (stepNumber) {
                        stepNumber.classList.remove('bg-light', 'text-muted', 'border', 'bg-primary');
                        stepNumber.classList.add('bg-success', 'text-white');
                    }
                    if (stepTitle) {
                        stepTitle.classList.remove('text-muted');
                        stepTitle.classList.add('text-success');
                    }
                } else {
                    stepEl.classList.remove('active', 'completed');
                    if (stepNumber) {
                        stepNumber.classList.remove('bg-primary', 'bg-success', 'text-white');
                        stepNumber.classList.add('bg-light', 'text-muted', 'border');
                    }
                    if (stepTitle) {
                        stepTitle.classList.remove('text-dark', 'text-success');
                        stepTitle.classList.add('text-muted');
                    }
                }
            });

            // Update content visibility
            const stepContents = document.querySelectorAll('.step-content');
            stepContents.forEach(content => {
                const contentStep = parseInt(content.dataset.step);
                content.classList.toggle('active', contentStep === currentStep);
            });

            updateProgressBar();

            // Scroll to top
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Form submission function
        function submitForm() {
            const form = document.getElementById('nonNmimrProtocolForm');
            const formData = new FormData(form);

            showLoadingOverlay();

            // Show loading indicator
            const submitBtn = document.getElementById('nextBtn') || document.getElementById('nextStepBtn');
            const submitBtnMobile = document.getElementById('nextStepBtnMobile');
            const originalBtnText = submitBtn ? submitBtn.innerHTML : 'Submit';
            const originalBtnTextMobile = submitBtnMobile ? submitBtnMobile.innerHTML : 'Submit';

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Submitting...';
            }
            if (submitBtnMobile) {
                submitBtnMobile.disabled = true;
                submitBtnMobile.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Submitting...';
            }

            // Submit via AJAX
            fetch('/applicant/handlers/non_nmimr_application_handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // alert('Application submitted successfully!');
                        hideLoadingOverlay();
                        // Optionally redirect to dashboard or show success message
                        window.location.href = '/applicant-dashboard';
                    } else {
                        hideLoadingOverlay();
                        alert('Error: ' + (data.message || 'An error occurred while submitting the application.'));
                    }
                })
                .catch(error => {
                    hideLoadingOverlay();
                    console.error('Error:', error);
                    alert('An error occurred while submitting the application. Please try again.');
                })
                .finally(() => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                    if (submitBtnMobile) {
                        submitBtnMobile.disabled = false;
                        submitBtnMobile.innerHTML = originalBtnTextMobile;
                    }
                });
        }

        // Previous button click
        if (prevBtn) {
            prevBtn.addEventListener('click', function() {
                if (currentStep > 1) {
                    currentStep--;
                    updateSteps();
                    updateProgressBar();
                    updateNavigationButtons();
                }
            });
        }

        // Previous button mobile click
        if (prevBtnMobile) {
            prevBtnMobile.addEventListener('click', function() {
                if (currentStep > 1) {
                    currentStep--;
                    updateSteps();
                    updateProgressBar();
                    updateNavigationButtons();
                }
            });
        }

        // Step click navigation
        steps.forEach((step, index) => {
            step.style.cursor = 'pointer';
            step.addEventListener('click', function() {
                // Only allow navigation to completed steps or next step
                const stepNum = index + 1;
                if (stepNum <= currentStep || stepNum === currentStep + 1) {
                    // Validate current step before proceeding
                    if (stepNum !== currentStep && validateStep(currentStep)) {
                        currentStep = stepNum;
                        updateSteps();
                        updateProgressBar();
                        updateNavigationButtons();
                    }
                }
            });
        });

        // Functions
        function updateSteps() {
            // Update stepper sidebar
            steps.forEach((step, index) => {
                step.classList.remove('active', 'completed');

                const stepNumber = step.querySelector('.step-number');
                const stepTitle = step.querySelector('.step-title h6');
                const stepNum = index + 1;

                if (stepNum === currentStep) {
                    step.classList.add('active');
                    if (stepNumber) {
                        stepNumber.classList.remove('bg-light', 'text-muted', 'border');
                        stepNumber.classList.add('bg-primary', 'text-white');
                    }
                    if (stepTitle) {
                        stepTitle.classList.remove('text-muted');
                    }
                } else if (stepNum < currentStep) {
                    step.classList.add('completed');
                    if (stepNumber) {
                        stepNumber.classList.remove('bg-light', 'text-muted', 'border', 'bg-primary', 'text-white');
                        stepNumber.classList.add('bg-success', 'text-white');
                    }
                } else {
                    if (stepNumber) {
                        stepNumber.classList.remove('bg-primary', 'text-white', 'bg-success', 'text-white');
                        stepNumber.classList.add('bg-light', 'text-muted', 'border');
                    }
                    if (stepTitle) {
                        stepTitle.classList.add('text-muted');
                    }
                }
            });

            // Update step content visibility
            stepContents.forEach((content, index) => {
                content.classList.remove('active', 'show');
                const stepNum = index + 1;
                if (stepNum === currentStep) {
                    content.classList.add('active', 'show');
                }
            });

            // Update current step display
            if (currentStepDisplay) {
                currentStepDisplay.textContent = currentStep;
            }

            // Update button text on last step
            if (nextBtn) {
                if (currentStep === totalSteps) {
                    nextBtn.innerHTML = 'Submit <i class="fas fa-paper-plane ms-2"></i>';
                    nextBtn.classList.remove('btn-primary');
                    nextBtn.classList.add('btn-success');
                } else {
                    nextBtn.innerHTML = 'Next <i class="fas fa-arrow-right ms-2"></i>';
                    nextBtn.classList.remove('btn-success');
                    nextBtn.classList.add('btn-primary');
                }
            }

            if (nextBtnMobile) {
                if (currentStep === totalSteps) {
                    nextBtnMobile.innerHTML = 'Submit <i class="fas fa-paper-plane ms-2"></i>';
                    nextBtnMobile.classList.remove('btn-primary');
                    nextBtnMobile.classList.add('btn-success');
                } else {
                    nextBtnMobile.innerHTML = 'Next <i class="fas fa-arrow-right ms-2"></i>';
                    nextBtnMobile.classList.remove('btn-success');
                    nextBtnMobile.classList.add('btn-primary');
                }
            }

            // Show/hide final actions on last step
            if (finalActions) {
                if (currentStep === totalSteps) {
                    finalActions.classList.remove('d-none');
                } else {
                    finalActions.classList.add('d-none');
                }
            }
        }

        function updateProgressBar() {
            const progressBar = document.getElementById('stepperProgress');
            if (progressBar) {
                const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
                progressBar.style.width = `${progress}%`;
            }

            const currentStepDisplay = document.getElementById('currentStep');
            if (currentStepDisplay) {
                currentStepDisplay.textContent = currentStep;
            }
        }

        function updateNavigationButtons() {
            if (prevBtn) {
                prevBtn.disabled = currentStep === 1;
            }
            if (prevBtnMobile) {
                prevBtnMobile.disabled = currentStep === 1;
            }
        }

        function validateStep(stepIndex) {
            if (stepIndex === 1) {
                const studyTitle = document.getElementById('study_title');
                const piName = document.getElementById('pi_name');
                const piInstitution = document.getElementById('pi_institution');
                const piAddress = document.getElementById('pi_address');
                const piPhone = document.getElementById('pi_phone_number');
                const piEmail = document.getElementById('pi_email');
                const researchType = document.querySelector('input[name="research_type"]:checked');
                const duration = document.getElementById('duration');

                if (studyTitle && !studyTitle.value.trim()) {
                    alert('Please enter the title of the study');
                    studyTitle.focus();
                    return false;
                }

                if (piName && !piName.value.trim()) {
                    alert('Please enter the Principal Investigator name');
                    piName.focus();
                    return false;
                }

                if (piInstitution && !piInstitution.value.trim()) {
                    alert('Please enter the Principal Investigator institution');
                    piInstitution.focus();
                    return false;
                }

                if (piAddress && !piAddress.value.trim()) {
                    alert('Please enter the Principal Investigator address');
                    piAddress.focus();
                    return false;
                }

                if (piPhone && !piPhone.value.trim()) {
                    alert('Please enter the Principal Investigator phone number');
                    piPhone.focus();
                    return false;
                }

                if (piEmail && !piEmail.value.trim()) {
                    alert('Please enter the Principal Investigator email');
                    piEmail.focus();
                    return false;
                }

                if (piEmail && piEmail.value.trim()) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(piEmail.value.trim())) {
                        alert('Please enter a valid email address for Principal Investigator');
                        piEmail.focus();
                        return false;
                    }
                }

                if (!researchType) {
                    alert('Please select the type of research');
                    return false;
                }

                if (duration && !duration.value.trim()) {
                    alert('Please enter the duration of the project');
                    duration.focus();
                    return false;
                }

                return true;
            }

            if (stepIndex === 2) {
                const consolidatedProposal = document.getElementById('consolidatedProposal');
                const approvalLetters = document.getElementById('approval_letters');
                const consentForm = document.getElementById('consent_form');
                const collaboratingInstitutions = document.getElementById('collaborating_institutions');

                // For existing applications, files might already be uploaded
                const applicationId = document.querySelector('input[name="application_id"]').value;
                const isExistingApplication = applicationId && parseInt(applicationId) > 0;

                if (consolidatedProposal && consolidatedProposal.files.length === 0 && !isExistingApplication) {
                    alert('Please upload the consolidated proposal document');
                    return false;
                }

                if (approvalLetters && approvalLetters.files.length === 0 && !isExistingApplication) {
                    alert('Please upload the approval letters from collaborating institutions');
                    return false;
                }

                if (consentForm && consentForm.files.length === 0 && !isExistingApplication) {
                    alert('Please upload the consent form');
                    return false;
                }

                if (collaboratingInstitutions && !collaboratingInstitutions.value.trim()) {
                    alert('Please list collaborating institutions');
                    collaboratingInstitutions.focus();
                    return false;
                }

                return true;
            }

            if (stepIndex === 3) {
                const finalConfirmation = document.getElementById('final_confirmation');
                const piSignature = document.getElementById('pi_signature');
                const piDate = document.getElementById('pi_date');

                if (piSignature && !piSignature.value.trim()) {
                    alert('Please enter the Principal Investigator signature name');
                    piSignature.focus();
                    return false;
                }

                if (piDate && !piDate.value) {
                    alert('Please select the date for Principal Investigator signature');
                    piDate.focus();
                    return false;
                }

                if (finalConfirmation && !finalConfirmation.checked) {
                    alert('Please confirm that all information provided is accurate and complete');
                    return false;
                }

                return true;
            }

            return true;
        }



        // Show loading spinner on button
        function showLoading(buttonId) {
            const btn = document.getElementById(buttonId);
            if (btn) {
                const spinnerContainer = btn.querySelector('.spinner-container');
                // Find the main button text (outside spinner container)
                const mainButtonText = btn.querySelector('.button-text:not(.spinner-container .button-text)');
                if (spinnerContainer) {
                    spinnerContainer.style.display = 'inline-flex';
                }
                if (mainButtonText) {
                    mainButtonText.style.display = 'none';
                }
                btn.disabled = true;
            }
        }

        // Hide loading spinner on button
        function hideLoading(buttonId, originalText) {
            const btn = document.getElementById(buttonId);
            if (btn) {
                const spinnerContainer = btn.querySelector('.spinner-container');
                const mainButtonText = btn.querySelector('.button-text:not(.spinner-container .button-text)');
                if (spinnerContainer) {
                    spinnerContainer.style.display = 'none';
                }
                if (mainButtonText) {
                    mainButtonText.style.display = 'inline';
                    if (originalText) {
                        mainButtonText.innerHTML = originalText;
                    }
                }
                btn.disabled = false;
            }
        }

        // Handle Next step with draft save
        async function nextStep() {
            if (isSubmitting) return; // Prevent multiple clicks
            isSubmitting = true;

            // Show loading spinner
            showLoading('nextStepBtn');
            showLoading('nextStepBtnMobile');

            // Collect form data and save as draft
            const form = document.getElementById('nonNmimrProtocolForm');
            const formData = new FormData(form);
            formData.append('action', 'save_draft');
            formData.append('current_step', currentStep);

            try {
                // Send AJAX request to save draft
                const response = await fetch('/applicant/handlers/non_nmimr_application_handler.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'include' // Changed from 'same-origin' to 'include' to ensure cookies are sent
                });

                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    // Response is not JSON - likely a session/auth issue
                    console.error('Unexpected response type:', contentType);
                    console.error('Response text:', await response.text().then(t => t.substring(0, 500)));

                    hideLoading('nextStepBtn', '<span class="button-text">Next <i class="fas fa-arrow-right ms-2"></i></span>');
                    hideLoading('nextStepBtnMobile', '<span class="button-text">Next <i class="fas fa-arrow-right ms-2"></i></span>');

                    // Check for session-related errors
                    if (response.status === 403 || response.status === 401) {
                        alert('Your session may have expired. Please refresh the page and try again.');
                    } else {
                        alert('An error occurred while saving. Please try again. If the problem persists, please refresh the page.');
                    }
                    isSubmitting = false;
                    return;
                }

                const data = await response.json();
                hideLoading('nextStepBtn', '<span class="button-text">Next <i class="fas fa-arrow-right ms-2"></i></span>');
                hideLoading('nextStepBtnMobile', '<span class="button-text">Next <i class="fas fa-arrow-right ms-2"></i></span>');

                if (data.success) {
                    // Update application_id if it's a new draft
                    if (data.application_id) {
                        document.querySelector('input[name="application_id"]').value = data.application_id;
                    }

                    // Mark current step as completed and move to next step
                    completedSteps.add(currentStep);

                    if (currentStep < totalSteps) {
                        currentStep++;
                        updateSteps();
                        updateProgressBar();
                        updateNavigationButtons();
                    }
                } else {
                    alert(data.message || 'Failed to save draft. Please try again.');
                }
            } catch (error) {
                hideLoading('nextStepBtn', '<span class="button-text">Next <i class="fas fa-arrow-right ms-2"></i></span>');
                hideLoading('nextStepBtnMobile', '<span class="button-text">Next <i class="fas fa-arrow-right ms-2"></i></span>');
                console.error('Error saving draft:', error);
                alert('An error occurred while saving. Please try again.');
            } finally {
                isSubmitting = false;
            }
        }

        // Handle Save Draft button
        async function saveDraft() {
            if (isSubmitting) return;
            isSubmitting = true;

            showLoading('saveDraftBtn');

            const form = document.getElementById('nonNmimrProtocolForm');
            const formData = new FormData(form);
            formData.append('action', 'save_draft');
            formData.append('current_step', currentStep);

            try {
                const response = await fetch('/applicant/handlers/non_nmimr_application_handler.php', {
                    method: 'POST',
                    credentials: 'include', // Changed from 'same-origin' to 'include' to ensure cookies are sent
                    body: formData
                });

                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    // Response is not JSON - likely a session/auth issue
                    console.error('Unexpected response type:', contentType);
                    console.error('Response text:', await response.text().then(t => t.substring(0, 500)));

                    // Check for session-related errors
                    if (response.status === 403 || response.status === 401) {
                        alert('Your session may have expired. Please refresh the page and try again.');
                    } else {
                        alert('An error occurred. Please try again. If the problem persists, please refresh the page.');
                    }
                    isSubmitting = false;
                    hideLoading('saveDraftBtn', '<i class="fas fa-save me-2"></i>Save Draft');
                    return;
                }

                const result = await response.json();
                hideLoading('saveDraftBtn', '<i class="fas fa-save me-2"></i>Save Draft');

                if (result.success) {
                    alert(result.message);
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

        // Handle Confirm Submit button in modal
        const confirmSubmitBtn = document.getElementById('confirmSubmitBtn');
        if (confirmSubmitBtn) {
            confirmSubmitBtn.addEventListener('click', function() {
                const modal = bootstrap.Modal.getInstance(document.getElementById('submitConfirmationModal'));
                if (modal) modal.hide();

                console.log("Proceeding with submission...");

                // VALIDATE ALL STEPS FIRST
                for (let step = 1; step <= 3; step++) {
                    if (!validateStep(step)) {
                        alert(`Please complete Step ${step} before submitting.`);
                        // Go to the step that needs completion
                        goToStep(step);
                        return;
                    }
                }

                // Validate final confirmation checkbox
                const finalConfirmation = document.getElementById('final_confirmation');
                if (!finalConfirmation || !finalConfirmation.checked) {
                    alert('Please confirm that all information is accurate and complete.');
                    return;
                }

                showLoadingOverlay();

                const form = document.getElementById('nonNmimrProtocolForm');
                const formData = new FormData(form);
                formData.append('action', 'submit');

                // Show loading on confirm button
                confirmSubmitBtn.disabled = true;
                confirmSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Submitting...';

                fetch('/applicant/handlers/non_nmimr_application_handler.php', {
                        method: 'POST',
                        credentials: 'same-origin',
                        body: formData
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        console.log('Response headers:', response.headers);
                        // Check if response is OK
                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Hide loading overlay only once
                        hideLoadingOverlay();

                        if (data.success) {
                            // Show success message before redirect
                            alert(data.message || 'Application submitted successfully!');
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            } else {
                                window.location.href = '/applicant-dashboard';
                            }
                        } else {
                            // Show error message from server
                            alert(data.message || 'Submission failed. Please try again.');
                            if (data.errors) {
                                console.error('Validation errors:', data.errors);
                            }
                        }
                    })
                    .catch(error => {
                        hideLoadingOverlay();
                        console.error('Submit error:', error);
                        alert('An error occurred: ' + error.message + '. Please try again.');
                    })
                    .finally(() => {
                        confirmSubmitBtn.disabled = false;
                        confirmSubmitBtn.innerHTML = 'Confirm Submit';
                    });
            });
        }

        // Handle Submit Protocol button click - open modal explicitly
        const submitProtocolBtn = document.querySelector('button[data-bs-target="#submitConfirmationModal"]');
        if (submitProtocolBtn) {
            submitProtocolBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const modal = new bootstrap.Modal(document.getElementById('submitConfirmationModal'));
                modal.show();
            });
        }

        // Handle Save Draft button click
        const saveDraftBtn = document.getElementById('saveDraftBtn');
        if (saveDraftBtn) {
            saveDraftBtn.addEventListener('click', function(e) {
                e.preventDefault();
                saveDraft();
            });
        }

        // Handle form submission
        const form = document.getElementById('nonNmimrProtocolForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                // Don't submit directly - show confirmation modal instead
                // The modal will handle the actual submission
            });
        }
        // Function to show loading overlay
        function showLoadingOverlay() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });

            document.querySelectorAll('.loader-content').forEach(content => {
                content.style.display = 'none';
            });

            const loaderElement = document.getElementById(`${currentLoader}Loader`);
            if (loaderElement) {
                loaderElement.style.display = 'block';
            }

            const loadingText = document.querySelector('.loading-text');
            if (loadingText) {
                loadingText.textContent = `Processing...`;
            }

            const overlay = document.getElementById('loadingOverlay');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';

            // REMOVE THIS AUTOMATIC TIMEOUT - it will hide the overlay prematurely
            // setTimeout(() => {
            //     hideLoadingOverlay();
            // }, 3000);
        }

        function hideLoadingOverlay() {
            const overlay = document.getElementById('loadingOverlay');
            overlay.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    });

    // Mobile Sidebar Toggle Functions
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.querySelector('.sidebar-backdrop');

        if (sidebar) {
            sidebar.classList.toggle('show');
        }

        if (backdrop) {
            backdrop.classList.toggle('show');
        }

        // Prevent body scroll when sidebar is open
        document.body.classList.toggle('sidebar-open');
    }

    function closeSidebar() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.querySelector('.sidebar-backdrop');

        if (sidebar) {
            sidebar.classList.remove('show');
        }

        if (backdrop) {
            backdrop.classList.remove('show');
        }

        document.body.classList.remove('sidebar-open');
    }

    // Close sidebar on Escape key press
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSidebar();
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>