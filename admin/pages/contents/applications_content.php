<!-- Applications Content -->
<div class="content-wrapper fade-in-up">
    <div class="content-header">
        <div class="page-header-card">
            <div class="d-flex align-items-center">
                <div class="page-icon me-3">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div>
                    <h2 class="page-title">Applications Review</h2>
                    <p class="page-subtitle">Review and assign incoming applications to reviewers</p>
                </div>
            </div>
        </div>
    </div>
    <div class="content-body">
        <div class="container-fluid">
            <!-- Stats Cards Row -->
            <div class="row mb-4">
                <!-- Total Applications -->
                <div class="col-md-3">
                    <div class="stats-card-premium" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stats-label">Total Applications</div>
                                    <div class="stats-number" id="totalApplications">0</div>
                                </div>
                                <div class="stats-icon"><i class="fas fa-file-alt"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Pending Review -->
                <div class="col-md-3">
                    <div class="stats-card-premium" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stats-label">Pending Review</div>
                                    <div class="stats-number" id="pendingApplications">0</div>
                                </div>
                                <div class="stats-icon"><i class="fas fa-clock"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Assigned -->
                <div class="col-md-3">
                    <div class="stats-card-premium" style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stats-label">Assigned</div>
                                    <div class="stats-number" id="assignedApplications">0</div>
                                </div>
                                <div class="stats-icon"><i class="fas fa-user-check"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Reviewed -->
                <div class="col-md-3">
                    <div class="stats-card-premium" style="background: linear-gradient(135deg, #27ae60 0%, #229954 100%);">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stats-label">Reviewed</div>
                                    <div class="stats-number" id="reviewedApplications">0</div>
                                </div>
                                <div class="stats-icon"><i class="fas fa-check-circle"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Row -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card premium-card">
                        <div class="card-body">
                            <div class="row align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Status Filter</label>
                                    <select class="form-select" id="statusFilter">
                                        <option value="">All Statuses</option>
                                        <option value="pending">Pending Review</option>
                                        <option value="assigned">Assigned to Reviewer</option>
                                        <option value="under_review">Under Review</option>
                                        <option value="reviewed">Review Completed</option>
                                        <option value="approved">Approved</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">From Date</label>
                                    <input type="date" class="form-control" id="dateFrom">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">To Date</label>
                                    <input type="date" class="form-control" id="dateTo">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Search</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="searchInput" placeholder="Search by protocol, title, PI...">
                                        <button class="btn btn-primary" type="button" onclick="fetchApplications()">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Applications Table -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card premium-card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Applications List</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-premium">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Protocol #</th>
                                            <th>Study Title</th>
                                            <th>Principal Investigator</th>
                                            <th>Submission Date</th>
                                            <th>Status</th>
                                            <th>Assigned Reviewers</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="applicationsTableBody">
                                        <!-- Populated via JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                            <!-- Pagination -->
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="text-muted" id="tableInfo">Showing 0 of 0 applications</div>
                                <nav>
                                    <ul class="pagination mb-0" id="pagination">
                                        <!-- Populated via JavaScript -->
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Reviewer Modal -->
<div class="modal fade" id="assignReviewerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Assign Reviewers</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="assignReviewerForm">
                    <input type="hidden" id="applicationId">
                    <div class="mb-3">
                        <label class="form-label">Application</label>
                        <input type="text" class="form-control" id="applicationInfo" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Reviewers <span class="text-danger">*</span></label>
                        <select class="form-select" id="reviewerSelect" multiple required size="5">
                            <!-- Populated via JavaScript -->
                        </select>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple reviewers</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" class="form-control" id="reviewDueDate">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assignment Notes</label>
                        <textarea class="form-control" id="assignmentNotes" rows="3" placeholder="Optional notes for reviewers..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="assignReviewers()">
                    <i class="fas fa-check me-2"></i>Assign Reviewers
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Application Modal -->
<div class="modal fade" id="viewApplicationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2"></i>Application Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Application details populated via JavaScript -->
                <div id="applicationDetails"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printApplication()">
                    <i class="fas fa-print me-2"></i>Print
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    fetchApplications();
    fetchReviewers();
    
    // Set default date to 2 weeks from now
    const defaultDueDate = new Date();
    defaultDueDate.setDate(defaultDueDate.getDate() + 14);
    document.getElementById('reviewDueDate').value = defaultDueDate.toISOString().split('T')[0];
    
    // Filter event listeners
    document.getElementById('statusFilter').addEventListener('change', fetchApplications);
    document.getElementById('dateFrom').addEventListener('change', fetchApplications);
    document.getElementById('dateTo').addEventListener('change', fetchApplications);
    document.getElementById('searchInput').addEventListener('keyup', debounce(fetchApplications, 300));
});

