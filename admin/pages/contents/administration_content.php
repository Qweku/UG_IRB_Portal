<?php

$cpaTypes = getCPATypesCount();
$investigators = getInvestigatorCount();
$irbMeetings = getIRBMeetingsCount();
$irbActions = getIRBActionsCount();
$saeEventTypes = getSAETypesCount();
$cpaActions = getCPAActionCount();
$studyCodes = getStudyCodesCount();
$agendaCategories = getAgendaCategoriesCount();
$irbConditions = getIRBConditionCount();

$agendaCategoriesList = getAgendaCategoriesList();
$studyStatus = getStudyStatus();
$studyTypes = getReviewTypes();
$activeCodes = getActiveCodes();


?>
<style>
    .administration-dashboard {
        padding: 20px 0;
    }

    .stat-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }

    .admin-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s;
    }

    .admin-card:hover {
        transform: translateY(-2px);
    }

    .admin-list-item {
        display: flex;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .admin-list-item:last-child {
        border-bottom: none;
    }

    .admin-icon {
        width: 40px;
        height: 40px;
        background: #f8f9fa;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        color: var(--royal-blue);
    }

    .admin-content {
        flex: 1;
    }

    .category-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 10px;
        margin-bottom: 15px;
    }

    .category-item {
        background: #f8f9fa;
        padding: 12px;
        border-radius: 8px;
        text-align: center;
        transition: all 0.2s;
        border: 1px solid #e9ecef;
    }

    .category-item:hover {
        background: var(--royal-blue);
        color: white;
        transform: translateY(-2px);
        cursor: pointer;
    }

    .category-item i {
        display: block;
        margin-bottom: 5px;
        font-size: 1.2rem;
    }

    .category-item span {
        font-size: 0.85rem;
        font-weight: 500;
    }

    .code-tables {
        max-height: 300px;
        overflow-y: auto;
    }

    .code-table-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px;
        border-bottom: 1px solid #f0f0f0;
        transition: background-color 0.2s;
    }

    .code-table-item:hover {
        background-color: #f8f9fa;
        border-radius: 6px;
    }

    .code-name {
        font-weight: 500;
        color: #495057;
    }

    .code-count {
        font-size: 0.85rem;
        color: #6c757d;
        background: #e9ecef;
        padding: 2px 8px;
        border-radius: 12px;
    }

    .bg-purple {
        background-color: #6f42c1 !important;
    }

    .bg-orange {
        background-color: #fd7e14 !important;
    }

    /* Custom scrollbar */
    .code-tables::-webkit-scrollbar {
        width: 6px;
    }

    .code-tables::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .code-tables::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .code-tables::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>
