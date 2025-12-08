<?php

$agendaRecords = getAgendaRecords();
$meeting_date = isset($_GET['meeting_date']) ? trim($_GET['meeting_date']) : (is_array($agendaRecords) && !empty($agendaRecords) && isset($agendaRecords[0]['meeting_date']) ? $agendaRecords[0]['meeting_date'] : null);
$agenda_type = isset($_GET['type']) ? trim($_GET['type']) : null;
$filteredMeeting = [];

// Filter meetings by date
$db = new Database();
$conn = $db->connect();

if ($conn) {
    $query = "SELECT a.agenda_group, a.study_id, a.pi, a.title, a.reference_number, s.protocol_number
                        AS study_number FROM agenda_items a LEFT JOIN studies s
                        ON a.reference_number = s.ref_num WHERE a.meeting_date = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$meeting_date]);
    $filteredMeeting = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $filteredMeeting = [];
}

?>

<!-- Modern Agenda Records Content -->
<style>
    .selected {
        background-color: #e3f2fd !important;
    }
</style>
<div class="modern-agenda-records">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h1 class="page-title">IRB Meeting Schedule<?php if ($meeting_date) echo ' - ' . htmlspecialchars($meeting_date); ?></h1>
                <p class="page-subtitle">Review and manage meeting submissions and documents</p>
            </div>
            <div class="header-actions">
                <!-- <button class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i> New Submission
                    </button> -->
            </div>
        </div>
    </div>

    <!-- Meeting Info Table -->
    <div class="card meeting-info-card mb-4">
        <div class="card-body">
            <table class="table table-striped table-hover" role="table" aria-label="Meeting Information">
                <thead>
                    <tr>
                        <th scope="col">IRB Code</th>
                        <th scope="col">Date</th>
                        <th scope="col">Agenda Heading</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($agendaRecords)): ?>
                        <?php foreach ($agendaRecords as $agenda): ?>
                            <tr<?php if ($agenda['meeting_date'] == $meeting_date) echo ' class="selected"'; ?>>
                                <td><?php echo htmlspecialchars($agenda['irb_code'] ?? '') ?></td>
                                <td><?php echo htmlspecialchars($agenda['meeting_date'] ?? '') ?></td>
                                <td><?php echo htmlspecialchars($agenda['agenda_heading'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    No agenda added yet
                                </td>
                            </tr>
                        <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Navigation & Filters -->
    <!-- <div class="row mb-4">
        <div class="col-md-8">
            <div class="navigation-section">
                <nav aria-label="Meeting navigation">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item">
                            <a class="page-link" href="#">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">4</a></li>
                        <li class="page-item"><a class="page-link" href="#">5</a></li>
                        <li class="page-item disabled"><a class="page-link" href="#">—</a></li>
                        <li class="page-item"><a class="page-link" href="#">148</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        <div class="col-md-4">
            <div class="filter-section">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-filter"></i>
                    </span>
                    <select class="form-select">
                        <option value="" <?php if (!$agenda_type) echo ' selected'; ?>>All Submissions</option>
                        <option value="expedited" <?php if ($agenda_type == 'expedited') echo ' selected'; ?>>Expedited Reviews</option>
                        <option value="full_board" <?php if ($agenda_type == 'full_board') echo ' selected'; ?>>Full Board Reviews</option>
                        <option value="new_protocol" <?php if ($agenda_type == 'new_protocol') echo ' selected'; ?>>New Protocols</option>
                        <option value="continuing_review" <?php if ($agenda_type == 'continuing_review') echo ' selected'; ?>>Continuing Reviews</option>
                    </select>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Row click for meeting selection
                document.querySelectorAll('.meeting-info-card tbody tr').forEach(row => {
                    row.addEventListener('click', function() {
                        const date = this.cells[1].textContent.trim();
                        // Remove previous selection
                        document.querySelectorAll('.meeting-info-card tbody tr').forEach(r => r.classList.remove('selected'));
                        // Highlight selected
                        this.classList.add('selected');
                        // Reload with meeting_date
                        const urlParams = new URLSearchParams(window.location.search);
                        urlParams.set('meeting_date', date);
                        window.location.href = '?' + urlParams.toString();
                    });
                });

                // Filter dropdown
                const select = document.querySelector('.filter-section select');
                select.addEventListener('change', function() {
                    const type = this.value;
                    const urlParams = new URLSearchParams(window.location.search);
                    if (type) {
                        urlParams.set('type', type);
                    } else {
                        urlParams.delete('type');
                    }
                    window.location.href = '?' + urlParams.toString();
                });
            });
        </script>
    </div> -->

    <!-- Submissions Section -->
    <?php if (!empty($agendaRecords)): ?>
        <div class="card main-content-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-medical me-2"></i>
                        Submissions for Selected Meeting
                    </h5>
                    <small>3 submissions requiring review</small>
                </div>
                <div class="header-badges">
                    <span class="badge bg-primary">Expedited: 3</span>
                    <span class="badge bg-secondary">CPA: 3</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="120px">Agenda Group</th>
                                <th width="100px">Study Number</th>
                                <th width="80px">Type</th>
                                <th>PI</th>
                                <th>Study Title</th>
                                <th width="120px">Reference Number</th>
                                <th width="100px">Creators RefNum</th>
                                <th width="120px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($filteredMeeting)): ?>
                                <?php foreach ($filteredMeeting as $agendaItem): ?>
                                    <tr class="studyRow" data-study-id="<?php echo htmlspecialchars($agendaItem['study_id'] ?? "") ?>">
                                        <td><?php echo htmlspecialchars($agendaItem['agenda_group'] ?? "") ?></td>
                                        <td><?php echo htmlspecialchars($agendaItem['study_number'] ?? "") ?></td>
                                        <td><?php echo htmlspecialchars($agendaItem['agenda_type'] ?? "") ?></td>
                                        <td><?php echo htmlspecialchars($agendaItem['pi'] ?? "") ?></td>
                                        <td><?php echo htmlspecialchars($agendaItem['title'] ?? "") ?></td>
                                        <td><?php echo htmlspecialchars($agendaItem['reference_number'] ?? "") ?></td>
                                        <td><?php echo htmlspecialchars($agendaItem['c_ref_num'] ?? "") ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-outline-primary" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>

                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">No submissions found for the selected meeting.</td>
                                </tr>
                            <?php endif; ?>


                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Supporting Documents Section -->

    <div id="studyDocSection" class="row" style="display:none;">
        <div class="col-md-8">
            <div class="card documents-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-pdf me-2 text-danger"></i>
                        Supporting Documents
                    </h5>
                </div>
                <div class="card-body">
                    <div class="document-item">
                        <div class="document-icon">
                            <i class="fas fa-file-pdf text-danger fa-2x"></i>
                        </div>
                        <div class="document-details">
                            <div class="document-name">12824256responsecompressed.pdf</div>
                            <div class="document-meta">
                                <span class="meta-item">
                                    <i class="fas fa-calendar me-1"></i>
                                    Uploaded: November 18, 2025
                                </span>
                                <span class="meta-item">
                                    <i class="fas fa-file me-1"></i>
                                    2.4 MB
                                </span>
                            </div>
                            <div class="document-comments">
                                <i class="fas fa-comment me-1 text-muted"></i>
                                Response document for study review
                            </div>
                        </div>
                        <div class="document-actions">
                            <button class="btn btn-sm btn-outline-primary me-1">
                                <i class="fas fa-download"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary me-1">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- <div class="card-footer">
                    <button class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-upload me-1"></i> Upload New Document
                    </button>
                </div> -->
            </div>
        </div>
        <!-- <div class="col-md-4">
            <div class="card stats-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2 text-info"></i>
                        Meeting Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-value text-primary">3</div>
                            <div class="stat-label">Total Submissions</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value text-success">1</div>
                            <div class="stat-label">Approved</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value text-warning">1</div>
                            <div class="stat-label">Pending</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value text-info">1</div>
                            <div class="stat-label">Under Review</div>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: 33%"></div>
                        <div class="progress-bar bg-warning" style="width: 33%"></div>
                        <div class="progress-bar bg-info" style="width: 34%"></div>
                    </div>
                </div>
            </div>
        </div> -->
    </div>