let currentPage = 1;
const itemsPerPage = 10;

function fetchApplications() {
    const status = document.getElementById('statusFilter').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    const search = document.getElementById('searchInput').value;
    
    fetch('/admin/handlers/fetch_applications.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            status: status,
            date_from: dateFrom,
            date_to: dateTo,
            search: search,
            page: currentPage,
            limit: itemsPerPage
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            updateStats(data.stats);
            populateTable(data.data);
            updatePagination(data.total, data.page, data.limit);
        } else {
            showNotification('error', data.message || 'Failed to fetch applications');
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        showNotification('error', 'An error occurred while fetching applications');
    });
}

function updateStats(stats) {
    document.getElementById('totalApplications').textContent = stats.total || 0;
    document.getElementById('pendingApplications').textContent = stats.pending || 0;
    document.getElementById('assignedApplications').textContent = stats.assigned || 0;
    document.getElementById('reviewedApplications').textContent = stats.reviewed || 0;
}

function populateTable(applications) {
    const tbody = document.getElementById('applicationsTableBody');
    tbody.innerHTML = '';
    
    if (!applications || applications.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No applications found</td></tr>';
        return;
    }
    
    applications.forEach(app => {
        const statusBadge = getStatusBadge(app.status);
        const reviewersHtml = app.assigned_reviewers && app.assigned_reviewers.length > 0 
            ? app.assigned_reviewers.map(r => `<span class="badge bg-info me-1">${r.name}</span>`).join('')
            : '<span class="text-muted">Not assigned</span>';
        
        const row = `
            <tr>
                <td><strong>${app.protocol_number || 'N/A'}</strong></td>
                <td>${app.study_title || 'Untitled Study'}</td>
                <td>${app.pi_name || 'N/A'}</td>
                <td>${formatDate(app.updated_at)}</td>
                <td>${statusBadge}</td>
                <td>${reviewersHtml}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="viewApplication(${app.id})" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-outline-success" onclick="openAssignModal(${app.id}, '${app.protocol_number}', '${app.study_title}')" title="Assign Reviewer">
                            <i class="fas fa-user-plus"></i>
                        </button>
                        <button class="btn btn-outline-secondary" onclick="downloadDocuments(${app.id})" title="Download Documents">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
        tbody.innerHTML += row;
    });
    
    document.getElementById('tableInfo').textContent = `Showing ${applications.length} applications`;
}

function getStatusBadge(status) {
    const badges = {
        'pending': '<span class="badge bg-warning">Pending Review</span>',
        'assigned': '<span class="badge bg-info">Assigned</span>',
        'under_review': '<span class="badge bg-primary">Under Review</span>',
        'reviewed': '<span class="badge bg-success">Reviewed</span>',
        'approved': '<span class="badge bg-success">Approved</span>',
        'rejected': '<span class="badge bg-danger">Rejected</span>',
        'needs_revision': '<span class="badge bg-warning">Needs Revision</span>'
    };
    return badges[status] || `<span class="badge bg-secondary">${status || 'Unknown'}</span>`;
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

function updatePagination(total, page, limit) {
    const totalPages = Math.ceil(total / limit);
    const pagination = document.getElementById('pagination');
    pagination.innerHTML = '';
    
    if (totalPages <= 1) return;
    
    // Previous button
    pagination.innerHTML += `
        <li class="page-item ${page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="goToPage(${page - 1}); return false;">Previous</a>
        </li>
    `;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= page - 1 && i <= page + 1)) {
            pagination.innerHTML += `
                <li class="page-item ${i === page ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="goToPage(${i}); return false;">${i}</a>
                </li>
            `;
        } else if (i === page - 2 || i === page + 2) {
            pagination.innerHTML += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    // Next button
    pagination.innerHTML += `
        <li class="page-item ${page === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="goToPage(${page + 1}); return false;">Next</a>
        </li>
    `;
}

function goToPage(page) {
    currentPage = page;
    fetchApplications();
}

function fetchReviewers() {
    fetch('/admin/handlers/fetch_reviewers.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                populateReviewersSelect(data.data);
            }
        })
        .catch(error => console.error('Error fetching reviewers:', error));
}

function populateReviewersSelect(reviewers) {
    const select = document.getElementById('reviewerSelect');
    select.innerHTML = '';
    
    reviewers.forEach(reviewer => {
        const option = document.createElement('option');
        option.value = reviewer.id;
        option.textContent = `${reviewer.full_name} (${reviewer.department || 'N/A'}) - ${reviewer.active_reviews} active reviews`;
        option.dataset.activeReviews = reviewer.active_reviews;
        select.appendChild(option);
    });
}

function openAssignModal(applicationId, protocolNumber, studyTitle) {
    document.getElementById('applicationId').value = applicationId;
    document.getElementById('applicationInfo').value = `${protocolNumber} - ${studyTitle}`;
    
    // Reset reviewer selection
    document.getElementById('reviewerSelect').selectedIndex = -1;
    
    // Set minimum due date to tomorrow
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    document.getElementById('reviewDueDate').min = tomorrow.toISOString().split('T')[0];
    
    const modal = new bootstrap.Modal(document.getElementById('assignReviewerModal'));
    modal.show();
}

function assignReviewers() {
    const applicationId = document.getElementById('applicationId').value;
    const reviewerSelect = document.getElementById('reviewerSelect');
    const selectedReviewers = Array.from(reviewerSelect.selectedOptions).map(opt => opt.value);
    const dueDate = document.getElementById('reviewDueDate').value;
    const notes = document.getElementById('assignmentNotes').value;
    
    if (selectedReviewers.length === 0) {
        showNotification('warning', 'Please select at least one reviewer');
        return;
    }
    
    fetch('/admin/handlers/assign_reviewer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            application_id: applicationId,
            reviewers: selectedReviewers,
            due_date: dueDate,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showNotification('success', 'Reviewers assigned successfully');
            const modal = bootstrap.Modal.getInstance(document.getElementById('assignReviewerModal'));
            modal.hide();
            fetchApplications();
        } else {
            showNotification('error', data.message || 'Failed to assign reviewers');
        }
    })
    .catch(error => {
        console.error('Assignment error:', error);
        showNotification('error', 'An error occurred while assigning reviewers');
    });
}

function viewApplication(applicationId) {
    // Implementation for viewing full application details
    fetch(`/admin/handlers/get_application_details.php?id=${applicationId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('applicationDetails').innerHTML = data.html;
                const modal = new bootstrap.Modal(document.getElementById('viewApplicationModal'));
                modal.show();
            } else {
                showNotification('error', data.message || 'Failed to load application details');
            }
        })
        .catch(error => {
            console.error('Error loading application:', error);
            showNotification('error', 'An error occurred while loading application details');
        });
}

