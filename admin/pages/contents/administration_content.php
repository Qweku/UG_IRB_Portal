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

$letterTypes = getLetterTypes();
$letterTemplates = getLetterTemplates();

$contactsCount = getContactsCount();


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

    /* Template Modal Enhancements */
    #templateModal .modal-content {
        border: none;
        border-radius: 12px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        overflow: hidden;
    }

    #templateModal .modal-header {
        background: linear-gradient(135deg, var(--royal-blue) 0%, #0056b3 100%);
        border-bottom: none;
        padding: 1.5rem 2rem;
    }

    #templateModal .modal-title {
        font-weight: 600;
        font-size: 1.25rem;
    }

    #templateModal .modal-body {
        padding: 2rem;
    }

    #templateModal .table-responsive {
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-bottom: 1rem;
    }

    #templateModal .table {
        margin-bottom: 0;
        border-radius: 8px;
        overflow: hidden;
        table-layout: fixed;
    }

    #templateModal .table thead th {
        background-color: var(--royal-blue);
        color: white;
        border: none;
        font-weight: 600;
        padding: 1rem;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }

    #templateModal .table tbody tr {
        transition: all 0.2s ease;
    }

    #templateModal .table tbody tr:hover {
        background-color: #f8f9fa;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    #templateModal .table tbody td {
        padding: 0.75rem 1rem;
        border: none;
        vertical-align: middle;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 0; /* Allow flex shrinking */
    }

    #templateModal .actions-panel {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
        height: fit-content;
    }

    #templateModal .action-btn {
        margin-bottom: 0.75rem;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    #templateModal .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    #templateModal .action-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    #templateModal .action-btn:not(:disabled) {
        background: linear-gradient(135deg, var(--royal-blue) 0%, #0056b3 100%);
        border: none;
    }

    #templateModal .action-btn:not(:disabled):hover {
        background: linear-gradient(135deg, #0056b3 0%, var(--royal-blue) 100%);
    }

    #templateModal #addNewBtn {
        background: linear-gradient(135deg, var(--bs-success) 0%, #28a745 100%);
        border: none;
        font-weight: 600;
    }

    #templateModal #addNewBtn:hover {
        background: linear-gradient(135deg, #28a745 0%, var(--bs-success) 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    #templateModal #deleteBtn:not(:disabled) {
        background: linear-gradient(135deg, var(--bs-danger) 0%, #dc3545 100%);
        border: none;
    }

    #templateModal #deleteBtn:not(:disabled):hover {
        background: linear-gradient(135deg, #dc3545 0%, var(--bs-danger) 100%);
    }

    #templateModal .form-control {
        border-radius: 8px;
        border: 1px solid #ced4da;
        padding: 0.75rem 1rem;
        transition: all 0.2s ease;
    }

    #templateModal .form-control:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    #templateModal hr {
        border: none;
        height: 1px;
        background: linear-gradient(90deg, transparent, #dee2e6, transparent);
        margin: 1rem 0;
    }

    #replaceTemplateModal {
        z-index: 1060;
    }

    #replaceTemplateModal .modal-backdrop {
        z-index: 1055;
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
                            <h4 class="mb-0"><?php echo $contactsCount ?></h4>
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
                                <h6 class="mb-1">Templates Upload</h6>
                                <p class="text-muted mb-0">Manage template uploads and schedules</p>
                            </div>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#templateModal">Manage</button>
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

<!-- Replace Template Modal -->
<div class="modal fade" id="replaceTemplateModal" tabindex="-1" aria-labelledby="replaceTemplateModalLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="replaceTemplateModalLabel">
                    <i class="fas fa-exchange-alt me-2"></i>Replace Template
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="replaceTemplateForm" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="replaceLetterType" class="form-label fw-semibold">Letter Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="replaceLetterType" name="letter_type" required>
                                    <option value="" disabled>Select Letter Type</option>
                                    <?php foreach ($letterTypes as $type): ?>
                                        <option value="<?php echo htmlspecialchars($type); ?>">
                                            <?php echo htmlspecialchars($type); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="replaceClosing" class="form-label fw-semibold">Closing</label>
                                <input type="text" class="form-control" id="replaceClosing" name="closing" >
                            </div>
                            <div class="mb-3">
                                <label for="replaceSignatory" class="form-label fw-semibold">Signatory</label>
                                <input type="text" class="form-control" id="replaceSignatory" name="signatory" placeholder="e.g., Prof. A. Mensah">
                            </div>
                            <div class="mb-3">
                                <label for="replaceTitle" class="form-label fw-semibold">Title</label>
                                <input type="text" class="form-control" id="replaceTitle" name="title" placeholder="e.g., IRB Chair">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="replaceDocumentUpload" class="form-label fw-semibold">New Document Upload</label>
                                <input type="file" class="form-control" id="replaceDocumentUpload" name="document" accept=".doc,.docx,.pdf">
                                <div class="form-text">Accepted formats: .doc, .docx, .pdf (leave empty to keep current file)</div>
                            </div>
                            <div class="mb-3">
                                <label for="replaceEmailSubject" class="form-label fw-semibold">Default Email Subject</label>
                                <input type="text" class="form-control" id="replaceEmailSubject" name="email_subject" placeholder="e.g., SAE Notification">
                            </div>
                            <div class="mb-3">
                                <label for="replaceEmailMessage" class="form-label fw-semibold">Email Message</label>
                                <textarea class="form-control" id="replaceEmailMessage" name="email_body" rows="4" placeholder="Please find attached..."></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="replaceTemplateBtn">
                    <i class="fas fa-exchange-alt me-1"></i>Replace Template
                </button>
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


