<?php

$current_page = basename($_SERVER['PHP_SELF']);

error_log("Current Page: ". $current_page);

$meetingDates = getMeetingDates();
?>
<div id="sidebar" class="col-lg-2 col-md-3 d-md-block sidebar collapse">
    <div class="sidebar-sticky">
        <!-- Dashboard - Always visible -->
        <ul class="nav flex-column mb-3">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>" href="/dashboard">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
        </ul>


        <div class="sidebar-section mb-3">
            <h6 class="sidebar-header ms-4">
                <i class="fas fa-plus-circle me-2"></i>Add or Modify
            </h6>
            <ul class="nav flex-column submenu-nav">
                <li class="nav-item">
                    <a class="nav-link submenu-link <?php echo ($current_page == 'studies') ? 'active' : ''; ?>" href="/dashboard/studies">
                        <i class="fas fa-file-medical me-2"></i>Study / Protocol
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link submenu-link <?php echo ($current_page == 'preliminary-agenda') ? 'active' : ''; ?>" href="/dashboard/preliminary-agenda">
                        <i class="fas fa-calendar-alt me-2"></i>Preliminary Agenda Items
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link submenu-link <?php echo ($current_page == 'continue-review') ? 'active' : ''; ?>" href="/dashboard/continue-review">
                        <i class="fas fa-eye me-2"></i>Due for Continuing Review
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link submenu-link <?php echo ($current_page == 'post-irb-meeting') ? 'active' : ''; ?>" href="#" data-bs-target="#postIrbModal" data-bs-toggle="modal">
                        <i class="fas fa-clipboard-check me-2"></i>Post IRB Meeting Actions
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link submenu-link <?php echo ($current_page == 'agenda-records') ? 'active' : ''; ?>" href="/dashboard/agenda-records">
                        <i class="fas fa-book me-2"></i>Agenda Records
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link submenu-link <?php echo ($current_page == 'reports') ? 'active' : ''; ?>" href="/dashboard/reports">
                        <i class="fas fa-chart-bar me-2"></i>Reports
                    </a>
                </li>
            </ul>
        </div>

        <!-- Task Managers Section -->
        <div class="sidebar-section mb-3">
            <h6 class="sidebar-header ms-4">
                <i class="fas fa-tasks me-2"></i>Task Managers
            </h6>
            <ul class="nav flex-column submenu-nav">
                <li class="nav-item">
                    <a class="nav-link submenu-link <?php echo ($current_page == 'follow-up') ? 'active' : ''; ?>" href="/dashboard/follow-up">
                        <i class="fas fa-clock me-2"></i>Follow Up Manager
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link submenu-link <?php echo ($current_page == 'administration') ? 'active' : ''; ?>" href="/dashboard/administration">
                        <i class="fas fa-toolbox me-2"></i>Administration
                    </a>
                </li>
                <!-- <li class="nav-item">
                    <a class="nav-link submenu-link <?php echo ($current_page == 'general-letters') ? 'active' : ''; ?>" href="/dashboard/general-letters">
                        <i class="fas fa-envelope me-2"></i>General Letter Choices
                    </a>
                </li> -->
            </ul>
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