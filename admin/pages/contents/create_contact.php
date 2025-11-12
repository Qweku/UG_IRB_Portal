<!-- Contacts Management Content -->
<div class="contacts-management p-5">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Contacts</h2>
        <button class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Create New Contact
        </button>
    </div>

    <!-- Instructions -->
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle me-2"></i>
        Select an active status then scroll in the box below to see your contacts. You can select "Create New Contact" and enter information for a new contact.
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="row">
            <!-- Left Column - Contact List & Actions -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">Select Contact</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <select class="form-select">
                                <option selected disabled>Select a Contact</option>
                                <option>Active Contacts</option>
                                <option>Inactive Contacts</option>
                                <option>All Contacts</option>
                            </select>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button class="btn btn-success">
                                <i class="fas fa-save me-1"></i> Save Contact Data
                            </button>
                            <button class="btn btn-danger">
                                <i class="fas fa-trash me-1"></i> Delete Contact
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
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-hover mb-0">
                                <thead class="sticky-top">
                                    <tr>
                                        <th>Name</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="table-active">
                                        <td colspan="3" class="text-center text-muted py-4">
                                            No contacts available
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Contact Details -->
            <div class="col-md-8">
                <!-- Basic Information Card -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">Contact Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Title</label>
                                <select class="form-select">
                                    <option>Dr.</option>
                                    <option>Prof.</option>
                                    <option>Mr.</option>
                                    <option>Ms.</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Last Name</label>
                                <input type="text" class="form-control" placeholder="Enter last name">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">First Name</label>
                                <input type="text" class="form-control" placeholder="Enter first name">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Main Phone #</label>
                                <input type="tel" class="form-control" placeholder="Enter main phone">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Cell Phone #</label>
                                <input type="tel" class="form-control" placeholder="Enter cell phone">
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
                                    <input type="email" class="form-control" placeholder="Enter email address">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Specialty</label>
                                    <input type="text" class="form-control" placeholder="Enter primary specialty">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Specialty 2</label>
                                    <input type="text" class="form-control" placeholder="Enter secondary specialty">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Research Education</label>
                                    <input type="text" class="form-control" placeholder="Enter research education">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Logon Name</label>
                                    <input type="text" class="form-control" placeholder="Enter logon name">
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
                                    <label class="form-label fw-semibold">Client Name/Catalog</label>
                                    <input type="text" class="form-control" placeholder="Document/Company Name/Superwriting (Use Dept Address)">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Direct Address 1</label>
                                    <input type="text" class="form-control" placeholder="Street Address 1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Direct Address 2</label>
                                    <input type="text" class="form-control" placeholder="Street Address 2">
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">City</label>
                                        <input type="text" class="form-control" placeholder="City">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">State</label>
                                        <input type="text" class="form-control" placeholder="State">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Alternate Phone</label>
                                    <input type="tel" class="form-control" placeholder="Alternate Phone">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Alternate Fax</label>
                                    <input type="tel" class="form-control" placeholder="Alternate Fax">
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
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Foreign Phone</label>
                                    <input type="tel" class="form-control" placeholder="Foreign Phone">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Voice Mail</label>
                                    <input type="text" class="form-control" placeholder="Voice Mail">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Permanent or Alternate Member</label>
                                    <select class="form-select">
                                        <option>Permanent</option>
                                        <option>Alternate</option>
                                        <option>Both</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
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
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">ALT</label>
                                    <input type="text" class="form-control" placeholder="ALT">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Supporting Documents -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">Supporting Documents</h6>
                    </div>
                    <div class="card-body">
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
                                <tbody>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">
                                            No data available in table
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            <button class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-upload me-1"></i> Upload Document
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.contacts-management .card {
    border: 1px solid #e0e0e0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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

.contacts-management .table th {
    background-color: #f8f9fa;
    font-weight: 600;
    position: sticky;
    top: 0;
}

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