<!-- Template Modal -->
<div class="modal fade" id="templateModal" tabindex="-1" aria-labelledby="templateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title text-white" id="templateModalLabel">
                    <i class="fas fa-file-upload me-2"></i>
                    Upload / Modify Template – NOGUCHI MEMORIAL INSTITUTE FOR MEDICAL RESEARCH – IRB
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <div class="row">

                    <!-- LEFT: TABLE -->
                    <div class="col-md-9">

                        <div class="form-group mb-3">
                            <input type="text" id="templateSearch" class="form-control"
                                placeholder="Search templates...">
                        </div>

                        <div class="table-responsive" style="max-height: 400px; overflow-y:auto;">
                            <table class="table table-hover table-striped" id="templateTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="300px">IRB Code</th>
                                        <th width="200px">Letter Type</th>
                                        <th width="250px">Letter Name</th>
                                        <th width="250px">Closing</th>
                                        <th width="250px">Signatory</th>
                                        <th width="200px">Title</th>
                                        <th width="250px">Default Email Subject</th>
                                        <th width="250px">Default Email Body</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($letterTemplates)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">
                                                No templates found. Please add a new template.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php foreach ($letterTemplates as $template): ?>
                                        <tr id="templateRowId" data-id="<?php echo htmlspecialchars($template['id']); ?>">
                                            <td><?php echo htmlspecialchars($template['irb_code']); ?></td>
                                            <td><?php echo htmlspecialchars($template['letter_type']); ?></td>
                                            <td><?php echo htmlspecialchars($template['letter_name']); ?></td>
                                            <td><?php echo htmlspecialchars($template['closing']); ?></td>
                                            <td><?php echo htmlspecialchars($template['signatory']); ?></td>
                                            <td><?php echo htmlspecialchars($template['title']); ?></td>
                                            <td><?php echo htmlspecialchars($template['email_subject']); ?></td>
                                            <td><?php echo htmlspecialchars($template['email_message']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <!-- Repeat dynamically -->
                                </tbody>
                            </table>
                        </div>

                    </div>

                    <!-- RIGHT: ACTIONS -->
                    <div class="col-md-3">
                        <div class="actions-panel">
                            <h6 class="mb-3 text-center fw-bold text-primary">
                                <i class="fas fa-cogs me-1"></i>Actions
                            </h6>
                            <button class="btn w-100 mb-2 action-btn" disabled id="downloadBtn">
                                <i class="fas fa-download me-1"></i>Download
                            </button>
                            <button class="btn w-100 mb-2 action-btn" disabled id="updateBtn">
                                <i class="fas fa-edit me-1"></i>Update Defaults
                            </button>
                            <button class="btn w-100 mb-2 action-btn" disabled id="replaceBtn">
                                <i class="fas fa-exchange-alt me-1"></i>Replace Template
                            </button>
                            <button class="btn w-100 mb-2 action-btn" disabled id="deleteBtn">
                                <i class="fas fa-trash me-1"></i>Delete
                            </button>
                            <hr>
                            <button class="btn w-100 text-white" id="addNewBtn">
                                <i class="fas fa-plus me-1 "></i>Add New Template
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- Add New Template Modal -->
<div class="modal fade" id="addTemplateModal" tabindex="-1" aria-labelledby="addTemplateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTemplateModalLabel">
                    <i class="fas fa-plus me-2"></i>Add New Template
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addTemplateForm" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="letterType" class="form-label fw-semibold">Letter Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="letterType" name="letter_type" required>
                                    <option value="" disabled selected>Select Letter Type</option>
                                    <?php foreach ($letterTypes as $type): ?>
                                        <option value="<?php echo htmlspecialchars($type); ?>">
                                            <?php echo htmlspecialchars($type); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="closing" class="form-label fw-semibold">Closing</label>
                                <input type="text" class="form-control" id="closing" name="closing" placeholder="e.g., Yours Sincerely">
                            </div>
                            <div class="mb-3">
                                <label for="signatory" class="form-label fw-semibold">Signatory</label>
                                <input type="text" class="form-control" id="signatory" name="signatory" placeholder="e.g., Prof. A. Mensah">
                            </div>
                            <div class="mb-3">
                                <label for="title" class="form-label fw-semibold">Title</label>
                                <input type="text" class="form-control" id="title" name="title" placeholder="e.g., IRB Chair">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="documentUpload" class="form-label fw-semibold">Document Upload <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="documentUpload" name="document" accept=".doc,.docx,.pdf" required>
                                <div class="form-text">Accepted formats: .doc, .docx, .pdf</div>
                            </div>
                            <div class="mb-3">
                                <label for="emailSubject" class="form-label fw-semibold">Default Email Subject</label>
                                <input type="text" class="form-control" id="emailSubject" name="email_subject" placeholder="e.g., SAE Notification">
                            </div>
                            <div class="mb-3">
                                <label for="emailMessage" class="form-label fw-semibold">Email Message</label>
                                <textarea class="form-control" id="emailMessage" name="email_body" rows="4" placeholder="Please find attached..."></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="saveTemplateBtn">
                    <i class="fas fa-save me-1"></i>Save Template
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Configuration for different form types
    const formConfigs = {
        // Simple single-field forms
        simple: {
            classification: {
                field: 'classification_type',
                label: 'Classification Type'
            },
            division: {
                field: 'division_name',
                label: 'Division Name'
            },
            site: {
                field: 'site_name',
                label: 'Site Name'
            },
            grant: {
                field: 'grant_name',
                label: 'Grant Name'
            },
            device: {
                field: 'device_name',
                label: 'Device Name'
            },
            risk: {
                field: 'category_name',
                label: 'Category Name'
            },
            child: {
                field: 'age_range',
                label: 'Age Range'
            },
            vulnerable: {
                field: 'population_type',
                label: 'Population Type'
            },
            benefit: {
                field: 'benefit_type',
                label: 'Benefit Type'
            },
            drug: {
                field: 'drug_name',
                label: 'Drug Name'
            },
            investigator: {
                field: 'specialty_name',
                label: 'Investigator Specialties'
            },
            irb_condition: {
                field: 'condition_name',
                label: 'IRB Conditions'
            }
        },

        // Complex multi-field forms
        complex: {
            department: {
                title: 'Department/Groups',
                fields: [{
                        id: 'department_name',
                        label: 'Department Name',
                        type: 'text',
                        required: true
                    },
                    {
                        id: 'address_line_1',
                        label: 'Address Line 1',
                        type: 'text'
                    },
                    {
                        id: 'address_line_2',
                        label: 'Address Line 2',
                        type: 'text'
                    },
                    {
                        id: 'site',
                        label: 'Site',
                        type: 'text'
                    },
                    {
                        id: 'department_id',
                        label: 'Department ID',
                        type: 'text'
                    },
                    {
                        id: 'city',
                        label: 'City',
                        type: 'text'
                    },
                    {
                        id: 'state',
                        label: 'State',
                        type: 'text'
                    },
                    {
                        id: 'zip',
                        label: 'Zip',
                        type: 'text'
                    }
                ]
            },
            exempt: {
                title: 'Exempt Codes',
                fields: [{
                        id: 'exempt_cite',
                        label: 'Exempt Cite',
                        type: 'text',
                        required: true
                    },
                    {
                        id: 'exempt_description',
                        label: 'Exempt Description',
                        type: 'text'
                    }
                ]
            },
            expedited: {
                title: 'Expedited Codes',
                fields: [{
                        id: 'expedite_cite',
                        label: 'Expedited Cite',
                        type: 'text',
                        required: true
                    },
                    {
                        id: 'expedite_description',
                        label: 'Expedited Description',
                        type: 'text'
                    }
                ]
            },
            cpa_type: {
                title: 'CPA Types',
                fields: [{
                        id: 'type_name',
                        label: 'CPA Type',
                        type: 'text',
                        required: true
                    },
                    {
                        id: 'category',
                        label: 'Category',
                        type: 'select',
                        options: <?php echo json_encode($agendaCategoriesList); ?>
                    },
                    {
                        id: 'agenda',
                        label: 'Agenda?',
                        type: 'select',
                        options: ['Yes', 'No']
                    }
                ]
            },
            irb_meeting: {
                title: 'IRB Meeting Date',
                fields: [{
                        id: 'meeting_date',
                        label: 'Meeting Date',
                        type: 'date',
                        required: true
                    },
                    {
                        id: 'irb_code',
                        label: 'IRB Code',
                        type: 'text',
                        value: "NOGUCHI MEMORIAL INSTITUTE FOR MEDICAL RESEARCH-IRB"
                    }
                ]
            },
            irb_action: {
                title: 'IRB Action Codes',
                fields: [{
                        id: 'irb_action',
                        label: 'IRB Action',
                        type: 'text',
                        required: true
                    },
                    {
                        id: 'study_status',
                        label: 'Study Status',
                        type: 'select',
                        options: <?php echo json_encode($studyStatus); ?>
                    },
                    {
                        id: 'sort_sequence',
                        label: 'SortSeq',
                        type: 'text'
                    }
                ]
            },
            sae_type: {
                title: 'SAE Event Types',
                fields: [{
                        id: 'event_type',
                        label: 'Event Type',
                        type: 'text',
                        required: true
                    },
                    {
                        id: 'notify_irb',
                        label: 'Notify IRB',
                        type: 'select',
                        options: ['Yes', 'No']
                    }
                ]
            },
            cpa_action: {
                title: 'CPA Action Codes',
                fields: [{
                        id: 'cpa_action',
                        label: 'CPA Action',
                        type: 'text',
                        required: true
                    },
                    {
                        id: 'study_status',
                        label: 'Study Status',
                        type: 'select',
                        options: <?php echo json_encode($studyStatus); ?>
                    },
                    {
                        id: 'sort_sequence',
                        label: 'SortSeq',
                        type: 'text'
                    }
                ]
            },
            study_codes: {
                title: 'Study Status Codes',
                fields: [{
                        id: 'type',
                        label: 'Type',
                        type: 'select',
                        options: <?php echo json_encode($studyTypes); ?>
                    },
                    {
                        id: 'study_status',
                        label: 'Study Status',
                        type: 'text',
                        required: true
                    },
                    {
                        id: 'study_active_code',
                        label: 'Study Active Code',
                        type: 'select',
                        options: <?php echo json_encode($activeCodes); ?>
                    },
                    {
                        id: 'seq',
                        label: 'Seq',
                        type: 'text'
                    }
                ]
            },
            agenda_category: {
                title: 'Agenda Categories',
                fields: [{
                        id: 'category_name',
                        label: 'Agenda Category',
                        type: 'text',
                        required: true
                    },
                    {
                        id: 'agenda_class_code',
                        label: 'Class Code',
                        type: 'text'
                    },
                    {
                        id: 'agenda_print',
                        label: 'Print on Agenda and Minutes As',
                        type: 'text'
                    }
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
        const endpoint = window.isEdit ?
            `/admin/handlers/update_${type}.php` :
            `/admin/handlers/add_${type}.php`;

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
                body: JSON.stringify({
                    id: window.currentDeleteId
                })
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

    function showToast(message, type = 'success') {
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
        // const modal = new bootstrap.Modal(document.getElementById('adminModal'));
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

// Handle Replace Template button
document.getElementById('replaceBtn').addEventListener('click', function() {
    if (!selectedTemplateId) {
        alert('Please select a template to replace.');
        return;
    }

    // Fetch current template data to pre-fill the form
    fetch('/admin/handlers/fetch_template.php?id=' + selectedTemplateId)
        .then(response => response.json())
        .then(data => {
            if (data) {
                document.getElementById('replaceLetterType').value = data.letter_type || '';
                document.getElementById('replaceClosing').value = data.closing || '';
                document.getElementById('replaceSignatory').value = data.signatory || '';
                document.getElementById('replaceTitle').value = data.title || '';
                document.getElementById('replaceEmailSubject').value = data.email_subject || '';
                document.getElementById('replaceEmailMessage').value = data.email_message || '';
            }
            const replaceModal = new bootstrap.Modal(document.getElementById('replaceTemplateModal'));
            replaceModal.show();
        })
        .catch(error => {
            console.error('Error fetching template data:', error);
            alert('Failed to load template data.');
        });
});

// Handle Replace Template Save
document.getElementById('replaceTemplateBtn').addEventListener('click', function() {
    const form = document.getElementById('replaceTemplateForm');
    const formData = new FormData(form);

    // Basic validation
    const letterType = formData.get('letter_type');
    const documentFile = formData.get('document');

    if (!letterType) {
        alert('Please select a letter type.');
        return;
    }

    // Note: File is optional for replace, but if provided, validate
    if (documentFile && documentFile.size > 0) {
        const allowedTypes = ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf'];
        if (!allowedTypes.includes(documentFile.type)) {
            alert('Invalid file type. Only .doc, .docx, and .pdf files are allowed.');
            return;
        }
        if (documentFile.size > 10 * 1024 * 1024) {
            alert('File size too large. Maximum size is 10MB.');
            return;
        }
    }

    // Show loading state
    const replaceBtn = document.getElementById('replaceTemplateBtn');
    replaceBtn.disabled = true;
    replaceBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Replacing...';

    // Add template ID
    formData.append('id', selectedTemplateId);

    // Send request
    fetch('/admin/handlers/update_template.php', {
            method: 'POST',
            body: formData,
            credentials: 'include'
        })
        .then(response => response.json())
        .then(data => {
            // Reset button state
            replaceBtn.disabled = false;
            replaceBtn.innerHTML = '<i class="fas fa-exchange-alt me-1"></i>Replace Template';

            if (data.success) {
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('replaceTemplateModal')).hide();
                // Reset form
                form.reset();
                // Refresh template table (assuming there's a refresh function)
                if (typeof refreshTemplateTable === 'function') {
                    refreshTemplateTable();
                }
                // Show success message
                showToast('Template replaced successfully!', 'success');
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            // Reset button state
            replaceBtn.disabled = false;
            replaceBtn.innerHTML = '<i class="fas fa-exchange-alt me-1"></i>Replace Template';

            console.error('Error:', error);
            alert('An error occurred while replacing the template.');
        });
});

// Function to refresh template table
function refreshTemplateTable() {
    // Assuming the table is loaded via PHP, we can reload the page or fetch new data
    // For simplicity, reload the page
    location.reload();
}
</script>


<script>
    let selectedTemplateId = null;

    document.querySelectorAll('#templateTable tbody tr').forEach(row => {
        row.addEventListener('click', function() {

            // Clear previous selection
            document.querySelectorAll('#templateTable tr')
                .forEach(r => r.classList.remove('table-active'));

            // Set new selection
            this.classList.add('table-active');
            selectedTemplateId = this.dataset.id;

            console.log("Selected template id: "+ selectedTemplateId);

            // Enable action buttons
            document.querySelectorAll('.action-btn').forEach(btn => {
                btn.disabled = false;
                btn.classList.remove('btn-secondary');
                btn.classList.add(btn.id === 'deleteBtn' ? 'btn-danger' : 'btn-primary');
            });
        });
    });
</script>

<script>
    document.getElementById('templateSearch').addEventListener('keyup', function() {
        const value = this.value.toLowerCase();

        document.querySelectorAll('#templateTable tbody tr').forEach(row => {
            const match = row.innerText.toLowerCase().includes(value);
            row.style.display = match ? '' : 'none';
        });
    });
</script>

<script>
    // Handle Add New Template button
    document.getElementById('addNewBtn').addEventListener('click', function() {
        const addModal = new bootstrap.Modal(document.getElementById('addTemplateModal'));
        addModal.show();
    });

    // Handle Save Template
    document.getElementById('saveTemplateBtn').addEventListener('click', function() {
        const form = document.getElementById('addTemplateForm');
        const formData = new FormData(form);

        // Basic validation
        const letterType = formData.get('letter_type');
        const documentFile = formData.get('document');

        if (!letterType) {
            alert('Please select a letter type.');
            return;
        }

        if (!documentFile || documentFile.size === 0) {
            alert('Please select a document to upload.');
            return;
        }

        // Show loading state
        const saveBtn = document.getElementById('saveTemplateBtn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';

        // Add IRB code
        formData.append('irb_code', 'NOGUCHI MEMORIAL INSTITUTE FOR MEDICAL RESEARCH-IRB');

        // Send request
        fetch('/admin/handlers/add_template.php', {
                method: 'POST',
                body: formData,
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                // Reset button state
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save me-1"></i>Save Template';

                if (data.success) {
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('addTemplateModal')).hide();
                    // Reset form
                    form.reset();
                    // Refresh template table (assuming there's a refresh function)
                    if (typeof refreshTemplateTable === 'function') {
                        refreshTemplateTable();
                    }
                    // Show success message
                    showToast('Template added successfully!', 'success');
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                // Reset button state
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save me-1"></i>Save Template';

                console.error('Error:', error);
                alert('An error occurred while saving the template.');
            });
    });
</script>