<?php

$current_page = basename($_SERVER['PHP_SELF']);
$userRole = $_SESSION['role'] ?? 'reviewer';
$userName = $_SESSION['full_name'] ?? 'Reviewer';
$userId = $_SESSION['user_id'] ?? 0;

// Get stats for sidebar
$stats = getReviewerStats($userId);

?>
<style>
/* Reviewer Sidebar Styles */
#sidebar {
    background: #ffffff;
    border-right: 1px solid #e8ebf2;
    height: 100vh;
    position: sticky;
    top: 0;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.sidebar-sticky {
    height: 100%;
    padding: 0;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    overflow-x: hidden;
}

/* Brand Header */
.sidebar-brand {
    padding: 24px 20px;
    background: linear-gradient(180deg, #ffffff 0%, #f8f9ff 100%);
    border-bottom: 1px solid #e8ebf2;
    flex-shrink: 0;
}

.brand-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: var(--royal-blue);
}

.brand-icon {
    width: 42px;
    height: 42px;
    background: linear-gradient(135deg, #1a5276 0%, #2980b9 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    box-shadow: 0 4px 12px rgba(41, 128, 185, 0.3);
}

.brand-text {
    font-weight: 700;
    font-size: 13px;
    line-height: 1.3;
    color: #2c3e50;
}

.brand-text span {
    display: block;
    font-weight: 400;
    font-size: 11px;
    color: #8898aa;
}

/* User Card */
.user-card {
    padding: 20px;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
    border-bottom: 1px solid #e8ebf2;
    flex-shrink: 0;
}

.user-avatar-wrapper {
    display: flex;
    align-items: center;
    gap: 14px;
}

.user-avatar {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, #1a5276 0%, #2980b9 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(41, 128, 185, 0.3);
}

.user-info {
    flex: 1;
    min-width: 0;
}

.user-name {
    font-weight: 600;
    font-size: 14px;
    color: #2c3e50;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-role {
    font-size: 12px;
    color: #8898aa;
    text-transform: capitalize;
}

/* Quick Stats */
.quick-stats {
    padding: 16px 20px;
    background: #f8f9ff;
    border-bottom: 1px solid #e8ebf2;
    flex-shrink: 0;
}

.quick-stats-title {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #8898aa;
    margin-bottom: 12px;
    font-weight: 600;
}

.stats-row {
    display: flex;
    gap: 12px;
}

.stat-item {
    flex: 1;
    background: white;
    border-radius: 10px;
    padding: 10px;
    text-align: center;
    border: 1px solid #e8ebf2;
    transition: all 0.3s ease;
}

.stat-item:hover {
    border-color: #2980b9;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(41, 128, 185, 0.1);
}

.stat-number {
    font-size: 18px;
    font-weight: 700;
    color: #2980b9;
    line-height: 1;
}

.stat-label {
    font-size: 10px;
    color: #8898aa;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 4px;
}

/* Navigation */
.nav-section {
    padding: 16px 12px 8px;
    flex: 1;
    overflow-y: auto;
    min-height: 0;
}

.nav-section-title {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #c4c9d4;
    font-weight: 600;
    padding: 0 12px 8px;
    margin-bottom: 4px;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    border-radius: 10px;
    color: #525f7f;
    text-decoration: none;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.3s ease;
    margin-bottom: 4px;
    position: relative;
    overflow: hidden;
}

.nav-link::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 0;
    background: linear-gradient(180deg, #1a5276 0%, #2980b9 100%);
    border-radius: 0 4px 4px 0;
    transition: height 0.3s ease;
}

.nav-link:hover {
    background: linear-gradient(90deg, rgba(41, 128, 185, 0.06) 0%, rgba(41, 128, 185, 0.02) 100%);
    color: #2980b9;
}

.nav-link:hover::before {
    height: 24px;
}

.nav-link.active {
    background: linear-gradient(90deg, rgba(41, 128, 185, 0.1) 0%, rgba(41, 128, 185, 0.04) 100%);
    color: #2980b9;
    font-weight: 600;
}

.nav-link.active::before {
    height: 24px;
}

.nav-link i {
    width: 22px;
    text-align: center;
    font-size: 16px;
    transition: transform 0.3s ease;
}

.nav-link:hover i {
    transform: scale(1.1);
}

/* Notification Badge */
.nav-link .badge {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: #e74c3c;
    color: white;
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 10px;
}

/* Bottom Section */
.sidebar-bottom {
    padding: 16px;
    border-top: 1px solid #e8ebf2;
    background: linear-gradient(180deg, #f8f9ff 0%, #ffffff 100%);
    flex-shrink: 0;
}

.help-card {
    background: linear-gradient(135deg, #1a5276 0%, #2980b9 100%);
    border-radius: 14px;
    padding: 20px;
    color: white;
    position: relative;
    overflow: hidden;
    margin-bottom: 12px;
}

.help-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -30%;
    width: 120px;
    height: 120px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
}

.help-card::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -20%;
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 50%;
}

.help-icon {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    margin-bottom: 12px;
}

.help-title {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 4px;
}

.help-text {
    font-size: 12px;
    opacity: 0.9;
    margin-bottom: 12px;
}

.help-btn {
    width: 100%;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    background: rgba(255, 255, 255, 0.1);
    color: white;
    font-weight: 500;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.help-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.5);
}

