<div class="agenda-preparation p-5">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Agenda Preparation</h2>
        <div>
            <button class="btn btn-success me-2">
                <i class="fas fa-save me-1"></i> Save Agenda
            </button>
            <a class="btn btn-secondary" href="/">
                <i class="fas fa-times me-1"></i> Cancel
            </a>
        </div>
    </div>

    <!-- Institution Header -->
    <div class="card mb-4">
        <div class="card-body text-center bg-light">
            <h4 class="text-dark mb-1">NOGUCHI MEMORIAL INSTITUTE FOR MEDICAL RESEARCH-IRB</h4>

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
                            <h6 class="mb-0 fw-bold">Meeting Date<span class="text-primary ms-3">[2025-10-12]</span></h6>

                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-semibold">Chair Person</label>
                                <select class="form-select">
                                    <option>Dr. John Doe</option>
                                    <option>Dr. Mary Jane</option>
                                    <option>Dr. Michael Brown</option>
                                </select>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Title of Preparer</label>
                                <input type="text" class="form-control">
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-semibold">Name of Preparer</label>
                                <input type="text" class="form-control">
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- IRB Member Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">IRB Members Section</h6>
                        <div class="d-flex w-25 gap-2">
                            <label for="search" class="form-label">Search</label>
                            <input type="search" class="form-control">
                        </div>

                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>IRB Member</th>
                                        <th>List</th>
                                        <th>Representing</th>
                                        <th>Perm/Alternate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>-- No personnel added --</td>
                                        <td>
                                            <input type="checkbox">
                                        </td>
                                        <td>-</td>
                                        <td>-</td>


                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="my-3">
                            <button class="btn btn-md btn-primary" data-bs-target="#boardMembers" data-bs-toggle="modal">
                                <i class="fas fa-add me-1"></i> Add/Remove Board Member(s)
                            </button>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Guests/Staff</label>
                                <textarea type="text" class="form-control"></textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Education/Training</label>
                                <textarea type="text" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Agenda Details Section -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">Agenda Detials</h6>
                    </div>
                    <div class="card-body">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Time</label>
                                <input type="time" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Location</label>
                                <input type="text" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Agenda Heading</label>
                                <textarea type="text" class="form-control"></textarea>
                            </div>


                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Old Business</label>
                                <textarea type="text" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">New Business</label>
                                <textarea type="text" class="form-control"></textarea>
                            </div>

                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Additional Heading</label>
                                <textarea type="text" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Additional Remarks</label>
                                <textarea type="text" class="form-control"></textarea>
                            </div>

                        </div>
                    </div>
                </div>
            </div>


        </div>



        <!-- Actions Section -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">Actions</h6>
                    </div>
                    <div class="card-body">
                        <!-- Initial Application Section -->
                        <div class="row mb-4">
                            <div class="col-md-6">

                                <input type="checkbox">
                                <label class="form-label fw-semibold">Print Report Date</label>
                            </div>

                            <div class="col-md-6">
                                <input type="checkbox">
                                <label class="form-label fw-semibold">Print Sites</label>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">

                                <input type="checkbox">
                                <label class="form-label fw-semibold">Print Co-Investigators</label>
                            </div>

                            <div class="col-md-6">

                                <input type="checkbox">
                                <label class="form-label fw-semibold">Print Item Numbers</label>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <button class="btn btn-primary btn-md">
                                    <i class="fas fa-eye me-1"></i>Preview (Large fonts)
                                </button>
                            </div>

                            <div class="col-md-6">
                                <button class="btn btn-primary btn-md">
                                    <i class="fas fa-eye me-1"></i>Preview (Small fonts)
                                </button>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <button class="btn btn-primary btn-md">
                                    <i class="fas fa-download me-1"></i>Download PDF (Large fonts)
                                </button>
                            </div>

                            <div class="col-md-6">
                                <button class="btn btn-primary btn-md">
                                    <i class="fas fa-dowload me-1"></i>Download PDF (Small fonts)
                                </button>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <button class="btn btn-primary btn-md">
                                    <i class="fas fa-download me-1"></i>Download Word (Large fonts)
                                </button>
                            </div>

                            <div class="col-md-6">
                                <button class="btn btn-primary btn-md">
                                    <i class="fas fa-download me-1"></i>Download Word (Small fonts)
                                </button>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>


    <div id="boardMembers" class="modal fade" tabindex="-1" aria-labelledby="addPersonnelLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addPersonnelLabel">Add/Remove Board Members</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="col-12">

                        <div id="contentArea">
                            <div class="bg-light p-3 mb-3">
                                <h4 class="text-md">Available Board Members (Unassigned)</h4>
                            </div>
                            <ul class="mw-[300px]">
                                <li style="list-style: none;" class="mb-3">
                                    <div class="d-flex justify-content-between gap-2">
                                        <p>Dr. John Doe</p>
                                        <button class="btn btn-success btn-sm" onclick="assignMember()">
                                            <i class="fas fa-check me-1"></i>Assign
                                        </button>
                                    </div>
                                </li>

                                <li style="list-style: none;" class="mb-3">
                                    <div class="d-flex justify-content-between gap-2">
                                        <p>Dr. Kate Snow</p>
                                        <button class="btn btn-success btn-sm" onclick="assignMember()">
                                            <i class="fas fa-check me-1"></i>Assign
                                        </button>
                                    </div>
                                </li>
                            </ul>

                        </div>



                        <div class="bg-light p-3 my-3">
                            <h4 class="text-md">Current Board members (Assigned)</h4>
                        </div>
                        <ul>
                            <li style="list-style: none;" class="mb-3">
                                <div class="d-flex justify-content-between gap-2">
                                    <p>Dr. Mary Jane</p>
                                    <button class="btn btn-danger btn-sm" onclick="unAssignMember()">
                                        <i class="fas fa-cancel me-1"></i>Un-Assign
                                    </button>
                                </div>
                            </li>

                            <li style="list-style: none;" class="mb-3">
                                <div class="d-flex justify-content-between gap-2">
                                    <p>Dr. Michael Brown</p>
                                    <button class="btn btn-danger btn-sm" onclick="unAssignMember()">
                                        <i class="fas fa-cancel me-1"></i>Un-Assign
                                    </button>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    function assignMember(){
        
    }
</script>