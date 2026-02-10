<?php


// session_start();
// if (!is_admin_logged_in()) {
//     header('Location: /login');
//     exit;
// }

// Include CSRF protection
// require_once '../../includes/functions/csrf.php';
$userId = null;
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    error_log("User ID is: " . $_SESSION['user_id']);
}



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
        header('Location: /dashboard?error=password_mismatch');
        exit;
    }

    if (strlen($newPassword) < 8) {
        header('Location: /dashboard?error=password_short');
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
        header('Location: /dashboard');
    } else {
        header('Location: /dashboard?error=update_failed');
    }
    exit;
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3">
            <?php include 'admin/pages/contents/dashboard_content.php'; ?>
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