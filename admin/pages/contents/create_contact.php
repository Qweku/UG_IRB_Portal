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
                        <!-- Search Bar -->
                        <div class="p-2">
                            <input type="text" id="contactSearch" class="form-control form-control-sm"
                                placeholder="Search contacts...">
                        </div>

                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table mb-0">
                                <thead class="sticky-top">
                                    <tr>
                                        <th width="80px">Title</th>
                                        <th>Name</th>
                                    </tr>
                                </thead>
                                <tbody id="contactsTableBody">
                                    <?php if (empty($allContacts)): ?>
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-3">
                                                No contacts found.
                                            </td>
                                        </tr>
                                    <?php endif; ?>

                                    <?php foreach ($allContacts as $contact): ?>
                                        <tr class="contact-row"
                                            data-contact='<?= json_encode($contact, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                                            <td><?= htmlspecialchars($contact['title']) ?></td>
                                            <td><?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?></td>
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
                                    <select class="form-select" name="title">
                                        <option>Dr.</option>
                                        <option>Prof.</option>
                                        <option>Mr.</option>
                                        <option>Mrs.</option>
                                        <option>Ms.</option>
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
                                        <input type="text" class="form-control" disabled>
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
                                        name="documents[]"
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
</div>

<script>
    let selectedFiles = [];

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
        xhr.send(JSON.stringify({ id: docId }));
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

        });
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
        selectedFiles.forEach(file => {
            formData.append('documents[]', file);
        });


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