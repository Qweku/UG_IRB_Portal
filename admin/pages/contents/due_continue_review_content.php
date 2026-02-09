<?php
// Pagination parameters
$limit = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Get total count of continue review studies
$db = new Database();
$conn = $db->connect();
$total_records = 0;
if ($conn) {
    try {
        $institutionId = get_user_institution_id();
        if ($institutionId) {
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM studies WHERE institution_id = ? AND expiration_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
            $stmt->execute([$institutionId]);
        } else {
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM studies WHERE expiration_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
            $stmt->execute();
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_records = $result['count'] ?? 0;
    } catch (PDOException $e) {
        error_log("Error counting continue review studies: " . $e->getMessage());
    }
}
$total_pages = ceil($total_records / $limit);

// Fetch continue review studies with pagination
$studies = getContinueReviewStudies($limit, $offset);

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
<!-- Due for Continuing Review Items Content -->
<div class="content-wrapper">
    <!-- Page Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="page-header-card">
                        <div class="d-flex align-items-center">
                            <div class="header-icon-wrapper">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <div class="header-content">
                                <h4 class="page-title">Studies Due for Continue Review</h4>
                                <p class="page-subtitle">Manage and track studies requiring continuing review</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="container-fluid">
            <!-- Page Header Actions -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <!-- <button class="btn btn-primary me-2">
                        <i class="fas fa-plus me-1"></i> Preview All Agenda Items
                    </button>
                    <a href="/prepare-agenda" class="btn btn-outline-primary">
                        <i class="fas fa-file-export me-1"></i> Prepare Agenda
                    </a> -->
                </div>
            </div>

            <!-- Meeting Dates Section -->
            <div class="filter-section">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="section-title mb-3">Meeting Dates</h5>
                        <div class="d-flex align-items-center">
                            <select class="form-select me-2" style="max-width: 200px;">
                                <option selected>2025-10-01</option>
                                <option>2025-11-05</option>
                                <option>2025-12-03</option>
                            </select>

                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-end justify-content-end">
                        <button class="btn btn-outline-secondary me-2">
                            <i class="fas fa-download me-1"></i> Renewal Listing PDF
                        </button>
                        <button class="btn btn-outline-secondary me-2">
                            <i class="fas fa-download me-1"></i> Renewal Listing Excel
                        </button>

                    </div>
                </div>
                <div class="mt-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="studiesOnAgenda">
                        <label class="form-check-label small" for="studiesOnAgenda">
                            Show only those studies not already on Agenda for this date
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="crRequired">
                        <label class="form-check-label small" for="crRequired">
                            Show only those studies where CR is required
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="exemptStudies">
                        <label class="form-check-label small" for="exemptStudies">
                            Include Exempt Studies
                        </label>
                    </div>
                </div>
            </div>

            <!-- Agenda Items Table -->
            <div class="premium-card mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h4 class="section-title mb-0">
                        <i class="fas fa-list me-2"></i>
                        Study Items
                    </h4>
                    <div class="d-flex align-items-center">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search...">
                            <button class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-premium mb-0">
                            <thead class="table-primary">
                                <tr>
                                    <th>IRB #</th>
                                    <th>Study Type</th>
                                    <th>Protocol Number & Title</th>
                                    <th>ExpirationDate</th>
                                    <th>Agenda</th>
                                    <th>chkCRRqd</th>
                                    <th>ExpediteFlag</th>
                                    <th>SentFlag</th>
                                    <th>Date Sent</th>
                                    <th>FinalFlagNew</th>
                                    <th>ProgressFlagNew</th>
                                    <th>Date Received by IRB Co-Ord</th>
                                    <th>Last Renewal Date</th>
                                    <th>Renewal Cycle#</th>
                                    <th>PIDisplayName</th>
                                    <th>Study Status</th>
                                    <th>Ref Num</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (empty($studies)) {
                                    // Fallback to static data
                                    echo '<tr>
                                        <td>145/23-24</td>
                                        <td>Full Board</td>
                                        <td>Gender-based violence screening</td>
                                        <td>2025-07-02</td>
                                        <td>-</td>
                                        <td><input type="checkbox" disabled></td>
                                        <td>-</td>
                                        <td>0</td>
                                        <td>-</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>2024-07-03</td>
                                        <td>12</td>
                                        <td>Agbenu, Innes</td>
                                        <td><span class="status-badge status-open">Open</span></td>
                                        <td>REF-123</td>
                                    </tr>
                                   ';
                                } else {
                                    foreach ($studies as $index => $study) {
                                        echo '<tr>
                                            <td>' .htmlspecialchars($study['irb_number'] ?? '013/25-26') . '</td>
                                            <td>' . htmlspecialchars($study['review_type'] ?? '') . '</td>
                                             <td>' . htmlspecialchars($study['protocol_number'] ?? '') . '</td>
                                            <td>' . htmlspecialchars($study['expiration_date'] ?? '') . '</td>
                                            <td>' . htmlspecialchars($study['agenda'] ?? '') . '</td>
                                            <td><input type="checkbox" disabled></td>
                                            <td>' . htmlspecialchars($study['expedite_cite'] ?? '') . '</td>
                                            <td>' . htmlspecialchars($study['sent_flag'] ?? '0') . '</td>
                                            <td>' . htmlspecialchars($study['date_sent'] ?? '') . '</td>
                                            <td>' . htmlspecialchars($study['final_flag_sent'] ?? '') . '</td> 
                                            <td>' . htmlspecialchars($study['progress_flag_new'] ?? '') . '</td>
                                            <td>' . htmlspecialchars($study['date_received'] ?? '') . '</td>
                                            <td>' . htmlspecialchars($study['last_renewal_date'] ?? '') . '</td>
                                            <td>' . htmlspecialchars($study['renewal_cycle'] ?? '') . '</td>
                                            <td>' . htmlspecialchars($study['pi'] ?? '2025-10-01') . '</td>
                                            <td><span class="status-badge ' . (($study['study_status'] === "open") ? 'status-open' : 'status-closed') . '">' . htmlspecialchars($study['study_status'] ?? '') . '</span></td>
                                            <td>' . htmlspecialchars($study['ref_num'] ?? 'REC-123') . '</td>
                                        </tr>';
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination-wrapper mt-3">
                                <nav aria-label="Continue review studies pagination">
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
                                        (<?php echo $total_records; ?> total studies)
                                    </span>
                                </div>
                            </div>
                        <?php elseif ($total_records > 0): ?>
                            <div class="text-center mt-3">
                                <span class="text-muted small">
                                    Showing all <?php echo $total_records; ?> studies
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-between mt-4">
                <div>
                    <button class="btn btn-outline-primary me-2">
                        <i class="fas fa-copy me-1"></i>Place on Agenda Only
                    </button>
                    <button class="btn btn-outline-primary me-2">
                        <i class="fas fa-calendar-plus me-1"></i>Place on Agenda And Print Letter
                    </button>
                    
                </div>
               
            </div>
        </div>
    </div>
</div>
