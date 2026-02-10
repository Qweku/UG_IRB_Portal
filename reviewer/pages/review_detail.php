<?php

// NOTE: Authentication is already handled by index.php router
// No duplicate auth check needed here

$userId = $_SESSION['user_id'] ?? 0;
$userName = $_SESSION['full_name'] ?? 'Reviewer';

// Get application ID from query params
$applicationId = $_GET['id'] ?? 0;

if (!$applicationId) {
    header('Location: /reviewer-dashboard/reviews');
    exit;
}

// Get application details
$application = getApplicationForReview($applicationId);

if (!$application) {
    header('Location: /reviewer-dashboard/reviews');
    exit;
}

// Get existing comments
$comments = getApplicationComments($applicationId);

// Get review details
$reviewDetails = getReviewDetails($applicationId, $userId);

// Handle comment submission
$commentError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_comment') {
    $commentData = [
        'application_id' => $applicationId,
        'reviewer_id' => $userId,
        'section' => $_POST['section'] ?? 'general',
        'comment' => $_POST['comment'] ?? ''
    ];
    
    if (!empty($commentData['comment'])) {
        if (addReviewComment($commentData)) {
            // Update review status to in_progress if not already
            if ($reviewDetails && $reviewDetails['status'] === 'assigned') {
                updateReviewStatus($reviewDetails['id'], 'in_progress');
            }
            header('Location: /reviewer-dashboard/review?id=' . $applicationId);
            exit;
        } else {
            $commentError = 'Failed to add comment. Please try again.';
        }
    } else {
        $commentError = 'Comment cannot be empty.';
    }
}

?>
<style>
/* Review Detail Page Styles */
.review-header-section {
    background: linear-gradient(135deg, #1a5276 0%, #2980b9 100%);
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 24px;
    box-shadow: 0 10px 40px rgba(41, 128, 185, 0.2);
}

.review-status-badge {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 25px;
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 12px;
}

.review-status-badge.pending {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.review-status-badge.in_progress {
    background: rgba(255, 193, 7, 0.9);
    color: #333;
}

.review-status-badge.completed {
    background: rgba(40, 167, 69, 0.9);
    color: white;
}

/* Application Info Card */
.app-info-card {
    border: none;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    margin-bottom: 24px;
}

.app-info-card .card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: none;
    padding: 20px;
}

.app-info-card .card-body {
    padding: 24px;
}

/* Section Accordion */
.review-section {
    border: 1px solid #e9ecef;
    border-radius: 12px;
    margin-bottom: 16px;
    overflow: hidden;
}

.review-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    background: #f8f9fa;
    cursor: pointer;
    transition: all 0.3s ease;
}

.review-section-header:hover {
    background: #e9ecef;
}

.review-section-title {
    font-weight: 600;
    color: #2c3e50;
}

.review-section-content {
    padding: 20px;
    display: none;
}

.review-section-content.show {
    display: block;
}

.review-section-content .info-row {
    display: flex;
    padding: 12px 0;
    border-bottom: 1px solid #e9ecef;
}

.review-section-content .info-row:last-child {
    border-bottom: none;
}

.review-section-content .info-label {
    font-weight: 600;
    color: #6c757d;
    width: 200px;
    flex-shrink: 0;
}

.review-section-content .info-value {
    color: #2c3e50;
}

/* Comment Section */
.comment-section {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    margin-top: 20px;
}

.comment-item {
    background: white;
    border-radius: 10px;
    padding: 16px;
    margin-bottom: 12px;
    border-left: 4px solid #2980b9;
}

.comment-item .comment-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.comment-item .comment-author {
    font-weight: 600;
    color: #2c3e50;
}

.comment-item .comment-date {
    font-size: 12px;
    color: #6c757d;
}

.comment-item .comment-section-label {
    font-size: 11px;
    text-transform: uppercase;
    color: #2980b9;
    margin-bottom: 4px;
    font-weight: 600;
}

/* Decision Panel */
.decision-panel {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    position: sticky;
    top: 20px;
}

.decision-title {
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 20px;
    text-align: center;
}

