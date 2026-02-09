<?php

// Include CSRF protection
// require_once '../../includes/functions/csrf.php';

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
$usersCount = getUsersCount();
$templatesCount = getTemplatesCount();


?>
<!-- Administration Content -->
<div class="content-wrapper">
    <!-- Page Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="page-header-card">
                        <div class="d-flex align-items-center">
                            <div class="header-icon-wrapper">
                                <i class="fas fa-cogs"></i>
                            </div>
                            <div class="header-content">
                                <h4 class="page-title">Administration</h4>
                                <p class="page-subtitle">Manage system settings, configurations, and templates</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="container-fluid">
            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="premium-card">
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
                    <div class="premium-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-success">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="ms-3">
                                    <h4 class="mb-0"><?php echo $usersCount ?></h4>
                                    <span class="text-muted">User Accounts</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="premium-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-warning">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <div class="ms-3">
                                    <h4 class="mb-0"><?php echo $templatesCount ?></h4>
                                    <span class="text-muted">Templates</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="premium-card">
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
                    <div class="premium-card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-building me-2"></i>
                                Entities
                            </h5>
                            <span class="badge bg-primary">1 Items</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="admin-list">
                                <div class="admin-list-item">
                                    <div class="admin-icon">
                                        <i class="fas fa-address-book text-black"></i>
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
                    <div class="premium-card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-user-cog me-2"></i>
                                Account
                            </h5>
                            <span class="badge bg-success">Pro</span>
                        </div>
                        <div class="card-body p-3">
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
                                        <i class="fas fa-info-circle text-black"></i>
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
                    <div class="premium-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-file-alt me-2"></i>
                                Templates
                            </h5>
                            <span class="badge bg-info">Weekly</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="admin-list">
                                <div class="admin-list-item">
                                    <div class="admin-icon">
                                        <i class="fas fa-upload text-black"></i>
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
                    <div class="premium-card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-project-diagram me-2"></i>
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
                    <div class="premium-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-code me-2"></i>
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
                            <table class="table table-hover table-striped table-premium" id="templateTable">
                                <thead class="table-primary">
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
                const response = await fetch(window.currentEndpoint + '?id=' + id);
                const result = await response.json();
                if (result.success) {
                    data = result.data;
                }
            } catch (error) {
                console.error('Error fetching data:', error);
            }
        }

        // Generate form HTML with existing data
        document.getElementById('dynamicInput').innerHTML = generateFormHTML(config, data);

        // Show the modal
        const editModal = new bootstrap.Modal(document.getElementById('editItemModal'));
        editModal.show();
    }

    // Add new item
    function addItem() {
        window.currentEditId = null;
        window.isEdit = false;

        const type = window.currentType;
        const config = getFieldConfig(type);

        if (!config) {
            console.error('No configuration found for type:', type);
            return;
        }

        // Set modal title
        document.getElementById('editItemModalLabel').textContent = `Add ${config.title}`;

        // Generate empty form HTML
        document.getElementById('dynamicInput').innerHTML = generateFormHTML(config);
    }

    // Save edit
    document.getElementById('saveEditBtn').addEventListener('click', async function() {
        const type = window.currentType;
        const id = window.currentEditId;
        const config = getFieldConfig(type);

        if (!config) {
            console.error('No configuration found for type:', type);
            return;
        }

        // Collect form data
        const formData = {};
        config.fields.forEach(field => {
            formData[field.id] = document.getElementById(field.id).value;
        });

        try {
            const url = window.currentEndpoint + (id ? '?id=' + id : '');
            const method = id ? 'PUT' : 'POST';

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (result.success) {
                // Close modal
                const editModal = bootstrap.Modal.getInstance(document.getElementById('editItemModal'));
                editModal.hide();

                // Refresh the table
                loadData();
            } else {
                alert('Error saving item: ' + result.message);
            }
        } catch (error) {
            console.error('Error saving item:', error);
            alert('Error saving item. Please try again.');
        }
    });

    // Delete item
    document.getElementById('deleteBtn').addEventListener('click', async function() {
        const type = window.currentType;
        const id = window.currentDeleteId;

        if (!id) return;

        try {
            const response = await fetch(window.currentDeleteEndpoint + '?id=' + id, {
                method: 'DELETE'
            });

            const result = await response.json();

            if (result.success) {
                // Close modal
                const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
                deleteModal.hide();

                // Refresh the table
                loadData();
            } else {
                alert('Error deleting item: ' + result.message);
            }
        } catch (error) {
            console.error('Error deleting item:', error);
            alert('Error deleting item. Please try again.');
        }
    });

    // Template Modal Functions
    document.getElementById('templateTable').addEventListener('click', function(e) {
        const row = e.target.closest('tr');
        if (row && row.dataset.id) {
            // Remove previous selection
            document.querySelectorAll('#templateTable tbody tr').forEach(r => r.classList.remove('selected'));
            // Add selection to current row
            row.classList.add('selected');
            // Enable buttons
            document.querySelectorAll('.action-btn').forEach(btn => btn.disabled = false);
            // Store selected template ID
            window.selectedTemplateId = row.dataset.id;
        }
    });

    // Download template
    document.getElementById('downloadBtn').addEventListener('click', function() {
        if (window.selectedTemplateId) {
            window.location.href = '/admin/handlers/download_template.php?id=' + window.selectedTemplateId;
        }
    });

    // Update template defaults
    document.getElementById('updateBtn').addEventListener('click', function() {
        if (window.selectedTemplateId) {
            // Populate the addTemplateModal with selected template data
            const row = document.querySelector('#templateTable tr[data-id="' + window.selectedTemplateId + '"]');
            if (row) {
                const cells = row.querySelectorAll('td');
                document.getElementById('letterType').value = cells[1].textContent;
                document.getElementById('closing').value = cells[3].textContent;
                document.getElementById('signatory').value = cells[4].textContent;
                document.getElementById('title').value = cells[5].textContent;
                document.getElementById('emailSubject').value = cells[6].textContent;
                document.getElementById('emailMessage').value = cells[7].textContent;
                window.updateTemplateId = window.selectedTemplateId;
                // Close template modal and show add modal
                const templateModal = bootstrap.Modal.getInstance(document.getElementById('templateModal'));
                templateModal.hide();
                const addModal = new bootstrap.Modal(document.getElementById('addTemplateModal'));
                addModal.show();
            }
        }
    });

    // Replace template
    document.getElementById('replaceBtn').addEventListener('click', function() {
        if (window.selectedTemplateId) {
            window.replaceTemplateId = window.selectedTemplateId;
            // Close template modal and show replace modal
            const templateModal = bootstrap.Modal.getInstance(document.getElementById('templateModal'));
            templateModal.hide();
            const replaceModal = new bootstrap.Modal(document.getElementById('replaceTemplateModal'));
            replaceModal.show();
        }
    });

    // Delete template
    document.getElementById('deleteBtn').addEventListener('click', function() {
        if (window.selectedTemplateId) {
            if (confirm('Are you sure you want to delete this template?')) {
                fetch('/admin/handlers/delete_template.php?id=' + window.selectedTemplateId, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Refresh the table
                        location.reload();
                    } else {
                        alert('Error deleting template: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error deleting template:', error);
                    alert('Error deleting template. Please try again.');
                });
            }
        }
    });

    // Add new template button
    document.getElementById('addNewBtn').addEventListener('click', function() {
        // Clear form
        document.getElementById('addTemplateForm').reset();
        window.updateTemplateId = null;
        // Close template modal and show add modal
        const templateModal = bootstrap.Modal.getInstance(document.getElementById('templateModal'));
        templateModal.hide();
        const addModal = new bootstrap.Modal(document.getElementById('addTemplateModal'));
        addModal.show();
    });

    // Save template
    document.getElementById('saveTemplateBtn').addEventListener('click', function() {
        const form = document.getElementById('addTemplateForm');
        const formData = new FormData(form);

        // Add update ID if updating
        if (window.updateTemplateId) {
            formData.append('id', window.updateTemplateId);
        }

        fetch('/admin/handlers/add_template.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal
                const addModal = bootstrap.Modal.getInstance(document.getElementById('addTemplateModal'));
                addModal.hide();
                // Refresh the table
                location.reload();
            } else {
                alert('Error saving template: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error saving template:', error);
            alert('Error saving template. Please try again.');
        });
    });

    // Replace template button
    document.getElementById('replaceTemplateBtn').addEventListener('click', function() {
        const form = document.getElementById('replaceTemplateForm');
        const formData = new FormData(form);

        // Add template ID
        formData.append('id', window.replaceTemplateId);

        fetch('/admin/handlers/replace_template.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal
                const replaceModal = bootstrap.Modal.getInstance(document.getElementById('replaceTemplateModal'));
                replaceModal.hide();
                // Refresh the table
                location.reload();
            } else {
                alert('Error replacing template: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error replacing template:', error);
            alert('Error replacing template. Please try again.');
        });
    });

    // Template search
    document.getElementById('templateSearch').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#templateTable tbody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
</script>