<!-- Administration Content -->
<div class="administration-dashboard">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Administration</h2>
            <p class="text-muted mb-0">Manage system settings, configurations, and templates</p>
        </div>
        <div class="badge bg-primary fs-6">
            <i class="fas fa-cog me-1"></i> Admin Panel
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary">
                            <i class="fas fa-address-book"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0">24</h4>
                            <span class="text-muted">Active Contacts</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-success">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0">156</h4>
                            <span class="text-muted">User Accounts</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-warning">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0">42</h4>
                            <span class="text-muted">Templates</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-info">
                            <i class="fas fa-database"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0">9</h4>
                            <span class="text-muted">Code Tables</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row">
        <!-- Left Column - Entities & Account -->
        <div class="col-md-6">
            <!-- Entities Card -->
            <div class="card admin-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-building me-2 text-primary"></i>
                        Entities
                    </h5>
                    <span class="badge bg-primary">1 Items</span>
                </div>
                <div class="card-body">
                    <div class="admin-list">
                        <div class="admin-list-item">
                            <div class="admin-icon">
                                <i class="fas fa-address-book"></i>
                            </div>
                            <div class="admin-content">
                                <h6 class="mb-1">Contacts</h6>
                                <p class="text-muted mb-0">Manage research contacts</p>
                            </div>
                            <a href="/contacts" class="btn btn-sm btn-outline-primary">Manage</a>
                        </div>
                        <!-- <div class="admin-list-item">
                            <div class="admin-icon">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <div class="admin-content">
                                <h6 class="mb-1">IRB License</h6>
                                <p class="text-muted mb-0">License configuration and renewal</p>
                            </div>
                            <button class="btn btn-sm btn-outline-primary">Configure</button>
                        </div> -->
                    </div>
                </div>
            </div>

            <!-- Account Card -->
            <div class="card admin-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user-cog me-2 text-success"></i>
                        Account
                    </h5>
                    <span class="badge bg-success">Pro</span>
                </div>
                <div class="card-body">
                    <div class="admin-list">
                        <!-- <div class="admin-list-item">
                            <div class="admin-icon">
                                <i class="fas fa-crown text-warning"></i>
                            </div>
                            <div class="admin-content">
                                <h6 class="mb-1">Upgrade to Pro IRB</h6>
                                <p class="text-muted mb-0">Access advanced features and analytics</p>
                            </div>
                            <button class="btn btn-sm btn-warning">Upgrade</button>
                        </div> -->
                        <div class="admin-list-item">
                            <div class="admin-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="admin-content">
                                <h6 class="mb-1">Account Information</h6>
                                <p class="text-muted mb-0">View and update account details</p>
                            </div>
                            <a href="/account-information" class="btn btn-sm btn-outline-primary">View</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Templates Card -->
            <div class="card admin-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt me-2 text-info"></i>
                        Templates
                    </h5>
                    <span class="badge bg-info">Weekly</span>
                </div>
                <div class="card-body">
                    <div class="admin-list">
                        <div class="admin-list-item">
                            <div class="admin-icon">
                                <i class="fas fa-upload"></i>
                            </div>
                            <div class="admin-content">
                                <h6 class="mb-1">Templates Weekly Upload</h6>
                                <p class="text-muted mb-0">Manage weekly template uploads and schedules</p>
                            </div>
                            <button class="btn btn-sm btn-outline-primary">Manage</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Study Groupings & Codes -->
        <div class="col-md-6">
            <!-- Study Groupings Card -->
            <div class="card admin-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-project-diagram me-2 text-purple"></i>
                        Study Groupings
                    </h5>
                    <span class="badge bg-purple">13 Categories</span>
                </div>
                <div class="card-body">
                    <div class="category-grid">
                        <button class="category-item open-modal"
                            data-title="Classifications"
                            data-bs-toggle="modal"
                            data-bs-target="#adminModal"
                            data-endpoint="/admin/handlers/fetch_classifications.php"
                            data-type="classification">
                            <i class="fas fa-tags"></i>
                            <span>Classification(s)</span>
                        </button>
                        <button class="category-item open-modal"
                            data-title="Divisions"
                            data-bs-toggle="modal"
                            data-bs-target="#adminModal"
                            data-endpoint="/admin/handlers/fetch_divisions.php"
                            data-type="division">
                            <i class="fas fa-chart-pie"></i>
                            <span>Divisions</span>
                        </button>
                        <button class="category-item open-modal"
                            data-title="Department/Groups"
                            data-bs-toggle="modal"
                            data-bs-target="#adminModal"
                            data-endpoint="/admin/handlers/fetch_departments.php"
                            data-type="department">
                            <i class="fas fa-sitemap"></i>
                            <span>Dept/Group(s)</span>
                        </button>
                        <button class="category-item open-modal"
                            data-title="Sites"
                            data-bs-toggle="modal"
                            data-bs-target="#adminModal"
                            data-endpoint="/admin/handlers/fetch_sites.php"
                            data-type="site">
                            <i class="fas fa-toggle-on"></i>
                            <span>Site(s)</span>
                        </button>
                        <button class="category-item open-modal"
                            data-title="Benefits"
                            data-bs-toggle="modal"
                            data-bs-target="#adminModal"
                            data-endpoint="/admin/handlers/fetch_benefits.php"
                            data-type="benefit">
                            <i class="fas fa-gift"></i>
                            <span>Benefits</span>
                        </button>
                        <button class="category-item open-modal"
                            data-title="Drugs and Devices"
                            data-bs-toggle="modal"
                            data-bs-target="#adminModal"
                            data-endpoint="/admin/handlers/fetch_drugs.php"
                            data-type="drug">
                            <i class="fas fa-pills"></i>
                            <span>Drug(s)/Specific Devices</span>
                        </button>
                        <button class="category-item open-modal"
                            data-title="Exemption Codes"
                            data-bs-toggle="modal"
                            data-bs-target="#adminModal"
                            data-endpoint="/admin/handlers/fetch_exempts.php"
                            data-type="exempt">
                            <i class="fas fa-ban"></i>
                            <span>Exempt Category(ies)</span>
                        </button>
                        <button class="category-item open-modal"
                            data-title="Expedited Codes"
                            data-bs-toggle="modal"
                            data-bs-target="#adminModal"
                            data-endpoint="/admin/handlers/fetch_expedited.php"
                            data-type="expedited">
                            <i class="fas fa-bolt"></i>
                            <span>Expedited Category</span>
                        </button>
                        <button class="category-item open-modal"
                            data-title="Grants/Projects"
                            data-bs-toggle="modal"
                            data-bs-target="#adminModal"
                            data-endpoint="/admin/handlers/fetch_grants.php"
                            data-type="grant">
                            <i class="fas fa-tags"></i>
                            <span>Grant/Project(s)</span>
                        </button>
                        <button class="category-item open-modal"
                            data-title="IND/Device Types"
                            data-bs-toggle="modal"
                            data-bs-target="#adminModal"
                            data-endpoint="/admin/handlers/fetch_devices.php"
                            data-type="device">
                            <i class="fas fa-flask"></i>
                            <span>IND/Device Type(s)</span>
                        </button>
                        <button class="category-item open-modal"
                            data-title="Risks"
                            data-bs-toggle="modal"
                            data-bs-target="#adminModal"
                            data-endpoint="/admin/handlers/fetch_risks.php"
                            data-type="risk">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Risks</span>
                        </button>
                        <button class="category-item open-modal"
                            data-title="Children Categories"
                            data-bs-toggle="modal"
                            data-bs-target="#adminModal"
                            data-endpoint="/admin/handlers/fetch_children.php"
                            data-type="child">
                            <i class="fas fa-child"></i>
                            <span>Child Category(ies)</span>
                        </button>
                        <button class="category-item open-modal"
                            data-title="Vulnerable Populations"
                            data-bs-toggle="modal"
                            data-bs-target="#adminModal"
                            data-endpoint="/admin/handlers/fetch_vulnerables.php"
                            data-type="vulnerable">
                            <i class="fas fa-shield-alt"></i>
                            <span>Vulnerable Populations</span>
                        </button>
                    </div>
                    <div class="text-center mt-3">
                        <button class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-cog me-1"></i> Configure All
                        </button>
                    </div>
                </div>
            </div>

            <!-- Codes and Tables Card -->
            <div class="card admin-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-code me-2 text-orange"></i>
                        Codes and Tables
                    </h5>
                    <span class="badge bg-orange">9 Tables</span>
                </div>
                <div class="card-body">
                    <div class="code-tables">
                        <div class="code-table-item open-modal"
                            data-title="CPA Types"
                            data-bs-toggle="modal"
                            data-bs-target="#adminModal"
                            data-endpoint="/admin/handlers/fetch_cpa_types.php"
                            data-type="cpa_type">
                            <span class="code-name">CPA Types</span>
                            <span class="code-count"><?php echo $cpaTypes; ?> entries</span>
                        </div>
                        <div class="code-table-item open-modal"
                            data-title="Investigator Specialties"
                            data-bs-toggle="modal"
                            data-bs-target="#adminModal"
                            data-endpoint="/admin/handlers/fetch_investigators.php"
                            data-type="investigator">
                            <span class="code-name">Investigator Specialties</span>
                            <span class="code-count"><?php echo $investigators; ?> entries</span>
                        </div>
                        <div class="code-table-item open-modal"
                            data-title="IRB Meeting Dates"
                            data-bs-toggle="modal"
                            data-bs-target="#adminModal"
                            data-endpoint="/admin/handlers/fetch_irb_meetings.php"
                            data-type="irb_meeting">
                            <span class="code-name">IRB Meeting Dates</span>
                            <span class="code-count"><?php echo $irbMeetings; ?> entries</span>
                        </div>
                        <div class="code-table-item open-modal"
                            data-title="IRB Action Codes"
                            data-bs-toggle="modal"
                            data-bs-target="#adminModal"
                            data-endpoint="/admin/handlers/fetch_irb_actions.php"
                            data-type="irb_action">
                            <span class="code-name">IRB Action Codes</span>
                            <span class="code-count"><?php echo $irbActions; ?> entries</span>
                        </div>
                        <div class="code-table-item open-modal"
                            data-title="SAE Event Types"
                            data-bs-toggle="modal"
                            data-bs-target="#adminModal"
                            data-endpoint="/admin/handlers/fetch_sae_types.php"
                            data-type="sae_type">
                            <span class="code-name">SAE Event Types</span>
                            <span class="code-count"><?php echo $saeEventTypes; ?> entries</span>
                        </div>
                        <div class="code-table-item open-modal"
                            data-title="CPA Action Codes"
                            data-bs-toggle="modal"
                            data-bs-target="#adminModal"
                            data-endpoint="/admin/handlers/fetch_cpa_actions.php"
                            data-type="cpa_action">
                            <span class="code-name">CPA Action Codes</span>
                            <span class="code-count"><?php echo $cpaActions; ?> entries</span>
                        </div>
                        <div class="code-table-item open-modal"
                            data-title="Study Status Codes"
                            data-bs-toggle="modal"
                            data-bs-target="#adminModal"
                            data-endpoint="/admin/handlers/fetch_study_codes.php"
                            data-type="study_codes">
                            <span class="code-name">Study Status Codes</span>
                            <span class="code-count"><?php echo $studyCodes; ?> entries</span>
                        </div>
                        <div class="code-table-item open-modal"
                            data-title="Agenda Categories"
                            data-bs-toggle="modal"
                            data-bs-target="#adminModal"
                            data-endpoint="/admin/handlers/fetch_agenda_categories.php"
                            data-type="agenda_category">
                            <span class="code-name">Agenda Categories</span>
                            <span class="code-count"><?php echo $agendaCategories; ?> entries</span>
                        </div>
                        <div class="code-table-item open-modal"
                            data-title="IRB Conditions"
                            data-bs-toggle="modal"
                            data-bs-target="#adminModal"
                            data-endpoint="/admin/handlers/fetch_irb_conditions.php"
                            data-type="irb_condition">
                            <span class="code-name">IRB Conditions</span>
                            <span class="code-count"><?php echo $irbConditions; ?> entries</span>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <button class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-database me-1"></i> Manage Tables
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="adminModal" class="modal fade" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="adminModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="col-12">
                    <div id="contentArea">
                        <!-- Dynamic content will be loaded here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" onclick="addItem()">Add</button>
            </div>
        </div>

    </div>