function downloadDocuments(applicationId) {
    window.location.href = `/admin/handlers/download_application_docs.php?id=${applicationId}`;
}

function printApplication() {
    const content = document.getElementById('applicationDetails').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
        <head>
            <title>Application Details</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { padding: 20px; }
                @media print {
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            ${content}
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

function showNotification(type, message) {
    // Use existing notification system if available
    if (typeof showToastNotification === 'function') {
        showToastNotification(type, message);
    } else {
        // Fallback notification
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : type === 'warning' ? 'warning' : 'info'} position-fixed top-0 end-0 m-3`;
        notification.style.zIndex = '9999';
        notification.style.animation = 'slideIn 0.3s ease';
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'times-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
            ${message}
        `;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 5000);
    }
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>

<style>
/* Additional styles for Applications Review page */
.fade-in-up {
    animation: fadeInUp 0.5s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.stats-card-premium {
    border-radius: 12px;
    color: white;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stats-card-premium:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.stats-card-premium .stats-label {
    font-size: 0.85rem;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stats-card-premium .stats-number {
    font-size: 2rem;
    font-weight: 700;
    margin-top: 5px;
}

.stats-card-premium .stats-icon {
    font-size: 2.5rem;
    opacity: 0.3;
}

.premium-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
    transition: box-shadow 0.3s ease;
}

.premium-card:hover {
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
}

.table-premium th {
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #e9ecef;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

.table-premium td {
    vertical-align: middle;
}

.page-header-card {
    background: white;
    padding: 25px 30px;
    border-radius: 12px;
    margin-bottom: 25px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
}

.page-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.page-subtitle {
    color: #6c757d;
    margin: 5px 0 0 0;
    font-size: 0.9rem;
}

.modal-header {
    border-radius: 0;
    border-bottom: none;
}

.modal-footer {
    border-top: 1px solid #e9ecef;
}

.pagination .page-link {
    border: none;
    padding: 8px 15px;
    color: #3498db;
    border-radius: 8px;
    margin: 0 3px;
    transition: all 0.3s ease;
}

.pagination .page-link:hover {
    background: #e3f2fd;
    color: #2980b9;
}

.pagination .page-item.active .page-link {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.4);
}

.pagination .page-item.disabled .page-link {
    color: #adb5bd;
    background: transparent;
}
</style>
