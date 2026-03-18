<?php
// Include necessary files
require_once __DIR__ . '/../includes/functions/helpers.php';
require_once __DIR__ . '/../includes/functions/csrf.php';
require_once __DIR__ . '/../includes/config/database.php';

// Get token from URL
$token = $_GET['token'] ?? '';

// Validate token presence
if (empty($token)) {
    // Redirect to forgot password page if no token
    echo '<script>window.location.href = "/forgot-password"</script>';
    // header('Location: /forgot-password');
    exit;
}

// Validate token exists and is not expired
$token_valid = false;
$error_message = '';

try {
    $db = new Database();
    $conn = $db->connect();
    
    if ($conn) {
        $stmt = $conn->prepare("
            SELECT prt.id, prt.user_id, u.full_name, u.email, prt.expires_at, prt.used_at
            FROM password_reset_tokens prt
            JOIN users u ON prt.user_id = u.id
            WHERE prt.token = ? 
            AND prt.used_at IS NULL
            AND prt.expires_at > NOW()
        ");
        $stmt->execute([$token]);
        $reset_request = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($reset_request) {
            $token_valid = true;
            $user_name = $reset_request['full_name'];
            $user_email = $reset_request['email'];
        } else {
            $error_message = 'This password reset link is invalid or has expired. Please request a new one.';
        }
    } else {
        $error_message = 'Database connection failed. Please try again later.';
    }
} catch (Exception $e) {
    error_log("Token validation error: " . $e->getMessage());
    $error_message = 'An error occurred while validating the token. Please try again.';
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - UG IRB Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="/admin/assets/css/auth.css" rel="stylesheet">
    <style>
        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 2px;
            transition: all 0.3s;
        }
        .password-strength.weak { background-color: #dc3545; width: 33%; }
        .password-strength.medium { background-color: #ffc107; width: 66%; }
        .password-strength.strong { background-color: #28a745; width: 100%; }
        .password-requirements {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 10px;
        }
        .password-requirements li {
            margin-bottom: 3px;
        }
        .password-requirements li.valid {
            color: #28a745;
        }
        .password-requirements li.invalid {
            color: #dc3545;
        }
    </style>
</head>

<body class="auth-body">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-header-logo">
                <img src="/admin/assets/images/ug_logo_white.png" alt="UG Logo" class="d-inline-block align-text-top">
            </div>
            <h4></i> UG HARES</h4>
            <p>Reset Your Password</p>
        </div>
        <div class="auth-body-content">
            
            <?php if (!$token_valid): ?>
                <div class="alert auth-alert auth-alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
                <div class="text-center">
                    <a href="/forgot-password" class="auth-link">
                        <i class="fas fa-paper-plane me-1"></i>Request New Reset Link
                    </a>
                </div>
            <?php else: ?>
                <p class="text-center text-muted mb-4">
                    Hi <strong><?php echo htmlspecialchars($user_name); ?></strong>, enter your new password below.
                </p>
                
                <form id="resetPasswordForm" class="auth-form">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye" id="passwordIcon"></i>
                            </button>
                        </div>
                        <div class="password-strength" id="passwordStrength"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                <i class="fas fa-eye" id="confirmPasswordIcon"></i>
                            </button>
                        </div>
                        <div id="passwordMatch" class="form-text"></div>
                    </div>
                    
                    <ul class="password-requirements">
                        <li id="req-length"><i class="fas fa-circle me-1"></i>At least 8 characters</li>
                        <li id="req-upper"><i class="fas fa-circle me-1"></i>At least one uppercase letter</li>
                        <li id="req-lower"><i class="fas fa-circle me-1"></i>At least one lowercase letter</li>
                        <li id="req-number"><i class="fas fa-circle me-1"></i>At least one number</li>
                        <li id="req-special"><i class="fas fa-circle me-1"></i>At least one special character</li>
                    </ul>
                    
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn auth-btn auth-btn-primary text-white" id="submitBtn">
                            <i class="fas fa-save me-2"></i>Reset Password
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
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password visibility toggles
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = document.getElementById('passwordIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('confirm_password');
            const icon = document.getElementById('confirmPasswordIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Password strength checker
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const passwordStrength = document.getElementById('passwordStrength');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Check requirements
            const hasLength = password.length >= 8;
            const hasUpper = /[A-Z]/.test(password);
            const hasLower = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
            
            // Update requirement indicators
            updateRequirement('req-length', hasLength);
            updateRequirement('req-upper', hasUpper);
            updateRequirement('req-lower', hasLower);
            updateRequirement('req-number', hasNumber);
            updateRequirement('req-special', hasSpecial);
            
            // Calculate strength
            if (hasLength) strength++;
            if (hasUpper) strength++;
            if (hasLower) strength++;
            if (hasNumber) strength++;
            if (hasSpecial) strength++;
            
            // Update strength bar
            passwordStrength.className = 'password-strength';
            if (password.length === 0) {
                // No strength bar if empty
            } else if (strength <= 2) {
                passwordStrength.classList.add('weak');
            } else if (strength <= 3) {
                passwordStrength.classList.add('medium');
            } else {
                passwordStrength.classList.add('strong');
            }
            
            // Check match
            checkPasswordMatch();
        });
        
        function updateRequirement(id, isValid) {
            const element = document.getElementById(id);
            if (isValid) {
                element.classList.add('valid');
                element.classList.remove('invalid');
                element.querySelector('i').classList.remove('fa-circle');
                element.querySelector('i').classList.add('fa-check-circle');
            } else {
                element.classList.remove('valid');
                element.classList.add('invalid');
                element.querySelector('i').classList.remove('fa-check-circle');
                element.querySelector('i').classList.add('fa-circle');
            }
        }
        
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
        
        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const matchDiv = document.getElementById('passwordMatch');
            
            if (confirmPassword.length === 0) {
                matchDiv.textContent = '';
                matchDiv.className = 'form-text';
            } else if (password === confirmPassword) {
                matchDiv.textContent = 'Passwords match';
                matchDiv.className = 'form-text text-success';
            } else {
                matchDiv.textContent = 'Passwords do not match';
                matchDiv.className = 'form-text text-danger';
            }
        }
        
        // Form submission
        document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = document.getElementById('submitBtn');
            const messageContainer = document.getElementById('messageContainer');
            
            // Validate passwords match
            const password = formData.get('password');
            const confirm_password = formData.get('confirm_password');
            
            if (password !== confirm_password) {
                messageContainer.innerHTML = '<div class="alert auth-alert auth-alert-danger" role="alert">' +
                    '<i class="fas fa-exclamation-circle me-2"></i>Passwords do not match.</div>';
                return;
            }
            
            // Validate password strength
            if (password.length < 8) {
                messageContainer.innerHTML = '<div class="alert auth-alert auth-alert-danger" role="alert">' +
                    '<i class="fas fa-exclamation-circle me-2"></i>Password must be at least 8 characters.</div>';
                return;
            }
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2\"></i>Resetting...';
            messageContainer.innerHTML = '';
            
            fetch('/reset-password-action', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    messageContainer.innerHTML = '<div class="alert auth-alert auth-alert-success" role="alert">' +
                        '<i class=\"fas fa-check-circle me-2\"></i>' + data.message + '</div>';
                    document.getElementById('resetPasswordForm').reset();
                    // Redirect to login after 2 seconds
                    setTimeout(function() {
                        window.location.href = '/login?reset=success';
                    }, 2000);
                } else {
                    messageContainer.innerHTML = '<div class="alert auth-alert auth-alert-danger" role="alert">' +
                        '<i class=\"fas fa-exclamation-circle me-2\"></i>' + data.message + '</div>';
                }
            })
            .catch(error => {
                messageContainer.innerHTML = '<div class="alert auth-alert auth-alert-danger" role="alert">' +
                    '<i class="fas fa-exclamation-circle me-2\"></i>An error occurred. Please try again.</div>';
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Reset Password';
            });
        });
    </script>
</body>

</html>