</div>

<!-- Edit Modal -->
<div id="editItemModal" class="modal fade" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="editItemModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="dynamicInput" class="mb-3">
                    <!-- Dynamic input field -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEditBtn">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="modal fade" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    Are you sure you want to delete this item?
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <button type="button" class="btn btn-primary" id="deleteBtn">Yes</button>
            </div>
        </div>
    </div>
</div>


<script>
// Configuration for different form types
const formConfigs = {
    // Simple single-field forms
    simple: {
        classification: { field: 'classification_type', label: 'Classification Type' },
        division: { field: 'division_name', label: 'Division Name' },
        site: { field: 'site_name', label: 'Site Name' },
        grant: { field: 'grant_name', label: 'Grant Name' },
        device: { field: 'device_name', label: 'Device Name' },
        risk: { field: 'category_name', label: 'Category Name' },
        child: { field: 'age_range', label: 'Age Range' },
        vulnerable: { field: 'population_type', label: 'Population Type' },
        benefit: { field: 'benefit_type', label: 'Benefit Type' },
        drug: { field: 'drug_name', label: 'Drug Name' },
        investigator:{field: 'specialty_name', label: 'Investigator Specialties'},
        irb_condition:{field: 'condition_name', label: 'IRB Conditions'}
    },
    
    // Complex multi-field forms
    complex: {
        department: {
            title: 'Department/Groups',
            fields: [
                { id: 'department_name', label: 'Department Name', type: 'text', required: true },
                { id: 'address_line_1', label: 'Address Line 1', type: 'text' },
                { id: 'address_line_2', label: 'Address Line 2', type: 'text' },
                { id: 'site', label: 'Site', type: 'text' },
                { id: 'department_id', label: 'Department ID', type: 'text' },
                { id: 'city', label: 'City', type: 'text' },
                { id: 'state', label: 'State', type: 'text' },
                { id: 'zip', label: 'Zip', type: 'text' }
            ]
        },
        exempt: {
            title: 'Exempt Codes',
            fields: [
                { id: 'exempt_cite', label: 'Exempt Cite', type: 'text', required: true },
                { id: 'exempt_description', label: 'Exempt Description', type: 'text' }
            ]
        },
        expedited: {
            title: 'Expedited Codes',
            fields: [
                { id: 'expedite_cite', label: 'Expedited Cite', type: 'text', required: true },
                { id: 'expedite_description', label: 'Expedited Description', type: 'text' }
            ]
        },
        cpa_type: {
            title: 'CPA Types',
            fields: [
                { id: 'type_name', label: 'CPA Type', type: 'text', required: true },
                { id: 'category', label: 'Category', type: 'select', options: <?php echo json_encode($agendaCategoriesList); ?> },
                { id: 'agenda', label: 'Agenda?', type: 'select', options: ['Yes', 'No'] }
            ]
        },
        irb_meeting: {
            title: 'IRB Meeting Date',
            fields: [
                { id: 'meeting_date', label: 'Meeting Date', type: 'date', required: true },
                { id: 'irb_code', label: 'IRB Code', type: 'text', value:"NOGUCHI MEMORIAL INSTITUTE FOR MEDICAL RESEARCH-IRB" }
            ]
        },
        irb_action: {
            title: 'IRB Action Codes',
            fields: [
                { id: 'irb_action', label: 'IRB Action', type: 'text', required: true },
                { id: 'study_status', label: 'Study Status', type: 'select', options: <?php echo json_encode($studyStatus); ?> },
                { id: 'sort_sequence', label: 'SortSeq', type: 'text' }
            ]
        },
        sae_type: {
            title: 'SAE Event Types',
            fields: [
                { id: 'event_type', label: 'Event Type', type: 'text', required: true },
                { id: 'notify_irb', label: 'Notify IRB', type: 'select', options: ['Yes', 'No'] }
            ]
        },
        cpa_action: {
            title: 'CPA Action Codes',
            fields: [
                { id: 'cpa_action', label: 'CPA Action', type: 'text', required: true },
                { id: 'study_status', label: 'Study Status', type: 'select', options: <?php echo json_encode($studyStatus); ?> },
                { id: 'sort_sequence', label: 'SortSeq', type: 'text' }
            ]
        },
        study_codes: {
            title: 'Study Status Codes',
            fields: [
                { id: 'type', label: 'Type', type: 'select', options: <?php echo json_encode($studyTypes); ?> },
                { id: 'study_status', label: 'Study Status', type: 'text', required: true },
                { id: 'study_active_code', label: 'Study Active Code', type: 'select', options: <?php echo json_encode($activeCodes); ?> },
                { id: 'seq', label: 'Seq', type: 'text' }
            ]
        },
        agenda_category: {
            title: 'Agenda Categories',
            fields: [
                { id: 'category_name', label: 'Agenda Category', type: 'text', required: true },
                { id: 'agenda_class_code', label: 'Class Code', type: 'text' },
                { id: 'agenda_print', label: 'Print on Agenda and Minutes As', type: 'text' }
            ]
        }
    }
};

