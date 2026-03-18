<?php
// Include necessary files
require_once __DIR__ . '/../includes/functions/helpers.php';
require_once __DIR__ . '/../includes/functions/csrf.php';

// if (is_admin_logged_in()) {
//     header('Location: /dashboard');
//     exit;
// } elseif (is_applicant_logged_in()) {
//     header('Location: /applicant-dashboard');
//     exit;
// }

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - UG HARES Software</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="/admin/assets/css/auth.css" rel="stylesheet">
</head>

<body class="auth-body">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-header-logo">
                <img src="/admin/assets/images/ug_logo_white.png" alt="UG Logo" class="d-inline-block align-text-top">
            </div>
            <h4></i> UG HARES</h4>
            <p>Reset your password</p>
        </div>
        <div class="auth-body-content">
            <p class="text-center text-muted mb-4">
                Please enter your email address and we'll send you a link to reset your password.
            </p>
            
            <form id="forgotPasswordForm" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                <div class="mb-4">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" placeholder="your.email@example.com" required>
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn auth-btn auth-btn-primary text-white" id="submitBtn">
                        <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                    </button>
                </div>
            </form>
            
            <div id="messageContainer"></div>
            
            <div class="auth-divider">
                <span>Remember your password?</span>
            </div>
            
            <div class="text-center">
                <a href="/login" class="auth-link">
                    <i class="fas fa-sign-in-alt me-1"></i>Sign In Instead
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = document.getElementById('submitBtn');
            const messageContainer = document.getElementById('messageContainer');
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
            messageContainer.innerHTML = '';
            
            fetch('/forgot-password-action', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    messageContainer.innerHTML = '<div class="alert auth-alert auth-alert-success" role="alert">' +
                        '<i class="fas fa-check-circle me-2"></i>' + data.message + '</div>';
                    document.getElementById('forgotPasswordForm').reset();
                } else {
                    messageContainer.innerHTML = '<div class="alert auth-alert auth-alert-danger" role="alert">' +
                        '<i class="fas fa-exclamation-circle me-2"></i>' + data.message + '</div>';
                }
            })
            .catch(error => {
                messageContainer.innerHTML = '<div class="alert auth-alert auth-alert-danger" role="alert">' +
                    '<i class="fas fa-exclamation-circle me-2"></i>An error occurred. Please try again.</div>';
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Send Reset Link';
            });
        });
    </script>
</body>

</html>
