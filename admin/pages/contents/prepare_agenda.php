<?php

// Authentication check
// if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
//     header('Location: /login');
//     exit;
// }

// Include CSRF protection
// require_once '../../includes/functions/csrf.php';

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $conn->beginTransaction();

    // Fetch meetings
    $stmt = $conn->prepare("SELECT meeting_date FROM irb_meetings");
    $stmt->execute();
    $irbMeetings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Today's date
    $today = new DateTime();

    // Variable to store the next meeting
    $nextMeeting = null;

    foreach ($irbMeetings as $meeting) {
        $date = new DateTime($meeting['meeting_date']);

        // If date is in the future
        if ($date > $today) {
            // If not set yet or this date is earlier than the currently stored one
            if ($nextMeeting === null || $date < $nextMeeting) {
                $nextMeeting = $date;
            }
        }
    }
} catch (PDOException $e) {
    $conn->rollBack();
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch meeting. Please try again.']);
}

// Function to sanitize output
function esc($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$boardMembers = [
    'John Doe',
    'Mary Wood',
    'Michael Brown',
    'Kwame Bempong',
    'Jane Mensah',
    'Abena Sarpong'
];

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

    .file-upload-area {
        border: 2px dashed #dee2e6;
        border-radius: 0.375rem;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
    }

    .file-upload-area:hover {
        border-color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.05);
    }

    .file-upload-area.dragover {
        border-color: #198754;
        background-color: rgba(25, 135, 84, 0.1);
    }

    .personnel-table-container {
        max-height: 250px;
        overflow-y: auto;
    }

    @media (max-width: 768px) {
        .modal-sae {
            margin: 0.5rem;
        }

        .radio-group-horizontal .form-check {
            margin-right: 1rem;
        }
    }

    .cursor-pointer {
        cursor: pointer;
    }
</style>

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