// Get field configuration for a type
function getFieldConfig(type) {
    if (formConfigs.simple[type]) {
        return {
            title: type.charAt(0).toUpperCase() + type.slice(1),
            fields: [{
                id: formConfigs.simple[type].field,
                label: formConfigs.simple[type].label,
                type: 'text',
                required: true
            }]
        };
    }
    return formConfigs.complex[type];
}

// Generate form HTML based on configuration
function generateFormHTML(config, data = {}) {
    let html = '';
    
    config.fields.forEach(field => {
        const value = data[field.id] || (field.id === 'irb_code' ? 'NOGUCHI MEMORIAL INSTITUTE FOR MEDICAL RESEARCH-IRB' : '');
        const requiredAttr = field.required ? 'required' : '';
        
        html += `<div class="row mb-3">`;
        html += `<div class="col-md-12">`;
        html += `<label class="form-label fw-semibold">${field.label}</label>`;
        
        if (field.type === 'select') {
            html += `<select id="${field.id}" class="form-select" ${requiredAttr}>`;
            field.options.forEach(option => {
                const selected = value === option ? 'selected' : '';
                html += `<option value="${option}" ${selected}>${option}</option>`;
            });
            html += `</select>`;
        } else {
            html += `<input type="${field.type}" id="${field.id}" class="form-control" value="${value}" ${requiredAttr}>`;
        }
        
        html += `</div></div>`;
    });
    
    return html;
}

