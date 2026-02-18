<?php

$study_id = isset($_GET['study_id']) ? (int) $_GET['study_id'] : null;

error_log("Accessing General Letters Content with study_id: " . var_export($study_id, true));

if (!$study_id) {
    echo '<div class="container-fluid"><div class="alert alert-danger">Study ID is missing.</div></div>';
    return;
}

// Get study details if study_id is provided
$study_details = null;
if ($study_id) {
    // require_once '../../includes/config/database.php';
    $db = new Database();
    $conn = $db->connect();

    $stmt = $conn->prepare("SELECT * FROM studies WHERE id = ?");
    $stmt->execute([$study_id]);
    $study_details = $stmt->fetch(PDO::FETCH_ASSOC);
}

$staffTypes = getStaffTypes();

// error_log("Fetched Staff Types: " . var_export($staffTypes, true));

$actionLetters = getActionLetters();

?>

<!-- Letter Manager Content -->
<div class="content-wrapper p-4">
    <!-- Page Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="page-header-card">
                        <div class="d-flex align-items-center">
                            <div class="header-icon-wrapper">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="header-content">
                                <h4 class="page-title">Letter Manager</h4>
                                <p class="page-subtitle">Generate and send correspondence letters</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="container-fluid">
            <!-- Institution Header -->
            <div class="premium-card mb-4">
                <div class="card-body text-center">
                    <h4 class="text-primary mb-0">IRB NOGUCHI MEMORIAL INSTITUTE FOR MEDICAL RESEARCH-IRB</h4>
                </div>
            </div>

            <!-- Main Content -->
            <div class="row">
                <!-- Left Column - Letter Details -->
                <div class="col-md-8">
                    <div class="premium-card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-file-alt me-2"></i>
                                Letter Details
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Study#</label>
                                    <input type="text" name="study_number" class="form-control" value="<?php echo htmlspecialchars($study_details['protocol_number'] ?? '#') ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Meeting Date</label>
                                    <input type="text" class="form-control" readonly value="<?php echo htmlspecialchars($study_details['meeting_date'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="responseRequired">
                                        <label class="form-check-label fw-semibold" for="responseRequired">
                                            Response Required?
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Date Required for Response</label>
                                    <input type="date" name="follow_up_date" class="form-control" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Addressee</label>
                                    <div class="input-group">
                                        <span class="input-group-text">To:</span>
                                        <select class="form-select" name="addressee_role" id="addressee_role">
                                            <option selected disabled>Select Role</option>
                                            <?php foreach ($staffTypes as $typeName): ?>
                                                <option value="<?php echo htmlspecialchars($typeName ?? ''); ?>">
                                                    <?php echo htmlspecialchars($typeName ?? ''); ?>
                                                </option>
                                            <?php endforeach; ?>

                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Addressee Name</label>
                                    <input type="text" class="form-control" name="addressee_name" id="addressee_name" readonly>
                                </div>
                            </div>


                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">CC (Copy to clipcare)</label>
                                    <textarea type="text" id="cc_field" class="form-control mb-2" placeholder="Enter CC recipients"></textarea>
                                    <button class="btn btn-outline-primary" onclick="openContactsModal()">
                                        <i class="fas fa-address-book me-1"></i>Add/Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Letter Configuration -->
                <div class="col-md-4">
                    <div class="premium-card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-cog me-2"></i>
                                Letter Configuration
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Date To Follow Up</label>
                                    <input type="date" name="due_by" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Letter Date</label>
                                    <input type="text" name="date_sent" class="form-control" value="<?php echo date('Y-m-d'); ?>" readonly>
                                </div>
                            </div>


                            <div class="mb-3">
                                <label class="form-label fw-semibold">Select your Letter</label>
                                <select id="letterSelect" class="form-select">
                                    <option selected disabled>Choose letter type</option>
                                    <?php foreach ($actionLetters as $letter): ?>
                                        <option value="<?php echo htmlspecialchars($letter['file_path'] ?? ''); ?>">
                                            <?php echo htmlspecialchars($letter['letter_name'] ?? ''); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <!-- Closing and Signatory Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="premium-card">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-bold">
                                        <i class="fas fa-pen me-2"></i>
                                        Closing and Signatory
                                    </h6>
                                    <div>
                                        <button class="btn btn-sm btn-outline-secondary me-2">
                                            <i class="fas fa-undo me-1"></i>Reset To Default
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit me-1"></i>Change Closing
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Closing and Signatory</label>
                                            <input type="text" id="signatoryInput" class="form-control mb-2" name="signatoryField">

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>




            <!-- Action Buttons -->
            <div class="row">
                <div class="col-12">
                    <div class="premium-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0 fw-bold">
                                        <i class="fas fa-tasks me-2"></i>
                                        Letter Actions
                                    </h6>
                                </div>
                                <div>
                                    <button class="btn btn-outline-primary me-2">
                                        <i class="fas fa-eye me-1"></i> Preview
                                    </button>
                                    <button class="btn btn-success me-2">
                                        <i class="fas fa-paper-plane me-1"></i> Send
                                    </button>
                                    <button id="downloadBtn" class="btn btn-secondary" onclick="downloadLetter()">
                                        <i class="fas fa-download me-1"></i> Download
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Select Contacts Modal -->
<div class="modal fade" id="contactsModal" tabindex="-1" aria-labelledby="contactsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="contactsModalLabel">
                    <i class="fas fa-address-book me-2"></i>Select Contacts
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <!-- Search -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <input type="text" id="contactSearch"
                            class="form-control"
                            placeholder="Search by name, email, phone...">
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="contactsTable">
                        <thead class="table-light">
                            <tr>
                                <th width="50">
                                    <input type="checkbox" id="selectAllContacts">
                                </th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody id="contactsTableBody">
                            <!-- Loaded dynamically -->
                        </tbody>
                    </table>
                </div>

            </div>

            <div class="modal-footer justify-content-between">
                <span class="text-muted">
                    Selected: <strong><span id="selectedCount">0</span></strong>
                </span>
                <div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmContactsBtn">
                        Add Selected Contacts
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>


