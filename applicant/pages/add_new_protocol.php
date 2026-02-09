<?php

// Check if applicant is logged in
if (!is_applicant_logged_in()) {
    header('Location: /login');
    exit;
}

$userId = $_SESSION['user_id'] ?? 0;

// Check if user can submit new application (max 3)
if (!canSubmitNewApplication($userId)) {
    header('Location: /applicant-dashboard?error=max_applications');
    exit;
}

// Get application type
$type = $_GET['type'] ?? 'student';

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

<div class="add-new-protocol container-fluid mt-4 mb-4">
    <!-- Header -->
    <div class="welcome-header text-white p-4 rounded mb-4 position-relative overflow-hidden" 
         style="background: linear-gradient(135deg, #2c3e50, #4a6491);">
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
                                    <input type="text" class="form-control" id="protocol_number" name="protocol_number" required>
                                    <small class="text-muted">Unique identifier for your study</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="version_number" class="form-label fw-semibold">Version Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="version_number" name="version_number" placeholder="e.g., 1.0" required>
                                    <small class="text-muted">Document version (start with 1.0)</small>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="study_title" class="form-label fw-semibold">Title of Study <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="study_title" name="study_title" rows="2" required></textarea>
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
                                        <input type="text" class="form-control" id="student_name" name="student_name" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="student_institution" class="form-label fw-semibold">Institution <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="student_institution" name="student_institution" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="student_department" class="form-label fw-semibold">Faculty/Department/School <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="student_department" name="student_department" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="student_address" class="form-label fw-semibold">Address</label>
                                        <input type="text" class="form-control" id="student_address" name="student_address">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="student_number" class="form-label fw-semibold">Student Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="student_number" name="student_number" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="student_phone" class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="student_phone" name="student_phone" required>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="student_email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="student_email" name="student_email" required>
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
                                            <input type="text" class="form-control" id="supervisor1_name" name="supervisor1_name" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="supervisor1_institution" class="form-label fw-semibold">Institution/Faculty/Department/School <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="supervisor1_institution" name="supervisor1_institution" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="supervisor1_address" class="form-label fw-semibold">Address</label>
                                            <input type="text" class="form-control" id="supervisor1_address" name="supervisor1_address">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="supervisor1_phone" class="form-label fw-semibold">Phone Number</label>
                                            <input type="tel" class="form-control" id="supervisor1_phone" name="supervisor1_phone">
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label for="supervisor1_email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="supervisor1_email" name="supervisor1_email" required>
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
                                            <input type="text" class="form-control" id="supervisor2_name" name="supervisor2_name">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="supervisor2_institution" class="form-label">Institution/Faculty/Department/School</label>
                                            <input type="text" class="form-control" id="supervisor2_institution" name="supervisor2_institution">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="supervisor2_address" class="form-label">Address</label>
                                            <input type="text" class="form-control" id="supervisor2_address" name="supervisor2_address">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="supervisor2_phone" class="form-label">Phone Number</label>
                                            <input type="tel" class="form-control" id="supervisor2_phone" name="supervisor2_phone">
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label for="supervisor2_email" class="form-label">Email Address</label>
                                            <input type="email" class="form-control" id="supervisor2_email" name="supervisor2_email">
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
                                                <input class="form-check-input" type="radio" name="research_type" id="type_biomedical" value="Biomedical" required>
                                                <label class="form-check-label" for="type_biomedical">Biomedical</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="research_type" id="type_social" value="Social/Behavioural">
                                                <label class="form-check-label" for="type_social">Social/Behavioural</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="research_type" id="type_other" value="Other">
                                                <label class="form-check-label" for="type_other">Others</label>
                                            </div>
                                        </div>
                                        <input type="text" class="form-control mt-2" id="research_type_other" name="research_type_other" placeholder="Please specify" style="display: none;">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Student Status <span class="text-danger">*</span></label>
                                        <div class="mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="student_status" id="status_undergrad" value="Undergraduate" required>
                                                <label class="form-check-label" for="status_undergrad">Undergraduate</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="student_status" id="status_masters" value="Masters">
                                                <label class="form-check-label" for="status_masters">Masters</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="student_status" id="status_phd" value="PhD">
                                                <label class="form-check-label" for="status_phd">PhD</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="study_duration_years" class="form-label fw-semibold">Duration of Research/Study <span class="text-danger">*</span></label>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <input type="number" class="form-control" id="study_duration_years" name="study_duration_years" min="0.5" max="10" step="0.5" placeholder="Years" required>
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
                                                <input type="date" class="form-control" id="study_start_date" name="study_start_date" required>
                                                <small class="text-muted">Start Date</small>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <input type="date" class="form-control" id="study_end_date" name="study_end_date" required>
                                                <small class="text-muted">End Date</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label for="funding_sources" class="form-label fw-semibold">Source(s) of Funding</label>
                                        <textarea class="form-control" id="funding_sources" name="funding_sources" rows="2" placeholder="Name, Address and Email"></textarea>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label for="approval_letter" class="form-label fw-semibold">Departmental Thesis Approval Letter and Introductory Letter from Head of Department <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control" id="approval_letter" name="approval_letter" accept=".pdf,.doc,.docx" required>
                                        <small class="text-muted">Attach Letter of Approval</small>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label for="prior_irb_review" class="form-label fw-semibold">Prior IRB Review</label>
                                        <textarea class="form-control" id="prior_irb_review" name="prior_irb_review" rows="2" placeholder="Name any other IRB this proposal has been submitted to and attach approval letter if applicable. In case of rejection, state reasons"></textarea>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label for="collaborating_institutions" class="form-label fw-semibold">Collaborating Institutions</label>
                                        <textarea class="form-control" id="collaborating_institutions" name="collaborating_institutions" rows="2" placeholder="List collaborating institutions"></textarea>
                                        <input type="file" class="form-control mt-2" id="collaboration_letter" name="collaboration_letter" accept=".pdf,.doc,.docx">
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
                                <textarea class="form-control" id="abstract" name="abstract" rows="4" maxlength="250" required></textarea>
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-muted">Not more than 250 words</small>
                                    <small class="text-muted"><span id="abstract-count">0</span>/250 words</small>
                                </div>
                            </div>

                            <!-- Background/Rationale -->
                            <div class="mb-4">
                                <label for="background" class="form-label fw-semibold">BACKGROUND OR RATIONALE OF STUDY <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="background" name="background" rows="6" maxlength="1500" required></textarea>
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-muted">Include aims and objectives, literature review; not more than 1500 words</small>
                                    <small class="text-muted"><span id="background-count">0</span>/1500 words</small>
                                </div>
                            </div>

                            <!-- Methods -->
                            <div class="mb-4">
                                <label for="methods" class="form-label fw-semibold">METHODS <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="methods" name="methods" rows="6" required></textarea>
                                <small class="text-muted">Include study site, population, study design, sampling, data collection, data analysis, inclusion and exclusion criteria</small>
                            </div>

                            <!-- Ethical Considerations -->
                            <div class="mb-4">
                                <label for="ethical_considerations" class="form-label fw-semibold">ETHICAL CONSIDERATIONS <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="ethical_considerations" name="ethical_considerations" rows="6" required></textarea>
                                <small class="text-muted">Provide description of likely ethical issues and how they would be resolved (consent procedures, confidentiality, privacy, risks and benefits, etc.)</small>
                            </div>

                            <!-- Expected Outcome/Results -->
                            <div class="mb-4">
                                <label for="expected_outcome" class="form-label fw-semibold">EXPECTED OUTCOME/RESULTS <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="expected_outcome" name="expected_outcome" rows="4" required></textarea>
                            </div>

                            <!-- Key References -->
                            <div class="mb-4">
                                <label for="key_references" class="form-label fw-semibold">KEY REFERENCES</label>
                                <textarea class="form-control" id="key_references" name="key_references" rows="4"></textarea>
                            </div>

                            <!-- Work Plan -->
                            <div class="mb-4">
                                <label for="work_plan" class="form-label fw-semibold">WORK PLAN</label>
                                <textarea class="form-control" id="work_plan" name="work_plan" rows="4"></textarea>
                            </div>

                            <!-- Budget and Justification -->
                            <div class="mb-4">
                                <label for="budget" class="form-label fw-semibold">BUDGET AND BUDGET JUSTIFICATION</label>
                                <textarea class="form-control" id="budget" name="budget" rows="4"></textarea>
                            </div>

                            <!-- Attachments -->
                            <div class="attachments-section p-3 border rounded">
                                <h6 class="fw-semibold mb-3"><i class="fas fa-paperclip me-2"></i>Required Attachments</h6>
                                
                                <!-- Consent Form -->
                                <div class="mb-3">
                                    <label for="consent_form" class="form-label fw-semibold">CONSENT FORM <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="consent_form" name="consent_form" accept=".pdf,.doc,.docx" required>
                                    <small class="text-muted">Download the NMIMR-IRB Consent form Template for guidance</small>
                                </div>

                                <!-- Assent and Parental Consent Forms -->
                                <div class="mb-3">
                                    <label for="assent_form" class="form-label fw-semibold">ASSENT FORM AND PARENTAL CONSENT FORM</label>
                                    <input type="file" class="form-control" id="assent_form" name="assent_form" accept=".pdf,.doc,.docx">
                                    <small class="text-muted">Only applicable where children of ages 12 to 17 would be recruited as research participants</small>
                                </div>

                                <!-- Data Collection Instruments -->
                                <div class="mb-3">
                                    <label for="data_instruments" class="form-label fw-semibold">DATA COLLECTION INSTRUMENTS <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="data_instruments" name="data_instruments" accept=".pdf,.doc,.docx,.xls,.xlsx" multiple required>
                                    <small class="text-muted">Interview Guide, Questionnaire, etc.</small>
                                </div>

                                <!-- Additional Documents -->
                                <div class="mb-3">
                                    <label for="additional_documents" class="form-label fw-semibold">Additional Supporting Documents</label>
                                    <input type="file" class="form-control" id="additional_documents" name="additional_documents[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
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
                                        <input type="text" class="form-control" id="student_declaration_name" name="student_declaration_name" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="student_declaration_date" class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="student_declaration_date" name="student_declaration_date" required>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="student_declaration_signature" class="form-label fw-semibold">Electronic Signature <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="student_declaration_signature" name="student_declaration_signature" placeholder="Type your full name as signature" required>
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
                                        <input type="text" class="form-control" id="supervisor_declaration_name" name="supervisor_declaration_name" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="supervisor_declaration_date" class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="supervisor_declaration_date" name="supervisor_declaration_date" required>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="supervisor_declaration_signature" class="form-label fw-semibold">Electronic Signature <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="supervisor_declaration_signature" name="supervisor_declaration_signature" placeholder="Type your full name as signature" required>
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
    
    .student-info, .supervisor-entry, .study-info {
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
        border-color: #0dcaf0 !important;
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
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
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
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .card-body {
            padding-bottom: 5rem;
        }
    }
