<!-- Agenda Details Content -->
<div class="agenda-details">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Agenda Details</h2>
        <div>
            <button class="btn btn-success me-2">
                <i class="fas fa-save me-1"></i> Save
            </button>

        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Table Section -->
        <div id="tableContent" class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-bordered agenda-table">
                            <thead>
                                <tr>
                                    <th>Item #</th>
                                    <th>IRB#</th>
                                    <th>Source Number</th>
                                    <th>Agenda Group</th>
                                    <th>Action Taken</th>
                                    <th>PI</th>
                                    <th>Title</th>
                                    <th>RefNumber</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>0123-2-15</td>
                                    <td>5605</td>
                                    <td>New Protocol</td>
                                    <td>-</td>
                                    <td>Abdel Samed</td>
                                    <td>Assessing the Future that Influence Critical Training of Student Nurses in Nursing College of the North East Region of Ghana</td>
                                    <td>HGQ1Y2S-3606R</td>
                                </tr>
                                <!-- More rows as needed -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div id="studyContent">
            <!-- Header Section -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold">Study Information</h6>
                        </div>
                        <div class="card-body">
                            <?php
                            // require_once '../database/db_functions.php';
                            $meetings = getMeetings();
                            if (!empty($meetings)) {
                                $meeting = $meetings[0]; // Get first meeting for demo
                                echo '<div class="row mb-3">
                                <div class="col-md-6">
                                    <strong class="text-muted d-block">Name</strong>
                                    <span>' . htmlspecialchars($meeting['name'] ?? 'RDB') . '</span>
                                </div>
                                <div class="col-md-6">
                                    <strong class="text-muted d-block">Source Number</strong>
                                    <span>' . htmlspecialchars($meeting['source_number'] ?? '9:00:05') . '</span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong class="text-muted d-block">Agenda Group</strong>
                                    <span>' . htmlspecialchars($meeting['agenda_group'] ?? 'Expedited') . '</span>
                                </div>
                                <div class="col-md-6">
                                    <strong class="text-muted d-block">RefNum</strong>
                                    <span>' . htmlspecialchars($meeting['ref_num'] ?? 'HGQ1Y2S-3606R') . '</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <strong class="text-muted d-block">Protocol Title</strong>
                                    <p class="mb-0">' . htmlspecialchars($meeting['protocol_title'] ?? 'Assessing the Future that Influence Critical Training of Student Nurses in Nursing College of the North East Region of Ghana') . '</p>
                                </div>
                            </div>';
                            } else {
                                // Fallback to static data
                                echo '<div class="row mb-3">
                                <div class="col-md-6">
                                    <strong class="text-muted d-block">Name</strong>
                                    <span>RDB</span>
                                </div>
                                <div class="col-md-6">
                                    <strong class="text-muted d-block">Source Number</strong>
                                    <span>9:00:05</span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong class="text-muted d-block">Agenda Group</strong>
                                    <span>Expedited</span>
                                </div>
                                <div class="col-md-6">
                                    <strong class="text-muted d-block">RefNum</strong>
                                    <span>HGQ1Y2S-3606R</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <strong class="text-muted d-block">Protocol Title</strong>
                                    <p class="mb-0">Assessing the Future that Influence Critical Training of Student Nurses in Nursing College of the North East Region of Ghana</p>
                                </div>
                            </div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold">Meeting Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong class="text-muted d-block">Meeting Date</strong>
                                <span>2025-10-21</span>
                            </div>
                            <div class="mb-3">
                                <strong class="text-muted d-block">Study #</strong>
                                <span>0123-2-15</span>
                            </div>
                            <div>
                                <strong class="text-muted d-block">Internal Source Number</strong>
                                <span>5605</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Protocol Details -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold">Protocol Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <strong class="text-muted d-block">Principal Investigator</strong>
                                    <span>Abdel Samed</span>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <strong class="text-muted d-block">Review Cycle</strong>
                                    <span>12</span>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <strong class="text-muted d-block">Exp. Date</strong>
                                    <span>2025-10-16</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <strong class="text-muted d-block">Date Received</strong>
                                    <span>2025-04-23</span>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <strong class="text-muted d-block">First IRB Review</strong>
                                    <span>2025-06-08</span>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <strong class="text-muted d-block">Original Approval</strong>
                                    <span>2025-06-08</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <strong class="text-muted d-block">Last Review by IRB</strong>
                                    <span>2025-06-08</span>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <strong class="text-muted d-block">Last IRB Renewal</strong>
                                    <span>2025-06-08</span>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <strong class="text-muted d-block">Study Status</strong>
                                    <span class="badge bg-success">Open</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <strong class="text-muted d-block">Type</strong>
                                    <span class="badge bg-primary">Full Board</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Agenda Information -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">Reason Study is On Agenda</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Agenda Category</label>
                                <select class="form-select">
                                    <option>Expedited</option>
                                    <option>Full Board</option>
                                    <option>Continuing Review</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">IRB Meeting Action</label>
                                <select class="form-select">
                                    <option>Select One</option>
                                    <option>Approved</option>
                                    <option>Modifications Required</option>
                                    <option>Deferred</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Business 1</label>
                                <select class="form-select">
                                    <option>Procedure</option>
                                    <option>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Business 2</label>
                                <select class="form-select">
                                    <option>Other</option>
                                    <option>Procedure</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">Action Conditions</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Action Team</label>
                            <select class="form-select">
                                <option>Select One</option>
                                <option>Review Committee</option>
                                <option>PI Response Required</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Conditions 1</label>
                            <input type="text" class="form-control" placeholder="Enter condition...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Conditions 2</label>
                            <input type="text" class="form-control" placeholder="Enter condition...">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Info & Action Explanation -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">Agenda Info</h6>
                    </div>
                    <div class="card-body">
                        <label class="form-label fw-semibold">Additional info</label>
                        <textarea class="form-control" rows="4">
The plan assumes a parallel and logical approach on August 4th, 2023. The following resources were submitted on September 04, 2023. It has been identified as a future focus member for each year, which reflects the potential to be granted through the data analysis section (as been reviewed by class). A 1% decrease for assessing the documentation has occurred included from 2 weeks to 1 hour, 5. On the previous basis, the following comments have been addressed. The data provided include:
                        </textarea>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">Action Explanation</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Action Explanation</label>
                            <input type="text" class="form-control" value="">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Discussion</label>
                            <input type="text" class="form-control" value="">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Vote</label>
                            <input type="text" class="form-control" value="">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 fw-bold">Quick Actions</h6>
                            </div>
                            <div>
                                <button id="recordBtn" class="btn btn-outline-primary me-2" onclick="showHideRecordtable()">
                                    <i class="fas fa-file-alt me-1"></i> Hide Record Table
                                </button>
                                <button id="studyBtn" class="btn btn-outline-primary me-2" onclick="showHideStudyDetials()">
                                    <i class="fas fa-question-circle me-1"></i> Hide Study Details
                                </button>
                                <a href="/minutes" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-clipboard me-1"></i> Meeting Minutes
                                </a>
                                <a href="/generate-letter" class="btn btn-outline-primary">
                                    <i class="fas fa-envelope me-1"></i> Send Correspondence
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<style>
    .agenda-details .card {
        border: 1px solid #e0e0e0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .agenda-details .card-header {
        background-color: #f8f9fa !important;
        border-bottom: 1px solid #e0e0e0;
    }

    .agenda-details .form-label {
        color: #495057;
        font-weight: 600;
    }

    .agenda-details .text-muted {
        font-size: 0.875rem;
    }
</style>

<script>
    function showHideRecordtable() {
        const tableContent = document.getElementById('tableContent');
        const recordButton = document.getElementById('recordBtn');
        if (tableContent.style.display === 'none') {
            tableContent.style.display = 'block';
            recordButton.innerHTML = '<i class="fas fa-file-alt me-1"></i> Hide Record Table';
        } else {
            tableContent.style.display = 'none';
            recordButton.innerHTML = '<i class="fas fa-file-alt me-1"></i> Show Record Table';
        }
    }

    function showHideStudyDetials() {
        const studyContent = document.getElementById('studyContent');
        const studyButton = document.getElementById('studyBtn');
        if (studyContent.style.display === 'none') {
            studyContent.style.display = 'block';
            studyButton.innerHTML = '<i class="fas fa-question-circle me-1"></i> Hide Study Details';
        } else {
            studyContent.style.display = 'none';
            studyButton.innerHTML = '<i class="fas fa-question-circle me-1"></i> Show Study Details';
        }
    }
</script>