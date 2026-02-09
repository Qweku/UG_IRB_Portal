<?php

$agendaRecords = getAgendaRecords();
$meeting_date = isset($_GET['meeting_date']) ? trim($_GET['meeting_date']) : (is_array($agendaRecords) && !empty($agendaRecords) && isset($agendaRecords[0]['meeting_date']) ? $agendaRecords[0]['meeting_date'] : null);
$agenda_type = isset($_GET['type']) ? trim($_GET['type']) : null;
$filteredMeeting = [];

// Pagination parameters
$limit = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Get total count of agenda records
$db = new Database();
$conn = $db->connect();
$total_records = 0;
if ($conn) {
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM agenda_records");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_records = $result['count'] ?? 0;
    } catch (PDOException $e) {
        error_log("Error counting agenda records: " . $e->getMessage());
    }
}
$total_pages = ceil($total_records / $limit);

// Fetch agenda records with pagination
$agendaRecords = getAgendaRecords($limit, $offset);

// Filter meetings by date
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

<!-- Modern Agenda Records Content -->
<div class="content-wrapper">
    <!-- Page Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="page-header-card">
                        <div class="d-flex align-items-center">
                            <div class="header-icon-wrapper">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="header-content">
                                <h4 class="page-title">IRB Meeting Schedule</h4>
                                <p class="page-subtitle"><?php if ($meeting_date) echo htmlspecialchars($meeting_date ?? ''); ?> - Review and manage meeting submissions and documents</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="container-fluid">
            <style>
                .selected {
                    background-color: #e3f2fd !important;
                }
            </style>
            <div class="modern-agenda-records">
                <!-- Meeting Info Table -->
                <div class="premium-card mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-premium" role="table" aria-label="Meeting Information">
                                <thead class="table-primary">
                                    <tr>
                                        <th scope="col">IRB Code</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Agenda Heading</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($agendaRecords)): ?>
                                        <?php foreach ($agendaRecords as $agenda): ?>
                                            <tr<?php if (isset($agenda['meeting_date']) && $agenda['meeting_date'] == $meeting_date) echo ' class="selected"'; ?>>
                                                <td><?php echo htmlspecialchars($agenda['irb_code'] ?? '') ?></td>
                                                <td><?php echo htmlspecialchars($agenda['meeting_date'] ?? '') ?></td>
                                                <td><?php echo htmlspecialchars($agenda['agenda_heading'] ?? '') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4">
                                                    <i class="fas fa-folder-open fa-3x text-muted mb-3 d-block"></i>
                                                    No agenda added yet
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination-wrapper mt-3">
                                <nav aria-label="Agenda records pagination">
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
                                        (<?php echo $total_records; ?> total agenda records)
                                    </span>
                                </div>
                            </div>
                        <?php elseif ($total_records > 0): ?>
                            <div class="text-center mt-3">
                                <span class="text-muted small">
                                    Showing all <?php echo $total_records; ?> agenda records
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Submissions Section -->
                <?php if (!empty($agendaRecords)): ?>
                    <div class="premium-card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-file-medical me-2"></i>
                                    Submissions for Selected Meeting
                                </h5>
                                <small><?php echo count($filteredMeeting); ?> submissions requiring review</small>
                            </div>
                            <div class="header-badges">
                                <span class="badge bg-primary">Expedited: 3</span>
                                <span class="badge bg-secondary">CPA: 3</span>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="table-responsive">
                                <table class="table table-hover table-premium mb-0">
                                    <thead class="table-primary">
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
                                                <tr class="studyRow" data-study-id="<?php echo htmlspecialchars($agendaItem['study_id'] ?? '') ?>">
                                                    <td><?php echo htmlspecialchars($agendaItem['agenda_group'] ?? '') ?></td>
                                                    <td><?php echo htmlspecialchars($agendaItem['study_number'] ?? '') ?></td>
                                                    <td><?php echo htmlspecialchars($agendaItem['agenda_type'] ?? '') ?></td>
                                                    <td><?php echo htmlspecialchars($agendaItem['pi'] ?? '') ?></td>
                                                    <td><?php echo htmlspecialchars($agendaItem['title'] ?? '') ?></td>
                                                    <td><?php echo htmlspecialchars($agendaItem['reference_number'] ?? '') ?></td>
                                                    <td><?php echo htmlspecialchars($agendaItem['c_ref_num'] ?? '') ?></td>
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
                                                <td colspan="8" class="text-center py-4">
                                                    <i class="fas fa-folder-open fa-3x text-muted mb-3 d-block"></i>
                                                    No submissions found for the selected meeting.
                                                </td>
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
                        <div class="card documents-card premium-card">
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
                        </div>
                    </div>
                </div>

            </div>
        </div>
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
