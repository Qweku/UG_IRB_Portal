<?php

// New Study Input Form Content
$study_number = '';
$ref_number = '';
$exp_date = '';
$protocol_title = '';
$sponsor = '';
$active = '';
$type = '';
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
$number_of_saes = '';
$number_of_cpas = '';
$initial_summary_of_agenda = '';
$internal_notes = '';



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Process form submission
    $study_number = $_POST['studyNumber'] ?? '';
    $ref_number = $_POST['refNumber'] ?? '';
    $exp_date = $_POST['expDate'] ?? '';
    $protocol_title = $_POST['protocolTitle'] ?? '';
    $sponsor = $_POST['sponsor'] ?? '';
    $active = $_POST['actv'] ?? '';
    $type = $_POST['type'] ?? '';
    $status = $_POST['status'] ?? '';
    $risk_category = $_POST['riskCat'] ?? '';
    $approval_patient_enrollment = $_POST['ape'] ?? '';
    $current_enrolled = $_POST['currentEnroll'] ?? '';
    $on_agenda_date = $_POST['oad'] ?? '';
    $irb_of_record = $_POST['ior'] ?? '';
    $cr_required = $_POST['cRequired'] ?? '';
    $renewal_cycle = $_POST['rcm'] ?? '';
    $date_received = $_POST['dateReceived'] ?? '';
    $first_irb_review = $_POST['fir'] ?? '';
    $original_approval = $_POST['origApp'] ?? '';
    $last_seen_by_irb = $_POST['lsbi'] ?? '';
    $last_irb_renewal = $_POST['lir'] ?? '';
    $number_of_saes = $_POST['nos'] ?? '';
    $number_of_cpas = $_POST['noc'] ?? '';
    $initial_summary_of_agenda = $_POST['isoa'] ?? '';
    $internal_notes = $_POST['internalNotes'] ?? '';

    if (empty($study_number) || empty($ref_number) || empty($exp_date) || empty($protocol_title) || empty($sponsor) || empty($active) || empty($date_received)) {
        echo '<div class="alert alert-danger">Please fill in all required fields.</div>';
    } else {
        // $db = new Database();
        // $conn = $db->connect();
        // if (!$conn) {
        //     return 0;
        // }
        // // Insert new study into the database
        // try {
        //     $stmt = $conn->prepare("INSERT INTO studies (protocol_number, ref_num, expiration_date, title, sponsor_displayname, study_active, review_type, study_status, risk_category, patients_enrolled, init_enroll, on_agenda_date, irb_of_record, cr_required, renewal_cycle, date_received, first_irb_review, original_approval, last_irb_review, last_renewal_date, remarks) VALUES (:study_number, :ref_number, :exp_date, :protocol_title, :sponsor, :active, :type, :status, :risk_category, :approval_patient_enrollment, :current_enrolled, :on_agenda_date, :irb_of_record, :cr_required, :renewal_cycle, :date_received, :first_irb_review, :original_approval, :last_seen_by_irb, :last_irb_renewal, :internal_notes)");
        //     $stmt->execute();
        //     // $result = $stmt->fetch();
        //     echo '<div class="alert alert-success">New study has been saved successfully!</div>';
        //     return 1;
        // } catch (PDOException $e) {
        //     error_log("Error saving studies to database: " . $e->getMessage());
        //     return 0;
        // }

        echo '<div class="alert alert-success">New study has been saved successfully!</div>';
    }

    
    // For now, we will just display a success message
    // echo '<div class="alert alert-success">New study has been saved successfully!</div>';
}



?>


