<?php

// Study Search columns
$studySearchColumns = [
    'IRB'                        => 'irb_number',
    'Protocol'                   => 'protocol_number',
    'Protocol Title'             => 'title',
    'Study Status'               => 'study_status',
    'Study Type'                 => 'review_type',
    'Expiration Date'            => 'expiration_date',
    'Date Received'              => 'date_received',
    'Active'                     => 'study_active',
    'Sponsor'                    => 'sponsor_displayname',
    'Principal Investigator'     => 'pi',
    'Study Coordinator'          => 'coor_displayname',
    'IRB of Record'              => 'irb_of_record',
    'Renewal Cycle'              => 'renewal_cycle',
    'First IRB Review'           => 'first_irb_review',
    'Original Approval Date'     => 'approval_date',
    'Last IRB Renewal Date'      => 'last_irb_review',
    'Last seen by IRB'           => 'on_agenda_date',
    'Approved Patient Enrollment' => 'number_of_subjects',
    'Currently Enrolled'         => 'init_enroll',
    'ExemptCite'                 => 'exempt_cite',
    'ExpeditedCite'              => 'expedite_cite',
    'Reviewer(s)'                => 'reviewers',
    'Co-Investigator(s)'         => 'admins',
    'Benefits'                   => 'benefits',
    'Grant/Project(s)'           => 'grant_projects',
    'Division(s)'                => 'divs',
    'Classification(s)'          => 'classifications',
    'Risk(s)'                    => 'risk_category',
    'Ind/Device Type(s)'         => 'inds',
    'Dept/Group(s)'              => 'dept_group',
    'Child Category(s)'          => 'childs',
    'Vulnerable Population(s)'   => 'vul_props',
    'Drug(s)'                    => 'drugs',
    'Site/Affiliation'           => 'sites'
];

// Contact Search columns
$contactSearchColumns = [
    'First'                 => 'first',
    'Middle'                => 'middle',
    'Last'                  => 'last',
    'Contact Type'          => 'contact_type',
    'Company/Dept Name'     => 'company_dept_name',
    'Active'                => 'active',
    'Specialty 1'           => 'specialty_1',
    'Specialty 2'           => 'specialty_2',
    'Street Address 1'    => 'street_address_1',
    'Street Address 2'    => 'street_address_2',
    'City'                  => 'city',
    'State'                 => 'state',
    'Zip'                   => 'zip',
    'Main Phone'              => 'main_phone',
    'Ext'                    => 'ext',
    'Alt Phone'               => 'alt_phone',
    'Fax'                    => 'fax',
    'Alt Fax'                 => 'alt_fax',
    'Cell Phone'              => 'cell_phone',
    'Pager'                  => 'pager',
    'Email'                 => 'email',
];

// SAE Search columns
$saeSearchColumns = [
    'SAE Number'                 => 'internal_sae_number',
    'Ref Number'                 => 'id',
    'Protocol Number'            => 'protocol_id',
    'Event Type'                 => 'type_of_event',
    'Description of Event'       => 'description',
    'Location if Off Site'       => 'location',
    'Patient Age'                => 'age',
    'Patient Sex'                => 'sex',
    'FollowUp Report Flag'       => 'follow_up_report',
    'FU Report Number'           => 'secondary_sae',
    'Ind Report Number'          => 'ind_report_number',
    'Original SAE Number'        => 'original_sae_number',
    'Related Combo'              => 'study_related',
    'Secondary Flag'             => 'secondary_sae',
    'Patient Identifier'         => 'patient_identifier',
    'On Site?'                   => 'local_event',
    'MedWatch Report Filed'      => 'medwatch_report_filed',
    'MedWatch #'                 => 'medwatch_number',
    'Are the Risks Altered?'     => 'risks_altered',
    'New Consent Required?'      => 'new_consent_required',
    'Signed by PI?'              => 'signed_by_pi',
    'Patient Status'             => 'patient_status',
    'Date Event Occured'         => 'date_of_event',
    'Date Received by IRB CoOrd' => 'date_received',
    'Date Signed'                => 'date_signed',
    'Date PI Aware'              => 'date_pi_aware',
];

// CPA Search columns
$cpaSearchColumns = [
    'CPA Number'               => 'cpa_number',
    'Study Number'             => 'protocol_id',
    'Date of change'          => 'date_of_change',
    'Date received'           => 'date_received',
    'Protocol title'          => 'protocol_title',
    'Signed by PI?'            => 'signed_by_pi',
    'Sponsor'                  => 'sponsor',
    'RefNum'                  => 'reference_number',
    'CPA Type'                 => 'cpa_type',
    'IRB Code'                 => 'irb_code',
    'Active'                    => 'active',
    'Study Status'              => 'study_status',
    'IRB of Record'            => 'irb_of_record',
    'Expedited'                 => 'expedited',
    'CR Required'               => 'cr_required',
    'mem CR Required'          => 'mem_cr_required',
];

