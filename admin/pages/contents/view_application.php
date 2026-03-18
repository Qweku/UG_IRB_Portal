<?php

// Prevent caching
// header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
// header('Pragma: no-cache');
// header('Expires: 0');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    $session_name = 'ug_irb_session';
    session_name($session_name);
    session_start();
}

// Authentication check
// if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
//     header('Location: /login');
//     exit;
// }

// Include CSRF protection functions
// require_once '../../includes/functions/csrf.php';

// Initialize study variables with default values
$institution_name = getInstitutionName();



$study_vars = [
    'study_number' => '',
    'ref_number' => '',
    'exp_date' => '',
    'protocol_title' => '',
    'sponsor' => '',
    'active' => 'Open',
    'review_type' => '',
    'status' => '',
    'risk_category' => '',
    'approval_patient_enrollment' => '',
    'current_enrolled' => '',
    'on_agenda_date' => '',
    'irb_of_record' => $institution_name ?? '',
    'cr_required' => '',
    'renewal_cycle' => '12',
    'date_received' => date('Y-m-d'),
    'first_irb_review' => '',
    'original_approval' => '',
    'last_seen_by_irb' => '',
    'last_irb_renewal' => '',
    'internal_notes' => '',
    'initial_summary_of_agenda' => '',
    // Additional fields with defaults
    'funding_source' => '',
    'duration' => '',
    'study_design' => '',
    'target_population' => '',
    'sample_size' => '',
    'age_range' => '',
    'vulnerable_population' => ''
];

// Extract variables for default case (will be overwritten if application data exists)
extract($study_vars, EXTR_SKIP);

$is_edit = false;
$study_id = null;
$personnel_data = [];
$documents = [];
$application = [];

