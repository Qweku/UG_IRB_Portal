<?php
// Include necessary files
require_once __DIR__ . '/../includes/functions/helpers.php';
require_once __DIR__ . '/../includes/functions/csrf.php';

// Start session before generating CSRF token
if (session_status() === PHP_SESSION_NONE) {
    session_name('ug_irb_session');
    session_start();
}

if (is_admin_logged_in()) {
    header('Location: /dashboard');
    exit;
} elseif (is_applicant_logged_in()) {
    header('Location: /applicant-dashboard');
    exit;
}

// Generate CSRF token for the form
$csrf_token = csrf_token();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - UG HARES</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="/admin/assets/css/auth.css" rel="stylesheet">
</head>

<body class="auth-body">
    <div class="auth-card auth-card-lg">
        <div class="auth-header">
            <div class="auth-header-logo">
                <img src="/admin/assets/images/ug_logo_white.png" alt="UG Logo" class="d-inline-block align-text-top">
            </div>
            <h4> UG HARES</h4>
            <p>Applicant Registration</p>
        </div>
        <div class="auth-body-content">
            <form id="registerForm" action="/user/handlers/register.php" method="post" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <h5 class="register-section-title">
                    <i class="fas fa-user me-2"></i>Personal Information
                </h5>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First name" required>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="middle_name" class="form-label">Middle Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="middle_name" name="middle_name" placeholder="Middle name">
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last name" required>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="phone_number" class="form-label">Phone Number <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input type="tel" class="form-control" id="phone_number" name="phone_number" placeholder="+233 XX XXX XXXX" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" placeholder="your.email@example.com" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="application_type" class="form-label">Application Type <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-file-alt"></i></span>
                        <select class="form-select" id="application_type" name="application_type" required>
                            <option class="text-muted" selected disabled>Select Application Type</option>
                            <option value="student">Student</option>
                            <option value="nmimr">NMIMR Researchers</option>
                            <option value="non_nmimr">Non-NMIMR Researchers</option>
                        </select>
                    </div>
                </div>
                
                <!-- If Student is selected then show for student id input -->
                 
                <div class="mb-3" id="student_id_field" style="display: none;">
                    <label for="student_id" class="form-label">Student ID <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                        <input type="text" class="form-control" id="student_id" name="student_id" placeholder="Enter your student ID">
                    </div>
                </div>


                <h5 class="register-section-title">
                    <i class="fas fa-lock me-2"></i>Security Information
                </h5>

                <div class="mb-3">
                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Create a strong password" required minlength="8" autocomplete="new-password">
                        <button class="btn toggle-password" type="button" tabindex="-1">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength" id="passwordStrength"></div>
                    <ul class="password-requirements mt-2">
                        <li id="req-length"><i class="fas fa-circle"></i> At least 8 characters</li>
                        <li id="req-uppercase"><i class="fas fa-circle"></i> One uppercase letter (A-Z)</li>
                        <li id="req-lowercase"><i class="fas fa-circle"></i> One lowercase letter (a-z)</li>
                        <li id="req-number"><i class="fas fa-circle"></i> One number (0-9)</li>
                        <li id="req-special"><i class="fas fa-circle"></i> One special character (!@#$%^&*)</li>
                    </ul>
                </div>

                <div class="mb-4">
                    <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required autocomplete="new-password">
                        <button class="btn toggle-password" type="button" tabindex="-1">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback" id="confirmFeedback">
                        Passwords do not match
                    </div>
                </div>

                <input type="hidden" name="role" value="applicant">

                <div class="d-grid mb-4">
                    <button type="submit" class="btn auth-btn auth-btn-primary" id="submitBtn">
                        <i class="fas fa-user-plus me-2"></i>Create Account
                    </button>
                </div>
            </form>

            <div id="messageContainer"></div>

            <div class="auth-divider">
                <span>Already have an account?</span>
            </div>

            <div class="text-center">
                <a href="/login" class="auth-link">
                    <i class="fas fa-sign-in-alt me-1"></i>Sign In Here
                </a>
            </div>
        </div>
    </div>

    <?php include 'admin/includes/loading_overlay.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Current selected loader
        let currentLoader = 'spinner';

        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const confirmInput = document.getElementById('confirm_password');
            const confirmFeedback = document.getElementById('confirmFeedback');
            const form = document.getElementById('registerForm');
            const messageContainer = document.getElementById('messageContainer');

            // Password requirements
            const requirements = {
                length: document.getElementById('req-length'),
                uppercase: document.getElementById('req-uppercase'),
                lowercase: document.getElementById('req-lowercase'),
                number: document.getElementById('req-number'),
                special: document.getElementById('req-special')
            };

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

            // Password strength checker
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                const strengthBar = document.getElementById('passwordStrength');

                // Check requirements
                const hasLength = password.length >= 8;
                const hasUppercase = /[A-Z]/.test(password);
                const hasLowercase = /[a-z]/.test(password);
                const hasNumber = /[0-9]/.test(password);
                const hasSpecial = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);

                // Update requirement indicators
                updateRequirement(requirements.length, hasLength);
                updateRequirement(requirements.uppercase, hasUppercase);
                updateRequirement(requirements.lowercase, hasLowercase);
                updateRequirement(requirements.number, hasNumber);
                updateRequirement(requirements.special, hasSpecial);

                // Calculate strength
                let strength = 0;
                if (hasLength) strength++;
                if (hasUppercase) strength++;
                if (hasLowercase) strength++;
                if (hasNumber) strength++;
                if (hasSpecial) strength++;

                // Update strength bar
                strengthBar.className = 'password-strength';
                if (strength <= 1) {
                    strengthBar.classList.add('weak');
                } else if (strength <= 2) {
                    strengthBar.classList.add('medium');
                } else if (strength <= 3) {
                    strengthBar.classList.add('strong');
                } else if (strength <= 4) {
                    strengthBar.classList.add('very-strong');
                } else {
                    strengthBar.classList.add('very-strong');
                }

                // Recheck confirm password
                checkConfirmPassword();
            });

            function updateRequirement(element, isValid) {
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

            // Confirm password validation
            confirmInput.addEventListener('input', checkConfirmPassword);

            function checkConfirmPassword() {
                if (confirmInput.value === '') {
                    confirmInput.classList.remove('is-invalid');
                    return;
                }

                if (passwordInput.value !== confirmInput.value) {
                    confirmInput.classList.add('is-invalid');
                } else {
                    confirmInput.classList.remove('is-invalid');
                }
            }

            // Form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Validate password requirements before submission
                const password = passwordInput.value;
                const hasUppercase = /[A-Z]/.test(password);
                const hasLowercase = /[a-z]/.test(password);
                const hasNumber = /[0-9]/.test(password);
                const hasSpecial = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);

                if (!hasUppercase || !hasLowercase || !hasNumber || !hasSpecial) {
                    showMessage('Password does not meet all requirements. Please check the password requirements.', 'danger');
                    return;
                }

                if (password !== confirmInput.value) {
                    showMessage('Passwords do not match.', 'danger');
                    return;
                }

                // Validate student_id if student application type is selected
                if (applicationTypeSelect.value === 'student' && !studentIdInput.value.trim()) {
                    showMessage('Student ID is required for student applications.', 'danger');
                    return;
                }

                // Submit form via AJAX
                const formData = new FormData(form);

                showLoadingOverlay();

                fetch('/user/handlers/register.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showMessage(data.message, 'success');
                            form.reset();
                            document.getElementById('passwordStrength').className = 'password-strength';
                            Object.values(requirements).forEach(req => {
                                req.classList.remove('valid');
                                req.classList.add('invalid');
                                req.querySelector('i').classList.remove('fa-check-circle');
                                req.querySelector('i').classList.add('fa-circle');
                            });

                            // Redirect to login after success
                            setTimeout(() => {
                                window.location.href = '/login?registered=1';
                            }, 2000);
                        } else {
                            showMessage(data.message || 'Registration failed. Please try again.', 'danger');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showMessage('An error occurred. Please try again.', 'danger');
                    });
            });

            function showMessage(message, type) {
                messageContainer.innerHTML = `
                    <div class="alert auth-alert auth-alert-${type} mt-3" role="alert">
                        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                        ${message}
                    </div>
                `;
            }

            // Function to show loading overlay
            function showLoadingOverlay() {
                // Scroll to top smoothly
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });

                // Hide all loader contents
                document.querySelectorAll('.loader-content').forEach(content => {
                    content.style.display = 'none';
                });

                // Show selected loader
                const loaderElement = document.getElementById(`${currentLoader}Loader`);
                if (loaderElement) {
                    loaderElement.style.display = 'block';
                }

                // Update loading text with user's name
                const loadingText = document.querySelector('.loading-text');
                if (loadingText) {
                    loadingText.textContent = `Processing...`;
                }

                // Show overlay with animation
                const overlay = document.getElementById('loadingOverlay');
                overlay.classList.add('active');

                // Disable body scroll
                document.body.style.overflow = 'hidden';

                // Simulate processing (3 seconds)
                setTimeout(() => {
                    hideLoadingOverlay();

                    // Show success message
                    setTimeout(() => {
                        showToast('success', data.message);
                        // Reset form
                        // document.getElementById('studyForm').reset();
                    }, 300);
                }, 3000);
            }

            // Function to hide loading overlay
            function hideLoadingOverlay() {
                const overlay = document.getElementById('loadingOverlay');
                overlay.classList.remove('active');

                // Re-enable body scroll
                document.body.style.overflow = 'auto';

                // Fade out animation
                setTimeout(() => {
                    overlay.style.display = 'none';
                }, 300);
            }

            // Function to show loading programmatically (for other uses)
            window.showLoading = function(loaderType = 'spinner', duration = 3000, message = 'Processing...') {
                if (loaderType) currentLoader = loaderType;

                // Update message if provided
                const loadingText = document.querySelector('.loading-text');
                if (loadingText && message) {
                    loadingText.textContent = message;
                }

                showLoadingOverlay({
                    firstName: 'User'
                });

                // Auto-hide after duration if provided
                if (duration) {
                    setTimeout(hideLoadingOverlay, duration);
                }
            };

            // Function to hide loading programmatically
            window.hideLoading = hideLoadingOverlay;

            // Application Type - Show/Hide Student ID Field
            const applicationTypeSelect = document.getElementById('application_type');
            const studentIdField = document.getElementById('student_id_field');
            const studentIdInput = document.getElementById('student_id');

            applicationTypeSelect.addEventListener('change', function() {
                if (this.value === 'student') {
                    studentIdField.style.display = 'block';
                    studentIdInput.setAttribute('required', 'required');
                } else {
                    studentIdField.style.display = 'none';
                    studentIdInput.removeAttribute('required');
                    studentIdInput.value = ''; // Clear the value when hidden
                }
            });
        });
    </script>
</body>

</html>