<!-- New Study Input Form Content -->
<div class="new-study-form p-5">
    <form method="POST" action="/add-study" class="needs-validation">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Study Input Form</h2>

        </div>

        <!-- Institution Header -->
        <div class="card mb-4">
            <div class="card-body text-center bg-light">
                <h4 class="text-dark mb-1 fw-bold">NOGUCHI MEMORIAL INSTITUTE FOR MEDICAL RESEARCH-IRB</h4>
                <h5 class="text-muted">New Study</h5>
            </div>
        </div>

        <!-- Main Form Content -->
        <div class="main-content">

            <!-- Study Header Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">Study</h6>
                                <span class="badge bg-warning text-dark">New Study</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Study Number</label>
                                    <input type="text" id="studyNumber" name="studyNumber" class="form-control" value="<?= htmlspecialchars($study_number) ?>" required>
                                    <div class="valid-feedback">Valid.</div>
                                    <div class="invalid-feedback">Please fill out this field.</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Reference Number</label>
                                    <input type="text" id="refNumber" name="refNumber" class="form-control" value="<?= htmlspecialchars($ref_number) ?>" required>
                                    <div class="valid-feedback">Valid.</div>
                                    <div class="invalid-feedback">Please fill out this field.</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Expiration Date</label>
                                    <input type="date" id="expDate" name="expDate" class="form-control" value="<?= htmlspecialchars($exp_date) ?>" required>
                                    <div class="valid-feedback">Valid.</div>
                                    <div class="invalid-feedback">Please fill out this field.</div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-semibold">Protocol Title</label>
                                    <input type="text" id="protocolTitle" name="protocolTitle" class="form-control" value="<?= htmlspecialchars($protocol_title) ?>" required>
                                    <div class="valid-feedback">Valid.</div>
                                    <div class="invalid-feedback">Please fill out this field.</div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Study Personnel -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">Current Study Personnel</h6>
                            <button class="btn btn-sm btn-primary" data-bs-target="#addPersonnel" data-bs-toggle="modal">
                                <i class="fas fa-plus me-1"></i> Add Personnel<span class="text-danger">*</span>
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
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
                                    <tbody>
                                        <tr>
                                            <td>-- No personnel added --</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>

                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" disabled>
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Study Details Section -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold">Study Detials</h6>
                        </div>
                        <div class="card-body">

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Sponsor<span class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="col-md-10">
                                            <select id="sponsor" name="sponsor" class="form-select" required>
                                                <option>John Doe</option>
                                                <option>Mary Jane</option>
                                                <option>Michael Brown</option>
                                                <option>Suspended</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <button class="btn btn-md w-100 btn-primary">
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
                                        <option selected>Open</option>
                                        <option>Closed</option>
                                        <option>Pending</option>
                                        <option>Suspended</option>
                                    </select>
                                    <div class="valid-feedback">Valid.</div>
                                    <div class="invalid-feedback">Please fill out this field.</div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Type<span class="text-danger">*</span></label>
                                    <select id="type" name="type" class="form-select" required>
                                        <option selected>Full Board</option>
                                        <option>Expedited</option>
                                        <option>Exempt</option>
                                    </select>
                                    <div class="valid-feedback">Valid.</div>
                                    <div class="invalid-feedback">Please fill out this field.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Status<span class="text-danger">*</span></label>
                                    <select id="status" name="status" class="form-select" required>
                                        <option selected>Pending</option>
                                        <option>Approved</option>
                                        <option>Opened</option>
                                        <option>Inactive</option>
                                    </select>
                                    <div class="valid-feedback">Valid.</div>
                                    <div class="invalid-feedback">Please fill out this field.</div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Risk Category</label>
                                    <select id="riskCat" name="riskCat" class="form-select">
                                        <option selected>Minimal Risk</option>
                                        <option>Low Risk</option>
                                        <option>Moderate Risk</option>
                                        <option>High Risk</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Approval Patient Enrollment</label>
                                    <input id="ape" name="ape" type="text" class="form-control" placeholder="e.g, Open">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Currently Enrolled</label>
                                    <input id="currentEnroll" name="currentEnroll" type="text" class="form-control">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">On Agenda Date</label>
                                    <select id="oad" name="oad" class="form-select" disabled>
                                        <option selected>No Meetings</option>
                                        <option>Approved</option>
                                        <option>Opened</option>
                                        <option>Inactive</option>
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


            </div>

            <!-- IRB Information Section -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold">IRB Information</h6>
                        </div>
                        <div class="card-body">

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">CR Required</label>
                                    <input id="cRequired" name="cRequired" type="text" class="form-control">
                                </div>

                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Renewal Cycle (Mo)</label>
                                    <select id="rcm" name="rcm" class="form-select">
                                        <option selected>12</option>
                                        <option>11</option>
                                        <option>10</option>
                                        <option>9</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Date Received<span class="text-danger">*</span></label>
                                    <input id="dateReceived" name="dateReceived" type="date" class="form-control" required>
                                    <div class="valid-feedback">Valid.</div>
                                    <div class="invalid-feedback">Please fill out this field.</div>
                                </div>

                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Firt IRB Review</label>
                                    <input id="fir" name="fir" type="date" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Original Approval</label>
                                    <input id="origApp" name="origApp" type="date" class="form-control">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Last Seen By IRB</label>
                                    <input id="lsbi" name="lsbi" type="date" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Last IRB Renewal</label>
                                    <input id="lir" name="lir" type="date" class="form-control">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Number of SAEs</label>
                                    <div class="row">
                                        <div class="col-md-9">
                                            <input id="nos" name="nos" type="text" class="form-control" value="<?= htmlspecialchars('0') ?>" disabled>
                                        </div>
                                        <div class="col-md-3">
                                            <button class="btn btn-primary btn-md">View/Add</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Number of CPAs</label>
                                    <div class="row">
                                        <div class="col-md-9">
                                            <input id="noc" name="noc" type="text" class="form-control" value="<?= htmlspecialchars('0') ?>" disabled>
                                        </div>
                                        <div class="col-md-3">
                                            <button class="btn btn-primary btn-md">View/Add</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Initial Summary of Agenda</label>
                                    <textarea id="isoa" name="isoa" type="text" class="form-control"></textarea>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Internal Notes</label>
                                    <textarea id="internalNotes" name="internalNotes" type="text" class="form-control"></textarea>
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
                                    <button class="btn btn-sm btn-success">
                                        <i class="fas fa-upload me-1"></i> Upload File
                                    </button>
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
                                        <tbody>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-3">
                                                    No documents uploaded yet
                                                </td>
                                            </tr>
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
                <i class="fas fa-save me-1"></i> Save Study
            </button>
            <a class="btn btn-secondary" href="/">
                <i class="fas fa-times me-1"></i> Cancel
            </a>
        </div>
    </form>


