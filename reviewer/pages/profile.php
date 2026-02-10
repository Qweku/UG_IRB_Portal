<?php

// NOTE: Authentication is already handled by index.php router
// No duplicate auth check needed here

$userId = $_SESSION['user_id'] ?? 0;
$userName = $_SESSION['full_name'] ?? 'Reviewer';
$userEmail = $_SESSION['email'] ?? '';
$userRole = $_SESSION['role'] ?? 'reviewer';

// Get reviewer profile
$profile = getReviewerProfile($userId);

// Get reviewer stats
$stats = getReviewerStats($userId);

?>
<style>
/* Profile Page Specific Styles */
.profile-header-section {
    background: linear-gradient(135deg, #1a5276 0%, #2980b9 100%);
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 24px;
    box-shadow: 0 10px 40px rgba(41, 128, 185, 0.2);
}

.profile-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    font-size: 48px;
    font-weight: 700;
    color: #2980b9;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.profile-name {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 4px;
}

.profile-role {
    opacity: 0.9;
    font-size: 14px;
    text-transform: capitalize;
}

.profile-card {
    border: none;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    margin-bottom: 24px;
}

.profile-card .card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: none;
    padding: 20px;
}

.profile-card .card-body {
    padding: 24px;
}

/* Info Item */
.info-item {
    display: flex;
    align-items: center;
    padding: 16px 0;
    border-bottom: 1px solid #e9ecef;
}

.info-item:last-child {
    border-bottom: none;
}

.info-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 16px;
    color: #2980b9;
    font-size: 18px;
}

.info-content {
    flex: 1;
}

.info-label {
    font-size: 12px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}

.info-value {
    font-weight: 600;
    color: #2c3e50;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.stat-box {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
}

.stat-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
}

.stat-box .stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    font-size: 20px;
}

.stat-box .stat-number {
    font-size: 24px;
    font-weight: 700;
    color: #2c3e50;
}

.stat-box .stat-label {
    font-size: 12px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 4px;
}

/* Form Styles */
.form-label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
}

