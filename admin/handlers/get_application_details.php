<?php
require_once '../includes/auth_check.php';
require_once '../../includes/functions/helpers.php';

header('Content-Type: application/json');

// Use centralized role check
require_role('admin');

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    }

    $applicationId = $_GET['id'] ?? null;

    if (empty($applicationId)) {
        echo json_encode(['status' => 'error', 'message' => 'Application ID is required']);
        exit;
    }

    // Get main application data with all three detail tables
    $stmt = $conn->prepare("SELECT a.*, 
                        sa.*, 
                        na.*, 
                        nna.* 
                        FROM applications a
                        LEFT JOIN student_application_details sa ON sa.application_id = a.id
                        LEFT JOIN nmimr_application_details na ON na.application_id = a.id
                        LEFT JOIN non_nmimr_application_details nna ON nna.application_id = a.id
                        WHERE a.id = ?");
    $stmt->execute([$applicationId]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("All Application Details:" .  print_r($application, true));

    if (!$application) {
        echo json_encode(['status' => 'error', 'message' => 'Application not found']);
        exit;
    }

    // Get assigned reviewers
    $stmt = $conn->prepare("
        SELECT ar.*, u.full_name, u.email
        FROM application_reviews ar
        JOIN users u ON ar.reviewer_id = u.id
        WHERE ar.application_id = ?
        ORDER BY ar.created_at DESC
    ");
    $stmt->execute([$applicationId]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch documents - try application_id first, then study_id
    $stmt = $conn->prepare("SELECT * FROM application_documents WHERE application_id = ?");
    $stmt->execute([$applicationId]);
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If no documents with application_id, try study_id
    if (empty($documents)) {
        $stmt = $conn->prepare("SELECT * FROM application_documents WHERE study_id = ?");
        $stmt->execute([$applicationId]);
        $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Determine application type based on which detail table has data
    $applicationType = 'student';
    if (!empty($application['nmimr_organization']) || !empty($application['nmimr_department'])) {
        $applicationType = 'nmimr';
    } elseif (!empty($application['institution_name']) || !empty($application['principal_investigator'])) {
        $applicationType = 'non_nmimr';
    }

    // Build HTML output
    $html = buildApplicationDetailsHtml($application, $reviews, $documents, $applicationType);

    echo json_encode([
        'status' => 'success',
        'html' => $html,
        'data' => $application,
        'application_type' => $applicationType,
        'documents' => $documents
    ]);
} catch (PDOException $e) {
    error_log(__FILE__ . " - Database error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}

function buildApplicationDetailsHtml($application, $reviews, $documents, $applicationType = 'student')
{
    // Get research type from application
    // $researchType = $application['research_type'] ?? 'Student Research';
    
    ob_start();
?>
<!-- Stepper Navigation -->
<div class="application-stepper mb-4">
    <ul class="nav nav-tabs" id="applicationSteps" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="step1-tab" data-bs-toggle="tab" data-bs-target="#step1" type="button" role="tab">
                <i class="fas fa-info-circle me-1"></i> Basic Info
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="step2-tab" data-bs-toggle="tab" data-bs-target="#step2" type="button" role="tab">
                <i class="fas fa-user me-1"></i> Researcher
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="step3-tab" data-bs-toggle="tab" data-bs-target="#step3" type="button" role="tab">
                <i class="fas fa-flask me-1"></i> Study Details
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="step4-tab" data-bs-toggle="tab" data-bs-target="#step4" type="button" role="tab">
                <i class="fas fa-file-alt me-1"></i> Documents
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="step5-tab" data-bs-toggle="tab" data-bs-target="#step5" type="button" role="tab">
                <i class="fas fa-check-circle me-1"></i> Review & Actions
            </button>
        </li>
    </ul>
</div>

<!-- Step Content -->
<div class="tab-content" id="applicationStepsContent">
    
    <!-- Step 1: Basic Application Info -->
    <div class="tab-pane fade show active" id="step1" role="tabpanel">
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-muted mb-3">Application Information</h6>
                <table class="table table-sm table-bordered">
                    <tr>
                        <td><strong>Protocol Number</strong></td>
                        <td><?= htmlspecialchars($application['protocol_number'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <td><strong>Study Title</strong></td>
                        <td><?= htmlspecialchars($application['study_title'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <td><strong>Research Type</strong></td>
                        <td><span class="badge bg-primary"><?= htmlspecialchars($applicationType) ?></span></td>
                    </tr>
                    <tr>
                        <td><strong>Submission Date</strong></td>
                        <td><?= !empty($application['updated_at']) ? date('d M Y', strtotime($application['updated_at'])) : 'N/A' ?></td>
                    </tr>
                    <tr>
                        <td><strong>Status</strong></td>
                        <td>
                            <?php $statusClass = match($application['status'] ?? '') {
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'pending' => 'warning',
                                'under_review' => 'info',
                                default => 'secondary'
                            }; ?>
                            <span class="badge bg-<?= $statusClass ?>"><?= htmlspecialchars(ucfirst($application['status'] ?? 'Unknown')) ?></span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted mb-3">Reviewer Assignments</h6>
                <?php if (empty($reviews)): ?>
                    <p class="text-muted">No reviewers assigned yet</p>
                <?php else: ?>
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Reviewer</th>
                                <th>Status</th>
                                <th>Due Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reviews as $review): ?>
                                <tr>
                                    <td><?= htmlspecialchars($review['full_name']) ?></td>
                                    <td><?= htmlspecialchars(ucfirst($review['status'])) ?></td>
                                    <td><?= !empty($review['due_date']) ? date('d M Y', strtotime($review['due_date'])) : 'N/A' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-12">
                <h6 class="text-muted mb-3">Abstract</h6>
                <div class="p-3 bg-light rounded"><?= nl2br(htmlspecialchars($application['abstract'] ?? 'No abstract provided')) ?></div>
            </div>
        </div>
    </div>

    <!-- Step 2: Researcher Details (varies by research type) -->
    <div class="tab-pane fade" id="step2" role="tabpanel">
        <?php if ($applicationType === 'student'): ?>
            <h6 class="text-muted mb-3">Student Researcher Details</h6>
            <table class="table table-sm table-bordered">
                <tr>
                    <td><strong>Student Name</strong></td>
                    <td><?= htmlspecialchars($application['student_name'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td><strong>Student ID</strong></td>
                    <td><?= htmlspecialchars($application['student_number'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td><strong>Department</strong></td>
                    <td><?= htmlspecialchars($application['student_department'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td><strong>Program of Study</strong></td>
                    <td><?= htmlspecialchars($application['student_institution'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td><strong>Supervisor Name</strong></td>
                    <td><?= htmlspecialchars($application['supervisor1_name'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td><strong>Supervisor Email</strong></td>
                    <td><?= htmlspecialchars($application['supervisor1_email'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td><strong>Contact Phone</strong></td>
                    <td><?= htmlspecialchars($application['supervisor1_phone'] ?? 'N/A') ?></td>
                </tr>
            </table>
        <?php elseif ($applicationType === 'nmimr'): ?>
            <h6 class="text-muted mb-3">NMIMR Research Details</h6>
            <table class="table table-sm table-bordered">
                <tr>
                    <td><strong>Organization</strong></td>
                    <td><?= htmlspecialchars($application['nmimr_organization'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td><strong>Department</strong></td>
                    <td><?= htmlspecialchars($application['nmimr_department'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td><strong>Principal Investigator</strong></td>
                    <td><?= htmlspecialchars($application['nmimr_pi'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td><strong>PI Email</strong></td>
                    <td><?= htmlspecialchars($application['nmimr_pi_email'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td><strong>Research Staff</strong></td>
                    <td><?= htmlspecialchars($application['nmimr_research_staff'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td><strong>Contact Phone</strong></td>
                    <td><?= htmlspecialchars($application['nmimr_phone'] ?? 'N/A') ?></td>
                </tr>
            </table>
        <?php else: // non_nmimr ?>
            <h6 class="text-muted mb-3">Non-NMIMR Research Details</h6>
            <table class="table table-sm table-bordered">
                <tr>
                    <td><strong>Institution Name</strong></td>
                    <td><?= htmlspecialchars($application['institution_name'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td><strong>Principal Investigator</strong></td>
                    <td><?= htmlspecialchars($application['principal_investigator'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td><strong>PI Title/Position</strong></td>
                    <td><?= htmlspecialchars($application['pi_title'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td><strong>PI Email</strong></td>
                    <td><?= htmlspecialchars($application['pi_email'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td><strong>Institution Address</strong></td>
                    <td><?= htmlspecialchars($application['institution_address'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td><strong>Contact Phone</strong></td>
                    <td><?= htmlspecialchars($application['contact_phone'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td><strong>Local Collaborator</strong></td>
                    <td><?= htmlspecialchars($application['local_collaborator'] ?? 'N/A') ?></td>
                </tr>
            </table>
        <?php endif; ?>
    </div>

    <!-- Step 3: Study Details -->
    <div class="tab-pane fade" id="step3" role="tabpanel">
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-muted mb-3">Study Information</h6>
                <table class="table table-sm table-bordered">
                    <tr>
                        <td><strong>Study Title</strong></td>
                        <td><?= htmlspecialchars($application['study_title'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <td><strong>Protocol Number</strong></td>
                        <td><?= htmlspecialchars($application['protocol_number'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <td><strong>Sponsor</strong></td>
                        <td><?= htmlspecialchars($application['sponsor'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <td><strong>Funding Source</strong></td>
                        <td><?= htmlspecialchars($application['funding_source'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <td><strong>Study Duration</strong></td>
                        <td><?= htmlspecialchars($application['study_duration'] ?? 'N/A') ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted mb-3">Methodology & Participants</h6>
                <table class="table table-sm table-bordered">
                    <tr>
                        <td><strong>Study Design</strong></td>
                        <td><?= htmlspecialchars($application['study_design'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <td><strong>Target Population</strong></td>
                        <td><?= htmlspecialchars($application['target_population'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <td><strong>Sample Size</strong></td>
                        <td><?= htmlspecialchars($application['sample_size'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <td><strong>Age Range</strong></td>
                        <td><?= htmlspecialchars($application['age_range'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <td><strong>Vulnerable Population</strong></td>
                        <td><?= htmlspecialchars($application['vulnerable_population'] ?? 'No') ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-12">
                <h6 class="text-muted mb-3">Study Objectives</h6>
                <div class="p-3 bg-light rounded"><?= nl2br(htmlspecialchars($application['ethical_considerations'] ?? 'Not specified')) ?></div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-12">
                <h6 class="text-muted mb-3">Methodology Description</h6>
                <div class="p-3 bg-light rounded"><?= nl2br(htmlspecialchars($application['work_plan'] ?? 'Not specified')) ?></div>
            </div>
        </div>
    </div>

    <!-- Step 4: Documents & Attachments -->
    <div class="tab-pane fade" id="step4" role="tabpanel">
        <h6 class="text-muted mb-3">Uploaded Documents</h6>
        <?php if (empty($documents)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> No documents uploaded for this application yet.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($documents as $doc): 
                    $fileExt = pathinfo($doc['file_name'] ?? '', PATHINFO_EXTENSION);
                    $isPdf = strtolower($fileExt) === 'pdf';
                    $isImage = in_array(strtolower($fileExt), ['jpg', 'jpeg', 'png', 'gif']);
                    $filePath = htmlspecialchars($doc['file_path'] ?? '');
                    $fileName = htmlspecialchars($doc['file_name'] ?? 'Document');
                    $docType = htmlspecialchars($doc['document_type'] ?? 'Document');
                ?>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-file-<?= $isPdf ? 'pdf' : ($isImage ? 'image' : 'alt') ?> me-2 text-primary"></i>
                                    <?= $fileName ?>
                                </h6>
                                <p class="card-text text-muted small">
                                    <strong>Type:</strong> <?= ucfirst(str_replace('_', ' ', $docType)) ?><br>
                                    <strong>Uploaded:</strong> <?= !empty($doc['uploaded_at']) ? date('d M Y H:i', strtotime($doc['uploaded_at'])) : 'N/A' ?>
                                </p>
                                
                                <!-- Document Preview -->
                                <?php if ($isPdf || $isImage): ?>
                                    <div class="document-preview mb-2" style="max-height: 200px; overflow: hidden;">
                                        <?php if ($isPdf): ?>
                                            <embed src="<?= $filePath ?>" type="application/pdf" width="100%" height="200px" />
                                        <?php elseif ($isImage): ?>
                                            <img src="<?= $filePath ?>" alt="<?= $fileName ?>" class="img-fluid" style="max-height: 200px;" />
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Action Buttons -->
                                <div class="btn-group" role="group">
                                    <a href="<?= '/uploads/'.$filePath ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i> View
                                    </a>
                                    <a href="<?= '/uploads/'.$filePath ?>" download="<?= $fileName ?>" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-download me-1"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        
            <!-- Document Summary Table -->
            <div class="mt-4">
                <h6 class="text-muted mb-3">Document Summary</h6>
                <table class="table table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Document Name</th>
                            <th>Type</th>
                            <th>Upload Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $doc): 
                            $filePath = htmlspecialchars($doc['file_path'] ?? '');
                            $fileName = htmlspecialchars($doc['file_name'] ?? 'Document');
                            $docType = htmlspecialchars($doc['document_type'] ?? 'Document');
                        ?>
                            <tr>
                                <td>
                                    <i class="fas fa-file-alt me-2 text-muted"></i>
                                    <?= $fileName ?>
                                </td>
                                <td><span class="badge bg-secondary"><?= ucfirst(str_replace('_', ' ', $docType)) ?></span></td>
                                <td><?= !empty($doc['uploaded_at']) ? date('d M Y', strtotime($doc['uploaded_at'])) : 'N/A' ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= '/uploads/'. $filePath ?>" target="_blank" class="btn btn-outline-primary btn-sm" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= $filePath ?>" download="<?= $fileName ?>" class="btn btn-outline-success btn-sm" title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Step 5: Review & Actions -->
    <div class="tab-pane fade" id="step5" role="tabpanel">
        <div class="row">
            <div class="col-md-12">
                <h6 class="text-muted mb-3">Application Review Summary</h6>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <p class="mb-1"><strong>Protocol Number:</strong></p>
                                <p class="text-muted"><?= htmlspecialchars($application['protocol_number'] ?? 'N/A') ?></p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1"><strong>Research Type:</strong></p>
                                <p class="text-muted"><?= htmlspecialchars($application['research_type']) ?></p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1"><strong>Current Status:</strong></p>
                                <p class="text-muted">
                                    <?php $statusClass = match($application['status'] ?? '') {
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        'pending' => 'warning',
                                        'under_review' => 'info',
                                        default => 'secondary'
                                    }; ?>
                                    <span class="badge bg-<?= $statusClass ?>"><?= htmlspecialchars(ucfirst($application['status'] ?? 'Unknown')) ?></span>
                                </p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-4">
                                <p class="mb-1"><strong>Submitted:</strong></p>
                                <p class="text-muted"><?= !empty($application['updated_at']) ? date('d M Y', strtotime($application['updated_at'])) : 'N/A' ?></p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1"><strong>Last Updated:</strong></p>
                                <p class="text-muted"><?= !empty($application['updated_at']) ? date('d M Y H:i', strtotime($application['updated_at'])) : 'N/A' ?></p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1"><strong>Documents:</strong></p>
                                <p class="text-muted"><?= count($documents) ?> file(s) attached</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h6 class="text-muted mb-3">Available Actions</h6>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-primary" onclick="assignReviewer(<?= $application['id'] ?>, '<?= htmlspecialchars($application['protocol_number'] ?? '') ?>', '<?= htmlspecialchars($application['study_title'] ?? '') ?>')">
                        <i class="fas fa-user-plus me-1"></i> Assign Reviewer
                    </button>
                    <button type="button" class="btn btn-info" onclick="viewApplicationHistory(<?= $application['id'] ?>)">
                        <i class="fas fa-history me-1"></i> View History
                    </button>
                    <button type="button" class="btn btn-success" onclick="approveApplication(<?= $application['id'] ?>)">
                        <i class="fas fa-check me-1"></i> Approve
                    </button>
                    <button type="button" class="btn btn-warning" onclick="requestChanges(<?= $application['id'] ?>)">
                        <i class="fas fa-edit me-1"></i> Request Changes
                    </button>
                    <button type="button" class="btn btn-danger" onclick="rejectApplication(<?= $application['id'] ?>)">
                        <i class="fas fa-times me-1"></i> Reject
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="generateApprovalLetter(<?= $application['id'] ?>)">
                        <i class="fas fa-file-word me-1"></i> Generate Letter
                    </button>
                </div>
                
                <!-- Quick Status Change -->
                <div class="card mt-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-sliders-h me-2"></i>Quick Status Update</h6>
                    </div>
                    <div class="card-body">
                        <form id="quickStatusForm" class="row g-3">
                            <div class="col-md-8">
                                <label for="newStatus" class="form-label">Change Status To:</label>
                                <select class="form-select" id="newStatus" name="status">
                                    <option value="">Select new status...</option>
                                    <option value="pending">Pending</option>
                                    <option value="under_review">Under Review</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="requires_changes">Requires Changes</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="button" class="btn btn-primary w-100" onclick="updateApplicationStatus(<?= $application['id'] ?>)">
                                    <i class="fas fa-save me-1"></i> Update Status
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.application-stepper .nav-tabs {
    border-bottom: 2px solid #dee2e6;
    background: #f8f9fa;
    padding: 10px 10px 0 10px;
    border-radius: 8px 8px 0 0;
}
.application-stepper .nav-link {
    border: none;
    border-radius: 8px 8px 0 0;
    padding: 10px 20px;
    color: #6c757d;
    font-weight: 500;
    transition: all 0.3s;
}
.application-stepper .nav-link:hover {
    background: #e9ecef;
    color: #495057;
}
.application-stepper .nav-link.active {
    background: #fff;
    color: #0d6efd;
    border: 1px solid #dee2e6;
    border-bottom: none;
}
.tab-pane {
    padding: 20px 0;
}
</style>
<?php
    return ob_get_clean();
}
