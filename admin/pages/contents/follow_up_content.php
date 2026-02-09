<?php
$followUpReports = [];
$pendingReports = [];
$pastReports = [];
$completedReports = [];
$allLetters = [];




try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Fetch follow up reports
    $stmt = $conn->prepare("SELECT * FROM follow_ups ");
    $stmt->execute();
    $followUpReports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // fetch number of pending follow up reports
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM follow_ups WHERE status = 'Pending' ");
    $stmt->execute();
    $pendingReports = $stmt->fetch();

    // fetch number of completed follow up reports
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM follow_ups WHERE status = 'Completed' ");
    $stmt->execute();
    $completedReports = $stmt->fetch();

    // fetch number of past due follow up reports
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM follow_ups WHERE status = 'Past Due' ");
    $stmt->execute();
    $pastReports = $stmt->fetch();

    // fetch number of all follow up reports letters
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM follow_ups");
    $stmt->execute();
    $allLetters = $stmt->fetch();
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['error_message'] = 'Database connection error';
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    $_SESSION['error_message'] = $e->getMessage();
}


?>
<!-- Follow-Up Manager Content -->
<div class="content-wrapper">
    <!-- Page Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="page-header-card">
                        <div class="d-flex align-items-center">
                            <div class="header-icon-wrapper">
                                <i class="fas fa-envelope-open-text"></i>
                            </div>
                            <div class="header-content">
                                <h4 class="page-title">Follow-Up Manager</h4>
                                <p class="page-subtitle">Track and manage letter responses and follow-ups</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="container-fluid">
            <!-- Filter Cards -->
            <div class="filter-section">
                <div class="card-body">
                    <div class="filter-options">
                        <div class="filter-buttons">
                            <button class="filter-btn active">
                                <i class="fas fa-clock me-2"></i>
                                Response Required - Waiting
                                <span class="badge bg-warning ms-2"><?php echo ($pendingReports['count'])?></span>
                            </button>
                            <button class="filter-btn">
                                <i class="fas fa-check-circle me-2"></i>
                                Response Required - Completed
                                <span class="badge bg-success ms-2"><?php echo ($completedReports['count'])?></span>
                            </button>
                            <button class="filter-btn">
                                <i class="fas fa-calendar-times me-2"></i>
                                Response Required - Past Due
                                <span class="badge bg-danger ms-2"><?php echo ($pastReports['count'])?></span>
                            </button>
                            <button class="filter-btn">
                                <i class="fas fa-envelope me-2"></i>
                                All Letters
                                <span class="badge bg-primary ms-2"><?php echo ($allLetters['count'])?></span>
                            </button>
                        </div>

                        <div class="date-filter mt-4">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <label class="form-label fw-semibold mb-0">For follow-up on or before</label>
                                </div>
                                <div class="col-auto">
                                    <div class="input-group">
                                        <input type="date" class="form-control" value="2025-11-12">
                                        <button class="btn btn-outline-primary">
                                            <i class="fas fa-calendar-alt"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-primary">
                                        <i class="fas fa-sync-alt me-1"></i> Apply Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="premium-card">
                        <div class="card-body">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="Search by IRB#, PI Name, or Letter Type...">
                                <button class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i> Search
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Letters Table -->
            <div class="premium-card mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-list-alt me-2 text-primary"></i>
                        Letters Requiring Follow-Up
                    </h6>
                    <div class="d-flex align-items-center">
                        <span class="text-white me-3">Showing 10 of 24 letters</span>
                        <button class="btn btn-sm btn-outline-default text-white me-2">
                            <i class="fas fa-download  me-1"></i> Export
                        </button>
                        <button class="btn btn-sm btn-primary">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-hover table-premium mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="120px">
                                        IRB#
                                    </th>
                                    <th width="100px">Follow Up?</th>
                                    <th>Follow Up Date</th>
                                    <th>Letter</th>
                                    <th>To</th>
                                    <th width="120px">Date Sent</th>
                                    <th width="120px">Due By</th>
                                    <th width="80px">Status</th>
                                    <th width="100px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="premium-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0 fw-bold">Bulk Actions</h6>
                                    <small class="text-muted">Apply actions to selected letters</small>
                                </div>
                                <div>
                                    <button class="btn btn-success me-2">
                                        <i class="fas fa-paper-plane me-1"></i> Send Follow-Up Letter
                                    </button>
                                    <button class="btn btn-primary me-2">
                                        <i class="fas fa-file-pdf me-1"></i> Generate Follow-Up Report
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        fetchFollowUps();
    });

    function fetchFollowUps() {
        fetch('/admin/handlers/fetch_follow_ups.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateTable(data.data);
                } else {
                    console.error('Error fetching follow-ups:', data.message);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
            });
    }

    function populateTable(followUps) {
        const tbody = document.querySelector('.table tbody');
        tbody.innerHTML = '';

        followUps.forEach(followUp => {
            const row = document.createElement('tr');

            // IRB#
            const irbCell = document.createElement('td');
            irbCell.textContent = followUp.irb_number || '';
            row.appendChild(irbCell);

            // Follow Up?
            const followUpCell = document.createElement('td');
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.checked = followUp.follow_up_required == 1;
            checkbox.disabled = true;
            followUpCell.appendChild(checkbox);
            row.appendChild(followUpCell);

            // Follow Up Date
            const followDateCell = document.createElement('td');
            followDateCell.textContent = followUp.follow_up_date || '';
            row.appendChild(followDateCell);

            // Letter
            const letterCell = document.createElement('td');
            letterCell.textContent = followUp.letter_type || '';
            row.appendChild(letterCell);

            // To
            const toCell = document.createElement('td');
            toCell.textContent = followUp.sent_to || '';
            row.appendChild(toCell);

            // Date Sent
            const sentCell = document.createElement('td');
            sentCell.textContent = followUp.date_sent || '';
            row.appendChild(sentCell);

            // Due By
            const dueCell = document.createElement('td');
            dueCell.textContent = followUp.due_by || '';
            row.appendChild(dueCell);

            // Status
            const statusCell = document.createElement('td');
            const statusBadge = document.createElement('span');
            statusBadge.className = 'badge';
            const status = followUp.status || '';
            if (status.toLowerCase() === 'waiting') {
                statusBadge.className += ' bg-warning';
            } else if (status.toLowerCase() === 'past due') {
                statusBadge.className += ' bg-danger';
            } else if (status.toLowerCase() === 'completed') {
                statusBadge.className += ' bg-success';
            } else {
                statusBadge.className += ' bg-secondary';
            }
            statusBadge.textContent = status;
            statusCell.appendChild(statusBadge);
            row.appendChild(statusCell);

            // Actions
            const actionsCell = document.createElement('td');
            const viewBtn = document.createElement('button');
            viewBtn.className = 'btn btn-sm btn-outline-primary me-1';
            viewBtn.textContent = 'View';
            viewBtn.onclick = () => viewFollowUp(followUp.id);
            actionsCell.appendChild(viewBtn);
            row.appendChild(actionsCell);

            tbody.appendChild(row);
        });
    }

    function viewFollowUp(id) {
        // Placeholder for view action
        alert('View follow-up ID: ' + id);
    }
