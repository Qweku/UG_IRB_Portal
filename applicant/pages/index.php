<?php

// Check if applicant is logged in
if (!is_applicant_logged_in()) {
    header('Location: /login');
    exit;
}

$userId = $_SESSION['user_id'] ?? 0;
$userName = $_SESSION['full_name'] ?? 'Applicant';

// Get applicant stats
$stats = getApplicantStats($userId);

// Check for draft application
$draftApplication = getDraftApplication($userId);
$hasDraftApplication = $draftApplication !== null;

error_log("Applicant ID: $userId, Has Draft Application: " . ($hasDraftApplication ? 'Yes' : 'No'));

$applicant_type = $_SESSION['applicant_type'] ?? 'student';

// Check first login for password modal
$showPasswordModal = isset($_SESSION['is_first']) && $_SESSION['is_first'] == 1;

?>


<div class="container-fluid dashboard-container">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3">

            <!-- Welcome Header -->
            <div class="welcome-section text-white mb-4 fade-in-up">
                <div class="welcome-content">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="welcome-title">Welcome back, <?php echo htmlspecialchars($userName); ?>!</h1>
                            <p class="welcome-subtitle">Manage your IRB applications and track their progress</p>
                        </div>
                        <div class="welcome-icon d-none d-lg-block">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards Row -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="stats-card-modern text-white bg-primary fade-in-up">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stats-icon" style="background: rgba(255,255,255,0.2);">
                                        <i class="fas fa-file-alt"></i>
                                    </div>

                                    <div class="stats-label">Total Applications</div>
                                </div>
                                <div>
                                    <div class="stats-number"><?php echo $stats['total']; ?></div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stats-card-modern text-white fade-in-up" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stats-icon" style="background: rgba(255,255,255,0.2);">
                                        <i class="fas fa-clock"></i>
                                    </div>

                                    <div class="stats-label">Under Review</div>
                                </div>
                                <div>
                                    <div class="stats-number"><?php echo $stats['under_review']; ?></div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stats-card-modern text-white fade-in-up" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stats-icon" style="background: rgba(255,255,255,0.2);">
                                        <i class="fas fa-check-circle"></i>
                                    </div>

                                    <div class="stats-label">Approved</div>
                                </div>
                                <div>
                                    <div class="stats-number"><?php echo $stats['approved']; ?></div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stats-card-modern text-white fade-in-up" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stats-icon" style="background: rgba(255,255,255,0.2);">
                                        <i class="fas fa-times-circle"></i>
                                    </div>

                                    <div class="stats-label">Rejected</div>
                                </div>
                                <div>
                                    <div class="stats-number"><?php echo $stats['rejected']; ?></div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Application Limit Warning -->
            <?php //if (!$stats['can_submit']): 
            ?>
            <!-- <div class="alert alert-warning alert-custom mb-4 fade-in-up">
                    <div class="alert-icon" style="background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%); color: #856404;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-bold">Maximum Applications Reached</h6>
                        <p class="mb-0 text-muted">You have submitted the maximum of 3 applications. Please contact the IRB office if you need to submit additional applications.</p>
                    </div>
                </div> -->
            <?php //endif; 
            ?>

            <!-- Application Types Section -->
            <?php if ($hasDraftApplication): ?>
                <!-- Ongoing Application Section -->
                <div class="mb-4 fade-in-up">
                    <div class="section-header">
                        <div class="section-icon" style="background: linear-gradient(135deg, rgba(40, 167, 69, 0.1) 0%, rgba(40, 167, 69, 0.05) 100%); color: #28a745;">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                        <div>
                            <h4>Continue Your Application</h4>
                            <p>You have an ongoing application that you can continue working on</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="ongoing-application-card bg-white">
                                <div class="card-body p-4">
                                    <div class="row align-items-center">
                                        <div class="col-lg-8">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="card-header-icon me-3">
                                                    <i class="fas fa-file-signature"></i>
                                                </div>
                                                <div>
                                                    <h5 class="protocol-number mb-0">
                                                        <?php echo htmlspecialchars($draftApplication['protocol_number'] ?? 'Draft Application'); ?>
                                                    </h5>
                                                    <span class="badge bg-<?php 
                                                        $statusColors = [
                                                            'draft' => 'secondary',
                                                            'submitted' => 'info',
                                                            'under_review' => 'warning'
                                                        ];
                                                        echo $statusColors[$draftApplication['status']] ?? 'secondary';
                                                    ?> status-badge">
                                                        <?php echo ucfirst(htmlspecialchars($draftApplication['status'] ?? 'Draft')); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <h6 class="study-title">
                                                <?php echo htmlspecialchars($draftApplication['study_title'] ?? 'Untitled Application'); ?>
                                            </h6>
                                            
                                            <!-- Progress Bar -->
                                            <div class="mt-4">
                                                <div class="progress-info">
                                                    <span class="progress-label">Application Progress</span>
                                                    <span class="progress-step">
                                                        <i class="fas fa-tasks me-1"></i>
                                                        Step <?php echo ($draftApplication['current_step'] ?? 1); ?> of 5
                                                    </span>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar bg-success" role="progressbar" 
                                                         style="width: <?php echo getApplicationProgress($draftApplication['current_step'] ?? 1); ?>%">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <p class="last-updated mb-0">
                                                <i class="fas fa-clock me-2"></i>
                                                Last updated: <?php echo isset($draftApplication['updated_at']) ? date('M d, Y \a\t g:i A', strtotime($draftApplication['updated_at'])) : 'Recently'; ?>
                                            </p>
                                        </div>
                                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
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
                                            <a href="<?php echo $continueUrl; ?>" class="btn btn-success continue-btn">
                                                <i class="fas fa-edit me-2"></i>Continue Application
                                            </a>
                                            <div class="mt-3">
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Application ID: <?php echo htmlspecialchars($draftApplication['id'] ?? 'N/A'); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Normal Submit New Application Section -->
                <div class="mb-4 fade-in-up">
                    <div class="section-header">
                        <div class="section-icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <div>
                            <h4>Submit New Application</h4>
                            <p>Select the appropriate application type for your research</p>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Application Type 1: Students -->
                        <?php if ($applicant_type === 'student'): ?>
                            <div class="col-md-12 mb-3">
                                <div class="application-card bg-white">
                                    <div class="card-icon" style="background: linear-gradient(135deg, rgba(36, 63, 129, 0.1) 0%, rgba(36, 63, 129, 0.05) 100%); color: var(--royal-blue);">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <div class="card-body text-center">
                                        <h5 class="card-title">Initial Submission Form A</h5>
                                        <p class="card-subtitle">Students</p>

                                        <a href="/add-protocol/student-application" class="btn btn-primary mb-3">
                                            <i class="fas fa-plus me-2"></i>Start Application
                                        </a>

                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Application Type 2: NMIMR Researchers -->
                        <?php if ($applicant_type === 'nmimr'): ?>
                            <div class="col-md-12 mb-3">
                                <div class="application-card bg-white">
                                    <div class="card-icon" style="background: linear-gradient(135deg, rgba(39, 174, 96, 0.1) 0%, rgba(39, 174, 96, 0.05) 100%); color: #27ae60;">
                                        <i class="fas fa-flask"></i>
                                    </div>
                                    <div class="card-body text-center">
                                        <h5 class="card-title">Initial Submission Form A</h5>
                                        <p class="card-subtitle">NMIMR Researchers</p>

                                        <a href="/add-protocol/nmimr-application" class="btn btn-success mb-3">
                                            <i class="fas fa-plus me-2"></i>Start Application
                                        </a>

                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Application Type 3: Non-NMIMR Researchers -->
                        <?php if ($applicant_type === 'non_nmimr'): ?>
                            <div class="col-md-12 mb-3">
                                <div class="application-card bg-white">
                                    <div class="card-icon" style="background: linear-gradient(135deg, rgba(52, 152, 219, 0.1) 0%, rgba(52, 152, 219, 0.05) 100%); color: #3498db;">
                                        <i class="fas fa-university"></i>
                                    </div>
                                    <div class="card-body text-center">
                                        <h5 class="card-title">Initial Submission Form A</h5>
                                        <p class="card-subtitle">Non-NMIMR Researchers</p>

                                        <a href="/add-protocol/non-nmimr-application" class="btn mb-3" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white;">
                                            <i class="fas fa-plus me-2"></i>Start Application
                                        </a>

                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Quick Links Section -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="quick-link-card bg-white fade-in-up">
                        <div class="card-body p-4">
                            <div class="card-icon">
                                <i class="fas fa-list"></i>
                            </div>
                            <h5>My Applications</h5>
                            <p>View and track all your submitted IRB applications</p>
                            <a href="/applicant-dashboard/applications" class="btn btn-outline-primary">
                                <i class="fas fa-eye me-2"></i>View Applications
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="quick-link-card bg-white fade-in-up">
                        <div class="card-body p-4">
                            <div class="card-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <h5>My Profile</h5>
                            <p>View and manage your profile information</p>
                            <a href="/applicant-dashboard/profile" class="btn btn-outline-primary">
                                <i class="fas fa-id-card me-2"></i>View Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Password Reset Modal (for first login) -->