</div>

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
                            <!-- <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Name</label>
                                        <input type="text" class="form-control" placeholder="Enter name">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Staff Type</label>
                                        <select class="form-select">
                                            <option>Principal Investigator</option>
                                            <option>Co-Investigator</option>
                                            <option>Study Coordinator</option>
                                            <option>Other</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Title</label>
                                        <input type="text" class="form-control" placeholder="Enter title">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Date Added</label>
                                        <input type="date" class="form-control">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Company Name</label>
                                        <input type="text" class="form-control" placeholder="Enter title">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Main Phone</label>
                                        <input type="phone" class="form-control">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Email</label>
                                        <input type="email" class="form-control" placeholder="Enter title">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Comments</label>
                                        <input type="text" class="form-control">
                                    </div>
                                </div>-->
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
        </div>
    </div>

</div>
<script>
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
                                    <input type="text" class="form-control" placeholder="Enter name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Staff Type</label>
                                    <select class="form-select">
                                        <option>Principal Investigator</option>
                                        <option>Co-Investigator</option>
                                        <option>Study Coordinator</option>
                                        <option>Other</option>
                                    </select>
                                </div>
                            </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Title</label>
                                        <input type="text" class="form-control" placeholder="Enter title">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Date Added</label>
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
                                        <input type="email" class="form-control" placeholder="Enter email">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Comments</label>
                                        <input type="text" class="form-control">
                                    </div>
                                </div> 
                                </div>`;
        form.insertAdjacentHTML('beforeend', newFields);



    }
</script>