.decision-btn {
    width: 100%;
    padding: 14px;
    border-radius: 12px;
    font-weight: 600;
    margin-bottom: 12px;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.decision-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.decision-btn.approve {
    background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    color: white;
}

.decision-btn.changes {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
}

.decision-btn.reject {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: white;
}

.decision-btn.active {
    border-color: #2c3e50;
    transform: scale(1.02);
}

/* Decision Notes */
.decision-notes {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
}

.decision-notes textarea {
    border-radius: 12px;
    border: 2px solid #e9ecef;
    padding: 16px;
    resize: vertical;
    min-height: 120px;
}

.decision-notes textarea:focus {
    border-color: #2980b9;
    box-shadow: 0 0 0 3px rgba(41, 128, 185, 0.1);
}

.submit-decision-btn {
    width: 100%;
    padding: 16px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 16px;
    margin-top: 16px;
    background: linear-gradient(135deg, #1a5276 0%, #2980b9 100%);
    color: white;
    border: none;
    transition: all 0.3s ease;
}

.submit-decision-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(41, 128, 185, 0.3);
}

.submit-decision-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* Animations */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in {
    animation: fadeIn 0.5s ease forwards;
}
</style>

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3">

            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3 fade-in">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/reviewer-dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/reviewer-dashboard/reviews">Reviews</a></li>
                    <li class="breadcrumb-item active">Application Review</li>
                </ol>
            </nav>

            <!-- Review Header -->
            <div class="review-header-section text-white fade-in">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="review-status-badge <?php echo $reviewDetails['status'] ?? 'pending'; ?>">
                            <?php echo getStatusLabel($application['status'] ?? 'submitted'); ?>
                        </span>
                        <h2 class="mb-2"><?php echo htmlspecialchars($application['study_title'] ?? 'Untitled Application'); ?></h2>
                        <p class="mb-0 opacity-75">
                            <i class="fas fa-user me-2"></i>
                            <?php echo htmlspecialchars($application['applicant_name'] ?? 'Unknown'); ?> | 
                            <i class="fas fa-calendar-alt ms-2 me-2"></i>
                            Submitted: <?php echo date('M d, Y', strtotime($application['submitted_at'] ?? $application['created_at'])); ?>
                        </p>
                    </div>
                    <div class="text-end">
                        <div class="badge bg-light text-dark" style="border-radius: 25px; padding: 10px 20px; font-size: 14px;">
                            <i class="fas fa-tag me-2"></i>
                            <?php echo htmlspecialchars($application['protocol_number'] ?? 'PENDING'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Left Column - Application Details -->
                <div class="col-lg-8">
                    <div class="app-info-card fade-in">
                        <div class="card-header">
                            <h5 class="mb-0" style="font-weight: 700; color: #2c3e50;">
                                <i class="fas fa-file-alt me-2" style="color: #2980b9;"></i>
                                Application Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Protocol Information -->
                            <div class="review-section">
                                <div class="review-section-header" onclick="toggleSection(this)">
                                    <span class="review-section-title">
                                        <i class="fas fa-info-circle me-2"></i>Protocol Information
                                    </span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="review-section-content show">
                                    <div class="info-row">
                                        <span class="info-label">Protocol Number:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($application['protocol_number'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Application Type:</span>
                                        <span class="info-value"><?php echo getApplicationTypeName($application['application_type'] ?? 'student'); ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Version:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($application['version_number'] ?? '1.0'); ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Submitted Date:</span>
                                        <span class="info-value"><?php echo date('M d, Y', strtotime($application['submitted_at'] ?? $application['created_at'])); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Study Information -->
                            <div class="review-section">
                                <div class="review-section-header" onclick="toggleSection(this)">
                                    <span class="review-section-title">
                                        <i class="fas fa-flask me-2"></i>Study Information
                                    </span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="review-section-content show">
                                    <div class="info-row">
                                        <span class="info-label">Study Title:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($application['study_title'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Research Type:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($application['research_type'] ?? 'Not specified'); ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Study Duration:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($application['study_duration_years'] ?? 'N/A'); ?> years</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Start Date:</span>
                                        <span class="info-value"><?php echo $application['study_start_date'] ? date('M d, Y', strtotime($application['study_start_date'])) : 'N/A'; ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">End Date:</span>
                                        <span class="info-value"><?php echo $application['study_end_date'] ? date('M d, Y', strtotime($application['study_end_date'])) : 'N/A'; ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Background & Objectives -->
                            <div class="review-section">
                                <div class="review-section-header" onclick="toggleSection(this)">
                                    <span class="review-section-title">
                                        <i class="fas fa-book me-2"></i>Background & Objectives
                                    </span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="review-section-content">
                                    <h6 class="font-weight-600 mb-3">Abstract</h6>
                                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($application['abstract'] ?? 'No abstract provided.')); ?></p>
                                    
                                    <h6 class="font-weight-600 mb-3 mt-4">Background</h6>
                                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($application['background'] ?? 'No background provided.')); ?></p>
                                    
                                    <h6 class="font-weight-600 mb-3 mt-4">Objectives</h6>
                                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($application['objectives'] ?? 'No objectives provided.')); ?></p>
                                </div>
                            </div>

                            <!-- Methodology -->
                            <div class="review-section">
                                <div class="review-section-header" onclick="toggleSection(this)">
                                    <span class="review-section-title">
                                        <i class="fas fa-cogs me-2"></i>Methodology
                                    </span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="review-section-content">
                                    <h6 class="font-weight-600 mb-3">Study Methods</h6>
                                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($application['methods'] ?? 'No methods provided.')); ?></p>
                                    
                                    <h6 class="font-weight-600 mb-3 mt-4">Data Analysis</h6>
                                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($application['data_analysis'] ?? 'No data analysis provided.')); ?></p>
                                </div>
                            </div>

                            <!-- Ethical Considerations -->
                            <div class="review-section">
                                <div class="review-section-header" onclick="toggleSection(this)">
                                    <span class="review-section-title">
                                        <i class="fas fa-shield-alt me-2"></i>Ethical Considerations
                                    </span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="review-section-content">
                                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($application['ethical_considerations'] ?? 'No ethical considerations provided.')); ?></p>
                                </div>
                            </div>

                            <!-- Prior IRB Review -->
                            <div class="review-section">
                                <div class="review-section-header" onclick="toggleSection(this)">
                                    <span class="review-section-title">
                                        <i class="fas fa-history me-2"></i>Prior IRB Review
                                    </span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="review-section-content">
                                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($application['prior_irb_review'] ?? 'No prior IRB review information provided.')); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Comments Section -->
                    <div class="app-info-card fade-in">
                        <div class="card-header">
                            <h5 class="mb-0" style="font-weight: 700; color: #2c3e50;">
                                <i class="fas fa-comments me-2" style="color: #2980b9;"></i>
                                Review Comments
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Add Comment Form -->
                            <form method="POST" class="mb-4">
                                <input type="hidden" name="action" value="add_comment">
                                <?php echo csrf_field(); ?>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Section</label>
                                    <select name="section" class="form-select" style="border-radius: 10px;">
                                        <option value="general">General Comment</option>
                                        <option value="protocol">Protocol Information</option>
                                        <option value="background">Background & Objectives</option>
                                        <option value="methodology">Methodology</option>
                                        <option value="ethical">Ethical Considerations</option>
                                        <option value="documents">Documents</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Your Comment</label>
                                    <textarea name="comment" class="form-control" rows="4" 
                                              placeholder="Enter your review comment or feedback..." 
                                              style="border-radius: 10px; border: 2px solid #e9ecef;"></textarea>
                                </div>
                                
                                <?php if ($commentError): ?>
                                    <div class="alert alert-danger" style="border-radius: 10px;"><?php echo $commentError; ?></div>
                                <?php endif; ?>
                                
                                <button type="submit" class="btn btn-primary" style="border-radius: 25px; font-weight: 600;">
                                    <i class="fas fa-plus me-2"></i>Add Comment
                                </button>
                            </form>

                            <!-- Existing Comments -->
                            <?php if (!empty($comments)): ?>
                                <div class="mt-4">
                                    <h6 class="fw-semibold mb-3">Previous Comments</h6>
                                    <?php foreach ($comments as $comment): ?>
                                        <div class="comment-item">
                                            <div class="comment-header">
                                                <span class="comment-author"><?php echo htmlspecialchars($comment['reviewer_name'] ?? 'Reviewer'); ?></span>
                                                <span class="comment-date"><?php echo date('M d, Y \a\t g:i A', strtotime($comment['created_at'])); ?></span>
                                            </div>
                                            <?php if ($comment['section']): ?>
                                                <div class="comment-section-label"><?php echo ucfirst(htmlspecialchars($comment['section'])); ?></div>
                                            <?php endif; ?>
                                            <p class="mb-0 mt-2"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-comment-slash" style="font-size: 36px; opacity: 0.3;"></i>
                                    <p class="mt-2 mb-0">No comments yet. Add your first comment above.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Decision Panel -->
                <div class="col-lg-4">
                    <div class="decision-panel fade-in">
                        <h5 class="decision-title">
                            <i class="fas fa-gavel me-2"></i>Review Decision
                        </h5>

                        <form method="POST" id="decisionForm">
                            <input type="hidden" name="decision" id="selectedDecision" value="">
                            <?php echo csrf_field(); ?>

                            <button type="button" class="decision-btn approve" onclick="selectDecision('approved')">
                                <i class="fas fa-check-circle me-2"></i>Approve
                            </button>

                            <button type="button" class="decision-btn changes" onclick="selectDecision('changes_requested')">
                                <i class="fas fa-edit me-2"></i>Request Changes
                            </button>

                            <button type="button" class="decision-btn reject" onclick="selectDecision('rejected')">
                                <i class="fas fa-times-circle me-2"></i>Reject
                            </button>

                            <div class="decision-notes">
                                <label class="form-label fw-semibold">Decision Notes</label>
                                <textarea name="decision_notes" id="decisionNotes" 
                                          placeholder="Provide detailed reasons for your decision..."></textarea>
                            </div>

                            <button type="submit" class="submit-decision-btn" id="submitDecision" disabled>
                                <i class="fas fa-paper-plane me-2"></i>Submit Decision
                            </button>
                        </form>

                        <div class="mt-4 pt-4 border-top">
                            <h6 class="fw-semibold mb-3">Application Summary</h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Applicant:</span>
                                <span class="fw-600"><?php echo htmlspecialchars($application['applicant_name'] ?? 'Unknown'); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Email:</span>
                                <span class="fw-600"><?php echo htmlspecialchars($application['applicant_email'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Submitted:</span>
                                <span class="fw-600"><?php echo date('M d, Y', strtotime($application['submitted_at'] ?? $application['created_at'])); ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Total Comments:</span>
                                <span class="fw-600"><?php echo count($comments); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function toggleSection(header) {
    const content = header.nextElementSibling;
    const icon = header.querySelector('.fa-chevron-down');
    
    content.classList.toggle('show');
    icon.classList.toggle('fa-chevron-up');
}

function selectDecision(decision) {
    document.getElementById('selectedDecision').value = decision;
    
    // Update button styles
    document.querySelectorAll('.decision-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Add active class to selected button
    event.target.closest('.decision-btn').classList.add('active');
    
    // Enable submit button
    document.getElementById('submitDecision').disabled = false;
    
    // Update button text
    const decisionLabels = {
        'approved': 'Approve',
        'changes_requested': 'Request Changes',
        'rejected': 'Reject'
    };
    document.getElementById('submitDecision').innerHTML = '<i class="fas fa-paper-plane me-2"></i>Submit Decision - ' + decisionLabels[decision];
}

// Handle decision form submission
document.getElementById('decisionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const decision = document.getElementById('selectedDecision').value;
    const notes = document.getElementById('decisionNotes').value;
    
    if (!decision) {
        alert('Please select a decision.');
        return;
    }
    
    if (confirm('Are you sure you want to submit this decision? This action cannot be undone.')) {
        fetch('/reviewer/handlers/submit_decision.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'application_id=<?php echo $applicationId; ?>&decision=' + decision + '&decision_notes=' + encodeURIComponent(notes)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Decision submitted successfully!');
                window.location.href = '/reviewer-dashboard/reviews';
            } else {
                alert('Error: ' + (data.message || 'Failed to submit decision'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
});
</script>
