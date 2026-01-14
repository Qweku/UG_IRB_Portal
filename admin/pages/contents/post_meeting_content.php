<?php


$conditions = getConditions();
$study_id = isset($_GET['study_id']) ? (int) $_GET['study_id'] : null;

$irb_actions = getIRBActions();
?>

<!-- Agenda Details Content -->
<div class="agenda-details">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Agenda Details</h2>
        <div>
            <!-- Quick Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <!-- <div>
                                <h6 class="mb-0 fw-bold">Quick Actions</h6>
                            </div> -->
                                <div>
                                    <button id="recordBtn" class="btn btn-outline-primary me-2" onclick="showHideRecordtable()">
                                        <i class="fas fa-file-alt me-1"></i> Hide Record Table
                                    </button>
                                    <button id="studyBtn" class="btn btn-outline-primary me-2" onclick="showHideStudyDetials()">
                                        <i class="fas fa-question-circle me-1"></i> Hide Study Details
                                    </button>
                                    <a href="/agenda/minutes" class="btn btn-outline-primary me-2">
                                        <i class="fas fa-clipboard me-1"></i> Meeting Minutes
                                    </a>
                                    <a href="/generate-letter" id="sendCorrespondenceLink" class="btn btn-outline-primary me-2">
                                        <i class="fas fa-envelope me-1"></i> Send Correspondence
                                    </a>
                                    <button id="saveBtn" class="btn btn-success me-2" onclick="saveAgendaItem()">
                                        <i class="fas fa-save me-1"></i> Save
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Table Section -->
        <div id="tableContent" class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-hover agenda-table">
                            <thead>
                                <tr>
                                    <th>Item #</th>
                                    <th>IRB#</th>
                                    <th>Source Number</th>
                                    <th>Agenda Group</th>
                                    <th>Action Taken</th>
                                    <th>PI</th>
                                    <th>Title</th>
                                    <th>RefNumber</th>
                                </tr>
                            </thead>
                            <tbody id="postMeetingRow">
                                <input type="text" name="study_id" id="studyIdInput" value="<?php echo $study_id; ?>" hidden>
                                <input type="text" name="agenda_id" id="agendaItemIdInput" value="" hidden>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div id="studyContent">


        </div>

        <!-- Agenda Information -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">Reason Study is On Agenda</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Agenda Category</label>
                                <select class="form-select" disabled>
                                    <option>Expedited</option>
                                    <option>Full Board</option>
                                    <option>Continuing Review</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">IRB Meeting Action</label>
                                <select class="form-select" disabled>
                                    <option>Select One</option>
                                    <option>Approved</option>
                                    <option>Modifications Required</option>
                                    <option>Deferred</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Reason 1</label>
                                <select class="form-select" disabled>
                                    <option>Procedure</option>
                                    <option>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Reason 2</label>
                                <select class="form-select" disabled>
                                    <option>Other</option>
                                    <option>Procedure</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">Action Conditions</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Action Taken</label>
                            <select name="action_taken" id="actionTakenSelect" class="form-select">
                                <option disabled selected value="">Select One</option>
                                <?php foreach($irb_actions as $action): ?>
                                <option value="<?php echo htmlspecialchars($action) ?>">
                                    <?php echo htmlspecialchars($action) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Conditions 1</label>
                            <select name="condition_1" class="form-select" placeholder="Select condition...">
                                <option value="">Select Condition</option>
                                <?php foreach ($conditions as $condition): ?>
                                    <option value="<?php echo htmlspecialchars($condition); ?>">
                                        <?php echo htmlspecialchars($condition); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Conditions 2</label>
                            <select name="condition_2" class="form-select" placeholder="Select condition...">
                                <option value="">Select Condition</option>
                                <?php foreach ($conditions as $condition): ?>
                                    <option value="<?php echo htmlspecialchars($condition); ?>">
                                        <?php echo htmlspecialchars($condition); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Info & Action Explanation -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">Agenda Info</h6>
                    </div>
                    <div class="card-body">
                        <label class="form-label fw-semibold">Additional info</label>
                        <textarea class="form-control" rows="4" readonly>
The plan assumes a parallel and logical approach on August 4th, 2023. The following resources were submitted on September 04, 2023. It has been identified as a future focus member for each year, which reflects the potential to be granted through the data analysis section (as been reviewed by class). A 1% decrease for assessing the documentation has occurred included from 2 weeks to 1 hour, 5. On the previous basis, the following comments have been addressed. The data provided include:
                        </textarea>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">Action Explanation</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Action Explanation</label>
                            <textarea type="text" name="action_explanation" class="form-control" value=""></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Discussion</label>
                            <input type="text" class="form-control" value="">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Vote</label>
                            <input type="text" class="form-control" value="">
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
</div>



