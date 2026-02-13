<?php

$conditions = getConditions();
$study_id = isset($_GET['study_id']) ? (int) $_GET['study_id'] : null;

$irb_actions = getIRBActions();
?>

<!-- Post-Meeting Content -->
<div class="content-wrapper">
    <!-- Page Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="page-header-card">
                        <div class="d-flex align-items-center">
                            <div class="header-icon-wrapper">
                                <i class="fas fa-gavel"></i>
                            </div>
                            <div class="header-content">
                                <h4 class="page-title">Post-Meeting Actions</h4>
                                <p class="page-subtitle">Record IRB decisions for agenda items</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="container-fluid">
            <!-- Quick Actions -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="premium-card flex-grow-1">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <button id="recordBtn" class="btn btn-outline-primary me-2" onclick="showHideRecordtable()">
                                    <i class="fas fa-file-alt me-1"></i> Hide Record Table
                                </button>
                                <button id="studyBtn" class="btn btn-outline-primary me-2" onclick="showHideStudyDetials()">
                                    <i class="fas fa-question-circle me-1"></i> Hide Study Details
                                </button>
                            </div>
                            <div>
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

            <!-- Table Section -->
            <div id="tableContent" class="row mb-4">
                <div class="col-12">
                    <div class="premium-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>
                                Agenda Items
                            </h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="table-responsive">
                                <table class="table table-hover table-premium">
                                    <thead class="table-primary">
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
            </div>

            <div id="studyContent"></div>

            <!-- Agenda Information -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="premium-card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-clipboard-check me-2"></i>
                                Reason Study is On Agenda
                            </h6>
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
                    <div class="premium-card h-100">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-tasks me-2"></i>
                                Action Conditions
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Action Taken</label>
                                <select name="action_taken" id="actionTakenSelect" class="form-select">
                                    <option disabled selected value="">Select One</option>
                                    <?php foreach ($irb_actions as $action): ?>
                                        <option value="<?php echo htmlspecialchars($action ?? '') ?>">
                                            <?php echo htmlspecialchars($action ?? '') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Conditions 1</label>
                                <select name="condition_1" class="form-select" placeholder="Select condition...">
                                    <option value="">Select Condition</option>
                                    <?php foreach ($conditions as $condition): ?>
                                        <option value="<?php echo htmlspecialchars($condition ?? ''); ?>">
                                            <?php echo htmlspecialchars($condition ?? ''); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Conditions 2</label>
                                <select name="condition_2" class="form-select" placeholder="Select condition...">
                                    <option value="">Select Condition</option>
                                    <?php foreach ($conditions as $condition): ?>
                                        <option value="<?php echo htmlspecialchars($condition ?? ''); ?>">
                                            <?php echo htmlspecialchars($condition ?? ''); ?>
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
                    <div class="premium-card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Agenda Info
                            </h6>
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
                    <div class="premium-card h-100">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-comment-alt me-2"></i>
                                Action Explanation
                            </h6>
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
</div>

<?php include 'admin/includes/loading_overlay.php' ?>

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

            console.log("Selected Agenda ID: " + agendaId);
            updateCorrespondenceLink();

            // Fetch and populate agenda item details
            fetch('/admin/handlers/fetch_agenda_details.php?id=' + agendaId, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
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
    // Current selected loader
    let currentLoader = 'spinner';

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

        showLoadingOverlay();

        fetch('/admin/handlers/update_agenda_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
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
                    showToast('success', 'Agenda item updated successfully.');
                } else {
                    showToast('error', 'Error updating agenda item: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('error', 'An error occurred while updating the agenda item.');
            })
            .finally(() => {
                // Hide loading spinner
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save';
            });
    }

    // Function to show loading overlay
    function showLoadingOverlay() {
        // Scroll to top smoothly
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });

        // Hide all loader contents
        document.querySelectorAll('.loader-content').forEach(content => {
            content.style.display = 'none';
        });

        // Show selected loader
        const loaderElement = document.getElementById(`${currentLoader}Loader`);
        if (loaderElement) {
            loaderElement.style.display = 'block';
        }

        // Update loading text with user's name
        const loadingText = document.querySelector('.loading-text');
        if (loadingText) {
            loadingText.textContent = `Processing agenda request`;
        }

        // Show overlay with animation
        const overlay = document.getElementById('loadingOverlay');
        overlay.classList.add('active');

        // Disable body scroll
        document.body.style.overflow = 'hidden';

        // Simulate processing (3 seconds)
        setTimeout(() => {
            hideLoadingOverlay();

            // Show success message
            setTimeout(() => {
                showToast('success', data.message);
                // Reset form
                // document.getElementById('studyForm').reset();
            }, 300);
        }, 3000);
    }

    // Function to hide loading overlay
    function hideLoadingOverlay() {
        const overlay = document.getElementById('loadingOverlay');
        overlay.classList.remove('active');

        // Re-enable body scroll
        document.body.style.overflow = 'auto';

        // Fade out animation
        setTimeout(() => {
            overlay.style.display = 'none';
        }, 300);
    }

    // Function to show loading programmatically (for other uses)
    window.showLoading = function(loaderType = 'spinner', duration = 3000, message = 'Processing...') {
        if (loaderType) currentLoader = loaderType;

        // Update message if provided
        const loadingText = document.querySelector('.loading-text');
        if (loadingText && message) {
            loadingText.textContent = message;
        }

        showLoadingOverlay({
            firstName: 'User'
        });

        // Auto-hide after duration if provided
        if (duration) {
            setTimeout(hideLoadingOverlay, duration);
        }
    };

    // Function to hide loading programmatically
    window.hideLoading = hideLoadingOverlay;
</script>