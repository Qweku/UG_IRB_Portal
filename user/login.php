<?php
// Include necessary files
require_once __DIR__ . '/../includes/functions/helpers.php';
require_once __DIR__ . '/../includes/functions/csrf.php';

if (is_admin_logged_in()) {
    header('Location: /dashboard');
    exit;
} elseif (is_applicant_logged_in()) {
    header('Location: /applicant-dashboard');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UG HARES Software</title>
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
            <h4>UG HARES</h4>
            <p>Please sign in to continue</p>
        </div>
        <div class="auth-body-content">
            <form action="/authenticate" method="post" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" placeholder="your.email@example.com" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                        <button class="btn toggle-password" type="button" tabindex="-1">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn auth-btn auth-btn-primary">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </button>
                </div>
            </form>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert auth-alert auth-alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Invalid email or password. Please try again.
                </div>
            <?php endif; ?>
            
            <div class="text-center mt-4">
                <a href="/forgot-password" class="auth-link">
                    <i class="fas fa-key me-1"></i>Forgot password?
                </a>
            </div>
            
            <div class="auth-divider">
                <span>New Applicant?</span>
            </div>
            
            <div class="text-center">
                <a href="/register" class="btn auth-btn auth-btn-outline">
                    <i class="fas fa-user-plus me-2"></i>Register Here
                </a>
            </div>
        </div>
    </div>

    <?php include 'admin/includes/loading_overlay.php' ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Current selected loader
        let currentLoader = 'spinner';
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
    </script>
</body>

</html>