// Main edit function
async function editItem(id, currentValue) {
    window.currentEditId = id;
    window.isEdit = true;
    
    const type = window.currentType;
    const config = getFieldConfig(type);
    
    if (!config) {
        console.error('No configuration found for type:', type);
        return;
    }
    
    // Set modal title
    document.getElementById('editItemModalLabel').textContent = `Edit ${config.title}`;
    
    // Fetch existing data for complex forms
    let data = {};
    const multiFieldTypes = Object.keys(formConfigs.complex);
    if (multiFieldTypes.includes(type)) {
        try {
            const res = await fetch(window.currentEndpoint + '?id=' + id);
            data = await res.json();
            console.log(data);
        } catch (err) {
            console.error('Error fetching item data:', err);
        }
    } else {
        // For simple forms, use the current value
        const fieldName = formConfigs.simple[type].field;
        data[fieldName] = currentValue;
    }
    
    // Generate and set form HTML
    const dynamicInput = document.getElementById('dynamicInput');
    dynamicInput.innerHTML = generateFormHTML(config, data);
    
    // Show modal
    const editModal = new bootstrap.Modal(document.getElementById('editItemModal'));
    editModal.show();
}

// Main add function
function addItem() {
    window.isEdit = false;
    
    const type = window.currentType;
    const config = getFieldConfig(type);
    
    if (!config) {
        console.error('No configuration found for type:', type);
        return;
    }
    
    // Set modal title
    document.getElementById('editItemModalLabel').textContent = `Add ${config.title}`;
    
    // Generate and set form HTML
    const dynamicInput = document.getElementById('dynamicInput');
    dynamicInput.innerHTML = generateFormHTML(config);
    
    // Show modal
    const editModal = new bootstrap.Modal(document.getElementById('editItemModal'));
    editModal.show();
}