<style>
    /* Stepper Styles */
    .stepper-sidebar {
        z-index: 100;
    }

    .stepper-vertical {
        position: relative;
    }

    .step {
        position: relative;
        padding-bottom: 1.5rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .step:last-child {
        padding-bottom: 0;
    }

    .step-number {
        width: 36px;
        height: 36px;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .step-title h6 {
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .step-title small {
        font-size: 11px;
        transition: all 0.3s ease;
    }

    .step-line {
        position: absolute;
        left: 18px;
        top: 36px;
        bottom: 0;
        width: 2px;
        background-color: #e9ecef;
    }

    .step:last-child .step-line {
        display: none;
    }

    .step-progress {
        position: relative;
    }

    /* Step content styles */
    .step-content {
        display: none;
    }

    .step-content.active {
        display: block;
    }

    /* Navigation buttons */
    .stepper-navigation .btn {
        transition: all 0.3s ease;
    }

    /* Mobile stepper navigation */
    @media (max-width: 991px) {
        .stepper-navigation {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 1rem;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .stepper-sidebar {
            position: relative !important;
            top: 0 !important;
        }
    }

    /* Review summary styles */
    #reviewChairPerson,
    #reviewPreparer,
    #reviewGuests,
    #reviewTime,
    #reviewLocation,
    #reviewHeading,
    #reviewOldBusiness,
    #reviewNewBusiness {
        font-weight: normal;
        color: #6c757d;
    }
</style>

<div class="agenda-preparation p-4 p-lg-5">


    <!-- Page Header -->
    <div class="content-header">
        <div class="page-header-card d-flex">
            <div class="header-icon-wrapper">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="header-content">
                <h4 class="page-title">Agenda Preparation</h4>
                <p class="page-subtitle">Prepare the agenda for upcoming IRB meetings</p>
            </div>
        </div>
    </div>

    <!-- Institution Header -->
    <!-- <div class="card mb-4 border-primary">
        <div class="card-body text-center bg-primary bg-opacity-10 py-3">
            <h4 class="text-primary mb-1 fw-bold">NOGUCHI MEMORIAL INSTITUTE FOR MEDICAL RESEARCH-IRB</h4>
            <h5 class="text-muted mb-0">Institutional Review Board</h5>
        </div>
    </div> -->

    <!-- Actions Section -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-tools me-2"></i>Actions</h6>
                </div>
                <div class="card-body bg-light-subtle">
                    <!-- Options Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="bg-white rounded p-3 border">
                                <div class="d-flex flex-wrap gap-4 align-items-center">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="printDate">
                                        <label class="form-check-label fw-semibold" for="printDate">Print Report Date</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="printSites">
                                        <label class="form-check-label fw-semibold" for="printSites">Print Sites</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="printCoInv">
                                        <label class="form-check-label fw-semibold" for="printCoInv">Print Co-Investigators</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="printItemNum">
                                        <label class="form-check-label fw-semibold" for="printItemNum">Print Item Numbers</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>



                    <div class="row mb-4">
                        <div class="col-md-6">
                            <button class="btn btn-outline-primary btn-lg w-100">
                                <i class="fas fa-eye me-2"></i>Preview Agenda
                            </button>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-6">
                                    <button class="btn btn-primary w-100">
                                        <i class="fas fa-file-pdf me-2"></i>PDF
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button class="btn btn-success w-100">
                                        <i class="fas fa-file-word me-2"></i>Word
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>





                </div>
            </div>
        </div>
    </div>

    <!-- Main Form Content -->
    <form class="needs-validation" id="agendaForm">
        <?php echo csrf_field(); ?>

        <!-- Stepper Sidebar -->
        <div class="row mb-4">
            <div class="col-lg-3 mb-4 mt-4 mb-lg-0">
                <div class="stepper-sidebar card border-0 shadow-sm h-100 sticky-top" style="top: 20px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Agenda Steps</h5>

                        <div class="stepper-vertical">
                            <!-- Step 1: Meeting Details -->
                            <div class="step active" data-step="1">
                                <div class="step-header d-flex align-items-center mb-2">
                                    <div class="step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                        1
                                    </div>
                                    <div class="step-title ms-3">
                                        <h6 class="fw-semibold mb-0">Meeting Details</h6>
                                        <small class="text-muted">Date, Chair, Preparer</small>
                                    </div>
                                </div>
                                <div class="step-progress ms-4 ps-3">
                                    <div class="step-line"></div>
                                </div>
                            </div>

                            <!-- Step 2: IRB Members -->
                            <div class="step" data-step="2">
                                <div class="step-header d-flex align-items-center mb-2">
                                    <div class="step-number bg-light text-muted border rounded-circle d-flex align-items-center justify-content-center">
                                        2
                                    </div>
                                    <div class="step-title ms-3">
                                        <h6 class="fw-semibold mb-0 text-muted">IRB Members</h6>
                                        <small class="text-muted">Board members, Guests</small>
                                    </div>
                                </div>
                                <div class="step-progress ms-4 ps-3">
                                    <div class="step-line"></div>
                                </div>
                            </div>

                            <!-- Step 3: Agenda Items -->
                            <div class="step" data-step="3">
                                <div class="step-header d-flex align-items-center mb-2">
                                    <div class="step-number bg-light text-muted border rounded-circle d-flex align-items-center justify-content-center">
                                        3
                                    </div>
                                    <div class="step-title ms-3">
                                        <h6 class="fw-semibold mb-0 text-muted">Agenda Items</h6>
                                        <small class="text-muted">Time, Location, Business</small>
                                    </div>
                                </div>
                                <div class="step-progress ms-4 ps-3">
                                    <div class="step-line"></div>
                                </div>
                            </div>

                            <!-- Step 4: Review & Save -->
                            <div class="step" data-step="4">
                                <div class="step-header d-flex align-items-center">
                                    <div class="step-number bg-light text-muted border rounded-circle d-flex align-items-center justify-content-center">
                                        4
                                    </div>
                                    <div class="step-title ms-3">
                                        <h6 class="fw-semibold mb-0 text-muted">Review & Save</h6>
                                        <small class="text-muted">Final review</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-2">
                            <small class="text-muted">Step <span id="currentStep">1</span> of 4</small>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="stepper-navigation mt-4 d-none d-lg-block">
                            <button type="button" class="btn btn-outline-secondary w-100 mb-2" id="prevStepBtn" disabled>
                                <i class="fas fa-arrow-left me-2"></i>Previous
                            </button>
                            <button type="button" class="btn btn-primary w-100" id="nextStepBtn">
                                <span class="spinner-container" style="display:none;">
                                    <i class="fas fa-spinner fa-spin me-2"></i>
                                </span>
                                Next
                                <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <input type="hidden" name="action" value="add_agenda">
                <input type="hidden" name="current_step" id="currentStepField" value="1">

                <div class="main-content">
                    <!-- Step 1: Meeting Details -->
                    <div class="step-content active" data-step="1">
                        <!-- Study Header Section -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0 fw-bold">Meeting Date<span class="text-primary ms-3"><?php echo htmlspecialchars($nextMeeting->format('Y-m-d') ?? ""); ?></span></h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label fw-semibold">Chair Person <span class="text-danger">*</span></label>
                                                <select class="form-select" name="chair_person" id="chair_person" required>
                                                    <option value="">-- Select Chair Person --</option>
                                                    <option>Dr. John Doe</option>
                                                    <option>Dr. Mary Jane</option>
                                                    <option>Dr. Michael Brown</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label fw-semibold">Title of Preparer</label>
                                                <input type="text" name="preparer_title" id="preparer_title" class="form-control">
                                            </div>
                                            <div class="col-md-8 mb-3">
                                                <label class="form-label fw-semibold">Name of Preparer</label>
                                                <input type="text" name="preparer" id="preparer" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation buttons for step 1 -->
                        <!-- <div class="d-flex justify-content-between mt-4">
                            <div></div>
                            <button type="button" class="btn btn-primary" onclick="nextStep()">
                                Next <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div> -->
                    </div>

                    <!-- Step 2: IRB Members -->
                    <div class="step-content" data-step="2">
                        <div class="row mb-4">
                            <!-- IRB Member Section -->
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-bold">IRB Members Section</h6>
                                        <div class="d-flex w-25 gap-2">
                                            <label for="search" class="form-label">Search</label>
                                            <input type="search" class="form-control">
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>IRB Member</th>
                                                        <th>List</th>
                                                        <th>Representing</th>
                                                        <th>Perm/Alternate</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-3">
                                                            -- No member added yet --
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="my-3">
                                            <button class="btn btn-md btn-primary" data-bs-target="#boardMembers" data-bs-toggle="modal">
                                                <i class="fas fa-add me-1"></i> Add/Remove Board Member(s)
                                            </button>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold">Guests/Staff</label>
                                                <textarea type="text" name="guests" id="guests" class="form-control"></textarea>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold">Education/Training</label>
                                                <textarea type="text" name="education" id="education" class="form-control"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation buttons for step 2 -->
                        <!-- <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-outline-secondary" onclick="prevStep()">
                                <i class="fas fa-arrow-left me-2"></i>Previous
                            </button>
                            <button type="button" class="btn btn-primary" onclick="nextStep()">
                                Next <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div> -->
                    </div>

                    <!-- Step 3: Agenda Items -->
                    <div class="step-content" data-step="3">
                        <div class="row mb-4">
                            <!-- Agenda Details Section -->
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0 fw-bold">Agenda Details</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Time <span class="text-danger">*</span></label>
                                                <input type="time" name="agend_time" id="agenda_time" class="form-control" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Location <span class="text-danger">*</span></label>
                                                <input type="text" name="location" id="location" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold">Agenda Heading <span class="text-danger">*</span></label>
                                                <textarea type="text" name="agenda_heading" id="agenda_heading" class="form-control" required></textarea>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold">Old Business</label>
                                                <textarea type="text" name="old_business" id="old_business" class="form-control"></textarea>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold">New Business</label>
                                                <textarea type="text" name="new_business" id="new_business" class="form-control"></textarea>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold">Additional Heading</label>
                                                <textarea type="text" name="additional_heading" id="additional_heading" class="form-control"></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold">Additional Remarks</label>
                                                <textarea type="text" name="additional_remarks" id="additional_remarks" class="form-control"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation buttons for step 3 -->
                        <!-- <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-outline-secondary" onclick="prevStep()">
                                <i class="fas fa-arrow-left me-2"></i>Previous
                            </button>
                            <button type="button" class="btn btn-primary" onclick="nextStep()">
                                Next <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div> -->
                    </div>

                    <!-- Step 4: Review & Save -->
                    <div class="step-content" data-step="4">
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0 fw-bold">Review & Save Agenda</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info mb-4">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Please review all the information below before saving the agenda.
                                        </div>

                                        <!-- Meeting Details Summary -->
                                        <div class="mb-4">
                                            <h6 class="fw-bold border-bottom pb-2 mb-3">
                                                <i class="fas fa-calendar-alt me-2"></i>Meeting Details
                                            </h6>
                                            <div class="row">
                                                <div class="col-md-6 mb-2">
                                                    <strong>Chair Person:</strong> <span id="reviewChairPerson">-</span>
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <strong>Preparer:</strong> <span id="reviewPreparer">-</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- IRB Members Summary -->
                                        <div class="mb-4">
                                            <h6 class="fw-bold border-bottom pb-2 mb-3">
                                                <i class="fas fa-users me-2"></i>IRB Members
                                            </h6>
                                            <div class="row">
                                                <div class="col-md-12 mb-2">
                                                    <strong>Guests/Staff:</strong> <span id="reviewGuests">-</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Agenda Items Summary -->
                                        <div class="mb-4">
                                            <h6 class="fw-bold border-bottom pb-2 mb-3">
                                                <i class="fas fa-list-alt me-2"></i>Agenda Items
                                            </h6>
                                            <div class="row">
                                                <div class="col-md-6 mb-2">
                                                    <strong>Time:</strong> <span id="reviewTime">-</span>
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <strong>Location:</strong> <span id="reviewLocation">-</span>
                                                </div>
                                                <div class="col-md-12 mb-2">
                                                    <strong>Agenda Heading:</strong> <span id="reviewHeading">-</span>
                                                </div>
                                                <div class="col-md-12 mb-2">
                                                    <strong>Old Business:</strong> <span id="reviewOldBusiness">-</span>
                                                </div>
                                                <div class="col-md-12 mb-2">
                                                    <strong>New Business:</strong> <span id="reviewNewBusiness">-</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation buttons for step 4 -->
                        <div class="d-flex justify-content-between mt-4">
                            <!-- <button type="button" class="btn btn-outline-secondary" onclick="prevStep()">
                                <i class="fas fa-arrow-left me-2"></i>Previous
                            </button> -->
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> Save Agenda
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div id="boardMembers" class="modal fade" tabindex="-1" aria-labelledby="boardMembersLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="boardMembersLabel">Add/Remove Board Members</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="col-12">

                        <div id="contentArea">
                            <div class="bg-light p-3 mb-3">
                                <h4 class="text-md">Available Board Members (Unassigned)</h4>
                            </div>
                            <ul class="mw-[300px]" id="availableMembers">
                                <?php foreach ($boardMembers as $member): ?>
                                    <li id="available-<?php echo htmlspecialchars($member); ?>" style="list-style: none;" class="mb-3">
                                        <div class="d-flex justify-content-between gap-2">
                                            <p><?php echo ($member) ?></p>
                                            <button class="btn btn-success btn-sm" onclick="assignMember('<?php echo htmlspecialchars($member); ?>')">
                                                <i class="fas fa-check me-1"></i>Assign
                                            </button>
                                        </div>
                                    </li>
                                <?php endforeach; ?>

                                <!-- <li style="list-style: none;" class="mb-3">
                                    <div class="d-flex justify-content-between gap-2">
                                        <p>Dr. Kate Snow</p>
                                        <button class="btn btn-success btn-sm" onclick="assignMember()">
                                            <i class="fas fa-check me-1"></i>Assign
                                        </button>
                                    </div>
                                </li> -->
                            </ul>

                        </div>



                        <div class="bg-light p-3 my-3">
                            <h4 class="text-md">Current Board members (Assigned)</h4>
                        </div>
                        <ul id="currentMembers">
                            <!-- <li style="list-style: none;" class="mb-3">
                                <div class="d-flex justify-content-between gap-2">
                                    <p>Dr. Mary Jane</p>
                                    <button class="btn btn-danger btn-sm" onclick="unAssignMember()">
                                        <i class="fas fa-cancel me-1"></i>Un-Assign
                                    </button>
                                </div>
                            </li> -->

                            <!-- <li style="list-style: none;" class="mb-3">
                                <div class="d-flex justify-content-between gap-2">
                                    <p>Dr. Michael Brown</p>
                                    <button class="btn btn-danger btn-sm" onclick="unAssignMember()">
                                        <i class="fas fa-cancel me-1"></i>Un-Assign
                                    </button>
                                </div>
                            </li> -->
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    // Stepper functionality
    let currentStep = 1;
    const totalSteps = 4;

    // Initialize stepper on page load
    document.addEventListener('DOMContentLoaded', function() {
        initStepper();
        updateReviewSummary();
        clearValidationOnInput();

        // Add event listeners to form inputs for review summary update
        const formInputs = document.querySelectorAll('#agendaForm input, #agendaForm textarea, #agendaForm select');
        formInputs.forEach(input => {
            input.addEventListener('change', updateReviewSummary);
            input.addEventListener('input', updateReviewSummary);
        });
    });

    function initStepper() {
        const prevBtn = document.getElementById('prevStepBtn');
        const nextBtn = document.getElementById('nextStepBtn');

        // Previous button click
        if (prevBtn) {
            prevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (currentStep > 1) {
                    prevStep();
                }
            });
        }

        // Next button click
        if (nextBtn) {
            nextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (currentStep < totalSteps) {
                    nextStep();
                }
            });
        }

        // Add click handlers to step elements
        const stepElements = document.querySelectorAll('.step');
        stepElements.forEach(step => {
            step.addEventListener('click', function() {
                const stepNumber = parseInt(this.dataset.step);

                if (stepNumber < currentStep) {
                    // Navigate to completed step
                    goToStep(stepNumber);
                } else if (stepNumber === currentStep) {
                    // Already on this step - do nothing
                } else {
                    // Future step - show toast message
                    showToast('info', 'Please complete the current step before moving to the next one.');
                }
            });
        });
    }

    // Go to specific step
    function goToStep(step) {
        if (step < 1 || step > totalSteps) return;

        // Clear all validation errors when navigating
        clearAllValidation();

        currentStep = step;

        // Show/hide step content
        const contents = document.querySelectorAll('.step-content');
        contents.forEach(content => {
            const contentStep = parseInt(content.dataset.step);
            content.classList.toggle('active', contentStep === currentStep);
        });

        // Update current step field
        const currentStepField = document.getElementById('currentStepField');
        if (currentStepField) {
            currentStepField.value = currentStep;
        }

        // Update step indicators
        updateStepIndicators();

        // Update navigation buttons
        updateNavigationButtons();

        // Update review summary when going to step 4
        if (currentStep === 4) {
            updateReviewSummary();
        }

        // Scroll to top of content
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    // Update step indicators styling
    function updateStepIndicators() {
        const steps = document.querySelectorAll('.step');

        steps.forEach(step => {
            const stepNum = parseInt(step.dataset.step);
            const stepNumber = step.querySelector('.step-number');
            const stepTitle = step.querySelector('.step-title h6');
            const stepSubtitle = step.querySelector('.step-title small');

            if (stepNum === currentStep) {
                // Current active step
                stepNumber.className = 'step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center';
                stepTitle.classList.remove('text-muted');
                stepTitle.classList.add('text-dark');
                if (stepSubtitle) stepSubtitle.classList.remove('text-muted');
                step.style.cursor = 'default';
            } else if (stepNum < currentStep) {
                // Completed steps - green styling
                stepNumber.className = 'step-number bg-success text-white rounded-circle d-flex align-items-center justify-content-center';
                stepNumber.innerHTML = '<i class="fas fa-check"></i>';
                stepTitle.classList.remove('text-muted');
                stepTitle.classList.add('text-dark');
                if (stepSubtitle) stepSubtitle.classList.remove('text-muted');
                step.style.cursor = 'pointer';
            } else {
                // Future steps - gray styling
                stepNumber.className = 'step-number bg-light text-muted border rounded-circle d-flex align-items-center justify-content-center';
                stepNumber.textContent = stepNum;
                stepTitle.classList.add('text-muted');
                if (stepSubtitle) stepSubtitle.classList.add('text-muted');
                step.style.cursor = 'not-allowed';
            }
        });

        // Update step counter
        const currentStepSpan = document.getElementById('currentStep');
        if (currentStepSpan) {
            currentStepSpan.textContent = currentStep;
        }
    }

    // Update navigation buttons
    function updateNavigationButtons() {
        const prevBtn = document.getElementById('prevStepBtn');
        const nextBtn = document.getElementById('nextStepBtn');

        if (prevBtn) {
            prevBtn.disabled = currentStep === 1;
        }

        if (nextBtn) {
            if (currentStep === totalSteps) {
                // Final step - hide next button (use submit button instead)
                nextBtn.style.display = 'none';
            } else {
                nextBtn.style.display = 'block';
                nextBtn.innerHTML = `
                    <span class="spinner-container" style="display:none;">
                        <i class="fas fa-spinner fa-spin me-2"></i>
                    </span>
                    Next
                    <i class="fas fa-arrow-right ms-2"></i>
                `;
            }
        }
    }

    // Next step function
    function nextStep() {
        // Validate current step before proceeding
        if (!validateStep(currentStep)) {
            return;
        }

        if (currentStep < totalSteps) {
            goToStep(currentStep + 1);
        }
    }

    // Validate current step
    function validateStep(stepIndex) {
        // Get the current step content
        const currentStepContent = document.querySelector('.step-content[data-step="' + stepIndex + '"]');
        if (!currentStepContent) return true;

        let isValid = true;

        // Step 1: Validate Meeting Details
        if (stepIndex === 1) {
            const chairPerson = document.getElementById('chair_person');
            if (chairPerson && !chairPerson.value.trim()) {
                isValid = false;
                chairPerson.classList.add('is-invalid');
            } else if (chairPerson) {
                chairPerson.classList.remove('is-invalid');
            }
        }

        // Step 2: Validate IRB Members (check if table has members)
        if (stepIndex === 2) {
            const memberTable = currentStepContent.querySelector('table tbody');
            if (memberTable) {
                const rows = memberTable.querySelectorAll('tr');
                let hasMembers = false;
                rows.forEach(row => {
                    if (!row.textContent.includes('No member added yet')) {
                        hasMembers = true;
                    }
                });
                // Show validation error on the table if no members
                if (!hasMembers) {
                    isValid = false;
                    memberTable.classList.add('is-invalid');
                    showToast('error', 'Please add at least one IRB member');
                } else {
                    memberTable.classList.remove('is-invalid');
                }
            }
        }

        // Step 3: Validate Agenda Items
        if (stepIndex === 3) {
            const agendaTime = document.getElementById('agenda_time');
            const location = document.getElementById('location');
            const agendaHeading = document.getElementById('agenda_heading');

            if (agendaTime && !agendaTime.value.trim()) {
                isValid = false;
                agendaTime.classList.add('is-invalid');
            } else if (agendaTime) {
                agendaTime.classList.remove('is-invalid');
            }

            if (location && !location.value.trim()) {
                isValid = false;
                location.classList.add('is-invalid');
            } else if (location) {
                location.classList.remove('is-invalid');
            }

            if (agendaHeading && !agendaHeading.value.trim()) {
                isValid = false;
                agendaHeading.classList.add('is-invalid');
            } else if (agendaHeading) {
                agendaHeading.classList.remove('is-invalid');
            }
        }

        if (!isValid) {
            showToast('error', 'Please fill in all required fields for this step');
        }

        return isValid;
    }

    // Clear validation error when user starts typing
    function clearValidationOnInput() {
        const inputs = document.querySelectorAll('.form-control, .form-select');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('is-invalid');
            });
            input.addEventListener('change', function() {
                this.classList.remove('is-invalid');
            });
        });

        // Also clear validation on table
        const tables = document.querySelectorAll('table tbody');
        tables.forEach(table => {
            table.addEventListener('click', function() {
                this.classList.remove('is-invalid');
            });
        });
    }

    // Clear all validation errors
    function clearAllValidation() {
        const invalidFields = document.querySelectorAll('.is-invalid');
        invalidFields.forEach(field => {
            field.classList.remove('is-invalid');
        });
    }

    // Previous step function
    function prevStep() {
        if (currentStep > 1) {
            goToStep(currentStep - 1);
        }
    }

    // Update review summary
    function updateReviewSummary() {
        // Meeting Details
        const chairPerson = document.getElementById('chair_person');
        const preparer = document.getElementById('preparer');

        if (chairPerson) {
            document.getElementById('reviewChairPerson').textContent = chairPerson.value || '-';
        }
        if (preparer) {
            document.getElementById('reviewPreparer').textContent = preparer.value || '-';
        }

        // IRB Members
        const guests = document.getElementById('guests');
        if (guests) {
            document.getElementById('reviewGuests').textContent = guests.value || '-';
        }

        // Agenda Items
        const agendaTime = document.getElementById('agenda_time');
        const location = document.getElementById('location');
        const agendaHeading = document.getElementById('agenda_heading');
        const oldBusiness = document.getElementById('old_business');
        const newBusiness = document.getElementById('new_business');

        if (agendaTime) {
            document.getElementById('reviewTime').textContent = agendaTime.value || '-';
        }
        if (location) {
            document.getElementById('reviewLocation').textContent = location.value || '-';
        }
        if (agendaHeading) {
            document.getElementById('reviewHeading').textContent = agendaHeading.value || '-';
        }
        if (oldBusiness) {
            document.getElementById('reviewOldBusiness').textContent = oldBusiness.value || '-';
        }
        if (newBusiness) {
            document.getElementById('reviewNewBusiness').textContent = newBusiness.value || '-';
        }
    }

    function assignMember(memberName) {
        const currentMembers = document.getElementById('currentMembers');
        const availableMembers = document.getElementById('availableMembers');
        
        // Find the member element in available members
        const memberElement = document.getElementById('available-' + memberName);
        
        if (memberElement) {
            // Create the new element for current members with Un-Assign button
            const newLi = document.createElement('li');
            newLi.id = 'current-' + memberName;
            newLi.style.listStyle = 'none';
            newLi.className = 'mb-3';
            newLi.innerHTML = `
                <div class="d-flex justify-content-between gap-2">
                    <p>${memberName}</p>
                    <button class="btn btn-danger btn-sm" onclick="unAssignMember('${memberName}')">
                        <i class="fas fa-cancel me-1"></i>Un-Assign
                    </button>
                </div>
            `;
            
            // Add to current members list
            currentMembers.appendChild(newLi);
            
            // Remove from available members
            memberElement.remove();
        }
    }

    function unAssignMember(memberName) {
        const currentMembers = document.getElementById('currentMembers');
        const availableMembers = document.getElementById('availableMembers');
        
        // Find the member element in current members
        const memberElement = document.getElementById('current-' + memberName);
        
        if (memberElement) {
            // Create the new element for available members with Assign button
            const newLi = document.createElement('li');
            newLi.id = 'available-' + memberName;
            newLi.style.listStyle = 'none';
            newLi.className = 'mb-3';
            newLi.innerHTML = `
                <div class="d-flex justify-content-between gap-2">
                    <p>${memberName}</p>
                    <button class="btn btn-success btn-sm" onclick="assignMember('${memberName}')">
                        <i class="fas fa-check me-1"></i>Assign
                    </button>
                </div>
            `;
            
            // Add to available members list
            availableMembers.appendChild(newLi);
            
            // Remove from current members
            memberElement.remove();
        }
    }

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
    document.getElementById('agendaForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        try {
            const response = await fetch('/admin/handlers/add_agenda_record.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.status === 'success') {
                showToast('success', result.message);
                // Optionally redirect or reset form
                window.location.reload();
                // window.location.href = '/some-success-page';
            } else {
                showToast('error', result.message);
            }
        } catch (error) {
            showToast('error', 'An unexpected error occurred.');
        }
    });
</script>