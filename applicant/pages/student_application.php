<?php

// Use consistent session name across entire application
// defined('CSRF_SESSION_NAME') || define('CSRF_SESSION_NAME', 'ug_irb_session');
// session_name(CSRF_SESSION_NAME);

// Start session if not already started (for direct access)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if applicant is logged in
// if (!is_applicant_logged_in()) {
//     header('Location: /login');
//     exit;
// }

error_log("Accessing student application page for user ID: " . ($_SESSION['user_id'] ?? 'unknown'));

$userId = $_SESSION['user_id'] ?? 0;

error_log("Checking if user can submit new application for user ID: $userId");

// Fetch student data from database
$studentData = getApplicantProfile($userId);
$studentName = '';
$studentId = '';
$studentPhone = '';
$studentEmail = '';
$institutionName = '';

error_log("Fetched student data: " . print_r($studentData, true));

if ($studentData) {
    // Construct full name (Surname First format)
    $studentName = trim(($studentData['last_name'] ?? '') . ', ' . ($studentData['first_name'] ?? '') . ' ' . ($studentData['middle_name'] ?? ''));
    $studentName = preg_replace('/^,\s*/', '', $studentName); // Remove leading comma if last_name is empty
    $studentName = preg_replace('/\s+/', ' ', $studentName); // Remove extra spaces
    $studentPhone = $studentData['phone_number'] ?? '';
    $studentEmail = $studentData['email'] ?? '';
    $studentId = $studentData['student_id'] ?? '';

    // Get institution name from session or database
    $institutionId = $_SESSION['institution_id'] ?? ($studentData['institution_id'] ?? null);
    if ($institutionId) {
        $institution = getInstitutionById($institutionId);
        $institutionName = $institution['institution_name'] ?? '';
    }
}

error_log("Student Name: $studentName, Phone: $studentPhone, Email: $studentEmail, Institution: $institutionName");

// Get application type
$type = $_GET['type'] ?? 'student';

// Check if loading existing application
$existingApplicationId = $_GET['application_id'] ?? 0;
$existingApplication = null;
$currentStep = 1;

error_log("Checking for existing application with ID: $existingApplicationId for user ID: $userId");

if ($existingApplicationId > 0) {
    $db = new Database();
    $conn = $db->connect();
    if ($conn) {
        try {
            $stmt = $conn->prepare("SELECT * FROM student_applications WHERE id = ? AND applicant_id = ?");
            $stmt->execute([$existingApplicationId, $userId]);
            $existingApplication = $stmt->fetch(PDO::FETCH_ASSOC);

            error_log("Existing application data: " . print_r($existingApplication, true));
            if ($existingApplication) {
                $currentStep = $existingApplication['current_step'] ?? 1;
                $type = $existingApplication['application_type'] ?? $type;
            }
        } catch (PDOException $e) {
            error_log("Error loading existing application: " . $e->getMessage());
        }
    }
}

// Set title based on type
$applicationTypes = [
    'student' => [
        'title' => 'Initial Submission Form A - Student Research',
        'icon' => 'fa-graduation-cap',
        'description' => 'Complete all sections for ethics review consideration'
    ],
    'nmimr' => [
        'title' => 'Initial Submission Form A - NMIMR Researchers',
        'icon' => 'fa-flask',
        'description' => 'For NMIMR staff and researchers'
    ],
    'non_nmimr' => [
        'title' => 'Initial Submission Form A - Non-NMIMR Researchers',
        'icon' => 'fa-university',
        'description' => 'For external researchers'
    ]
];

$currentType = $applicationTypes[$type] ?? $applicationTypes['student'];

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

