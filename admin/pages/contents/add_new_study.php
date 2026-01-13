<?php

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login');
    exit;
}

$study_number = '';
$ref_number = '';
$exp_date = '';
$protocol_title = '';
$sponsor = '';
$active = '';
$review_type = '';
$status = '';
$risk_category = '';
$approval_patient_enrollment = '';
$current_enrolled = '';
$on_agenda_date = '';
$irb_of_record = '';
$cr_required = '';
$renewal_cycle = '';
$date_received = '';
$first_irb_review = '';
$original_approval = '';
$last_seen_by_irb = '';
$last_irb_renewal = '';
$internal_notes = '';
$is_edit = false;
$study_id = null;

// Get staff types from the database
$staffTypes = [];
$sponsors = [];
$study_types = [];
$sae_types = [];
$locations = [];
$study_statuses = [];
$risk_categories = [];

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Fetch dropdown data using helpers
    $staffTypes = getStaffTypes();
    $sponsors = getSponsors();
    $study_types = getReviewTypesList();
    $study_statuses = getStudyStatusesList();
    $risk_categories = getRiskCategoriesList();
    $sae_types = getSAETypesList();
    $locations = getStudyLocationsList();
    $allContacts = getAllContacts();
    $contacts = [];
    foreach ($allContacts as $c) {
        $fullName = trim($c['first_name'] . ' ' . ($c['middle_name'] ? $c['middle_name'] . ' ' : '') . $c['last_name']);
        if (!empty($fullName)) {
            $contacts[] = $fullName;
        }
    }
    // Check for edit mode
    $is_edit = isset($_GET['edit']) && $_GET['edit'] == '1' && isset($_GET['id']) && is_numeric($_GET['id']);
    $study_id = null;
    $personnel_data = [];

    if ($is_edit) {
        $study_id = (int)$_GET['id'];
        // Fetch study data
        $stmt = $conn->prepare("SELECT * FROM studies WHERE id = ?");
        $stmt->execute([$study_id]);
        $study = $stmt->fetch(PDO::FETCH_ASSOC);



        if ($study) {
            $study_number = $study['protocol_number'];
            $ref_number = $study['ref_num'];
            $exp_date = $study['expiration_date'];
            $protocol_title = $study['title'];
            $sponsor = $study['sponsor_displayname'];
            $active = $study['study_active'];
            $review_type = $study['review_type'];
            $status = $study['study_status'];
            $risk_category = $study['risk_category'];
            $approval_patient_enrollment = $study['patients_enrolled'];
            $current_enrolled = $study['init_enroll'];
            $on_agenda_date = $study['on_agenda_date'];
            $irb_of_record = $study['irb_of_record'];
            $cr_required = $study['cr_required'];
            $renewal_cycle = $study['renewal_cycle'];
            $date_received = $study['date_received'];
            $first_irb_review = $study['first_irb_review'];
            $original_approval = $study['approval_date'];
            $last_seen_by_irb = $study['last_irb_review'];
            $last_irb_renewal = $study['last_renewal_date'];
            $internal_notes = $study['remarks'];
        } else {
            // Study not found, handle error
            $is_edit = false;
        }

        // Fetch personnel
        $stmt = $conn->prepare("SELECT * FROM study_personnel WHERE study_id = ?");
        $stmt->execute([$study_id]);
        $personnel_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch documents
        $stmt = $conn->prepare("SELECT * FROM documents WHERE study_id = ?");
        $stmt->execute([$study_id]);
        $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
}

?>

<style>
    .modal-sae {
        max-width: 900px;
    }

    .sae-header {
        background: linear-gradient(135deg, var(--royal-blue), var(--royal-blue-light));
        color: white;
    }

    .study-info-card {
        border-left: 4px solid #0d6efd;
        background-color: #f8f9fa;
    }

    .status-badge {
        font-size: 0.8rem;
        padding: 0.35rem 0.75rem;
    }

    .section-divider {
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 0.5rem;
        margin-bottom: 1.5rem;
        color: #2c3e50;
        font-weight: 600;
    }

    .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 0.4rem;
    }

    .required-field::after {
        content: " *";
        color: #dc3545;
    }

    .radio-group-horizontal .form-check {
        margin-right: 1.5rem;
        margin-bottom: 0;
    }

    .date-input-group {
        position: relative;
    }

    .date-input-group .bi {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        pointer-events: none;
    }

    @media (max-width: 768px) {
        .modal-sae {
            margin: 0.5rem;
        }

        .radio-group-horizontal .form-check {
            margin-right: 1rem;
        }
    }
