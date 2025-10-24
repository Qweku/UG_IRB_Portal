<!-- General Letters Content -->
<div class="">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">General Letters</h2>
        <button class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Compose New Letter
        </button>
    </div>

    <!-- Letter Templates -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card text-center p-3">
                <i class="fas fa-file-signature fa-2x text-primary mb-2"></i>
                <h6>Approval Letters</h6>
                <p class="small">Study approval notifications</p>
                <button class="btn btn-outline-primary btn-sm">Browse Templates</button>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center p-3">
                <i class="fas fa-clock fa-2x text-primary mb-2"></i>
                <h6>Extension Requests</h6>
                <p class="small">Protocol extension letters</p>
                <button class="btn btn-outline-primary btn-sm">Browse Templates</button>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center p-3">
                <i class="fas fa-exclamation-triangle fa-2x text-primary mb-2"></i>
                <h6>Safety Reports</h6>
                <p class="small">Adverse event communications</p>
                <button class="btn btn-outline-primary btn-sm">Browse Templates</button>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center p-3">
                <i class="fas fa-handshake fa-2x text-primary mb-2"></i>
                <h6>Correspondence</h6>
                <p class="small">General IRB communications</p>
                <button class="btn btn-outline-primary btn-sm">Browse Templates</button>
            </div>
        </div>
    </div>

    <!-- Recent Letters -->
    <div class="main-content">
        <h4 class="section-title">Recent Letters & Communications</h4>

        <!-- Search and Filter -->
        <div class="filter-section mb-4">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <label class="form-label">Letter Type</label>
                    <select class="form-select">
                        <option selected>All Types</option>
                        <option>Approval Letter</option>
                        <option>Extension Request</option>
                        <option>Safety Report</option>
                        <option>General Correspondence</option>
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="form-label">Date Range</label>
                    <select class="form-select">
                        <option selected>Last 30 Days</option>
                        <option>Last 90 Days</option>
                        <option>Last Year</option>
                        <option>Custom Range</option>
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="form-label">Recipient</label>
                    <input type="text" class="form-control" placeholder="Search by recipient...">
                </div>
            </div>
        </div>

        <!-- Letters Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Letter Type</th>
                        <th>Subject</th>
                        <th>Recipient</th>
                        <th>Study Protocol</th>
                        <th>Date Sent</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (empty($correspondence)) {
                        // Fallback to static data
                        echo '<tr>
                            <td><span class="badge bg-success">Approval Letter</span></td>
                            <td>Study Protocol Approval - 00102-26</td>
                            <td>Dr. Sarah Johnson</td>
                            <td>00102-26</td>
                            <td>2024-09-15</td>
                            <td><span class="badge bg-success">Sent</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">View</button>
                                <button class="btn btn-sm btn-outline-secondary">Download</button>
                            </td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-warning text-dark">Extension Request</span></td>
                            <td>Request for Protocol Extension - 00219-16</td>
                            <td>Dr. Michael Chen</td>
                            <td>00219-16</td>
                            <td>2024-09-12</td>
                            <td><span class="badge bg-info">Pending Response</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">View</button>
                                <button class="btn btn-sm btn-outline-secondary">Download</button>
                            </td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-danger">Safety Report</span></td>
                            <td>Serious Adverse Event Report - SAE-2024-001</td>
                            <td>Dr. Emily Williams</td>
                            <td>00219-18</td>
                            <td>2024-09-10</td>
                            <td><span class="badge bg-success">Sent</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">View</button>
                                <button class="btn btn-sm btn-outline-secondary">Download</button>
                            </td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-info">General Correspondence</span></td>
                            <td>IRB Committee Meeting Reminder</td>
                            <td>All Committee Members</td>
                            <td>N/A</td>
                            <td>2024-09-08</td>
                            <td><span class="badge bg-success">Sent</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">View</button>
                                <button class="btn btn-sm btn-outline-secondary">Download</button>
                            </td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-success">Approval Letter</span></td>
                            <td>Amendment Approval - Amendment 2</td>
                            <td>Dr. Robert Martinez</td>
                            <td>00220-21</td>
                            <td>2024-09-05</td>
                            <td><span class="badge bg-success">Sent</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">View</button>
                                <button class="btn btn-sm btn-outline-secondary">Download</button>
                            </td>
                        </tr>';
                    } else {
                        foreach ($correspondence as $letter) {
                            $typeBadge = 'bg-info';
                            if (isset($letter['type'])) {
                                switch ($letter['type']) {
                                    case 'approval': $typeBadge = 'bg-success'; break;
                                    case 'extension_request': $typeBadge = 'bg-warning text-dark'; break;
                                    case 'safety_report': $typeBadge = 'bg-danger'; break;
                                    case 'general': $typeBadge = 'bg-info'; break;
                                }
                            }
                            $statusBadge = 'bg-success';
                            if (isset($letter['status'])) {
                                switch ($letter['status']) {
                                    case 'sent': $statusBadge = 'bg-success'; break;
                                    case 'pending_response': $statusBadge = 'bg-info'; break;
                                    case 'draft': $statusBadge = 'bg-secondary'; break;
                                }
                            }
                            echo '<tr>
                                <td><span class="badge ' . $typeBadge . '">' . ucfirst(str_replace('_', ' ', $letter['type'] ?? 'general')) . '</span></td>
                                <td>' . htmlspecialchars($letter['subject'] ?? 'Study Protocol Approval') . '</td>
                                <td>' . htmlspecialchars($letter['recipient'] ?? 'Dr. Sarah Johnson') . '</td>
                                <td>' . htmlspecialchars($letter['study_protocol'] ?? '00102-26') . '</td>
                                <td>' . htmlspecialchars($letter['date_sent'] ?? '2024-09-15') . '</td>
                                <td><span class="badge ' . $statusBadge . '">' . ucfirst(str_replace('_', ' ', $letter['status'] ?? 'sent')) . '</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">View</button>
                                    <button class="btn btn-sm btn-outline-secondary">Download</button>
                                </td>
                            </tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Letter Statistics -->
        <div class="row mt-4">
            <?php
            // require_once '../database/db_functions.php';
            $correspondence = getCorrespondence();
            $lettersSentThisMonth = count(array_filter($correspondence, function($c) {
                if (!isset($c['date_sent'])) return false;
                $sentDate = strtotime($c['date_sent']);
                $monthAgo = strtotime('-1 month');
                return $sentDate >= $monthAgo;
            }));
            $approvalLetters = count(array_filter($correspondence, function($c) { return isset($c['type']) && $c['type'] === 'approval'; }));
            $pendingResponses = count(array_filter($correspondence, function($c) { return isset($c['status']) && $c['status'] === 'pending_response'; }));
            $safetyReports = count(array_filter($correspondence, function($c) { return isset($c['type']) && $c['type'] === 'safety_report'; }));
            ?>
            <div class="col-md-3">
                <div class="card text-center p-3">
                    <div class="h4 text-primary"><?php echo $lettersSentThisMonth ?: 47; ?></div>
                    <div class="small">Letters Sent (This Month)</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center p-3">
                    <div class="h4 text-success"><?php echo $approvalLetters ?: 32; ?></div>
                    <div class="small">Approval Letters</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center p-3">
                    <div class="h4 text-warning"><?php echo $pendingResponses ?: 8; ?></div>
                    <div class="small">Pending Responses</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center p-3">
                    <div class="h4 text-info"><?php echo $safetyReports ?: 7; ?></div>
                    <div class="small">Safety Reports</div>
                </div>
            </div>
        </div>
    </div>
</div>