<div class="add-new-protocol container-fluid mt-4 mb-4 p-4">
    <!-- Header -->
    <div class="welcome-header text-white p-4 rounded mb-4 position-relative overflow-hidden"
        style="background:linear-gradient(135deg, #065c27 0%, #1b9b55 100%);">
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
                    <!-- <li class="mb-1">Use clear font size: Times New Roman 11pt/12pt, Arial 11pt, Calibri 12pt</li> -->
                    <li class="mb-1">Download the NMIMR-IRB Submission guide for further information</li>
                    <!-- <li>Send a single PDF file of all documents to nirb@noguchi.ug.edu.gh</li> -->
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
                                    <small class="text-muted">Student & supervisor info</small>
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
                                    <small class="text-muted">Research outline & methods</small>
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
                                    <small class="text-muted">Declarations & approvals</small>
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
                                    <small class="text-muted">Final verification</small>
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
                        <button class="btn btn-primary w-100" id="nextStepBtn">
                            Next <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Content - Form -->
        <div class="col-lg-9">
            <form id="studentProtocolForm" enctype="multipart/form-data">
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
                                    <input type="text" class="form-control" id="protocol_number" name="protocol_number" value="<?php echo htmlspecialchars($existingApplication['protocol_number'] ?? ''); ?>" required>
                                    <small class="text-muted">Unique identifier for your study</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="version_number" class="form-label fw-semibold">Version Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="version_number" name="version_number" placeholder="e.g., 1.0" value="<?php echo htmlspecialchars($existingApplication['version_number'] ?? ''); ?>" required>
                                    <small class="text-muted">Document version (start with 1.0)</small>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="study_title" class="form-label fw-semibold">Title of Study <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="study_title" name="study_title" rows="2" required><?php echo htmlspecialchars($existingApplication['study_title'] ?? ''); ?></textarea>
                                    <small class="text-muted">Clear and concise study title</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Section A - Background Information -->
                <div class="step-content" data-step="2">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><i class="fas fa-address-card me-2"></i>SECTION A - BACKGROUND INFORMATION</h5>
                                <p class="mb-0 opacity-75 small">Step 2 of 5 - Student & supervisor details</p>
                            </div>
                            <span class="badge bg-white text-primary">Required</span>
                        </div>
                        <div class="card-body">

                            <!-- Student Information -->
                            <div class="student-info mb-4 p-3 border rounded">
                                <h6 class="fw-semibold mb-3"><i class="fas fa-user-graduate me-2"></i>Student Investigator</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="student_name" class="form-label fw-semibold">Full Name (Surname First) <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="student_name" name="student_name" value="<?php echo htmlspecialchars($studentName); ?>" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="student_institution" class="form-label fw-semibold">Institution <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="student_institution" name="student_institution" value="<?php echo htmlspecialchars($institutionName); ?>" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="student_department" class="form-label fw-semibold">Faculty/Department/School <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="student_department" name="student_department" value="<?php echo htmlspecialchars($existingApplication['student_department'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="student_address" class="form-label fw-semibold">Address</label>
                                        <input type="text" class="form-control" id="student_address" name="student_address" value="<?php echo htmlspecialchars($existingApplication['student_address'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="student_number" class="form-label fw-semibold">Student Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="student_number" name="student_number" value="<?php echo htmlspecialchars($studentId); ?>" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="student_phone" class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="student_phone" name="student_phone" value="<?php echo htmlspecialchars($studentPhone); ?>" readonly>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="student_email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="student_email" name="student_email" value="<?php echo htmlspecialchars($studentEmail); ?>" readonly>
                                        <small class="text-muted">Please provide one email address</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Supervisors -->
                            <div class="supervisors-info mb-4">
                                <h6 class="fw-semibold mb-3"><i class="fas fa-chalkboard-teacher me-2"></i>Supervisors</h6>

                                <!-- Supervisor 1 -->
                                <div class="supervisor-entry mb-4 p-3 border rounded">
                                    <h6 class="fw-semibold mb-3">Supervisor 1</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="supervisor1_name" class="form-label fw-semibold">Name (Surname First, Title, Qualifications) <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="supervisor1_name" name="supervisor1_name" value="<?php echo htmlspecialchars($existingApplication['supervisor1_name'] ?? ''); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="supervisor1_institution" class="form-label fw-semibold">Institution/Faculty/Department/School <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="supervisor1_institution" name="supervisor1_institution" value="<?php echo htmlspecialchars($existingApplication['supervisor1_institution'] ?? ''); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="supervisor1_address" class="form-label fw-semibold">Address</label>
                                            <input type="text" class="form-control" id="supervisor1_address" name="supervisor1_address" value="<?php echo htmlspecialchars($existingApplication['supervisor1_address'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="supervisor1_phone" class="form-label fw-semibold">Phone Number</label>
                                            <input type="tel" class="form-control" id="supervisor1_phone" name="supervisor1_phone" value="<?php echo htmlspecialchars($existingApplication['supervisor1_phone'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label for="supervisor1_email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="supervisor1_email" name="supervisor1_email" value="<?php echo htmlspecialchars($existingApplication['supervisor1_email'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Supervisor 2 (Optional) -->
                                <div class="supervisor-entry mb-4 p-3 border rounded" id="supervisor2-section" style="display: none;">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="fw-semibold mb-0">Supervisor 2</h6>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSupervisor(2)">
                                            <i class="fas fa-times me-1"></i>Remove
                                        </button>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="supervisor2_name" class="form-label">Name (Surname First, Title, Qualifications)</label>
                                            <input type="text" class="form-control" id="supervisor2_name" name="supervisor2_name" value="<?php echo htmlspecialchars($existingApplication['supervisor2_name'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="supervisor2_institution" class="form-label">Institution/Faculty/Department/School</label>
                                            <input type="text" class="form-control" id="supervisor2_institution" name="supervisor2_institution" value="<?php echo htmlspecialchars($existingApplication['supervisor2_institution'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="supervisor2_address" class="form-label">Address</label>
                                            <input type="text" class="form-control" id="supervisor2_address" name="supervisor2_address" value="<?php echo htmlspecialchars($existingApplication['supervisor2_address'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="supervisor2_phone" class="form-label">Phone Number</label>
                                            <input type="tel" class="form-control" id="supervisor2_phone" name="supervisor2_phone" value="<?php echo htmlspecialchars($existingApplication['supervisor2_phone'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label for="supervisor2_email" class="form-label">Email Address</label>
                                            <input type="email" class="form-control" id="supervisor2_email" name="supervisor2_email" value="<?php echo htmlspecialchars($existingApplication['supervisor2_email'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>

                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addSupervisor()">
                                    <i class="fas fa-plus me-1"></i>Add Another Supervisor
                                </button>
                                <small class="text-muted d-block mt-1">Add on if you have more than two supervisors</small>
                            </div>

                            <!-- Proposed Study Information -->
                            <div class="study-info mb-4 p-3 border rounded">
                                <h6 class="fw-semibold mb-3"><i class="fas fa-clipboard-list me-2"></i>Proposed Study Information</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Type of Research/Study <span class="text-danger">*</span></label>
                                        <div class="mt-2">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="research_type" id="type_biomedical" value="Biomedical" required <?php echo (isset($existingApplication['research_type']) && $existingApplication['research_type'] == 'Biomedical') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="type_biomedical">Biomedical</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="research_type" id="type_social" value="Social/Behavioural" <?php echo (isset($existingApplication['research_type']) && $existingApplication['research_type'] == 'Social/Behavioural') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="type_social">Social/Behavioural</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="research_type" id="type_other" value="Other" <?php echo (isset($existingApplication['research_type']) && $existingApplication['research_type'] == 'Other') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="type_other">Others</label>
                                            </div>
                                        </div>
                                        <input type="text" class="form-control mt-2" id="research_type_other" name="research_type_other" placeholder="Please specify" style="display: none;" value="<?php echo htmlspecialchars($existingApplication['research_type_other'] ?? ''); ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Student Status <span class="text-danger">*</span></label>
                                        <div class="mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="student_status" id="status_undergrad" value="Undergraduate" required <?php echo (isset($existingApplication['student_status']) && $existingApplication['student_status'] == 'Undergraduate') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="status_undergrad">Undergraduate</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="student_status" id="status_masters" value="Masters" <?php echo (isset($existingApplication['student_status']) && $existingApplication['student_status'] == 'Masters') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="status_masters">Masters</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="student_status" id="status_phd" value="PhD" <?php echo (isset($existingApplication['student_status']) && $existingApplication['student_status'] == 'PhD') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="status_phd">PhD</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="study_duration_years" class="form-label fw-semibold">Duration of Research/Study <span class="text-danger">*</span></label>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <input type="number" class="form-control" id="study_duration_years" name="study_duration_years" min="0.5" max="10" step="0.5" placeholder="Years" value="<?php echo htmlspecialchars($existingApplication['study_duration_years'] ?? ''); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted">Number of years</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Study Dates <span class="text-danger">*</span></label>
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <input type="date" class="form-control" id="study_start_date" name="study_start_date" value="<?php echo htmlspecialchars($existingApplication['study_start_date'] ?? ''); ?>" required>
                                                <small class="text-muted">Start Date</small>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <input type="date" class="form-control" id="study_end_date" name="study_end_date" value="<?php echo htmlspecialchars($existingApplication['study_end_date'] ?? ''); ?>" required>
                                                <small class="text-muted">End Date</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label for="funding_sources" class="form-label fw-semibold">Source(s) of Funding</label>
                                        <textarea class="form-control" id="funding_sources" name="funding_sources" rows="2" placeholder="Name, Address and Email"><?php echo htmlspecialchars($existingApplication['funding_sources'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label for="approval_letter" class="form-label fw-semibold">Departmental Thesis Approval Letter and Introductory Letter from Head of Department <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control" id="approval_letter" name="approval_letter" accept=".pdf,.doc,.docx" required>
                                        <?php if (!empty($existingApplication['approval_letter'])): ?>
                                            <small class="text-success d-block mt-1">
                                                <i class="bi bi-file-earmark"></i> Current file: <?php echo htmlspecialchars(basename($existingApplication['approval_letter'])); ?>
                                            </small>
                                        <?php endif; ?>
                                        <small class="text-muted">Attach Letter of Approval</small>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label for="prior_irb_review" class="form-label fw-semibold">Prior IRB Review</label>
                                        <textarea class="form-control" id="prior_irb_review" name="prior_irb_review" rows="2" placeholder="Name any other IRB this proposal has been submitted to and attach approval letter if applicable. In case of rejection, state reasons"><?php echo htmlspecialchars($existingApplication['prior_irb_review'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label for="collaborating_institutions" class="form-label fw-semibold">Collaborating Institutions</label>
                                        <textarea class="form-control" id="collaborating_institutions" name="collaborating_institutions" rows="2" placeholder="List collaborating institutions"><?php echo htmlspecialchars($existingApplication['collaborating_institutions'] ?? ''); ?></textarea>
                                        <input type="file" class="form-control mt-2" id="collaboration_letter" name="collaboration_letter" accept=".pdf,.doc,.docx">
                                        <?php if (!empty($existingApplication['collaboration_letter'])): ?>
                                            <small class="text-success d-block mt-1">
                                                <i class="bi bi-file-earmark"></i> Current file: <?php echo htmlspecialchars(basename($existingApplication['collaboration_letter'])); ?>
                                            </small>
                                        <?php endif; ?>
                                        <small class="text-muted">Attach Letter of Approval if applicable</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Section B - Research Proposal Outline -->
                <div class="step-content" data-step="3">
                    <div class="card mb-4 border-info">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>SECTION B - RESEARCH PROPOSAL OUTLINE</h5>
                                <p class="mb-0 opacity-75 small">Step 3 of 5 - Research methodology & details</p>
                            </div>
                            <span class="badge bg-white text-info">Required</span>
                        </div>
                        <div class="card-body">

                            <!-- Abstract/Executive Summary -->
                            <div class="mb-4">
                                <label for="abstract" class="form-label fw-semibold">ABSTRACT/EXECUTIVE SUMMARY <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="abstract" name="abstract" rows="4" maxlength="250" required><?php echo htmlspecialchars($existingApplication['abstract'] ?? ''); ?></textarea>
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-muted">Not more than 250 words</small>
                                    <small class="text-muted"><span id="abstract-count">0</span>/250 words</small>
                                </div>
                            </div>

                            <!-- Background/Rationale -->
                            <div class="mb-4">
                                <label for="background" class="form-label fw-semibold">BACKGROUND OR RATIONALE OF STUDY <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="background" name="background" rows="6" maxlength="1500" required><?php echo htmlspecialchars($existingApplication['background'] ?? ''); ?></textarea>
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-muted">Include aims and objectives, literature review; not more than 1500 words</small>
                                    <small class="text-muted"><span id="background-count">0</span>/1500 words</small>
                                </div>
                            </div>

                            <!-- Methods -->
                            <div class="mb-4">
                                <label for="methods" class="form-label fw-semibold">METHODS <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="methods" name="methods" rows="6" required><?php echo htmlspecialchars($existingApplication['methods'] ?? ''); ?></textarea>
                                <small class="text-muted">Include study site, population, study design, sampling, data collection, data analysis, inclusion and exclusion criteria</small>
                            </div>

                            <!-- Ethical Considerations -->
                            <div class="mb-4">
                                <label for="ethical_considerations" class="form-label fw-semibold">ETHICAL CONSIDERATIONS <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="ethical_considerations" name="ethical_considerations" rows="6" required><?php echo htmlspecialchars($existingApplication['ethical_considerations'] ?? ''); ?></textarea>
                                <small class="text-muted">Provide description of likely ethical issues and how they would be resolved (consent procedures, confidentiality, privacy, risks and benefits, etc.)</small>
                            </div>

                            <!-- Expected Outcome/Results -->
                            <div class="mb-4">
                                <label for="expected_outcome" class="form-label fw-semibold">EXPECTED OUTCOME/RESULTS <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="expected_outcome" name="expected_outcome" rows="4" required><?php echo htmlspecialchars($existingApplication['expected_outcome'] ?? ''); ?></textarea>
                            </div>

                            <!-- Key References -->
                            <div class="mb-4">
                                <label for="key_references" class="form-label fw-semibold">KEY REFERENCES</label>
                                <textarea class="form-control" id="key_references" name="key_references" rows="4"><?php echo htmlspecialchars($existingApplication['key_references'] ?? ''); ?></textarea>
                            </div>

                            <!-- Work Plan -->
                            <div class="mb-4">
                                <label for="work_plan" class="form-label fw-semibold">WORK PLAN</label>
                                <textarea class="form-control" id="work_plan" name="work_plan" rows="4"><?php echo htmlspecialchars($existingApplication['work_plan'] ?? ''); ?></textarea>
                            </div>

                            <!-- Budget and Justification -->
                            <div class="mb-4">
                                <label for="budget" class="form-label fw-semibold">BUDGET AND BUDGET JUSTIFICATION</label>
                                <textarea class="form-control" id="budget" name="budget" rows="4"><?php echo htmlspecialchars($existingApplication['budget'] ?? ''); ?></textarea>
                            </div>

                            <!-- Attachments -->
                            <div class="attachments-section p-3 border rounded">
                                <h6 class="fw-semibold mb-3"><i class="fas fa-paperclip me-2"></i>Required Attachments</h6>

                                <!-- Consent Form -->
                                <div class="mb-3">
                                    <label for="consent_form" class="form-label fw-semibold">CONSENT FORM <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="consent_form" name="consent_form" accept=".pdf,.doc,.docx" required>
                                    <?php if (!empty($existingApplication['consent_form'])): ?>
                                        <small class="text-success d-block mt-1">
                                            <i class="bi bi-file-earmark"></i> Current file: <?php echo htmlspecialchars(basename($existingApplication['consent_form'])); ?>
                                        </small>
                                    <?php endif; ?>
                                    <small class="text-muted">Download the NMIMR-IRB Consent form Template for guidance</small>
                                </div>

                                <!-- Assent and Parental Consent Forms -->
                                <div class="mb-3">
                                    <label for="assent_form" class="form-label fw-semibold">ASSENT FORM AND PARENTAL CONSENT FORM</label>
                                    <input type="file" class="form-control" id="assent_form" name="assent_form" accept=".pdf,.doc,.docx">
                                    <?php if (!empty($existingApplication['assent_form'])): ?>
                                        <small class="text-success d-block mt-1">
                                            <i class="bi bi-file-earmark"></i> Current file: <?php echo htmlspecialchars(basename($existingApplication['assent_form'])); ?>
                                        </small>
                                    <?php endif; ?>
                                    <small class="text-muted">Only applicable where children of ages 12 to 17 would be recruited as research participants</small>
                                </div>

                                <!-- Data Collection Instruments -->
                                <div class="mb-3">
                                    <label for="data_instruments" class="form-label fw-semibold">DATA COLLECTION INSTRUMENTS <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="data_instruments" name="data_instruments" accept=".pdf,.doc,.docx,.xls,.xlsx" multiple required>
                                    <?php if (!empty($existingApplication['data_instruments'])): ?>
                                        <small class="text-success d-block mt-1">
                                            <i class="bi bi-file-earmark"></i> Current file: <?php echo htmlspecialchars(basename($existingApplication['data_instruments'])); ?>
                                        </small>
                                    <?php endif; ?>
                                    <small class="text-muted">Interview Guide, Questionnaire, etc.</small>
                                </div>

                                <!-- Additional Documents -->
                                <div class="mb-3">
                                    <label for="additional_documents" class="form-label fw-semibold">Additional Supporting Documents</label>
                                    <input type="file" class="form-control" id="additional_documents" name="additional_documents[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                    <?php if (!empty($existingApplication['additional_documents'])): ?>
                                        <small class="text-success d-block mt-1">
                                            <i class="bi bi-file-earmark"></i> Current file: <?php echo htmlspecialchars(basename($existingApplication['additional_documents'])); ?>
                                        </small>
                                    <?php endif; ?>
                                    <small class="text-muted">Any other supporting documents (maximum 10 files, 10MB each)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Section C - Signatures -->
                <div class="step-content" data-step="4">
                    <div class="card mb-4 border-success">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><i class="fas fa-signature me-2"></i>SECTION C - SIGNATURES</h5>
                                <p class="mb-0 opacity-75 small">Step 4 of 5 - Declarations & approvals</p>
                            </div>
                            <span class="badge bg-white text-success">Required</span>
                        </div>
                        <div class="card-body">

                            <!-- Student Declaration -->
                            <div class="declaration-card mb-4 p-4 border rounded bg-light">
                                <h6 class="fw-bold mb-3">I. STUDENT INVESTIGATOR DECLARATION</h6>
                                <p class="mb-3">As the <strong>Student Investigator</strong> on this project, my signature confirms that:</p>

                                <div class="mb-3">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="declaration1" name="declarations[]" value="1" required>
                                        <label class="form-check-label" for="declaration1">
                                            I will ensure that all procedures performed under the study will be conducted in accordance with all relevant policies and regulations that govern research involving human participants.
                                        </label>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="declaration2" name="declarations[]" value="2" required>
                                        <label class="form-check-label" for="declaration2">
                                            I understand that if there is any change from the project as originally approved I must submit an amendment to the NMIMR-IRB for review and approval prior to its implementation. Where I fail to do so, the amended aspect of the study is invalid.
                                        </label>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="declaration3" name="declarations[]" value="3" required>
                                        <label class="form-check-label" for="declaration3">
                                            I understand that I will report all serious adverse events associated with the study within seven days verbally and fourteen days in writing.
                                        </label>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="declaration4" name="declarations[]" value="4" required>
                                        <label class="form-check-label" for="declaration4">
                                            I understand that I will submit progress reports each year for review and renewal. Where I fail to do so, the NMIMR-IRB is mandated to terminate the study upon expiry.
                                        </label>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="declaration5" name="declarations[]" value="5" required>
                                        <label class="form-check-label" for="declaration5">
                                            I agree that I will submit a final report to the NMIMR-IRB at the end of the study.
                                        </label>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-6 mb-3">
                                        <label for="student_declaration_name" class="form-label fw-semibold">Name of Student <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="student_declaration_name" name="student_declaration_name" value="<?php echo htmlspecialchars($existingApplication['student_declaration_name'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="student_declaration_date" class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="student_declaration_date" name="student_declaration_date" value="<?php echo htmlspecialchars($existingApplication['student_declaration_date'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="student_declaration_signature" class="form-label fw-semibold">Electronic Signature <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="student_declaration_signature" name="student_declaration_signature" placeholder="Type your full name as signature" value="<?php echo htmlspecialchars($existingApplication['student_declaration_signature'] ?? ''); ?>" required>
                                        <small class="text-muted">By typing your name, you are signing this document electronically</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Supervisor Declaration -->
                            <div class="declaration-card p-4 border rounded bg-light">
                                <h6 class="fw-bold mb-3">II. SUPERVISOR DECLARATION</h6>
                                <p class="mb-3">As the <strong>Student Supervisor</strong> on this project, my signature confirms that I have read the student's work which has been reviewed and approved by the departmental review committee/scientific and technical committee:</p>

                                <div class="row mt-3">
                                    <div class="col-md-6 mb-3">
                                        <label for="supervisor_declaration_name" class="form-label fw-semibold">Name of Supervisor <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="supervisor_declaration_name" name="supervisor_declaration_name" value="<?php echo htmlspecialchars($existingApplication['supervisor_declaration_name'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="supervisor_declaration_date" class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="supervisor_declaration_date" name="supervisor_declaration_date" value="<?php echo htmlspecialchars($existingApplication['supervisor_declaration_date'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="supervisor_declaration_signature" class="form-label fw-semibold">Electronic Signature <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="supervisor_declaration_signature" name="supervisor_declaration_signature" placeholder="Type your full name as signature" value="<?php echo htmlspecialchars($existingApplication['supervisor_declaration_signature'] ?? ''); ?>" required>
                                        <small class="text-muted">By typing your name, you are signing this document electronically</small>
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
                                <p class="mb-0 opacity-75 small">Step 5 of 5 - Final verification</p>
                            </div>
                            <span class="badge bg-white text-warning">Final Step</span>
                        </div>
                        <div class="card-body">
                            <div class="review-summary">
                                <h6 class="fw-semibold mb-4 text-center">Please review your submission before finalizing</h6>

                                <div class="review-section mb-4">
                                    <h6 class="fw-semibold border-bottom pb-2 mb-3">Protocol Information</h6>
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
                                    </div>
                                </div>

                                <div class="review-section mb-4">
                                    <h6 class="fw-semibold border-bottom pb-2 mb-3">Student Information</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted">Student Name:</small>
                                            <div class="fw-medium" id="review_student_name">-</div>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted">Institution:</small>
                                            <div class="fw-medium" id="review_student_institution">-</div>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted">Student Number:</small>
                                            <div class="fw-medium" id="review_student_number">-</div>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted">Email:</small>
                                            <div class="fw-medium" id="review_student_email">-</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="review-section mb-4">
                                    <h6 class="fw-semibold border-bottom pb-2 mb-3">Study Information</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted">Research Type:</small>
                                            <div class="fw-medium" id="review_research_type">-</div>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted">Student Status:</small>
                                            <div class="fw-medium" id="review_student_status">-</div>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted">Duration:</small>
                                            <div class="fw-medium" id="review_study_duration">- years</div>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted">Dates:</small>
                                            <div class="fw-medium" id="review_study_dates">- to -</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="review-section mb-4">
                                    <h6 class="fw-semibold border-bottom pb-2 mb-3">Files to be Uploaded</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted">Approval Letter:</small>
                                            <div class="fw-medium" id="review_approval_letter">-</div>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted">Consent Form:</small>
                                            <div class="fw-medium" id="review_consent_form">-</div>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted">Data Instruments:</small>
                                            <div class="fw-medium" id="review_data_instruments">-</div>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted">Other Documents:</small>
                                            <div class="fw-medium" id="review_other_docs">-</div>
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
                                                <input class="form-check-input" type="checkbox" id="final_confirmation" required>
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
                        <button class="btn btn-primary" id="nextStepBtnMobile">
                            Next <i class="fas fa-arrow-right ms-2"></i>
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
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-paper-plane me-2"></i>Submit Protocol
                        </button>
                    </div>
                </div>
            </form>
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

    .student-info,
    .supervisor-entry,
    .study-info {
        border: 1px solid #dee2e6;
    }

    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    textarea.form-control {
        resize: vertical;
    }

    .border-primary {
        border-color: #0d6efd !important;
    }

    .border-info {
        border-color: #9d0df0 !important;
    }

    .border-success {
        border-color: #198754 !important;
    }

    .border-warning {
        border-color: #ffc107 !important;
    }

    .attachments-section {
        background-color: #f8f9fa;
    }

    .review-section {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
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

    @media (max-width: 992px) {
        .stepper-sidebar {
            margin-bottom: 1.5rem;
        }

        .stepper-navigation-mobile {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 1rem;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .card-body {
            padding-bottom: 5rem;
        }
    }
</style>

<script>
    // Current selected loader
    let currentLoader = 'spinner';

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('studentProtocolForm');
        const stepperProgress = document.getElementById('stepperProgress');
        const currentStepSpan = document.getElementById('currentStep');
        const prevStepBtn = document.getElementById('prevStepBtn');
        const nextStepBtn = document.getElementById('nextStepBtn');
        const prevStepBtnMobile = document.getElementById('prevStepBtnMobile');
        const nextStepBtnMobile = document.getElementById('nextStepBtnMobile');
        const editFormBtn = document.getElementById('editFormBtn');
        const finalActions = document.getElementById('finalActions');
        const steps = document.querySelectorAll('.step');
        const stepContents = document.querySelectorAll('.step-content');
        const abstractTextarea = document.getElementById('abstract');
        const backgroundTextarea = document.getElementById('background');
        const abstractCount = document.getElementById('abstract-count');
        const backgroundCount = document.getElementById('background-count');

        // Get initial step from PHP
        let currentStep = parseInt(document.getElementById('initialStep')?.value || 1);
        const totalSteps = 5;

        // Populate form with existing application data if available
        const existingApplicationId = document.getElementById('applicationId').value;
        if (existingApplicationId > 0) {
            populateFormFromExistingData();
        } else {
            // No existing application ID - check for saved draft
            fetchAndPopulateDraft();
        }

        // Initialize
        updateStepNavigation();
        updateReviewSummary();
        goToStep(currentStep);

        // Word count functions
        function countWords(text) {
            return text.trim().split(/\s+/).filter(word => word.length > 0).length;
        }

        function updateWordCounts() {
            abstractCount.textContent = countWords(abstractTextarea.value);
            backgroundCount.textContent = countWords(backgroundTextarea.value);
        }

        // Populate form with existing application data
        function populateFormFromExistingData() {
            const applicationId = document.getElementById('applicationId').value;

            fetch(`/applicant/handlers/get_application_data.php?id=${applicationId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.application) {
                        const app = data.application;

                        // Populate form using reusable function
                        populateFormWithData(app);

                        // Show file names for existing application
                        if (data.file_names) {
                            displaySavedFileNames(data.file_names);
                        }

                        // Update application ID and current step
                        if (app.current_step) {
                            const appStep = parseInt(app.current_step);
                            if (appStep > 1 && appStep <= totalSteps) {
                                currentStep = appStep;
                                document.getElementById('initialStep').value = appStep;
                            }
                        }

                        // Update navigation
                        updateStepNavigation();
                        goToStep(currentStep);
                    }
                })
                .catch(error => {
                    console.error('Error loading application data:', error);
                });
        }

        // Fetch and populate draft data on page load
        function fetchAndPopulateDraft() {
            const applicationType = document.querySelector('input[name="application_type"]').value || 'student';

            fetch(`/applicant/handlers/get_draft_data.php?type=${applicationType}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.has_draft && data.draft) {
                        // Update application ID if found
                        if (data.draft.id) {
                            document.getElementById('applicationId').value = data.draft.id;
                        }

                        // Update current step if different
                        if (data.draft.current_step) {
                            const draftStep = parseInt(data.draft.current_step);
                            if (draftStep > 1 && draftStep <= totalSteps) {
                                currentStep = draftStep;
                                document.getElementById('initialStep').value = draftStep;
                            }
                        }

                        // Populate form with draft data
                        populateFormWithData(data.draft);

                        // Show file names that have been uploaded
                        if (data.file_names) {
                            displaySavedFileNames(data.file_names);
                        }

                        // Show toast message
                        showToast('info', 'Your saved draft has been loaded.');

                        // Update navigation
                        updateStepNavigation();
                        updateReviewSummary();
                        goToStep(currentStep);
                    }
                })
                .catch(error => {
                    console.error('Error fetching draft data:', error);
                });
        }

        // Populate form with data (reusable function for both existing apps and drafts)
        function populateFormWithData(app) {
            // Protocol info
            if (app.protocol_number) {
                document.getElementById('protocol_number').value = app.protocol_number || '';
            }
            document.getElementById('version_number').value = app.version_number || '';
            document.getElementById('study_title').value = app.study_title || '';

            // Step 2 - Section A
            document.getElementById('student_department').value = app.student_department || '';
            document.getElementById('student_address').value = app.student_address || '';
            document.getElementById('supervisor1_name').value = app.supervisor1_name || '';
            document.getElementById('supervisor1_institution').value = app.supervisor1_institution || '';
            document.getElementById('supervisor1_address').value = app.supervisor1_address || '';
            document.getElementById('supervisor1_phone').value = app.supervisor1_phone || '';
            document.getElementById('supervisor1_email').value = app.supervisor1_email || '';
            document.getElementById('supervisor2_name').value = app.supervisor2_name || '';
            document.getElementById('supervisor2_institution').value = app.supervisor2_institution || '';
            document.getElementById('supervisor2_address').value = app.supervisor2_address || '';
            document.getElementById('supervisor2_phone').value = app.supervisor2_phone || '';
            document.getElementById('supervisor2_email').value = app.supervisor2_email || '';

            // Show supervisor 2 section if data exists
            if (app.supervisor2_name || app.supervisor2_email) {
                const supervisor2Section = document.getElementById('supervisor2-section');
                if (supervisor2Section) {
                    supervisor2Section.style.display = 'block';
                    supervisorCount = 2;
                }
            }

            // Research type radio buttons
            const researchTypeRadios = document.getElementsByName('research_type');
            for (const radio of researchTypeRadios) {
                if (radio.value === app.research_type) {
                    radio.checked = true;
                    if (app.research_type === 'Other') {
                        document.getElementById('research_type_other').style.display = 'block';
                        document.getElementById('research_type_other').value = app.research_type_other || '';
                    }
                }
            }

            // Student status radio buttons
            const studentStatusRadios = document.getElementsByName('student_status');
            for (const radio of studentStatusRadios) {
                if (radio.value === app.student_status) {
                    radio.checked = true;
                }
            }

            document.getElementById('study_duration_years').value = app.study_duration_years || '';
            document.getElementById('study_start_date').value = app.study_start_date || '';
            document.getElementById('study_end_date').value = app.study_end_date || '';
            document.getElementById('funding_sources').value = app.funding_sources || '';
            document.getElementById('prior_irb_review').value = app.prior_irb_review || '';
            document.getElementById('collaborating_institutions').value = app.collaborating_institutions || '';

            // Step 3 - Section B
            document.getElementById('abstract').value = app.abstract || '';
            document.getElementById('background').value = app.background || '';
            document.getElementById('methods').value = app.methods || '';
            document.getElementById('ethical_considerations').value = app.ethical_considerations || '';
            document.getElementById('expected_outcome').value = app.expected_outcome || '';
            document.getElementById('key_references').value = app.key_references || '';
            document.getElementById('work_plan').value = app.work_plan || '';
            document.getElementById('budget').value = app.budget || '';

            // Update word counts
            updateWordCounts();

            // Step 4 - Section C
            document.getElementById('student_declaration_name').value = app.student_declaration_name || '';
            document.getElementById('student_declaration_date').value = app.student_declaration_date || '';
            document.getElementById('student_declaration_signature').value = app.student_declaration_signature || '';
            document.getElementById('supervisor_declaration_name').value = app.supervisor_declaration_name || '';
            document.getElementById('supervisor_declaration_date').value = app.supervisor_declaration_date || '';
            document.getElementById('supervisor_declaration_signature').value = app.supervisor_declaration_signature || '';

            // Handle declarations
            if (app.declarations && app.declarations.length > 0) {
                const declarationCheckboxes = document.querySelectorAll('input[name="declarations[]"]');
                declarationCheckboxes.forEach(cb => {
                    if (app.declarations.includes(cb.value)) {
                        cb.checked = true;
                    }
                });
            }

            // Update review summary
            updateReviewSummary();
        }

        // Display saved file names (for reference, not actual file upload)
        function displaySavedFileNames(fileNames) {
            const fileFields = [{
                    fieldId: 'approval_letter',
                    label: 'Approval Letter'
                },
                {
                    fieldId: 'collaboration_letter',
                    label: 'Collaboration Letter'
                },
                {
                    fieldId: 'consent_form',
                    label: 'Consent Form'
                },
                {
                    fieldId: 'assent_form',
                    label: 'Assent Form'
                },
                {
                    fieldId: 'data_instruments',
                    label: 'Data Instruments'
                }
            ];

            fileFields.forEach(item => {
                if (fileNames[item.fieldId]) {
                    const input = document.getElementById(item.fieldId);
                    if (input) {
                        // Add a visual indicator that file was previously uploaded
                        const helpText = input.parentElement.querySelector('.text-muted');
                        if (helpText) {
                            const existingNote = helpText.textContent;
                            helpText.innerHTML = `Previously uploaded: <strong>${fileNames[item.fieldId]}</strong>. ${existingNote}`;
                        }
                    }
                }
            });
        }

        abstractTextarea.addEventListener('input', updateWordCounts);
        backgroundTextarea.addEventListener('input', updateWordCounts);
        updateWordCounts();

        // Step navigation
        function goToStep(step) {
            if (step < 1 || step > totalSteps) return;

            // Validate current step before leaving
            if (step > currentStep && !validateCurrentStep()) {
                alert('Please complete all required fields in the current section before proceeding.');
                return;
            }

            currentStep = step;

            // Update step indicators
            steps.forEach((stepEl, index) => {
                const stepNum = index + 1;
                const stepNumber = stepEl.querySelector('.step-number');
                const stepTitle = stepEl.querySelector('.step-title h6');

                if (stepNum === currentStep) {
                    stepEl.classList.add('active');
                    stepEl.classList.remove('completed');
                    stepNumber.classList.remove('bg-light', 'text-muted', 'border');
                    stepNumber.classList.add('bg-primary', 'text-white');
                    stepTitle.classList.remove('text-muted');
                    stepTitle.classList.add('text-primary');
                } else if (stepNum < currentStep) {
                    stepEl.classList.remove('active');
                    stepEl.classList.add('completed');
                    stepNumber.classList.remove('bg-light', 'text-muted', 'border');
                    stepNumber.classList.add('bg-success', 'text-white');
                    stepTitle.classList.remove('text-muted');
                    stepTitle.classList.add('text-success');
                } else {
                    stepEl.classList.remove('active', 'completed');
                    stepNumber.classList.remove('bg-primary', 'bg-success', 'text-white');
                    stepNumber.classList.add('bg-light', 'text-muted', 'border');
                    stepTitle.classList.remove('text-primary', 'text-success');
                    stepTitle.classList.add('text-muted');
                }
            });

            // Update content visibility
            stepContents.forEach(content => {
                const contentStep = parseInt(content.dataset.step);
                content.classList.toggle('active', contentStep === currentStep);
            });

            // Update progress bar
            const progressPercentage = ((currentStep - 1) / (totalSteps - 1)) * 100;
            stepperProgress.style.width = `${progressPercentage}%`;

            // Update step counter
            currentStepSpan.textContent = currentStep;

            // Update navigation buttons
            updateStepNavigation();

            // Show/hide final actions
            if (currentStep === totalSteps) {
                finalActions.classList.remove('d-none');
                updateReviewSummary();
            } else {
                finalActions.classList.add('d-none');
            }

            // Scroll to top of form
            document.querySelector('.step-content.active').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        function updateStepNavigation() {
            const isFirstStep = currentStep === 1;
            const isLastStep = currentStep === totalSteps;

            // Update button text for last step
            if (isLastStep) {
                nextStepBtn.textContent = 'Review';
                nextStepBtnMobile.textContent = 'Review';
            } else {
                nextStepBtn.textContent = 'Next';
                nextStepBtnMobile.textContent = 'Next';
            }

            // Enable/disable buttons
            prevStepBtn.disabled = isFirstStep;
            prevStepBtnMobile.disabled = isFirstStep;

            // Update button icons
            nextStepBtn.innerHTML = isLastStep ? 'Review <i class="fas fa-check-circle ms-2"></i>' : 'Next <i class="fas fa-arrow-right ms-2"></i>';
            nextStepBtnMobile.innerHTML = isLastStep ? 'Review <i class="fas fa-check-circle ms-2"></i>' : 'Next <i class="fas fa-arrow-right ms-2"></i>';
        }

        function validateCurrentStep() {
            const currentStepContent = document.querySelector(`.step-content[data-step="${currentStep}"]`);
            const requiredFields = currentStepContent.querySelectorAll('[required]');

            for (const field of requiredFields) {
                if (field.type === 'checkbox' || field.type === 'radio') {
                    const name = field.name;
                    // Use name if available, otherwise use ID for validation
                    const selector = name ? `input[name="${name}"]:checked` : `input[id="${field.id}"]:checked`;
                    const checked = currentStepContent.querySelectorAll(selector).length > 0;
                    if (!checked) {
                        field.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        field.focus();
                        return false;
                    }
                } else if (field.type === 'file') {
                    // File validation
                    if (!field.files || field.files.length === 0) {
                        field.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        field.focus();
                        return false;
                    }
                } else {
                    if (!field.value.trim()) {
                        field.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        field.focus();
                        return false;
                    }
                }
            }

            // Step-specific validations
            if (currentStep === 3) {
                const abstractWords = countWords(abstractTextarea.value);
                const backgroundWords = countWords(backgroundTextarea.value);

                if (abstractWords > 250) {
                    alert('Abstract/Executive Summary must not exceed 250 words.');
                    abstractTextarea.focus();
                    return false;
                }

                if (backgroundWords > 1500) {
                    alert('Background/Rationale must not exceed 1500 words.');
                    backgroundTextarea.focus();
                    return false;
                }
            }

            return true;
        }

        // Navigation button events
        nextStepBtn.addEventListener('click', () => handleNextStep());
        prevStepBtn.addEventListener('click', () => goToStep(currentStep - 1));
        nextStepBtnMobile.addEventListener('click', () => handleNextStep());
        prevStepBtnMobile.addEventListener('click', () => goToStep(currentStep - 1));

        // Handle Next step with draft save
        function handleNextStep() {
            // Save draft before proceeding to next step
            saveCurrentStepAsDraft().then(() => {
                goToStep(currentStep + 1);
            });
        }

        // Save current step as draft
        function saveCurrentStepAsDraft() {
            return new Promise((resolve, reject) => {
                const form = document.getElementById('studentProtocolForm');
                if (!form) {
                    reject(new Error('Form not found'));
                    return;
                }

                const formData = new FormData(form);
                formData.append('action', 'save_draft');
                formData.append('current_step', currentStep);

                fetch('/applicant/handlers/student_application_handler.php', {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin' // Ensure cookies are sent
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.application_id) {
                            document.getElementById('applicationId').value = data.application_id;
                        }
                        resolve(data);
                    })
                    .catch(error => {
                        reject(error);
                    });
            });
        }

        // Edit form button
        editFormBtn.addEventListener('click', () => goToStep(1));

        // Step click events
        steps.forEach(step => {
            step.addEventListener('click', () => {
                const stepNum = parseInt(step.dataset.step);
                if (stepNum < currentStep) {
                    goToStep(stepNum);
                }
            });
        });

        // Research type other field toggle
        document.querySelectorAll('input[name="research_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const otherInput = document.getElementById('research_type_other');
                otherInput.style.display = this.value === 'Other' ? 'block' : 'none';
                if (this.value !== 'Other') {
                    otherInput.value = '';
                }
            });
        });

        // Update review summary
        function updateReviewSummary() {
            // Protocol info
            document.getElementById('review_protocol_number').textContent =
                document.getElementById('protocol_number').value || '-';
            document.getElementById('review_version_number').textContent =
                document.getElementById('version_number').value || '-';
            document.getElementById('review_study_title').textContent =
                document.getElementById('study_title').value || '-';

            // Student info
            document.getElementById('review_student_name').textContent =
                document.getElementById('student_name').value || '-';
            document.getElementById('review_student_institution').textContent =
                document.getElementById('student_institution').value || '-';
            document.getElementById('review_student_number').textContent =
                document.getElementById('student_number').value || '-';
            document.getElementById('review_student_email').textContent =
                document.getElementById('student_email').value || '-';

            // Study info
            const researchType = document.querySelector('input[name="research_type"]:checked');
            document.getElementById('review_research_type').textContent =
                researchType ? researchType.value : '-';

            const studentStatus = document.querySelector('input[name="student_status"]:checked');
            document.getElementById('review_student_status').textContent =
                studentStatus ? studentStatus.value : '-';

            document.getElementById('review_study_duration').textContent =
                document.getElementById('study_duration_years').value || '0';

            const startDate = document.getElementById('study_start_date').value;
            const endDate = document.getElementById('study_end_date').value;
            document.getElementById('review_study_dates').textContent =
                `${startDate || '-'} to ${endDate || '-'}`;

            // File info
            const approvalFile = document.getElementById('approval_letter').files[0];
            document.getElementById('review_approval_letter').textContent =
                approvalFile ? approvalFile.name : 'No file selected';

            const consentFile = document.getElementById('consent_form').files[0];
            document.getElementById('review_consent_form').textContent =
                consentFile ? consentFile.name : 'No file selected';

            const dataFiles = document.getElementById('data_instruments').files;
            document.getElementById('review_data_instruments').textContent =
                dataFiles.length > 0 ? `${dataFiles.length} file(s)` : 'No files selected';

            const otherFiles = document.getElementById('additional_documents').files;
            document.getElementById('review_other_docs').textContent =
                otherFiles.length > 0 ? `${otherFiles.length} file(s)` : 'No files selected';
        }

        // Update review on input
        form.querySelectorAll('input, textarea, select').forEach(element => {
            element.addEventListener('input', updateReviewSummary);
            element.addEventListener('change', updateReviewSummary);
        });

        // File input change events
        document.getElementById('approval_letter').addEventListener('change', updateReviewSummary);
        document.getElementById('consent_form').addEventListener('change', updateReviewSummary);
        document.getElementById('data_instruments').addEventListener('change', updateReviewSummary);
        document.getElementById('additional_documents').addEventListener('change', updateReviewSummary);

        // Add supervisor functionality
        let supervisorCount = 1;

        window.addSupervisor = function() {
            if (supervisorCount < 4) {
                supervisorCount++;
                const section = document.getElementById('supervisor2-section');
                section.style.display = 'block';

                // Update field names and IDs
                section.querySelectorAll('input').forEach(input => {
                    const oldId = input.id;
                    const oldName = input.name;
                    if (oldId && oldName) {
                        const newId = oldId.replace('2', supervisorCount);
                        const newName = oldName.replace('2', supervisorCount);
                        input.id = newId;
                        input.name = newName;
                    }
                });

                // Update labels
                section.querySelectorAll('label').forEach(label => {
                    const htmlFor = label.getAttribute('for');
                    if (htmlFor) {
                        const newFor = htmlFor.replace('2', supervisorCount);
                        label.setAttribute('for', newFor);
                    }
                });

                // Update heading
                const heading = section.querySelector('h6');
                if (heading) {
                    heading.textContent = `Supervisor ${supervisorCount}`;
                }
            }

            if (supervisorCount >= 4) {
                document.querySelector('.supervisors-info .btn-outline-primary').style.display = 'none';
            }
        };

        window.removeSupervisor = function(num) {
            const section = document.getElementById('supervisor2-section');
            section.style.display = 'none';

            section.querySelectorAll('input').forEach(input => {
                input.value = '';
            });

            supervisorCount = 1;
            document.querySelector('.supervisors-info .btn-outline-primary').style.display = 'inline-block';
        };

        // Save draft functionality
        document.getElementById('saveDraftBtn').addEventListener('click', function() {
            const formData = new FormData(form);
            formData.append('action', 'save_draft');
            formData.append('current_step', currentStep);

            const btn = this;
            const originalText = btn.innerHTML;

            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
            btn.disabled = true;

            showLoadingOverlay();

            // Save draft via AJAX
            fetch('/applicant/handlers/student_application_handler.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin' // Ensure cookies are sent
                })
                .then(response => response.json())
                .then(data => {
                    hideLoadingOverlay();
                    if (data.success) {
                        // Update application_id if returned
                        if (data.application_id) {
                            document.getElementById('applicationId').value = data.application_id;
                        }
                        showToast('success', 'Draft saved successfully!');
                    } else {
                        showToast('error', data.message || 'Failed to save draft');
                    }
                })
                .catch(error => {
                    hideLoadingOverlay();
                    console.error('Error saving draft:', error);
                    showToast('error', 'An error occurred while saving the draft');
                })
                .finally(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        });

        // Form submission
        let isSubmitting = false; // Flag to prevent multiple submissions
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Prevent multiple submissions
            if (isSubmitting) {
                console.log('Submission already in progress, ignoring...');
                return;
            }
            isSubmitting = true;

            // Validate all steps
            for (let step = 1; step <= totalSteps; step++) {
                const stepContent = document.querySelector(`.step-content[data-step="${step}"]`);
                const requiredFields = stepContent.querySelectorAll('[required]');

                for (const field of requiredFields) {
                    if (field.type === 'checkbox' || field.type === 'radio') {
                        const name = field.name;
                        // Use name if available, otherwise use ID for validation
                        const selector = name ? `input[name="${name}"]:checked` : `input[id="${field.id}"]:checked`;
                        const checked = form.querySelectorAll(selector).length > 0;
                        if (!checked) {
                            goToStep(step);
                            field.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                            field.focus();
                            alert(`Please complete all required fields in Step ${step} before submitting.`);
                            return;
                        }
                    } else if (field.type === 'file') {
                        if (!field.files || field.files.length === 0) {
                            goToStep(step);
                            alert(`Please upload all required files in Step ${step} before submitting.`);
                            return;
                        }
                    } else {
                        if (!field.value.trim()) {
                            goToStep(step);
                            alert(`Please complete all required fields in Step ${step} before submitting.`);
                            return;
                        }
                    }
                }
            }

            // Validate final confirmation
            if (!document.getElementById('final_confirmation').checked) {
                alert('Please confirm that all information is accurate and complete.');
                return;
            }

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
            submitBtn.disabled = true;

            // Prepare form data for submission
            const submitFormData = new FormData(form);
            submitFormData.append('action', 'submit_application');

            // Show loading overlay
            showLoadingOverlay();

            // Submit form via AJAX
            fetch('/applicant/handlers/student_application_handler.php', {
                    method: 'POST',
                    body: submitFormData,
                    credentials: 'same-origin' // Ensure cookies are sent
                })
                .then(response => response.json())
                .then(data => {
                    hideLoadingOverlay();
                    if (data.success) {
                        showToast('success', 'Protocol submitted successfully! Protocol Number: ' + (data.protocol_number || 'Pending'));
                        // Redirect to confirmation page with protocol number
                        setTimeout(() => {
                            window.location.href = 'submission-confirmation.php?protocol=' + encodeURIComponent(data.protocol_number || '');
                        }, 1500);
                    } else {
                        showToast('error', data.message || 'Submission failed. Please try again.');
                        if (data.errors) {
                            console.error('Validation errors:', data.errors);
                        }
                    }
                })
                .catch(error => {
                    hideLoadingOverlay();
                    console.error('Error submitting form:', error);
                    showToast('error', 'An error occurred during submission. Please try again.');
                })
                .finally(() => {
                    isSubmitting = false;
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
        });

        // Set default dates
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('student_declaration_date').value = today;
        document.getElementById('supervisor_declaration_date').value = today;

        // Set default start date to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        document.getElementById('study_start_date').valueAsDate = tomorrow;

        // Set default end date to 1 year from start
        const oneYearLater = new Date(tomorrow);
        oneYearLater.setFullYear(oneYearLater.getFullYear() + 1);
        document.getElementById('study_end_date').valueAsDate = oneYearLater;
        document.getElementById('study_duration_years').value = 1;


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
                    showToast('success', data.message);
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

        // Function to show loading programmatically (for other uses)
        window.showLoading = function(loaderType = 'spinner', duration = 3000, message = 'Processing...') {
            if (loaderType) currentLoader = loaderType;

            // Update message if provided
            const loadingText = document.querySelector('.loading-text');
            if (loadingText && message) {
                loadingText.textContent = message;
            }

            showLoadingOverlay({
                firstName: 'User'
            });

            // Auto-hide after duration if provided
            if (duration) {
                setTimeout(hideLoadingOverlay, duration);
            }
        };

        // Function to hide loading programmatically
        window.hideLoading = hideLoadingOverlay;
    });
</script>