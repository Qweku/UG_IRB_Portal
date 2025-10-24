<!-- New Study Input Form Content -->
<div class="new-study-form p-5">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Study Input Form</h2>
        <div>
            <button class="btn btn-success me-2">
                <i class="fas fa-save me-1"></i> Save Study
            </button>
            <a class="btn btn-secondary" href="index.php">
                <i class="fas fa-times me-1"></i> Cancel
            </a>
        </div>
    </div>

    <!-- Institution Header -->
    <div class="card mb-4">
        <div class="card-body text-center bg-light">
            <h4 class="text-primary mb-1">NOGUCHI MEMORIAL INSTITUTE FOR MEDICAL RESEARCH-IRB</h4>
            <h5 class="text-muted">New Study</h5>
        </div>
    </div>

    <!-- Main Form Content -->
    <div class="main-content">
        <!-- Study Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">Study #<span class="text-primary">[1]</span></h6>
                            <span class="badge bg-warning text-dark">New Study</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Reference Number</label>
                                <input type="text" class="form-control" placeholder="Enter review title">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Expiration Date</label>
                                <input type="date" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-semibold">Protocol Title</label>
                                <input type="text" class="form-control">
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Study Personnel -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">Current Study Personnel</h6>
                        <button class="btn btn-sm btn-primary" data-bs-target="#addPersonnel" data-bs-toggle="modal">
                            <i class="fas fa-plus me-1"></i> Add Personnel
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Staff Type</th>
                                        <th>Title</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>-- No personnel added --</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>

                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" disabled>
                                                <i class="fas fa-edit"></i>
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

        <!-- Study Details Section -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">Study Detials</h6>
                    </div>
                    <div class="card-body">

                        <div class="row mb-3">
                            <div class="col-md-10">
                                <label class="form-label fw-semibold">Sponsor*</label>
                                <select class="form-select">
                                    <option>John Doe</option>
                                    <option>Mary Jane</option>
                                    <option>Michael Brown</option>
                                    <option>Suspended</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold"></label>
                                <button class="btn btn-sm w-100 btn-primary">
                                    <i class="fas fa-plus me-1"></i>Add to List
                                </button>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Active?*</label>
                                <select class="form-select">
                                    <option selected>Open</option>
                                    <option>Closed</option>
                                    <option>Pending</option>
                                    <option>Suspended</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Type*</label>
                                <select class="form-select">
                                    <option selected>Full Board</option>
                                    <option>Expedited</option>
                                    <option>Exempt</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Status</label>
                                <select class="form-select">
                                    <option selected>Pending</option>
                                    <option>Approved</option>
                                    <option>Opened</option>
                                    <option>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Risk Category</label>
                                <select class="form-select">
                                    <option selected>Minimal Risk</option>
                                    <option>Low Risk</option>
                                    <option>Moderate Risk</option>
                                    <option>High Risk</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Approval Patient Enrollment</label>
                                <input type="text" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Currently Enrolled</label>
                                <input type="text" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">On Agenda Date</label>
                                <select class="form-select" disabled>
                                    <option selected>No Meetings</option>
                                    <option>Approved</option>
                                    <option>Opened</option>
                                    <option>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">IRB of Record</label>
                                <input type="text" class="form-control">
                            </div>

                        </div>
                    </div>
                </div>
            </div>


        </div>

        <!-- IRB Information Section -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">IRB Information</h6>
                    </div>
                    <div class="card-body">

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">CR Required</label>
                                <input type="text" class="form-control">
                            </div>

                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Renewal Cycle (Mo)</label>
                                <select class="form-select">
                                    <option selected>12</option>
                                    <option>11</option>
                                    <option>10</option>
                                    <option>9</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Date Received*</label>
                                <input type="date" class="form-control">
                            </div>

                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Firt IRB Review</label>
                                <input type="date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Original Approval</label>
                                <input type="date" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Last Seen By IRB</label>
                                <input type="date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Last IRB Renewal</label>
                                <input type="date" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Number of SAEs</label>
                                <div class="row">
                                    <div class="col-md-9">
                                        <input type="text" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <button class="btn btn-primary btn-md">View/Add</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Number of CPAs</label>
                                <div class="row">
                                    <div class="col-md-9">
                                        <input type="text" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <button class="btn btn-primary btn-md">View/Add</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Initial Summary of Agenda</label>
                                <textarea type="text" class="form-control"></textarea>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Internal Notes</label>
                                <textarea type="text" class="form-control"></textarea>
                            </div>

                        </div>
                    </div>
                </div>
            </div>


        </div>

        <!-- Signature Documents -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">Signature Documents*</h6>
                    </div>
                    <div class="card-body">
                        <!-- Initial Application Section -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 text-primary">Initial Application*</h6>
                                <button class="btn btn-sm btn-success">
                                    <i class="fas fa-upload me-1"></i> Upload File
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>File Name</th>
                                            <th>Comments</th>
                                            <th>Date Uploaded</th>
                                            <th>Don't Include in Agenda</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">
                                                No documents uploaded yet
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


    <div id="addPersonnel" class="modal fade" tabindex="-1" aria-labelledby="addPersonnelLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addPersonnelLabel">Add Study Personnel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="col-12">
                        <form id="personnelForm">
                            <div id="contentArea">
                                <div class="bg-light p-3 mb-3">
                                    <h4 class="text-md">Assigned Personnel</h4>
                                </div>
                                <!-- <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Name</label>
                                        <input type="text" class="form-control" placeholder="Enter name">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Staff Type</label>
                                        <select class="form-select">
                                            <option>Principal Investigator</option>
                                            <option>Co-Investigator</option>
                                            <option>Study Coordinator</option>
                                            <option>Other</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Title</label>
                                        <input type="text" class="form-control" placeholder="Enter title">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Date Added</label>
                                        <input type="date" class="form-control">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Company Name</label>
                                        <input type="text" class="form-control" placeholder="Enter title">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Main Phone</label>
                                        <input type="phone" class="form-control">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Email</label>
                                        <input type="email" class="form-control" placeholder="Enter title">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Comments</label>
                                        <input type="text" class="form-control">
                                    </div>
                                </div>-->
                            </div>

                        </form>
                        <button class="btn btn-primary" onclick="addMorePersonnel()">
                            <i class="fas fa-plus me-1"></i> Add Personnel
                        </button>
                        <div class="bg-light p-3 my-3">
                            <h4 class="text-md">Personnel Previously Associated with Study</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    function addMorePersonnel() {
        // Function to add more personnel fields dynamically
        const form = document.getElementById('contentArea');
        const newFields = `
                        
                        <div class="new-personnel-section">
                            <hr>
                        <div class="d-flex justify-content-end">
                            
                            <button class="btn btn-danger btn-sm mb-3" onclick="this.parentElement.parentElement.remove();">
                                <i class="fas fa-trash me-1"></i> Remove Personnel </button>
                        </div>
                        <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Name</label>
                                    <input type="text" class="form-control" placeholder="Enter name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Staff Type</label>
                                    <select class="form-select">
                                        <option>Principal Investigator</option>
                                        <option>Co-Investigator</option>
                                        <option>Study Coordinator</option>
                                        <option>Other</option>
                                    </select>
                                </div>
                            </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Title</label>
                                        <input type="text" class="form-control" placeholder="Enter title">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Date Added</label>
                                        <input type="date" class="form-control">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Company Name</label>
                                        <input type="text" class="form-control" placeholder="Enter title">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Main Phone</label>
                                        <input type="phone" class="form-control">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Email</label>
                                        <input type="email" class="form-control" placeholder="Enter title">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Comments</label>
                                        <input type="text" class="form-control">
                                    </div>
                                </div> 
                                </div>`;
        form.insertAdjacentHTML('beforeend', newFields);



    }
</script>