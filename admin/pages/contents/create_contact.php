<?php

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login');
    exit;
}

$specialties = getSpecialties();
$allContacts = getAllContacts();

$contactId = $_GET['id'] ?? null;
if ($contactId !== null) {
    error_log("Contact ID from GET: " . $contactId);
}
$contactDocs = getContactDocs($contactId);


?>



<!-- Contacts Management Content -->
<div class="contacts-management p-5">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Contacts</h2>
        <!-- <button class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Create New Contact
        </button> -->
    </div>

    <!-- Instructions -->
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle me-2"></i>
        Search or scroll through the contacts list. Click a contact to edit their details, or fill the form on the right to create a new contact.
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="row">
            <!-- Left Column - Contact List & Actions -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary" id="inviteBtn" disabled>
                                <i class="fas fa-envelope me-1"></i> Invite
                            </button>
                            <button type="button" class="btn btn-secondary" id="permissionsBtn" data-bs-target="#permissionsModal" data-bs-toggle="modal" disabled>
                                <i class="fas fa-key me-1"></i> Permissions
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Contacts Table -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">Contacts List</h6>
                    </div>
                    <div class="card-body p-0">
                        <!-- Search Bar -->
                        <div class="p-2">
                            <input type="text" id="contactSearch" class="form-control form-control-sm"
                                placeholder="Search contacts...">
                        </div>

                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table mb-0">
                                <thead class="sticky-top">
                                    <tr>
                                        <!-- <th width="80px">Title</th> -->
                                        <th>Name</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="contactsTableBody">
                                    <?php if (empty($allContacts)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">
                                                No contacts found.
                                            </td>
                                        </tr>
                                    <?php endif; ?>

                                    <?php foreach ($allContacts as $contact): ?>
                                        <tr class="contact-row"
                                            data-contact='<?= json_encode($contact, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                                            

                                            <!-- Show last name and first but if empty show company name -->
                                            <?php if (empty($contact['first_name']) && empty($contact['last_name'])): ?>
                                                <td><?= htmlspecialchars($contact['company_dept_name']) ?></td>
                                            <?php else: ?>
                                                <td><?= htmlspecialchars($contact['last_name'] . ', ' . $contact['first_name']) ?></td>
                                            <?php endif; ?>

                                            <td><button class="btn btn-sm btn-danger" onclick="deleteContact(<?= $contact['id'] ?>)"><i class="fas fa-trash"></i></button></td>
                                        </tr>
                                    <?php endforeach; ?>


                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Contact Details -->
            <div class="col-md-8">
                <form id="contactForm">
                    <input type="hidden" name="id" id="contact_id">

                    <!-- Basic Information Card -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold">Contact Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Title</label>
                                    <input type="text" name="title" class="form-control" placeholder="Enter title">
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">Last Name</label>
                                    <input type="text" name="last_name" class="form-control" placeholder="Enter last name">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">First Name</label>
                                    <input type="text" name="first_name" class="form-control" placeholder="Enter first name">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Main Phone #</label>
                                    <input type="tel" name="main_phone" class="form-control" placeholder="Enter main phone">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Cell Phone #</label>
                                    <input type="tel" name="cell_phone" class="form-control" placeholder="Enter cell phone">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Fax</label>
                                    <input type="tel" name="fax" class="form-control" placeholder="Enter fax">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Details Cards -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold">Professional Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Email</label>
                                        <input type="email" name="email" class="form-control" placeholder="Enter email address">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Specialty</label>
                                        <select type="text" name="specialty_1" class="form-select">
                                            <?php foreach ($specialties as $spec): ?>
                                                <option value="<?= htmlspecialchars($spec) ?>"><?php echo htmlspecialchars($spec); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Specialty 2</label>
                                        <select type="text" name="specialty_2" class="form-select">
                                            <?php foreach ($specialties as $spec): ?>
                                                <option value="<?= htmlspecialchars($spec) ?>"><?php echo htmlspecialchars($spec); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Research Education</label>
                                        <input type="text" name="research_education" class="form-control" placeholder="Enter research education">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Logon Name</label>
                                        <input type="text" name="logon_name" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold">Address Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Company Name</label>
                                        <input type="text" class="form-control" name="company_dept_name" placeholder="Document/Company Name/Superwriting (Use Dept Address)">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Direct Address 1</label>
                                        <input type="text" name="street_address_1" class="form-control" placeholder="Street Address 1">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Direct Address 2</label>
                                        <input type="text" name="street_address_2" class="form-control" placeholder="Street Address 2">
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">City</label>
                                            <input type="text" name="city" class="form-control" placeholder="City">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">State</label>
                                            <input type="text" name="state" class="form-control" placeholder="State">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Alternate Phone</label>
                                        <input type="tel" name="alt_phone" class="form-control" placeholder="Alternate Phone">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Alternate Fax</label>
                                        <input type="tel" name="alt_fax" class="form-control" placeholder="Alternate Fax">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold">Additional Details</h6>
                                </div>
                                <div class="card-body">
                                    <!-- <div class="mb-3">
                                        <label class="form-label fw-semibold">Foreign Phone</label>
                                        <input type="tel" class="form-control" placeholder="Foreign Phone">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Voice Mail</label>
                                        <input type="text" class="form-control" placeholder="Voice Mail">
                                    </div> -->
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Permanent or Alternate Member</label>
                                        <select name="contact_type" class="form-select">
                                            <option>Permanent</option>
                                            <option>Alternate</option>
                                            <option>Ancillary</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold"></label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <input type="checkbox" class="form-check" name="contact_active" name="active" value="1"
                                                    onchange="toggleActive()">
                                            </span>
                                            <span class="input-group-text">
                                                <label class="form-label">Active?</label>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold">Member Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Agent Number</label>
                                        <input type="text" class="form-control" placeholder="Agent Number">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Bucket Member</label>
                                        <input type="text" class="form-control" placeholder="Bucket Member">
                                    </div>
                                    
                                    </div>
                                </div>
                            </div>
                        </div> -->
                        </div>

                        <!-- Supporting Documents -->
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 fw-bold">Supporting Documents</h6>
                            </div>
                            <div class="card-body" id="documents-upload-area">

                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>File Name</th>
                                                <th>Comments</th>
                                                <th>File Changed</th>
                                                <th width="100px">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="documents-tbody">
                                            <?php if (empty($contactDocs)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted py-3">
                                                        No data available in table
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                            <?php foreach ($contactDocs as $doc): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($doc['file_name']) ?></td>
                                                    <td><?= htmlspecialchars($doc['comments']) ?></td>
                                                    <td><?= htmlspecialchars(date('Y-m-d', strtotime($doc['uploaded_at']))) ?></td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="removeFile(<?= $doc['id'] ?>)">Remove</button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-3">
                                    <button type="button" id="uploadDoc" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('fileInput').click();">
                                        <i class="fas fa-upload me-1"></i> Upload Document
                                    </button>
                                    <input type="hidden" name="MAX_FILE_SIZE" value="10485760">
                                    <input type="file"
                                        id="fileInput"
                                        accept="application/pdf,.doc,.docx"
                                        multiple
                                        style="display: none;">

                                    <span id="fileNameDisplay" class="ms-2 text-muted"></span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 d-flex justify-content-end">
                            <button type="submit" id="addContactBtn" class="btn btn-success">
                                <i class="fas fa-save me-1"></i>
                                <span class="btn-text">Save Contact</span>
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            </button>
                        </div>


                </form>
            </div>
        </div>
    </div>

    <!-- Invite Confirmation Modal -->
    <div class="modal fade" id="inviteModal" tabindex="-1" aria-labelledby="inviteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="inviteModalLabel">Confirm Invite</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to send an invitation email to the selected contact?</p>
                    <p>This will generate a username and password and send them to the contact's email address.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmInviteBtn">Send Invite</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Permissions Modal -->
<div class="modal fade" id="permissionsModal" tabindex="-1" aria-labelledby="permissionsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="permissionsModalLabel">Permissions</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <div class="card permission-card mb-3">
              <div class="card-body">
                <h6 class="card-title"><i class="bi bi-building"></i> Office Access</h6>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="adminAccess" checked>
                  <label class="form-check-label" for="adminAccess"><i class="bi bi-shield-check text-primary"></i> Administrator</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="readWriteAccess">
                  <label class="form-check-label" for="readWriteAccess"><i class="bi bi-pencil-square text-success"></i> Read & Write</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="readOnlyAccess">
                  <label class="form-check-label" for="readOnlyAccess"><i class="bi bi-eye text-info"></i> Read Only</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="auditLogAccess">
                  <label class="form-check-label" for="auditLogAccess"><i class="bi bi-journal-text text-warning"></i> Access Audit Logs</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="exportDataAccess">
                  <label class="form-check-label" for="exportDataAccess"><i class="bi bi-download text-secondary"></i> Export System Data</label>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card permission-card mb-3">
              <div class="card-body">
                <h6 class="card-title"><i class="bi bi-calendar-event"></i> Agenda Management</h6>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="replaceAgendaAccess">
                  <label class="form-check-label" for="replaceAgendaAccess"><i class="bi bi-arrow-repeat text-primary"></i> Replace Agenda</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="uploadDocsAccess" checked>
                  <label class="form-check-label" for="uploadDocsAccess"><i class="bi bi-cloud-upload text-success"></i> Upload Documents</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="deleteOwnDocsAccess" checked>
                  <label class="form-check-label" for="deleteOwnDocsAccess"><i class="bi bi-trash text-danger"></i> Delete Own Documents</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="deleteGroupDocsAccess">
                  <label class="form-check-label" for="deleteGroupDocsAccess"><i class="bi bi-trash2 text-warning"></i> Delete Group Documents</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="deleteAllDocsAccess">
                  <label class="form-check-label" for="deleteAllDocsAccess"><i class="bi bi-trash3 text-danger"></i> Delete All Documents</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="editCommentsAccess">
                  <label class="form-check-label" for="editCommentsAccess"><i class="bi bi-chat-dots text-info"></i> Edit Own Comments</label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="savePermissions">Save</button>
      </div>
      </div>

    </div>
  </div>
</div>

<style>
:root {
  --primary-subtle: rgba(102, 126, 234, 0.1);
  --success-subtle: rgba(56, 239, 125, 0.1);
}

/* User Summary */
.user-summary-card {
  padding: 20px;
  background: #f8f9fa;
  border-radius: 12px;
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.user-avatar {
  width: 60px;
  height: 60px;
  font-size: 24px;
}

/* Permission Cards */
.permission-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  border-radius: 0.375rem;
}

.permission-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
}

.card-header {
  border-bottom: 1px solid #dee2e6;
}

/* Role Options */
.role-option .btn {
  border: 2px solid #e2e8f0;
  border-radius: 0.375rem;
  transition: all 0.3s ease;
}

.role-option .btn-check:checked + .btn {
  border-color: #667eea;
  background-color: var(--primary-subtle);
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.15);
}