<?php if ($showPasswordModal): ?>
    <div class="modal fade" id="passwordResetModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow" style="border-radius: 16px; border: none;">
                <div class="modal-header" style="background: linear-gradient(135deg, var(--royal-blue) 0%, var(--royal-blue-light) 100%); border-radius: 16px 16px 0 0;">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-lock me-2"></i>Set Your Password
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted mb-4">This is your first login. Please set a secure password to continue.</p>
                    <form id="passwordResetForm" method="post">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="newPassword" name="new_password" minlength="8" required placeholder="Enter new password">
                            </div>
                            <small class="text-muted">Must be at least 8 characters</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required placeholder="Confirm password">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2" id="submitBtn" style="border-radius: 25px; font-weight: 600;">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true" style="display: none;"></span>
                            <i class="fas fa-save me-2"></i>Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Show password modal if needed
        <?php if ($showPasswordModal): ?>
            const passwordModal = new bootstrap.Modal(document.getElementById('passwordResetModal'), {
                backdrop: 'static',
                keyboard: false
            });
            passwordModal.show();
        <?php endif; ?>

        // Handle password form submission
        const passwordForm = document.getElementById('passwordResetForm');
        if (passwordForm) {
            passwordForm.addEventListener('submit', (e) => {
                const p1 = document.getElementById('newPassword').value;
                const p2 = document.getElementById('confirmPassword').value;

                if (p1 !== p2) {
                    e.preventDefault();
                    alert('Passwords do not match.');
                } else if (p1.length < 8) {
                    e.preventDefault();
                    alert('Password must be at least 8 characters.');
                } else {
                    const spinner = document.querySelector('#submitBtn .spinner-border');
                    spinner.style.display = 'inline-block';
                    document.getElementById('submitBtn').disabled = true;
                }
            });
        }
    });
</script>