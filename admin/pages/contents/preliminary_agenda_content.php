<!-- Preliminary Agenda Items Content -->
<div class="preliminary-agenda">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Preliminary Agenda Items</h2>
        <div>
            <button class="btn btn-primary me-2">
                <i class="fas fa-plus me-1"></i> Preview All Agenda Items
            </button>
            <a href="/prepare-agenda" class="btn btn-outline-primary">
                <i class="fas fa-file-export me-1"></i> Prepare Agenda
            </a>
        </div>
    </div>

    <!-- Meeting Dates Section -->
    <div class="filter-section mb-4">
        <div class="row">
            <div class="col-md-6">
                <h5 class="section-title mb-3">Meeting Dates</h5>
                <div class="d-flex align-items-center">
                    <select class="form-select me-2" style="max-width: 200px;">
                        <option selected>2025-10-01</option>
                        <option>2025-11-05</option>
                        <option>2025-12-03</option>
                    </select>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="keepItemsTogether" checked>
                        <label class="form-check-label small" for="keepItemsTogether">
                            Keep all items for a Study Together
                        </label>
                    </div>
                </div>
            </div>
            <div class="col-md-6 d-flex align-items-end justify-content-end">
                <button class="btn btn-outline-secondary me-2">
                    <i class="fas fa-eye me-1"></i> Preview Summary Report
                </button>
                
            </div>
        </div>
    </div>

    <!-- Agenda Items Table -->
    <div class="main-content">
        <h4 class="section-title">Agenda Items</h4>

        <div class="table-responsive">
            <table class="table table-hover agenda-table">
                <thead>
                    <tr>
                        <th>Position</th>
                        <th>IRB #</th>
                        <th>Agenda Category</th>
                        <th>Agenda Group</th>
                        <th>Expedite</th>
                        <th>Internal Number</th>
                        <th>Agenda Explanation</th>
                        <th>Title</th>
                        <th>PI</th>
                        <th>Condition 1</th>
                        <th>Condition 2</th>
                        <th>Renewal</th>
                        <th>Review</th>
                        <th>Meeting Date</th>
                        <th>Reference #</th>
                        <th>recorder_id</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // require_once '../database/db_functions.php';
                    $meetings = getMeetings();
                    if (empty($meetings)) {
                        // Fallback to static data
                        echo '<tr>
                            <td>0</td>
                            <td>013/25-26</td>
                            <td><select class="form-select">
                            <option selected>Expedited</option>
                            <option>Procedure</option>
                            <option>Exempt</option>
                            <option>Renewal</option>
                            <option>Resubmission</option>
                            </select></td>
                            <td>Expedited</td>
                            <td><span class="badge bg-success">True</span></td>
                            <td>5085</td>
                            <td>The protocol was gr</td>
                            <td>Assessing the</td>
                            <td>Dr. John Smith</td>
                            <td>Approved</td>
                            <td>Pending</td>
                            <td>Yes</td>
                            <td>Initial</td>
                            <td>2025-10-01</td>
                            <td>REF-001</td>
                            <td>REC-123</td>
                        </tr>
                        <tr>
                            <td>1</td>
                            <td>014/25-26</td>
                            <td><select class="form-select">
                            <option selected>Expedited</option>
                            <option>Procedure</option>
                            <option>Exempt</option>
                            <option>Renewal</option>
                            <option>Resubmission</option>
                            </select></td>
                            <td>New Protocols</td>
                            <td><span class="badge bg-secondary">False</span></td>
                            <td>5086</td>
                            <td>Initial review required</td>
                            <td>Diabetes Treatment Study</td>
                            <td>Dr. Jane Doe</td>
                            <td>Approved</td>
                            <td>Approved</td>
                            <td>No</td>
                            <td>Initial</td>
                            <td>2025-10-01</td>
                            <td>REF-002</td>
                            <td>REC-124</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>015/25-26</td>
                            <td><select class="form-select">
                            <option selected>Expedited</option>
                            <option>Procedure</option>
                            <option>Exempt</option>
                            <option>Renewal</option>
                            <option>Resubmission</option>
                            </select></td>
                            <td>Continuing Reviews</td>
                            <td><span class="badge bg-secondary">False</span></td>
                            <td>5087</td>
                            <td>Annual review pending</td>
                            <td>Cardiovascular Health</td>
                            <td>Dr. Alex Johnson</td>
                            <td>Pending</td>
                            <td>Approved</td>
                            <td>Yes</td>
                            <td>Annual</td>
                            <td>2025-10-01</td>
                            <td>REF-003</td>
                            <td>REC-125</td>
                        </tr>';
                    } else {
                        foreach ($meetings as $index => $meeting) {
                            $badgeClass = 'bg-info';
                            if (isset($meeting['agenda_category'])) {
                                switch ($meeting['agenda_category']) {
                                    case 'Full Board':
                                        $badgeClass = 'bg-primary';
                                        break;
                                    case 'Continuing Review':
                                        $badgeClass = 'bg-warning text-dark';
                                        break;
                                }
                            }
                            echo '<tr>
                                <td>' . $index . '</td>
                                <td>' . htmlspecialchars($meeting['irb_number'] ?? '013/25-26') . '</td>
                                 <td><select class="form-select">
                            <option>' . htmlspecialchars($meeting['agenda_category'] ?? 'Expedited') . '</option>
                            
                            </select></td>
                                
                                <td>' . htmlspecialchars($meeting['agenda_group'] ?? 'Expedited') . '</td>
                                <td><span class="badge ' . (($meeting['expedite'] ?? false) ? 'bg-success' : 'bg-secondary') . '">' . (($meeting['expedite'] ?? false) ? 'True' : 'False') . '</span></td>
                                <td>' . htmlspecialchars($meeting['internal_number'] ?? '5085') . '</td>
                                <td>' . htmlspecialchars($meeting['agenda_explanation'] ?? 'The protocol was gr') . '</td>
                                <td>' . htmlspecialchars($meeting['title'] ?? 'Assessing the') . '</td>
                                <td>' . htmlspecialchars($meeting['pi'] ?? 'Dr. John Smith') . '</td>
                                <td>' . htmlspecialchars($meeting['condition1'] ?? 'Approved') . '</td>
                                <td>' . htmlspecialchars($meeting['condition2'] ?? 'Pending') . '</td>
                                <td>' . htmlspecialchars($meeting['renewal'] ?? 'Yes') . '</td>
                                <td>' . htmlspecialchars($meeting['review'] ?? 'Initial') . '</td>
                                <td>' . htmlspecialchars($meeting['meeting_date'] ?? '2025-10-01') . '</td>
                                <td>' . htmlspecialchars($meeting['reference_num'] ?? 'REF-001') . '</td>
                                <td>' . htmlspecialchars($meeting['recorder_id'] ?? 'REC-123') . '</td>
                            </tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-between mt-4">
            <div>
                <button class="btn btn-outline-primary me-2">
                    <i class="fas fa-copy me-1"></i> Assign Selected Study(s) to Another Meeting
                </button>
                <button class="btn btn-outline-primary me-2">
                    <i class="fas fa-calendar-plus me-1"></i> Assign All To New Meeting
                </button>
                <button class="btn btn-outline-danger">
                    <i class="fas fa-trash me-1"></i> Delete Selected Agenda Item(s)
                </button>
            </div>
            <div>
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search...">
                    <button class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>