</div>

<script>
    document.querySelectorAll('.studyRow').forEach(row => {

        row.addEventListener('click', function() {
            const studyId = this.getAttribute('data-study-id');

            // Remove highlight from all rows
            document.querySelectorAll('.studyRow').forEach(r => {
                r.classList.remove('table-primary');
            });

            // Highlight the clicked row
            this.classList.add('table-primary');

            if (!studyId) {
                console.error('Invalid study ID');
                return;
            }

            // Fetch study documents
            fetch(`/admin/handlers/get_study_documents.php?study_id=${studyId}`)
                .then(response => response.json())
                .then(data => {
                    const documentsCard = document.querySelector('.documents-card .card-body');
                    documentsCard.innerHTML = '';

                    if (data.length === 0) {
                        document.getElementById('studyDocSection').style.display = 'block';
                        documentsCard.innerHTML = '<p class="text-muted">No documents found for this study.</p>';
                    } else {
                        data.forEach(doc => {

                            const docItem = document.createElement('div');
                            docItem.classList.add('document-item');

                            if(!doc.file_name || doc.file_name.trim() === '' || doc.file_name === null) {
                                document.getElementById('studyDocSection').style.display = 'block';
                                docItem.innerHTML = '<p class="text-muted">No documents found for this study.</p>';
                                documentsCard.appendChild(docItem);
                                return;
                            }

                            docItem.innerHTML = `
                        <div class="document-icon">
                            <i class="fas fa-file-pdf text-danger fa-2x"></i>
                        </div>
                        <div class="document-details">
                            <div class="document-name">${doc.file_name}</div>
                            <div class="document-meta">
                                <span class="meta-item">
                                    <i class="fas fa-calendar me-1"></i>
                                    Uploaded: ${doc.uploaded_at}
                                </span>
                                <span class="meta-item">
                                    <i class="fas fa-file me-1"></i>
                                    ${doc.document_type}
                                </span>
                            </div>
                            <div class="document-comments">
                                <i class="fas fa-comment me-1 text-muted"></i>
                                ${doc.comments}
                            </div>
                        </div>
                        <div class="document-actions">
                            <button class="btn btn-sm btn-outline-primary me-1">
                                <i class="fas fa-download"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary me-1">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                            documentsCard.appendChild(docItem);
                            document.getElementById('studyDocSection').style.display = 'block';
                        });
                    }
                })
                .catch(error => console.error('Error fetching documents:', error));
        });

    });
</script>