</style>

<!-- New Study Input Form Content -->
<div id="addStudy" class="new-study-form p-5">
    <form class="needs-validation" id="studyForm" enctype="multipart/form-data">
        <input type="hidden" name="study_id" value="<?php echo $is_edit ? $study_id : ''; ?>">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Study Input Form</h2>

        </div>

        <!-- Institution Header -->
        <div class="card mb-4">
            <div class="card-body text-center bg-light">
                <h4 class="text-dark mb-1 fw-bold">NOGUCHI MEMORIAL INSTITUTE FOR MEDICAL RESEARCH-IRB</h4>
                <h5 class="text-muted"><?php echo $is_edit ? 'Edit Study' : 'New Study'; ?></h5>
            </div>
        </div>

        <!-- Main Form Content -->
        <div class="main-content">
            <?php
            $study_badge = $is_edit ? 'bg-warning' : 'bg-success';
            ?>
            <!-- Study Header Section -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">Study</h6>
                                <span class="badge <?= htmlspecialchars($study_badge); ?> text-white"><?php echo $is_edit ? 'Edit Study' : 'New Study'; ?></span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Study Number</label>
                                    <input type="text" id="study_number" name="study_number" class="form-control" value="<?= htmlspecialchars($study_number) ?>" required>
                                    <div class="valid-feedback">Valid.</div>
                                    <div class="invalid-feedback">Please fill out this field.</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Reference Number</label>
                                    <input type="text" id="ref_number" name="ref_number" class="form-control" value="<?= htmlspecialchars($ref_number) ?>" required>
                                    <div class="valid-feedback">Valid.</div>
                                    <div class="invalid-feedback">Please fill out this field.</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Expiration Date</label>
                                    <input type="date" id="exp_date" name="exp_date" class="form-control" value="<?= htmlspecialchars($exp_date) ?>" required>
                                    <div class="valid-feedback">Valid.</div>
                                    <div class="invalid-feedback">Please fill out this field.</div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-semibold">Protocol Title</label>
                                    <input type="text" id="protocol_title" name="protocol_title" class="form-control" value="<?= htmlspecialchars($protocol_title) ?>" required>
                                    <div class="valid-feedback">Valid.</div>
                                    <div class="invalid-feedback">Please fill out this field.</div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Study Personnel -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">Current Study Personnel</h6>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-target="#addPersonnel" data-bs-toggle="modal">
                                <i class="fas fa-plus me-1"></i> Add Personnel<span class="text-danger">*</span>
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive" style="height: 150px;">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Staff Type</th>
                                            <th>Title</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="personnel-table">
                                        <?php if ($is_edit && !empty($personnel_data)): ?>
                                            <?php foreach ($personnel_data as $person): ?>
                                                <tr data-personnel-id="<?php echo $person['id']; ?>">
                                                    <td><?php echo htmlspecialchars($person['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($person['role']); ?></td>
                                                    <td><?php echo htmlspecialchars($person['title']); ?></td>
                                                    <td><?php echo htmlspecialchars($person['start_date']); ?></td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                <input type="hidden" name="personnel[]" value='
                                                <?php echo json_encode([
                                                    'name' => $person['name'],
                                                    'staffType' => $person['role'],
                                                    'title' => $person['title'],
                                                    'dateAdded' => $person['start_date'],
                                                    'companyName' => $person['company_name'],
                                                    'email' => $person['email'],
                                                    'mainPhone' => $person['phone'],
                                                    'comments' => $person['comments']
                                                ]); ?>'>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-3">
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
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold">Study Detials</h6>
                        </div>
                        <div class="card-body">

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Sponsor<span class="text-danger">*</span></label>
                                    <div>
                                        <div class="mb-2">
                                            <select id="sponsor" name="sponsor" class="form-select" required>
                                                <?php foreach ($sponsors as $s): ?>
                                                    <option value="<?= htmlspecialchars($s) ?>" <?= $s == $sponsor ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-md w-100 btn-primary">
                                                <i class="fas fa-plus me-1"></i>Add to List
                                            </button>
                                        </div>

                                    </div>

                                </div>

                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Active?<span class="text-danger">*</span></label>
                                    <select id="actv" name="actv" class="form-select" required>
                                        <option value="Open" <?= $active == 'Open' ? 'selected' : '' ?>>Open</option>
                                        <option value="Closed" <?= $active == 'Closed' ? 'selected' : '' ?>>Closed</option>
                                        <option value="External" <?= $active == 'External' ? 'selected' : '' ?>>External</option>
                                    </select>
                                    <div class="valid-feedback">Valid.</div>
                                    <div class="invalid-feedback">Please fill out this field.</div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Type<span class="text-danger">*</span></label>
                                    <select id="review_type" name="review_type" class="form-select" required>
                                        <?php foreach ($study_types as $type): ?>
                                            <option value="<?= htmlspecialchars($type) ?>" <?= $type == $review_type ? 'selected' : '' ?>><?= htmlspecialchars(ucwords(str_replace('_', ' ', $type))) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="valid-feedback">Valid.</div>
                                    <div class="invalid-feedback">Please fill out this field.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Status<span class="text-danger">*</span></label>
                                    <select id="status" name="status" class="form-select" required>
                                        <?php foreach ($study_statuses as $status_option): ?>
                                            <option value="<?= htmlspecialchars($status_option) ?>" <?= $status_option == $status ? 'selected' : '' ?>><?= htmlspecialchars($status_option) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="valid-feedback">Valid.</div>
                                    <div class="invalid-feedback">Please fill out this field.</div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Risk Category</label>
                                    <select id="riskCat" name="riskCat" class="form-select">
                                        <?php foreach ($risk_categories as $category): ?>
                                            <option value="<?= htmlspecialchars($category) ?>" <?= $category == $risk_category ? 'selected' : '' ?>><?= htmlspecialchars($category) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Approval Patient Enrollment</label>
                                    <input id="ape" name="ape" type="text" class="form-control" placeholder="e.g, Open" value="<?= htmlspecialchars($approval_patient_enrollment) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Currently Enrolled</label>
                                    <input id="currentEnroll" name="currentEnroll" type="text" class="form-control" value="<?= htmlspecialchars($current_enrolled) ?>">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">On Agenda Date</label>
                                    <select id="oad" name="oad" class="form-select" disabled>
                                        <option value="No Meetings" <?= $on_agenda_date == 'No Meetings' ? 'selected' : '' ?>>No Meetings</option>
                                        <option value="Approved" <?= $on_agenda_date == 'Approved' ? 'selected' : '' ?>>Approved</option>
                                        <option value="Opened" <?= $on_agenda_date == 'Opened' ? 'selected' : '' ?>>Opened</option>
                                        <option value="Inactive" <?= $on_agenda_date == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">IRB of Record</label>
                                    <input id="ior" name="ior" type="text" class="form-control" value="<?= htmlspecialchars("NOGUCHI MEMORIAL INSTITUTE FOR MEDICAL RESEARCH-IRB"); ?>" readonly>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- IRB Information Section -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold">IRB Information</h6>
                        </div>
                        <div class="card-body">

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">CR Required</label>
                                    <input id="cRequired" name="cRequired" type="text" class="form-control" value="<?= htmlspecialchars($cr_required) ?>">
                                </div>

                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Renewal Cycle (Mo)</label>
                                    <select id="rcm" name="rcm" class="form-select">
                                        <option value="12" <?= $renewal_cycle == '12' ? 'selected' : '' ?>>12</option>
                                        <option value="11" <?= $renewal_cycle == '11' ? 'selected' : '' ?>>11</option>
                                        <option value="10" <?= $renewal_cycle == '10' ? 'selected' : '' ?>>10</option>
                                        <option value="9" <?= $renewal_cycle == '9' ? 'selected' : '' ?>>9</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Date Received<span class="text-danger">*</span></label>
                                    <input id="date_received" name="date_received" type="date" class="form-control" value="<?= htmlspecialchars($date_received) ?>" required>
                                    <div class="valid-feedback">Valid.</div>
                                    <div class="invalid-feedback">Please fill out this field.</div>
                                </div>

                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Firt IRB Review</label>
                                    <input id="first_irb_review" name="first_irb_review" type="date" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Original Approval</label>
                                    <input id="original_approval" name="original_approval" type="date" class="form-control" value="<?= htmlspecialchars($original_approval) ?>">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Last Seen By IRB</label>
                                    <input id="last_seen_by_irb" name="last_seen_by_irb" type="date" class="form-control" value="<?= htmlspecialchars($last_seen_by_irb) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Last IRB Renewal</label>
                                    <input id="last_irb_renewal" name="last_irb_renewal" type="date" class="form-control" value="<?= htmlspecialchars($last_irb_renewal) ?>">
                                </div>
                            </div>
                            <?php
                            $show_add_button = $is_edit ? '' : 'disabled';
                            ?>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Number of SAEs</label>
                                    <div>
                                        <div class="mb-2">
                                            <input id="nos" name="nos" type="text" class="form-control" value="<?= htmlspecialchars('0') ?>" disabled>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-primary btn-sm <?= htmlspecialchars($show_add_button) ?>" data-bs-target="#addSAE" data-bs-toggle="modal">View/Add</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Number of CPAs</label>
                                    <div>
                                        <div class="mb-2">
                                            <input id="noc" name="noc" type="text" class="form-control" value="<?= htmlspecialchars('0') ?>" disabled>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-primary btn-sm <?= htmlspecialchars($show_add_button) ?>" data-bs-target="#addCPA" data-bs-toggle="modal">View/Add</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Initial Summary of Agenda</label>
                                    <textarea id="isoa" name="isoa" type="text" class="form-control"><?php echo htmlspecialchars($initial_summary_of_agenda ?? ''); ?></textarea>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Internal Notes</label>
                                    <textarea id="internal_notes" name="internal_notes" type="text" class="form-control"><?php echo htmlspecialchars($internal_notes); ?></textarea>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>




            <!-- Signature Documents -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold">Signature Documents*</h6>
                        </div>
                        <div class="card-body">
                            <!-- Initial Application Section -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0 text-primary">Initial Application*</h6>
                                    <div>
                                        <button type="button" id="uploadBtn" class="btn btn-sm btn-success" onclick="document.getElementById('fileInput').click();">
                                            <i class="fas fa-upload me-1"></i> Upload File
                                        </button>
                                        <input type="hidden" name="MAX_FILE_SIZE" value="10485760">
                                        <input type="file" id="fileInput" name="initialApplication[]" accept="application/pdf,.doc,.docx" multiple style="display: none;">
                                        <span id="fileNameDisplay" class="ms-2 text-muted"></span>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>File Name</th>
                                                <th>Comments</th>
                                                <th>Date Uploaded</th>
                                                <th>Don't Include in Agenda</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="documents-tbody">
                                            <?php if ($is_edit && !empty($documents)): ?>
                                                <?php foreach ($documents as $doc): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($doc['file_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($doc['comments']); ?></td>
                                                        <td><?php echo date('Y-m-d', strtotime($doc['uploaded_at'])); ?></td>
                                                        <td>
                                                            <input type="checkbox" class="form-check-input" name="exclude_from_agenda[<?php echo $doc['id']; ?>]" <?php echo $doc['exclude_from_agenda'] ? 'checked' : ''; ?>>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteDocument(<?php echo $doc['id']; ?>)">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-3">
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

        </div>
        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-success me-2">
                <i class="fas fa-save me-1"></i> <?php echo $is_edit ? 'Update Study' : 'Save Study'; ?>
            </button>
            <button class="btn btn-secondary">
                <i class="fas fa-times me-1"></i> Cancel
            </button>
        </div>
    </form>


</div>


<!-- Add Personnel Modal -->
<div id="addPersonnel" class="modal fade" tabindex="-1" aria-labelledby="addPersonnelLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="addPersonnelLabel">Add Study Personnel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="col-12">
                    <form id="personnelForm">
                        <div id="contentArea">
                            <div class="bg-light p-3 mb-3">
                                <h4 class="text-md">Assigned Personnel</h4>
                            </div>
                        </div>

                    </form>

                    <button class="btn btn-primary" onclick="addMorePersonnel()">
                        <i class="fas fa-plus me-1"></i> Add Personnel
                    </button>
                    <div class="bg-light p-3 my-3">
                        <h4 class="text-md">Personnel Previously Associated with Study</h4>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" onclick="savePersonnel()">Save Personnel</button>
            </div>
        </div>

    </div>
</div>

<!-- Add SAE Modal -->
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






<script>
    const isEdit = <?php echo $is_edit ? 'true' : 'false'; ?>;


    function showToast(type, message) {
        // Create toast container if not exists
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '1050';
            document.body.appendChild(toastContainer);
        }

        // Create toast element
        const toast = document.createElement('div');
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

        // Initialize and show toast
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        // Remove toast after it's hidden
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }

    // Handle form submission
    document.getElementById('studyForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        toggleLoader(true); // 🔹 Show loader immediately

        const formData = new FormData(this);

        try {
            const response = await fetch('/admin/handlers/add_study_handler.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();


            if (result.status === 'success') {
                showToast('success', result.message);
                // Optionally redirect or reset form
                // window.location.href = '/some-success-page';
                toggleLoader(false);
                window.location.reload();
            } else {
                showToast('error', result.message);
                toggleLoader(false);
                window.location.reload();
            }
        } catch (error) {
            showToast('error', 'An unexpected error occurred.');
            toggleLoader(false);
            window.location.reload();
        } finally {
            // 🔹 Always hide loader after operations
            toggleLoader(false);
            window.location.reload();
        }
    });

    // Use event delegation for edit and delete buttons
    document.getElementById('personnel-table').addEventListener('click', async function(e) {
        if (e.target.closest('.btn-outline-primary')) {
            const row = e.target.closest('tr');
            const personnel_id = row.getAttribute('data-personnel-id');
            console.log('Edit personnel with ID:', personnel_id);

            // Set modal title for editing
            document.getElementById('addPersonnelLabel').textContent = 'Edit Study Personnel';

            // Fetch personnel data
            try {
                const response = await fetch(`/admin/handlers/fetch_personnel.php?id=${personnel_id}`);
                const data = await response.json();
                if (data.status === 'success') {
                    const person = data.personnel;

                    // Clear previous content
                    const form = document.getElementById('contentArea');
                    form.innerHTML = '<div class="bg-light p-3 mb-3"><h4 class="text-md">Assigned Personnel</h4></div>';

                    // Populate fields
                    const newFields = `
                        <div class="new-personnel-section">
                            <input type="hidden" name="personnel_id" value="${person.id}">
                            <hr>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Name</label>
                                    <select name="contact" class="form-select">
                                        <option value="${person.name}" selected>${person.name}</option>
                                        ${<?php echo json_encode($contacts); ?>.filter(c => c !== person.name).map(c => `<option value="${c}">${c}</option>`).join('')}
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Staff Type</label>
                                    <select name="staffType" class="form-select">
                                        ${<?php echo json_encode($staffTypes); ?>.map(type => `<option value="${type}" ${type === person.role ? 'selected' : ''}>${type}</option>`).join('')}
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Title</label>
                                    <input type="text" name="title" class="form-control" placeholder="Enter title" value="${person.title}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Start Date</label>
                                    <input type="date" name="start_date" class="form-control" value="${person.start_date}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Company Name</label>
                                    <input type="text" name="company_name" class="form-control" placeholder="Enter company name" value="${person.company_name}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Main Phone</label>
                                    <input type="phone" name="phone" class="form-control" value="${person.phone}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email</label>
                                    <input type="email" name="email" class="form-control" placeholder="Enter email" value="${person.email}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Comments</label>
                                    <input type="text" name="comments" class="form-control" placeholder="Enter comments" value="${person.comments}">
                                </div>
                            </div>
                        </div>`;
                    form.insertAdjacentHTML('beforeend', newFields);

                    // Open modal
                    const modal = new bootstrap.Modal(document.getElementById('addPersonnel'));
                    modal.show();
                } else {
                    showToast('error', 'Failed to fetch personnel data.');
                }
            } catch (error) {
                showToast('error', 'An error occurred while fetching personnel data.');
            }
        } else if (e.target.closest('.btn-outline-danger')) {
            const row = e.target.closest('tr');
            const personnel_id = row.getAttribute('data-personnel-id');
            const study_id = document.querySelector('input[name="study_id"]').value;

            console.log('Delete personnel with ID:', personnel_id, 'from study ID:', study_id);

            if (confirm('Are you sure you want to delete this personnel?')) {
                try {
                    const response = await fetch('/admin/handlers/delete_personnel.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: personnel_id,
                            study_id: study_id
                        })
                    });
                    const data = await response.json();
                    if (data.success) {
                        // Remove the row from table
                        row.remove();

                        // If editing and has personnel_id, remove the corresponding hidden input
                        if (isEdit && personnel_id) {
                            const studyForm = document.getElementById('studyForm');
                            const hiddenInputs = studyForm.querySelectorAll('input[name="personnel[]"]');
                            hiddenInputs.forEach(input => {
                                try {
                                    const inputData = JSON.parse(input.value);
                                    if (inputData.id == personnel_id) {
                                        input.remove();
                                    }
                                } catch (e) {
                                    // Ignore invalid JSON
                                }
                            });
                        }

                        showToast('success', 'Personnel deleted successfully.');
                    } else {
                        showToast('error', data.message || 'Failed to delete personnel');
                    }
                } catch (error) {
                    showToast('error', 'An error occurred while deleting personnel');
                }
            }
        }
    });

    async function refreshPersonnelTable() {
        if (!isEdit) return;
        const studyId = document.querySelector('input[name="study_id"]').value;
        try {
            const response = await fetch(`/admin/handlers/fetch_personnel.php?study_id=${studyId}`);
            const data = await response.json();
            if (data.status === 'success') {
                const tbody = document.getElementById('personnel-table');
                tbody.innerHTML = '';
                if (data.personnel.length > 0) {
                    data.personnel.forEach(person => {
                        const row = `
                            <tr data-personnel-id="${person.id}">
                                <td>${person.name}</td>
                                <td>${person.role}</td>
                                <td>${person.title}</td>
                                <td>${person.start_date}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                        tbody.insertAdjacentHTML('beforeend', row);
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">No personnel added yet</td></tr>';
                }
            }
        } catch (error) {
            console.error('Error refreshing personnel table:', error);
        }
    }

    async function refreshDocumentsTable() {
        if (!isEdit) return;
        const studyId = document.querySelector('input[name="study_id"]').value;
        try {
            const response = await fetch(`/admin/handlers/fetch_documents.php?study_id=${studyId}`);
            const data = await response.json();
            if (data.status === 'success') {
                const tbody = document.getElementById('documents-tbody');
                tbody.innerHTML = '';
                if (data.documents.length > 0) {
                    data.documents.forEach(doc => {
                        const row = `
                            <tr>
                                <td>${doc.file_name}</td>
                                <td>${doc.comments}</td>
                                <td>${new Date(doc.uploaded_at).toLocaleDateString()}</td>
                                <td><input type="checkbox" class="form-check-input" name="exclude_from_agenda[${doc.id}]" ${doc.exclude_from_agenda ? 'checked' : ''}></td>
                                <td><button type="button" class="btn btn-sm btn-danger" onclick="deleteDocument(${doc.id})"><i class="fas fa-trash"></i></button></td>
                            </tr>
                        `;
                        tbody.insertAdjacentHTML('beforeend', row);
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">No documents uploaded yet</td></tr>';
                }
            }
        } catch (error) {
            console.error('Error refreshing documents table:', error);
        }
    }

    function deleteDocument(id) {
        if (confirm('Are you sure you want to delete this document?')) {
            fetch('/admin/handlers/delete_document.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('success', 'Document deleted successfully');
                        refreshDocumentsTable();
                    } else {
                        showToast('error', data.message || 'Failed to delete document');
                    }
                })
                .catch(error => {
                    showToast('error', 'An error occurred while deleting the document');
                });
        }
    }

    function addMorePersonnel() {
        // Function to add more personnel fields dynamically
        const form = document.getElementById('contentArea');
        const newFields = `
                        
                        <div class="new-personnel-section">
                            <hr>
                        <div class="d-flex justify-content-end">
                            
                            <button class="btn btn-danger btn-sm mb-3" onclick="this.parentElement.parentElement.remove();">
                                <i class="fas fa-trash me-1"></i> Remove Personnel </button>
                        </div>
                        <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Name</label>
                                   <select name="contact" class="form-select">
                                   <option value="" disabled selected>Choose a personnel...</option>
                                        <?php foreach ($allContacts as $contact): ?>
                                             <?php if (empty($contact['first_name']) && empty($contact['last_name'])): ?>
                                                <option><?= htmlspecialchars($contact['company_dept_name']) ?></option>
                                            <?php else: ?>
                                                <option><?= htmlspecialchars($contact['last_name'] . ' ' . $contact['first_name']) ?></option>
                                            <?php endif; ?>

                                         <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Staff Type</label>
                                    <select name="staffType" class="form-select">
                                       <?php foreach ($staffTypes as $type): ?>
                                            <option><?= htmlspecialchars($type) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Title</label>
                                        <input type="text" name="title" class="form-control" placeholder="Enter title">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Start Date</label>
                                        <input type="date" class="form-control">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Company Name</label>
                                        <input type="text" class="form-control" placeholder="Enter company name">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Main Phone</label>
                                        <input type="phone" class="form-control">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Email</label>
                                        <input type="email" name="email" class="form-control" placeholder="Enter email">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Comments</label>
                                        <input type="text" name="comments" class="form-control" placeholder="Enter comments">
                                    </div>
                                </div>
                                </div>`;
        form.insertAdjacentHTML('beforeend', newFields);
    }

    function savePersonnel() {
        const button = document.querySelector('#addPersonnel .modal-footer .btn-success');
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';

        setTimeout(() => {
            // Function to save personnel data
            const personnelSections = document.querySelectorAll('.new-personnel-section');
            const personnelTable = document.getElementById('personnel-table');
            const studyForm = document.getElementById('studyForm');

            personnelSections.forEach(section => {
                const name = section.querySelector('select[name="contact"]').value;
                const staffType = section.querySelector('select[name="staffType"]').value;
                const title = section.querySelector('input[placeholder="Enter title"]').value;
                const dateAdded = section.querySelector('input[type="date"]').value;
                const companyName = section.querySelector('input[placeholder="Enter company name"]').value;
                const email = section.querySelector('input[type="email"]').value;
                const mainPhone = section.querySelector('input[type="phone"]').value;
                const comments = section.querySelector('input[placeholder="Enter comments"]').value; //

                const personnelData = {
                    name: name,
                    staffType: staffType,
                    title: title,
                    dateAdded: dateAdded,
                    companyName: companyName,
                    email: email,
                    mainPhone: mainPhone,
                    comments: comments
                };

                // Add to table
                const newRow = `
                    <tr>
                        <td>${name}</td>
                        <td>${staffType}</td>
                        <td>${title}</td>
                        <td>${dateAdded}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                `;
                personnelTable.insertAdjacentHTML('beforeend', newRow);

                // Add hidden input to form
                const hiddenInput = `<input type="hidden" name="personnel[]" value='${JSON.stringify(personnelData)}'>`;
                studyForm.insertAdjacentHTML('beforeend', hiddenInput);
            });

            // Close the modal after saving
            const modal = bootstrap.Modal.getInstance(document.getElementById('addPersonnel'));
            modal.hide();

            // Clear the form for next use
            document.getElementById('contentArea').innerHTML = `
                <div class="bg-light p-3 mb-3">
                    <h4 class="text-md">Assigned Personnel</h4>
                </div>
            `;

            button.disabled = false;
            button.innerHTML = 'Save Personnel';
        }, 500);
    }

    function toggleLoader(show = true) {
        let section = document.getElementById('loader-container');

        // Create wrapper if not exist
        if (!section) {
            section = document.createElement('div');
            section.id = 'loader-container';
            section.className = 'loader-container';
            section.style.position = 'fixed';
            section.style.top = '0';
            section.style.left = '0';
            section.style.width = '100%';
            section.style.height = '100%';
            section.style.zIndex = '1150';
            document.body.appendChild(section);
        }

        let loader = section.querySelector('.section-loader');

        // Create loader if not exist
        if (!loader) {
            loader = document.createElement('div');
            loader.className = `
            section-loader fade-loader d-flex justify-content-center align-items-center
            w-100 h-100 bg-dark bg-opacity-50
        `;
            loader.innerHTML = `
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        `;
            section.appendChild(loader);
        }

        // Toggle visibility
        loader.style.display = show ? 'flex' : 'none';
    }

    // Handle file selection for initial application upload
    document.getElementById('fileInput').addEventListener('change', function(event) {
        const files = event.target.files;
        const tbody = document.getElementById('documents-tbody');
        document.getElementById('fileNameDisplay').textContent = files.length > 0 ? `Selected: ${files.length} file(s)` : '';
        if (files.length === 0) {
            // If no files selected and no existing rows, show no documents
            if (tbody.querySelectorAll('tr').length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">No documents uploaded yet</td></tr>';
            }
            return;
        }
        // Remove the no documents row if present
        const noDocsRow = tbody.querySelector('tr td[colspan="5"]');
        if (noDocsRow) noDocsRow.closest('tr').remove();
        Array.from(files).forEach(file => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${file.name} (${file.type || 'Unknown'})</td>
                <td><input type="text" class="form-control" name="file_comments[]" placeholder="Comments"></td>
                <td>${new Date().toLocaleDateString()}</td>
                <td><input type="checkbox" name="dont_include[]"></td>
                <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()">Remove</button></td>
            `;
            tbody.appendChild(row);
        });
    });

    // Handle SAE form submission
    document.getElementById('saeForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        // Basic validation
        const required = ['description', 'eventType', 'sex', 'patientId', 'eventDate', 'receivedDate', 'patientStatus'];
        let valid = true;
        required.forEach(id => {
            const el = document.getElementById(id);
            if (!el.value.trim()) {
                el.classList.add('is-invalid');
                valid = false;
            } else {
                el.classList.remove('is-invalid');
            }
        });
        if (!valid) {
            showToast('error', 'Please fill in all required fields.');
            return;
        }

        const formData = new FormData(this);
        try {
            const response = await fetch('/admin/handlers/add_study_handler.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (result.status === 'success') {
                showToast('success', result.message);
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('addSAE'));
                modal.hide();
            } else {
                showToast('error', result.message);
            }
        } catch (error) {
            showToast('error', 'An unexpected error occurred.');
        }
    });


    function toggleFollowUpReport() {
        const checkbox = document.getElementById('followUpCheckbox');
        const select = document.getElementById('followUpReport');

        if (checkbox.checked) {
            select.disabled = false;
            select.focus();
        } else {
            select.disabled = true;
            select.value = ""; // reset selection when disabled
        }
    }

    function toggleSignedByPI(){
        const piCheckbox = document.getElementById('signedByPI');
        const piInput = document.getElementById('signedDate');

         if (piCheckbox.checked) {
            piInput.disabled = false;
            piInput.focus();
        } else {
            piInput.disabled = true;
            piInput.value = ""; // reset selection when disabled
        }
    }

    // Auto-fill email when name is selected
    document.addEventListener('change', function(e) {
        if (e.target.matches('select[name="contact"]')) {
            const section = e.target.closest('.new-personnel-section');
            const titleInput = section.querySelector('input[name="title"]');
            const emailInput = section.querySelector('input[name="email"]');
            const selectedName = e.target.value;

            if (selectedName && emailInput) {
                fetch(`/admin/handlers/fetch_contact_email.php?name=${encodeURIComponent(selectedName)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.email) {
                            emailInput.value = data.email;
                            titleInput.value = data.title || '';
                        }
                    })
                    .catch(error => console.error('Error fetching email:', error));
            }
        }
    });
</script>