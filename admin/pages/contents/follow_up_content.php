<!-- Follow-Up Manager Content -->
<div class="follow-up-manager">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Follow-Up Manager</h2>
            <p class="text-muted mb-0">Track and manage letter responses and follow-ups</p>
        </div>
        <div class="badge bg-primary fs-6">
            <i class="fas fa-envelope me-1"></i> Letter Tracking
        </div>
    </div>

    <!-- Filter Cards -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card filter-card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-filter me-2 text-primary"></i>
                        Letter Display Options
                    </h6>
                </div>
                <div class="card-body">
                    <div class="filter-options">
                        <div class="filter-buttons">
                            <button class="filter-btn active">
                                <i class="fas fa-clock me-2"></i>
                                Response Required - Waiting
                                <span class="badge bg-warning ms-2">12</span>
                            </button>
                            <button class="filter-btn">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                All Response Required
                                <span class="badge bg-primary ms-2">24</span>
                            </button>
                            <button class="filter-btn">
                                <i class="fas fa-calendar-times me-2"></i>
                                Response Required - Past Due
                                <span class="badge bg-danger ms-2">8</span>
                            </button>
                            <button class="filter-btn">
                                <i class="fas fa-envelope-open me-2"></i>
                                All Letters
                                <span class="badge bg-secondary ms-2">156</span>
                            </button>
                        </div>
                        
                        <div class="date-filter mt-3">
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
        </div>
    </div>

    <!-- Search Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
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
    <div class="card main-card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="fas fa-list-alt me-2 text-primary"></i>
                Letters Requiring Follow-Up
            </h6>
            <div class="d-flex align-items-center">
                <span class="text-muted me-3">Showing 10 of 24 letters</span>
                <button class="btn btn-sm btn-outline-primary me-2">
                    <i class="fas fa-download me-1"></i> Export
                </button>
                <button class="btn btn-sm btn-primary">
                    <i class="fas fa-sync-alt me-1"></i> Refresh
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
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
                        <tr class="table-warning">
                            <td>
                               
                                <p>112/12-13</p>
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" checked>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-warning">04/15/2015</span>
                            </td>
                            <td>
                                <i class="fas fa-file-contract me-2 text-primary"></i>
                                Continuing Review Notice
                            </td>
                            <td>PI Post</td>
                            <td>04/13/2015</td>
                            <td>
                                <span class="text-danger">04/22/2015</span>
                            </td>
                            <td>
                                <span class="badge bg-warning">Waiting</span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="table-danger">
                            <td>
                               
                                <p>101/15-16</p>
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" checked>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-danger">06/03/2018</span>
                            </td>
                            <td>
                                <i class="fas fa-file-contract me-2 text-primary"></i>
                                Continuing Review Notice
                            </td>
                            <td>PI Post</td>
                            <td>06/04/2018</td>
                            <td>
                                <span class="text-danger">06/18/2018</span>
                            </td>
                            <td>
                                <span class="badge bg-danger">Past Due</span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                               
                                <p>087/11-12</p>
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox">
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">04/17/2013</span>
                            </td>
                            <td>
                                <i class="fas fa-file-contract me-2 text-primary"></i>
                                Continuing Review Notice
                            </td>
                            <td>PI Post</td>
                            <td>04/15/2013</td>
                            <td>04/24/2013</td>
                            <td>
                                <span class="badge bg-success">Completed</span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="table-warning">
                            <td>
                                
                                <p>077/14-15</p>
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" checked>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-warning">04/13/2016</span>
                            </td>
                            <td>
                                <i class="fas fa-file-contract me-2 text-primary"></i>
                                Continuing Review Notice
                            </td>
                            <td>PI Post</td>
                            <td>04/06/2016</td>
                            <td>
                                <span class="text-warning">04/20/2016</span>
                            </td>
                            <td>
                                <span class="badge bg-warning">Waiting</span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                               
                                <p>076/13-14</p>
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox">
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">04/12/2017</span>
                            </td>
                            <td>
                                <i class="fas fa-file-contract me-2 text-primary"></i>
                                Continuing Review Notice
                            </td>
                            <td>PI Post</td>
                            <td>06/06/2017</td>
                            <td>04/19/2017</td>
                            <td>
                                <span class="badge bg-success">Completed</span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
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
                            <button class="btn btn-secondary">
                                <i class="fas fa-undo me-1"></i> Return
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <div class="stat-icon bg-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="text-warning mt-3">12</h3>
                    <p class="text-muted mb-0">Waiting Response</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <div class="stat-icon bg-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3 class="text-danger mt-3">8</h3>
                    <p class="text-muted mb-0">Past Due</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <div class="stat-icon bg-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="text-success mt-3">136</h3>
                    <p class="text-muted mb-0">Completed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <div class="stat-icon bg-primary">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3 class="text-primary mt-3">24</h3>
                    <p class="text-muted mb-0">Require Action</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.follow-up-manager {
    padding: 20px 0;
}

.filter-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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