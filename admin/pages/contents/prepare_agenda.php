<?php

// Authentication check
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /login');
    exit;
}

// Include CSRF protection
require_once '../../includes/functions/csrf.php';

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
function esc($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
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
        <?php echo esc($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo esc($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="agenda-preparation p-4 p-lg-5">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Agenda Preparation</h2>
            <p class="text-muted mb-0">Prepare the agenda for upcoming IRB meetings</p>
        </div>
        <span class="badge bg-success fs-6">Active</span>
    </div>

    <!-- Institution Header -->
    <div class="card mb-4 border-primary">
        <div class="card-body text-center bg-primary bg-opacity-10 py-3">
            <h4 class="text-primary mb-1 fw-bold">NOGUCHI MEMORIAL INSTITUTE FOR MEDICAL RESEARCH-IRB</h4>
            <h5 class="text-muted mb-0">Institutional Review Board</h5>
        </div>
    </div>

    <!-- Actions Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-bold">Actions</h6>
                </div>
                <div class="card-body">
                    <!-- Initial Application Section -->
                    <div class="row mb-4">
                        <div class="col-md-3">

                            <input type="checkbox">
                            <label class="form-label fw-semibold">Print Report Date</label>
                        </div>

                        <div class="col-md-3">
                            <input type="checkbox">
                            <label class="form-label fw-semibold">Print Sites</label>
                        </div>

                        <div class="col-md-3">

                            <input type="checkbox">
                            <label class="form-label fw-semibold">Print Co-Investigators</label>
                        </div>

                        <div class="col-md-3">

                            <input type="checkbox">
                            <label class="form-label fw-semibold">Print Item Numbers</label>
                        </div>
                    </div>

                   

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <button class="btn btn-primary btn-md">
                                <i class="fas fa-eye me-1"></i>Preview (Large fonts)
                            </button>
                        </div>

                        <div class="col-md-3">
                            <button class="btn btn-primary btn-md">
                                <i class="fas fa-eye me-1"></i>Preview (Small fonts)
                            </button>
                        </div>

                         <div class="col-md-3">
                            <button class="btn btn-primary btn-md">
                                <i class="fas fa-download me-1"></i>Download PDF (Large fonts)
                            </button>
                        </div>

                        <div class="col-md-3">
                            <button class="btn btn-primary btn-md">
                                <i class="fas fa-dowload me-1"></i>Download PDF (Small fonts)
                            </button>
                        </div>
                    </div>

                

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <button class="btn btn-primary btn-md">
                                <i class="fas fa-download me-1"></i>Download Word (Large fonts)
                            </button>
                        </div>

                        <div class="col-md-3">
                            <button class="btn btn-primary btn-md">
                                <i class="fas fa-download me-1"></i>Download Word (Small fonts)
                            </button>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>

    <!-- Main Form Content -->
    <form class="needs-validation" id="agendaForm">
        <?php echo csrf_token_field(); ?>
        <div class="main-content">
            <!-- Study Header Section -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">Meeting Date<span class="text-primary ms-3"><?php echo htmlspecialchars($nextMeeting->format('Y-m-d') ?? ""); ?></span></h6>

                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-semibold">Chair Person</label>
                                    <select class="form-select" name="chair_person" id="chair_person">
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

            <div class="row mb-4">
                <!-- IRB Member Section -->
                <div class="col-md-6">
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


                <!-- Agenda Details Section -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold">Agenda Detials</h6>
                        </div>
                        <div class="card-body">

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Time</label>
                                    <input type="time" name="agend_time" id="agenda_time" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Location</label>
                                    <input type="text" name="location" id="location" class="form-control">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Agenda Heading</label>
                                    <textarea type="text" name="agenda_heading" id="agenda_heading" class="form-control"></textarea>
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




        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-success me-2">
                <i class="fas fa-save me-1"></i> Save Agenda
            </button>
            <a class="btn btn-secondary" href="/">
                <i class="fas fa-times me-1"></i> Cancel
            </a>
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
                            <ul class="mw-[300px]">
                                <li style="list-style: none;" class="mb-3">
                                    <div class="d-flex justify-content-between gap-2">
                                        <p>Dr. John Doe</p>
                                        <button class="btn btn-success btn-sm" onclick="assignMember()">
                                            <i class="fas fa-check me-1"></i>Assign
                                        </button>
                                    </div>
                                </li>

                                <li style="list-style: none;" class="mb-3">
                                    <div class="d-flex justify-content-between gap-2">
                                        <p>Dr. Kate Snow</p>
                                        <button class="btn btn-success btn-sm" onclick="assignMember()">
                                            <i class="fas fa-check me-1"></i>Assign
                                        </button>
                                    </div>
                                </li>
                            </ul>

                        </div>



                        <div class="bg-light p-3 my-3">
                            <h4 class="text-md">Current Board members (Assigned)</h4>
                        </div>
                        <ul>
                            <li style="list-style: none;" class="mb-3">
                                <div class="d-flex justify-content-between gap-2">
                                    <p>Dr. Mary Jane</p>
                                    <button class="btn btn-danger btn-sm" onclick="unAssignMember()">
                                        <i class="fas fa-cancel me-1"></i>Un-Assign
                                    </button>
                                </div>
                            </li>

                            <li style="list-style: none;" class="mb-3">
                                <div class="d-flex justify-content-between gap-2">
                                    <p>Dr. Michael Brown</p>
                                    <button class="btn btn-danger btn-sm" onclick="unAssignMember()">
                                        <i class="fas fa-cancel me-1"></i>Un-Assign
                                    </button>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    function assignMember() {

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