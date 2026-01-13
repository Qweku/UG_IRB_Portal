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


<!-- <script>
document.addEventListener('DOMContentLoaded', () => {

    const sidebarLinks = document.querySelectorAll('#sidebar a[data-target]');
    const contentSections = document.querySelectorAll('.content-section');
    const sidebar = document.getElementById('sidebar');

    /**
     * Hide all content sections
     */
    function hideAllSections() {
        contentSections.forEach(section => {
            section.style.display = 'none';
        });
    }

    /**
     * Remove active class from all sidebar links
     */
    function clearActiveLinks() {
        sidebarLinks.forEach(link => link.classList.remove('active'));
    }

    /**
     * Show selected section
     */
    function showSection(sectionId) {
        const section = document.getElementById(sectionId);
        if (!section) return;

        hideAllSections();
        section.style.display = 'block';
    }

    /**
     * Handle sidebar navigation click
     */
    sidebarLinks.forEach(link => {
        link.addEventListener('click', event => {

            const target = link.dataset.target;

            // Allow Bootstrap modal triggers to behave normally
            if (link.hasAttribute('data-bs-toggle')) {
                return;
            }

            event.preventDefault();
            if (!target) return;

            clearActiveLinks();
            link.classList.add('active');
            showSection(target);

            // Auto-close sidebar on mobile
            if (window.innerWidth < 768 && sidebar?.classList.contains('show')) {
                const collapse = bootstrap.Collapse.getOrCreateInstance(sidebar, { toggle: false });
                collapse.hide();
            }
        });
    });

    /**
     * Initial page state
     */
    const defaultLink = document.querySelector('#sidebar a.active[data-target]');
    if (defaultLink) {
        showSection(defaultLink.dataset.target);
    }
});
</script> -->


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
        const studyIdInput = document.querySelector('input[name="study_id"]');
        const agendaItemInput = document.querySelector('input[name="agenda_id"]');
        studyIdInput.value = ""; // Clear previous value
        agendaItemInput.value = "";
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
                    <tr class="meeting-row" data-id="${item.id}" data-study-id="${item.study_id}">
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

                const rows = document.querySelectorAll(".meeting-row");

                // Attach click event to each row
                rows.forEach(row => {
                    row.addEventListener("click", function() {

                        const id = this.dataset.id;
                        const studyID = this.dataset.studyId;
                        studyIdInput.value = studyID; 
                        agendaItemInput.value = id;

                        // document.querySelector('select[name="action_explanation"]').value = dataset.action_taken ?? "";
                        // document.querySelector('select[name="condition_1"]').value = dataset.condition_1 ?? "";
                        // document.querySelector('select[name="condition_2"]').value = dataset.condition_2;
                        // document.querySelector('textarea[name="action_explanation"]').value = dataset.action_explanation ?? "";


                        console.log("Selected Study ID:", studyID);

                        // Remove active class from all rows
                        const allRows = document.querySelectorAll(".meeting-row");
                        allRows.forEach(r => r.classList.remove("active"));

                        // Add active class to clicked row
                        this.classList.add("active");

                        loadAgendaDetails(id);
                    });
                });

                if (rows.length > 0) {
                    const firstRow = rows[0];
                    firstRow.classList.add("active"); // highlight
                    const firstId = firstRow.dataset.id;

                    loadAgendaDetails(firstId); // load first item
                }

            })
            .catch(err => {
                alert("Request Failed");
                console.log(err);
            });
    }


    function formatReviewType(str) {
        return str.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    // Function that loads the details (so we don't repeat code)
    function loadAgendaDetails(id) {
        fetch("/admin/handlers/fetch_agenda_details.php?id=" + id)
            .then(res => res.json())
            .then(data => {

                if (data.error) {
                    alert("Error: " + data.error);
                    return;
                }


                document.getElementById("studyContent").innerHTML = `
                <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold">Study Information</h6>
                        </div>
                        <div class="card-body">
                           
                                <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong class="text-muted d-block">PI</strong>
                                    <span>${data.pi}</span>
                                </div>
                                <div class="col-md-6">
                                    <strong class="text-muted d-block">Source Number</strong>
                                    <span>${data.irb_number}</span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong class="text-muted d-block">Agenda Group</strong>
                                    <span>${data.agenda_group}</span>
                                </div>
                                <div class="col-md-6">
                                    <strong class="text-muted d-block">RefNum</strong>
                                    <span>${data.reference_number}</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <strong class="text-muted d-block">Protocol Title</strong>
                                    <p class="mb-0">${data.title}</p>
                                </div>
                            </div>
                           
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold">Meeting Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong class="text-muted d-block">Meeting Date</strong>
                                <span>${data.meeting_date}</span>
                            </div>
                            
                            <div>
                                <strong class="text-muted d-block">Internal Number</strong>
                                <span>${data.internal_number}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Protocol Details -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold">Protocol Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <strong class="text-muted d-block">Principal Investigator</strong>
                                    <span>${data.pi}</span>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <strong class="text-muted d-block">Review Cycle</strong>
                                    <span>${data.renewal_cycle}</span>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <strong class="text-muted d-block">Exp. Date</strong>
                                    <span>${data.expiration_date}</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <strong class="text-muted d-block">Date Received</strong>
                                    <span>${data.date_received}</span>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <strong class="text-muted d-block">First IRB Review</strong>
                                    <span>${data.first_irb_review}</span>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <strong class="text-muted d-block">Original Approval</strong>
                                    <span>${data.approval_date}</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <strong class="text-muted d-block">Last Review by IRB</strong>
                                    <span>${data.last_irb_review}</span>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <strong class="text-muted d-block">Last IRB Renewal</strong>
                                    <span>${data.last_renewal_date}</span>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <strong class="text-muted d-block">Study Status</strong>
                                    <span class="badge bg-success">${data.study_status}</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <strong class="text-muted d-block">Type</strong>
                                    <span class="badge bg-primary">${formatReviewType(data.review_type)}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            `;

            })
            .catch(err => {
                alert("Request Failed");
                console.log(err);
            });
    }
</script>