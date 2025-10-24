<?php
$activeStudies = getActiveStudiesCount();
$pendingReviews = getPendingReviewsCount();
$overdueActions = getOverdueActionsCount();
$newSAEReports = getNewSAEReportsCount();
$recentActivities = getRecentActivities();
?>
<!-- Main Content -->
<div class="">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <h2>Hello, Admin User!</h2>
        <p class="mb-0">Welcome back to the Noguchi ProIRB. You have <?php echo $pendingReviews; ?> pending tasks that need your attention.</p>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card-dashboard text-center p-3">
                <div class="stats-number"><?php echo $activeStudies; ?></div>
                <div class="stats-label">Active Studies</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-dashboard text-center p-3">
                <div class="stats-number"><?php echo $pendingReviews; ?></div>
                <div class="stats-label">Pending Reviews</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-dashboard text-center p-3">
                <div class="stats-number"><?php echo $overdueActions; ?></div>
                <div class="stats-label">Overdue Actions</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-dashboard text-center p-3">
                <div class="stats-number"><?php echo $newSAEReports; ?></div>
                <div class="stats-label">New SAE Reports</div>
            </div>
        </div>
    </div>

    <!-- Search Container -->
    <div class="search-container mb-4">
        <h4 class="mb-3">Study Quick Search</h4>
        <div class="input-group">
            <input type="text" class="form-control" placeholder="Search by study name, PI, or ID...">
            <button class="btn btn-primary" type="button">
                <i class="fas fa-search me-1"></i> Search
            </button>
        </div>
        <div class="mt-2">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="openStudies" checked>
                <label class="form-check-label" for="openStudies">Open Studies</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="closedStudies">
                <label class="form-check-label" for="closedStudies">Closed Studies</label>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        <h4 class="section-title">Quick Actions</h4>
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="quick-action">
                    <i class="fas fa-plus-circle"></i>
                    <h6>New Study</h6>
                    <p class="small">Create a new study protocol</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="quick-action">
                    <i class="fas fa-file-medical"></i>
                    <h6>Continuing Review</h6>
                    <p class="small">Review ongoing studies</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="quick-action">
                    <i class="fas fa-exclamation-circle"></i>
                    <h6>SAE Reports</h6>
                    <p class="small">View serious adverse events</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="quick-action">
                    <i class="fas fa-user-md"></i>
                    <h6>PI Reports</h6>
                    <p class="small">Reports by principal investigator</p>
                </div>
            </div>
        </div>

        <h4 class="section-title">Recent Activities</h4>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Study Name</th>
                        <th>Principal Investigator</th>
                        <th>Status</th>
                        <th>Last Activity</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($recentActivities as $activity) {
                        $statusBadge = '';
                        switch ($activity['status']) {
                            case 'open':
                                $statusBadge = '<span class="badge bg-success">Open</span>';
                                break;
                            case 'pending':
                                $statusBadge = '<span class="badge bg-warning text-dark">Pending Review</span>';
                                break;
                            case 'closed':
                                $statusBadge = '<span class="badge bg-secondary">Closed</span>';
                                break;
                            default:
                                $statusBadge = '<span class="badge bg-info">' . ucfirst($activity['status']) . '</span>';
                        }
                        $lastActivity = date('M j, Y', strtotime($activity['updated_at']));
                        echo "<tr>
       <td>{$activity['title']}</td>
       <td>N/A</td> <!-- Placeholder, as PI not directly in studies table -->
       <td>{$statusBadge}</td>
       <td>{$lastActivity}</td>
       <td><button class='btn btn-sm btn-outline-primary'>View</button></td>
   </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>