<style>
    .letter-manager .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .letter-manager .card-header {
        background-color: #f8f9fa !important;
        border-bottom: 1px solid #e0e0e0;
    }

    .letter-manager .form-label {
        color: #495057;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .letter-manager .input-group-text {
        background-color: #f8f9fa;
        font-weight: 500;
    }

    .letter-manager .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    .letter-manager .form-check-input:checked {
        background-color: var(--royal-blue);
        border-color: var(--royal-blue);
    }
</style>

<script>
    var study_id = <?php echo json_encode($study_id); ?>;
    var actionLetters = <?php echo json_encode($actionLetters); ?>;
    var letterType = null;
    document.getElementById('addressee_role').addEventListener('change', function() {
        const selectedRole = this.value;
        // Simulated mapping of roles to names
        fetch('/admin/handlers/getStaffNamesByRole.php?role=' + encodeURIComponent(selectedRole) + '&study_id=' + encodeURIComponent(study_id))
            .then(response => response.json())
            .then(data => {
                if (data.names && data.names.length > 0) {
                    document.getElementById('addressee_name').value = data.names.join(', ');
                } else {
                    document.getElementById('addressee_name').value = '';
                }
            })
            .catch(error => {
                console.error('Error fetching staff name:', error);
                document.getElementById('addressee_name').value = '';
            });
    });

    document.getElementById('letterSelect').addEventListener('change', function() {
        const selectedPath = this.value;
        const letter = actionLetters.find(l => l.file_path === selectedPath);
        if (letter && letter.signatory) {
            document.getElementById('signatoryInput').value = letter.closing +' '+ letter.signatory;
            letterType = letter.letter_type;

            console.log("Selected Letter Type: "+ letterType);
        } else {
            document.getElementById('signatoryInput').value = '';
            letterType = letter.letter_type;
        }
    });
