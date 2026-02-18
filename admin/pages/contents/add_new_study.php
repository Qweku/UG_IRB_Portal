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
    'irb_of_record' => 'NOGUCHI MEMORIAL INSTITUTE FOR MEDICAL RESEARCH-IRB',
    'cr_required' => '',
    'renewal_cycle' => '12',
    'date_received' => date('Y-m-d'),
    'first_irb_review' => '',
    'original_approval' => '',
    'last_seen_by_irb' => '',
    'last_irb_renewal' => '',
    'internal_notes' => '',
    'initial_summary_of_agenda' => ''
];

// Extract variables for easier access
extract($study_vars, EXTR_SKIP);

$is_edit = false;
$study_id = null;
$personnel_data = [];
$documents = [];

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
        throw new Exception("Database connection failed");
    }

    // Fetch dropdown data using helpers
    $dropdown_data['staffTypes'] = getStaffTypes();
    $dropdown_data['sponsors'] = getSponsors();
    $dropdown_data['study_types'] = getReviewTypesList();
    $dropdown_data['study_statuses'] = getStudyStatusesList();
    $dropdown_data['risk_categories'] = getRiskCategoriesList();
    $dropdown_data['sae_types'] = getSAETypesList();
    $dropdown_data['locations'] = getStudyLocationsList();

    // Process contacts
    $allContacts = getAllContacts();

    $contact_list = [];
    foreach ($allContacts as $c) {
        if (!empty($c['first_name']) || !empty($c['last_name'])) {
            $fullName = trim($c['first_name'] . ' ' . ($c['middle_name'] ? $c['middle_name'] . ' ' : '') . $c['last_name']);

            if (!empty($fullName)) {
                $contact_list[] = [
                    'name' => $fullName,
                    'id' => $c['id']
                ];
            }
        }
    }
    $dropdown_data['contacts'] = $contact_list;

    // Log contact list in readable format
    error_log("Contact list: " . implode(', ', array_map(function ($c) {
        return $c['name'] . ' => ' . $c['id'];
    }, $contact_list)));

    // Check for edit mode
    if (isset($_GET['edit']) && $_GET['edit'] == '1' && isset($_GET['id']) && is_numeric($_GET['id'])) {
        $is_edit = true;
        $study_id = (int)$_GET['id'];

        // Fetch study data
        $stmt = $conn->prepare("SELECT * FROM studies WHERE id = ?");
        $stmt->execute([$study_id]);
        $study = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($study) {
            // Map database fields to variables
            $study_mapping = [
                'study_number' => 'protocol_number',
                'ref_number' => 'ref_num',
                'exp_date' => 'expiration_date',
                'protocol_title' => 'title',
                'sponsor' => 'sponsor_displayname',
                'active' => 'study_active',
                'review_type' => 'review_type',
                'status' => 'study_status',
                'risk_category' => 'risk_category',
                'approval_patient_enrollment' => 'patients_enrolled',
                'current_enrolled' => 'init_enroll',
                'on_agenda_date' => 'on_agenda_date',
                'irb_of_record' => 'irb_of_record',
                'cr_required' => 'cr_required',
                'renewal_cycle' => 'renewal_cycle',
                'date_received' => 'date_received',
                'first_irb_review' => 'first_irb_review',
                'original_approval' => 'approval_date',
                'last_seen_by_irb' => 'last_irb_review',
                'last_irb_renewal' => 'last_renewal_date',
                'internal_notes' => 'remarks'
            ];

            foreach ($study_mapping as $var => $field) {
                if (isset($study[$field])) {
                    $$var = $study[$field];
                }
            }

            // Fetch personnel
            $stmt = $conn->prepare("SELECT * FROM study_personnel WHERE study_id = ? ORDER BY id");
            $stmt->execute([$study_id]);
            $personnel_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch documents
            $stmt = $conn->prepare("SELECT * FROM documents WHERE study_id = ? ORDER BY uploaded_at DESC");
            $stmt->execute([$study_id]);
            $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $is_edit = false;
            $_SESSION['error_message'] = 'Study not found';
        }
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['error_message'] = 'Database connection error';
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    $_SESSION['error_message'] = $e->getMessage();
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
                <h4 class="page-title"><?php echo $is_edit ? 'Edit Study' : 'Add New Study'; ?></h4>
                <p class="page-subtitle">Register a new research protocol for IRB review</p>
            </div>
        </div>
    </div>

    <div class="content-body">
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
                    <input type="hidden" name="study_id" value="<?php echo $is_edit ? $study_id : ''; ?>">
                    <input type="hidden" name="action" value="<?php echo $is_edit ? 'update_study' : 'add_study'; ?>">

                    <!-- Institution Header -->
                    <!-- <div class="premium-card mb-4">
            <div class="card-body text-center py-3">
                <h4 class="text-primary mb-1 fw-bold">NOGUCHI MEMORIAL INSTITUTE FOR MEDICAL RESEARCH-IRB</h4>
                <h5 class="text-muted mb-0">Institutional Review Board</h5>
            </div>
        </div> -->

                    <!-- Main Form Content -->
                    <div class="main-content">
                        <!-- Study Header Section -->
                        <div class="row mb-4">
                            <div class="col-lg-6 mb-4 mb-lg-0">
                                <div class="premium-card h-100">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-bold"><i class="fas fa-flask me-2"></i>Study Information</h6>
                                        <span class="badge bg-info">Required Fields</span>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label fw-semibold required-field">Study Number</label>
                                                <input type="text" id="study_number" name="study_number" class="form-control"
                                                    value="<?php echo esc($study_number); ?>" required>
                                                <div class="invalid-feedback">Please enter a study number.</div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label fw-semibold required-field">Reference Number</label>
                                                <input type="text" id="ref_number" name="ref_number" class="form-control"
                                                    value="<?php echo esc($ref_number); ?>" readonly required>
                                                <div class="invalid-feedback">Please enter a reference number.</div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label fw-semibold required-field">Expiration Date</label>
                                                <input type="date" id="exp_date" name="exp_date" class="form-control"
                                                    value="<?php echo esc($exp_date); ?>" readonly required>
                                                <div class="invalid-feedback">Please select an expiration date.</div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <label class="form-label fw-semibold required-field">Protocol Title</label>
                                                <input type="text" id="protocol_title" name="protocol_title" class="form-control"
                                                    value="<?php echo esc($protocol_title); ?>" required>
                                                <div class="invalid-feedback">Please enter the protocol title.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Current Study Personnel -->
                            <div class="col-lg-6">
                                <div class="premium-card h-100">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-bold"><i class="fas fa-users me-2"></i>Study Personnel</h6>
                                        <button type="button" class="btn btn-sm btn-success" data-bs-target="#addPersonnel" data-bs-toggle="modal">
                                            <i class="fas fa-plus me-1"></i> Add Personnel
                                        </button>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="personnel-table-container">
                                            <table class="table table-hover mb-0 table-premium">
                                                <thead class="sticky-top bg-light">
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
                                                            <td colspan="4" class="text-center text-muted py-4">
                                                                <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                                                No personnel added yet
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

                        <!-- Study Details Section -->
                        <div class="row mb-4">
                            <div class="col-lg-6 mb-4 mb-lg-0">
                                <div class="premium-card h-100">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2"></i>Study Details</h6>
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
                                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addSponsorModal">
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
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Approved Enrollment</label>
                                                <input id="ape" name="ape" type="number" class="form-control" min="0"
                                                    placeholder="Enter number" value="<?php echo esc($approval_patient_enrollment); ?>">
                                            </div>
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
                                                    value="NOGUCHI MEMORIAL INSTITUTE FOR MEDICAL RESEARCH-IRB" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- IRB Information Section -->
                            <div class="col-lg-6">
                                <div class="premium-card h-100">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0 fw-bold"><i class="fas fa-clipboard-check me-2"></i>IRB Information</h6>
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
                                                        <button type="button" class="btn btn-sm btn-outline-primary mt-2 <?php echo !$is_edit ? 'disabled' : ''; ?>"
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
                                                        <button type="button" class="btn btn-sm btn-outline-info mt-2 <?php echo !$is_edit ? 'disabled' : ''; ?>"
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
                        </div>

                        <!-- Documents Section -->
                        <div class="row">
                            <div class="col-12">
                                <div class="premium-card">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-bold"><i class="fas fa-file-alt me-2"></i>Study Documents</h6>
                                        <span class="badge bg-danger">Required</span>
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
                                                                        name="exclude_from_agenda[<?php echo esc($doc['id']); ?>]"
                                                                        <?php echo $doc['exclude_from_agenda'] ? 'checked' : ''; ?>>
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

                    <!-- Form Actions -->
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
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i>
                                <?php echo $is_edit ? 'Update Study' : 'Save Study'; ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
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

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between align-items-center border-top pt-3">
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>Fields marked with * are required
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                    <i class="bi bi-x-circle me-1"></i>Cancel
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i>Save Data
                                </button>
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

    document.addEventListener('DOMContentLoaded', function() {
        const isEdit = <?php echo $is_edit ? 'true' : 'false'; ?>;
        const studyId = <?php echo $study_id ?: 'null'; ?>;

        // Update table on load
        updatePersonnelTable();

        // Initialize Bootstrap form validation
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                showLoadingOverlay();
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
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
            });
        }

        // File upload handling
        const fileInput = document.getElementById('fileInput');
        const fileDropArea = document.getElementById('fileDropArea');
        const documentsTbody = document.getElementById('documents-tbody');

        // Drag and drop functionality
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

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files);
        }

        fileInput.addEventListener('change', function() {
            handleFiles(this.files);
        });

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
        document.getElementById('personnel-table').addEventListener('click', e => {
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



        // Document deletion
        documentsTbody.addEventListener('click', function(e) {
            if (e.target.closest('.delete-document')) {
                const documentId = e.target.closest('.delete-document').dataset.id;
                const row = e.target.closest('tr');
                deleteDocument(documentId, row);
            }
        });

        // Add Personnel Modal
        document.querySelector('[data-bs-target="#addPersonnel"]').addEventListener('click', () => {
            currentPersonnelIndex = null;
            setAddMode();
        });



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

        const form = e.target;
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