<?php

// NOTE: Authentication is already handled by index.php router
// No duplicate auth check needed here

$userId = $_SESSION['user_id'] ?? 0;
$userName = $_SESSION['full_name'] ?? 'Reviewer';

// Get reviewer stats
$stats = getReviewerStats($userId);

// Get reviewer's assignments
$assignments = getReviewerAssignments($userId);

// Calculate workload metrics
$totalAssignments = count($assignments);
$pendingCount = count(array_filter($assignments, fn($a) => ($a['review_status'] ?? '') === 'assigned'));
$inProgressCount = count(array_filter($assignments, fn($a) => ($a['review_status'] ?? '') === 'in_progress'));
$completedCount = count(array_filter($assignments, fn($a) => ($a['review_status'] ?? '') === 'completed'));

// Calculate completion rate
$completionRate = $totalAssignments > 0 ? round(($completedCount / $totalAssignments) * 100) : 0;

?>
<style>
/* Workload Page Specific Styles */
.workload-header-section {
    background: linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%);
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 24px;
    box-shadow: 0 10px 40px rgba(142, 68, 173, 0.2);
}

.workload-stat-card {
    border: none;
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    height: 100%;
}

.workload-stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

.workload-stat-card .card-body {
    padding: 24px;
    text-align: center;
}

.workload-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    font-size: 32px;
}

.workload-stat-card .stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    color: #2c3e50;
    line-height: 1;
}

.workload-stat-card .stat-label {
    font-size: 14px;
    color: #6c757d;
    margin-top: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Progress Section */
.progress-section {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
}

.progress-ring-container {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 24px;
}

.progress-ring {
    width: 180px;
    height: 180px;
}

.progress-ring circle {
    transition: stroke-dashoffset 0.5s ease;
    transform: rotate(-90deg);
    transform-origin: 50% 50%;
}

.progress-center {
    text-align: center;
}

.progress-center .percentage {
    font-size: 2.5rem;
    font-weight: 800;
    color: #8e44ad;
}

.progress-center .label {
    color: #6c757d;
}

/* Assignment Table */
.assignment-table {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
}

.assignment-table thead th {
    background: linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%);
    color: white;
    border: none;
    padding: 16px 20px;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 12px;
    letter-spacing: 0.5px;
}

.assignment-table tbody tr {
    transition: all 0.3s ease;
    border-bottom: 1px solid #e9ecef;
}

.assignment-table tbody tr:hover {
    background-color: rgba(142, 68, 173, 0.03);
}

.assignment-table tbody td {
    padding: 16px 20px;
    border: none;
    vertical-align: middle;
}

/* Status Badge */
.status-badge-custom {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge-custom.assigned {
    background: #e9ecef;
    color: #6c757d;
}

.status-badge-custom.in_progress {
    background: #cce5ff;
    color: #004085;
}

.status-badge-custom.completed {
    background: #d4edda;
    color: #155724;
}

/* Performance Chart Placeholder */
.performance-card {
    border: none;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
}

.performance-card .card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: none;
    padding: 20px;
}

.performance-metric {
    padding: 16px 0;
    border-bottom: 1px solid #e9ecef;
}

.performance-metric:last-child {
    border-bottom: none;
}

.metric-bar {
    height: 8px;
    border-radius: 4px;
    background: #e9ecef;
    overflow: hidden;
    margin-top: 8px;
}

