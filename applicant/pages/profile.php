<?php

// Check if applicant is logged in
if (!is_applicant_logged_in()) {
    header('Location: /login');
    exit;
}

$userId = $_SESSION['user_id'] ?? 0;
$userName = $_SESSION['full_name'] ?? 'Applicant';
$userEmail = $_SESSION['user_email'] ?? '';
$userRole = $_SESSION['role'] ?? 'applicant';

// Get applicant profile from database
$profile = getApplicantProfile($userId);

// Fallback to session data if not in database
$fullName = $profile['first_name'] . ' ' . $profile['middle_name'] . ' ' . $profile['last_name'] ?? $userName;
$email = $profile['email'] ?? $userEmail;
$phone = $profile['phone_number'] ?? ($_SESSION['phone_number'] ?? 'Not provided');
$applicant_type = $profile['applicant_type'] ?? ($_SESSION['applicant_type'] ?? 'student');
$institution_id = $profile['institution_id'] ?? ($_SESSION['institution_id'] ?? null);
$institutionName = 'Not provided';

try{
    $institution = getInstitutionById($institution_id);
    $institutionName = $institution['institution_name'] ?? 'Not provided';
} catch (Exception $e) {
    $institutionName = 'Not provided';
}
$institution = $profile['institution'] ?? ($_SESSION['institution'] ?? 'Not provided');

// Get stats
$stats = getApplicantStats($userId);

?>



<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3">

            <!-- Page Header -->
            <div class="profile-header-section text-white fade-in">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2><i class="fas fa-user me-3"></i>My Profile</h2>
                        <p class="mb-0">View and manage your account information</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <!-- <button class="btn btn-light" onclick="window.print()" style="border-radius: 25px; font-weight: 600;">
                            <i class="fas fa-print me-2"></i>Print Profile
                        </button> -->
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Profile Side Card -->
                <div class="col-md-4 mb-4">
                    <div class="profile-side-card bg-white fade-in p-4">
                        <div class="my-5">
                            <div class="card-header text-center">
                                <div class="profile-avatar">
                                    <span><?php echo strtoupper(substr($fullName, 0, 1)); ?></span>
                                </div>
                                <h4 class="profile-name"><?php echo htmlspecialchars($fullName); ?></h4>
                                <p class="profile-role"><?php echo htmlspecialchars(ucfirst($userRole)); ?></p>
                            </div>
                            <div class="card-body">
                                <!-- Mini Stats -->
                                <div class="row mb-3">
                                    <div class="col-4">
                                        <div class="mini-stat">
                                            <div class="mini-stat-number"><?php echo $stats['total']; ?></div>
                                            <div class="mini-stat-label">Apps</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="mini-stat">
                                            <div class="mini-stat-number text-success"><?php echo $stats['approved']; ?></div>
                                            <div class="mini-stat-label">Approved</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="mini-stat">
                                            <div class="mini-stat-number text-warning"><?php echo $stats['under_review']; ?></div>
                                            <div class="mini-stat-label">Review</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="/applicant-dashboard" class="btn btn-primary w-100" style="border-radius: 25px; padding: 12px; font-weight: 600;">
                                    <i class="fas fa-plus me-2"></i>New Application
                                </a>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Profile Details -->
                <div class="col-md-8">
                    <!-- Personal Information -->
                    <div class="info-card fade-in">
                        <div class="card-header">
                            <h5><i class="fas fa-id-card me-2"></i>Personal Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="info-item">
                                <div class="info-item-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="info-item-content">
                                    <label>Full Name</label>
                                    <span><?php echo htmlspecialchars($fullName); ?></span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-item-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="info-item-content">
                                    <label>Email Address</label>
                                    <span><?php echo htmlspecialchars($email); ?></span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-item-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="info-item-content">
                                    <label>Phone Number</label>
                                    <span><?php echo htmlspecialchars($phone); ?></span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-item-icon">
                                    <i class="fas fa-university"></i>
                                </div>
                                <div class="info-item-content">
                                    <label>Institution</label>
                                    <span><?php echo htmlspecialchars($institutionName); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div class="info-card fade-in">
                        <div class="card-header">
                            <h5><i class="fas fa-shield-alt me-2"></i>Account Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-item-icon">
                                            <i class="fas fa-id-badge"></i>
                                        </div>
                                        <div class="info-item-content">
                                            <label>Applicant Type</label>
                                            <span><?php echo htmlspecialchars($applicant_type); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-item-icon">
                                            <i class="fas fa-user-tag"></i>
                                        </div>
                                        <div class="info-item-content">
                                            <label>Role</label>
                                            <span><?php echo htmlspecialchars(ucfirst($userRole)); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="contact-card fade-in">
                        <div class="card-header">
                            <h5><i class="fas fa-headset me-2"></i>Contact IRB Office</h5>
                        </div>
                        <div class="card-body">
                            <a href="mailto:nirb@noguchi.ug.edu.gh" class="contact-link">
                                <i class="fas fa-envelope"></i>
                                <div>
                                    <h6>Email Support</h6>
                                    <p class="mb-0">nirb@noguchi.ug.edu.gh</p>
                                </div>
                            </a>
                            <div class="contact-link">
                                <i class="fas fa-phone"></i>
                                <div>
                                    <h6>Phone</h6>
                                    <p class="mb-0">+233 302 501 382 / +233 302 501 383</p>
                                </div>
                            </div>
                            <div class="contact-link">
                                <i class="fas fa-map-marker-alt"></i>
                                <div>
                                    <h6>Location</h6>
                                    <p class="mb-0">NMIMR, University of Ghana, Legon</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>