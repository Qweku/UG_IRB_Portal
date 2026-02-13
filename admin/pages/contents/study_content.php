<?php
// Get filter parameters from GET request or default
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$review_type = isset($_GET['review_type']) ? $_GET['review_type'] : 'all';
$pi_name = isset($_GET['pi_name']) ? $_GET['pi_name'] : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'protocol_number';

// Pagination parameters
$limit = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Include CSRF protection
// require_once '../../includes/functions/csrf.php';

// Fetch studies based on filters with pagination
$studies = getStudies($status, $review_type, $pi_name, $sort_by, $limit, $offset);

// Get total count for pagination
$total_records = getStudiesCount($status, $review_type, $pi_name);
$total_pages = ceil($total_records / $limit);

// Fetch contact details for email modal
$contacts = getAllContacts();

// Fetch distinct PI names for dropdown
$pi_names = getDistinctPINames();

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
                                <i class="fas fa-flask"></i>
                            </div>
                            <div class="header-content">
                                <h4 class="page-title">Study / Protocol Management</h4>
                                <p class="page-subtitle">Manage and track all research studies and protocols</p>
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
                <a class="btn btn-primary" href="/studies/add-study">
                    <i class="fas fa-plus me-2"></i>Add New Study
                </a>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <form method="GET" action="">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-semibold">Study Status</label>
                            <select class="form-select" name="status">
                                <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>All Studies</option>
                                <option value="open" <?php echo $status == 'open' ? 'selected' : ''; ?>>Open Studies</option>
                                <option value="closed" <?php echo $status == 'closed' ? 'selected' : ''; ?>>Closed Studies</option>
                                <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending Review</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-semibold">Study Type</label>
                            <select class="form-select" name="review_type">
                                <option value="all" <?php echo $review_type == 'all' ? 'selected' : ''; ?>>All Types</option>
                                <option value="full_board" <?php echo $review_type == 'full_board' ? 'selected' : ''; ?>>Full Board</option>
                                <option value="expedited" <?php echo $review_type == 'expedited' ? 'selected' : ''; ?>>Expedited</option>
                                <option value="exempt" <?php echo $review_type == 'exempt' ? 'selected' : ''; ?>>Exempt</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-semibold">Principal Investigator</label>
                            <select class="form-select" name="pi_name">
                                <option value="" <?php echo $pi_name == '' ? 'selected' : ''; ?>>All PIs</option>
                                <?php foreach ($pi_names as $pi): ?>
                                    <option value="<?php echo htmlspecialchars($pi ?? ''); ?>" <?php echo $pi_name == $pi ? 'selected' : ''; ?>><?php echo htmlspecialchars($pi ?? ''); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-semibold">Sort By</label>
                            <select class="form-select" name="sort_by">
                                <option value="protocol_number" <?php echo $sort_by == 'protocol_number' ? 'selected' : ''; ?>>Protocol Number</option>
                                <option value="approval_date" <?php echo $sort_by == 'approval_date' ? 'selected' : ''; ?>>Approval Date</option>
                                <option value="title" <?php echo $sort_by == 'title' ? 'selected' : ''; ?>>Study Title</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-2"></i>Apply Filters
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Study Table -->
            <div class="premium-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Directory of Open Studies
                        </h5>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <button id="send-email-btn" type="button" class="btn btn-outline-light" onclick="openEmailModal()">
                            <i class="fas fa-envelope me-2"></i>Send Email
                        </button>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-hover table-premium">
                            <thead>
                                <tr>
                                    <th>Select</th>
                                    <th>Protocol Number</th>
                                    <th>Title</th>
                                    <th>Study Active?</th>
                                    <th>Study Type</th>
                                    <th>Study Status</th>
                                    <th>PI</th>
                                    <th>Review Cycle</th>
                                    <th>Data Received</th>
                                    <th>First IRB Review</th>
                                    <th>Approval Date</th>
                                    <th>Last Renewal Date</th>
                                    <th>InitEnroll</th>
                                    <th>#Patients enrolled</th>
                                    <th>ExpirationDate</th>
                                    <th>MostRecentMeeting</th>
                                    <th>ExemptCite</th>
                                    <th>ExpediteCite</th>
                                    <th>Remarks</th>
                                    <th>IRB Code</th>
                                    <th>AddToAgenda</th>
                                    <th>RiskDescription</th>
                                    <th>RefNum</th>
                                    <th>AuthorizedIRB</th>
                                    <th>FeeRequired</th>
                                    <th>CoorDisplayname</th>
                                    <th>SponsorDisplayname</th>
                                    <th>Reviewers</th>
                                    <th>Cols</th>
                                    <th>Admins</th>
                                    <th>Classifications</th>
                                    <th>Sites</th>
                                    <th>DeptGroups</th>
                                    <th>VulPops</th>
                                    <th>Childs</th>
                                    <th>Drugs</th>
                                    <th>Risks</th>
                                    <th>Benefits</th>
                                    <th>Div_s</th>
                                    <th>GrantProjects</th>
                                    <th>Inds</th>
                                    <th>UnderGrad_Grad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($studies)): ?>
                                    <?php foreach ($studies as $study): ?>
                                        <tr onclick="if(event.target.type !== 'checkbox') window.location.href='/studies/add-study?edit=1&id=<?php echo $study['id']; ?>'" style="cursor: pointer;">
                                            <td><input type="checkbox" class="study-checkbox" value="<?php echo $study['id']; ?>" data-title="<?php echo htmlspecialchars($study['title'] ?? ''); ?>" data-protocol="<?php echo htmlspecialchars($study['protocol_number'] ?? ''); ?>" onclick="event.stopPropagation();"></td>
                                            <td><?php echo htmlspecialchars($study['protocol_number'] ?? ''); ?></td>
                                            <td>
                                                <div style="width:250px;"><?php echo htmlspecialchars($study['title'] ?? ''); ?></div>
                                            </td>
                                            <td><span class="status-badge status-<?php echo strtolower($study['study_active']); ?>"><?php echo ucfirst($study['study_active']); ?></span></td>
                                            <td><?php echo ucwords(str_replace('_', ' ', $study['review_type'])); ?></td>
                                            <td><span class="status-badge status-<?php echo strtolower($study['study_status']); ?>"><?php echo ucfirst($study['study_status']); ?></span></td>
                                            <td>
                                                <div style="width:200px;"><?php echo htmlspecialchars(isset($study['pi']) ? $study['pi'] : ''); ?></div>
                                            </td>
                                            <td><?php echo htmlspecialchars($study['renewal_cycle'] ?? ''); ?></td>
                                            <td><?php echo  htmlspecialchars($study['data_received'] ?? ""); ?></td>
                                            <td><?php echo  htmlspecialchars($study['first_irb_review'] ?? ""); ?></td>
                                            <td><?php echo htmlspecialchars($study['approval_date'] ?? ""); ?></td>
                                            <td><?php echo htmlspecialchars($study['last_renewal_date'] ?? ""); ?></td>
                                            <td><?php echo htmlspecialchars($study['init_enroll'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($study['patients_enrolled'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($study['expiration_date'] ?? ""); ?></td>
                                            <td><?php echo htmlspecialchars($study['most_recent_meeting'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($study['exempt_cite'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($study['expedite_cite'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($study['remarks'] ?? ''); ?></td>
                                            <td>
                                                <div style="width:300px;" <?php echo htmlspecialchars($study['irb_code'] ?? ''); ?>></div>
                                            </td>
                                            <td><?php echo !empty($study['add_to_agenda']) ? 'Yes' : 'No'; ?></td>
                                            <td><?php echo htmlspecialchars($study['risk_description'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($study['ref_num'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($study['authorized_irb'] ?? ''); ?></td>
                                            <td><?php echo !empty($study['fee_required']) ? 'Yes' : 'No'; ?></td>
                                            <td><?php echo htmlspecialchars($study['coor_displayname'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($study['sponsor_displayname'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($study['reviewers'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($study['cols'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($study['admins'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($study['classifications'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($study['sites'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($study['dept_group'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($study['vul_props'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($study['childs'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($study['drugs'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($study['risk_category'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($study['benefits'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($study['divs'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($study['grant_projects'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($study['inds'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($study['under_grad'] ?? ''); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="41" class="text-center py-4">
                                            <i class="fas fa-folder-open fa-3x text-muted mb-3 d-block"></i>
                                            No studies found.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination-wrapper mt-3">
                                <nav aria-label="Studies pagination">
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

            <!-- Email Modal -->
            <div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <!-- Modal Header -->
                        <div class="modal-header email-header">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-envelope fs-4 me-3"></i>
                                <div>
                                    <h5 class="modal-title mb-0" id="emailModalLabel">Compose Email</h5>
                                    <small class="opacity-75">Send communication to study personnel</small>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <!-- Modal Body -->
                        <div class="modal-body p-4">
                            <form id="emailForm" enctype="multipart/form-data">
                                <?php echo csrf_field(); ?>
                                <!-- Study Selection -->
                                <div class="mb-4">
                                    <label for="studySelect" class="form-label fw-semibold">
                                        <i class="fas fa-file-text me-2"></i>Select Study
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-search"></i>
                                        </span>
                                        <select class="form-select form-select" id="studySelect" name="study_id" required>
                                            <option value="" disabled selected>Choose a study...</option>
                                            <?php foreach ($studies as $study): ?>
                                                <option value="<?php echo $study['id']; ?>"
                                                    data-title="<?php echo htmlspecialchars($study['title'] ?? ''); ?>"
                                                    data-protocol="<?php echo htmlspecialchars($study['protocol_number'] ?? ''); ?>"
                                                    data-status="<?php echo $study['status'] ?? 'active'; ?>"
                                                    data-pi="<?php echo htmlspecialchars($study['pi'] ?? ''); ?>">
                                                    <?php echo htmlspecialchars($study['protocol_number'] . ' - ' . ($study['title'] ?? '')); ?>

                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-text text-muted mt-2">
                                        <i class="fas fa-info-circle me-1"></i>Select a study to auto-fill study details
                                    </div>
                                </div>

                                <!-- Study Details Card -->
                                <div class="card border-primary mb-4" id="studyDetailsCard" style="display: none;">
                                    <div class="card-header bg-primary bg-opacity-10 border-primary">
                                        <h6 class="mb-0">
                                            <i class="fas fa-clipboard-list me-2"></i>Study Details
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label small text-muted mb-1">Protocol Number</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light">
                                                        <i class="fas fa-hashtag"></i>
                                                    </span>
                                                    <input type="text" class="form-control" id="protocolInput" name="protocol_number" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label small text-muted mb-1">Study Personnel</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light">
                                                        <i class="fas fa-user-tag"></i>
                                                    </span>
                                                    <input type="text" class="form-control" id="piInput" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small text-muted mb-1">Study Title</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">
                                                    <i class="fas fa-paragraph"></i>
                                                </span>
                                                <input type="text" class="form-control" id="titleInput" name="title" readonly>
                                            </div>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label small text-muted mb-1">Study Status</label>
                                            <div class="d-flex align-items-center">
                                                <span class="badge" id="statusBadge">Active</span>
                                                <button type="button" class="btn btn-sm btn-link ms-auto" id="clearStudyBtn">
                                                    <i class="fas fa-times-circle me-1"></i>Clear Selection
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Email Details -->
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="recipientsInput" class="form-label fw-semibold">
                                            <i class="fas fa-users me-2"></i>Recipients
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">
                                                <i class="fas fa-at"></i>
                                            </span>
                                            <input type="text" class="form-control" id="recipientsInput" name="recipients"
                                                placeholder="email1@example.com, email2@example.com" required>
                                            <button class="btn btn-outline-secondary" type="button" id="addRecipientBtn">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                        <div class="form-text text-muted mt-1">
                                            Separate multiple emails with commas
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="subjectInput" class="form-label fw-semibold">
                                            <i class="fas fa-tag me-2"></i>Subject
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">
                                                <i class="fas fa-comment"></i>
                                            </span>
                                            <input type="text" class="form-control" id="subjectInput" name="subject"
                                                placeholder="Enter email subject" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Message -->
                                <div class="mb-4">
                                    <label for="messageTextarea" class="form-label fw-semibold">
                                        <i class="fas fa-comment-alt me-2"></i>Message
                                    </label>
                                    <div class="border rounded-3 overflow-hidden">
                                        <div class="bg-light border-bottom px-3 py-2">
                                            <div class="btn-group btn-group-sm" role="group" aria-label="Text formatting">
                                                <button type="button" class="btn btn-outline-secondary" onclick="formatText('bold')">
                                                    <i class="fas fa-bold"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary" onclick="formatText('italic')">
                                                    <i class="fas fa-italic"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary" onclick="formatText('underline')">
                                                    <i class="fas fa-underline"></i>
                                                </button>
                                                <div class="vr mx-2"></div>
                                                <button type="button" class="btn btn-outline-secondary" onclick="insertTemplate('followup')">
                                                    <i class="fas fa-history me-1"></i>Follow-up
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary" onclick="insertTemplate('urgent')">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>Urgent
                                                </button>
                                            </div>
                                        </div>
                                        <textarea class="form-control border-0" id="messageTextarea" name="message"
                                            rows="6" placeholder="Type your message here..." required></textarea>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <div class="form-text text-muted">
                                            <i class="fas fa-lightbulb me-1"></i>Use templates for common messages
                                        </div>
                                        <small class="text-muted">
                                            <span id="charCount">0</span> characters
                                        </small>
                                    </div>
                                </div>

                                <!-- Attachment -->
                                <div class="mb-4">
                                    <label for="attachmentInput" class="form-label fw-semibold">
                                        <i class="fas fa-paperclip me-2"></i>Attachment
                                    </label>
                                    <div class="border rounded-3 p-3">
                                        <div class="input-group">
                                            <input type="file" class="form-control" id="attachmentInput" name="attachment"
                                                aria-describedby="attachmentHelp">
                                            <button class="btn btn-outline-secondary" type="button" onclick="clearAttachment()">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        <div class="mt-2" id="attachmentPreview"></div>
                                        <div id="attachmentHelp" class="form-text text-muted mt-2">
                                            <i class="fas fa-info-circle me-1"></i>Maximum file size: 10MB. Allowed types: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG
                                        </div>
                                    </div>
                                </div>

                                <!-- Email Options -->
                                <div class="accordion mb-4" id="emailOptions">
                                    <div class="accordion-item border-0">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed bg-light" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#optionsCollapse">
                                                <i class="fas fa-cog me-2"></i>Advanced Options
                                            </button>
                                        </h2>
                                        <div id="optionsCollapse" class="accordion-collapse collapse" data-bs-parent="#emailOptions">
                                            <div class="accordion-body pt-3">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="readReceipt" name="read_receipt">
                                                            <label class="form-check-label" for="readReceipt">
                                                                Request read receipt
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="highPriority" name="high_priority">
                                                            <label class="form-check-label" for="highPriority">
                                                                <i class="fas fa-exclamation-triangle text-warning me-1"></i>High priority
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="saveCopy" name="save_copy" checked>
                                                            <label class="form-check-label" for="saveCopy">
                                                                Save copy to Sent folder
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="scheduleSend" name="schedule_send">
                                                            <label class="form-check-label" for="scheduleSend">
                                                                Schedule send
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3" id="scheduleOptions" style="display: none;">
                                                    <label for="scheduleDate" class="form-label small">Schedule Date & Time</label>
                                                    <div class="row g-2">
                                                        <div class="col-md-6">
                                                            <input type="date" class="form-control" id="scheduleDate" name="schedule_date">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <input type="time" class="form-control" id="scheduleTime" name="schedule_time">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Modal Footer -->
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times-circle me-1"></i>Cancel
                            </button>
                            <button type="button" class="btn btn-primary" onclick="sendEmail(this)" id="sendEmailBtn">
                                <i class="fas fa-paper-plane me-1"></i>Send Email
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-primary dropdown-toggle" type="button" id="sendOptionsDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="sendOptionsDropdown">
                                    <li><a class="dropdown-item" href="#" onclick="saveAsDraft()">
                                            <i class="fas fa-save me-2"></i>Save as Draft
                                        </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="previewEmail()">
                                            <i class="fas fa-eye me-2"></i>Preview
                                        </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview Modal -->
            <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Email Preview</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="previewContent"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" onclick="sendEmail(this)">Send Now</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .email-header {
        background: linear-gradient(135deg, var(--royal-blue), var(--royal-blue-light));
        color: white;
    }

    #studyDetailsCard {
        transition: all 0.3s ease;
        border-left-width: 4px;
    }

    #attachmentPreview {
        min-height: 40px;
    }
</style>