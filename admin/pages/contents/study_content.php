<?php
// Get filter parameters from GET request or default
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$review_type = isset($_GET['review_type']) ? $_GET['review_type'] : 'all';
$pi_name = isset($_GET['pi_name']) ? $_GET['pi_name'] : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'protocol_number';

// Include CSRF protection
// require_once '../../includes/functions/csrf.php';

// Fetch studies based on filters
$studies = getStudies($status, $review_type, $pi_name, $sort_by);

// Fetch contact details for email modal
$contacts = getAllContacts();

// Fetch distinct PI names for dropdown
$pi_names = getDistinctPINames();
?>
<!-- Main Content -->
<div class="">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Study / Protocol Management</h2>
        <a class="btn btn-success" href="/studies/add-study">
            <i class="fas fa-plus me-1"></i> Add New Study
        </a>
    </div>

    <!-- Filter Section -->
    <form method="GET" action="">
        <div class="filter-section">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label class="form-label">Study Status</label>
                    <select class="form-select" name="status">
                        <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>All Studies</option>
                        <option value="open" <?php echo $status == 'open' ? 'selected' : ''; ?>>Open Studies</option>
                        <option value="closed" <?php echo $status == 'closed' ? 'selected' : ''; ?>>Closed Studies</option>
                        <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending Review</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Study Type</label>
                    <select class="form-select" name="review_type">
                        <option value="all" <?php echo $review_type == 'all' ? 'selected' : ''; ?>>All Types</option>
                        <option value="full_board" <?php echo $review_type == 'full_board' ? 'selected' : ''; ?>>Full Board</option>
                        <option value="expedited" <?php echo $review_type == 'expedited' ? 'selected' : ''; ?>>Expedited</option>
                        <option value="exempt" <?php echo $review_type == 'exempt' ? 'selected' : ''; ?>>Exempt</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Principal Investigator</label>
                    <select class="form-select" name="pi_name">
                        <option value="" <?php echo $pi_name == '' ? 'selected' : ''; ?>>All PIs</option>
                        <?php foreach ($pi_names as $pi): ?>
                            <option value="<?php echo htmlspecialchars($pi); ?>" <?php echo $pi_name == $pi ? 'selected' : ''; ?>><?php echo htmlspecialchars($pi); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Sort By</label>
                    <select class="form-select" name="sort_by">
                        <option value="protocol_number" <?php echo $sort_by == 'protocol_number' ? 'selected' : ''; ?>>Protocol Number</option>
                        <option value="approval_date" <?php echo $sort_by == 'approval_date' ? 'selected' : ''; ?>>Approval Date</option>
                        <option value="title" <?php echo $sort_by == 'title' ? 'selected' : ''; ?>>Study Title</option>
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </div>
        </div>
    </form>

    <!-- Study Table -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3 section-title">
            <h4 class="bold"><strong>Directory of Open Studies</strong></h4>
            <!-- Send Email -->
            <button id="send-email-btn" type="button" class="btn btn-primary" onclick="openEmailModal()">
                <i class="fas fa-envelope me-1"></i> Send Email
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover study-table">
                <thead class="table-primary">
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
                                <td><input type="checkbox" class="study-checkbox" value="<?php echo $study['id']; ?>" data-title="<?php echo htmlspecialchars($study['title']); ?>" data-protocol="<?php echo htmlspecialchars($study['protocol_number']); ?>" onclick="event.stopPropagation();"></td>
                                <td><?php echo htmlspecialchars($study['protocol_number']); ?></td>
                                <td>
                                    <div style="width:250px;"><?php echo htmlspecialchars($study['title']); ?></div>
                                </td>
                                <td><span class="status-badge status-<?php echo strtolower($study['study_active']); ?>"><?php echo ucfirst($study['study_active']); ?></span></td>
                                <td><?php echo ucwords(str_replace('_', ' ', $study['review_type'])); ?></td>
                                <td><span class="status-badge status-<?php echo strtolower($study['study_status']); ?>"><?php echo ucfirst($study['study_status']); ?></span></td>
                                <td>
                                    <div style="width:200px;"><?php echo htmlspecialchars(isset($study['pi']) ? $study['pi'] : ''); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($study['renewal_cycle']); ?></td>
                                <td><?php echo  htmlspecialchars($study['data_received'] ?? ""); ?></td>
                                <td><?php echo  htmlspecialchars($study['first_irb_review'] ?? ""); ?></td>
                                <td><?php echo htmlspecialchars($study['approval_date'] ?? ""); ?></td>
                                <td><?php echo htmlspecialchars($study['last_renewal_date'] ?? ""); ?></td>
                                <td><?php echo htmlspecialchars($study['init_enroll']); ?></td>
                                <td><?php echo htmlspecialchars($study['patients_enrolled']); ?></td>
                                <td><?php echo htmlspecialchars($study['expiration_date'] ?? ""); ?></td>
                                <td><?php echo htmlspecialchars($study['most_recent_meeting' ?? ""]); ?></td>
                                <td><?php echo htmlspecialchars($study['exempt_cite']); ?></td>
                                <td><?php echo htmlspecialchars($study['expedite_cite']); ?></td>
                                <td><?php echo htmlspecialchars($study['remarks']); ?></td>
                                <td><?php echo htmlspecialchars($study['irb_code']); ?></td>
                                <td><?php echo !empty($study['add_to_agenda']) ? 'Yes' : 'No'; ?></td>
                                <td><?php echo htmlspecialchars($study['risk_description']); ?></td>
                                <td><?php echo htmlspecialchars($study['ref_num']); ?></td>
                                <td><?php echo htmlspecialchars($study['authorized_irb']); ?></td>
                                <td><?php echo !empty($study['fee_required']) ? 'Yes' : 'No'; ?></td>
                                <td><?php echo htmlspecialchars($study['coor_displayname']); ?></td>
                                <td><?php echo htmlspecialchars($study['sponsor_displayname']); ?></td>
                                <td><?php echo htmlspecialchars($study['reviewers']); ?></td>
                                <td><?php echo htmlspecialchars($study['cols']); ?></td>
                                <td><?php echo htmlspecialchars($study['admins']); ?></td>
                                <td><?php echo htmlspecialchars($study['classifications']); ?></td>
                                <td><?php echo htmlspecialchars($study['sites']); ?></td>
                                <td><?php echo htmlspecialchars($study['dept_group']); ?></td>
                                <td><?php echo htmlspecialchars($study['vul_props']); ?></td>
                                <td><?php echo htmlspecialchars($study['childs']); ?></td>
                                <td><?php echo htmlspecialchars($study['drugs']); ?></td>
                                <td><?php echo htmlspecialchars($study['risk_category']); ?></td>
                                <td><?php echo htmlspecialchars($study['benefits']); ?></td>
                                <td><?php echo htmlspecialchars($study['divs']); ?></td>
                                <td><?php echo htmlspecialchars($study['grant_projects']); ?></td>
                                <td><?php echo htmlspecialchars($study['inds']); ?></td>
                                <td><?php echo htmlspecialchars($study['under_grad']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="41" class="text-center">No studies found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Email Modal -->
        <div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <!-- Modal Header -->
                    <div class="modal-header email-header text-white">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-envelope fs-4 me-2"></i>
                            <h5 class="modal-title mb-0" id="emailModalLabel">Compose Email</h5>
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
                                    <i class="fas fa-file-text me-1"></i>Select Study
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <select class="form-select form-select" id="studySelect" name="study_id" required>
                                        <option value="" disabled selected>Choose a study...</option>
                                        <?php foreach ($studies as $study): ?>
                                            <option value="<?php echo $study['id']; ?>"
                                                data-title="<?php echo htmlspecialchars($study['title']); ?>"
                                                data-protocol="<?php echo htmlspecialchars($study['protocol_number']); ?>"
                                                data-status="<?php echo $study['status'] ?? 'active'; ?>"
                                                data-pi="<?php echo htmlspecialchars($study['pi'] ?? ''); ?>">
                                                <?php echo htmlspecialchars($study['protocol_number'] . ' - ' . $study['title']); ?>

                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-text text-muted mt-1">
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
                                        <i class="fas fa-users me-1"></i>Recipients
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
                                        <i class="fas fa-tag me-1"></i>Subject
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
                                    <i class="fas fa-comment-alt me-1"></i>Message
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
                                    <i class="fas fa-paperclip me-1"></i>Attachment
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

            .file-preview {
                background: #f8f9fa;
                border-radius: 6px;
                padding: 8px 12px;
                margin-top: 8px;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .btn-group-sm .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.875rem;
            }

            #messageTextarea {
                resize: vertical;
                min-height: 150px;
            }

            .accordion-button:not(.collapsed) {
                background-color: rgba(13, 110, 253, 0.1);
                color: #0d6efd;
            }

            .dropdown-toggle::after {
                margin-left: 0.25rem;
            }
        </style>

        <script>
            // Helper function for status badge colors (implement in your PHP)
            function getStatusBadgeColor(status) {
                const colors = {
                    'active': 'success',
                    'pending': 'warning',
                    'completed': 'info',
                    'terminated': 'danger',
                    'suspended': 'secondary'
                };
                return colors[status.toLowerCase()] || 'secondary';
            }

            document.addEventListener('DOMContentLoaded', function() {
                const studySelect = document.getElementById('studySelect');
                const studyDetailsCard = document.getElementById('studyDetailsCard');
                const titleInput = document.getElementById('titleInput');
                const protocolInput = document.getElementById('protocolInput');
                const piInput = document.getElementById('piInput');
                const statusBadge = document.getElementById('statusBadge');
                const messageTextarea = document.getElementById('messageTextarea');
                const charCount = document.getElementById('charCount');
                const attachmentInput = document.getElementById('attachmentInput');
                const attachmentPreview = document.getElementById('attachmentPreview');
                const scheduleSendCheckbox = document.getElementById('scheduleSend');
                const scheduleOptions = document.getElementById('scheduleOptions');

                // Study selection handler
                studySelect.addEventListener('change', function() {
                    if (this.value) {
                        const selectedOption = this.options[this.selectedIndex];
                        const title = selectedOption.getAttribute('data-title');
                        const protocol = selectedOption.getAttribute('data-protocol');
                        const status = selectedOption.getAttribute('data-status');
                        const pi = selectedOption.getAttribute('data-pi');

                        titleInput.value = title;
                        protocolInput.value = protocol;
                        piInput.value = pi || 'N/A';

                        // Update status badge
                        if (status) {
                            const badgeColor = getStatusBadgeColor(status);
                            statusBadge.className = `badge bg-${badgeColor}`;
                            statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                        }

                        // Show study details card
                        studyDetailsCard.style.display = 'block';

                        // Fetch and populate recipient emails
                        fetch(`/admin/handlers/fetch_personnel_emails.php?study_id=${this.value}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.emails && data.emails.length > 0) {
                                    recipientsInput.value = data.emails.join(', ');
                                } else {
                                    recipientsInput.value = '';
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching personnel emails:', error);
                                recipientsInput.value = '';
                            });

                        // Auto-fill subject if empty
                        if (!document.getElementById('subjectInput').value) {
                            document.getElementById('subjectInput').value = `Update: ${protocol} - ${title}`;
                        }

                        // Auto-fill message template
                        if (!messageTextarea.value.trim()) {
                            const template = `Dear Team,\n\nRegarding study ${protocol} - ${title}:\n\n`;
                            messageTextarea.value = template;
                            updateCharCount();
                        }
                    }
                });

                // Clear study selection
                document.getElementById('clearStudyBtn').addEventListener('click', function() {
                    studySelect.value = '';
                    studyDetailsCard.style.display = 'none';
                    titleInput.value = '';
                    protocolInput.value = '';
                    piInput.value = '';
                    recipientsInput.value = '';
                });

                // Character count for message
                messageTextarea.addEventListener('input', updateCharCount);

                function updateCharCount() {
                    const count = messageTextarea.value.length;
                    charCount.textContent = count;
                    charCount.className = count > 5000 ? 'text-danger' : 'text-muted';
                }

                // Attachment preview
                attachmentInput.addEventListener('change', function() {
                    const file = this.files[0];
                    attachmentPreview.innerHTML = '';

                    if (file) {
                        const preview = document.createElement('div');
                        preview.className = 'file-preview';
                        preview.innerHTML = `
                <div>
                    <i class="fas fa-file me-2"></i>
                    <span>${file.name}</span>
                    <small class="text-muted ms-2">(${(file.size / 1024).toFixed(1)} KB)</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearAttachment()">
                    <i class="fas fa-times"></i>
                </button>
            `;
                        attachmentPreview.appendChild(preview);

                        // Validate file size
                        if (file.size > 10 * 1024 * 1024) { // 10MB
                            alert('File size exceeds 10MB limit');
                            clearAttachment();
                        }

                        // Validate file type
                        const allowedTypes = ['.pdf', '.doc', '.docx', '.xls', '.xlsx', '.jpg', '.jpeg', '.png'];
                        const fileExt = '.' + file.name.split('.').pop().toLowerCase();
                        if (!allowedTypes.includes(fileExt)) {
                            alert('File type not allowed. Please upload PDF, DOC, XLS, JPG, or PNG files.');
                            clearAttachment();
                        }
                    }
                });

                // Schedule send options
                scheduleSendCheckbox.addEventListener('change', function() {
                    scheduleOptions.style.display = this.checked ? 'block' : 'none';
                    if (this.checked) {
                        // Set minimum date to tomorrow
                        const tomorrow = new Date();
                        tomorrow.setDate(tomorrow.getDate() + 1);
                        document.getElementById('scheduleDate').min = tomorrow.toISOString().split('T')[0];
                        document.getElementById('scheduleDate').value = tomorrow.toISOString().split('T')[0];
                        document.getElementById('scheduleTime').value = '09:00';
                    }
                });

                // Add recipient button
                document.getElementById('addRecipientBtn').addEventListener('click', function() {
                    const recipientsInput = document.getElementById('recipientsInput');
                    const email = prompt('Enter email address to add:');
                    if (email && validateEmail(email)) {
                        if (recipientsInput.value) {
                            recipientsInput.value += ', ' + email;
                        } else {
                            recipientsInput.value = email;
                        }
                    } else if (email) {
                        alert('Please enter a valid email address');
                    }
                });

                // Initialize character count
                updateCharCount();
            });

            function clearAttachment() {
                document.getElementById('attachmentInput').value = '';
                document.getElementById('attachmentPreview').innerHTML = '';
            }

            function validateEmail(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(email);
            }

            function formatText(command) {
                const textarea = document.getElementById('messageTextarea');
                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                const selectedText = textarea.value.substring(start, end);

                let formattedText = selectedText;
                switch (command) {
                    case 'bold':
                        formattedText = `**${selectedText}**`;
                        break;
                    case 'italic':
                        formattedText = `*${selectedText}*`;
                        break;
                    case 'underline':
                        formattedText = `<u>${selectedText}</u>`;
                        break;
                }

                textarea.value = textarea.value.substring(0, start) + formattedText + textarea.value.substring(end);
                textarea.focus();
                textarea.setSelectionRange(start + formattedText.length, start + formattedText.length);
                updateCharCount();
            }

            function insertTemplate(type) {
                const textarea = document.getElementById('messageTextarea');
                let template = '';

                switch (type) {
                    case 'followup':
                        template = "\n\n--- FOLLOW-UP REQUEST ---\n\nPlease provide an update on the status of this study at your earliest convenience.\n\nThank you,\n[Your Name]";
                        break;
                    case 'urgent':
                        template = "\n\n--- URGENT ---\n\nThis matter requires immediate attention. Please respond within 24 hours.\n\nThank you,\n[Your Name]";
                        break;
                }

                const cursorPos = textarea.selectionStart;
                const textBefore = textarea.value.substring(0, cursorPos);
                const textAfter = textarea.value.substring(cursorPos);

                textarea.value = textBefore + template + textAfter;
                textarea.focus();
                textarea.setSelectionRange(cursorPos + template.length, cursorPos + template.length);
                updateCharCount();
            }

            function previewEmail() {
                const subject = document.getElementById('subjectInput').value;
                const message = document.getElementById('messageTextarea').value;
                const recipients = document.getElementById('recipientsInput').value;
                const studyTitle = document.getElementById('titleInput').value;

                let previewHTML = `
        <div class="email-preview">
            <div class="mb-3">
                <strong>To:</strong> ${recipients || 'No recipients specified'}
            </div>
            <div class="mb-3">
                <strong>Subject:</strong> ${subject || 'No subject'}
            </div>
            ${studyTitle ? `<div class="mb-3"><strong>Study:</strong> ${studyTitle}</div>` : ''}
            <div class="border rounded p-3 bg-light">
                ${message.replace(/\n/g, '<br>') || '<em>No message content</em>'}
            </div>
        </div>
    `;

                document.getElementById('previewContent').innerHTML = previewHTML;
                const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
                previewModal.show();
            }

            function saveAsDraft() {
                // Implement draft saving logic
                alert('Draft saved successfully!');
            }

        </script>

        <script>
            document.addEventListener('DOMContentLoaded', () => {

                function openEmailModal() {
                    
                    const modalEl = document.getElementById('emailModal');
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }

                window.openEmailModal = openEmailModal;

                document.getElementById('studySelect').addEventListener('change', function() {
                    const opt = this.options[this.selectedIndex];
                    document.getElementById('titleInput').value = opt.dataset.title || '';
                    document.getElementById('protocolInput').value = opt.dataset.protocol || '';
                });

            });

            function sendEmail(button) {
                const form = document.getElementById('emailForm');
                const formData = new FormData(form);

                // Show loading state
                const originalText = button.innerHTML;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
                button.disabled = true;

                fetch('/admin/handlers/send_email_handler.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Email sent successfully!');
                            bootstrap.Modal.getInstance(document.getElementById('emailModal')).hide();
                            form.reset();
                        } else {
                            alert('Error sending email: ' + data.message);
                        }
                        // Reset button
                        button.innerHTML = originalText;
                        button.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while sending the email.');
                        // Reset button
                        button.innerHTML = originalText;
                        button.disabled = false;
                    });
            }
        </script>

        <!-- Pagination -->
        <div class="pagination-container mt-3">
            <nav aria-label="Study table navigation">
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1">Previous</a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>