<style>
    .agenda-details .card {
        border: 1px solid #e0e0e0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .agenda-details .card-header {
        background-color: #f8f9fa !important;
        border-bottom: 1px solid #e0e0e0;
    }

    .agenda-details .form-label {
        color: #495057;
        font-weight: 600;
    }

    .agenda-details .text-muted {
        font-size: 0.875rem;
    }

    .highlight {
        background-color: #e3f2fd;
    }
</style>

<script>
    function showHideRecordtable() {
        const tableContent = document.getElementById('tableContent');
        const recordButton = document.getElementById('recordBtn');
        if (tableContent.style.display === 'none') {
            tableContent.style.display = 'block';
            recordButton.innerHTML = '<i class="fas fa-file-alt me-1"></i> Hide Record Table';
        } else {
            tableContent.style.display = 'none';
            recordButton.innerHTML = '<i class="fas fa-file-alt me-1"></i> Show Record Table';
        }
    }

    function showHideStudyDetials() {
        const studyContent = document.getElementById('studyContent');
        const studyButton = document.getElementById('studyBtn');
        if (studyContent.style.display === 'none') {
            studyContent.style.display = 'block';
            studyButton.innerHTML = '<i class="fas fa-question-circle me-1"></i> Hide Study Details';
        } else {
            studyContent.style.display = 'none';
            studyButton.innerHTML = '<i class="fas fa-question-circle me-1"></i> Show Study Details';
        }
    }
</script>

<script>
    document.getElementById('postMeetingRow').addEventListener('click', function(e) {
        const clickedRow = e.target.closest('tr');
        if (clickedRow) {
            // Remove highlight from all rows
            const rows = this.querySelectorAll('tr');
            rows.forEach(row => row.classList.remove('table-primary'));
            // Add highlight to clicked row
            clickedRow.classList.add('table-primary');

            // Update study ID and correspondence link
            const studyId = clickedRow.dataset.studyId;
            document.getElementById('studyIdInput').value = studyId;
            // Update agenda item ID
            const agendaId = clickedRow.dataset.id;
            document.getElementById('agendaItemIdInput').value = agendaId;

            console.log("Selected Agenda ID: "+ agendaId);
            updateCorrespondenceLink();

            // Fetch and populate agenda item details
            fetch('/admin/handlers/fetch_agenda_details.php?id=' + agendaId)
            .then(response => response.json())
            .then(data => {
                if (data) {
                    document.getElementById('actionTakenSelect').value = data.action_taken || '';
                    document.querySelector('select[name="condition_1"]').value = data.condition_1 || '';
                    document.querySelector('select[name="condition_2"]').value = data.condition_2 || '';
                    document.querySelector('textarea[name="action_explanation"]').value = data.action_explanation || '';
                }
            })
            .catch(error => console.error('Error fetching agenda details:', error));
        }
    });
</script>

<script>
    function updateCorrespondenceLink() {
        const studyId = document.getElementById('studyIdInput').value;
        const link = document.getElementById('sendCorrespondenceLink');

        console.log("Updating correspondence link with study ID:", studyId);
        if (studyId) {
            link.href = '/generate-letter?study_id=' + encodeURIComponent(studyId);
        } else {
            link.href = '/generate-letter';
        }
    }

    function saveAgendaItem() {
        const agendaItemId = document.getElementById('agendaItemIdInput').value;
        const actionTaken = document.getElementById('actionTakenSelect').value;
        const condition1 = document.querySelector('select[name="condition_1"]').value;
        const condition2 = document.querySelector('select[name="condition_2"]').value;
        const actionExplanation = document.querySelector('textarea[name="action_explanation"]').value;
        const saveBtn = document.getElementById('saveBtn');

        if (!agendaItemId) {
            alert('Please select an agenda item to update.');
            return;
        }

        if (!actionTaken) {
            alert('Please select an action taken.');
            return;
        }

        console.log("Agenda ID: " + agendaItemId);

        // Show loading spinner
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

        fetch('/admin/handlers/update_agenda_item.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: agendaItemId,
                action_taken: actionTaken,
                condition_1: condition1,
                condition_2: condition2,
                action_explanation: actionExplanation
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Agenda item updated successfully.');
            } else {
                alert('Error updating agenda item: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the agenda item.');
        })
        .finally(() => {
            // Hide loading spinner
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save';
        });
    }

</script>