<?php
$meetingDates = getMeetingDates();
$agendaCategoriesList = getAgendaCategoriesList();
?>
<style>
    .table-active {
        background-color: #9da0c2ff !important;
    }
</style>
<!-- Preliminary Agenda Items Content -->
<div class="preliminary-agenda">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Preliminary Agenda Items</h2>
        <div>
            <button class="btn btn-primary me-2">
                <i class="fas fa-plus me-1"></i> Preview All Agenda Items
            </button>
            <a href="/prepare-agenda" class="btn btn-outline-primary">
                <i class="fas fa-file-export me-1"></i> Prepare Agenda
            </a>
        </div>
    </div>

    <!-- Meeting Dates Section -->
    <div class="filter-section mb-4">
        <div class="row">
            <div class="col-md-6">
                <h5 class="section-title mb-3">Meeting Dates</h5>
                <div class="d-flex align-items-center">
                    <select id="meetingFilter" class="form-select me-2" style="max-width: 200px;">
                        <?php foreach ($meetingDates as $mDate): ?>
                            <option value="<?= htmlspecialchars($mDate) ?>"><?= htmlspecialchars($mDate) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="keepItemsTogether" checked>
                        <label class="form-check-label small" for="keepItemsTogether">
                            Keep all items for a Study Together
                        </label>
                    </div>
                </div>
            </div>
            <div class="col-md-6 d-flex align-items-end justify-content-end">
                <button class="btn btn-outline-secondary me-2">
                    <i class="fas fa-eye me-1"></i> Preview Summary Report
                </button>

            </div>
        </div>
    </div>

    <!-- Agenda Items Table -->
    <div class="main-content">
        <h4 class="section-title">Agenda Items</h4>

        <div class="table-responsive">
            <table id="agendaTable" class="table table-hover agenda-table" style="table-layout: auto;">
                <thead>
                    <tr>
                        <th>Position</th>
                        <th>IRB #</th>
                        <th>Agenda Category</th>
                        <th>Agenda Group</th>
                        <th>Expedite</th>
                        <th>Internal Number</th>
                        <th>Agenda Explanation</th>
                        <th>Title</th>
                        <th>PI</th>
                        <th>Condition 1</th>
                        <th>Condition 2</th>
                        <th>Renewal</th>
                        <th>Review</th>
                        <th>Meeting Date</th>
                        <th>Reference #</th>
                        <th>recorder_id</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $meetings = getMeetings();
                    if (empty($meetings)) {
                        // Fallback to static data
                        echo '<tr>
                            <td>0</td>
                            <td>013/25-26</td>
                            <td><select class="form-select">
                            <option selected>Expedited</option>
                            <option>Procedure</option>
                            <option>Exempt</option>
                            <option>Renewal</option>
                            <option>Resubmission</option>
                            </select></td>
                            <td>Expedited</td>
                            <td><span class="badge bg-success">True</span></td>
                            <td>5085</td>
                            <td>The protocol was gr</td>
                            <td>Assessing the</td>
                            <td>Dr. John Smith</td>
                            <td>Approved</td>
                            <td>Pending</td>
                            <td>Yes</td>
                            <td>Initial</td>
                            <td>2025-10-01</td>
                            <td>REF-001</td>
                            <td>REC-123</td>
                        </tr>
                        ';
                    } else {
                        foreach ($meetings as $index => $meeting) {
                            $badgeClass = 'bg-info';
                            if (isset($meeting['agenda_category'])) {
                                switch ($meeting['agenda_category']) {
                                    case 'Full Board':
                                        $badgeClass = 'bg-primary';
                                        break;
                                    case 'Continuing Review':
                                        $badgeClass = 'bg-warning text-dark';
                                        break;
                                }
                            }
                            echo '<tr id="agendaRow"  data-id="' . $meeting['id'] . '">
                                <td>' . $index . '</td>
                                <td>' . htmlspecialchars($meeting['irb_number'] ?? '013/25-26') . '</td>
                                 <td><select class="form-select" style="width:200px;">';
                            foreach ($agendaCategoriesList as $category) {
                                echo '<option value="' . htmlspecialchars($category) . '" ' . ($category == htmlspecialchars($meeting["agenda_category"]) ? "selected" : "") . '>' . htmlspecialchars($category) . '</option>';
                            }
                            echo '</select></td>
                                
                                <td>' . htmlspecialchars($meeting['agenda_group'] ?? 'Expedited') . '</td>
                                <td><span class="badge ' . (($meeting['expedite'] ?? false) ? 'bg-success' : 'bg-secondary') . '">' . (($meeting['expedite'] ?? false) ? 'True' : 'False') . '</span></td>
                                <td>' . htmlspecialchars($meeting['internal_number'] ?? '5085') . '</td>
                                <td>' . htmlspecialchars($meeting['agenda_explanation'] ?? '') . '</td>
                                <td> <div style="width:250px;">' . htmlspecialchars($meeting['title'] ?? '') . '</div></td>
                                <td><div style="width:200px;">' . htmlspecialchars($meeting['pi'] ?? '') . '</div></td>
                                <td>' . htmlspecialchars($meeting['condition1'] ?? '') . '</td>
                                <td>' . htmlspecialchars($meeting['condition2'] ?? '') . '</td>
                                <td>' . htmlspecialchars($meeting['renewal'] ?? '') . '</td>
                                <td>' . htmlspecialchars($meeting['review'] ?? '') . '</td>
                                <td class="meeting-date">' . htmlspecialchars($meeting['meeting_date'] ?? '') . '</td>
                                <td>' . htmlspecialchars($meeting['reference_num'] ?? 'REF-001') . '</td>
                                <td>' . htmlspecialchars($meeting['recorder_id'] ?? 'REC-123') . '</td>
                            </tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-between mt-4">
            <div>
                <button class="btn btn-outline-primary me-2" data-bs-target="#assignMeetingModal" data-bs-toggle="modal">
                    <i class="fas fa-copy me-1"></i> Assign Selected Study(s) to Another Meeting
                </button>
                <button class="btn btn-outline-primary me-2" data-bs-target="#assignMeetingModal" data-bs-toggle="modal">
                    <i class="fas fa-calendar-plus me-1"></i> Assign All To New Meeting
                </button>
                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
                    <i class="fas fa-trash me-1"></i> Delete Selected Agenda Item(s)
                </button>
            </div>
            <div>
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search...">
                    <button class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Meeting Modal -->
<div class="modal fade" id="assignMeetingModal" tabindex="-1" aria-labelledby="assignMeetingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignMeetingModalLabel">Select a meeting Date</h5>
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
                <button type="button" class="btn btn-primary" id="assignOkBtn" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this agenda item(s)? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
    function filterAgendaByMeetingDate() {
        const selectedDate = document.getElementById("meetingFilter").value.trim();
        const rows = document.querySelectorAll("#agendaTable tbody tr");

        rows.forEach(row => {
            const meetingDateCell = row.cells[13]?.innerText.trim(); // Meeting Date column
            if (meetingDateCell === selectedDate || selectedDate === "") {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    // Run on page load
    window.addEventListener('DOMContentLoaded', filterAgendaByMeetingDate);

    // Run when dropdown changes
    document.getElementById("meetingFilter").addEventListener("change", filterAgendaByMeetingDate);

    function showToast(type, message) {
        // Create toast container if not exists
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '1050';
            document.body.appendChild(toastContainer);
        }

        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');

        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
             `;

        toastContainer.appendChild(toast);

        // Initialize and show toast
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        // Remove toast after it's hidden
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }

    // Select all rows in the table
    const rows = document.querySelectorAll("#agendaTable tbody tr");

    rows.forEach(row => {
        row.addEventListener("click", function() {
            // Toggle the highlight on this row
            this.classList.toggle("table-active");

            selectedRow = this;
        });
    });

    document.getElementById("assignOkBtn").addEventListener("click", function() {
        const selectedDateInput = document.querySelector('input[name="meetingDate"]:checked');
        if (!selectedDateInput) {
            alert("Please select a meeting date.");
            return;
        }

        const newMeetingDate = selectedDateInput.value;
        const selectedRows = document.querySelectorAll("#agendaTable tbody tr.table-active");

        if (selectedRows.length === 0) {
            alert("Please select at least one row.");
            return;
        }

        // Collect all fetch promises
        const updatePromises = Array.from(selectedRows).map(row => {
            row.querySelector(".meeting-date").textContent = newMeetingDate;
            const rowId = row.dataset.id;

            return fetch("/admin/handlers/update_agenda_meeting_date.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({
                        id: rowId,
                        meetingDate: newMeetingDate
                    }),
                })
                .then(response => response.json());
        });

        // Wait for all updates to finish
        Promise.all(updatePromises)
            .then(results => {
                const allSuccess = results.every(r => r.success);
                if (allSuccess) {
                    showToast('success', "Meeting date updated successfully");
                } else {
                    showToast('error', "Some rows failed to update");
                }
                // Reload page after updates
                window.location.reload();
            })
            .catch(error => {
                console.error(error);
                showToast('error', "Error updating meeting dates");
            });
    });

    // Handle confirmation button click
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        // Hide modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'));
        modal.hide();

        // Call deleteAgenda
       const selectedRows = document.querySelectorAll("#agendaTable tbody tr.table-active");

        if (selectedRows.length === 0) {
            alert("Please select at least one row.");
            return;
        }
        // Collect all delete promises
        const deletePromises = Array.from(selectedRows).map(row => {
            const rowId = row.dataset.id;

            return fetch("/admin/handlers/delete_agenda_item.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({
                        id: rowId
                    }),
                })
                .then(response => response.json());
        });
        // Wait for all deletions to finish
        Promise.all(deletePromises)
            .then(results => {
                const allSuccess = results.every(r => r.success);
                if (allSuccess) {
                    showToast('success', "Agenda item(s) deleted successfully");
                } else {
                    showToast('error', "Some items failed to delete");
                }
                // Reload page after deletions
                window.location.reload();
            })
            .catch(error => {
                console.error(error);
                showToast('error', "Error deleting agenda items");
            });
    });
</script>