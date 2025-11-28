<!-- Reports Content -->
<div class="reports-section">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Reports</h2>
        <!-- <button class="btn btn-secondary">
            <i class="fas fa-undo me-1"></i> Return
        </button> -->
    </div>

    <!-- Report Generation Panel -->
    <div class="main-content">
        <h4 class="section-title">Generate Report</h4>
        
        <div class="row">
            <div class="col-md-6">
                <!-- Available Reports Selection -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Available Reports:</label>
                    <select class="form-select">
                        <option selected disabled>Select Report</option>
                        <option>Study Progress Report</option>
                        <option>SAE Reports</option>
                        <option>CPA Reports</option>
                        <option>Continuing Review Report</option>
                        <option>PI Study List</option>
                        <option>Sponsor Study List</option>
                        <option>Open Studies Report</option>
                        <option>Closed Studies Report</option>
                    </select>
                </div>

                <!-- Filter Selection -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Choose Filter</label>
                    <p class="text-muted small mb-3">
                        After selecting from the 'Available Reports' above, choose a filter under the Search Column 
                        and then select a pre-populated filter or enter custom text below, then add the filter.
                    </p>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Search Column</label>
                            <select class="form-select">
                                <option selected disabled>Select Column</option>
                                <option>Study Status</option>
                                <option>Principal Investigator</option>
                                <option>Study Type</option>
                                <option>Approval Date</option>
                                <option>Sponsor</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Detail Level</label>
                            <select class="form-select">
                                <option selected>Summary</option>
                                <option>Detailed</option>
                                <option>Comprehensive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Filter Input -->
                    <div class="mb-3">
                        <label class="form-label">Add Filter</label>
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Enter filter value...">
                            <button class="btn btn-outline-primary">Add Filter</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <!-- Current Filter Choices -->
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="mb-0">Current Filter Choices</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Filter</th>
                                        <th>Set To</th>
                                        <th width="80px">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Study Status</td>
                                        <td>Open</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Study Type</td>
                                        <td>Full Board</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            <button class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-trash me-1"></i> Clear All
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Actions -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                    <div>
                        <span class="fw-bold me-3">Output Format:</span>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="outputFormat" id="pdfFormat" checked>
                            <label class="form-check-label" for="pdfFormat">PDF</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="outputFormat" id="excelFormat">
                            <label class="form-check-label" for="excelFormat">Excel</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="outputFormat" id="csvFormat">
                            <label class="form-check-label" for="csvFormat">CSV</label>
                        </div>
                    </div>
                    <div>
                        <button class="btn btn-primary me-2">
                            <i class="fas fa-play me-1"></i> Submit
                        </button>
                        <button class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recently Generated Reports -->
        <div class="mt-5">
            <h5 class="section-title">Recently Generated Reports</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Report Name</th>
                            <th>Generated Date</th>
                            <th>Filters Applied</th>
                            <th>Format</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $recentReports = getRecentReports();
                        if (empty($recentReports)) {
                            // Fallback to static data if no DB data
                            echo '<tr>
                                <td>Open Studies Report</td>
                                <td>2025-09-05 14:30</td>
                                <td>Status: Open, Type: Full Board</td>
                                <td>PDF</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-download me-1"></i> Download
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>SAE Summary Report</td>
                                <td>2025-09-04 10:15</td>
                                <td>Date Range: Last 30 days</td>
                                <td>Excel</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-download me-1"></i> Download
                                    </button>
                                </td>
                            </tr>';
                        } else {
                            foreach ($recentReports as $report) {
                                echo '<tr>
                                    <td>' . htmlspecialchars($report['report_name']) . '</td>
                                    <td>' . htmlspecialchars($report['generated_date']) . '</td>
                                    <td>' . htmlspecialchars($report['filters_applied']) . '</td>
                                    <td>' . htmlspecialchars($report['format']) . '</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download me-1"></i> Download
                                        </button>
                                    </td>
                                </tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>