?>

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
                    <select id="reportType" class="form-select">
                        <option selected disabled>Select Report</option>
                        <option>Study Search</option>
                        <option>SAE Search</option>
                        <option>CPA Search</option>
                        <option>Contact Search</option>

                    </select>
                </div>

                <!-- Filter Selection -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Choose Filter</label>
                    <p class="text-muted small mb-3">
                        After selecting from the 'Available Reports' above, choose a filter under the Search Column
                        and then select a pre-populated filter, then add the filter.
                    </p>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Search Column</label>
                            <select id="searchColumn" class="form-select">

                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Detail Level</label>
                            <select id="detailLevel" class="form-select">

                            </select>
                        </div>
                    </div>

                    <!-- Filter Input -->
                    <div class="row mb-3">
                        <button class="btn btn-primary" onclick="addFilter()">Add Filter</button>

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
                                <tbody id="reportFilterItem">
                                    <!-- Dynamic filter rows will be added here -->

                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <button class="btn btn-outline-danger btn-sm" onclick="clearAllFilters()">
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
                        <button id="generateReport" class="btn btn-primary me-2">
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
                                    <td colspan="5" class="text-center text-muted py-3">
                                    -- No reports generated yet --
                                    </td>
                                </tr>';
                        } else {
                            foreach ($recentReports as $report) {
                                echo '<tr data-report-id="' . htmlspecialchars($report['id']) . '">
                                    <td>' . htmlspecialchars($report['report_name']) . '</td>
                                    <td>' . htmlspecialchars($report['generated_date']) . '</td>
                                    <td>' . htmlspecialchars($report['filters_applied']) . '</td>
                                    <td>' . htmlspecialchars($report['doc_format']) . '</td>
                                    <td>
                                        <button id="downloadReport" class="btn btn-sm btn-outline-primary">
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

<script>
    let activeFilters = [];

    const reportTableMap = {
        "Study Search": "studies",
        "SAE Search": "saes",
        "CPA Search": "cpas",
        "Contact Search": "contacts"
    };

    document.getElementById('reportType').addEventListener('change', function() {
        const selectedReport = this.value;
        const filterSection = document.getElementById('searchColumn');

        // Reset dropdown
        filterSection.innerHTML = '<option value="">Select column</option>';
        

        let columns = {};

        if (selectedReport === 'Study Search') {
            columns = <?php echo json_encode($studySearchColumns); ?>;
        } else if (selectedReport === 'SAE Search') {
            columns = <?php echo json_encode($saeSearchColumns); ?>;
        } else if (selectedReport === 'CPA Search') {
            columns = <?php echo json_encode($cpaSearchColumns); ?>;
        } else if (selectedReport === 'Contact Search') {
            columns = <?php echo json_encode($contactSearchColumns); ?>;
        }

        // Populate options using Object.keys
        Object.keys(columns).forEach(function(label) {
            const value = columns[label];
            const option = document.createElement('option');
            option.value = value;
            option.textContent = label;
            filterSection.appendChild(option);
        });
    });


    document.getElementById('searchColumn').addEventListener('change', function() {

        const selectedColumn = this.value;
        const selectedReport = document.getElementById('reportType').value;
        const detailLevelSection = document.getElementById('detailLevel');

        detailLevelSection.innerHTML = '<option>Loading...</option>';

        const tableName = reportTableMap[selectedReport];

        fetch('/admin/handlers/fetch_detail_levels.php?table=' +
                encodeURIComponent(tableName) +
                '&column=' + encodeURIComponent(selectedColumn))
            .then(response => response.json())
            .then(data => {

                detailLevelSection.innerHTML = '<option value="">Select detail</option>';

                data.forEach(function(level) {
                    const option = document.createElement('option');
                    option.value = level;
                    option.textContent = level;
                    detailLevelSection.appendChild(option);
                });

            })
            .catch(error => {
                console.error('Error fetching detail levels:', error);
                detailLevelSection.innerHTML = '<option>Error loading</option>';
            });

    });

    function addFilter() {
        const searchColumnSelect = document.getElementById('searchColumn');
        const selectedColumnOption = searchColumnSelect.selectedOptions[0];
        const selectedColumnLabel = selectedColumnOption ? selectedColumnOption.textContent : '';
        const selectedColumnValue = searchColumnSelect.value;
        const selectedDetail = document.getElementById('detailLevel').value;

        if (!selectedColumnValue || !selectedDetail) {
            alert('Please select both a search column and detail level.');
            return;
        }

        // Add to activeFilters array
        activeFilters.push({
            column: selectedColumnValue,
            value: selectedDetail
        });

        const filterTableBody = document.querySelector('#reportFilterItem').parentElement;
        const newRow = document.createElement('tr');
        newRow.setAttribute('data-index', activeFilters.length - 1);
        newRow.innerHTML = `
        <td>${selectedColumnLabel}</td>
        <td>${selectedDetail}</td>
        <td>
            <button class="btn btn-sm btn-outline-danger" onclick="removeFilter(this)">
                <i class="fas fa-trash text-danger"></i>
            </button>
        </td>
    `;
        filterTableBody.appendChild(newRow);

    }

    function removeFilter(button) {
        const row = button.closest('tr');
        const index = parseInt(row.getAttribute('data-index'));
        activeFilters.splice(index, 1);
        row.remove();
        // Update indices of remaining rows
        const rows = document.querySelectorAll('#reportFilterItem tr');
        rows.forEach((r, i) => r.setAttribute('data-index', i));
    }

    function clearAllFilters() {
        activeFilters = [];
        document.getElementById('reportFilterItem').innerHTML = '';
    }

    document.getElementById('generateReport').addEventListener('click', function() {

        const reportType = document.getElementById('reportType').value;
        const format = document.querySelector('input[name="outputFormat"]:checked').id.replace("Format", "");

        const formData = new FormData();
        formData.append("reportType", reportType);
        formData.append("format", format);
        formData.append("filters", JSON.stringify(activeFilters)); // you will implement activeFilters array

        fetch("/admin/handlers/generate_report.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement("a");
                a.href = url;
                a.download = "report." + format;
                a.click();
                window.URL.revokeObjectURL(url);
            });
    });

    // Download report handler
    document.querySelectorAll('#downloadReport').forEach(button => {
        button.addEventListener('click', function() {
            const reportId = this.getAttribute('data-report-id');
            window.location.href = '/admin/handlers/download_report.php?id=' + encodeURIComponent(reportId);
        });
    });
</script>