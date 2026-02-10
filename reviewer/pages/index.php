<?php

// NOTE: Authentication is already handled by index.php router
// No duplicate auth check needed here - it causes redirect loops
// because admin/includes/header.php has already sent output.

// Get user data from session (already set by index.php)
$userId = $_SESSION['user_id'] ?? 0;
$userName = $_SESSION['full_name'] ?? 'Reviewer';

// Get reviewer stats
$stats = getReviewerStats($userId);

// Get pending applications
$pendingApplications = getPendingApplications(5);

// Get upcoming deadlines
$deadlines = getReviewerDeadlines($userId);

// Get upcoming meetings
$meetings = getReviewerMeetings();

// Get reviewer's assigned applications
$assignments = getReviewerAssignments($userId);

// Include CSRF protection
// require_once '../../includes/functions/csrf.php';

// Check if first login and show modal
$showPasswordModal = isset($_SESSION['is_first']) && $_SESSION['is_first'] == 1;

error_log("First time : " . $showPasswordModal);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {

    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !csrf_validate()) {
        http_response_code(403);
        die('CSRF validation failed');
    }

    $newPassword     = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $userId          = (int) $_SESSION['user_id'];

    if ($newPassword !== $confirmPassword) {
        header('Location: /reviewer-dashboard?error=password_mismatch');
        exit;
    }

    if (strlen($newPassword) < 8) {
        header('Location: /reviewer-dashboard?error=password_short');
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
        header('Location: /reviewer-dashboard');
    } else {
        header('Location: /reviewer-dashboard?error=update_failed');
    }
    exit;
}

?>



