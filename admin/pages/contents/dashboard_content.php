<?php
$activeStudies = getActiveStudiesCount();
$pendingReviews = getPendingReviewsCount();
$overdueActions = getOverdueActionsCount();
$newSAEReports = getNewReportsCount();
$recentActivities = getRecentActivities();

$user_name = "";
if (isset($_SESSION['user_id'])) {
    $db = new Database();
    $conn = $db->connect();

    $stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $user_name = $user['full_name'];
    }
}
?>
<style>
    .section-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-right: 16px;
        background: linear-gradient(135deg, var(--applicant-primary-dark) 0%, var(--applicant-primary) 100%);
        color: white;
    }
</style>

<!-- Main Content -->
<div class="dashboard-premium">

    <!-- Welcome Header -->
    <div class="welcome-header-admin text-white mb-4 fade-in-up">
        <div class="welcome-content-admin">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="welcome-title-admin">Welcome back, <?php echo htmlspecialchars($user_name ?? ''); ?>!</h1>
                    <p class="welcome-subtitle-admin mb-0">You have <?php echo $pendingReviews; ?> pending tasks that need your attention.</p>
                </div>
                <div class="d-none d-lg-block" style="font-size: 48px; opacity: 0.3;">
                    <i class="fas fa-clipboard-check"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stats-card-premium text-white fade-in-up" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="stats-icon" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-flask"></i>
                            </div>
                            <div class="stats-label">Active Studies</div>
                        </div>

                        <div>
                            <div class="stats-number"><?php echo $activeStudies; ?></div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stats-card-premium text-white fade-in-up" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="stats-icon" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-clock"></i>
                            </div>

                            <div class="stats-label">Pending Reviews</div>
                        </div>
                        <div>
                            <div class="stats-number"><?php echo $pendingReviews; ?></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stats-card-premium text-white fade-in-up" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="stats-icon" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>

                            <div class="stats-label">Overdue Actions</div>
                        </div>
                        <div>
                            <div class="stats-number"><?php echo $overdueActions; ?></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stats-card-premium text-white fade-in-up" style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="stats-icon" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-file-medical"></i>
                            </div>

                            <div class="stats-label">Reports</div>
                        </div>
                        <div>
                            <div class="stats-number"><?php echo $newSAEReports; ?></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mb-4 fade-in-up">
        <div class="d-flex section-header-premium">
            <div class="section-icon">
                <i class="fas fa-bolt"></i>
            </div>
            <div>
                <h4>Quick Actions</h4>
                <p>Common administrative tasks</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="quick-action-premium bg-white">
                    <div class="card-body">
                        <a href="/studies/add-study" style="text-decoration:none;">
                            <div class="action-icon">
                                <i class="fas fa-plus"></i>
                            </div>
                            <h6>New Study</h6>
                            <p>Create a new study protocol</p>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="quick-action-premium bg-white">
                    <div class="card-body">
                        <a href="/dashboard/continue-review" style="text-decoration:none;">
                            <div class="action-icon">
                                <i class="fas fa-sync"></i>
                            </div>
                            <h6>Continuing Review</h6>
                            <p>Review ongoing studies</p>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="quick-action-premium bg-white">
                    <div class="card-body">
                        <a href="/dashboard/report" style="text-decoration:none;">
                            <div class="action-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <h6>SAE Reports</h6>
                            <p>View adverse events</p>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="quick-action-premium bg-white">
                    <div class="card-body">
                        <a href="/dashboard/report" style="text-decoration:none;">
                            <div class="action-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h6>PI Reports</h6>
                            <p>Investigator reports</p>
                        </a>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="fade-in-up">
        <div class="d-flex section-header-premium">
            <div class="section-icon">
                <i class="fas fa-history"></i>
            </div>
            <div>
                <h4>Recent Activities</h4>
                <p>Latest study updates and changes</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-premium">
                <thead>
                    <tr>
                        <th><i class="fas fa-flask me-2"></i>Study Name</th>
                        <th><i class="fas fa-user-md me-2"></i>Principal Investigator</th>
                        <th><i class="fas fa-info-circle me-2"></i>Status</th>
                        <th><i class="fas fa-clock me-2"></i>Last Activity</th>
                        <th><i class="fas fa-cog me-2"></i>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($recentActivities)) {
                        foreach ($recentActivities as $activity) {
                            $statusBadge = '';
                            switch ($activity['study_status']) {
                                case 'open':
                                    $statusBadge = '<span class="badge" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); padding: 6px 14px; border-radius: 20px;">Open</span>';
                                    break;
                                case 'pending':
                                    $statusBadge = '<span class="badge" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); padding: 6px 14px; border-radius: 20px;">Pending Review</span>';
                                    break;
                                case 'closed':
                                    $statusBadge = '<span class="badge" style="background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%); padding: 6px 14px; border-radius: 20px;">Closed</span>';
                                    break;
                                default:
                                    $statusBadge = '<span class="badge bg-secondary" style="padding: 6px 14px; border-radius: 20px;">' . ucfirst($activity['study_status']) . '</span>';
                            }
                            $lastActivity = date('M j, Y', strtotime($activity['updated_at']));
                            echo "<tr>
                                <td><strong>" . htmlspecialchars($activity['title']) . "</strong></td>
                                <td>" . htmlspecialchars($activity['pi']) . "</td>
                                <td>{$statusBadge}</td>
                                <td>{$lastActivity}</td>
                                <td><button class='btn btn-sm btn-outline-primary' style='border-radius: 20px;'><i class='fas fa-eye me-1'></i>View</button></td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center py-5'><i class='fas fa-inbox fa-3x text-muted mb-3'></i><p class='text-muted'>No recent activities found.</p></td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</div>