.metric-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.5s ease;
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
            <div class="workload-header-section text-white fade-in">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-1"><i class="fas fa-chart-bar me-3"></i>My Workload</h2>
                        <p class="mb-0">Track your review performance and statistics</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <span class="badge bg-light text-dark" style="border-radius: 25px; padding: 10px 20px; font-size: 14px;">
                            <i class="fas fa-tasks me-2"></i>
                            <?php echo $totalAssignments; ?> Total Reviews
                        </span>
                    </div>
                </div>
            </div>

            <!-- Stats Cards Row -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="workload-stat-card fade-in">
                        <div class="card-body">
                            <div class="workload-icon" style="background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%); color: #856404;">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-number"><?php echo $pendingCount; ?></div>
                            <div class="stat-label">Pending</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="workload-stat-card fade-in" style="animation-delay: 0.1s;">
                        <div class="card-body">
                            <div class="workload-icon" style="background: linear-gradient(135deg, #cce5ff 0%, #b8daff 100%); color: #004085;">
                                <i class="fas fa-spinner"></i>
                            </div>
                            <div class="stat-number"><?php echo $inProgressCount; ?></div>
                            <div class="stat-label">In Progress</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="workload-stat-card fade-in" style="animation-delay: 0.2s;">
                        <div class="card-body">
                            <div class="workload-icon" style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); color: #155724;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-number"><?php echo $completedCount; ?></div>
                            <div class="stat-label">Completed</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="workload-stat-card fade-in" style="animation-delay: 0.3s;">
                        <div class="card-body">
                            <div class="workload-icon" style="background: linear-gradient(135deg, #e2d5f1 0%, #d4b8e8 100%); color: #6c3483;">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <div class="stat-number"><?php echo $completionRate; ?>%</div>
                            <div class="stat-label">Completion Rate</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Progress Ring -->
                <div class="col-lg-4 mb-4">
                    <div class="progress-section fade-in">
                        <h5 class="text-center mb-4" style="font-weight: 700; color: #2c3e50;">
                            <i class="fas fa-chart-pie me-2"></i>Review Progress
                        </h5>
                        <div class="progress-ring-container">
                            <svg class="progress-ring" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="45" fill="none" stroke="#e9ecef" stroke-width="8"/>
                                <circle cx="50" cy="50" r="45" fill="none" stroke="#8e44ad" stroke-width="8"
                                        stroke-dasharray="282.7" 
                                        stroke-dashoffset="<?php echo 282.7 - (282.7 * $completionRate / 100); ?>"
                                        stroke-linecap="round"/>
                            </svg>
                            <div class="progress-center">
                                <div class="percentage"><?php echo $completionRate; ?>%</div>
                                <div class="label">Complete</div>
                            </div>
                        </div>
                        
                        <div class="row text-center mt-4">
                            <div class="col-4">
                                <div class="fw-bold text-warning"><?php echo $pendingCount; ?></div>
                                <small class="text-muted">Pending</small>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold text-info"><?php echo $inProgressCount; ?></div>
                                <small class="text-muted">Active</small>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold text-success"><?php echo $completedCount; ?></div>
                                <small class="text-muted">Done</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics -->
                <div class="col-lg-8 mb-4">
                    <div class="performance-card fade-in">
                        <div class="card-header">
                            <h5 class="mb-0" style="font-weight: 700; color: #2c3e50;">
                                <i class="fas fa-trophy me-2"></i>Performance Metrics
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="performance-metric">
                                <div class="d-flex justify-content-between">
                                    <span>Average Review Time</span>
                                    <span class="fw-600"><?php echo $stats['avg_review_time']; ?></span>
                                </div>
                                <div class="metric-bar">
                                    <div class="metric-fill" style="width: 75%; background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);"></div>
                                </div>
                            </div>
                            
                            <div class="performance-metric">
                                <div class="d-flex justify-content-between">
                                    <span>On-Time Submissions</span>
                                    <span class="fw-600">95%</span>
                                </div>
                                <div class="metric-bar">
                                    <div class="metric-fill" style="width: 95%; background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);"></div>
                                </div>
                            </div>
                            
                            <div class="performance-metric">
                                <div class="d-flex justify-content-between">
                                    <span>Quality Score</span>
                                    <span class="fw-600">4.8/5.0</span>
                                </div>
                                <div class="metric-bar">
                                    <div class="metric-fill" style="width: 96%; background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);"></div>
                                </div>
                            </div>
                            
                            <div class="performance-metric">
                                <div class="d-flex justify-content-between">
                                    <span>Peer Approval Rate</span>
                                    <span class="fw-600">98%</span>
                                </div>
                                <div class="metric-bar">
                                    <div class="metric-fill" style="width: 98%; background: linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%);"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assignments Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm fade-in" style="border-radius: 16px;">
                        <div class="card-header bg-white" style="border-radius: 16px 16px 0 0; padding: 20px; border-bottom: 1px solid #e9ecef;">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0" style="font-weight: 700; color: #2c3e50;">
                                    <i class="fas fa-history me-2" style="color: #8e44ad;"></i>
                                    Review History
                                </h5>
                            </div>
                        </div>
                        <div class="card-body" style="padding: 0;">
                            <?php if (!empty($assignments)): ?>
                                <div class="table-responsive">
                                    <table class="table assignment-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Study Title</th>
                                                <th>Applicant</th>
                                                <th>Assigned Date</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($assignments as $assignment): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($assignment['study_title'] ?? 'Untitled'); ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($assignment['protocol_number'] ?? 'N/A'); ?></small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($assignment['applicant_name'] ?? 'Unknown'); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($assignment['assigned_at'] ?? $assignment['created_at'])); ?></td>
                                                    <td>
                                                        <span class="status-badge-custom <?php echo $assignment['review_status'] ?? 'assigned'; ?>">
                                                            <?php echo getStatusLabel($assignment['review_status'] ?? 'assigned'); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="/reviewer-dashboard/review?id=<?php echo $assignment['id']; ?>" class="btn btn-sm btn-outline-primary" style="border-radius: 20px;">
                                                            <i class="fas fa-eye me-1"></i>View
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                                        <i class="fas fa-clipboard-list" style="font-size: 36px; color: #adb5bd;"></i>
                                    </div>
                                    <h5 style="color: #2c3e50; margin-bottom: 8px;">No Review History</h5>
                                    <p class="text-muted mb-0">You haven't been assigned any applications to review yet.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