<div class="container-fluid reviewer-dashboard-container">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3">
            
            <!-- Welcome Header -->
            <div class="reviewer-welcome-section text-white mb-4 reviewer-fade-in-up">
                <div class="reviewer-welcome-content">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="reviewer-welcome-title">Welcome back, <?php echo htmlspecialchars($userName); ?>!</h1>
                            <p class="reviewer-welcome-subtitle">Review and manage IRB applications efficiently</p>
                        </div>
                        <div class="reviewer-welcome-icon d-none d-lg-block">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards Row -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="reviewer-stats-card text-white reviewer-fade-in-up" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stats-icon" style="background: rgba(255,255,255,0.2);">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="stats-label">Pending Reviews</div>
                                </div>
                                <div>
                                    <div class="stats-number"><?php echo $stats['pending']; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="reviewer-stats-card text-white reviewer-fade-in-up" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stats-icon" style="background: rgba(255,255,255,0.2);">
                                        <i class="fas fa-spinner"></i>
                                    </div>
                                    <div class="stats-label">In Progress</div>
                                </div>
                                <div>
                                    <div class="stats-number"><?php echo $stats['in_progress']; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="reviewer-stats-card text-white reviewer-fade-in-up" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stats-icon" style="background: rgba(255,255,255,0.2);">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="stats-label">Completed</div>
                                </div>
                                <div>
                                    <div class="stats-number"><?php echo $stats['completed']; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="reviewer-stats-card text-white reviewer-fade-in-up" style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stats-icon" style="background: rgba(255,255,255,0.2);">
                                        <i class="fas fa-tasks"></i>
                                    </div>
                                    <div class="stats-label">Avg. Review Time</div>
                                </div>
                                <div>
                                    <div class="stats-number" style="font-size: 1.8rem;"><?php echo $stats['avg_review_time']; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Row -->
            <div class="row">
                <!-- Pending Applications Section -->
                <div class="col-lg-8 mb-4">
                    <div class="card border-0 shadow-sm reviewer-fade-in-up" style="border-radius: 16px;">
                        <div class="card-header bg-white" style="border-radius: 16px 16px 0 0; padding: 20px; border-bottom: 1px solid #e9ecef;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0" style="font-weight: 700; color: #2c3e50;">
                                        <i class="fas fa-clipboard-list me-2" style="color: #2980b9;"></i>
                                        Pending Applications
                                    </h5>
                                    <small class="text-muted">Applications awaiting review</small>
                                </div>
                                <a href="/reviewer-dashboard/reviews" class="btn btn-outline-primary" style="border-radius: 25px;">
                                    View All <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                        <div class="card-body" style="padding: 20px;">
                            <?php if (!empty($pendingApplications)): ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th style="font-weight: 600; color: #6c757d; font-size: 12px; text-transform: uppercase;">Study Title</th>
                                                <th style="font-weight: 600; color: #6c757d; font-size: 12px; text-transform: uppercase;">Applicant</th>
                                                <th style="font-weight: 600; color: #6c757d; font-size: 12px; text-transform: uppercase;">Type</th>
                                                <th style="font-weight: 600; color: #6c757d; font-size: 12px; text-transform: uppercase;">Submitted</th>
                                                <th style="font-weight: 600; color: #6c757d; font-size: 12px; text-transform: uppercase;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pendingApplications as $app): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($app['study_title'] ?? 'Untitled'); ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($app['protocol_number'] ?? 'N/A'); ?></small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($app['applicant_name'] ?? 'Unknown'); ?></td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo getApplicationTypeName($app['application_type'] ?? 'student'); ?></span>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                            $date = $app['submitted_at'] ?? $app['created_at'];
                                                            echo $date ? date('M d, Y', strtotime($date)) : 'N/A';
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <a href="/reviewer-dashboard/review?id=<?php echo $app['id']; ?>" class="btn btn-sm btn-primary" style="border-radius: 20px;">
                                                            <i class="fas fa-eye me-1"></i>Review
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                                        <i class="fas fa-check" style="font-size: 36px; color: #27ae60;"></i>
                                    </div>
                                    <h5 style="color: #2c3e50; margin-bottom: 8px;">All Caught Up!</h5>
                                    <p class="text-muted mb-0">No pending applications to review at the moment.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Content -->
                <div class="col-lg-4">
                    <!-- Upcoming Deadlines -->
                    <div class="card border-0 shadow-sm mb-4 reviewer-fade-in-up" style="border-radius: 16px;">
                        <div class="card-header bg-white" style="border-radius: 16px 16px 0 0; padding: 16px 20px; border-bottom: 1px solid #e9ecef;">
                            <h5 class="mb-0" style="font-weight: 700; color: #2c3e50;">
                                <i class="fas fa-calendar-exclamation me-2" style="color: #e74c3c;"></i>
                                Upcoming Deadlines
                            </h5>
                        </div>
                        <div class="card-body" style="padding: 16px;">
                            <?php if (!empty($deadlines)): ?>
                                <?php foreach ($deadlines as $deadline): ?>
                                    <?php 
                                        $deadlineDate = new DateTime($deadline['review_deadline']);
                                        $now = new DateTime();
                                        $diff = $deadlineDate->diff($now);
                                        $daysLeft = $deadlineDate->diff($now)->days;
                                        $isUrgent = $daysLeft <= 3;
                                    ?>
                                    <div class="review-item">
                                        <div class="review-item-header">
                                            <div>
                                                <div class="review-item-title"><?php echo htmlspecialchars($deadline['study_title'] ?? 'Untitled'); ?></div>
                                                <div class="review-item-meta">Due: <?php echo date('M d, Y', strtotime($deadline['review_deadline'])); ?></div>
                                            </div>
                                            <span class="deadline-badge <?php echo $isUrgent ? 'urgent' : 'upcoming'; ?>">
                                                <?php echo $daysLeft; ?> days
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <p class="text-muted mb-0">No upcoming deadlines</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Upcoming Meetings -->
                    <div class="card border-0 shadow-sm mb-4 reviewer-fade-in-up" style="border-radius: 16px;">
                        <div class="card-header bg-white" style="border-radius: 16px 16px 0 0; padding: 16px 20px; border-bottom: 1px solid #e9ecef;">
                            <h5 class="mb-0" style="font-weight: 700; color: #2c3e50;">
                                <i class="fas fa-calendar-alt me-2" style="color: #3498db;"></i>
                                Upcoming Meetings
                            </h5>
                        </div>
                        <div class="card-body" style="padding: 16px;">
                            <?php if (!empty($meetings)): ?>
                                <?php foreach (array_slice($meetings, 0, 5) as $meeting): ?>
                                    <div class="meeting-card" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 12px; padding: 16px; margin-bottom: 12px;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div style="font-weight: 600; color: #2c3e50;">
                                                    <?php echo date('l', strtotime($meeting['meeting_date'])); ?>
                                                </div>
                                                <small class="text-muted">First Friday of Month</small>
                                            </div>
                                            <div style="text-align: right;">
                                                <div style="font-weight: 700; color: #2980b9; font-size: 18px;">
                                                    <?php echo date('M d', strtotime($meeting['meeting_date'])); ?>
                                                </div>
                                                <small class="text-muted"><?php echo date('Y', strtotime($meeting['meeting_date'])); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <p class="text-muted mb-0">No upcoming meetings scheduled</p>
                                </div>
                            <?php endif; ?>
                            <?php if (count($meetings) > 5): ?>
                                <a href="?page=meetings" class="btn btn-outline-primary w-100" style="border-radius: 25px;">
                                    View All Meetings
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card border-0 shadow-sm reviewer-fade-in-up" style="border-radius: 16px;">
                        <div class="card-header bg-white" style="border-radius: 16px 16px 0 0; padding: 16px 20px; border-bottom: 1px solid #e9ecef;">
                            <h5 class="mb-0" style="font-weight: 700; color: #2c3e50;">
                                <i class="fas fa-bolt me-2" style="color: #f39c12;"></i>
                                Quick Actions
                            </h5>
                        </div>
                        <div class="card-body" style="padding: 16px;">
                            <a href="/reviewer-dashboard/reviews" class="reviewer-quick-link">
                                <div class="quick-link-icon" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                                    <i class="fas fa-clipboard-check"></i>
                                </div>
                                <div class="quick-link-text">
                                    <div class="quick-link-title">Start Review</div>
                                    <div class="quick-link-desc">Begin reviewing pending applications</div>
                                </div>
                                <i class="fas fa-chevron-right" style="color: #c4c9d4;"></i>
                            </a>
                            <a href="/reviewer-dashboard/workload" class="reviewer-quick-link">
                                <div class="quick-link-icon" style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
                                <div class="quick-link-text">
                                    <div class="quick-link-title">View Workload</div>
                                    <div class="quick-link-desc">Check your review statistics</div>
                                </div>
                                <i class="fas fa-chevron-right" style="color: #c4c9d4;"></i>
                            </a>
                            <a href="/reviewer-dashboard/meetings" class="reviewer-quick-link">
                                <div class="quick-link-icon" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);">
                                    <i class="fas fa-calendar"></i>
                                </div>
                                <div class="quick-link-text">
                                    <div class="quick-link-title">Meeting Schedule</div>
                                    <div class="quick-link-desc">View upcoming IRB meetings</div>
                                </div>
                                <i class="fas fa-chevron-right" style="color: #c4c9d4;"></i>
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
/* Include reviewer dashboard CSS */
</style>
