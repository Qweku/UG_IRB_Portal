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

            if ($existingApplication) {
                $currentStep = $existingApplication['current_step'] ?? 1;
                $type = $existingApplication['application_type'] ?? $type;
                $draftData = array_merge($existingApplication, $existingApplicationDetails ?? []);
            }
        } catch (PDOException $e) {
            error_log("Error loading existing application: " . $e->getMessage());
        }
    }
}

// Set title based on type
$applicationTypes = [
    'non_nmimr' => [
        'title' => 'Initial Submission Form A - Non-NMIMR Researchers',
        'icon' => 'fa-university',
        'description' => 'For external researchers - Complete all sections for ethics review consideration'
    ],
    'nmimr' => [
        'title' => 'Initial Submission Form A - NMIMR Researchers',
        'icon' => 'fa-flask',
        'description' => 'For NMIMR staff and researchers'
    ]
];

$currentType = $applicationTypes[$type] ?? $applicationTypes['non_nmimr'];
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
        <?php include 'sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3">
            
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

    <div class="row justify-content-end mb-4">
        <div class="col-md-6">
            <!-- Instructions Alert -->
            <div class="alert alert-info mb-4" style="height: 100%;">
                <div class="d-flex align-items-start">
                    <i class="fas fa-info-circle me-3 mt-1 fs-4"></i>
                    <div>
                        <h6 class="alert-heading mb-2">Submission Instructions</h6>
                        <ol class="mb-0 ps-3">
                            <li class="mb-1">Please complete all sections before it will be considered for ethics review</li>
                            <li class="mb-1">Download the NMIMR-IRB Researchers Checklist for further instructions</li>
                            <li class="mb-1">The proposal and the consent form should be paged separately</li>
                            <!-- <li class="mb-1">Use very clear font size such as Times New Roman 11pt/12pt, Arial 11pt, Calibri 12pt</li> -->
                            <li class="mb-1">Download the NMIMR-IRB Submission guide for further information</li>
                            <!-- <li class="mb-1">Send a single pdf file of all documents to <strong>nirb@noguchi.ug.edu.gh</strong> to facilitate the review process</li> -->
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <!-- Required Documents Alert -->
            <div class="alert alert-warning mb-4" style="height: 100%;">
                <div class="d-flex align-items-start">
                    <i class="fas fa-file-download me-3 mt-1 fs-4"></i>
                    <div>
                        <h6 class="alert-heading mb-2">Required Documents</h6>
                        <ul class="mb-0 ps-3">
                            <li>NMIMR-IRB Researchers Checklist</li>
                            <li>NMIMR-IRB Submission Guide</li>
                            <li>NMIMR-IRB Consent Form Template</li>
                            <li>Approval Letters from Collaborating Institutions</li>
                            <li>Prior IRB Approval Letter (if applicable)</li>
                        </ul>
                        <p class="mb-0 mt-2">These documents should be downloaded from the NMIMR website before proceeding.</p>
                    </div>
                </div>
            </div>
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
                            <!-- Step 1: Protocol Info -->
                            <div class="step active" data-step="1">
                                <div class="step-header d-flex align-items-center mb-2">
                                    <div class="step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                        1
                                    </div>
                                    <div class="step-title ms-3">
                                        <h6 class="fw-semibold mb-0">Protocol Information</h6>
                                        <small class="text-muted">Basic study details</small>
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
                                        <small class="text-muted">Project information & team</small>
                                    </div>
                                </div>
                                <div class="step-progress ms-4 ps-3">
                                    <div class="step-line"></div>
                                </div>
                            </div>

                            <!-- Step 3: Section B -->
                            <div class="step" data-step="3">
                                <div class="step-header d-flex align-items-center mb-2">
                                    <div class="step-number bg-light text-muted border rounded-circle d-flex align-items-center justify-content-center">
                                        3
                                    </div>
                                    <div class="step-title ms-3">
                                        <h6 class="fw-semibold mb-0 text-muted">Section B: Proposal</h6>
                                        <small class="text-muted">Abstract, methodology & ethics</small>
                                    </div>
                                </div>
                                <div class="step-progress ms-4 ps-3">
                                    <div class="step-line"></div>
                                </div>
                            </div>

                            <!-- Step 4: Section C -->
                            <div class="step" data-step="4">
                                <div class="step-header d-flex align-items-center mb-2">
                                    <div class="step-number bg-light text-muted border rounded-circle d-flex align-items-center justify-content-center">
                                        4
                                    </div>
                                    <div class="step-title ms-3">
                                        <h6 class="fw-semibold mb-0 text-muted">Section C: Signatures</h6>
                                        <small class="text-muted">Commitments & declarations</small>
                                    </div>
                                </div>
                                <div class="step-progress ms-4 ps-3">
                                    <div class="step-line"></div>
                                </div>
                            </div>

                            <!-- Step 5: Review -->
                            <div class="step" data-step="5">
                                <div class="step-header d-flex align-items-center">
                                    <div class="step-number bg-light text-muted border rounded-circle d-flex align-items-center justify-content-center">
                                        5
                                    </div>
                                    <div class="step-title ms-3">
                                        <h6 class="fw-semibold mb-0 text-muted">Review & Submit</h6>
                                        <small class="text-muted">Finalize and send to IRB</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Indicator -->
                        <div class="progress mt-4" style="height: 6px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 20%" id="stepperProgress"></div>
                        </div>
                        <div class="text-center mt-2">
                            <small class="text-muted">Step <span id="currentStep">1</span> of 5</small>
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
                                    <h5 class="mb-0"><i class="fas fa-file-signature me-2"></i>Protocol Identification</h5>
                                    <p class="text-muted mb-0 small">Step 1 of 5 - Basic study information</p>
                                </div>
                                <span class="badge bg-primary">Required</span>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="protocol_number" class="form-label fw-semibold">Protocol Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="protocol_number" name="protocol_number" value="<?php echo htmlspecialchars($draftData['protocol_number'] ?? ''); ?>" required>
                                        <small class="text-muted">Unique identifier for your study</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="version_number" class="form-label fw-semibold">Version Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="version_number" name="version_number" placeholder="e.g., 1.0" value="<?php echo htmlspecialchars($draftData['version_number'] ?? ''); ?>" required>
                                        <small class="text-muted">Document version (start with 1.0)</small>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="study_title" class="form-label fw-semibold">Title of Study <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="study_title" name="study_title" rows="2" required><?php echo htmlspecialchars($draftData['study_title'] ?? ''); ?></textarea>
                                        <small class="text-muted">Clear and concise study title</small>
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
                                    <p class="mb-0 opacity-75 small">Step 2 of 5 - Project information & team details</p>
                                </div>
                                <span class="badge bg-white text-primary">Required</span>
                            </div>
                            <div class="card-body">
                                <div class="row">
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
                                    <div class="col-12 mb-3">
                                        <label for="collaborating_institutions" class="form-label fw-semibold">Collaborating Institutions <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="collaborating_institutions" name="collaborating_institutions" rows="2" placeholder="List all collaborating institutions" required><?php echo htmlspecialchars($draftData['collaborating_institutions'] ?? ''); ?></textarea>
                                        <small class="text-muted">Attach Letter of Approval for each institution</small>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="approval_letters" class="form-label fw-semibold">Upload Approval Letters</label>
                                        <input type="file" class="form-control" id="approval_letters" name="approval_letters[]" multiple>
                                        <small class="text-muted">Upload approval letters from collaborating institutions</small>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="funding_source" class="form-label fw-semibold">Source(s) of Funding</label>
                                        <textarea class="form-control" id="funding_source" name="funding_source" rows="2" placeholder="Name and Address of funding source(s)"><?php echo htmlspecialchars($draftData['funding_source'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Type of Research <span class="text-danger">*</span></label>
                                        <div class="mt-2">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" id="type_biomedical" name="research_type" value="Biomedical" <?php echo (isset($draftData['research_type']) && $draftData['research_type'] == 'Biomedical') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="type_biomedical">Biomedical</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" id="type_social" name="research_type" value="Social/Behavioural" <?php echo (isset($draftData['research_type']) && $draftData['research_type'] == 'Social/Behavioural') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="type_social">Social/Behavioural</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" id="type_other" name="research_type" value="Other" <?php echo (isset($draftData['research_type']) && $draftData['research_type'] == 'Other') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="type_other">Others</label>
                                            </div>
                                        </div>
                                        <input type="text" class="form-control mt-2" id="research_type_other" name="research_type_other" placeholder="Specify other research type" style="display: <?php echo (isset($draftData['research_type']) && $draftData['research_type'] == 'Other') ? 'block' : 'none'; ?>;" value="<?php echo htmlspecialchars($draftData['research_type_other'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="duration" class="form-label fw-semibold">Duration of Project <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="duration" name="duration" placeholder="e.g. 12 months, 24 months" value="<?php echo htmlspecialchars($draftData['duration'] ?? ''); ?>" required>
                                        <small class="text-muted">Expected duration of the research project</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Section B - Proposal Outline -->
                    <div class="step-content" data-step="3">
                        <div class="card mb-4 border-info">
                            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>SECTION B - PROPOSAL OUTLINE</h5>
                                    <p class="mb-0 opacity-75 small">Step 3 of 5 - Abstract, methodology & ethics</p>
                                </div>
                                <span class="badge bg-white text-info">Required</span>
                            </div>
                            <div class="card-body">

                                <div class="mb-4">
                                    <label for="abstract" class="form-label fw-semibold">ABSTRACT/EXECUTIVE SUMMARY <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="abstract" name="abstract" rows="4" placeholder="Not more than 250 words" required><?php echo htmlspecialchars($draftData['abstract'] ?? ''); ?></textarea>
                                    <div class="d-flex justify-content-between mt-1">
                                        <small class="text-muted">Use clear and concise language</small>
                                        <small class="text-muted"><span id="abstract-counter">0</span>/250 words</small>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="introduction" class="form-label fw-semibold">INTRODUCTION/RATIONALE</label>
                                    <textarea class="form-control" id="introduction" name="introduction" rows="6" placeholder="Not more than 5 pages"><?php echo htmlspecialchars($draftData['introduction'] ?? ''); ?></textarea>
                                    <small class="text-muted">Use font size Times New Roman 11pt/12pt, Arial 11pt, or Calibri 12pt</small>
                                </div>

                                <div class="mb-4">
                                    <label for="literature_review" class="form-label fw-semibold">LITERATURE REVIEW</label>
                                    <textarea class="form-control" id="literature_review" name="literature_review" rows="6" placeholder="Not more than 5 pages"><?php echo htmlspecialchars($draftData['literature_review'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="aims" class="form-label fw-semibold">AIMS OR OBJECTIVES OF STUDY <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="aims" name="aims" rows="4" placeholder="State the specific aims or objectives of your study" required><?php echo htmlspecialchars($draftData['aims'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="methodology" class="form-label fw-semibold">METHODOLOGY <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="methodology" name="methodology" rows="6" placeholder="Include Inclusion and Exclusion Criteria" required><?php echo htmlspecialchars($draftData['methodology'] ?? ''); ?></textarea>
                                    <small class="text-muted">Provide detailed methodology including study design, sampling, data collection, and analysis</small>
                                </div>

                                <div class="mb-4">
                                    <label for="ethical_considerations" class="form-label fw-semibold">ETHICAL CONSIDERATIONS <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="ethical_considerations" name="ethical_considerations" rows="6" placeholder="i.e. consent procedures, confidentiality, privacy, risks and benefits, etc." required><?php echo htmlspecialchars($draftData['ethical_considerations'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="expected_outcomes" class="form-label fw-semibold">EXPECTED OUTCOME/RESULTS</label>
                                    <textarea class="form-control" id="expected_outcomes" name="expected_outcomes" rows="4" placeholder="Describe the expected outcomes and results"><?php echo htmlspecialchars($draftData['expected_outcomes'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="application_references" class="form-label fw-semibold">REFERENCES</label>
                                    <textarea class="form-control" id="application_references" name="application_references" rows="4" placeholder="List all references using appropriate citation style"><?php echo htmlspecialchars($draftData['application_references'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="work_plan" class="form-label fw-semibold">WORK PLAN</label>
                                    <textarea class="form-control" id="work_plan" name="work_plan" rows="4" placeholder="Provide a detailed work plan/timeline for the project"><?php echo htmlspecialchars($draftData['work_plan'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="budget" class="form-label fw-semibold">BUDGET AND BUDGET JUSTIFICATION</label>
                                    <textarea class="form-control" id="budget" name="budget" rows="4" placeholder="Provide detailed budget and justification"><?php echo htmlspecialchars($draftData['budget'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Required Forms</label>
                                    <div class="mb-3">
                                        <label for="consent_form" class="form-label">Consent Form (Download NMIMR-IRB Consent form template) <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control" id="consent_form" name="consent_form" accept=".pdf,.doc,.docx" required>
                                        <?php if (!empty($draftData['consent_form_filename'])): ?>
                                            <small class="text-muted">Current file: <?php echo htmlspecialchars($draftData['consent_form_filename']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-3">
                                        <label for="assent_form" class="form-label">Assent Form and Parental Consent Form (Only applicable where children of ages 12 to 17 would be recruited as research participants)</label>
                                        <input type="file" class="form-control" id="assent_form" name="assent_form" accept=".pdf,.doc,.docx">
                                        <?php if (!empty($draftData['assent_form_filename'])): ?>
                                            <small class="text-muted">Current file: <?php echo htmlspecialchars($draftData['assent_form_filename']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-3">
                                        <label for="data_instruments" class="form-label">Data Collection Instruments (i.e. Interview Guide, Questionnaire, etc.)</label>
                                        <input type="file" class="form-control" id="data_instruments" name="data_instruments" accept=".pdf,.doc,.docx">
                                        <?php if (!empty($draftData['data_instruments_filename'])): ?>
                                            <small class="text-muted">Current file: <?php echo htmlspecialchars($draftData['data_instruments_filename']); ?></small>
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

                    <!-- Step 4: Section C - Signatures -->
                    <div class="step-content" data-step="4">
                        <div class="card mb-4 border-success">
                            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>SECTION C - SIGNATURES</h5>
                                    <p class="mb-0 opacity-75 small">Step 4 of 5 - Commitments & declarations</p>
                                </div>
                                <span class="badge bg-white text-success">Required</span>
                            </div>
                            <div class="card-body">

                                <div class="alert alert-warning mb-4">
                                    <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Important Declarations</h6>
                                    <p class="mb-2">By signing below, you confirm that:</p>
                                    <ol class="mb-0 ps-3">
                                        <li class="mb-1">You will ensure that all procedures performed under the study will be conducted in accordance with all relevant policies and regulations that govern research involving human participants.</li>
                                        <li class="mb-1">You understand that if there is any change from the project as originally approved you must submit an amendment to the NMIMR-IRB for review and approval prior to its implementation.</li>
                                        <li class="mb-1">You understand that you will report all serious adverse events associated with the study within seven days verbally and fourteen days in writing.</li>
                                        <li class="mb-1">You understand that you will submit progress reports each year for review and renewal.</li>
                                        <li class="mb-0">You agree that you will submit a final report to the NMIMR-IRB at the end of the study.</li>
                                    </ol>
                                </div>

                                <div class="card mb-4 bg-light">
                                    <div class="card-body">
                                        <h6 class="fw-semibold mb-3"><i class="fas fa-user me-2"></i>Principal Investigator</h6>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="pi_name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="pi_name" name="pi_name" value="<?php echo htmlspecialchars($applicantName); ?>" readonly>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="pi_signature" class="form-label fw-semibold">Signature (Type your name) <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="pi_signature" name="pi_signature" placeholder="Type your name as signature" value="<?php echo htmlspecialchars($draftData['pi_signature'] ?? ''); ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="pi_date" class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" id="pi_date" name="pi_date" value="<?php echo htmlspecialchars($draftData['pi_date'] ?? date('Y-m-d')); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-4 bg-light">
                                    <div class="card-body">
                                        <h6 class="fw-semibold mb-3"><i class="fas fa-user-friends me-2"></i>Co-Principal Investigator</h6>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="co_pi_name" class="form-label fw-semibold">Full Name</label>
                                                <input type="text" class="form-control" id="co_pi_name" name="co_pi_name" placeholder="Full Name" value="<?php echo htmlspecialchars($draftData['co_pi_name'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="co_pi_signature" class="form-label fw-semibold">Signature (Type your name)</label>
                                                <input type="text" class="form-control" id="co_pi_signature" name="co_pi_signature" placeholder="Type your name as signature" value="<?php echo htmlspecialchars($draftData['co_pi_signature'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="co_pi_date" class="form-label fw-semibold">Date</label>
                                                <input type="date" class="form-control" id="co_pi_date" name="co_pi_date" value="<?php echo htmlspecialchars($draftData['co_pi_date'] ?? date('Y-m-d')); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 5: Review & Submit -->
                    <div class="step-content" data-step="5">
                        <div class="card mb-4 border-warning">
                            <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>REVIEW & SUBMIT</h5>
                                    <p class="mb-0 opacity-75 small">Step 5 of 5 - Finalize and send to IRB</p>
                                </div>
                                <span class="badge bg-white text-warning">Final Step</span>
                            </div>
                            <div class="card-body">

                                <!-- Application Summary -->
                                <div class="alert alert-info mb-4">
                                    <h6 class="alert-heading"><i class="fas fa-file-alt me-2"></i>Application Summary</h6>
                                    <p class="mb-3">Review all the information you have provided before final submission.</p>

                                    <div class="row">
                                        <!-- Protocol Information -->
                                        <div class="col-md-6 mb-3">
                                            <h6 class="fw-semibold border-bottom pb-2 mb-2"><i class="fas fa-clipboard-list me-2"></i>Protocol Information</h6>
                                            <p class="mb-1"><strong>Protocol Number:</strong> <span id="summary_protocol_number"><?php echo htmlspecialchars($draftData['protocol_number'] ?? 'Not provided'); ?></span></p>
                                            <p class="mb-1"><strong>Version:</strong> <span id="summary_version_number"><?php echo htmlspecialchars($draftData['version_number'] ?? 'Not provided'); ?></span></p>
                                            <p class="mb-1"><strong>Study Title:</strong> <span id="summary_study_title"><?php echo htmlspecialchars($draftData['study_title'] ?? 'Not provided'); ?></span></p>
                                        </div>

                                        <!-- Research Details -->
                                        <div class="col-md-6 mb-3">
                                            <h6 class="fw-semibold border-bottom pb-2 mb-2"><i class="fas fa-flask me-2"></i>Research Details</h6>
                                            <p class="mb-1"><strong>Research Type:</strong> <span id="summary_research_type"><?php echo htmlspecialchars($draftData['research_type'] ?? 'Not provided'); ?></span></p>
                                            <p class="mb-1"><strong>Duration:</strong> <span id="summary_duration"><?php echo htmlspecialchars($draftData['duration'] ?? 'Not provided'); ?></span></p>
                                            <p class="mb-1"><strong>Funding Source:</strong> <span id="summary_funding_source"><?php echo htmlspecialchars($draftData['funding_source'] ?? 'Not provided'); ?></span></p>
                                        </div>

                                        <!-- Principal Investigator -->
                                        <div class="col-md-6 mb-3">
                                            <h6 class="fw-semibold border-bottom pb-2 mb-2"><i class="fas fa-user me-2"></i>Principal Investigator</h6>
                                            <p class="mb-1"><strong>Name:</strong> <span id="summary_pi_name"><?php echo htmlspecialchars($draftData['pi_name'] ?? 'Not provided'); ?></span></p>
                                            <p class="mb-1"><strong>Institution:</strong> <span id="summary_pi_institution"><?php echo htmlspecialchars($draftData['pi_institution'] ?? 'Not provided'); ?></span></p>
                                            <p class="mb-1"><strong>Address:</strong> <span id="summary_pi_address"><?php echo htmlspecialchars($draftData['pi_address'] ?? 'Not provided'); ?></span></p>
                                            <p class="mb-1"><strong>Phone:</strong> <span id="summary_pi_phone"><?php echo htmlspecialchars($draftData['pi_phone_number'] ?? 'Not provided'); ?></span></p>
                                            <p class="mb-1"><strong>Email:</strong> <span id="summary_pi_email"><?php echo htmlspecialchars($draftData['pi_email'] ?? 'Not provided'); ?></span></p>
                                        </div>

                                        <!-- Co-Principal Investigator -->
                                        <div class="col-md-6 mb-3">
                                            <h6 class="fw-semibold border-bottom pb-2 mb-2"><i class="fas fa-user-friends me-2"></i>Co-Principal Investigator</h6>
                                            <p class="mb-1"><strong>Name:</strong> <span id="summary_co_pi_name"><?php echo htmlspecialchars($draftData['co_pi_name'] ?? 'Not provided'); ?></span></p>
                                            <?php if (!empty($draftData['co_pi_qualification'])): ?>
                                                <p class="mb-1"><strong>Qualification:</strong> <span id="summary_co_pi_qualification"><?php echo htmlspecialchars($draftData['co_pi_qualification'] ?? ''); ?></span></p>
                                            <?php endif; ?>
                                            <?php if (!empty($draftData['co_pi_department'])): ?>
                                                <p class="mb-1"><strong>Department:</strong> <span id="summary_co_pi_department"><?php echo htmlspecialchars($draftData['co_pi_department'] ?? ''); ?></span></p>
                                            <?php endif; ?>
                                            <?php if (!empty($draftData['co_pi_address'])): ?>
                                                <p class="mb-1"><strong>Address:</strong> <span id="summary_co_pi_address"><?php echo htmlspecialchars($draftData['co_pi_address'] ?? ''); ?></span></p>
                                            <?php endif; ?>
                                            <?php if (!empty($draftData['co_pi_phone_number'])): ?>
                                                <p class="mb-1"><strong>Phone:</strong> <span id="summary_co_pi_phone"><?php echo htmlspecialchars($draftData['co_pi_phone_number'] ?? ''); ?></span></p>
                                            <?php endif; ?>
                                            <?php if (!empty($draftData['co_pi_email'])): ?>
                                                <p class="mb-1"><strong>Email:</strong> <span id="summary_co_pi_email"><?php echo htmlspecialchars($draftData['co_pi_email'] ?? ''); ?></span></p>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Collaborating Institutions -->
                                        <div class="col-12 mb-3">
                                            <h6 class="fw-semibold border-bottom pb-2 mb-2"><i class="fas fa-building me-2"></i>Collaborating Institutions</h6>
                                            <p id="summary_collaborating_institutions"><?php echo nl2br(htmlspecialchars($draftData['collaborating_institutions'] ?? 'None specified')); ?></p>
                                        </div>

                                        <!-- Abstract -->
                                        <div class="col-12 mb-3">
                                            <h6 class="fw-semibold border-bottom pb-2 mb-2"><i class="fas fa-paragraph me-2"></i>Abstract</h6>
                                            <p id="summary_abstract" class="text-muted"><?php echo nl2br(htmlspecialchars($draftData['abstract'] ?? 'Not provided')); ?></p>
                                        </div>

                                        <!-- Signatures -->
                                        <div class="col-md-6 mb-3">
                                            <h6 class="fw-semibold border-bottom pb-2 mb-2"><i class="fas fa-edit me-2"></i>PI Signature</h6>
                                            <p class="mb-1"><strong>Name:</strong> <span id="summary_pi_sig_name"><?php echo htmlspecialchars($draftData['pi_name'] ?? 'Not provided'); ?></span></p>
                                            <p class="mb-1"><strong>Signature:</strong> <span id="summary_pi_signature"><?php echo htmlspecialchars($draftData['pi_signature'] ?? 'Not provided'); ?></span></p>
                                            <p class="mb-1"><strong>Date:</strong> <span id="summary_pi_date"><?php echo htmlspecialchars($draftData['pi_date'] ?? 'Not provided'); ?></span></p>
                                        </div>

                                        <?php if (!empty($draftData['co_pi_signature'])): ?>
                                            <div class="col-md-6 mb-3">
                                                <h6 class="fw-semibold border-bottom pb-2 mb-2"><i class="fas fa-edit me-2"></i>Co-PI Signature</h6>
                                                <p class="mb-1"><strong>Name:</strong> <span id="summary_co_pi_sig_name"><?php echo htmlspecialchars($draftData['co_pi_name'] ?? ''); ?></span></p>
                                                <p class="mb-1"><strong>Signature:</strong> <span id="summary_co_pi_signature"><?php echo htmlspecialchars($draftData['co_pi_signature'] ?? ''); ?></span></p>
                                                <p class="mb-1"><strong>Date:</strong> <span id="summary_co_pi_date"><?php echo htmlspecialchars($draftData['co_pi_date'] ?? ''); ?></span></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="alert alert-info mb-4">
                                    <h6 class="alert-heading"><i class="fas fa-paper-plane me-2"></i>Submission Instructions</h6>
                                    <p class="mb-0">Send a single PDF file of all documents to <strong>nirb@noguchi.ug.edu.gh</strong> to facilitate the review process.</p>
                                    <p class="mb-0 mt-2"><small>The soft copy should be signed and dated.</small></p>
                                </div>

                                <div class="mb-4">
                                    <label for="final_pdf" class="form-label fw-semibold">Upload Final PDF <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="final_pdf" name="final_pdf" accept=".pdf" required>
                                    <small class="text-muted">Upload the final signed PDF document</small>
                                </div>

                                <div class="mb-4">
                                    <label for="submission_notes" class="form-label fw-semibold">Additional Notes/Comments</label>
                                    <textarea class="form-control" id="submission_notes" name="submission_notes" rows="3" placeholder="Any additional information for the IRB"><?php echo htmlspecialchars($draftData['submission_notes'] ?? ''); ?></textarea>
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
            const totalSteps = steps.length;
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

            // Word counter for abstract
            const abstractTextarea = document.getElementById('abstract');
            const abstractCounter = document.getElementById('abstract-counter');

            if (abstractTextarea && abstractCounter) {
                abstractTextarea.addEventListener('input', function() {
                    const wordCount = countWords(this.value);
                    abstractCounter.textContent = wordCount;

                    if (wordCount > 250) {
                        abstractCounter.classList.add('text-danger');
                        abstractCounter.classList.remove('text-muted');
                    } else {
                        abstractCounter.classList.remove('text-danger');
                        abstractCounter.classList.add('text-muted');
                    }
                });
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
                });
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
                const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
                if (progressBar) {
                    progressBar.style.width = `${progress}%`;
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
                // Simple validation for demonstration
                // In a real application, you would implement more comprehensive validation

                if (stepIndex === 1) { // Protocol Info validation
                    const protocolNumber = document.getElementById('protocol_number');
                    const versionNumber = document.getElementById('version_number');
                    const studyTitle = document.getElementById('study_title');

                    if (protocolNumber && !protocolNumber.value.trim()) {
                        alert('Please enter the protocol number');
                        return false;
                    }

                    if (versionNumber && !versionNumber.value.trim()) {
                        alert('Please enter the version number');
                        return false;
                    }

                    if (studyTitle && !studyTitle.value.trim()) {
                        alert('Please enter the title of the study');
                        return false;
                    }
                }

                if (stepIndex === 2) { // Section A validation
                    const piDetails = document.getElementById('pi_details');
                    const collaboratingInstitutions = document.getElementById('collaborating_institutions');
                    const duration = document.getElementById('duration');

                    if (piDetails && !piDetails.value.trim()) {
                        alert('Please enter Principal Investigator details');
                        return false;
                    }

                    if (collaboratingInstitutions && !collaboratingInstitutions.value.trim()) {
                        alert('Please enter collaborating institutions');
                        return false;
                    }

                    if (duration && !duration.value.trim()) {
                        alert('Please enter the project duration');
                        return false;
                    }
                }

                if (stepIndex === 3) { // Section B validation
                    const abstract = document.getElementById('abstract');
                    const aims = document.getElementById('aims');
                    const methodology = document.getElementById('methodology');
                    const ethicalConsiderations = document.getElementById('ethical_considerations');

                    if (abstract && !abstract.value.trim()) {
                        alert('Please enter an abstract/executive summary');
                        return false;
                    }

                    if (abstract && countWords(abstract.value) > 250) {
                        alert('Abstract should not exceed 250 words');
                        return false;
                    }

                    if (aims && !aims.value.trim()) {
                        alert('Please enter the aims or objectives of the study');
                        return false;
                    }

                    if (methodology && !methodology.value.trim()) {
                        alert('Please enter the methodology');
                        return false;
                    }

                    if (ethicalConsiderations && !ethicalConsiderations.value.trim()) {
                        alert('Please enter ethical considerations');
                        return false;
                    }
                }

                if (stepIndex === 4) { // Section C validation
                    const piName = document.getElementById('pi_name');
                    const piSignature = document.getElementById('pi_signature');

                    if ((piName && !piName.value.trim()) || (piSignature && !piSignature.value.trim())) {
                        alert('Principal Investigator name and signature are required');
                        return false;
                    }
                }

                if (stepIndex === 5) { // Review validation
                    const finalPdf = document.getElementById('final_pdf');
                    const checkComplete = document.getElementById('check_complete');

                    if (finalPdf && finalPdf.files.length === 0) {
                        alert('Please upload the final PDF document');
                        return false;
                    }

                    if (checkComplete && !checkComplete.checked) {
                        alert('Please confirm that all sections of the form have been completed');
                        return false;
                    }
                }

                return true;
            }

            function countWords(text) {
                if (!text) return 0;
                return text.trim().split(/\s+/).filter(word => word.length > 0).length;
            }

            // Show loading spinner on button
            function showLoading(buttonId) {
                const btn = document.getElementById(buttonId);
                if (btn) {
                    const spinnerContainer = btn.querySelector('.spinner-container');
                    // Get all button-text elements
                    const buttonTexts = btn.querySelectorAll('.button-text');
                    if (spinnerContainer) {
                        spinnerContainer.style.display = 'inline-flex';
                        // Show "Saving..." text inside spinner
                        const savingText = spinnerContainer.querySelector('.button-text');
                        if (savingText) {
                            savingText.style.display = 'inline';
                        }
                    }
                    // Hide all other button-text elements (the "Next" text)
                    buttonTexts.forEach(text => {
                        if (!spinnerContainer || !spinnerContainer.contains(text)) {
                            text.style.display = 'none';
                        }
                    });
                    btn.disabled = true;
                }
            }

            // Hide loading spinner on button
            function hideLoading(buttonId, originalText) {
                const btn = document.getElementById(buttonId);
                if (btn) {
                    const spinnerContainer = btn.querySelector('.spinner-container');
                    // Get all button-text elements
                    const buttonTexts = btn.querySelectorAll('.button-text');
                    if (spinnerContainer) {
                        spinnerContainer.style.display = 'none';
                    }
                    // Restore the "Next" text (the one outside spinner-container)
                    buttonTexts.forEach(text => {
                        if (!spinnerContainer || !spinnerContainer.contains(text)) {
                            text.style.display = 'inline';
                            if (originalText) {
                                text.innerHTML = originalText;
                            }
                        }
                    });
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

                    console.log("Hide confirmation modal. Proceeding with submission...");

                    showLoadingOverlay();
                    // Save draft first, then submit
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
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {

                                // Hide modal
                                const modal = bootstrap.Modal.getInstance(document.getElementById('submitConfirmationModal'));
                                if (modal) modal.hide();

                                hideLoadingOverlay();

                                window.location.href = '/applicant-dashboard';

                                // alert(data.message);
                                if (data.redirect) {
                                    window.location.href = data.redirect;
                                }
                            } else {

                                hideLoadingOverlay();

                                alert(data.message || 'Submission failed. Please try again.');
                            }
                        })
                        .catch(error => {
                            hideLoadingOverlay();
                            console.error('Submit error:', error);
                            alert('An error occurred. Please try again.');
                        })
                        .finally(() => {
                            hideLoadingOverlay();
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