.role-option.active .btn {
  border-color: #667eea;
  background-color: var(--primary-subtle);
}

.role-icon {
  width: 50px;
  height: 50px;
  background: rgba(102, 126, 234, 0.1);
  border-radius: 0.375rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Card Checkboxes */
.card-check .form-check-input {
  margin-top: 0;
  margin-right: 10px;
}

.card-check .form-check-label {
  cursor: pointer;
  transition: all 0.2s ease;
}

.card-check .form-check-input:checked + .form-check-label {
  border-color: #667eea;
  background-color: var(--primary-subtle);
}

.card-check:hover .form-check-label {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

/* Permission Grid */
.permission-grid {
  display: grid;
  gap: 12px;
}

/* Form Switches */
.form-switch .form-check-input {
  width: 3em;
  height: 1.5em;
  background-color: #e2e8f0;
  border-color: #e2e8f0;
}

.form-switch .form-check-input:checked {
  background-color: #667eea;
  border-color: #667eea;
}

/* Stats Cards */
.stat-card {
  transition: transform 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.stat-card h3 {
  font-weight: 800;
}

/* Badges */
.badge {
  font-weight: 500;
  letter-spacing: 0.3px;
}

.bg-primary-subtle {
  background-color: var(--primary-subtle) !important;
}

.bg-success-subtle {
  background-color: var(--success-subtle) !important;
}

/* Access Level Badge */
.access-level .badge {
  font-size: 1rem;
  padding: 8px 20px;
  border-radius: 20px;
}

/* Responsive Design */
@media (max-width: 992px) {
  .modal-dialog {
    margin: 1rem;
  }
  
  .permission-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 576px) {
  .modal-body {
    padding: 1.5rem !important;
  }
  
  .stat-card h3 {
    font-size: 1.5rem;
  }
  
  .user-avatar {
    width: 50px;
    height: 50px;
    font-size: 20px;
  }
}

/* Animation for active items */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(5px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.role-option, .card-check {
  animation: fadeIn 0.3s ease forwards;
}

/* Smooth transitions */
.form-check-input, .btn, .badge {
  transition: all 0.2s ease;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const permissionsModal = document.getElementById('permissionsModal');
  
  // Permission tracking
  let permissionStates = {
    office: {
      enabled: true,
      role: 'admin',
      auditLog: true,
      exportData: false
    },
    agenda: {
      enabled: true,
      replaceAgenda: false,
      uploadDocs: true,
      deleteUserDocs: true,
      deleteGroupDocs: false,
      deleteAllDocs: false,
      editComments: false
    }
  };
  
  // Initialize event listeners
  function initializeListeners() {
    // Office toggle
    const officeToggle = document.getElementById('officeToggle');
    const agendaToggle = document.getElementById('agendaToggle');
    
    if (officeToggle) {
      officeToggle.addEventListener('change', function() {
        permissionStates.office.enabled = this.checked;
        updatePermissionStats();
        toggleSectionState('office', this.checked);
      });
    }
    
    if (agendaToggle) {
      agendaToggle.addEventListener('change', function() {
        permissionStates.agenda.enabled = this.checked;
        updatePermissionStats();
        toggleSectionState('agenda', this.checked);
      });
    }
    
    // Office role selection
    document.querySelectorAll('input[name="officeRole"]').forEach(radio => {
      radio.addEventListener('change', function() {
        const role = this.id.replace('role', '').toLowerCase();
        permissionStates.office.role = role;
        updatePermissionStats();
        updateAccessPreview();
        
        // Show active state
        document.querySelectorAll('.role-option').forEach(opt => {
          opt.classList.remove('active');
        });
        this.closest('.role-option').classList.add('active');
      });
    });
    
    // Agenda permissions
    const agendaPermissions = ['replaceAgenda', 'uploadDocs', 'deleteUserDocs', 
                               'deleteGroupDocs', 'deleteAllDocs', 'editComments'];
    
    agendaPermissions.forEach(permission => {
      const checkbox = document.getElementById(permission);
      if (checkbox) {
        checkbox.addEventListener('change', function() {
          permissionStates.agenda[permission] = this.checked;
          updatePermissionStats();
        });
      }
    });
    
    // Office advanced options
    const officeAdvanced = ['auditLog', 'exportData'];
    officeAdvanced.forEach(option => {
      const checkbox = document.getElementById(option);
      if (checkbox) {
        checkbox.addEventListener('change', function() {
          permissionStates.office[option] = this.checked;
          updatePermissionStats();
        });
      }
    });
    
    // Save button
    const saveBtn = document.getElementById('savePermissions');
    if (saveBtn) {
      saveBtn.addEventListener('click', savePermissions);
    }
  }
  
  function updatePermissionStats() {
    // Calculate total permissions
    let totalPerms = 0;
    let activePerms = 0;
    let warningPerms = 0;
    
    // Office permissions
    if (permissionStates.office.enabled) {
      totalPerms += 1; // Main office permission
      activePerms += 1;
      
      // Advanced permissions
      ['auditLog', 'exportData'].forEach(perm => {
        totalPerms += 1;
        if (permissionStates.office[perm]) {
          activePerms += 1;
        }
      });
    }
    
    // Agenda permissions
    if (permissionStates.agenda.enabled) {
      const agendaPerms = ['replaceAgenda', 'uploadDocs', 'deleteUserDocs', 
                          'deleteGroupDocs', 'deleteAllDocs', 'editComments'];
      
      agendaPerms.forEach(perm => {
        totalPerms += 1;
        if (permissionStates.agenda[perm]) {
          activePerms += 1;
          
          // High risk permissions
          if (['deleteGroupDocs', 'deleteAllDocs'].includes(perm)) {
            warningPerms += 1;
          }
        }
      });
    }
    
    // Update display
    document.getElementById('totalPermissions').textContent = totalPerms;
    document.getElementById('activePermissions').textContent = activePerms;
    document.getElementById('warningPermissions').textContent = warningPerms;
    
    // Update access preview
    updateAccessPreview();
  }
  
  function toggleSectionState(section, enabled) {
    const checkboxes = document.querySelectorAll(`#${section}Permissions input[type="checkbox"]:not(#${section}Toggle)`);
    const radios = document.querySelectorAll(`#${section}Permissions input[type="radio"]`);
    
    [...checkboxes, ...radios].forEach(input => {
      input.disabled = !enabled;
    });
    
    const labels = document.querySelectorAll(`#${section}Permissions .form-check-label`);
    labels.forEach(label => {
      label.style.opacity = enabled ? '1' : '0.5';
    });
  }
  
  function updateAccessPreview() {
    let accessLevel = 'No Access';
    let description = 'User has no permissions';
    
    if (permissionStates.office.enabled) {
      switch(permissionStates.office.role) {
        case 'admin':
          accessLevel = 'Administrator';
          description = 'Full system access with administrative privileges';
          break;
        case 'readwrite':
          accessLevel = 'Read & Write';
          description = 'Can create and edit content';
          break;
        case 'readonly':
          accessLevel = 'Read Only';
          description = 'View-only access to system content';
          break;
      }
    } else if (permissionStates.agenda.enabled) {
      accessLevel = 'Limited Access';
      description = 'Agenda management only';
    }
    
    // Update current access level badge
    const badge = document.querySelector('.access-level .badge');
    if (badge) {
      badge.textContent = accessLevel;
      badge.className = 'badge px-3 py-2 fs-6 ';
      
      switch(accessLevel) {
        case 'Administrator':
          badge.classList.add('bg-primary');
          break;
        case 'Read & Write':
          badge.classList.add('bg-success');
          break;
        case 'Read Only':
          badge.classList.add('bg-secondary');
          break;
        case 'Limited Access':
          badge.classList.add('bg-warning');
          break;
        default:
          badge.classList.add('bg-dark');
      }
    }
    
    // Update preview text
    document.getElementById('previewAccess').textContent = description;
  }
  
  function savePermissions() {
    const saveBtn = document.getElementById('savePermissions');
    const originalText = saveBtn.innerHTML;
    
    // Show loading state
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
    
    // Simulate API call
    setTimeout(() => {
      // In production, this would be an actual API call
      console.log('Saving permissions:', permissionStates);
      
      // Show success message
      const modal = bootstrap.Modal.getInstance(permissionsModal);
      modal.hide();
      
      // Show success toast (you can implement this separately)
      showSuccessToast('Permissions updated successfully!');
      
      // Reset button state
      saveBtn.disabled = false;
      saveBtn.innerHTML = originalText;
    }, 1500);
  }
  
  function showSuccessToast(message) {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = 'toast align-items-center text-white bg-success border-0 position-fixed bottom-0 end-0 m-3';
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">
          <i class="fas fa-check-circle me-2"></i>${message}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    `;
    
    document.body.appendChild(toast);
    
    // Initialize and show toast
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remove toast after hidden
    toast.addEventListener('hidden.bs.toast', () => {
      toast.remove();
    });
  }
  
  // Initialize when modal is shown
  permissionsModal.addEventListener('show.bs.modal', function() {
    initializeListeners();
    updatePermissionStats();
    updateAccessPreview();
  });
  
  // Initialize on page load
  updatePermissionStats();
  updateAccessPreview();
});
</script>

<script>
    let selectedFiles = [];
    let selectedContactId = null;

    function showToast(type, message) {
        // Create toast container if not exists
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '1050';
            document.body.appendChild(toastContainer);
        }

        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');

        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
             `;

        toastContainer.appendChild(toast);

        // Initialize and show toast
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        // Remove toast after it's hidden
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }

    // Search functionality for contacts
    document.getElementById('contactSearch').addEventListener('input', function() {
        const query = this.value.toLowerCase();
        document.querySelectorAll('.contact-row').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
        });
    });

    // Function to fetch and update contact documents
    function fetchContactDocuments(contactId) {
        const tbody = document.getElementById('documents-tbody');
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Loading documents...</td></tr>';

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `/admin/handlers/fetch_contact_documents.php?contact_id=${contactId}`, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const data = JSON.parse(xhr.responseText);
                    if (data.status === 'success') {
                        updateDocumentsTable(data.documents);
                    } else {
                        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Error loading documents</td></tr>';
                        console.error('Error fetching documents:', data.message);
                    }
                } catch (e) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Error parsing response</td></tr>';
                    console.error('JSON parse error:', e);
                }
            } else {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Error loading documents</td></tr>';
                console.error('HTTP error:', xhr.status);
            }
        };
        xhr.onerror = function() {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Network error</td></tr>';
            console.error('Network error');
        };
        xhr.send();
    }

    // Function to delete contact document
    function deleteContactDocument(docId) {
        if (!confirm('Are you sure you want to delete this document?')) {
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/admin/handlers/delete_contact_document.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        showToast('success', 'Document deleted successfully');
                        // Refresh documents for current contact
                        const contactId = document.getElementById('contact_id').value;
                        if (contactId) {
                            fetchContactDocuments(contactId);
                        }
                    } else {
                        showToast('error', data.message || 'Failed to delete document');
                    }
                } catch (e) {
                    showToast('error', 'Invalid response from server');
                }
            } else {
                showToast('error', 'Failed to delete document');
            }
        };
        xhr.onerror = function() {
            showToast('error', 'Network error');
        };
        xhr.send(JSON.stringify({
            id: docId
        }));
    }

    // Function to delete contact
    function deleteContact(contactId) {
        if (!confirm('Are you sure you want to delete this contact?')) {
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/admin/handlers/delete_contacts.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        showToast('success', 'Contact deleted successfully');
                        // Reload the page to refresh the list
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showToast('error', data.message || 'Failed to delete contact');
                    }
                } catch (e) {
                    showToast('error', 'Invalid response from server');
                }
            } else {
                showToast('error', 'Failed to delete contact');
            }
        };
        xhr.onerror = function() {
            showToast('error', 'Network error');
        };
        xhr.send(JSON.stringify({
            id: contactId
        }));
    }

    // Function to update documents table
    function updateDocumentsTable(documents) {
        const tbody = document.getElementById('documents-tbody');
        tbody.innerHTML = '';
        if (documents.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">No documents found</td></tr>';
            return;
        }
        documents.forEach(doc => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${sanitizeInput(doc.file_name)}</td>
                <td>${sanitizeInput(doc.comments || '')}</td>
                <td>${new Date(doc.uploaded_at).toLocaleDateString()}</td>
                <td><button type="button" class="btn btn-sm btn-danger" onclick="deleteContactDocument(${doc.id})">Remove</button></td>
            `;
            tbody.appendChild(row);
        });
    }

    // Function to toggle action buttons
    function toggleActionButtons(enable) {
        document.getElementById('inviteBtn').disabled = !enable;
        document.getElementById('permissionsBtn').disabled = !enable;
    }

    // Handle contact row click to populate form
    document.querySelectorAll('.contact-row').forEach(row => {
        row.addEventListener('click', function() {

            // Highlight selected row
            document.querySelectorAll('.contact-row').forEach(r => r.classList.remove('table-primary'));
            this.classList.add('table-primary');

            const contact = JSON.parse(this.dataset.contact);

            // Fill form fields
            Object.keys(contact).forEach(key => {
                const field = document.querySelector(`[name="${key}"]`);


                if (field) {
                    if (field.type === 'checkbox') {
                        field.checked = contact[key] == 1;
                    } else {
                        field.value = contact[key] ?? '';
                        console.log("Field key: " + contact[key]);
                    }
                }
            });

            // Set contact ID
            document.getElementById('contact_id').value = contact.id;

            console.log('Loaded contact ID:', contact.id);

            // Fetch and display contact documents
            fetchContactDocuments(contact.id);

            // Switch button to UPDATE mode
            const btnText = document.querySelector('#addContactBtn .btn-text');
            btnText.textContent = 'Update Contact';

            // Enable action buttons
            selectedContactId = contact.id;
            toggleActionButtons(true);

        });
    });

    // Handle Invite button click
    document.getElementById('inviteBtn').addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('inviteModal'));
        modal.show();
    });

    // Handle Confirm Invite button click
    document.getElementById('confirmInviteBtn').addEventListener('click', function() {
        if (!selectedContactId) {
            showToast('error', 'No contact selected');
            return;
        }

        // Disable button to prevent multiple clicks
        this.disabled = true;
        this.textContent = 'Sending...';

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/admin/handlers/send_invite.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onload = function() {
            document.getElementById('confirmInviteBtn').disabled = false;
            document.getElementById('confirmInviteBtn').textContent = 'Send Invite';

            if (xhr.status === 200) {
                try {
                    const data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        showToast('success', 'Invitation sent successfully');
                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('inviteModal'));
                        modal.hide();
                    } else {
                        showToast('error', data.message || 'Failed to send invitation');
                    }
                } catch (e) {
                    showToast('error', 'Invalid response from server');
                }
            } else {
                showToast('error', 'Failed to send invitation');
            }
        };
        xhr.onerror = function() {
            document.getElementById('confirmInviteBtn').disabled = false;
            document.getElementById('confirmInviteBtn').textContent = 'Send Invite';
            showToast('error', 'Network error');
        };
        xhr.send(JSON.stringify({
            contact_id: selectedContactId
        }));
    });

    function sanitizeInput(input) {
        return input.replace(/[<>]/g, '');
    }

    function validateFile(file) {
        const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        const maxSize = 10 * 1024 * 1024; // 10MB
        if (!allowedTypes.includes(file.type)) {
            return `Invalid file type: ${file.name}. Only PDF, DOC, DOCX allowed.`;
        }
        if (file.size > maxSize) {
            return `File too large: ${file.name}. Max 10MB.`;
        }
        return null;
    }

    function updateFileInput() {
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        document.getElementById('fileInput').files = dt.files;
        document.getElementById('fileNameDisplay').textContent = selectedFiles.length > 0 ? `Selected: ${selectedFiles.length} file(s)` : '';
    }

    function updateTable() {
        const tbody = document.getElementById('documents-tbody');
        tbody.innerHTML = '';
        if (selectedFiles.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">No documents uploaded yet</td></tr>';
            return;
        }
        selectedFiles.forEach((file, index) => {
            const row = document.createElement('tr');
            const sanitizedName = sanitizeInput(file.name);
            row.innerHTML = `
                <td>${sanitizedName} (${file.type || 'Unknown'})</td>
                <td><input type="text" class="form-control" name="file_comments[]" placeholder="Comments"></td>
                <td>${new Date().toLocaleDateString()}</td>
                <td><button type="button" class="btn btn-sm btn-danger" onclick="removeFile(${index})">Remove</button></td>
            `;
            tbody.appendChild(row);
        });

        document.addEventListener('input', function(e) {
            if (e.target.matches('input[name="file_comments[]"]')) {
                e.target.value = sanitizeInput(e.target.value);
            }
        });

    }

    function removeFile(index) {
        selectedFiles.splice(index, 1);
        updateFileInput();
        updateTable();
    }

    // Handle form submission
    document.getElementById('contactForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const form = this;
        const btn = document.getElementById('addContactBtn');
        const btnText = btn.querySelector('.btn-text');
        const spinner = btn.querySelector('.spinner-border');

        // Enable loading
        btn.disabled = true;
        btnText.textContent = 'Saving...';
        spinner.classList.remove('d-none');

        const formData = new FormData(this);
        // selectedFiles.forEach(file => {
        //     formData.append('documents[]', file);
        // });


        const xhr = new XMLHttpRequest();
        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percent = (e.loaded / e.total) * 100;
                let progressBar = document.getElementById('uploadProgress');
                if (!progressBar) {
                    progressBar = document.createElement('div');
                    progressBar.id = 'uploadProgress';
                    progressBar.className = 'progress mt-2';
                    progressBar.innerHTML = '<div class="progress-bar" role="progressbar" style="width: 0%"></div>';
                    document.getElementById('documents-upload-area').appendChild(progressBar);

                }
                progressBar.querySelector('.progress-bar').style.width = percent + '%';
            }
        });
        xhr.addEventListener('load', () => {
            const progressBar = document.getElementById('uploadProgress');
            if (progressBar) progressBar.remove();
            if (xhr.status === 200) {
                try {
                    const data = JSON.parse(xhr.responseText.trim());

                    if (data.success) {
                        showToast('success', data.message);
                        document.getElementById('contact_id').value = '';
                        document.querySelector('#addContactBtn .btn-text').textContent = 'Save Contact';
                        document.querySelectorAll('.contact-row').forEach(r => r.classList.remove('table-primary'));

                        this.reset();
                        selectedFiles = [];
                        updateTable();
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                        console.log('Reloading page after successful contact save');
                        // Reload after short delay to show toast
                        setTimeout(() => {

                            window.location.reload();
                        }, 1500);


                    } else {
                        showToast('error', data.message);
                    }
                } catch (err) {
                    console.error('JSON parse error:', xhr.responseText);
                    showToast('error', 'Invalid response from server');
                } finally {
                    // Reset button state
                    btn.disabled = false;
                    btnText.textContent = 'Save Contact';
                    spinner.classList.add('d-none');
                }
            } else {
                showToast('error', 'Upload failed');
            }
        });
        xhr.addEventListener('error', () => {
            const progressBar = document.getElementById('uploadProgress');
            if (progressBar) progressBar.remove();
            showToast('error', 'Upload error');
        });
        xhr.open('POST', '/admin/handlers/add_contacts.php');
        xhr.send(formData);
    });

    // Handle file selection
    document.getElementById('fileInput').addEventListener('change', function(event) {
        const files = Array.from(event.target.files);
        let validFiles = [];
        let errors = [];
        files.forEach(file => {
            const error = validateFile(file);
            if (error) {
                errors.push(error);
            } else {
                validFiles.push(file);
            }
        });
        if (errors.length > 0) {
            showToast('error', errors.join('<br>'));
        }
        selectedFiles = selectedFiles.concat(validFiles);
        updateFileInput();
        updateTable();
    });

    // Drag and drop
    const uploadArea = document.getElementById('documents-upload-area'); // Supporting Documents card body
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.style.backgroundColor = '#f8f9fa';
    });
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.style.backgroundColor = '';
    });
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.style.backgroundColor = '';
        const files = Array.from(e.dataTransfer.files);
        let validFiles = [];
        let errors = [];
        files.forEach(file => {
            const error = validateFile(file);
            if (error) {
                errors.push(error);
            } else {
                validFiles.push(file);
            }
        });
        if (errors.length > 0) {
            showToast('error', errors.join('<br>'));
        }
        selectedFiles = selectedFiles.concat(validFiles);
        updateFileInput();
        updateTable();
    });
</script>

<style>
    .permission-modal {
        border-radius: 8px;
    }

    .permission-card {
        background: #fff;
        border-radius: 6px;
        border: 1px solid #e1e4e8;
        height: 100%;
    }

    .permission-card .card-header {
        background: #f8f9fa;
        font-size: 15px;
        padding: 12px 15px;
        border-bottom: 1px solid #ddd;
    }

    .permission-card .card-body {
        padding: 15px;
    }

    .permission-card label {
        display: block;
        margin-bottom: 10px;
        cursor: pointer;
    }

    .help-icon {
        color: #6c757d;
        font-size: 13px;
        margin-left: 4px;
    }

    .modal-header {
        background: #2f75b5;
        color: #fff;
    }

    .modal-header .close {
        color: #fff;
    }

    .contacts-management .card {
        border: 1px solid #e0e0e0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .contacts-management .card-header {
        background-color: #f8f9fa !important;
        border-bottom: 1px solid #e0e0e0;
    }

    .contacts-management .form-label {
        color: #495057;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    /* .contacts-management .table th {
    background-color: #f8f9fa;
    font-weight: 600;
    position: sticky;
    top: 0;
} */

    .contacts-management .table-responsive {
        scrollbar-width: thin;
    }

    .contacts-management .alert {
        border-left: 4px solid #1a56db;
    }

    .contacts-management .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
    }
</style>