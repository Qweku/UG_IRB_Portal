<!-- Letter Manager Content -->
<div class="letter-manager p-5">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Letter Manager</h2>
        <div class="badge bg-primary fs-6">From: General</div>
    </div>

    <!-- Institution Header -->
    <div class="card mb-4">
        <div class="card-body text-center bg-light">
            <h4 class="text-primary mb-0">IRB NOGUCHI MEMORIAL INSTITUTE FOR MEDICAL RESEARCH-IRB</h4>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="row">
            <!-- Left Column - Letter Details -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">Letter Details</h6>
                    </div>
                    <div class="card-body">
                         <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Study#</label>
                                <input type="text" class="form-control" placeholder="#" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Meeting Date</label>
                                <input type="date" class="form-control" disabled>
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
                                <label class="form-label fw-semibold">Date Required</label>
                                <input type="date" class="form-control">
                            </div>
                        </div>

                        

                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Addressee</label>
                                <div class="input-group">
                                    <span class="input-group-text">To:</span>
                                    <input type="text" class="form-control" placeholder="Enter addressee name">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Select Unagreee</label>
                                <select class="form-select">
                                    <option selected disabled>Choose option</option>
                                    <option>Option 1</option>
                                    <option>Option 2</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Select Pretest</label>
                                <select class="form-select">
                                    <option selected disabled>Choose pretest</option>
                                    <option>Pretest A</option>
                                    <option>Pretest B</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">CC (Copy to clipcare)</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Enter CC recipients">
                                    <button class="btn btn-outline-secondary">(Add/Remove)</button>
                                </div>
                            </div>
                        </div>

                       
                    </div>
                </div>
            </div>

            <!-- Right Column - Letter Configuration -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">Letter Configuration</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Date To Follow Up</label>
                            <input type="date" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Letter Date</label>
                            <input type="text" class="form-control" value="10/27/2023" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Letter Format</label>
                            <select class="form-select">
                                <option selected>Standard</option>
                                <option>Formal</option>
                                <option>Informal</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Select your Letter</label>
                            <select class="form-select">
                                <option selected disabled>Choose letter type</option>
                                <option>Approval Letter</option>
                                <option>Modification Request</option>
                                <option>Continuing Review</option>
                                <option>SAE Notification</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Closing and Signatory Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">Closing and Signatory</h6>
                        <div>
                            <button class="btn btn-sm btn-outline-secondary me-2">
                                Reset To Details
                            </button>
                            <button class="btn btn-sm btn-outline-primary">
                                Change Closing
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">For this Letter:</label>
                                <textarea class="form-control" rows="4" placeholder="Enter custom closing message..."></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Signatory</label>
                                <select class="form-select mb-3">
                                    <option selected>IRB Chairperson</option>
                                    <option>IRB Director</option>
                                    <option>Committee Secretary</option>
                                    <option>Custom Signatory</option>
                                </select>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="includeSignature">
                                    <label class="form-check-label" for="includeSignature">
                                        Include Digital Signature
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="includeStamp">
                                    <label class="form-check-label" for="includeStamp">
                                        Include Official Stamp
                                    </label>
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
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 fw-bold">Letter Actions</h6>
                            </div>
                            <div>
                                <button class="btn btn-outline-primary me-2">
                                    <i class="fas fa-eye me-1"></i> Preview
                                </button>
                                <button class="btn btn-success me-2">
                                    <i class="fas fa-paper-plane me-1"></i> Send
                                </button>
                                <button class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> Dismissed
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Letters (Optional Section) -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">Recent Letters</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Addressee</th>
                                        <th>Study#</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>10/25/2023</td>
                                        <td>Approval Letter</td>
                                        <td>Dr. Sarah Johnson</td>
                                        <td>00102-26</td>
                                        <td><span class="badge bg-success">Sent</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>10/20/2023</td>
                                        <td>Modification Request</td>
                                        <td>Dr. Michael Chen</td>
                                        <td>00219-16</td>
                                        <td><span class="badge bg-warning text-dark">Draft</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.letter-manager .card {
    border: 1px solid #e0e0e0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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