<?php

// NOTE: Authentication is already handled by index.php router
// No duplicate auth check needed here

$userId = $_SESSION['user_id'] ?? 0;
$userName = $_SESSION['full_name'] ?? 'Reviewer';

// Get filter from query params
$status_filter = $_GET['status'] ?? 'all';

// Get pending applications
$applications = getPendingApplications($userId);

// Get reviewer's assignments
$assignments = getReviewerAssignments($userId);

?>
<style>
/* Reviews Page Specific Styles */
.page-header-section {
    background: linear-gradient(135deg, #1a5276 0%, #2980b9 100%);
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 24px;
    box-shadow: 0 10px 40px rgba(41, 128, 185, 0.2);
}

.page-header-section h2 {
    font-weight: 700;
    margin-bottom: 8px;
}

.page-header-section p {
    opacity: 0.9;
    margin: 0;
}

/* Filter Tabs */
.filter-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.filter-tab {
    padding: 10px 20px;
    border-radius: 25px;
    border: 2px solid #e9ecef;
    background: white;
    color: #6c757d;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.filter-tab:hover {
    border-color: #2980b9;
    color: #2980b9;
}

.filter-tab.active {
    background: linear-gradient(135deg, #1a5276 0%, #2980b9 100%);
    border-color: transparent;
    color: white;
}

/* Application Card */
.review-application-card {
    border: none;
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    margin-bottom: 20px;
}

.review-application-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

.review-application-card .card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: none;
    padding: 20px;
}

.review-application-card .protocol-badge {
    display: inline-block;
    padding: 6px 12px;
    background: linear-gradient(135deg, #1a5276 0%, #2980b9 100%);
    color: white;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 8px;
}

.review-application-card .study-title {
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 4px;
}

.review-application-card .applicant-info {
    font-size: 14px;
    color: #6c757d;
}

.review-application-card .card-body {
    padding: 20px;
}

.application-meta {
    display: flex;
    gap: 24px;
    margin-bottom: 16px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.meta-item i {
    color: #2980b9;
}

.meta-label {
    font-size: 12px;
    color: #6c757d;
}

.meta-value {
    font-weight: 600;
    color: #2c3e50;
}

/* Priority Badge */
.priority-badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.priority-badge.high {
    background: #f8d7da;
    color: #721c24;
}

.priority-badge.medium {
    background: #fff3cd;
    color: #856404;
}

.priority-badge.low {
    background: #d1ecf1;
    color: #0c5460;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 40px;
}

.empty-state-icon {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
}

.empty-state-icon i {
    font-size: 48px;
    color: #adb5bd;
}

.empty-state h5 {
    color: #2c3e50;
    margin-bottom: 8px;
}

.empty-state p {
    color: #6c757d;
    margin-bottom: 24px;
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

            <!-- Page Header -->
            <div class="page-header-section text-white fade-in">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-1"><i class="fas fa-clipboard-check me-3"></i>Pending Reviews</h2>
                        <p class="mb-0">Review and process IRB applications efficiently</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <span class="badge bg-light text-dark" style="border-radius: 25px; padding: 10px 20px; font-size: 14px;">
                            <i class="fas fa-clock me-2"></i>
                            <?php echo count($applications); ?> Pending
                        </span>
                    </div>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div class="filter-tabs fade-in">
                <a href="?status=all" class="filter-tab <?php echo $status_filter == 'all' ? 'active' : ''; ?>">
                    All Applications
                </a>
                <a href="?status=submitted" class="filter-tab <?php echo $status_filter == 'submitted' ? 'active' : ''; ?>">
                    New Submissions
                </a>
                <a href="?status=under_review" class="filter-tab <?php echo $status_filter == 'under_review' ? 'active' : ''; ?>">
                    Under Review
                </a>
                <a href="?status=my_reviews" class="filter-tab <?php echo $status_filter == 'my_reviews' ? 'active' : ''; ?>">
                    My Assignments
                </a>
            </div>

            <!-- Applications List -->
            <div class="row">
                <?php 
                $displayApplications = $status_filter == 'my_reviews' ? $assignments : $applications;
                
                if (!empty($displayApplications)): 
                ?>
                    <?php foreach ($displayApplications as $index => $app): ?>
                        <div class="col-lg-6 fade-in" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                            <div class="review-application-card">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="protocol-badge">
                                                <?php echo htmlspecialchars($app['protocol_number'] ?? 'PENDING'); ?>
                                            </span>
                                            <span class="priority-badge <?php echo ($app['priority'] ?? 'medium'); ?>">
                                                <?php echo ucfirst($app['priority'] ?? 'Medium'); ?> Priority
                                            </span>
                                        </div>
                                        <span class="badge bg-info" style="border-radius: 20px;">
                                            <?php echo getApplicationTypeName($app['application_type'] ?? 'student'); ?>
                                        </span>
                                    </div>
                                    <h5 class="study-title mt-3">
                                        <?php echo htmlspecialchars($app['study_title'] ?? 'Untitled Application'); ?>
                                    </h5>
                                    <div class="applicant-info">
                                        <i class="fas fa-user me-2"></i>
                                        <?php echo htmlspecialchars($app['applicant_name'] ?? 'Unknown Applicant'); ?>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="application-meta">
                                        <div class="meta-item">
                                            <i class="fas fa-calendar-alt"></i>
                                            <div>
                                                <div class="meta-label">Submitted</div>
                                                <div class="meta-value">
                                                    <?php 
                                                        $date = $app['submitted_at'] ?? $app['created_at'];
                                                        echo $date ? date('M d, Y', strtotime($date)) : 'N/A';
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="meta-item">
                                            <i class="fas fa-tag"></i>
                                            <div>
                                                <div class="meta-label">Status</div>
                                                <div class="meta-value">
                                                    <span class="badge bg-warning" style="border-radius: 20px;">
                                                        <?php echo getStatusLabel($app['status'] ?? 'submitted'); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex gap-2 mt-3">
                                        <?php if ($status_filter == 'my_reviews' || in_array($app['status'] ?? '', ['submitted', 'under_review'])): ?>
                                            <a href="/reviewer-dashboard/review?id=<?php echo $app['id']; ?>" 
                                               class="btn btn-primary flex-grow-1" 
                                               style="border-radius: 25px; font-weight: 600;">
                                                <i class="fas fa-search me-2"></i>Review Application
                                            </a>
                                        <?php else: ?>
                                            <a href="/reviewer-dashboard/review?id=<?php echo $app['id']; ?>" 
                                               class="btn btn-outline-primary flex-grow-1" 
                                               style="border-radius: 25px; font-weight: 600;">
                                                <i class="fas fa-eye me-2"></i>View Details
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($status_filter != 'my_reviews'): ?>
                                            <button class="btn btn-outline-secondary" 
                                                    onclick="assignToMe(<?php echo $app['id']; ?>)"
                                                    title="Assign to me for review"
                                                    style="border-radius: 25px;">
                                                <i class="fas fa-user-plus"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="empty-state fade-in">
                            <div class="empty-state-icon">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <h5>No Applications Found</h5>
                            <p>
                                <?php if ($status_filter == 'my_reviews'): ?>
                                    You don't have any assigned reviews at the moment.
                                <?php else: ?>
                                    There are no applications waiting for review.
                                <?php endif; ?>
                            </p>
                            <a href="/reviewer-dashboard" class="btn btn-primary" style="border-radius: 25px; padding: 12px 30px;">
                                <i class="fas fa-home me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Help Section -->
            <div class="card mt-4 fade-in" style="border-radius: 16px; border: none; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);">
                <div class="card-header" style="background: linear-gradient(135deg, #1a5276 0%, #2980b9 100%); color: white; border-radius: 16px 16px 0 0; border: none;">
                    <h5 class="mb-0"><i class="fas fa-question-circle me-2"></i>Review Guidelines</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="d-flex align-items-start">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); display: flex; align-items: center; justify-content: center; margin-right: 12px; flex-shrink: 0;">
                                    <i class="fas fa-check" style="color: #155724;"></i>
                                </div>
                                <div>
                                    <h6 style="font-weight: 600; color: #2c3e50;">Approve</h6>
                                    <p class="mb-0 text-muted" style="font-size: 13px;">When application meets all IRB requirements</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="d-flex align-items-start">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%); display: flex; align-items: center; justify-content: center; margin-right: 12px; flex-shrink: 0;">
                                    <i class="fas fa-edit" style="color: #856404;"></i>
                                </div>
                                <div>
                                    <h6 style="font-weight: 600; color: #2c3e50;">Request Changes</h6>
                                    <p class="mb-0 text-muted" style="font-size: 13px;">When minor revisions are needed</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="d-flex align-items-start">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%); display: flex; align-items: center; justify-content: center; margin-right: 12px; flex-shrink: 0;">
                                    <i class="fas fa-times" style="color: #721c24;"></i>
                                </div>
                                <div>
                                    <h6 style="font-weight: 600; color: #2c3e50;">Reject</h6>
                                    <p class="mb-0 text-muted" style="font-size: 13px;">When application does not meet basic criteria</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function assignToMe(applicationId) {
    if (confirm('Are you sure you want to assign this application to yourself for review?')) {
        fetch('/reviewer/handlers/assign_application.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'application_id=' + applicationId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to assign application'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
}
</script>