</style>

<script>
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
    
    let currentStep = 1;
    const totalSteps = 5;
    
    // Initialize
    updateStepNavigation();
    updateReviewSummary();
    
    // Word count functions
    function countWords(text) {
        return text.trim().split(/\s+/).filter(word => word.length > 0).length;
    }
    
    function updateWordCounts() {
        abstractCount.textContent = countWords(abstractTextarea.value);
        backgroundCount.textContent = countWords(backgroundTextarea.value);
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
        document.querySelector('.step-content.active').scrollIntoView({ behavior: 'smooth', block: 'start' });
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
                const checked = currentStepContent.querySelectorAll(`input[name="${name}"]:checked`).length > 0;
                if (!checked) {
                    field.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    field.focus();
                    return false;
                }
            } else if (field.type === 'file') {
                // File validation
                if (!field.files || field.files.length === 0) {
                    field.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    field.focus();
                    return false;
                }
            } else {
                if (!field.value.trim()) {
                    field.scrollIntoView({ behavior: 'smooth', block: 'center' });
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
    nextStepBtn.addEventListener('click', () => goToStep(currentStep + 1));
    prevStepBtn.addEventListener('click', () => goToStep(currentStep - 1));
    nextStepBtnMobile.addEventListener('click', () => goToStep(currentStep + 1));
    prevStepBtnMobile.addEventListener('click', () => goToStep(currentStep - 1));
    
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
        if (!validateCurrentStep()) {
            alert('Please complete all required fields before saving.');
            return;
        }
        
        const formData = new FormData(form);
        formData.append('action', 'save_draft');
        formData.append('current_step', currentStep);
        
        const btn = this;
        const originalText = btn.innerHTML;
        
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
        btn.disabled = true;
        
        // Simulate API call
        setTimeout(() => {
            alert('Draft saved successfully!');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }, 1000);
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate all steps
        for (let step = 1; step <= totalSteps; step++) {
            const stepContent = document.querySelector(`.step-content[data-step="${step}"]`);
            const requiredFields = stepContent.querySelectorAll('[required]');
            
            for (const field of requiredFields) {
                if (field.type === 'checkbox' || field.type === 'radio') {
                    const name = field.name;
                    const checked = form.querySelectorAll(`input[name="${name}"]:checked`).length > 0;
                    if (!checked) {
                        goToStep(step);
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
        
        // Simulate form submission
        setTimeout(() => {
            alert('Protocol submitted successfully! You will receive a confirmation email shortly.');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            window.location.href = '/submission-confirmation';
        }, 2000);
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
});
</script>