<?php

// Check if applicant is logged in
// if (!is_applicant_logged_in()) {
//     header('Location: /login');
//     exit;
// }

$userId = $_SESSION['user_id'] ?? 0;
$userName = $_SESSION['full_name'] ?? 'Applicant';

$profile = getApplicantProfile($userId);

// Check for draft application
$applicant_type = $profile['applicant_type'] ?? 'student';

if ($applicant_type === 'nmimr') {
    $draftApplication = getDraftApplication($userId, 'nmimr_applications');
} elseif ($applicant_type === 'non_nmimr') {
    $draftApplication = getDraftApplication($userId, 'non_nmimr_applications');
} else {
    $draftApplication = getDraftApplication($userId, 'student_applications');
}
$hasDraftApplication = !empty($draftApplication);

// Get applicant's studies
$studies = $applicant_type == "student" ? getStudentApplicantStudies($userId) : ($applicant_type == "nmimr" ? getNMIMRApplicantStudies($userId) : getNONNMIMRApplicantStudies($userId));

// Handle status filter
$status_filter = $_GET['status'] ?? 'all';
if ($status_filter !== 'all') {
    $studies = array_filter($studies, fn($s) => ($s['status'] ?? '') === $status_filter);
}

?>

<style>
    /* Studies Page Specific Styles */
    .page-header-section {
        background:linear-gradient(135deg, #35493d 0%, #445e50 100%);
        border-radius: 16px;
        padding: 32px;
        margin-bottom: 24px;
        box-shadow: 0 10px 40px rgba(44, 62, 80, 0.2);
    }

    .page-header-section h2 {
        font-weight: 700;
        margin-bottom: 8px;
    }

    .page-header-section p {
        opacity: 0.9;
        margin: 0;
    }

    /* Stats Cards */
    .stats-card-mini {
        border: none;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .stats-card-mini:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }

    .stats-card-mini .card-body {
        padding: 20px;
    }

    /* Filter Card */
    .filter-card {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        margin-bottom: 24px;
    }

    .filter-card .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: none;
        padding: 16px 24px;
    }

    .filter-card .card-header h5 {
        margin: 0;
        font-weight: 600;
        color: #2c3e50;
    }

    .filter-card .card-body {
        padding: 24px;
    }

    /* Modern Table */
    .modern-table {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .modern-table thead th {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: white;
        border: none;
        padding: 16px 20px;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }

    .modern-table tbody tr {
        transition: all 0.3s ease;
        border-bottom: 1px solid #f1f3f4;
    }

    .modern-table tbody tr:hover {
        background-color: rgba(36, 63, 129, 0.03);
    }

    .modern-table tbody td {
        padding: 16px 20px;
        border: none;
        vertical-align: middle;
    }

    /* Status Badges */
    .status-badge {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Action Buttons */
    .action-btn {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }

    .action-btn:hover {
        transform: scale(1.1);
    }

    /* Help Card */
    .help-card {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .help-card .card-header {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: white;
        border: none;
        padding: 16px 24px;
    }

    .help-card .card-header h5 {
        margin: 0;
        font-weight: 600;
    }

    .help-card .card-body {
        padding: 24px;
    }

    .help-item {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        padding: 16px;
        background: #f8f9fa;
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .help-item:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }

    .help-item i {
        font-size: 20px;
        width: 32px;
        text-align: center;
        color: var(--royal-blue);
    }

    .help-item h6 {
        margin: 0 0 4px 0;
        font-weight: 600;
        color: #2c3e50;
    }

    .help-item p {
        margin: 0;
        font-size: 14px;
        color: #6c757d;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 40px;
    }

    .empty-state-icon {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
    }

    .empty-state-icon i {
        font-size: 48px;
        color: #adb5bd;
    }

    .empty-state h5 {
        color: #2c3e50;
        margin-bottom: 8px;
    }

    .empty-state p {
        color: #6c757d;
        margin-bottom: 24px;
    }

    /* Form Controls */
    .form-select,
    .form-control {
        border-radius: 10px;
        border: 2px solid #e9ecef;
        padding: 10px 16px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .form-select:focus,
    .form-control:focus {
        border-color: var(--royal-blue);
        box-shadow: 0 0 0 3px rgba(36, 63, 129, 0.1);
    }

    /* Animations */
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

    .fade-in {
        animation: fadeIn 0.5s ease forwards;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3">

            <!-- Page Header -->
            <div class="page-header-section text-white fade-in">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-1"><i class="fas fa-list me-3"></i>My Applications</h2>
                        <p class="mb-0">View and track all your submitted IRB applications</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <?php if (!$hasDraftApplication): ?>
                            <a href="/applicant-dashboard" class="btn btn-light" style="border-radius: 25px; font-weight: 600;">
                                <i class="fas fa-plus me-2"></i>New Application
                            </a>
                        <?php else: ?>
                            <?php
                            $continueUrl = '';
                            $appType = $draftApplication['application_type'] ?? 'student';
                            switch ($appType) {
                                case 'nmimr':
                                    $continueUrl = '/add-protocol/nmimr-application';
                                    break;
                                case 'non_nmimr':
                                    $continueUrl = '/add-protocol/non-nmimr-application';
                                    break;
                                default:
                                    $continueUrl = '/add-protocol/student-application';
                            }
                            $continueUrl .= '?application_id=' . ($draftApplication['id'] ?? '');
                            ?>
                            <a href="<?php echo $continueUrl; ?>" class="btn btn-outline-light" style="border-radius: 25px; font-weight: 600;">
                                <i class="fas fa-edit me-2"></i>Continue Draft
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Stats Summary -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="stats-card-mini bg-white fade-in">
                        <div class="card-body text-center">
                            <h3 class="mb-0" style="color: #2c3e50;"><?php echo count($studies); ?></h3>
                            <small class="text-muted">Total Applications</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stats-card-mini bg-white border border-info fade-in">
                        <div class="card-body text-center">
                            <h3 class="mb-0 text-info"><?php echo count(array_filter($studies, fn($s) => ($s['status'] ?? '') === 'submitted')); ?></h3>
                            <small class="text-muted">Submitted</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stats-card-mini bg-white border border-warning fade-in">
                        <div class="card-body text-center">
                            <h3 class="mb-0 text-warning"><?php echo count(array_filter($studies, fn($s) => ($s['status'] ?? '') === 'under_review')); ?></h3>
                            <small class="text-muted">Under Review</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stats-card-mini bg-white border border-success fade-in">
                        <div class="card-body text-center">
                            <h3 class="mb-0 text-success"><?php echo count(array_filter($studies, fn($s) => ($s['status'] ?? '') === 'approved')); ?></h3>
                            <small class="text-muted">Approved</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filter-card fade-in">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-filter me-2"></i>Filter Applications</h5>
                    <a href="/applicant-dashboard/studies" class="btn btn-sm btn-outline-secondary" style="border-radius: 20px;">
                        <i class="fas fa-sync-alt me-1"></i> Reset
                    </a>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="status" class="form-label fw-semibold">Status</label>
                            <select class="form-select" id="status" name="status" onchange="this.form.submit()">
                                <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Statuses</option>
                                <option value="submitted" <?php echo $status_filter == 'submitted' ? 'selected' : ''; ?>>Submitted</option>
                                <option value="under_review" <?php echo $status_filter == 'under_review' ? 'selected' : ''; ?>>Under Review</option>
                                <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Applications Table -->
            <div class="fade-in">
                <table class="table modern-table" id="studiesTable">
                    <thead>
                        <tr>
                            <th><i class="fas fa-file-alt me-2"></i>Study Title</th>
                            <th><i class="fas fa-calendar me-2"></i>Date Submitted</th>
                            <th><i class="fas fa-tag me-2"></i>Application Type</th>
                            <th><i class="fas fa-info-circle me-2"></i>Status</th>
                            <th><i class="fas fa-cogs me-2"></i>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($studies)): ?>
                            <?php foreach ($studies as $study): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($study['study_title'] ?? 'Untitled Study'); ?></strong>
                                        <br>
                                        <!-- <small class="text-muted">ID: <?php //echo htmlspecialchars($study['id'] ?? 'N/A'); 
                                                                            ?></small> -->
                                    </td>
                                    <td>
                                        <?php
                                        $date = $study['created_at'] ?? null;
                                        echo $date ? date('M d, Y', strtotime($date)) : 'N/A';
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars(getApplicationTypeName($study['application_type'] ?? 'student')); ?>
                                    </td>
                                    <td>
                                        <span class="badge status-badge bg-<?php echo getStatusColor($study['status'] ?? 'submitted'); ?>">
                                            <?php echo getStatusLabel($study['status'] ?? 'submitted'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="action-btn btn btn-outline-primary me-1" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="action-btn btn btn-outline-secondary me-1" title="Download">
                                            <i class="fas fa-download"></i>
                                        </button>
                                        <?php if (($study['status'] ?? '') === 'rejected'): ?>
                                            <button class="action-btn btn btn-outline-warning" title="Resubmit">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="fas fa-folder-open"></i>
                                        </div>
                                        <h5>No Applications Found</h5>
                                        <p>You haven't submitted any applications yet.</p>
                                        <a href="/applicant-dashboard" class="btn btn-primary" style="border-radius: 25px; padding: 12px 30px;">
                                            <i class="fas fa-plus me-2"></i>Submit Your First Application
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Help Section -->
            <div class="help-card mt-4 fade-in">
                <div class="card-header">
                    <h5><i class="fas fa-question-circle me-2"></i>Need Help?</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="mailto:nirb@noguchi.ug.edu.gh" class="help-item text-decoration-none">
                                <i class="fas fa-envelope"></i>
                                <div>
                                    <h6>Email Support</h6>
                                    <p class="mb-0">nirb@noguchi.ug.edu.gh</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="help-item">
                                <i class="fas fa-phone"></i>
                                <div>
                                    <h6>Phone</h6>
                                    <p class="mb-0">+233 302 501 382 / 383</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="help-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <div>
                                    <h6>Location</h6>
                                    <p class="mb-0">NMIMR, University of Ghana</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>