</script>
<script>
    let contactsData = [];
    let selectedContacts = new Set();

    /* Open Modal & Load Contacts */
    function openContactsModal() {
        fetch('/admin/handlers/fetch_contacts.php')
            .then(res => res.json())
            .then(res => {
                if (!res.success) return alert('Failed to load contacts');
                contactsData = res.data;
                renderContacts(contactsData);
                new bootstrap.Modal(document.getElementById('contactsModal')).show();
            });
    }

    /* Render contacts */
    function renderContacts(data) {
        const tbody = document.getElementById('contactsTableBody');
        tbody.innerHTML = '';

        data.forEach(contact => {
            const tr = document.createElement('tr');

            if (selectedContacts.has(contact.id)) {
                tr.classList.add('table-primary');
            }

            tr.innerHTML = `
          <td>
            <input type="checkbox"
                   class="contact-checkbox"
                   value="${contact.id}"
                   ${selectedContacts.has(contact.id) ? 'checked' : ''}>
          </td>
          <td>${contact.name}</td>
          <td>${contact.email ?? ''}</td>
          <td>${contact.main_phone ?? ''}</td>
          <td>${contact.contact_type ?? ''}</td>
        `;

            tbody.appendChild(tr);
        });

        updateSelectedCount();
    }

    /* Row checkbox logic */
    document.addEventListener('change', e => {
        if (!e.target.classList.contains('contact-checkbox')) return;

        const id = Number(e.target.value);
        const row = e.target.closest('tr');

        if (e.target.checked) {
            selectedContacts.add(id);
            row.classList.add('table-primary');
        } else {
            selectedContacts.delete(id);
            row.classList.remove('table-primary');
        }

        updateSelectedCount();
    });

    /* Select All */
    document.getElementById('selectAllContacts').addEventListener('change', function() {
        const checked = this.checked;

        document.querySelectorAll('.contact-checkbox').forEach(cb => {
            cb.checked = checked;
            cb.dispatchEvent(new Event('change'));
        });
    });

    /* Search */
    document.getElementById('contactSearch').addEventListener('input', function() {
        const term = this.value.toLowerCase();
        const filtered = contactsData.filter(c =>
            Object.values(c).join(' ').toLowerCase().includes(term)
        );
        renderContacts(filtered);
    });

    /* Selected Count */
    function updateSelectedCount() {
        document.getElementById('selectedCount').textContent = selectedContacts.size;
    }

    /* Confirm Button */
    document.getElementById('confirmContactsBtn').addEventListener('click', () => {
        const selectedIds = [...selectedContacts];
        console.log('Selected Contact IDs:', selectedIds);

        // Add to CC field or handle as needed comma-separated
        const ccField = document.getElementById('cc_field');
        const ccEmails = selectedIds.map(id => {
            const contact = contactsData.find(c => c.id === id);
            // Check if contact has email before adding email
            if (contact && contact.email) {
                return contact.email;
            } else {
                alert('Contact ' + contact.name + ' does not have an email address and will not be added to CC.');
            }
        }).filter(Boolean);

        ccField.value = ccEmails.join(', ');
        bootstrap.Modal.getInstance(document.getElementById('contactsModal')).hide();
    });

    /* Download Letter Function */
    function downloadLetter() {
        // Get the selected letter template
        const letterSelect = document.getElementById('letterSelect');
        const templatePath = letterSelect.value;

        // Validate that a letter is selected
        if (!templatePath || letterSelect.options[letterSelect.selectedIndex].disabled) {
            alert('Please select a letter template first.');
            return;
        }

        // Get study number (IRB number)
        const studyNumberInput = document.querySelector('input[name="study_number"]');
        const studyNumber = studyNumberInput ? studyNumberInput.value : '';

        if (!studyNumber || studyNumber === '#') {
            alert('Study number is not available.');
            return;
        }

        // Get other form values
        const followUpDate = document.querySelector('input[name="follow_up_date"]').value;
        const dueBy = document.querySelector('input[name="due_by"]').value;
        const dateSent = document.querySelector('input[name="date_sent"]').value;

        // Get letter type from the actionLetters array
        const selectedLetter = actionLetters.find(l => l.file_path === templatePath);
        const letterType = selectedLetter ? selectedLetter.letter_type : '';

        // Prepare the form data
        const formData = new FormData();
        formData.append('study_id', study_id);
        formData.append('template_path', templatePath);
        formData.append('study_number', studyNumber);
        formData.append('follow_up_date', followUpDate);
        formData.append('due_by', dueBy);
        formData.append('date_sent', dateSent);
        formData.append('letter_type', letterType);

        // Show loading indicator
        const downloadBtn = document.getElementById('downloadBtn');
        const originalBtnContent = downloadBtn.innerHTML;
        downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Downloading...';
        downloadBtn.disabled = true;

        // Call the download handler
        fetch('/admin/handlers/download_letter.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // Check if the response is JSON (error) or a file download (success)
            const contentType = response.headers.get('content-type');
            
            if (contentType && contentType.includes('application/json')) {
                // It's a JSON error response
                return response.json().then(errorData => {
                    throw new Error(errorData.message || 'Failed to generate letter');
                });
            }

            // It's a file download - get the filename from headers
            const disposition = response.headers.get('Content-Disposition');
            let filename = 'IRBActionLetter.docx';
            if (disposition && disposition.includes('filename=')) {
                const filenameMatch = disposition.match(/filename="?([^";\n]+)"?/);
                if (filenameMatch) {
                    filename = filenameMatch[1];
                }
            }

            return response.blob().then(blob => ({ blob, filename }));
        })
        .then(({ blob, filename }) => {
            // Create a download link and trigger it
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);

            // Show success message
            alert('Letter downloaded successfully!');
        })
        .catch(error => {
            console.error('Download error:', error);
            alert('Error: ' + error.message);
        })
        .finally(() => {
            // Restore button state
            downloadBtn.innerHTML = originalBtnContent;
            downloadBtn.disabled = false;
        });
    }
</script>
