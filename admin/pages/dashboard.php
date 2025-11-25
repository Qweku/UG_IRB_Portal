<?php

// session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login');
    exit;
}

$meetingDates = getMeetingDates();

?>



<div class="container-fluid">
    <div class="row">
        <!-- Side Bar -->
        <div id="sidebar" class="col-lg-2 col-md-3 d-md-block sidebar collapse">
            <div class="sidebar-sticky">
                <!-- Dashboard - Always visible -->
                <ul class="nav flex-column mb-3">
                    <li class="nav-item">
                        <a class="nav-link active" href="#" data-target="dashboard-content">
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
                            <a class="nav-link submenu-link" href="#" data-target="study-content">
                                <i class="fas fa-file-medical me-2"></i>Study / Protocol
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link submenu-link" href="#" data-target="preliminary-agenda-content">
                                <i class="fas fa-calendar-alt me-2"></i>Preliminary Agenda Items
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link submenu-link" href="#" data-target="due-continue-review-content">
                                <i class="fas fa-eye me-2"></i>Due for Continuing Review
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link submenu-link" href="#" data-bs-target="#postIrbModal" data-bs-toggle="modal" data-target="post-meeting-content">
                                <i class="fas fa-clipboard-check me-2"></i>Post IRB Meeting Actions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link submenu-link" href="#" data-target="agenda-records-content">
                                <i class="fas fa-book me-2"></i>Agenda Records
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link submenu-link" href="#" data-target="reports-content">
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
                            <a class="nav-link submenu-link" href="#" data-target="follow-up-content">
                                <i class="fas fa-clock me-2"></i>Follow Up Manager
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link submenu-link" href="#" data-target="administration-content">
                                <i class="fas fa-toolbox me-2"></i>Administration
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link submenu-link" href="#" data-target="general-letters-content">
                                <i class="fas fa-envelope me-2"></i>General Letter Choices
                            </a>
                        </li>
                    </ul>
                </div>


            </div>
        </div>

        <!-- Main Content Area -->
        <div id="dashboard-content" class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3">
            <?php include 'contents/dashboard_content.php'; ?>
        </div>
        <div id="study-content" class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3" style="display: none;">
            <?php include 'contents/study_content.php'; ?>
        </div>
        <div id="reports-content" class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3" style="display: none;">
            <?php include 'contents/reports_content.php'; ?>
        </div>

        <div id="task-managers-content" class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3" style="display: none;">
            <?php include 'contents/task_managers_content.php'; ?>
        </div>

        <div id="administration-content" class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3" style="display: none;">
            <?php include 'contents/administration_content.php'; ?>
        </div>

        <div id="general-letters-content" class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3" style="display: none;">
            <?php include 'contents/general_letters_content.php'; ?>
        </div>
        <div id="preliminary-agenda-content" class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3" style="display: none;">
            <?php include 'contents/preliminary_agenda_content.php'; ?>
        </div>
        <div id="due-continue-review-content" class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3" style="display: none;">
            <?php include 'contents/due_continue_review_content.php'; ?>
        </div>
        <div id="post-meeting-content" class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3" style="display: none;">
            <?php include 'contents/post_meeting_content.php'; ?>
        </div>
        <div id="agenda-records-content" class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3" style="display: none;">
            <?php include 'contents/agenda_records_content.php'; ?>
        </div>

        <div id="follow-up-content" class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3" style="display: none;">
            <?php include 'contents/follow_up_content.php'; ?>
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
                        <!-- <label class="list-group-item list-group-item-action">
                            <input class="form-check-input me-2" type="radio" name="meetingDate" value="2026-01-07" checked>
                            2026-01-07
                        </label>
                        <label class="list-group-item list-group-item-action">
                            <input class="form-check-input me-2" type="radio" name="meetingDate" value="2025-12-03">
                            2025-12-03
                        </label>
                        <label class="list-group-item list-group-item-action">
                            <input class="form-check-input me-2" type="radio" name="meetingDate" value="2025-11-05">
                            2025-11-05
                        </label>
                        <label class="list-group-item list-group-item-action">
                            <input class="form-check-input me-2" type="radio" name="meetingDate" value="2025-10-01">
                            2025-10-01
                        </label> -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="postIrbOkBtn" data-target="post-meeting-content" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>



<script>
    const postIrbOkBtn = document.getElementById('postIrbOkBtn');
    postIrbOkBtn.addEventListener('click', () => {
        const target = postIrbOkBtn.getAttribute('data-target');
        const menuSystem = new MenuSystem();
        menuSystem.showContent(target);

         const selectedDateInput = document.querySelector('input[name="meetingDate"]:checked');
        if (!selectedDateInput) {
            alert("Please select a meeting date.");
            return;
        }

        const newMeetingDate = selectedDateInput.value;

         loadAgendaMeetings(newMeetingDate);
    });

    function loadAgendaMeetings(meeting_date) {
    fetch("/admin/handlers/fetch_agenda_meetings.php?meeting_date=" + meeting_date)
        .then(res => res.json())
        .then(data => {

            if (data.error) {
                alert("Error: " + data.error);
                return;
            }

            let rowsHTML = "";

            data.forEach(item => {
                rowsHTML += `
                    <tr class="meeting-row" data-id="${item.id}">
                        <td>${item.id}</td>
                        <td>${item.irb_number}</td>
                        <td>${item.internal_number}</td>
                        <td>${item.agenda_group}</td>
                        <td>-</td>
                        <td>${item.pi}</td>
                        <td>${item.title}</td>
                        <td>${item.reference_number}</td>
                    </tr>
                `;
            });

            document.getElementById("postMeetingRow").innerHTML = rowsHTML;

        })
        .catch(err => {
            alert("Request Failed");
            console.log(err);
        });
}

</script>