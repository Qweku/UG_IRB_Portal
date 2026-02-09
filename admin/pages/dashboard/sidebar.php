<?php

$current_page = basename($_SERVER['PHP_SELF']);
$meetingDates = getMeetingDates();
$userRole = $_SESSION['role'] ?? 'reviewer';
$userName = $_SESSION['full_name'] ?? 'Admin';
$showAdminSections = ($userRole === 'admin' || $userRole === 'super_admin');
$showInstitutionSection = ($userRole === 'super_admin');

// Get admin stats
$activeStudies = getActiveStudiesCount();
$pendingReviews = getPendingReviewsCount();

?>


<div id="sidebar" class="col-lg-2 col-md-3 d-md-block sidebar collapse">
    <div class="sidebar-sticky">



        <!-- User Card -->
        <!-- <div class="user-card">
            <div class="user-avatar-wrapper">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($userName, 0, 1)); ?>
                </div>
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($userName); ?></div>
                    <div class="user-role"><?php echo htmlspecialchars($userRole); ?></div>
                </div>
            </div>
        </div> -->



        <!-- Navigation - Dashboard -->
        <div class="nav-section">
            <div class="nav-section-title">Main Menu</div>

            <a class="nav-link <?php echo ($current_page == 'dashboard' || $current_page == 'index.php') ? 'active' : ''; ?>" href="/dashboard">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>

            <!-- Add or Modify Section -->
            <div class="nav-section-title" style="margin-top: 16px;">Management</div>

            <a class="nav-link <?php echo ($current_page == 'studies') ? 'active' : ''; ?>" href="/dashboard/studies">
                <i class="fas fa-file-medical"></i>
                <span>Studies / Protocol</span>
            </a>

            <a class="nav-link <?php echo ($current_page == 'preliminary-agenda') ? 'active' : ''; ?>" href="/dashboard/preliminary-agenda">
                <i class="fas fa-calendar-alt"></i>
                <span>Agenda Items</span>
            </a>

            <a class="nav-link <?php echo ($current_page == 'continue-review') ? 'active' : ''; ?>" href="/dashboard/continue-review">
                <i class="fas fa-eye"></i>
                <span>Continuing Review</span>
            </a>

            <a class="nav-link <?php echo ($current_page == 'post-irb-meeting') ? 'active' : ''; ?>" href="#" data-bs-target="#postIrbModal" data-bs-toggle="modal">
                <i class="fas fa-clipboard-check"></i>
                <span>Post IRB Meeting Actions</span>
            </a>

            <a class="nav-link <?php echo ($current_page == 'agenda-records') ? 'active' : ''; ?>" href="/dashboard/agenda-records">
                <i class="fas fa-book"></i>
                <span>Agenda Records</span>
            </a>

            <a class="nav-link <?php echo ($current_page == 'reports') ? 'active' : ''; ?>" href="/dashboard/reports">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>

            <!-- Task Managers Section -->
            <?php if ($showAdminSections): ?>
                <div class="nav-section-title" style="margin-top: 16px;">Administration</div>

                <a class="nav-link <?php echo ($current_page == 'follow-up') ? 'active' : ''; ?>" href="/dashboard/follow-up">
                    <i class="fas fa-clock"></i>
                    <span>Follow Up</span>
                </a>

                <a class="nav-link <?php echo ($current_page == 'administration') ? 'active' : ''; ?>" href="/dashboard/administration">
                    <i class="fas fa-toolbox"></i>
                    <span>Settings</span>
                </a>

                <?php if ($showInstitutionSection): ?>
                    <a class="nav-link <?php echo ($current_page == 'institutions') ? 'active' : ''; ?>" href="/dashboard/institutions">
                        <i class="fas fa-building"></i>
                        <span>Institutions</span>
                    </a>
                <?php endif; ?>

            <?php endif; ?>
        </div>


    </div>
</div>


<!-- Post IRB Actions Modal -->
<div class="modal fade" id="postIrbModal" tabindex="-1" aria-labelledby="postIrbModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="postIrbModalLabel">Post IRB Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <h6 class="fw-bold text-primary mb-2">IRB NOGUCHI MEMORIAL INSTITUTE FOR MEDICAL RESEARCH-IRB</h6>
                    <p class="text-muted small">
                        Verify the IRB and the Meeting Date you wish to Post IRB Actions to
                    </p>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Select a meeting date</label>
                    <div class="list-group">
                        <?php foreach ($meetingDates as $mDate): ?>
                            <label class="list-group-item list-group-item-action">
                                <input class="form-check-input me-2" type="radio" name="meetingDate" value="<?= htmlspecialchars($mDate) ?>">
                                <?= htmlspecialchars($mDate) ?>
                            </label>

                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="postIrbOkBtn" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>


<script>
    const postIrbOkBtn = document.getElementById('postIrbOkBtn');
    postIrbOkBtn.addEventListener('click', () => {
        const selectedDateInput = document.querySelector('input[name="meetingDate"]:checked');
        if (!selectedDateInput) {
            alert("Please select a meeting date.");
            return;
        }

        const newMeetingDate = selectedDateInput.value;

        // Redirect to post_meeting_content.php with the date
        window.location.href = '/dashboard/post-irb-meeting?meeting_date=' + encodeURIComponent(newMeetingDate);
    });
</script>