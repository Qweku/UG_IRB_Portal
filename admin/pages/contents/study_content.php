<?php
// require_once '../database/db_functions.php';

// Get filter parameters from GET request or default
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$review_type = isset($_GET['review_type']) ? $_GET['review_type'] : 'all';
$pi_name = isset($_GET['pi_name']) ? $_GET['pi_name'] : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'protocol_number';

// Fetch studies based on filters
$studies = getStudies($status, $review_type, $pi_name, $sort_by);

// Fetch distinct PI names for dropdown
$pi_names = getDistinctPINames();
?>
<!-- Main Content -->
<div class="">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Study / Protocol Management</h2>
        <a class="btn btn-primary" href="/add-study">
            <i class="fas fa-plus me-1"></i> Add New Study
        </a>
    </div>

    <!-- Filter Section -->
    <form method="GET" action="">
        <div class="filter-section">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label class="form-label">Study Status</label>
                    <select class="form-select" name="status">
                        <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>All Studies</option>
                        <option value="open" <?php echo $status == 'open' ? 'selected' : ''; ?>>Open Studies</option>
                        <option value="closed" <?php echo $status == 'closed' ? 'selected' : ''; ?>>Closed Studies</option>
                        <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending Review</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Study Type</label>
                    <select class="form-select" name="review_type">
                        <option value="all" <?php echo $review_type == 'all' ? 'selected' : ''; ?>>All Types</option>
                        <option value="full_board" <?php echo $review_type == 'full_board' ? 'selected' : ''; ?>>Full Board</option>
                        <option value="expedited" <?php echo $review_type == 'expedited' ? 'selected' : ''; ?>>Expedited</option>
                        <option value="exempt" <?php echo $review_type == 'exempt' ? 'selected' : ''; ?>>Exempt</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Principal Investigator</label>
                    <select class="form-select" name="pi_name">
                        <option value="" <?php echo $pi_name == '' ? 'selected' : ''; ?>>All PIs</option>
                        <?php foreach ($pi_names as $pi): ?>
                            <option value="<?php echo htmlspecialchars($pi); ?>" <?php echo $pi_name == $pi ? 'selected' : ''; ?>><?php echo htmlspecialchars($pi); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Sort By</label>
                    <select class="form-select" name="sort_by">
                        <option value="protocol_number" <?php echo $sort_by == 'protocol_number' ? 'selected' : ''; ?>>Protocol Number</option>
                        <option value="approval_date" <?php echo $sort_by == 'approval_date' ? 'selected' : ''; ?>>Approval Date</option>
                        <option value="title" <?php echo $sort_by == 'title' ? 'selected' : ''; ?>>Study Title</option>
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </div>
        </div>
    </form>

    <!-- Study Table -->
    <div class="main-content">
        <h4 class="section-title">Directory of Open Studies</h4>

        <div class="table-responsive">
            <table class="table table-hover study-table">
                <thead class="table-primary">
                    <tr>
                        <th>Protocol Number</th>
                        <th>Title</th>
                        <th>Study Active?</th>
                        <th>Study Type</th>
                        <th>Study Status</th>
                        <th>PI</th>
                        <th>Review Cycle</th>
                        <th>Data Received</th>
                        <th>First IRB Review</th>
                        <th>Approval Date</th>
                        <th>Last Renewal Date</th>
                        <th>InitEnroll</th>
                        <th>#Patients enrolled</th>
                        <th>ExpirationDate</th>
                        <th>MostRecentMeeting</th>
                        <th>ExemptCite</th>
                        <th>ExpediteCite</th>
                        <th>Remarks</th>
                        <th>IRB Code</th>
                        <th>AddToAgenda</th>
                        <th>RiskDescription</th>
                        <th>RefNum</th>
                        <th>AuthorizedIRB</th>
                        <th>FeeRequired</th>
                        <th>CoorDisplayname</th>
                        <th>SponsorDisplayname</th>
                        <th>Reviewers</th>
                        <th>Cols</th>
                        <th>Admins</th>
                        <th>Classifications</th>
                        <th>Sites</th>
                        <th>DeptGroups</th>
                        <th>VulPops</th>
                        <th>Childs</th>
                        <th>Drugs</th>
                        <th>Risks</th>
                        <th>Benefits</th>
                        <th>Div_s</th>
                        <th>GrantProjects</th>
                        <th>Inds</th>
                        <th>UnderGrad_Grad</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($studies)): ?>
                        <?php foreach ($studies as $study): ?>
                            <tr onclick="window.location.href='/add-study?edit=1&id=<?php echo $study['id']; ?>'" style="cursor: pointer;">
                                <td><?php echo htmlspecialchars($study['protocol_number']); ?></td>
                                <td><div style="width:250px;"><?php echo htmlspecialchars($study['title']); ?></div></td>
                                <td><span class="status-badge status-<?php echo strtolower($study['study_active']); ?>"><?php echo ucfirst($study['study_active']); ?></span></td>
                                <td><?php echo ucwords(str_replace('_', ' ', $study['review_type'])); ?></td>
                                 <td><span class="status-badge status-<?php echo strtolower($study['study_status']); ?>"><?php echo ucfirst($study['study_status']); ?></span></td>
                                <td><div style="width:200px;"><?php echo htmlspecialchars(isset($study['pi']) ? $study['pi'] : ''); ?></div></td>
                                <td><?php echo htmlspecialchars($study['renewal_cycle']); ?></td>
                                <td><?php echo  htmlspecialchars($study['data_received'] ?? ""); ?></td>
                                <td><?php echo  htmlspecialchars($study['first_irb_review'] ?? ""); ?></td>
                                <td><?php echo htmlspecialchars($study['approval_date'] ?? ""); ?></td>
                                <td><?php echo htmlspecialchars($study['last_renewal_date'] ?? "") ; ?></td>
                                <td><?php echo htmlspecialchars($study['init_enroll']); ?></td>
                                <td><?php echo htmlspecialchars($study['patients_enrolled']); ?></td>
                                <td><?php echo htmlspecialchars($study['expiration_date'] ?? ""); ?></td>
                                <td><?php echo htmlspecialchars($study['most_recent_meeting' ?? ""]) ; ?></td>
                                <td><?php echo htmlspecialchars($study['exempt_cite']); ?></td>
                                <td><?php echo htmlspecialchars($study['expedite_cite']); ?></td>
                                <td><?php echo htmlspecialchars($study['remarks']); ?></td>
                                <td><?php echo htmlspecialchars($study['irb_code']); ?></td>
                                <td><?php echo !empty($study['add_to_agenda']) ? 'Yes' : 'No'; ?></td>
                                <td><?php echo htmlspecialchars($study['risk_description']); ?></td>
                                <td><?php echo htmlspecialchars($study['ref_num']); ?></td>
                                <td><?php echo htmlspecialchars($study['authorized_irb']); ?></td>
                                <td><?php echo !empty($study['fee_required']) ? 'Yes' : 'No'; ?></td>
                                <td><?php echo htmlspecialchars($study['coor_displayname']); ?></td>
                                <td><?php echo htmlspecialchars($study['sponsor_displayname']); ?></td>
                                <td><?php echo htmlspecialchars($study['reviewers']); ?></td>
                                <td><?php echo htmlspecialchars($study['cols']); ?></td>
                                <td><?php echo htmlspecialchars($study['admins']); ?></td>
                                <td><?php echo htmlspecialchars($study['classifications']); ?></td>
                                <td><?php echo htmlspecialchars($study['sites']); ?></td>
                                <td><?php echo htmlspecialchars($study['dept_group']); ?></td>
                                <td><?php echo htmlspecialchars($study['vul_props']); ?></td>
                                <td><?php echo htmlspecialchars($study['childs']); ?></td>
                                <td><?php echo htmlspecialchars($study['drugs']); ?></td>
                                <td><?php echo htmlspecialchars($study['risk_category']); ?></td>
                                <td><?php echo htmlspecialchars($study['benefits']); ?></td>
                                <td><?php echo htmlspecialchars($study['divs']); ?></td>
                                <td><?php echo htmlspecialchars($study['grant_projects']); ?></td>
                                <td><?php echo htmlspecialchars($study['inds']); ?></td>
                                <td><?php echo htmlspecialchars($study['under_grad']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="40" class="text-center">No studies found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-container mt-3">
            <nav aria-label="Study table navigation">
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1">Previous</a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>