// Get staff types from the database
$dropdown_data = [
    'staffTypes' => [],
    'sponsors' => [],
    'study_types' => [],
    'sae_types' => [],
    'locations' => [],
    'study_statuses' => [],
    'risk_categories' => [],
    'contacts' => []
];

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    }
    // Fetch dropdown data using helpers
    $dropdown_data['staffTypes'] = getStaffTypes();
    $dropdown_data['sponsors'] = getSponsors();
    $dropdown_data['study_types'] = getReviewTypesList();
    $dropdown_data['study_statuses'] = getStudyStatusesList();
    $dropdown_data['risk_categories'] = getRiskCategoriesList();
    $dropdown_data['sae_types'] = getSAETypesList();
    $dropdown_data['locations'] = getStudyLocationsList();

    $applicationId = $_GET['id'] ?? null;

    if (empty($applicationId)) {
        echo json_encode(['status' => 'error', 'message' => 'Application ID is required']);
        exit;
    }

    // Get main application data with all three detail tables
    $stmt = $conn->prepare("SELECT a.*, 
                        sa.*, 
                        na.*, 
                        nna.* 
                        FROM applications a
                        LEFT JOIN student_application_details sa ON sa.application_id = a.id
                        LEFT JOIN nmimr_application_details na ON na.application_id = a.id
                        LEFT JOIN non_nmimr_application_details nna ON nna.application_id = a.id
                        WHERE a.id = ?");
    $stmt->execute([$applicationId]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);

    error_log("All Application Details:" .  print_r($application, true));

    if (!$application) {
        echo json_encode(['status' => 'error', 'message' => 'Application not found']);
        exit;
    }

    // Map application data to study_vars for form population
    if (!empty($application)) {
        $study_vars = [
            'study_number' => $application['protocol_number'] ?? '',
            'ref_number' => 'NR' . ($application['protocol_number'] ?? ''),
            'exp_date' => date('Y-m-d', strtotime('+1 year')),
            'protocol_title' => $application['study_title'] ?? '',
            'sponsor' => $application['sponsor'] ?? '',
            'active' => 'Open',
            'review_type' => '',
            'status' => $application['status'] ?? '',
            'risk_category' => '',
            'approval_patient_enrollment' => '',
            'current_enrolled' => '',
            'on_agenda_date' => '',
            'irb_of_record' => $institution_name ?? '',
            'cr_required' => '',
            'renewal_cycle' => '12',
            'date_received' => $application['created_at'] ?? date('Y-m-d'),
            'first_irb_review' => '',
            'original_approval' => '',
            'last_seen_by_irb' => '',
            'last_irb_renewal' => '',
            'internal_notes' => $application['abstract'] ?? '',
            'initial_summary_of_agenda' => '',
            // Additional fields from application
            'funding_source' => $application['funding_source'] ?? '',
            'duration' => $application['study_duration'] ?? '',
            'study_design' => $application['study_design'] ?? '',
            'target_population' => $application['target_population'] ?? '',
            'sample_size' => $application['sample_size'] ?? '',
            'age_range' => $application['age_range'] ?? '',
            'vulnerable_population' => $application['vulnerable_population'] ?? ''
        ];

        // Extract variables for easier access
        extract($study_vars, EXTR_SKIP);
    }

    // Get assigned reviewers
    $stmt = $conn->prepare("
        SELECT ar.*, u.full_name, u.email
        FROM application_reviews ar
        JOIN users u ON ar.reviewer_id = u.id
        WHERE ar.application_id = ?
        ORDER BY ar.created_at DESC
    ");
    $stmt->execute([$applicationId]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch documents - try application_id first, then study_id
    $stmt = $conn->prepare("SELECT * FROM application_documents WHERE application_id = ?");
    $stmt->execute([$applicationId]);
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If no documents with application_id, try study_id
    if (empty($documents)) {
        $stmt = $conn->prepare("SELECT * FROM application_documents WHERE study_id = ?");
        $stmt->execute([$applicationId]);
        $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Determine application type based on which detail table has data
    $applicationType = 'student';
    if (!empty($application['nmimr_organization']) || !empty($application['nmimr_department'])) {
        $applicationType = 'nmimr';
    } elseif (!empty($application['institution_name']) || !empty($application['principal_investigator'])) {
        $applicationType = 'non_nmimr';
    }

    // Store application data for use in HTML display
    $app_data = $application;
    $app_reviews = $reviews;
    $app_documents = $documents;
    $app_type = $applicationType;
} catch (PDOException $e) {
    error_log(__FILE__ . " - Database error: " . $e->getMessage());
    $error_message = 'Database error: ' . $e->getMessage();
}

// Set default values for new studies
if (!$is_edit) {
    $ref_number = 'NR' . $study_number;
    $exp_date = date('Y-m-d', strtotime('+1 year'));
}

// Function to get status badge color
function getStatusBadgeColor($status)
{
    $status = strtolower($status);
    $colors = [
        'active' => 'success',
        'approved' => 'success',
        'open' => 'success',
        'pending' => 'warning',
        'review' => 'warning',
        'completed' => 'info',
        'closed' => 'secondary',
        'terminated' => 'danger',
        'suspended' => 'danger'
    ];
    return $colors[$status] ?? 'secondary';
}

// Function to sanitize output
function esc($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Function to format file size from bytes to human-readable format
function formatFileSize($bytes)
{
    if ($bytes == 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

error_log("Institution Name for Study Initialization: " . $institution_name);

?>
<!-- New Study Input Form Content -->
<div class="content-wrapper p-4">
    <!-- Page Header -->
    <div class="content-header">
        <div class="page-header-card d-flex">
            <div class="header-icon-wrapper">
                <i class="fas fa-file-medical-alt"></i>
            </div>
            <div class="header-content">
                <h4 class="page-title"> New Study</h4>
                <p class="page-subtitle">Research protocol for IRB review</p>
            </div>
        </div>
    </div>

    <div class="content-body">
        <form class="needs-validation" id="studyForm" enctype="multipart/form-data" novalidate>

            <div class="container-fluid">
                <div id="addStudy" class="new-study-form">
                    <!-- Error/Success Messages -->
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo esc($_SESSION['error_message']);
                            unset($_SESSION['error_message']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo esc($_SESSION['success_message']);
                            unset($_SESSION['success_message']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Stepper Sidebar -->
                    <div class="row">
                        <!-- Left Sidebar - Stepper -->
                        <div class="col-lg-3 mb-4 mb-lg-0">
                            <div class="stepper-sidebar card border-0 shadow-sm h-100 sticky-top" style="top: 20px;">
                                <div class="card-body p-4">
                                    <h5 class="fw-semibold mb-4 text-dark">
                                        <i class="fas fa-list-ol me-2 text-primary"></i>Form Sections
                                    </h5>

                                    <div class="stepper-vertical">
                                        <!-- Step 1: Study Information -->
                                        <div class="step active" data-step="1">
                                            <div class="step-header d-flex align-items-center mb-2">
                                                <div class="step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                                    1
                                                </div>
                                                <div class="step-title ms-3">
                                                    <h6 class="fw-semibold mb-0">Study Information</h6>
                                                    <small class="text-muted">Basic study details</small>
                                                </div>
                                            </div>
                                            <div class="step-progress ms-4 ps-3">
                                                <div class="step-line"></div>
                                            </div>
                                        </div>

                                        <!-- Step 2: Study Personnel -->
                                        <div class="step" data-step="2">
                                            <div class="step-header d-flex align-items-center mb-2">
                                                <div class="step-number bg-light text-muted border rounded-circle d-flex align-items-center justify-content-center">
                                                    2
                                                </div>
                                                <div class="step-title ms-3">
                                                    <h6 class="fw-semibold mb-0 text-muted">Study Personnel</h6>
                                                    <small class="text-muted">Research team members</small>
                                                </div>
                                            </div>
                                            <div class="step-progress ms-4 ps-3">
                                                <div class="step-line"></div>
                                            </div>
                                        </div>

                                        <!-- Step 3: Study Details -->
                                        <div class="step" data-step="3">
                                            <div class="step-header d-flex align-items-center mb-2">
                                                <div class="step-number bg-light text-muted border rounded-circle d-flex align-items-center justify-content-center">
                                                    3
                                                </div>
                                                <div class="step-title ms-3">
                                                    <h6 class="fw-semibold mb-0 text-muted">Study Details</h6>
                                                    <small class="text-muted">Sponsor, status & type</small>
                                                </div>
                                            </div>
                                            <div class="step-progress ms-4 ps-3">
                                                <div class="step-line"></div>
                                            </div>
                                        </div>

                                        <!-- Step 4: IRB Information -->
                                        <div class="step" data-step="4">
                                            <div class="step-header d-flex align-items-center mb-2">
                                                <div class="step-number bg-light text-muted border rounded-circle d-flex align-items-center justify-content-center">
                                                    4
                                                </div>
                                                <div class="step-title ms-3">
                                                    <h6 class="fw-semibold mb-0 text-muted">IRB Information</h6>
                                                    <small class="text-muted">Review & renewal dates</small>
                                                </div>
                                            </div>
                                            <div class="step-progress ms-4 ps-3">
                                                <div class="step-line"></div>
                                            </div>
                                        </div>

                                        <!-- Step 5: Documents -->
                                        <div class="step" data-step="5">
                                            <div class="step-header d-flex align-items-center mb-2">
                                                <div class="step-number bg-light text-muted border rounded-circle d-flex align-items-center justify-content-center">
                                                    5
                                                </div>
                                                <div class="step-title ms-3">
                                                    <h6 class="fw-semibold mb-0 text-muted">Documents</h6>
                                                    <small class="text-muted">Upload study files</small>
                                                </div>
                                            </div>
                                            <div class="step-progress ms-4 ps-3">
                                                <div class="step-line"></div>
                                            </div>
                                        </div>

                                        <!-- Step 6: Review -->
                                        <div class="step" data-step="6">
                                            <div class="step-header d-flex align-items-center">
                                                <div class="step-number bg-light text-muted border rounded-circle d-flex align-items-center justify-content-center">
                                                    6
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
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 16.67%" id="stepperProgress"></div>
                                    </div>
                                    <div class="text-center mt-2">
                                        <small class="text-muted">Step <span id="currentStep">1</span> of 6</small>
                                    </div>

                                    <!-- Navigation Buttons -->
                                    <div class="stepper-navigation mt-4 d-none d-lg-block">
                                        <button type="button" class="btn btn-outline-secondary w-100 mb-2" id="prevStepBtn" disabled>
                                            <i class="fas fa-arrow-left me-2"></i>Previous
                                        </button>
                                        <button type="button" class="btn btn-primary w-100" id="nextStepBtn">
                                            <span class="spinner-container" style="display:none;">
                                                <span class="spinner-border spinner-border-sm" role="status"></span>
                                                <span class="button-text">Saving...</span>
                                            </span>
                                            <span class="button-text">Next <i class="fas fa-arrow-right ms-2"></i></span>
                                        </button>
                                        <button type="submit" class="btn btn-success w-100" style="display:none;">
                                            <i class="fas fa-save me-1"></i>
                                            <?php echo $is_edit ? 'Update Study' : 'Save Study'; ?>
                                        </button>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Content - Form -->
                        <div class="col-lg-9">
                            <form class="needs-validation" id="studyForm" enctype="multipart/form-data" novalidate>
                                <?php
                                // Debug logging for CSRF token generation
                                $token = csrf_token();
                                error_log("=== CSRF FORM DEBUG ===");
                                error_log("CSRF token generated: " . substr($token, 0, 8) . '...');
                                error_log("CSRF token in session: " . (isset($_SESSION['csrf_token']) ? 'set' : 'not set'));
                                error_log("=======================");
                                echo csrf_field();
                                ?>
                                <input type="hidden" name="study_id" id="study_id" value="<?php echo $is_edit ? $study_id : ''; ?>">
                                <input type="hidden" name="action" value="<?php echo $is_edit ? 'update_study' : 'add_study'; ?>">
                                <input type="hidden" name="current_step" id="currentStepField" value="1">

                                <!-- Step 1: Study Information -->
                                <div class="step-content active" data-step="1">
                                    <div class="premium-card mb-4">
                                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0"><i class="fas fa-file-signature me-2"></i>Study Information</h5>
                                                <p class="text-white mb-0 small">Step 1 of 6 - Basic study information</p>
                                            </div>
                                            <span class="badge bg-primary">Required</span>
                                        </div>
                                        <div class="card-body p-4">
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-semibold required-field">Study Number</label>
                                                    <input type="text" id="study_number" name="study_number" class="form-control"
                                                        value="<?php echo esc($application['protocol_number']); ?>" required>
                                                    <div class="invalid-feedback">Please enter a study number.</div>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-semibold required-field">Reference Number</label>
                                                    <input type="text" id="ref_number" name="ref_number" class="form-control"
                                                        value="<?php echo esc($application['protocol_number']); ?>" readonly required>
                                                    <div class="invalid-feedback">Please enter a reference number.</div>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-semibold required-field">Expiration Date</label>
                                                    <input type="date" id="exp_date" name="exp_date" class="form-control"
                                                         readonly>
                                                    <div class="invalid-feedback">Please select an expiration date.</div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12 mb-3">
                                                    <label class="form-label fw-semibold required-field">Protocol Title</label>
                                                    <input type="text" id="protocol_title" name="protocol_title" class="form-control"
                                                        value="<?php echo esc($application['study_title']); ?>" required>
                                                    <div class="invalid-feedback">Please enter the protocol title.</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 2: Study Personnel (Placeholder - content below) -->
                                <div class="step-content" data-step="2">
                                    <div class="premium-card mb-4">
                                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Study Personnel</h5>
                                                <p class="mb-0 opacity-75 small">Step 2 of 6 - Research team members</p>
                                            </div>
                                            <button type="button" class="btn btn-md bg-success text-white" data-bs-target="#addPersonnel" data-bs-toggle="modal"><i class="fas fa-user-circle-plus"></i>Add Personnel</button>
                                        </div>


                                        <!-- Right Content - Form -->
                                        <div class="col-lg-12">
                                            <?php
                                            // Debug logging for CSRF token generation
                                            $token = csrf_token();
                                            error_log("=== CSRF FORM DEBUG ===");
                                            error_log("CSRF token generated: " . substr($token, 0, 8) . '...');
                                            error_log("CSRF token in session: " . (isset($_SESSION['csrf_token']) ? 'set' : 'not set'));
                                            error_log("=======================");
                                            echo csrf_field();
                                            ?>
                                            <input type="hidden" name="study_id" value="<?php echo $is_edit ? $study_id : ''; ?>">
                                            <input type="hidden" name="action" value="<?php echo $is_edit ? 'update_study' : 'add_study'; ?>">
                                            <input type="hidden" name="current_step" id="currentStepField" value="1">
                                            <table class="table table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Role</th>
                                                        <th>Title</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="personnel-table">
                                                    <?php if ($is_edit && !empty($personnel_data)): ?>
                                                        <?php foreach ($personnel_data as $index => $person): ?>
                                                            <tr data-personnel-id="<?php echo esc($person['id']); ?>">
                                                                <td><?php echo esc($person['name']); ?></td>
                                                                <td><span class="badge bg-secondary"><?php echo esc($person['role']); ?></span></td>
                                                                <td><?php echo esc($person['title']); ?></td>
                                                                <td>
                                                                    <div class="btn-group btn-group-sm" role="group">
                                                                        <button type="button" class="btn btn-outline-primary edit-personnel"
                                                                            data-index="<?php echo $index; ?>">
                                                                            <i class="fas fa-edit"></i>
                                                                        </button>
                                                                        <button type="button" class="btn btn-outline-danger delete-personnel"
                                                                            data-index="<?php echo $index; ?>">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </div>
                                                                    <input type="hidden" name="personnel[<?php echo ($person['id']); ?>]"
                                                                        value='<?php echo json_encode($person); ?>'>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="4" class="text-center justify-content-center text-muted py-4">
                                                                <div class="text-center p-5">
                                                                    <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                                                    No personnel added yet
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 3: Study Details -->
                                <div class="step-content" data-step="3">
                                    <div class="premium-card mb-4">
                                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Study Details</h5>
                                                <p class="text-white mb-0 small">Step 3 of 6 - Sponsor, status & type</p>
                                            </div>
                                        </div>
                                        <div class="card-body p-4">
                                            <div class="row mb-3">
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold required-field">Sponsor</label>
                                                    <div class="input-group">
                                                        <select id="sponsor" name="sponsor" class="form-select" required>
                                                            <option value="">Select Sponsor</option>
                                                            <?php foreach ($dropdown_data['sponsors'] as $s): ?>
                                                                <option value="<?php echo esc($s); ?>"
                                                                    <?php echo $s == $sponsor ? 'selected' : ''; ?>>
                                                                    <?php echo esc($s); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addSponsor">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                    <div class="invalid-feedback">Please select a sponsor.</div>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold required-field">Status</label>
                                                    <select id="status" name="status" class="form-select" required>
                                                        <?php foreach ($dropdown_data['study_statuses'] as $status_option): ?>
                                                            <option value="<?php echo esc($status_option); ?>"
                                                                <?php echo $status_option == $status ? 'selected' : ''; ?>>
                                                                <?php echo esc($status_option); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold required-field">Active</label>
                                                    <select id="actv" name="actv" class="form-select" required>
                                                        <option value="Open" <?php echo $active == 'Open' ? 'selected' : ''; ?>>Open</option>
                                                        <option value="Closed" <?php echo $active == 'Closed' ? 'selected' : ''; ?>>Closed</option>
                                                        <option value="External" <?php echo $active == 'External' ? 'selected' : ''; ?>>External</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold required-field">Type</label>
                                                    <select id="review_type" name="review_type" class="form-select" required>
                                                        <?php foreach ($dropdown_data['study_types'] as $type): ?>
                                                            <option value="<?php echo esc($type); ?>"
                                                                <?php echo $type == $review_type ? 'selected' : ''; ?>>
                                                                <?php echo esc(ucwords(str_replace('_', ' ', $type))); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold">Risk Category</label>
                                                    <select id="riskCat" name="riskCat" class="form-select">
                                                        <option value="">Select Risk Category</option>
                                                        <?php foreach ($dropdown_data['risk_categories'] as $category): ?>
                                                            <option value="<?php echo esc($category); ?>"
                                                                <?php echo $category == $risk_category ? 'selected' : ''; ?>>
                                                                <?php echo esc($category); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <!-- <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Approved Enrollment</label>
                                                    <input id="ape" name="ape" type="number" class="form-control" min="0"
                                                        placeholder="Enter number" value="">
                                                </div> -->
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Currently Enrolled</label>
                                                    <input id="currentEnroll" name="currentEnroll" type="number" class="form-control" min="0"
                                                        value="<?php echo esc($current_enrolled); ?>">
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold">IRB of Record</label>
                                                    <input id="ior" name="ior" type="text" class="form-control bg-light"
                                                        value="<?php echo esc($irb_of_record); ?>" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <!-- Step 4: IRB Information -->
                                <div class="step-content" data-step="4">
                                    <div class="premium-card mb-4">
                                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>IRB Information</h5>
                                                <p class="text-white mb-0 small">Step 4 of 6 - IRB review & renewal information</p>
                                            </div>
                                        </div>
                                        <div class="card-body p-4">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Renewal Cycle (Months)</label>
                                                    <select id="rcm" name="rcm" class="form-select">
                                                        <?php for ($i = 6; $i <= 24; $i++): ?>
                                                            <option value="<?php echo $i; ?>"
                                                                <?php echo $i == $renewal_cycle ? 'selected' : ''; ?>>
                                                                <?php echo $i; ?> months
                                                            </option>
                                                        <?php endfor; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold required-field">Date Received</label>
                                                    <input id="date_received" name="date_received" type="date" class="form-control"
                                                        value="<?php echo esc($date_received); ?>" required>
                                                    <div class="invalid-feedback">Please select the date received.</div>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">First IRB Review</label>
                                                    <input id="first_irb_review" name="first_irb_review" type="date" class="form-control"
                                                        value="<?php echo esc($first_irb_review); ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Original Approval</label>
                                                    <input id="original_approval" name="original_approval" type="date" class="form-control"
                                                        value="<?php echo esc($original_approval); ?>">
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Last Seen By IRB</label>
                                                    <input id="last_seen_by_irb" name="last_seen_by_irb" type="date" class="form-control"
                                                        value="<?php echo esc($last_seen_by_irb); ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Last IRB Renewal</label>
                                                    <input id="last_irb_renewal" name="last_irb_renewal" type="date" class="form-control"
                                                        value="<?php echo esc($last_irb_renewal); ?>">
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <div class="card border-primary">
                                                        <div class="card-body text-center p-2">
                                                            <h5 class="card-title text-primary mb-1" id="saeCount">0</h5>
                                                            <p class="card-text text-muted small mb-0">SAEs</p>
                                                            <button type="button" class="btn btn-sm btn-outline-primary mt-2"
                                                                data-bs-target="#addSAE" data-bs-toggle="modal">
                                                                View/Add SAEs
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="card border-info">
                                                        <div class="card-body text-center p-2">
                                                            <h5 class="card-title text-info mb-1" id="cpaCount">0</h5>
                                                            <p class="card-text text-muted small mb-0">CPAs</p>
                                                            <button type="button" class="btn btn-sm btn-outline-info mt-2"
                                                                data-bs-target="#addCPA" data-bs-toggle="modal">
                                                                View/Add CPAs
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold">Internal Notes</label>
                                                    <textarea id="internal_notes" name="internal_notes" class="form-control" rows="3"
                                                        placeholder="Enter internal notes..."><?php echo esc($internal_notes); ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 5: Documents -->
                                <div class="step-content" data-step="5">
                                    <!-- Documents Section -->
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="premium-card">
                                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0 fw-bold"><i class="fas fa-file-alt me-2"></i>Study Documents</h6>
                                                    <span class="badge bg-warning">Optional</span>
                                                </div>
                                                <div class="card-body p-4">
                                                    <!-- File Upload Area -->
                                                    <div class="file-upload-area mb-4" id="fileDropArea">
                                                        <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                                        <h5>Drag & Drop Documents Here</h5>
                                                        <p class="text-muted mb-3">or click to browse files</p>
                                                        <button type="button" class="btn btn-primary" onclick="document.getElementById('fileInput').click();">
                                                            <i class="fas fa-upload me-2"></i>Browse Files
                                                        </button>
                                                        <input type="file" id="fileInput" name="documents[]" multiple style="display: none;"
                                                            accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                                        <p class="small text-muted mt-2 mb-0">Max file size: 10MB. Supported: PDF, DOC, XLS, JPG, PNG</p>
                                                    </div>

                                                    <!-- Uploaded Files Table -->
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-premium">
                                                            <thead class="table-primary">
                                                                <tr>
                                                                    <th>File Name</th>
                                                                    <th>Size</th>
                                                                    <th>Type</th>
                                                                    <th>Comments</th>
                                                                    <th>Exclude from Agenda</th>
                                                                    <th>Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="documents-tbody">
                                                                <?php if (!empty($documents)): ?>
                                                                    <?php foreach ($documents as $doc): ?>
                                                                        <tr data-document-id="<?php echo esc($doc['id']); ?>">
                                                                            <td>
                                                                                <i class="fas fa-file me-2"></i>
                                                                                <?php echo esc($doc['file_name'] ?? ''); ?>
                                                                            </td>
                                                                            <td><?php echo formatFileSize($doc['file_size'] ?? 0); ?></td>
                                                                            <td><span class="badge bg-secondary"><?php echo esc(pathinfo($doc['file_name'] ?? '', PATHINFO_EXTENSION)); ?></span></td>
                                                                            <td>
                                                                                <input type="text" class="form-control form-control-sm"
                                                                                    name="doc_comments[<?php echo esc($doc['id']); ?>]"
                                                                                    value="<?php echo esc($doc['comments']); ?>"
                                                                                    placeholder="Add comments">
                                                                            </td>
                                                                            <td class="text-center">
                                                                                <input type="checkbox" class="form-check-input"
                                                                                    name="exclude_from_agenda"
                                                                                    <?php echo $doc['id'] ? 'checked' : ''; ?>>
                                                                            </td>
                                                                            <td>
                                                                                <div class="btn-group btn-group-sm" role="group">
                                                                                    <a href="<?php echo esc($doc['file_path']); ?>" class="btn btn-outline-primary" target="_blank">
                                                                                        <i class="fas fa-eye"></i>
                                                                                    </a>
                                                                                    <a href="<?php echo esc($doc['file_path']); ?>" class="btn btn-outline-success" download>
                                                                                        <i class="fas fa-download"></i>
                                                                                    </a>
                                                                                    <button type="button" class="btn btn-outline-danger delete-document"
                                                                                        data-id="<?php echo esc($doc['id']); ?>">
                                                                                        <i class="fas fa-trash"></i>
                                                                                    </button>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <tr id="no-documents-row">
                                                                        <td colspan="6" class="text-center text-muted py-4">
                                                                            <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                                                            No documents uploaded yet
                                                                        </td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 6: Review & Submit -->
                                <div class="step-content" data-step="6">
                                    <div class="premium-card mb-4">
                                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Review & Submit</h5>
                                                <p class="mb-0 opacity-75 small">Step 6 of 6 - Final verification</p>
                                            </div>
                                            <span class="badge bg-white text-success">Final Step</span>
                                        </div>
                                        <div class="card-body">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <strong>Please review your study information before submitting.</strong>
                                                <p class="mb-0 mt-2">Make sure all required fields are completed and documents are uploaded.</p>
                                            </div>

                                            <div class="row mt-4">
                                                <div class="col-md-6">
                                                    <h6 class="fw-bold">Study Summary</h6>
                                                    <ul class="list-group list-group-flush" id="reviewSummary">
                                                        <li class="list-group-item">
                                                            <strong>Study Number:</strong> <span id="reviewStudyNumber"><?php echo esc($study_number); ?></span>
                                                        </li>
                                                        <li class="list-group-item">
                                                            <strong>Protocol Title:</strong> <span id="reviewProtocolTitle"><?php echo esc($protocol_title); ?></span>
                                                        </li>
                                                        <li class="list-group-item">
                                                            <strong>Sponsor:</strong> <span id="reviewSponsor"><?php echo esc($sponsor); ?></span>
                                                        </li>
                                                        <li class="list-group-item">
                                                            <strong>Status:</strong> <span id="reviewStatus"><?php echo esc($status); ?></span>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6 class="fw-bold">Completion Checklist</h6>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" id="checkStudyInfo" checked>
                                                        <label class="form-check-label" for="checkStudyInfo">
                                                            Study Information completed
                                                        </label>
                                                    </div>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" id="checkPersonnel" checked>
                                                        <label class="form-check-label" for="checkPersonnel">
                                                            Study Personnel added
                                                        </label>
                                                    </div>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" id="checkDetails" checked>
                                                        <label class="form-check-label" for="checkDetails">
                                                            Study Details completed
                                                        </label>
                                                    </div>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" id="checkIRB" checked>
                                                        <label class="form-check-label" for="checkIRB">
                                                            IRB Information completed
                                                        </label>
                                                    </div>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" id="checkDocuments" checked>
                                                        <label class="form-check-label" for="checkDocuments">
                                                            Documents uploaded
                                                        </label>
                                                    </div>
                                                    <div class="form-check mt-3">
                                                        <input class="form-check-input" type="checkbox" id="confirmSubmit">
                                                        <label class="form-check-label fw-bold" for="confirmSubmit">
                                                            I confirm that all information provided is accurate
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>


            </div>



            <!-- Form Actions (moved inside the form but outside step-content) -->
            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                <div>
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Fields marked with <span class="text-danger">*</span> are required
                    </small>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.history.back();">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="saveDraftBtn">
                        <i class="fas fa-save me-2"></i>Save Draft
                    </button>

                </div>
            </div>
        </form>

    </div>
</div>

<!-- Add Sponsor Modal -->
<div id="addSponsor" class="modal fade" tabindex="-1" aria-labelledby="addSponsorLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="addSponsorForm">
                <div class="modal-header sae-header text-white">
                    <h5 class="modal-title"><i class="fas fa-file-contract me-2"></i>Add Sponsor</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="sponsor_name">Sponsor Name</label>
                        <input type="text" class="form-control" id="sponsor_name" name="sponsor_name" placeholder="Enter sponsor name">
                    </div>
                    <div class="form-group mt-3">
                        <label for="sponsor_contact">Contact Information</label>
                        <input type="text" class="form-control" id="sponsor_contact" name="sponsor_contact" placeholder="Enter contact information">
                    </div>
                    <div class="form-group mt-3">
                        <label for="sponsor_email">Email Address</label>
                        <input type="email" class="form-control" id="sponsor_email" name="sponsor_email" placeholder="Enter email address">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="saveSponsorBtn">Save Sponsor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Personnel Modal -->
<div id="addPersonnel" class="modal fade" tabindex="-1" aria-labelledby="addPersonnelLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header sae-header text-white">
                <h5 class="modal-title fw-bold" id="addPersonnelLabel">
                    <i class="fas fa-user-plus me-2"></i>Add Study Personnel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="personnelForm">
                    <?php echo csrf_field(); ?>
                    <div id="personnelFormContent">
                        <!-- Personnel form fields will be added here dynamically -->
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-success" id="savePersonnelBtn">
                    <i class="fas fa-save me-1"></i> Save Personnel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- SAE Modal (simplified for example) -->
<div id="addSAE" class="modal fade" tabindex="-1" aria-labelledby="addSAELabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-sae">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header sae-header">
                <h5 class="modal-title" id="saeModalLabel">
                    <i class="bi bi-clipboard-plus me-2"></i>Add New SAE Report
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Study Information Card -->
            <div class="card study-info-card border-0 rounded-0">
                <div class="card-body py-3">
                    <div class="row">
                        <div class="col-md-3">
                            <small class="text-muted d-block">Study #</small>
                            <strong><?= htmlspecialchars($study_number) ?></strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Study Status</small>
                            <span class="badge bg-warning status-badge"><?= htmlspecialchars($status) ?></span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Active</small>
                            <span class="badge bg-success status-badge"><?= htmlspecialchars($active) ?></span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Expiration Date</small>
                            <strong><?= htmlspecialchars($exp_date) ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form id="saeForm">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="add_sae">
                    <input type="hidden" name="protocol_id" value="<?php echo $study_id; ?>">
                    <!-- Event Details Section -->
                    <div class="mb-4">
                        <h6 class="section-divider">Event Details</h6>
                        <div class="mb-3">
                            <label for="description" class="form-label required-field">Description of Event</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter detailed description of the adverse event" required></textarea>
                            <div class="form-text">Provide a comprehensive description of the event, including symptoms, timing, and severity.</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="eventType" class="form-label">Type of Event</label>
                                <select class="form-select" id="eventType" name="type_of_event" required>
                                    <?php foreach ($sae_types as $sae): ?>
                                        <option value="<?= htmlspecialchars($sae) ?>"><?= htmlspecialchars($sae) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required-field">Follow-up Report?</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <input type="checkbox" class="form-check-input mt-0" id="followUpCheckbox"
                                            onchange="toggleFollowUpReport()">
                                    </span>
                                    <select class="form-select" id="followUpReport" name="follow_up_report" disabled>
                                        <option value=""></option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="other">Other</option>
                                        <option value="final">Final</option>
                                        <option value="none">None</option>
                                    </select>
                                </div>
                            </div>
                        </div>


                        <!-- Report Numbers -->
                        <div class="row g-3 mt-2">
                            <div class="col-md-4">
                                <label class="form-label"></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <input type="checkbox" class="form-check" id="secondarySaeCheckbox" name="secondary_sae" value="1"
                                            onchange="toggleSecondarySAE()">
                                    </span>
                                    <span class="input-group-text">
                                        <label class="form-label">Secondary SAE?</label>
                                    </span>
                                </div>

                            </div>
                            <div class="col-md-4">
                                <label for="originalSae" class="form-label">Original SAE #</label>
                                <input type="text" class="form-control" id="originalSae" name="original_sae_number" placeholder="Enter original SAE number">
                            </div>

                            <div class="col-md-4">
                                <label for="noReport" class="form-label">IND Report #</label>
                                <input type="text" class="form-control" id="noReport" name="ind_report_number" placeholder="Enter report number">
                            </div>
                        </div>

                        <!-- MedWatch Section -->
                        <div class="row g-3 mt-2">
                            <div class="col-md-4">
                                <label class="form-label"></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <input type="checkbox" class="form-check" id="medWatchCheckbox" name="medwatch_report_filed" value="1"
                                            onchange="toggleMedWatchReport()">
                                    </span>
                                    <span class="input-group-text">
                                        <label class="form-label">MedWatch Report Filed?</label>
                                    </span>
                                </div>

                            </div>

                            <div class="col-md-4">
                                <label for="medwatchNumber" class="form-label">MedWatch #</label>
                                <input type="text" class="form-control" id="medwatchNumber" name="medwatch_number" placeholder="Enter MedWatch number">
                            </div>
                            <div class="col-md-4">
                                <label for="internalSae" class="form-label">Internal SAE #</label>
                                <input type="text" class="form-control" id="internalSae" name="internal_sae_number" placeholder="0" disabled>
                            </div>
                        </div>
                    </div>

                    <!-- Patient Information Section -->
                    <div class="mb-4">
                        <h6 class="section-divider">Patient Information</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="age" class="form-label">Age</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="age" name="age" min="0" max="120" placeholder="Age">
                                    <span class="input-group-text">years</span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="sex" class="form-label required-field">Sex</label>
                                <select class="form-select" id="sex" name="sex" required>
                                    <option value="">Select sex</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>

                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="patientId" class="form-label required-field">Patient Identifier</label>
                                <input type="text" class="form-control" id="patientId" name="patient_identifier" placeholder="Patient ID" required>
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label class="form-label"></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <input type="checkbox" class="form-check-input mt-0" id="localEventCheckbox" name="local_event" value="1"
                                            onchange="toggleLocalEvent()">
                                    </span>
                                    <span class="input-group-text">
                                        <label class="form-label required-field">Local Event?</label>
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="location" class="form-label">Location</label>
                                <select type="text" class="form-control" id="location" name="location">
                                    <?php foreach ($locations as $site): ?>
                                        <option value="<?= htmlspecialchars($site) ?>"><?= htmlspecialchars($site) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>


                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label class="form-label"></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <input type="checkbox" class="form-check-input mt-0" id="studyRelatedCheckbox" name="study_related" value="1"
                                            onchange="toggleStudyRelated()">
                                    </span>
                                    <span class="input-group-text">
                                        <label class="form-label">Study Related?</label>
                                    </span>
                                </div>


                            </div>

                            <div class="col-md-6">
                                <label for="patientStatus" class="form-label required-field">Patient Status</label>
                                <select class="form-select" id="patientStatus" name="patient_status" required>
                                    <option value="">Select status</option>
                                    <option value="recovered">Recovered/Resolved</option>
                                    <option value="recovering">Recovering/Resolving</option>
                                    <option value="not-recovered">Not Recovered/Not Resolved</option>
                                    <option value="fatal">Fatal</option>
                                    <option value="unknown">Unknown</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline Section -->
                    <div class="mb-4">
                        <h6 class="section-divider">Timeline</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="eventDate" class="form-label required-field">Date of Event</label>

                                <input type="date" class="form-control" id="eventDate" name="date_of_event" required>

                            </div>

                            <div class="col-md-4">
                                <label for="receivedDate" class="form-label required-field">Date Received</label>

                                <input type="date" class="form-control" id="receivedDate" name="date_received" value="2025-12-02" required>

                            </div>

                            <div class="col-md-4">
                                <label for="piAwareDate" class="form-label">Date PI Aware</label>

                                <input type="date" class="form-control" id="piAwareDate" name="date_pi_aware">

                            </div>
                        </div>

                        <!-- Additional Date Fields -->
                        <div class="row g-3 mt-3">
                            <div class="col-md-6">
                                <label for="signedDate" class="form-label">Date Signed by PI</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <input type="checkbox" class="form-check-input mt-0" id="signedByPI" name="signed_by_pi" value="1"
                                            onchange="toggleSignedByPI()">
                                    </span>
                                    <input type="date" class="form-control" id="signedDate" name="date_signed" disabled>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Additional Details Section -->
                    <div class="mb-4">
                        <h6 class="section-divider">Additional Details</h6>

                        <!-- Radio Options Row 1 -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label"></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <input type="checkbox" class="form-check-input mt-0" id="risksAlteredCheckbox" name="risks_altered" value="1"
                                            onchange="toggleRisksAltered()">
                                    </span>
                                    <span class="input-group-text">
                                        <label class="form-label">Risks Altered?</label>
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label"></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <input type="checkbox" class="form-check-input mt-0" id="consentModifiedCheckbox" name="new_consent_required" value="1"
                                            onchange="toggleConsentModified()">
                                    </span>
                                    <span class="input-group-text">
                                        <label class="form-label">New Consent Required?</label>
                                    </span>

                                </div>


                            </div>


                        </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- CPA Modal (simplified for example) -->
<div id="addCPA" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-file-contract me-2"></i>Add CPA</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>CPA functionality would be implemented here.</p>
            </div>
        </div>
    </div>
</div>



<?php include 'admin/includes/loading_overlay.php' ?>

<script>
    // Global variables from PHP
    var isEdit = <?php echo $is_edit ? 'true' : 'false'; ?>;
    var studyId = <?php echo $study_id ?: 'null'; ?>;

    // Utility function to escape HTML
    function esc(str) {
        return str.replace(/&/g, '&').replace(/</g, '<').replace(/>/g, '>').replace(/"/g, '"');
    }

    // Initialize personnel list at script level scope
    let personnelList = <?php echo $is_edit ? json_encode($personnel_data) : '[]'; ?>;
    // Current selected loader
    let currentLoader = 'spinner';
    let currentPersonnelIndex = null;


    // Function to update personnel table and hidden inputs
    function updatePersonnelTable() {
        const tbody = document.getElementById('personnel-table');
        if (!tbody) return;
        tbody.innerHTML = '';

        if (personnelList.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4"><i class="fas fa-users fa-2x mb-2 d-block"></i>No personnel added yet</td></tr>`;
        } else {
            personnelList.forEach((person, index) => {
                const row = `<tr data-index="${index}">
                <td>${esc(person.name)}</td>
                <td><span class="badge bg-secondary">${esc(person.role)}</span></td>
                <td>${esc(person.title)}</td>
                <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary edit-personnel" data-index="${index}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger delete-personnel" data-index="${index}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
                tbody.innerHTML += row;
            });
        }

        // Clear existing hidden inputs
        const form = document.getElementById('studyForm');
        const existing = form.querySelectorAll('input[name="personnel[]"]');
        existing.forEach(input => input.remove());

        // Add new hidden inputs
        personnelList.forEach(person => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'personnel[]';
            input.value = JSON.stringify(person);
            form.appendChild(input);
        });
    }

    // =====================================================
    // STEPPER FUNCTIONALITY
    // =====================================================

    // Get application ID from hidden field for unique storage key
    function getApplicationStorageKey() {
        const studyIdField = document.getElementById('study_id');
        const studyId = studyIdField ? studyIdField.value : '';
        return 'irb_application_current_step' + (studyId ? '_' + studyId : '');
    }

    // Load step from sessionStorage
    function loadSavedStep() {
        const storageKey = getApplicationStorageKey();
        const savedStep = sessionStorage.getItem(storageKey);
        if (savedStep) {
            const stepNum = parseInt(savedStep, 10);
            // Validate step is a number and within reasonable range (1-10)
            if (!isNaN(stepNum) && stepNum >= 1 && stepNum <= 10) {
                return stepNum;
            }
        }
        return 1;
    }

    // Save step to sessionStorage
    function saveStepToStorage(step) {
        const storageKey = getApplicationStorageKey();
        sessionStorage.setItem(storageKey, step.toString());
    }

    // Clear saved step from sessionStorage
    function clearSavedStep() {
        const storageKey = getApplicationStorageKey();
        sessionStorage.removeItem(storageKey);
    }

    let currentStep = loadSavedStep();
    const totalSteps = 6;
    let isSubmitting = false;


    // Initialize stepper
    function initStepper() {
        const prevBtn = document.getElementById('prevStepBtn');
        const nextBtn = document.getElementById('nextStepBtn');
        const saveDraftBtn = document.getElementById('saveDraftBtn');

        // Previous button
        if (prevBtn) {
            prevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (currentStep > 1) {
                    goToStep(currentStep - 1);
                }
            });
        }

        // Next button
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
                } else {
                    // Final step - submit form
                    if (!validateStep(currentStep)) {
                        return;
                    }
                    submitStudyForm(e);
                }
            });
        }

        // Save draft button
        if (saveDraftBtn) {
            saveDraftBtn.addEventListener('click', function(e) {
                e.preventDefault();
                saveDraft();
            });
        }

        // Add click handler to step indicators for navigation
        const stepElements = document.querySelectorAll('.step');
        stepElements.forEach(stepEl => {
            stepEl.addEventListener('click', function(e) {
                e.preventDefault();
                const stepNumber = parseInt(this.dataset.step);

                if (this.classList.contains('completed')) {
                    // Navigate to completed step
                    goToStep(stepNumber);
                } else if (this.classList.contains('active')) {
                    // Already on this step - do nothing
                    return;
                } else {
                    // Future step - show message or do nothing
                    showToast('info', 'Please complete the current step before moving forward.');
                }
            });
        });

        // Initialize step indicators
        updateStepIndicators();
        
        // Update content visibility for the loaded step
        const stepContents = document.querySelectorAll('.step-content');
        stepContents.forEach(content => {
            const contentStep = parseInt(content.dataset.step);
            content.classList.toggle('active', contentStep === currentStep);
        });
        
        // Update current step field with loaded value
        const currentStepField = document.getElementById('currentStepField');
        if (currentStepField) {
            currentStepField.value = currentStep;
        }
        
        updateProgressBar();
        updateNavigationButtons();
    }

    // Go to specific step
    function goToStep(step) {
        if (step < 1 || step > totalSteps) return;

        currentStep = step;

        // Save step to sessionStorage for persistence
        saveStepToStorage(currentStep);

        // Update step indicators
        updateStepIndicators();

        // Update content visibility
        const stepContents = document.querySelectorAll('.step-content');
        stepContents.forEach(content => {
            const contentStep = parseInt(content.dataset.step);
            content.classList.toggle('active', contentStep === currentStep);
        });

        // Update progress bar
        updateProgressBar();

        // Update current step field
        const currentStepField = document.getElementById('currentStepField');
        if (currentStepField) {
            currentStepField.value = currentStep;
        }

        // Scroll to top
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });

        // Update navigation buttons
        updateNavigationButtons();
    }

    // Update step indicators styling
    function updateStepIndicators() {

        const steps = document.querySelectorAll('.step');
        steps.forEach((stepEl, index) => {
            const stepNum = index + 1;
            const stepNumber = stepEl.querySelector('.step-number');
            const stepTitle = stepEl.querySelector('.step-title h6');



            if (stepNum === currentStep) {
                // Current active step
                stepEl.classList.add('active');
                stepEl.classList.remove('completed');
                stepNumber.classList.remove('bg-light', 'text-muted', 'border', 'bg-success');
                stepNumber.classList.add('bg-primary', 'text-white');
                stepEl.style.cursor = 'default';
                if (stepTitle) {
                    stepTitle.classList.remove('text-muted', 'text-success');
                    stepTitle.classList.add('text-dark');
                }
            } else if (stepNum < currentStep) {
                // Completed steps - green styling
                stepEl.classList.remove('active');
                stepEl.classList.add('completed');
                stepNumber.classList.remove('bg-light', 'text-muted', 'border', 'bg-primary');
                stepNumber.classList.add('bg-success', 'text-white');
                stepEl.style.cursor = 'pointer';
                if (stepTitle) {
                    stepTitle.classList.remove('text-muted', 'text-dark');
                    stepTitle.classList.add('text-success');
                }
            } else {
                // Future steps
                stepEl.classList.remove('active', 'completed');
                stepNumber.classList.remove('bg-primary', 'bg-success', 'text-white');
                stepNumber.classList.add('bg-light', 'text-muted', 'border');
                stepEl.style.cursor = 'not-allowed';
                if (stepTitle) {
                    stepTitle.classList.remove('text-dark', 'text-success');
                    stepTitle.classList.add('text-muted');
                }
            }
        });
    }

    // Update progress bar
    function updateProgressBar() {
        const progressBar = document.getElementById('stepperProgress');
        const currentStepSpan = document.getElementById('currentStep');

        if (progressBar) {
            const progress = (currentStep / totalSteps) * 100;
            progressBar.style.width = progress + '%';
        }

        if (currentStepSpan) {
            currentStepSpan.textContent = currentStep;
        }
    }

    // Update navigation buttons state
    function updateNavigationButtons() {
        const prevBtn = document.getElementById('prevStepBtn');
        const nextBtn = document.getElementById('nextStepBtn');

        // Use global PHP variables for edit mode
        const isEditMode = isEdit === true || (studyId !== null && studyId !== '');

        if (prevBtn) {
            prevBtn.disabled = currentStep === 1;
        }

        if (nextBtn) {
            const buttonText = nextBtn.querySelector('.button-text');
            const submitBtn = document.querySelector('button[type="submit"]');
            const navBtn = document.getElementById('nextStepBtn');
            if (currentStep === totalSteps) {
                if (buttonText) {
                    // Check edit mode and set appropriate button text
                    const submitText = isEditMode ? 'Update Study' : 'Submit Study';
                    buttonText.innerHTML = submitText + ' <i class="fas fa-check ms-2"></i>';
                }

                console.log("Last step reached");
                navBtn.style.display = 'none';
                submitBtn.style.display = 'block';

                nextBtn.classList.remove('btn-primary');
                nextBtn.classList.add('btn-success');
            } else {
                if (buttonText) {
                    buttonText.innerHTML = 'Next <i class="fas fa-arrow-right ms-2"></i>';
                }
                nextBtn.classList.add('btn-primary');
                nextBtn.classList.remove('btn-success');
            }
        }
    }

    // Validate current step
    function validateStep(stepIndex) {
        // Get the current step content
        const currentStepContent = document.querySelector('.step-content[data-step="' + stepIndex + '"]');
        if (!currentStepContent) return true;

        // Find all required fields in current step
        const requiredFields = currentStepContent.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(function(field) {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('is-invalid');
                field.classList.remove('is-valid');
            } else {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
            }
        });

        if (!isValid) {
            showToast('error', 'Please fill in all required fields for this step');
        }

        return isValid;
    }

    // Next step function
    async function nextStep() {
        if (isSubmitting) return;

        isSubmitting = true;
        const nextBtn = document.getElementById('nextStepBtn');

        if (!nextBtn) {
            console.error('Next button not found');
            isSubmitting = false;
            return;
        }

        // Show saving indicator
        if (nextBtn) {
            const spinnerContainer = nextBtn.querySelector('.spinner-container');
            const buttonTexts = nextBtn.querySelectorAll('.button-text');
            if (spinnerContainer) {
                spinnerContainer.style.display = 'inline-flex';
            }
            buttonTexts.forEach(function(text) {
                text.style.display = 'none';
            });
        }

        try {
            // Save current step
            await saveDraft(true);

            // Move to next step
            if (currentStep < totalSteps) {
                goToStep(currentStep + 1);
            }
        } catch (error) {
            console.error('Error saving step:', error);
            showToast('error', 'Error saving draft. Please try again.');
        } finally {
            isSubmitting = false;
            // Reset button (isSubmitting is reset by saveDraft)
            if (nextBtn) {
                const spinnerContainer = nextBtn.querySelector('.spinner-container');
                const buttonTexts = nextBtn.querySelectorAll('.button-text');
                if (spinnerContainer) {
                    spinnerContainer.style.display = 'none';
                }
                buttonTexts.forEach(function(text) {
                    text.style.display = 'inline';
                });
            }
        }
    }

    // Save draft function
    async function saveDraft(isFromNextStep = false) {
        if (isSubmitting && !isFromNextStep) return;

        if (!isFromNextStep) {
            isSubmitting = true;
        }
        const form = document.getElementById('studyForm');
        const saveDraftBtn = document.getElementById('saveDraftBtn');

        if (!form) {
            console.error('Study form not found');
            showToast('error', 'Form not found. Please refresh the page.');
            isSubmitting = false;
            return;
        }

        // Show saving indicator on draft button
        if (saveDraftBtn) {
            var originalText = saveDraftBtn.innerHTML;
            saveDraftBtn.disabled = true;
            saveDraftBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Saving...';

            try {
                var formData = new FormData(form);
                formData.append('action', 'save_draft');
                formData.append('current_step', currentStep);

                var response = await fetch('/admin/handlers/add_study_handler.php', {
                    method: 'POST',
                    body: formData
                });

                var data = await response.json();

                // Check for success - accept both 'status' and 'success' flags
                if (data.status === 'success' || data.success === true) {
                    // Update study_id if new
                    var studyIdField = form.querySelector('input[name="study_id"]');
                    if (studyIdField && data.study_id) {
                        studyIdField.value = data.study_id;
                    }
                    showToast('success', data.message || 'Draft saved successfully');
                } else {
                    // Only show error message if there's an actual error
                    var errorMsg = data.message || 'Error saving draft';
                    // Don't show technical error details to users
                    if (errorMsg.includes('active transaction') || errorMsg.includes('SQLSTATE')) {
                        errorMsg = 'Error saving draft. Please try again.';
                    }
                    showToast('error', errorMsg);
                }
            } catch (error) {
                console.error('Save draft error:', error);
                showToast('error', 'Error saving draft. Please try again.');
            } finally {
                saveDraftBtn.disabled = false;
                saveDraftBtn.innerHTML = originalText;
            }
        }

        isSubmitting = false;
    }

    // Initialize stepper when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initStepper();
    });

    // =====================================================
    // EXISTING CODE
    // =====================================================

    // =====================================================
    // SAVE SPONSOR FUNCTIONALITY
    // =====================================================
    document.getElementById('addSponsorForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveSponsor();
    });

    function saveSponsor() {
        const sponsorNameInput = document.querySelector('input[name="sponsor_name"]');
        const sponsorContactInput = document.querySelector('input[name="sponsor_contact"]');
        const sponsorEmailInput = document.querySelector('input[name="sponsor_email"]');
        const sponsorEmail = sponsorEmailInput.value.trim() || ""; // Set to null if empty
        const sponsorName = sponsorNameInput.value.trim();
        const sponsorContact = sponsorContactInput.value.trim() || ""; // Set to null if empty

        const sponsorBtn = document.getElementById('saveSponsorBtn');
        sponsorBtn.disabled = true;

        // Add spinner to button
        const originalBtnContent = sponsorBtn.innerHTML;
        sponsorBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Saving...';


        if (sponsorName === '') {
            showToast('error', 'Sponsor name cannot be empty');
            return;
        }

        // Add sponsor to server via AJAX
        fetch('/admin/handlers/add_sponsor.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                sponsor_name: sponsorName,
                sponsor_contact: sponsorContact,
                sponsor_email: sponsorEmail
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear input and close modal
                sponsorNameInput.value = '';
                const addSponsorModal = bootstrap.Modal.getInstance(document.getElementById('addSponsor'));
                addSponsorModal.hide();
            } else {
                showToast('error', data.message || 'Error adding sponsor');
            }
        })
        .catch(error => {
            console.error('Error adding sponsor:', error);
            showToast('error', 'Error adding sponsor. Please try again.');
        });

        // Show in select sponsor field immediately
        const sponsorSelect = document.querySelector('select[name="sponsor"]');
        const newOption = document.createElement('option');
        newOption.value = sponsorName;
        newOption.textContent = sponsorName;
        sponsorSelect.appendChild(newOption);
        sponsorSelect.value = sponsorName;

    }
  

    document.addEventListener('DOMContentLoaded', function() {
        // Use global variables isEdit and studyId
        // Update table on load
        updatePersonnelTable();

        // Load user draft if not in edit mode
        // if (!isEdit) {
        //     loadUserDraft();
        // }

        // Initialize Bootstrap form validation
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                showLoadingOverlay();
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                } else {
                    // Form is valid - clear saved step from sessionStorage
                    clearSavedStep();
                }
                form.classList.add('was-validated');
            }, false);
        });

        // Auto-populate reference number based on study number
        const studyNumberInput = document.getElementById('study_number');
        const refNumberInput = document.getElementById('ref_number');
        if (studyNumberInput && refNumberInput) {
            studyNumberInput.addEventListener('input', function() {
                refNumberInput.value = 'NR' + this.value;
                refNumberInput.setAttribute('value', 'NR' + this.value);
            });
        }

        // File upload handling
        const fileInput = document.getElementById('fileInput');
        const fileDropArea = document.getElementById('fileDropArea');
        const documentsTbody = document.getElementById('documents-tbody');

        // Drag and drop functionality - only if elements exist
        if (fileDropArea) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                fileDropArea.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                fileDropArea.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                fileDropArea.addEventListener(eventName, unhighlight, false);
            });

            function highlight() {
                fileDropArea.classList.add('dragover');
            }

            function unhighlight() {
                fileDropArea.classList.remove('dragover');
            }

            fileDropArea.addEventListener('drop', handleDrop, false);
        }

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files);
        }

        if (fileInput) {
            fileInput.addEventListener('change', function() {
                handleFiles(this.files);
            });
        }

        function handleFiles(files) {
            if (files.length === 0) return;

            // Remove "no documents" row if present
            const noDocsRow = document.getElementById('no-documents-row');
            if (noDocsRow) noDocsRow.remove();

            Array.from(files).forEach(file => {
                if (file.size > 10 * 1024 * 1024) {
                    showToast('error', `File ${file.name} exceeds 10MB limit`);
                    return;
                }

                const validTypes = ['.pdf', '.doc', '.docx', '.xls', '.xlsx', '.jpg', '.jpeg', '.png'];
                const fileExt = '.' + file.name.split('.').pop().toLowerCase();
                if (!validTypes.includes(fileExt)) {
                    showToast('error', `File type ${fileExt} not allowed for ${file.name}`);
                    return;
                }

                const row = document.createElement('tr');
                row.innerHTML = `
                <td><i class="fas fa-file me-2"></i>${file.name}</td>
                <td>${formatFileSize(file.size)}</td>
                <td><span class="badge bg-secondary">${fileExt.substring(1)}</span></td>
                <td><input type="text" class="form-control form-control-sm" name="new_doc_comments[]" placeholder="Add comments"></td>
                <td class="text-center"><input type="checkbox" class="form-check-input" name="new_exclude_from_agenda[]"></td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
                documentsTbody.appendChild(row);
            });

            // Clear file input
            fileInput.value = '';
        }

        // Personnel table actions
        const personnelTable = document.getElementById('personnel-table');
        if (personnelTable) {
            personnelTable.addEventListener('click', e => {
                const btn = e.target.closest('button');
                if (!btn) return;

                const index = btn.dataset.index;

                if (btn.classList.contains('edit-personnel')) {
                    editPersonnel(index);
                }

                if (btn.classList.contains('delete-personnel')) {
                    deletePersonnel(index);
                }
            });
        }



        // Document deletion
        if (documentsTbody) {
            documentsTbody.addEventListener('click', function(e) {
                if (e.target.closest('.delete-document')) {
                    const documentId = e.target.closest('.delete-document').dataset.id;
                    const row = e.target.closest('tr');
                    deleteDocument(documentId, row);
                }
            });
        }

        // Add Personnel Modal
        const addPersonnelTrigger = document.querySelector('[data-bs-target="#addPersonnel"]');
        if (addPersonnelTrigger) {
            addPersonnelTrigger.addEventListener('click', () => {
                currentPersonnelIndex = null;
                setAddMode();
            });
        }



        // Save Personnel button
        const savePersonnelBtn = document.getElementById('savePersonnelBtn');
        if (savePersonnelBtn) {
            savePersonnelBtn.addEventListener('click', savePersonnel);
        }

        // Form submission
        const studyForm = document.getElementById('studyForm');
        if (studyForm) {
            studyForm.addEventListener('submit', submitStudyForm);
        }
    });

    function setAddMode() {
        document.getElementById('addPersonnelLabel').innerHTML =
            '<i class="fas fa-user-plus me-2"></i>Add Study Personnel';

        loadPersonnelForm(); // empty form
    }


    // Utility functions
    function showToast(type, message) {
        // Check if toast container exists
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '1060';
            document.body.appendChild(toastContainer);
        }

        // Create toast
        const toastId = 'toast-' + Date.now();
        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');

        toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        // Remove toast after hidden
        toast.addEventListener('hidden.bs.toast', function() {
            toast.remove();
        });
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function editPersonnel(index) {
        currentPersonnelIndex = index;
        const personnel = personnelList[index];

        document.getElementById('addPersonnelLabel').innerHTML =
            '<i class="fas fa-edit me-2"></i>Edit Personnel';

        const formContent = document.getElementById('personnelFormContent');

        formContent.innerHTML = `
        <input type="hidden" name="personnel_index" value="${index}">
        <input type="hidden" name="contact_id" value="${personnel.contact_id || ''}">

        <div class="mb-3">
            <label class="form-label required-field">Name</label>
            <input type="text" class="form-control" name="name"
                   value="${esc(personnel.name || '')}" required>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label required-field">Role</label>
                <select class="form-select" name="role" required>
                   ${<?php echo json_encode($dropdown_data['staffTypes']); ?>.map(type =>
                    `<option value="${type}" ${type === personnel.role ? 'selected' : ''}>${type}</option>` 
                ).join('')}
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Title</label>
                <input type="text" class="form-control" name="title"
                       value="${esc(personnel.title || '')}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Email</label>
                <input class="form-control" name="email" value="${esc(personnel.email || '')}">
            </div>
            <div class="col-md-6">
                <label>Phone</label>
                <input class="form-control" name="phone" value="${esc(personnel.phone || '')}">
            </div>
        </div>
         <div class="mb-3">
             <label class="form-label">Comments</label>
             <textarea class="form-control" name="comments" rows="2">${esc(personnel.comments || "")}</textarea>
         </div>
    `;

        attachAutocomplete(formContent);

        bootstrap.Modal.getOrCreateInstance(
            document.getElementById('addPersonnel')
        ).show();
    }


    function deletePersonnel(index) {
        if (!confirm('Are you sure you want to remove this personnel?')) return;

        personnelList.splice(index, 1);
        updatePersonnelTable();
        showToast('success', 'Personnel removed');
    }

    async function deleteDocument(documentId, row) {
        if (!confirm('Are you sure you want to delete this document?')) return;

        try {
            const formData = new FormData();
            formData.append('action', 'delete_document');
            formData.append('document_id', documentId);

            const response = await fetch('/admin/handlers/delete_document.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.status === 'success') {
                row.remove();
                showToast('success', data.message);
            } else {
                showToast('error', data.message);
            }
        } catch (error) {
            showToast('error', 'Error deleting document');
            console.error('Error:', error);
        }
    }

    function loadPersonnelForm() {
        document.getElementById('addPersonnelLabel').innerHTML = '<i class="fas fa-user-plus me-2"></i>Add Personnel';
        const formContent = document.getElementById('personnelFormContent');
        formContent.innerHTML = `
        <input type="hidden" name="contact_id" hidden>
        <div class="mb-3">
            <label class="form-label required-field">Name</label>
            <input type="text" class="form-control" name="name" required placeholder="Enter full name">
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label required-field">Role</label>
                <select class="form-select" name="role" required>
                    <option value="">Select Role</option>
                    ${<?php echo json_encode($dropdown_data['staffTypes']); ?>.map(type => 
                        `<option value="${type}">${type}</option>`
                    ).join('')}
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Title</label>
                <input type="text" class="form-control" name="title" placeholder="Enter title">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" placeholder="Enter email">
            </div>
            <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="tel" class="form-control" name="phone" placeholder="Enter phone number">
            </div>
        </div>
         <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Company Name</label>
                        <input type="text" class="form-control" name="company_name" >
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" name="start_date" >
                    </div>
                </div>
        <div class="mb-3">
            <label class="form-label">Comments</label>
            <textarea class="form-control" name="comments" rows="2" placeholder="Enter comments"></textarea>
        </div>
    `;

        // Add autocomplete to name input
        attachAutocomplete(formContent);
    }

    function attachAutocomplete(formContent) {
        const nameInput = formContent.querySelector('input[name="name"]');
        const contactId = formContent.querySelector('input[name="contact_id"]').value;

        // console.log("Selected contact id: " + contactId);
        if (!nameInput) return;

        // Remove existing autocomplete container if present
        const oldContainer = nameInput.parentElement.querySelector('.autocomplete-suggestions');
        if (oldContainer) oldContainer.remove();

        // Create container
        const container = document.createElement('div');
        container.className = 'autocomplete-suggestions position-absolute bg-white border rounded shadow-sm d-none';
        container.style.zIndex = '1055';
        container.style.maxHeight = '200px';
        container.style.overflowY = 'auto';

        nameInput.parentElement.style.position = 'relative';
        nameInput.parentElement.appendChild(container);

        let debounceTimer = null;

        nameInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const query = this.value.trim();

            if (query.length < 2) {
                container.classList.add('d-none');
                return;
            }

            debounceTimer = setTimeout(async () => {
                try {
                    const res = await fetch(
                        `/admin/handlers/fetch_contacts.php?q=${encodeURIComponent(query)}`
                    );
                    const json = await res.json();

                    if (json.success) {
                        showPersonnelSuggestions(
                            json.data,
                            container,
                            nameInput,
                            formContent
                        );
                    }
                } catch (err) {
                    console.error('Autocomplete error:', err);
                }
            }, 300);
        });

        nameInput.addEventListener('focus', () => {
            if (nameInput.value.trim().length >= 2) {
                nameInput.dispatchEvent(new Event('input'));
            }
        });

        nameInput.addEventListener('blur', e => {
            if (!container.contains(e.relatedTarget)) {
                setTimeout(() => container.classList.add('d-none'), 150);
            }
        });
    }

    function showPersonnelSuggestions(contacts, container, input, formContent) {
        container.innerHTML = '';

        if (!contacts.length) {
            container.classList.add('d-none');
            return;
        }

        contacts.forEach(contact => {
            const item = document.createElement('div');
            item.className = 'p-2 border-bottom cursor-pointer';
            item.tabIndex = -1;
            item.textContent = contact.name;

            item.addEventListener('mousedown', () => {
                input.value = contact.name;
                const contactId = formContent.querySelector('input[name="contact_id"]');
                const email = formContent.querySelector('input[name="email"]');
                const phone = formContent.querySelector('input[name="phone"]');
                const title = formContent.querySelector('input[name="title"]');

                if (contactId) contactId.value = contact.id || '';
                if (email) email.value = contact.email || '';
                if (phone) phone.value = contact.main_phone || '';
                if (title) title.value = contact.title || '';

                console.log("Selected contact id: " + contactId.value);

                container.classList.add('d-none');
            });

            container.appendChild(item);
        });

        container.classList.remove('d-none');
    }


    function savePersonnel() {
        const form = document.getElementById('personnelForm');
        const fd = new FormData(form);

        const personnel = {
            contact_id: fd.get('contact_id'),
            name: fd.get('name'),
            role: fd.get('role'),
            title: fd.get('title'),
            email: fd.get('email'),
            phone: fd.get('phone'),
            company_name: fd.get('company_name'),
            start_date: fd.get('start_date'),
            comments: fd.get('comments')
        };

        if (currentPersonnelIndex !== null) {
            // EDIT
            personnelList[currentPersonnelIndex] = {
                ...personnelList[currentPersonnelIndex],
                ...personnel
            };
            showToast('success', 'Personnel updated');
        } else {
            // ADD
            personnelList.push(personnel);
            showToast('success', 'Personnel added');
        }

        updatePersonnelTable();

        bootstrap.Modal.getInstance(
            document.getElementById('addPersonnel')
        ).hide();
    }


    async function submitStudyForm(e) {
        e.preventDefault();

        const form = document.getElementById('studyForm');
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            showToast('error', 'Please fill in all required fields');
            return;
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        try {
            updatePersonnelTable();
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

            // Show loading overlay with animation
            showLoadingOverlay();

            const formData = new FormData(form);



            const response = await fetch('/admin/handlers/add_study_handler.php', {
                method: 'POST',
                body: formData
            });

            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers.get('content-type'));

            const responseText = await response.text();
            console.log('Response text:', responseText);

            let data;
            try {
                data = JSON.parse(responseText);
            } catch (e) {
                // Response is not JSON - show the error
                hideLoadingOverlay();
                showToast('error', 'Server error: ' + responseText.substring(0, 200));
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                console.error('Non-JSON response:', responseText);
                return;
            }

            console.log('Response data:', data);

            if (data.status === 'success') {
                hideLoadingOverlay();
                showToast('success', data.message);
                setTimeout(() => {
                    window.location.href = data.redirect || '/dashboard/studies';
                }, 1500);
            } else {
                hideLoadingOverlay();
                showToast('error', data.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        } catch (error) {
            hideLoadingOverlay();
            showToast('error', 'Error: ' + error.message + '. Check console for details.');
            console.error('Error:', error);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }

    // Initialize date pickers with today's date
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date().toISOString().split('T')[0];
        const dateReceived = document.getElementById('date_received');
        if (dateReceived && !dateReceived.value) {
            dateReceived.value = today;
        }
    });

    async function fetchContactsForPersonnel(query, container, input, form) {
        try {
            const response = await fetch(`/admin/handlers/fetch_contacts.php?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            if (data.success) {
                showSuggestionsForPersonnel(data.data, container, input, form);
            }
        } catch (error) {
            console.error('Error fetching contacts:', error);
        }
    }

    function showSuggestionsForPersonnel(contacts, container, input, form) {
        container.innerHTML = '';
        if (contacts.length === 0) {
            container.classList.add('d-none');
            return;
        }
        contacts.forEach(contact => {
            const item = document.createElement('div');
            item.className = 'p-2 border-bottom cursor-pointer';
            item.textContent = contact.name;
            item.addEventListener('mousedown', () => {
                selectContactForPersonnel(contact, input, form);
                container.classList.add('d-none');
            });
            container.appendChild(item);
        });
        container.classList.remove('d-none');
    }

    function selectContactForPersonnel(contact, input, form) {
        input.value = contact.name;
        const contactId = form.querySelector('input[name=contact_id"]');
        const emailInput = form.querySelector('input[name="email"]');
        const phoneInput = form.querySelector('input[name="phone"]');
        const titleInput = form.querySelector('input[name="title"]');
        if (contactId) contactId.value = contact.id || '';
        if (emailInput) emailInput.value = contact.email || '';
        if (phoneInput) phoneInput.value = contact.main_phone || '';
        if (titleInput) titleInput.value = contact.title || '';

        console.log("Contact id: " + contactId);
    }

    // Function to show loading overlay
    function showLoadingOverlay() {
        console.log('[DEBUG] showLoadingOverlay() called');
        console.log('[DEBUG] currentLoader value:', currentLoader);

        // Scroll to top smoothly
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });

        // Get overlay element
        let overlay = document.getElementById('loadingOverlay');

        // If overlay doesn't exist, create it
        if (!overlay) {
            console.log('[DEBUG] Creating overlay element');
            overlay = document.createElement('div');
            overlay.id = 'loadingOverlay';
            overlay.className = 'loading-overlay';
            overlay.innerHTML = `
                <div class="loading-container">
                    <div class="loader-content" id="spinnerLoader" style="display: block;">
                        <div class="spinner-modern">
                            <div class="spinner-circle"></div>
                            <div class="spinner-inner-circle"></div>
                        </div>
                        <div class="loading-text">Processing study request</div>
                        <div class="loading-subtext">Please wait while we save your information</div>
                        <div class="loading-progress">
                            <div class="loading-progress-bar"></div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Move overlay to body to avoid stacking context issues
        if (overlay.parentElement !== document.body) {
            document.body.appendChild(overlay);
        }

        console.log('[DEBUG] overlay element found:', overlay);

        // Show overlay - force inline styles to bypass any CSS issues
        overlay.classList.add('active');
        overlay.style.position = 'fixed';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.width = '100vw';
        overlay.style.height = '100vh';
        overlay.style.background = 'rgba(0, 0, 0, 0.5)'; // Semi-transparent dark
        overlay.style.zIndex = '999999';
        overlay.style.display = 'flex';
        overlay.style.alignItems = 'center';
        overlay.style.justifyContent = 'center';
        overlay.style.opacity = '1';
        overlay.style.visibility = 'visible';
        console.log('[DEBUG] active class added to overlay');

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
        if (overlay) {
            overlay.classList.remove('active');

            // Re-enable body scroll
            document.body.style.overflow = 'auto';

            // Fade out animation
            setTimeout(() => {
                overlay.style.display = 'none';
            }, 300);
        }
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
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>