</script>

<style>
    .follow-up-manager {
        padding: 20px 0;
    }

    .filter-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .filter-buttons {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 10px;
    }

    .filter-btn {
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        text-align: left;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        font-weight: 500;
    }

    .filter-btn:hover {
        border-color: var(--royal-blue);
        transform: translateY(-2px);
    }

    .filter-btn.active {
        border-color: var(--royal-blue);
        background-color: #e8f0fe;
        color: var(--royal-blue);
    }

    .main-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    /* .table th {
    background-color: #f8f9fa;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
} */

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .table-warning {
        background-color: #fff3cd !important;
    }

    .table-danger {
        background-color: #f8d7da !important;
    }

    .stat-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        color: white;
        font-size: 1.5rem;
    }

    .form-check-input:checked {
        background-color: var(--royal-blue);
        border-color: var(--royal-blue);
    }

    .form-check-input:focus {
        border-color: var(--royal-blue);
        box-shadow: 0 0 0 0.2rem rgba(26, 86, 219, 0.25);
    }

    .badge {
        font-size: 0.75rem;
        font-weight: 500;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .filter-buttons {
            grid-template-columns: 1fr;
        }

        .table-responsive {
            font-size: 0.875rem;
        }

        .action-buttons {
            flex-direction: column;
            gap: 10px;
        }

        .action-buttons .btn {
            width: 100%;
        }
    }
</style>
