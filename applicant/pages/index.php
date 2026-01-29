<?php

// session_name('applicant_session');
// require_once '../../includes/functions/helpers.php';

$userId = null;
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    error_log("User ID is: " . $_SESSION['user_id']);
}



// Check if first login and show modal
$showPasswordModal = isset($_SESSION['is_first']) && $_SESSION['is_first'] == 1;

error_log("First time : " . $showPasswordModal);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {

    $newPassword     = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $userId          = (int) $_SESSION['user_id'];

    if ($newPassword !== $confirmPassword) {
        header('Location: /applicant-dashboard?error=password_mismatch');
        exit;
    }

    if (strlen($newPassword) < 8) {
        header('Location: /applicant-dashboard?error=password_short');
        exit;
    }

    $db   = new Database();
    $conn = $db->connect();

    $hash = password_hash($newPassword, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
    UPDATE users
    SET password_hash = ?, is_first = 0
    WHERE id = ?
");

    if ($stmt->execute([$hash, $userId])) {
        $_SESSION['is_first'] = 0;
        header('Location: /applicant-dashboard');
    } else {
        header('Location: /applicant-dashboard?error=update_failed');
    }
    exit;
}

// Get filter parameters
$status = $_GET['status'] ?? 'all';
$review_type = $_GET['type'] ?? '';
$pi_name = $_GET['pi'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Fetch studies with filters
$studies = getStudies($status, $review_type, $pi_name, 'date_received DESC');

// Filter by date range if provided
if (!empty($date_from) || !empty($date_to)) {
    $filtered_studies = [];
    foreach ($studies as $study) {
        $received_date = strtotime($study['date_received']);
        $from_check = empty($date_from) || $received_date >= strtotime($date_from);
        $to_check = empty($date_to) || $received_date <= strtotime($date_to);
        if ($from_check && $to_check) {
            $filtered_studies[] = $study;
        }
    }
    $studies = $filtered_studies;
}
?>

<div class="applicant-dashboard container-fluid mt-4">
    <!-- Welcome Header -->
    <div class="welcome-header text-white p-4 rounded mb-4 position-relative overflow-hidden">
        <div class="header-gradient"></div>
        <div class="d-flex align-items-center position-relative z-1">
            <!-- <img src="../../admin/assets/images/ug-nmimr-logo.jpg" alt="UG NMIMR Logo" class="me-3 logo-shadow" style="height: 60px;"> -->
            <div>
                <h2 class="mb-1 fw-bold">Welcome to UG Hares</h2>
                <p class="mb-0 opacity-75">Applicant Dashboard - Track Your Submitted Studies</p>
            </div>
        </div>
        <div class="header-decoration">
            <i class="fas fa-chart-line"></i>
            <i class="fas fa-clipboard-list"></i>
            <i class="fas fa-search"></i>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stats-card text-white bg-primary">
                <div class="card-body text-center">
                    <i class="fas fa-file-alt fa-2x mb-2"></i>
                    <h4><?php echo count($studies); ?></h4>
                    <p class="mb-0">Total Studies</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stats-card text-white bg-success">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h4><?php echo count(array_filter($studies, fn($s) => strtolower($s['study_status'] ?? '') === 'open')); ?></h4>
                    <p class="mb-0">Open Studies</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stats-card text-white bg-warning">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x mb-2"></i>
                    <h4><?php echo count(array_filter($studies, fn($s) => strtolower($s['study_status'] ?? '') === 'pending')); ?></h4>
                    <p class="mb-0">Pending Studies</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stats-card text-white bg-danger">
                <div class="card-body text-center">
                    <i class="fas fa-times-circle fa-2x mb-2"></i>
                    <h4><?php echo count(array_filter($studies, fn($s) => strtolower($s['study_status'] ?? '') === 'closed')); ?></h4>
                    <p class="mb-0">Closed Studies</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Studies</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>All</option>
                        <option value="open" <?php echo $status == 'open' ? 'selected' : ''; ?>>Open</option>
                        <option value="closed" <?php echo $status == 'closed' ? 'selected' : ''; ?>>Closed</option>
                        <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="type" class="form-label">Type</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">All Types</option>
                        <option value="new" <?php echo $review_type == 'new' ? 'selected' : ''; ?>>New</option>
                        <option value="old" <?php echo $review_type == 'old' ? 'selected' : ''; ?>>Old</option>
                        <option value="revised" <?php echo $review_type == 'revised' ? 'selected' : ''; ?>>Revised</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="pi" class="form-label">Principal Investigator</label>
                    <input type="text" class="form-control" id="pi" name="pi" value="<?php echo htmlspecialchars($pi_name); ?>" placeholder="Search by PI name">
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Studies Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-table me-2"></i>Submitted Studies (<?php echo count($studies); ?>)</h5>
            <div>
                <button class="btn btn-sm btn-outline-primary me-2" onclick="window.location.reload()" title="Refresh Data">
                    <i class="fas fa-sync-alt me-1"></i> Refresh
                </button>
                <a class="btn btn-success btn-lg" href="/applicant-dashboard/add-protocol" title="Submit New Protocol">
                    <i class="fas fa-plus me-1"></i> Add New Protocol
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 modern-table" id="studiesTable">
                    <thead class="table-dark">
                        <tr>
                            <th><i class="fas fa-hashtag me-1"></i>IRB#</th>
                            <th><i class="fas fa-file-alt me-1"></i>Protocol Title</th>
                            <th><i class="fas fa-code-branch me-1"></i>Version</th>
                            <th><i class="fas fa-tag me-1"></i>Type</th>
                            <th><i class="fas fa-user-md me-1"></i>PI</th>
                            <th><i class="fas fa-calendar-check me-1"></i>Date Received</th>
                            <th><i class="fas fa-calendar-times me-1"></i>Expiration Date</th>
                            <th><i class="fas fa-info-circle me-1"></i>Status</th>
                            <th><i class="fas fa-list-ol me-1"></i>Ref Num</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($studies)): ?>
                            <?php foreach ($studies as $study): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($study['protocol_number'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($study['title'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($study['version'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($study['review_type'] ?? ''); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($study['pi_name'] ?? $study['pi'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($study['date_received'] ? date('Y-m-d', strtotime($study['date_received'])) : ''); ?></td>
                                    <td><?php echo htmlspecialchars($study['expiration_date'] ? date('Y-m-d', strtotime($study['expiration_date'])) : ''); ?></td>
                                    <td>
                                        <?php
                                        $status_class = 'bg-secondary';
                                        switch (strtolower($study['study_status'] ?? '')) {
                                            case 'open':
                                                $status_class = 'bg-success';
                                                break;
                                            case 'closed':
                                                $status_class = 'bg-danger';
                                                break;
                                            case 'pending':
                                                $status_class = 'bg-warning';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($study['study_status'] ?? ''); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($study['ref_num'] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">No studies found matching your criteria.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Password Reset Modal -->
<div class="modal fade"
    id="passwordResetModal"
    tabindex="-1"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">

            <div class="modal-header">
                <h5 class="modal-title">Set Your Password</h5>
                <button type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <p class="text-muted">
                    This is your first login. Please set a secure password to continue.
                </p>

                <form id="passwordResetForm"
                    method="post">

                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password"
                            class="form-control"
                            id="newPassword"
                            name="new_password"
                            minlength="8"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password"
                            class="form-control"
                            id="confirmPassword"
                            name="confirm_password"
                            required>
                    </div>

                    <button type="submit"
                        class="btn btn-primary w-100"
                        id="submitBtn">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                        Update Password
                    </button>

                </form>
            </div>
        </div>
    </div>
</div>



<script>
    document.addEventListener('DOMContentLoaded', () => {

        const modalEl = document.getElementById('passwordResetModal');

        if (!modalEl) return;

        const passwordModal = new bootstrap.Modal(modalEl, {
            backdrop: 'static',
            keyboard: false
        });

        // FIRST LOGIN → FORCE MODAL
        <?php if (!empty($showPasswordModal)): ?>
            passwordModal.show();
        <?php endif; ?>

        // SHOW AGAIN AFTER ERROR REDIRECT
        <?php if (isset($_GET['error'])): ?>
            passwordModal.show();

            <?php if ($_GET['error'] === 'password_mismatch'): ?>
                alert('Passwords do not match.');
            <?php elseif ($_GET['error'] === 'password_short'): ?>
                alert('Password must be at least 8 characters.');
            <?php elseif ($_GET['error'] === 'update_failed'): ?>
                alert('Password update failed. Try again.');
            <?php endif; ?>
        <?php endif; ?>

        // CLIENT-SIDE CONFIRM VALIDATION
        document
            .getElementById('passwordResetForm')
            .addEventListener('submit', e => {

                const p1 = document.getElementById('newPassword').value;
                const p2 = document.getElementById('confirmPassword').value;

                if (p1 !== p2) {
                    e.preventDefault();
                    alert('Passwords do not match.');
                } else {
                    // Show spinner and disable button
                    const spinner = document.querySelector('#submitBtn .spinner-border');
                    spinner.style.display = 'inline-block';
                    document.getElementById('submitBtn').disabled = true;
                }
            });

    });
</script>

<style>
/* Header Styles */
.welcome-header {
    background: linear-gradient(135deg, var(--royal-blue) 0%, var(--royal-blue-light) 100%);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border: none;
}

.header-gradient {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
    z-index: 0;
}

.logo-shadow {
    filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
    transition: transform 0.3s ease;
}

.logo-shadow:hover {
    transform: scale(1.05);
}

.header-decoration {
    position: absolute;
    top: 20px;
    right: 20px;
    opacity: 0.3;
    font-size: 2rem;
}

.header-decoration i {
    margin-left: 10px;
    animation: float 3s ease-in-out infinite;
}

.header-decoration i:nth-child(1) { animation-delay: 0s; }
.header-decoration i:nth-child(2) { animation-delay: 1s; }
.header-decoration i:nth-child(3) { animation-delay: 2s; }

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

/* Stats Cards */
.stats-card {
    border-radius: 15px;
    border: none;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    overflow: hidden;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.stats-card .card-body {
    padding: 1.5rem;
}

/* Card Enhancements */
.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    overflow: hidden;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
}

.card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid rgba(0,0,0,0.05);
    padding: 1.5rem;
}

.card-header h5 {
    color: #495057;
    font-weight: 600;
    margin: 0;
}

.card-body {
    padding: 2rem;
}

/* Modern Table */
.modern-table {
    border-radius: 15px;
    overflow: hidden;
}

.modern-table thead th {
    background: linear-gradient(135deg, #343a40 0%, #495057 100%);
    color: white;
    border: none;
    padding: 1rem;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.modern-table tbody tr {
    transition: all 0.3s ease;
}

.modern-table tbody tr:hover {
    background-color: rgba(102, 126, 234, 0.05);
    transform: scale(1.01);
}

.modern-table tbody td {
    padding: 1rem;
    border: none;
    vertical-align: middle;
}

/* Form Controls */
.form-control, .form-select {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
    background: rgba(255,255,255,0.8);
}

.form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    background: white;
}

.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

/* Button Styles */
.btn {
    border-radius: 10px;
    padding: 0.75rem 2rem;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
}

.btn-primary {
    background: linear-gradient(135deg, var(--royal-blue) 0%, var(--royal-blue-light) 100%);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.btn-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
}

.btn-outline-primary {
    border: 2px solid #667eea;
    color: #667eea;
    background: transparent;
}

.btn-outline-primary:hover {
    background: #667eea;
    color: white;
    transform: translateY(-2px);
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeInUp 0.6s ease-out;
}

.card:nth-child(1) { animation-delay: 0.1s; }
.card:nth-child(2) { animation-delay: 0.2s; }
.card:nth-child(3) { animation-delay: 0.3s; }
.card:nth-child(4) { animation-delay: 0.4s; }

/* Responsive adjustments */
@media (max-width: 768px) {
    .welcome-header {
        text-align: center;
    }

    .header-decoration {
        display: none;
    }

    .card-body {
        padding: 1.5rem;
    }

    .stats-card .card-body {
        padding: 1rem;
    }

    .stats-card h4 {
        font-size: 1.5rem;
    }
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
}
</style>

<script>
    // Optional: Add any client-side filtering or enhancements here
    document.addEventListener('DOMContentLoaded', function() {
        // Could add dynamic filtering if needed
    });
</script>