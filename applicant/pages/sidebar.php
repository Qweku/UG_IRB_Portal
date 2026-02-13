<?php

$current_page = basename($_SERVER['PHP_SELF']);
$userRole = $_SESSION['role'] ?? 'applicant';
$userName = $_SESSION['full_name'] ?? 'Applicant';
$userId = $_SESSION['user_id'] ?? 0;

// Get stats for sidebar
// $stats = getApplicantStats($userId);

?>
<div class="sidebar-backdrop" onclick="closeSidebar()"></div>

<div id="sidebar" class="col-lg-2 col-sm-0 d-md-block sidebar collapse">
    <button class="sidebar-close-btn" onclick="closeSidebar()">×</button>
    <div class="sidebar-sticky">
        
               <!-- User Card -->
        <div class="user-card">
            <div class="user-avatar-wrapper">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($userName, 0, 1)); ?>
                </div>
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($userName); ?></div>
                    <div class="user-role"><?php echo htmlspecialchars($userRole); ?></div>
                </div>
            </div>
        </div>

       

        <!-- Navigation -->
        <div class="nav-section">
            <div class="nav-section-title">Main Menu</div>
            
            <a class="nav-link <?php echo ($current_page == 'applicant-dashboard') ? 'active' : ''; ?>" href="/applicant-dashboard">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            
            <a class="nav-link <?php echo ($current_page == 'applications') ? 'active' : ''; ?>" href="/applicant-dashboard/applications">
                <i class="fas fa-clipboard-list"></i>
                <span>My Applications</span>
            </a>
            
            <a class="nav-link <?php echo ($current_page == 'profile') ? 'active' : ''; ?>" href="/applicant-dashboard/profile">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
        </div>

        <!-- Bottom Section -->
        <div class="sidebar-bottom">
            <div class="help-card">
                <div class="help-icon">
                    <i class="fas fa-question"></i>
                </div>
                <div class="help-title">Need Help?</div>
                <div class="help-text">Contact IRB support for assistance</div>
                <a href="mailto:nirb@noguchi.ug.edu.gh" class="help-btn">
                    <i class="fas fa-envelope"></i>
                    Email Support
                </a>
            </div>
            
           
        </div>
    </div>
</div>
