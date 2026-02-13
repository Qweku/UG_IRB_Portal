<?php
$meetingDates = getMeetingDates();
$agendaCategoriesList = getAgendaCategoriesList();

// Pagination parameters
$limit = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Get total count of agenda items
$db = new Database();
$conn = $db->connect();
$total_records = 0;
if ($conn) {
    try {
        $institutionId = get_user_institution_id();
        if ($institutionId) {
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM agenda_items WHERE institution_id = ?");
            $stmt->execute([$institutionId]);
        } else {
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM agenda_items");
            $stmt->execute();
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_records = $result['count'] ?? 0;
    } catch (PDOException $e) {
        error_log("Error counting agenda items: " . $e->getMessage());
    }
}
$total_pages = ceil($total_records / $limit);

// Fetch meetings with pagination
$meetings = getMeetings($limit, $offset);

// Build query string for pagination links (preserve filter params)
function buildQueryString($exclude = [])
{
    $params = $_GET;
    foreach ($exclude as $key) {
        unset($params[$key]);
    }
    return http_build_query($params);
}
?>
<!-- Main Content -->
<div class="content-wrapper">
    <!-- Page Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="page-header-card">
                        <div class="d-flex align-items-center">
                            <div class="header-icon-wrapper">
                                <i class="fas fa-list-ol"></i>
                            </div>
                            <div class="header-content">
                                <h4 class="page-title">Preliminary Agenda Items</h4>
                                <p class="page-subtitle">Manage and organize preliminary agenda items for IRB meetings</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="container-fluid">
            <!-- Actions Bar -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <button class="btn btn-primary me-2" onclick="previewAllAgendaItems()">
                        <i class="fas fa-eye me-2"></i>Preview All Agenda Items
                    </button>
                    <a href="/agenda/prepare-agenda" class="btn btn-outline-primary">
                        <i class="fas fa-file-export me-2"></i>Prepare Agenda
                    </a>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <form method="GET" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Meeting Date</label>
                            <select id="meetingFilter" class="form-select" name="meeting_date">
                                <?php foreach ($meetingDates as $mDate): ?>
                                    <option value="<?= htmlspecialchars($mDate) ?>"><?= htmlspecialchars($mDate) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="keepItemsTogether" name="keep_together" value="1" checked>
                                <label class="form-check-label fw-semibold" for="keepItemsTogether">
                                    Keep all items for a Study Together
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <button type="button" class="btn btn-primary" onclick="filterAgendaByMeetingDate()">
                                <i class="fas fa-filter me-2"></i>Apply Filters
                            </button>
                            <button type="button" class="btn btn-outline-secondary ms-2" onclick="previewSummaryReport()">
                                <i class="fas fa-eye me-2"></i>Preview Summary Report
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Agenda Items Table -->
            <div class="premium-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Agenda Items Directory
                        </h5>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <button class="btn btn-outline-light" data-bs-target="#assignMeetingModal" data-bs-toggle="modal">
                            <i class="fas fa-copy me-2"></i>Assign Selected Study(s) to Another Meeting
                        </button>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="agendaTable" class="table table-hover table-premium">
                            <thead>
                                <tr>
                                    <th>Select</th>
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
                                    <th>Recorder ID</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (empty($meetings)) {
                                    // Fallback to static data
                                    echo '<tr>
                                        <td><input type="checkbox" class="agenda-checkbox" onclick="event.stopPropagation();"></td>
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
                                        <td><div style="width:250px;">Assessing the</div></td>
                                        <td><div style="width:200px;">Dr. John Smith</div></td>
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
                                        $position = $offset + $index;
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
                                        echo '<tr id="agendaRow" data-id="' . $meeting['id'] . '" onclick="toggleRowSelection(this)" style="cursor: pointer;">
                                            <td><input type="checkbox" class="agenda-checkbox" value="' . $meeting['id'] . '" onclick="event.stopPropagation();"></td>
                                            <td>' . $position . '</td>
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
                                            <td><div style="width:250px;">' . htmlspecialchars($meeting['title'] ?? '') . '</div></td>
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

                        <!-- Empty State -->
                        <?php if (empty($meetings)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-folder-open fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted">No agenda items found.</p>
                            </div>
                        <?php endif; ?>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination-wrapper mt-3">
                                <nav aria-label="Agenda items pagination">
                                    <ul class="pagination justify-content-center mb-0">
                                        <?php
                                        $queryString = buildQueryString(['page']);
                                        $currentUrl = strtok($_SERVER['REQUEST_URI'], '?');
                                        $separator = empty($queryString) ? '?' : '?' . $queryString . '&';
                                        ?>
                                        <!-- First Page -->
                                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="<?php echo $currentUrl . $separator . 'page=1'; ?>" aria-label="First">
                                                <i class="fas fa-angle-double-left"></i>
                                            </a>
                                        </li>
                                        <!-- Previous Page -->
                                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="<?php echo $currentUrl . $separator . 'page=' . max(1, $page - 1); ?>" aria-label="Previous">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>

                                        <?php
                                        // Show limited page numbers around current page
                                        $startPage = max(1, min($page - 2, $total_pages - 4));
                                        $endPage = min($total_pages, max(5, $page + 2));

                                        if ($startPage > 1):
                                        ?>
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        <?php endif; ?>

                                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="<?php echo $currentUrl . $separator . 'page=' . $i; ?>">
                                                    <?php echo $i; ?>
                                                    <?php if ($i == $page): ?>
                                                        <span class="visually-hidden">(current)</span>
                                                    <?php endif; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($endPage < $total_pages): ?>
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        <?php endif; ?>

                                        <!-- Next Page -->
                                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="<?php echo $currentUrl . $separator . 'page=' . min($total_pages, $page + 1); ?>" aria-label="Next">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                        <!-- Last Page -->
                                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="<?php echo $currentUrl . $separator . 'page=' . $total_pages; ?>" aria-label="Last">
                                                <i class="fas fa-angle-double-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                                <div class="text-center mt-2">
                                    <span class="text-muted small">
                                        Showing page <?php echo $page; ?> of <?php echo $total_pages; ?>
                                        (<?php echo $total_records; ?> total agenda items)
                                    </span>
                                </div>
                            </div>
                        <?php elseif ($total_records > 0): ?>
                            <div class="text-center mt-3">
                                <span class="text-muted small">
                                    Showing all <?php echo $total_records; ?> agenda items
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary" data-bs-target="#assignMeetingModal" data-bs-toggle="modal">
                                <i class="fas fa-copy me-2"></i>Assign Selected Study(s) to Another Meeting
                            </button>
                            <button class="btn btn-outline-primary" data-bs-target="#assignMeetingModal" data-bs-toggle="modal">
                                <i class="fas fa-calendar-plus me-2"></i>Assign All To New Meeting
                            </button>
                            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
                                <i class="fas fa-trash me-2"></i>Delete Selected Agenda Item(s)
                            </button>
                        </div>
                        <div class="input-group" style="width: 300px;">
                            <input type="text" id="agendaSearch" class="form-control" placeholder="Search agenda items...">
                            <button class="btn btn-primary" onclick="searchAgendaItems()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Meeting Modal -->
<div class="modal fade" id="assignMeetingModal" tabindex="-1" aria-labelledby="assignMeetingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title" id="assignMeetingModalLabel">
                    <i class="fas fa-calendar-alt me-2"></i>Select a Meeting Date
                </h5>
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
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmDeleteModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to delete this agenda item(s)? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash me-2"></i>Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Toggle row selection
    function toggleRowSelection(row) {
        const checkbox = row.querySelector('.agenda-checkbox');
        if (checkbox && event.target.type !== 'checkbox') {
            checkbox.checked = !checkbox.checked;
            row.classList.toggle('table-active');
        }
    }

    // Filter agenda by meeting date
    function filterAgendaByMeetingDate() {
        const selectedDate = document.getElementById("meetingFilter").value.trim();
        const rows = document.querySelectorAll("#agendaTable tbody tr");

        rows.forEach(row => {
            const meetingDateCell = row.querySelector(".meeting-date");
            if (meetingDateCell) {
                const meetingDateText = meetingDateCell.innerText.trim();
                if (meetingDateText === selectedDate || selectedDate === "") {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            }
        });
    }

    // Search agenda items
    function searchAgendaItems() {
        const searchTerm = document.getElementById("agendaSearch").value.toLowerCase();
        const rows = document.querySelectorAll("#agendaTable tbody tr");

        rows.forEach(row => {
            let found = false;
            const cells = row.querySelectorAll("td");
            cells.forEach(cell => {
                if (cell.innerText.toLowerCase().includes(searchTerm)) {
                    found = true;
                }
            });
            row.style.display = found ? "" : "none";
        });
    }

    // Preview all agenda items
    function previewAllAgendaItems() {
        // Implementation for preview all agenda items
        window.location.href = '/agenda/preview-all';
    }

    // Preview summary report
    function previewSummaryReport() {
        // Implementation for preview summary report
        window.location.href = '/agenda/summary-report';
    }

    // Show toast notification
    function showToast(type, message) {
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '1050';
            document.body.appendChild(toastContainer);
        }

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

        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }

    // Handle assign button click
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
        const modal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'));
        modal.hide();

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

    // Initialize search on Enter key
    document.getElementById('agendaSearch').addEventListener('keyup', function(event) {
        if (event.key === 'Enter') {
            searchAgendaItems();
        }
    });

    // Run on page load
    window.addEventListener('DOMContentLoaded', function() {
        filterAgendaByMeetingDate();
    });
</script>
