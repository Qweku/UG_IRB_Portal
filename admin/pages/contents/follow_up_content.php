<!-- Follow Up Manager Content -->
<div class="">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Follow Up Manager</h2>
        <button class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Schedule Follow-up
        </button>
    </div>

    <!-- Follow-up Overview -->
    <div class="row mb-4">
        <?php
        // require_once '../database/db_functions.php';
        $followUps = getFollowUps();
        $pendingCount = count(array_filter($followUps, function($f) { return isset($f['status']) && $f['status'] === 'pending'; }));
        $dueThisWeek = count(array_filter($followUps, function($f) {
            if (!isset($f['due_date'])) return false;
            $dueDate = strtotime($f['due_date']);
            $weekFromNow = strtotime('+1 week');
            return $dueDate <= $weekFromNow;
        }));
        $overdueCount = count(array_filter($followUps, function($f) {
            if (!isset($f['due_date'])) return false;
            return strtotime($f['due_date']) < time();
        }));
        $completionRate = $followUps ? round((count($followUps) - $pendingCount) / count($followUps) * 100) . '%' : '0%';
        ?>
        <div class="col-md-3">
            <div class="card-dashboard text-center p-3">
                <div class="stats-number"><?php echo $pendingCount ?: 18; ?></div>
                <div class="stats-label">Pending Follow-ups</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-dashboard text-center p-3">
                <div class="stats-number"><?php echo $dueThisWeek ?: 7; ?></div>
                <div class="stats-label">Due This Week</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-dashboard text-center p-3">
                <div class="stats-number"><?php echo $overdueCount ?: 12; ?></div>
                <div class="stats-label">Overdue</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-dashboard text-center p-3">
                <div class="stats-number"><?php echo $completionRate ?: '95%'; ?></div>
                <div class="stats-label">Completion Rate</div>
            </div>
        </div>
    </div>

    <!-- Follow-up Tasks -->
    <div class="main-content">
        <h4 class="section-title">Follow-up Tasks</h4>

        <!-- Filter Section -->
        <div class="filter-section mb-4">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label class="form-label">Status</label>
                    <select class="form-select">
                        <option selected>All Tasks</option>
                        <option>Pending</option>
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
                    <label class="form-label">Type</label>
                    <select class="form-select">
                        <option selected>All Types</option>
                        <option>Protocol Review</option>
                        <option>Safety Monitoring</option>
                        <option>Data Analysis</option>
                        <option>Regulatory</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <button class="btn btn-primary w-100">Apply Filters</button>
                </div>
            </div>
        </div>

        <!-- Tasks Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Protocol Number</th>
                        <th>Follow-up Task</th>
                        <th>Type</th>
                        <th>Due Date</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (empty($followUps)) {
                        // Fallback to static data
                        echo '<tr>
                            <td>00102-26</td>
                            <td>Review 6-month safety data and adverse events</td>
                            <td>Safety Monitoring</td>
                            <td>2024-09-20</td>
                            <td><span class="badge bg-danger">High</span></td>
                            <td><span class="badge bg-warning text-dark">Pending</span></td>
                            <td>Dr. Sarah Johnson</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">View</button>
                                <button class="btn btn-sm btn-success">Complete</button>
                            </td>
                        </tr>
                        <tr>
                            <td>00219-16</td>
                            <td>Submit annual progress report to IRB</td>
                            <td>Regulatory</td>
                            <td>2024-09-25</td>
                            <td><span class="badge bg-warning text-dark">Medium</span></td>
                            <td><span class="badge bg-info">In Progress</span></td>
                            <td>Dr. Michael Chen</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">View</button>
                                <button class="btn btn-sm btn-success">Complete</button>
                            </td>
                        </tr>
                        <tr>
                            <td>00219-18</td>
                            <td>Follow up on protocol deviation #2024-003</td>
                            <td>Protocol Review</td>
                            <td>2024-09-18</td>
                            <td><span class="badge bg-danger">High</span></td>
                            <td><span class="badge bg-danger">Overdue</span></td>
                            <td>Dr. Emily Williams</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">View</button>
                                <button class="btn btn-sm btn-success">Complete</button>
                            </td>
                        </tr>
                        <tr>
                            <td>00220-21</td>
                            <td>Schedule monitoring visit for data verification</td>
                            <td>Data Analysis</td>
                            <td>2024-10-01</td>
                            <td><span class="badge bg-info">Low</span></td>
                            <td><span class="badge bg-success">Completed</span></td>
                            <td>Dr. Robert Martinez</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">View</button>
                                <button class="btn btn-sm btn-outline-secondary">Reopen</button>
                            </td>
                        </tr>
                        <tr>
                            <td>00221-22</td>
                            <td>Review updated consent form for approval</td>
                            <td>Regulatory</td>
                            <td>2024-09-28</td>
                            <td><span class="badge bg-warning text-dark">Medium</span></td>
                            <td><span class="badge bg-warning text-dark">Pending</span></td>
                            <td>Dr. Lisa Anderson</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">View</button>
                                <button class="btn btn-sm btn-success">Complete</button>
                            </td>
                        </tr>';
                    } else {
                        foreach ($followUps as $followUp) {
                            $priorityBadge = 'bg-info';
                            switch ($followUp['priority'] ?? 'low') {
                                case 'high': $priorityBadge = 'bg-danger'; break;
                                case 'medium': $priorityBadge = 'bg-warning text-dark'; break;
                            }
                            $statusBadge = 'bg-warning text-dark';
                            switch ($followUp['status'] ?? 'pending') {
                                case 'completed': $statusBadge = 'bg-success'; break;
                                case 'in_progress': $statusBadge = 'bg-info'; break;
                                case 'overdue': $statusBadge = 'bg-danger'; break;
                            }
                            echo '<tr>
                                <td>' . htmlspecialchars($followUp['protocol_number'] ?? '00102-26') . '</td>
                                <td>' . htmlspecialchars($followUp['task'] ?? 'Review 6-month safety data and adverse events') . '</td>
                                <td>' . htmlspecialchars($followUp['type'] ?? 'Safety Monitoring') . '</td>
                                <td>' . htmlspecialchars($followUp['due_date'] ?? '2024-09-20') . '</td>
                                <td><span class="badge ' . $priorityBadge . '">' . ucfirst($followUp['priority'] ?? 'high') . '</span></td>
                                <td><span class="badge ' . $statusBadge . '">' . ucfirst(str_replace('_', ' ', $followUp['status'] ?? 'pending')) . '</span></td>
                                <td>' . htmlspecialchars($followUp['assigned_to'] ?? 'Dr. Sarah Johnson') . '</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">View</button>
                                    <button class="btn btn-sm btn-success">Complete</button>
                                </td>
                            </tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Recent Activity -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Recent Follow-up Activity</h6>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item mb-3">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <small class="text-muted">2024-09-15 10:30 AM</small>
                                    <p class="mb-0">Completed safety review for protocol 00102-26</p>
                                </div>
                            </div>
                            <div class="timeline-item mb-3">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <small class="text-muted">2024-09-14 2:15 PM</small>
                                    <p class="mb-0">Scheduled monitoring visit for protocol 00219-18</p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-warning"></div>
                                <div class="timeline-content">
                                    <small class="text-muted">2024-09-13 9:45 AM</small>
                                    <p class="mb-0">Overdue: Protocol deviation follow-up</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Follow-up Statistics</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="followupChart" width="200" height="200"></canvas>
                        <div class="row text-center mt-3">
                            <div class="col-4">
                                <div class="h6 text-success">67</div>
                                <small class="text-muted">Completed</small>
                            </div>
                            <div class="col-4">
                                <div class="h6 text-warning">18</div>
                                <small class="text-muted">Pending</small>
                            </div>
                            <div class="col-4">
                                <div class="h6 text-danger">12</div>
                                <small class="text-muted">Overdue</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>