/* Logout Button */
.logout-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    border-radius: 10px;
    color: #e74c3c;
    text-decoration: none;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.3s ease;
    border: none;
    background: none;
    width: 100%;
    cursor: pointer;
}

.logout-btn:hover {
    background: linear-gradient(90deg, rgba(231, 76, 60, 0.08) 0%, rgba(231, 76, 60, 0.02) 100%);
}

.logout-btn i {
    width: 22px;
    text-align: center;
    font-size: 16px;
}

/* Scrollbar */
.sidebar-sticky::-webkit-scrollbar {
    width: 6px;
}

.sidebar-sticky::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar-sticky::-webkit-scrollbar-thumb {
    background: #e0e5ec;
    border-radius: 3px;
}

.sidebar-sticky::-webkit-scrollbar-thumb:hover {
    background: #c4c9d4;
}

/* Responsive */
@media (max-width: 768px) {
    #sidebar {
        height: auto;
        max-height: calc(100vh - 70px);
        overflow: hidden;
    }
    
    .sidebar-brand {
        padding: 16px;
        flex-shrink: 0;
    }
    
    .user-card {
        padding: 16px;
        flex-shrink: 0;
    }
    
    .quick-stats {
        flex-shrink: 0;
    }
    
    .nav-section {
        overflow-y: auto;
    }
    
    .sidebar-bottom {
        flex-shrink: 0;
    }
}
</style>

<div id="sidebar" class="col-lg-2 col-md-3 d-md-block sidebar collapse">
    <div class="sidebar-sticky">
        
        <!-- Brand Header -->
        <!-- <div class="sidebar-brand">
            <a href="/reviewer-dashboard" class="brand-logo">
                <div class="brand-icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="brand-text">
                    UG HARES
                    <span>Reviewer Portal</span>
                </div>
            </a>
        </div> -->
        
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

        <!-- Quick Stats -->
        <div class="quick-stats">
            <div class="quick-stats-title">Reviewer Stats</div>
            <div class="stats-row">
                <div class="stat-item" title="Pending Reviews">
                    <div class="stat-number"><?php echo $stats['pending'] ?? 0; ?></div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat-item" title="In Progress">
                    <div class="stat-number"><?php echo $stats['in_progress'] ?? 0; ?></div>
                    <div class="stat-label">In Progress</div>
                </div>
                <div class="stat-item" title="Completed">
                    <div class="stat-number"><?php echo $stats['completed'] ?? 0; ?></div>
                    <div class="stat-label">Done</div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="nav-section">
            <div class="nav-section-title">Main Menu</div>
            
            <a class="nav-link <?php echo ($current_page == 'index' || $current_page == 'reviewer-dashboard') ? 'active' : ''; ?>" href="/reviewer-dashboard">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            
            <a class="nav-link <?php echo ($current_page == 'reviews') ? 'active' : ''; ?>" href="/reviewer-dashboard/reviews">
                <i class="fas fa-clipboard-list"></i>
                <span>Pending Reviews</span>
                <?php if (($stats['pending'] ?? 0) > 0): ?>
                    <span class="badge"><?php echo $stats['pending']; ?></span>
                <?php endif; ?>
            </a>
            
            <a class="nav-link <?php echo ($current_page == 'meetings') ? 'active' : ''; ?>" href="/reviewer-dashboard/meetings">
                <i class="fas fa-calendar-alt"></i>
                <span>IRB Meetings</span>
            </a>
            
            <a class="nav-link <?php echo ($current_page == 'workload') ? 'active' : ''; ?>" href="/reviewer-dashboard/workload">
                <i class="fas fa-chart-bar"></i>
                <span>Workload</span>
            </a>
            
            <a class="nav-link <?php echo ($current_page == 'profile') ? 'active' : ''; ?>" href="/reviewer-dashboard/profile">
                <i class="fas fa-user"></i>
                <span>My Profile</span>
            </a>
        </div>

        <!-- Bottom Section -->
        <div class="sidebar-bottom">
            <div class="help-card">
                <div class="help-icon">
                    <i class="fas fa-question"></i>
                </div>
                <div class="help-title">Need Help?</div>
                <div class="help-text">Contact IRB office for assistance</div>
                <a href="mailto:nirb@noguchi.ug.edu.gh" class="help-btn">
                    <i class="fas fa-envelope"></i>
                    Email Support
                </a>
            </div>
            
            <form action="/logout" method="post" style="margin: 0;">
                <?php echo csrf_field(); ?>
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
</div>