.form-control {
    border-radius: 10px;
    border: 2px solid #e9ecef;
    padding: 12px 16px;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #2980b9;
    box-shadow: 0 0 0 3px rgba(41, 128, 185, 0.1);
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

            <!-- Profile Header -->
            <div class="profile-header-section text-white fade-in">
                <div class="text-center">
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($userName, 0, 2)); ?>
                    </div>
                    <h2 class="profile-name"><?php echo htmlspecialchars($userName); ?></h2>
                    <p class="profile-role">
                        <i class="fas fa-id-badge me-2"></i>
                        <?php echo htmlspecialchars($userRole); ?>
                    </p>
                </div>
            </div>

            <div class="row">
                <!-- Profile Info -->
                <div class="col-lg-4 mb-4">
                    <div class="profile-card fade-in">
                        <div class="card-header">
                            <h5 class="mb-0" style="font-weight: 700; color: #2c3e50;">
                                <i class="fas fa-user me-2" style="color: #2980b9;"></i>
                                Profile Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Email</div>
                                    <div class="info-value"><?php echo htmlspecialchars($userEmail); ?></div>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-id-card"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Role</div>
                                    <div class="info-value text-capitalize"><?php echo htmlspecialchars($userRole); ?></div>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Member Since</div>
                                    <div class="info-value"><?php echo date('M Y', strtotime($_SESSION['created_at'] ?? 'now')); ?></div>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Account Status</div>
                                    <div class="info-value">
                                        <span class="badge bg-success" style="border-radius: 20px;">
                                            <i class="fas fa-check-circle me-1"></i>Active
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="profile-card fade-in">
                        <div class="card-header">
                            <h5 class="mb-0" style="font-weight: 700; color: #2c3e50;">
                                <i class="fas fa-chart-bar me-2" style="color: #2980b9;"></i>
                                Review Statistics
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="stats-grid">
                                <div class="stat-box">
                                    <div class="stat-icon" style="background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%); color: #856404;">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="stat-number"><?php echo $stats['pending']; ?></div>
                                    <div class="stat-label">Pending</div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-icon" style="background: linear-gradient(135deg, #cce5ff 0%, #b8daff 100%); color: #004085;">
                                        <i class="fas fa-spinner"></i>
                                    </div>
                                    <div class="stat-number"><?php echo $stats['in_progress']; ?></div>
                                    <div class="stat-label">In Progress</div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-icon" style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); color: #155724;">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="stat-number"><?php echo $stats['completed']; ?></div>
                                    <div class="stat-label">Completed</div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-icon" style="background: linear-gradient(135deg, #e2d5f1 0%, #d4b8e8 100%); color: #6c3483;">
                                        <i class="fas fa-tasks"></i>
                                    </div>
                                    <div class="stat-number"><?php echo $stats['total_assigned']; ?></div>
                                    <div class="stat-label">Total</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Settings -->
                <div class="col-lg-8 mb-4">
                    <div class="profile-card fade-in">
                        <div class="card-header">
                            <h5 class="mb-0" style="font-weight: 700; color: #2c3e50;">
                                <i class="fas fa-cog me-2" style="color: #2980b9;"></i>
                                Account Settings
                            </h5>
                        </div>
                        <div class="card-body">
                            <form>
                                <?php echo csrf_field(); ?>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($userName); ?>" readonly>
                                        <small class="text-muted">Contact admin to change your name</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($userEmail); ?>" readonly>
                                        <small class="text-muted">Contact admin to change your email</small>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Department</label>
                                        <input type="text" class="form-control" placeholder="Enter your department" 
                                               value="<?php echo htmlspecialchars($profile['department'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Specialization</label>
                                        <input type="text" class="form-control" placeholder="Enter your specialization"
                                               value="<?php echo htmlspecialchars($profile['specialization'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Review Expertise</label>
                                    <textarea class="form-control" rows="3" placeholder="List your areas of expertise for reviewing applications"><?php echo htmlspecialchars($profile['expertise'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Bio</label>
                                    <textarea class="form-control" rows="4" placeholder="Brief bio about yourself"><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary" style="border-radius: 25px; font-weight: 600;">
                                        <i class="fas fa-save me-2"></i>Save Changes
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" style="border-radius: 25px;">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Notification Settings -->
                    <div class="profile-card fade-in">
                        <div class="card-header">
                            <h5 class="mb-0" style="font-weight: 700; color: #2c3e50;">
                                <i class="fas fa-bell me-2" style="color: #2980b9;"></i>
                                Notification Preferences
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                                <label class="form-check-label" for="emailNotifications">
                                    Email notifications for new assignments
                                </label>
                            </div>
                            
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="deadlineReminders" checked>
                                <label class="form-check-label" for="deadlineReminders">
                                    Deadline reminders
                                </label>
                            </div>
                            
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="meetingReminders" checked>
                                <label class="form-check-label" for="meetingReminders">
                                    Meeting reminders
                                </label>
                            </div>
                            
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="applicationUpdates">
                                <label class="form-check-label" for="applicationUpdates">
                                    Updates on reviewed applications
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Security -->
                    <div class="profile-card fade-in">
                        <div class="card-header">
                            <h5 class="mb-0" style="font-weight: 700; color: #2c3e50;">
                                <i class="fas fa-lock me-2" style="color: #2980b9;"></i>
                                Security
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="mb-1">Password</h6>
                                    <p class="mb-0 text-muted" style="font-size: 13px;">Last changed: Never</p>
                                </div>
                                <button class="btn btn-outline-primary" style="border-radius: 25px;">
                                    <i class="fas fa-key me-2"></i>Change Password
                                </button>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Two-Factor Authentication</h6>
                                    <p class="mb-0 text-muted" style="font-size: 13px;">Add an extra layer of security</p>
                                </div>
                                <button class="btn btn-outline-success" style="border-radius: 25px;">
                                    <i class="fas fa-shield-alt me-2"></i>Enable 2FA
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
