<?php


// session_start();
// if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
//     header('Location: /login');
//     exit;
// }

$meetingDates = getMeetingDates();
$meeting_date = $_GET['meeting_date'] ?? null;

?>


<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3">
            <?php include 'admin/pages/contents/post_meeting_content.php'; ?>
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
    // const postIrbOkBtn = document.getElementById('postIrbOkBtn');
    // postIrbOkBtn.addEventListener('click', () => {
    //     const selectedDateInput = document.querySelector('input[name="meetingDate"]:checked');
    //     if (!selectedDateInput) {
    //         alert("Please select a meeting date.");
    //         return;
    //     }

    //     const newMeetingDate = selectedDateInput.value;

    //     // Redirect to this page with the date
    //     window.location.href = '/dashboard/post-irb-meeting?meeting_date=' + encodeURIComponent(newMeetingDate);
    // });

    // If meeting_date is provided, load the agenda
    const urlParams = new URLSearchParams(window.location.search);
    const meetingDate = urlParams.get('meeting_date');
    if (meetingDate) {
        loadAgendaMeetings(meetingDate);
    }

    function loadAgendaMeetings(meeting_date) {
        const studyIdInput = document.querySelector('input[name="study_id"]');
        const agendaItemInput = document.querySelector('input[name="agenda_id"]');
        studyIdInput.value = ""; // Clear previous value
        agendaItemInput.value = "";
        console.log("Loading agenda for date:", meeting_date);
        fetch("/admin/handlers/fetch_agenda_meetings.php?meeting_date=" + encodeURIComponent(meeting_date), {
            credentials: 'same-origin'
        })
            .then(res => {
                console.log("Response status:", res.status);
                if (!res.ok) {
                    return res.text().then(text => {
                        console.log("Error response:", text);
                        throw new Error('HTTP ' + res.status + ': ' + text);
                    });
                }
                return res.json();
            })
            .then(response => {
                console.log("Response:", response);

                // Handle both array response and {status, data} response
                let data = response;
                if (response.status === 'success' && response.data) {
                    data = response.data;
                } else if (response.error) {
                    alert("Error: " + response.error);
                    return;
                }

                if (!Array.isArray(data)) {
                    console.error("Expected array but got:", data);
                    alert("Unexpected response format");
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
                alert("Request Failed: " + err.message);
                console.error("Fetch error:", err);
            });
    }


    function formatReviewType(str) {
        return str.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    // Function that loads the details (so we don't repeat code)
    function loadAgendaDetails(id) {
        fetch("/admin/handlers/fetch_agenda_details.php?id=" + id, {
            credentials: 'same-origin'
        })
            .then(res => {
                if (!res.ok) {
                    return res.text().then(text => {
                        throw new Error('HTTP ' + res.status + ': ' + text);
                    });
                }
                return res.json();
            })
            .then(response => {
                // Handle both direct response and {status, data} response
                let data = response;
                if (response.status === 'success' && response.data) {
                    data = response.data;
                } else if (response.error) {
                    alert("Error: " + response.error);
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
                alert("Request Failed: " + err.message);
                console.error("Fetch error:", err);
            });
    }
</script>