// Delete function remains the same
function deleteItem(id) {
    window.currentDeleteId = id;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

// Enhanced save functionality
document.getElementById('saveEditBtn').addEventListener('click', function() {
    const type = window.currentType;
    const config = getFieldConfig(type);
    const endpoint = window.isEdit
        ? `/admin/handlers/update_${type}.php`
        : `/admin/handlers/add_${type}.php`;

    if (!config) {
        alert('Invalid form configuration');
        return;
    }

    // Collect form data
    const formData = {};
    let isValid = true;

    config.fields.forEach(field => {
        const element = document.getElementById(field.id);
        if (element) {
            const value = element.value.trim();

            // Validation
            if (field.required && !value) {
                isValid = false;
                element.classList.add('is-invalid');
                alert(`Please fill in the ${field.label} field.`);
            } else {
                element.classList.remove('is-invalid');
                formData[field.id] = value;
            }
        }
    });

    if (!isValid) return;

    // Add ID for edit operations
    if (window.isEdit) {
        formData.id = window.currentEditId;
    }

    // Show loading state on button
    const saveBtn = document.getElementById('saveEditBtn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';

    // Send request
    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        credentials: 'include',
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        // Reset button state
        saveBtn.disabled = false;
        saveBtn.innerHTML = 'Save';

        if (data.success) {
            // Refresh the table
            refreshTable();
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('editItemModal')).hide();
            // Show success message
            showToast('Operation completed successfully!', 'success');
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        // Reset button state
        saveBtn.disabled = false;
        saveBtn.innerHTML = 'Save';

        console.error('Error:', error);
        alert('An error occurred while saving.');
    });
});

// Enhanced delete functionality
document.getElementById('deleteBtn').addEventListener('click', function() {
    const endpoint = `/admin/handlers/delete_${window.currentType}.php`;
    
    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        credentials: 'include',
        body: JSON.stringify({ id: window.currentDeleteId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Refresh the table
            refreshTable();
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
            // Show success message
            showToast('Item deleted successfully!', 'success');
        } else {
            alert('Error deleting item: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting.');
    });
});

// Utility functions
function refreshTable() {
    fetch(window.currentEndpoint)
        .then(response => response.text())
        .then(html => {
            document.getElementById('contentArea').innerHTML = html;
        })
        .catch(error => console.error('Error refreshing table:', error));
}

function showToast(message, type = 'info') {
    // Simple toast implementation - you can replace with a proper toast library
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 3000);
}

// Initialize modal functionality
document.addEventListener('DOMContentLoaded', function() {
    const modal = new bootstrap.Modal(document.getElementById('adminModal'));
    const title = document.getElementById('adminModalLabel');
    const content = document.getElementById('contentArea');

    document.querySelectorAll('.open-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            const modalTitle = this.getAttribute('data-title');
            const endpoint = this.getAttribute('data-endpoint');
            const type = this.getAttribute('data-type');

            // Store current type and endpoint
            window.currentType = type;
            window.currentEndpoint = endpoint;

            // Update modal title
            title.textContent = modalTitle;

            // Show loading state
            content.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading...</p></div>';

            // Fetch data
            fetch(endpoint)
                .then(response => response.text())
                .then(data => {
                    content.innerHTML = data;
                })
                .catch(error => {
                    console.error('Error loading data:', error);
                    content.innerHTML = '<div class="alert alert-danger text-center">Failed to load data.</div>';
                });

            modal.show();
        });
    });
});
</script>