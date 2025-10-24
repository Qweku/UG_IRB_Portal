<!-- Task Managers Content -->
<div class="">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Task Managers</h2>
        <button class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Create New Task
        </button>
    </div>

    <!-- Task Filters -->
    <div class="filter-section">
        <div class="row">
            <div class="col-md-3 mb-2">
                <label class="form-label">Task Status</label>
                <select class="form-select">
                    <option selected>All Tasks</option>
                    <option>Pending</option>
                    <option>In Progress</option>
                    <option>Completed</option>
                    <option>Overdue</option>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label">Priority</label>
                <select class="form-select">
                    <option selected>All Priorities</option>
                    <option>High</option>
                    <option>Medium</option>
                    <option>Low</option>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label">Assigned To</label>
                <select class="form-select">
                    <option selected>All Users</option>
                    <option>Dr. Sarah Johnson</option>
                    <option>Dr. Michael Chen</option>
                    <option>Admin User</option>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <button class="btn btn-primary w-100">Apply Filters</button>
            </div>
        </div>
    </div>

    <!-- Task List -->
    <div class="main-content">
        <h4 class="section-title">Active Tasks</h4>
        <div class="row">
            <?php
            // require_once '../database/db_functions.php';
            $tasks = getTasks();
            if (empty($tasks)) {
                // Fallback to static data
                echo '<div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Review Study Protocol 00102-26</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-2">Complete initial review of the veterans study protocol</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-warning text-dark">High Priority</span>
                                <small class="text-muted">Due: Sep 20, 2024</small>
                            </div>
                            <div class="mt-3">
                                <button class="btn btn-sm btn-outline-primary me-2">View Details</button>
                                <button class="btn btn-sm btn-success">Mark Complete</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Follow-up on SAE Report</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-2">Investigate serious adverse event reported in study 00219-16</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-danger">High Priority</span>
                                <small class="text-muted">Due: Sep 18, 2024</small>
                            </div>
                            <div class="mt-3">
                                <button class="btn btn-sm btn-outline-primary me-2">View Details</button>
                                <button class="btn btn-sm btn-success">Mark Complete</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Update Study Documentation</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-2">Review and update consent forms for study 00219-18</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-info">Medium Priority</span>
                                <small class="text-muted">Due: Sep 25, 2024</small>
                            </div>
                            <div class="mt-3">
                                <button class="btn btn-sm btn-outline-primary me-2">View Details</button>
                                <button class="btn btn-sm btn-success">Mark Complete</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Schedule IRB Meeting</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-2">Organize monthly IRB committee meeting</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Low Priority</span>
                                <small class="text-muted">Due: Oct 01, 2024</small>
                            </div>
                            <div class="mt-3">
                                <button class="btn btn-sm btn-outline-primary me-2">View Details</button>
                                <button class="btn btn-sm btn-success">Mark Complete</button>
                            </div>
                        </div>
                    </div>
                </div>';
            } else {
                foreach ($tasks as $task) {
                    $priorityBadge = 'bg-info';
                    switch ($task['priority'] ?? 1) {
                        case 3: $priorityBadge = 'bg-danger'; break;
                        case 2: $priorityBadge = 'bg-warning text-dark'; break;
                        case 1: $priorityBadge = 'bg-success'; break;
                    }
                    $priorityText = ['Low', 'Medium', 'High'][$task['priority'] - 1 ?? 1] . ' Priority';
                    echo '<div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">' . htmlspecialchars($task['title'] ?? 'Review Study Protocol 00102-26') . '</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-2">' . htmlspecialchars($task['description'] ?? 'Complete initial review of the veterans study protocol') . '</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge ' . $priorityBadge . '">' . $priorityText . '</span>
                                    <small class="text-muted">Due: ' . htmlspecialchars($task['due_date'] ?? 'Sep 20, 2024') . '</small>
                                </div>
                                <div class="mt-3">
                                    <button class="btn btn-sm btn-outline-primary me-2">View Details</button>
                                    <button class="btn btn-sm btn-success">Mark Complete</button>
                                </div>
                            </div>
                        </div>
                    </div>';
                }
            }
            ?>
        </div>
    </div>
</div>