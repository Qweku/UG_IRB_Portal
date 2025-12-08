<!-- Due for Continuing Review Items Content -->
<div class="due-continue-review-content p-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Studies Due for Continue Review</h2>
        <div>
            <!-- <button class="btn btn-primary me-2">
                <i class="fas fa-plus me-1"></i> Preview All Agenda Items
            </button>
            <a href="/prepare-agenda" class="btn btn-outline-primary">
                <i class="fas fa-file-export me-1"></i> Prepare Agenda
            </a> -->
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

                </div>
            </div>
            <div class="col-md-6 d-flex align-items-end justify-content-end">
                <button class="btn btn-outline-secondary me-2">
                    <i class="fas fa-download me-1"></i> Renewal Listing PDF
                </button>
                <button class="btn btn-outline-secondary me-2">
                    <i class="fas fa-download me-1"></i> Renewal Listing Excel
                </button>

            </div>
        </div>
        <div class="mt-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="studiesOnAgenda">
                <label class="form-check-label small" for="studiesOnAgenda">
                    Show only those studies not already on Agenda for this date
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="crRequired">
                <label class="form-check-label small" for="crRequired">
                    Show only those studies where CR is required
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="exemptStudies">
                <label class="form-check-label small" for="exemptStudies">
                    Include Exempt Studies
                </label>
            </div>
        </div>
    </div>

    <!-- Agenda Items Table -->
    <div class="main-content">
        <div class="d-flex justify-content-between section-title">
            <h4 class="">Study Items</h4>
             <div>
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search...">
                    <button class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
        

        <div class="table-responsive">
            <table class="table table-hover agenda-table">
                <thead>
                    <tr>
                        
                        <th>IRB #</th>
                        <th>Study Type</th>
                        <th>Protocol Number & Title</th>
                        <th>ExpirationDate</th>
                        <th>Agenda</th>
                        <th>chkCRRqd</th>
                        <th>ExpediteFlag</th>
                        <th>SentFlag</th>
                        <th>Date Sent</th>
                        <th>FinalFlagNew</th>
                        <th>ProgressFlagNew</th>
                        <th>Date Received by IRB Co-Ord</th>
                        <th>Last Renewal Date</th>
                        <th>Renewal Cycle#</th>
                        <th>PIDisplayName</th>
                        <th>Study Status</th>
                        <th>Ref Num</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // require_once '../database/db_functions.php';
                    $studies = getContinueReviewStudies();
                    if (empty($studies)) {
                        // Fallback to static data
                        echo '<tr>
                            <td>145/23-24</td>
                            <td>Full Board</td>
                            <td>Gender-based violence screening</td>
                            <td>2025-07-02</td>
                            <td>-</td>
                            <td><input type="checkbox" disabled></td>
                            <td>-</td>
                            <td>0</td>
                            <td>-</td>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>
                            <td>2024-07-03</td>
                            <td>12</td>
                            <td>Agbenu, Innes</td>
                            <td><span class="badge bg-success">Open</span></td>
                            <td>REF-123</td>
                        </tr>
                       ';
                    } else {
                        foreach ($studies as $index => $study) {
                            // $badgeClass = 'bg-info';
                            // if (isset($meeting['agenda_category'])) {
                            //     switch ($meeting['agenda_category']) {
                            //         case 'Full Board':
                            //             $badgeClass = 'bg-primary';
                            //             break;
                            //         case 'Continuing Review':
                            //             $badgeClass = 'bg-warning text-dark';
                            //             break;
                            //     }
                            // }
                            echo '<tr>
                                <td>' .htmlspecialchars($study['irb_number'] ?? '013/25-26') . '</td>
                                <td>' . htmlspecialchars($study['review_type'] ?? '') . '</td>
                                 <td>' . htmlspecialchars($study['protocol_number'] ?? '') . '</td>
                                <td>' . htmlspecialchars($study['expiration_date'] ?? '') . '</td>
                                <td>' . htmlspecialchars($study['agenda'] ?? '') . '</td>
                                <td><input type="checkbox" disabled></td>
                                <td>' . htmlspecialchars($study['expedite_cite'] ?? '') . '</td>
                                <td>' . htmlspecialchars($study['sent_flag'] ?? '0') . '</td>
                                <td>' . htmlspecialchars($study['date_sent'] ?? '') . '</td>
                                <td>' . htmlspecialchars($study['final_flag_sent'] ?? '') . '</td> 
                                <td>' . htmlspecialchars($study['progress_flag_new'] ?? '') . '</td>
                                <td>' . htmlspecialchars($study['date_received'] ?? '') . '</td>
                                <td>' . htmlspecialchars($study['last_renewal_date'] ?? '') . '</td>
                                <td>' . htmlspecialchars($study['renewal_cycle'] ?? '') . '</td>
                                <td>' . htmlspecialchars($study['pi'] ?? '2025-10-01') . '</td>
                                <td><span class="badge ' . (($study['study_status'] === "open") ? 'bg-success' : 'bg-secondary') . '">' . htmlspecialchars($study['study_status'] ?? '') . '</span></td>
                                <td>' . htmlspecialchars($study['ref_num'] ?? 'REC-123') . '</td>
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
                    <i class="fas fa-copy me-1"></i>Place on Agenda Only
                </button>
                <button class="btn btn-outline-primary me-2">
                    <i class="fas fa-calendar-plus me-1"></i>Place on Agenda And Print Letter
                </button>
                
            </div>
           
        </div>
    </div>
</div>