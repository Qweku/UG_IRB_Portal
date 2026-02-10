<style>
        .stepper-vertical .step:not(:last-child) .step-progress {
            min-height: 40px;
        }
        .stepper-vertical .step-progress {
            padding-left: 2rem;
        }
        .stepper-vertical .step-line {
            width: 2px;
            background-color: #e9ecef;
            height: 100%;
            position: relative;
        }
        .stepper-vertical .step-line::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 2px;
            height: var(--progress, 0%);
            background-color: #0d6efd;
            transition: height 0.3s ease;
        }
        .step.active .step-line::before {
            height: calc(100% - 40px);
        }
        .step.completed .step-line::before {
            height: 100%;
            background-color: #198754;
        }
    </style>
    
    <div class="add-new-protocol container-fluid mt-4 mb-4 p-4">
        <!-- Header -->
        <div class="welcome-header text-white p-4 rounded mb-4 position-relative overflow-hidden" 
             style="background: linear-gradient(135deg, #2c3e50, #4a6491);">
            <div class="header-gradient"></div>
            <div class="d-flex align-items-center position-relative z-1">
                <div>
                    <h2 class="mb-1 fw-bold">Initial Submission Form A - Non-NMIMR Researchers</h2>
                    <p class="mb-0 opacity-75">For external researchers - Complete all sections for ethics review consideration</p>
                </div>
            </div>
            <div class="header-decoration">
                <i class="fas fa-university"></i>
                <i class="fas fa-file-alt"></i>
                <i class="fas fa-edit"></i>
            </div>
        </div>

        <!-- Instructions Alert -->
        <div class="alert alert-info mb-4">
            <div class="d-flex align-items-start">
                <i class="fas fa-info-circle me-3 mt-1 fs-4"></i>
                <div>
                    <h6 class="alert-heading mb-2">Submission Instructions</h6>
                    <ol class="mb-0 ps-3">
                        <li class="mb-1">Please complete all sections before it will be considered for ethics review</li>
                        <li class="mb-1">Download the NMIMR-IRB Researchers Checklist for further instructions</li>
                        <li class="mb-1">The proposal and the consent form should be paged separately</li>
                        <li class="mb-1">Use very clear font size such as Times New Roman 11pt/12pt, Arial 11pt, Calibri 12pt</li>
                        <li class="mb-1">Download the NMIMR-IRB Submission guide for further information</li>
                        <li class="mb-1">Send a single pdf file of all documents to <strong>nirb@noguchi.ug.edu.gh</strong> to facilitate the review process</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Required Documents Alert -->
        <div class="alert alert-warning mb-4">
            <div class="d-flex align-items-start">
                <i class="fas fa-file-download me-3 mt-1 fs-4"></i>
                <div>
                    <h6 class="alert-heading mb-2">Required Documents</h6>
                    <ul class="mb-0 ps-3">
                        <li>NMIMR-IRB Researchers Checklist</li>
                        <li>NMIMR-IRB Submission Guide</li>
                        <li>NMIMR-IRB Consent Form Template</li>
                        <li>Approval Letters from Collaborating Institutions</li>
                        <li>Prior IRB Approval Letter (if applicable)</li>
                    </ul>
                    <p class="mb-0 mt-2">These documents should be downloaded from the NMIMR website before proceeding.</p>
                </div>
            </div>
        </div>

        <!-- Main Wizard Container -->
        <div class="row">
            <!-- Left Sidebar - Stepper -->
            <div class="col-lg-3">
                <div class="stepper-sidebar card border-0 shadow-sm h-100 sticky-top" style="top: 20px;">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold mb-4 text-dark">
                            <i class="fas fa-list-ol me-2 text-primary"></i>Form Sections
                        </h5>
                        
                        <div class="stepper-vertical">
                            <!-- Step 1: Instructions -->
                            <div class="step active" data-step="0">
                                <div class="step-header d-flex align-items-center mb-2">
                                    <div class="step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                        1
                                    </div>
                                    <div class="step-title ms-3">
                                        <h6 class="fw-semibold mb-0">Instructions</h6>
                                        <small class="text-muted">Read submission guidelines</small>
                                    </div>
                                </div>
                                <div class="step-progress ms-4 ps-3">
                                    <div class="step-line"></div>
                                </div>
                            </div>

                            <!-- Step 2: Section A -->
                            <div class="step" data-step="1">
                                <div class="step-header d-flex align-items-center mb-2">
                                    <div class="step-number bg-light text-muted border rounded-circle d-flex align-items-center justify-content-center">
                                        2
                                    </div>
                                    <div class="step-title ms-3">
                                        <h6 class="fw-semibold mb-0 text-muted">Section A: Background</h6>
                                        <small class="text-muted">Project information & team</small>
                                    </div>
                                </div>
                                <div class="step-progress ms-4 ps-3">
                                    <div class="step-line"></div>
                                </div>
                            </div>

                            <!-- Step 3: Section B -->
                            <div class="step" data-step="2">
                                <div class="step-header d-flex align-items-center mb-2">
                                    <div class="step-number bg-light text-muted border rounded-circle d-flex align-items-center justify-content-center">
                                        3
                                    </div>
                                    <div class="step-title ms-3">
                                        <h6 class="fw-semibold mb-0 text-muted">Section B: Proposal</h6>
                                        <small class="text-muted">Abstract, methodology & ethics</small>
                                    </div>
                                </div>
                                <div class="step-progress ms-4 ps-3">
                                    <div class="step-line"></div>
                                </div>
                            </div>

                            <!-- Step 4: Section C -->
                            <div class="step" data-step="3">
                                <div class="step-header d-flex align-items-center mb-2">
                                    <div class="step-number bg-light text-muted border rounded-circle d-flex align-items-center justify-content-center">
                                        4
                                    </div>
                                    <div class="step-title ms-3">
                                        <h6 class="fw-semibold mb-0 text-muted">Section C: Signatures</h6>
                                        <small class="text-muted">Commitments & declarations</small>
                                    </div>
                                </div>
                                <div class="step-progress ms-4 ps-3">
                                    <div class="step-line"></div>
                                </div>
                            </div>

                            <!-- Step 5: Review -->
                            <div class="step" data-step="4">
                                <div class="step-header d-flex align-items-center">
                                    <div class="step-number bg-light text-muted border rounded-circle d-flex align-items-center justify-content-center">
                                        5
                                    </div>
                                    <div class="step-title ms-3">
                                        <h6 class="fw-semibold mb-0 text-muted">Review & Submit</h6>
                                        <small class="text-muted">Finalize and send to IRB</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Indicator -->
                        <div class="progress mt-4" style="height: 6px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 20%" id="stepperProgress"></div>
                        </div>
                        <div class="text-center mt-2">
                            <small class="text-muted">Step <span id="currentStep">1</span> of 5</small>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="stepper-navigation mt-4 d-none d-lg-block">
                            <button class="btn btn-outline-secondary w-100 mb-2" id="prevStepBtn" disabled>
                                <i class="fas fa-arrow-left me-2"></i>Previous
                            </button>
                            <button class="btn btn-primary w-100" id="nextStepBtn">
                                Next <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Content - Form -->
            <div class="col-lg-9">
                <form id="nonNmimrProtocolForm">
                    
                    <!-- Step 0: Instructions -->
                    <div class="step-content active" data-step="0">
                        <div class="card mb-4">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Submission Instructions</h5>
                                    <p class="text-muted mb-0 small">Step 1 of 5 - Read and understand the guidelines</p>
                                </div>
                                <span class="badge bg-info">Important</span>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading"><i class="fas fa-check me-2"></i>Acknowledgment</h6>
                                    <p class="mb-0">Please confirm that you have read and understood all submission instructions before proceeding.</p>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="acknowledge-instructions">
                                    <label class="form-check-label fw-semibold" for="acknowledge-instructions">
                                        I have read and understood all submission instructions
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 1: Section A - Background Information -->
                    <div class="step-content" data-step="1">
                        <div class="card mb-4 border-primary">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0"><i class="fas fa-address-card me-2"></i>SECTION A - BACKGROUND INFORMATION</h5>
                                    <p class="mb-0 opacity-75 small">Step 2 of 5 - Project information & team details</p>
                                </div>
                                <span class="badge bg-white text-primary">Required</span>
                            </div>
                            <div class="card-body">
                <!-- Step 0: Instructions -->
                <div class="form-section active" id="step-0">
                    <h2 class="section-title">Submission Instructions</h2>
                    
                    <div class="instructions">
                        <h3><i class="fas fa-info-circle"></i> Important Instructions</h3>
                        <ol>
                            <li>Please complete all sections before it will be considered for ethics review.</li>
                            <li>Download the NMIMR-IRB Researchers Checklist for further instructions.</li>
                            <li>The proposal and the consent form should be paged separately.</li>
                            <li>Use very clear font size such as Times New Roman 11pt / 12pt, Arial 11 pt., Calibri 12pt.</li>
                            <li>Download the NMIMR-IRB Submission guide for further information.</li>
                            <li>Send a single pdf file of all documents to <strong>nirb@noguchi.ug.edu.gh</strong> to facilitate the review process. The soft copy should be signed and dated.</li>
                        </ol>
                    </div>

                    <div class="instructions">
                        <h3><i class="fas fa-file-download"></i> Required Documents</h3>
                        <ul>
                            <li>NMIMR-IRB Researchers Checklist</li>
                            <li>NMIMR-IRB Submission Guide</li>
                            <li>NMIMR-IRB Consent Form Template</li>
                            <li>Approval Letters from Collaborating Institutions</li>
                            <li>Prior IRB Approval Letter (if applicable)</li>
                        </ul>
                        <p style="margin-top: 10px;">These documents should be downloaded from the NMIMR website before proceeding.</p>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="acknowledge-instructions">
                            I have read and understood all submission instructions
                        </label>
                    </div>
                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="protocol-number" class="form-label fw-semibold">Protocol Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="protocol-number" placeholder="e.g. NMIMR-IRB/2023/123">
                                        <small class="text-muted">Unique identifier for your study</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="version-number" class="form-label fw-semibold">Version Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="version-number" placeholder="e.g. 1.0">
                                        <small class="text-muted">Document version (start with 1.0)</small>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="title" class="form-label fw-semibold">Title of Proposal <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="title" placeholder="Enter the full title of your research proposal">
                                        <small class="text-muted">Clear and concise study title</small>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="pi-details" class="form-label fw-semibold">Name of Principal Investigator <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="pi-details" rows="3" placeholder="Institution and Department, Postal Address, Telephone, Fax Number, E-mail Address"></textarea>
                                        <small class="text-muted">Full name and contact details of the Principal Investigator</small>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="co-pi" class="form-label fw-semibold">Co-PI(s)</label>
                                        <textarea class="form-control" id="co-pi" rows="3" placeholder="Name, Qualification (Specialty), Department, Postal Address, Telephone, Fax number, E-mail Address"></textarea>
                                        <small class="text-muted">List all co-investigators, one per line if multiple</small>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="prior-scientific-review" class="form-label fw-semibold">Prior Scientific Review</label>
                                        <textarea class="form-control" id="prior-scientific-review" rows="3" placeholder="Provide details of any prior scientific review this proposal has undergone"></textarea>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="prior-irb-review" class="form-label fw-semibold">Prior IRB Review</label>
                                        <textarea class="form-control" id="prior-irb-review" rows="3" placeholder="Name any other IRB this proposal has been submitted to and attach approval letter if applicable. In case of rejection, state reasons"></textarea>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="collaborating-institutions" class="form-label fw-semibold">Collaborating Institutions <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="collaborating-institutions" rows="2" placeholder="List all collaborating institutions"></textarea>
                                        <small class="text-muted">Attach Letter of Approval for each institution</small>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="approval-letters" class="form-label fw-semibold">Upload Approval Letters</label>
                                        <input type="file" class="form-control" id="approval-letters" multiple>
                                        <small class="text-muted">Upload approval letters from collaborating institutions</small>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="funding-source" class="form-label fw-semibold">Source(s) of Funding</label>
                                        <textarea class="form-control" id="funding-source" rows="2" placeholder="Name and Address of funding source(s)"></textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Type of Research <span class="text-danger">*</span></label>
                                        <div class="mt-2">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" id="type-biomedical" name="research-type" value="Biomedical">
                                                <label class="form-check-label" for="type-biomedical">Biomedical</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" id="type-social" name="research-type" value="Social/Behavioural">
                                                <label class="form-check-label" for="type-social">Social/Behavioural</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" id="type-other" name="research-type" value="Other">
                                                <label class="form-check-label" for="type-other">Others</label>
                                            </div>
                                        </div>
                                        <input type="text" class="form-control mt-2" id="other-type" placeholder="Specify other research type" style="display: none;">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="duration" class="form-label fw-semibold">Duration of Project <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="duration" placeholder="e.g. 12 months, 24 months">
                                        <small class="text-muted">Expected duration of the research project</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Section B - Proposal Outline -->
                    <div class="step-content" data-step="2">
                        <div class="card mb-4 border-info">
                            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>SECTION B - PROPOSAL OUTLINE</h5>
                                    <p class="mb-0 opacity-75 small">Step 3 of 5 - Abstract, methodology & ethics</p>
                                </div>
                                <span class="badge bg-white text-info">Required</span>
                            </div>
                            <div class="card-body">

                                <div class="mb-4">
                                    <label for="abstract" class="form-label fw-semibold">ABSTRACT/EXECUTIVE SUMMARY <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="abstract" rows="4" placeholder="Not more than 250 words"></textarea>
                                    <div class="d-flex justify-content-between mt-1">
                                        <small class="text-muted">Use clear and concise language</small>
                                        <small class="text-muted"><span id="abstract-counter">0</span>/250 words</small>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="introduction" class="form-label fw-semibold">INTRODUCTION/RATIONALE</label>
                                    <textarea class="form-control" id="introduction" rows="6" placeholder="Not more than 5 pages"></textarea>
                                    <small class="text-muted">Use font size Times New Roman 11pt/12pt, Arial 11pt, or Calibri 12pt</small>
                                </div>

                                <div class="mb-4">
                                    <label for="literature-review" class="form-label fw-semibold">LITERATURE REVIEW</label>
                                    <textarea class="form-control" id="literature-review" rows="6" placeholder="Not more than 5 pages"></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="aims" class="form-label fw-semibold">AIMS OR OBJECTIVES OF STUDY <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="aims" rows="4" placeholder="State the specific aims or objectives of your study"></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="methodology" class="form-label fw-semibold">METHODOLOGY <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="methodology" rows="6" placeholder="Include Inclusion and Exclusion Criteria"></textarea>
                                    <small class="text-muted">Provide detailed methodology including study design, sampling, data collection, and analysis</small>
                                </div>

                                <div class="mb-4">
                                    <label for="ethical-considerations" class="form-label fw-semibold">ETHICAL CONSIDERATIONS <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="ethical-considerations" rows="6" placeholder="i.e. consent procedures, confidentiality, privacy, risks and benefits, etc."></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="expected-outcomes" class="form-label fw-semibold">EXPECTED OUTCOME/RESULTS</label>
                                    <textarea class="form-control" id="expected-outcomes" rows="4" placeholder="Describe the expected outcomes and results"></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="references" class="form-label fw-semibold">REFERENCES</label>
                                    <textarea class="form-control" id="references" rows="4" placeholder="List all references using appropriate citation style"></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="work-plan" class="form-label fw-semibold">WORK PLAN</label>
                                    <textarea class="form-control" id="work-plan" rows="4" placeholder="Provide a detailed work plan/timeline for the project"></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="budget" class="form-label fw-semibold">BUDGET AND BUDGET JUSTIFICATION</label>
                                    <textarea class="form-control" id="budget" rows="4" placeholder="Provide detailed budget and justification"></textarea>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Required Forms</label>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="consent-form">
                                        <label class="form-check-label" for="consent-form">
                                            Consent Form (Download NMIMR-IRB Consent form template)
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="assent-form">
                                        <label class="form-check-label" for="assent-form">
                                            Assent Form and Parental Consent Form (Only applicable where children of ages 12 to 17 would be recruited as research participants)
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="data-instruments">
                                        <label class="form-check-label" for="data-instruments">
                                            Data Collection Instruments (i.e. Interview Guide, Questionnaire, etc.)
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="required-forms" class="form-label fw-semibold">Upload Required Forms</label>
                                    <input type="file" class="form-control" id="required-forms" multiple>
                                    <small class="text-muted">Upload consent forms, assent forms, and data collection instruments</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Section C - Signatures -->
                    <div class="step-content" data-step="3">
                        <div class="card mb-4 border-success">
                            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>SECTION C - SIGNATURES</h5>
                                    <p class="mb-0 opacity-75 small">Step 4 of 5 - Commitments & declarations</p>
                                </div>
                                <span class="badge bg-white text-success">Required</span>
                            </div>
                            <div class="card-body">

                                <div class="alert alert-warning mb-4">
                                    <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Important Declarations</h6>
                                    <p class="mb-2">By signing below, you confirm that:</p>
                                    <ol class="mb-0 ps-3">
                                        <li class="mb-1">You will ensure that all procedures performed under the study will be conducted in accordance with all relevant policies and regulations that govern research involving human participants.</li>
                                        <li class="mb-1">You understand that if there is any change from the project as originally approved you must submit an amendment to the NMIMR-IRB for review and approval prior to its implementation.</li>
                                        <li class="mb-1">You understand that you will report all serious adverse events associated with the study within seven days verbally and fourteen days in writing.</li>
                                        <li class="mb-1">You understand that you will submit progress reports each year for review and renewal.</li>
                                        <li class="mb-0">You agree that you will submit a final report to the NMIMR-IRB at the end of the study.</li>
                                    </ol>
                                </div>

                                <div class="card mb-4 bg-light">
                                    <div class="card-body">
                                        <h6 class="fw-semibold mb-3"><i class="fas fa-user me-2"></i>Principal Investigator</h6>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="pi-name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="pi-name" placeholder="Full Name">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="pi-signature" class="form-label fw-semibold">Signature (Type your name) <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="pi-signature" placeholder="Type your name as signature">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="pi-date" class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" id="pi-date">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-4 bg-light">
                                    <div class="card-body">
                                        <h6 class="fw-semibold mb-3"><i class="fas fa-user-friends me-2"></i>Co-Principal Investigator</h6>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="co-pi-name" class="form-label fw-semibold">Full Name</label>
                                                <input type="text" class="form-control" id="co-pi-name" placeholder="Full Name">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="co-pi-signature" class="form-label fw-semibold">Signature (Type your name)</label>
                                                <input type="text" class="form-control" id="co-pi-signature" placeholder="Type your name as signature">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="co-pi-date" class="form-label fw-semibold">Date</label>
                                                <input type="date" class="form-control" id="co-pi-date">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Review & Submit -->
                    <div class="step-content" data-step="4">
                        <div class="card mb-4 border-success">
                            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>REVIEW & SUBMIT</h5>
                                    <p class="mb-0 opacity-75 small">Step 5 of 5 - Finalize and send to IRB</p>
                                </div>
                                <span class="badge bg-white text-success">Required</span>
                            </div>
                            <div class="card-body">
                                
                                <div class="alert alert-success mb-4">
                                    <h6 class="alert-heading"><i class="fas fa-check-circle me-2"></i>Final Submission Checklist</h6>
                                    <p class="mb-3">Please confirm the following before submitting:</p>
                                    
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="check-complete">
                                        <label class="form-check-label" for="check-complete">
                                            All sections of the form have been completed
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="check-font">
                                        <label class="form-check-label" for="check-font">
                                            All documents use appropriate font size (Times New Roman 11pt/12pt, Arial 11pt, or Calibri 12pt)
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="check-consent">
                                        <label class="form-check-label" for="check-consent">
                                            Consent form is paged separately from the proposal
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="check-pdf">
                                        <label class="form-check-label" for="check-pdf">
                                            All documents are compiled into a single PDF file
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="check-signed">
                                        <label class="form-check-label" for="check-signed">
                                            PDF file is signed and dated
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="check-checklist">
                                        <label class="form-check-label" for="check-checklist">
                                            NMIMR-IRB Researchers Checklist has been completed
                                        </label>
                                    </div>
                                </div>

                                <div class="alert alert-info mb-4">
                                    <h6 class="alert-heading"><i class="fas fa-paper-plane me-2"></i>Submission Instructions</h6>
                                    <p class="mb-0">Send a single PDF file of all documents to <strong>nirb@noguchi.ug.edu.gh</strong> to facilitate the review process.</p>
                                    <p class="mb-0 mt-2"><small>The soft copy should be signed and dated.</small></p>
                                </div>

                                <div class="mb-4">
                                    <label for="final-pdf" class="form-label fw-semibold">Upload Final PDF <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="final-pdf" accept=".pdf">
                                    <small class="text-muted">Upload the final signed PDF document</small>
                                </div>

                                <div class="mb-4">
                                    <label for="submission-notes" class="form-label fw-semibold">Additional Notes/Comments</label>
                                    <textarea class="form-control" id="submission-notes" rows="3" placeholder="Any additional information for the IRB"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Navigation -->
                    <div class="d-flex justify-content-between mt-4">
                        <button class="btn btn-outline-secondary" id="prev-btn" disabled>
                            <i class="fas fa-arrow-left me-2"></i>Previous
                        </button>
                        <button class="btn btn-success" id="next-btn">
                            Next <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </form>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Step management
            const steps = document.querySelectorAll('.stepper-vertical .step');
            const stepContents = document.querySelectorAll('.step-content');
            const prevBtn = document.getElementById('prev-btn');
            const nextBtn = document.getElementById('next-btn');
            const progressBar = document.getElementById('stepperProgress');
            const currentStepDisplay = document.getElementById('currentStep');
            
            let currentStep = 0;
            const totalSteps = steps.length;
            
            // Initialize steps
            updateSteps();
            updateProgressBar();
            
            // Word counter for abstract
            const abstractTextarea = document.getElementById('abstract');
            const abstractCounter = document.getElementById('abstract-counter');
            
            if (abstractTextarea && abstractCounter) {
                abstractTextarea.addEventListener('input', function() {
                    const wordCount = countWords(this.value);
                    abstractCounter.textContent = wordCount;
                    
                    if (wordCount > 250) {
                        abstractCounter.classList.add('text-danger');
                        abstractCounter.classList.remove('text-muted');
                    } else {
                        abstractCounter.classList.remove('text-danger');
                        abstractCounter.classList.add('text-muted');
                    }
                });
            }
            
            // File input change handlers
            const fileInputs = document.querySelectorAll('input[type="file"]');
            fileInputs.forEach(input => {
                input.addEventListener('change', function() {
                    if (this.files.length > 0) {
                        this.classList.add('is-valid');
                    }
                });
            });
            
            // Next button click
            if (nextBtn) {
                nextBtn.addEventListener('click', function() {
                    if (currentStep < totalSteps - 1) {
                        // Validate current step before proceeding
                        if (validateStep(currentStep)) {
                            currentStep++;
                            updateSteps();
                            updateProgressBar();
                            updateNavigationButtons();
                        }
                    } else {
                        // On last step, submit form
                        if (validateStep(currentStep)) {
                            alert('Form submission complete! Please email the compiled PDF to nirb@noguchi.ug.edu.gh');
                            // In a real application, you would submit the form data here
                        }
                    }
                });
            }
            
            // Previous button click
            if (prevBtn) {
                prevBtn.addEventListener('click', function() {
                    if (currentStep > 0) {
                        currentStep--;
                        updateSteps();
                        updateProgressBar();
                        updateNavigationButtons();
                    }
                });
            }
            
            // Step click navigation
            steps.forEach((step, index) => {
                step.style.cursor = 'pointer';
                step.addEventListener('click', function() {
                    // Only allow navigation to completed steps or next step
                    if (index <= currentStep || index === currentStep + 1) {
                        // Validate current step before proceeding
                        if (index !== currentStep && validateStep(currentStep)) {
                            currentStep = index;
                            updateSteps();
                            updateProgressBar();
                            updateNavigationButtons();
                        }
                    }
                });
            });
            
            // Acknowledge instructions checkbox
            const acknowledgeCheckbox = document.getElementById('acknowledge-instructions');
            if (acknowledgeCheckbox) {
                acknowledgeCheckbox.addEventListener('change', function() {
                    if (nextBtn) {
                        nextBtn.disabled = !this.checked;
                    }
                });
            }
            
            // Functions
            function updateSteps() {
                // Update stepper sidebar
                steps.forEach((step, index) => {
                    step.classList.remove('active', 'completed');
                    
                    const stepNumber = step.querySelector('.step-number');
                    const stepTitle = step.querySelector('.step-title h6');
                    
                    if (index === currentStep) {
                        step.classList.add('active');
                        if (stepNumber) {
                            stepNumber.classList.remove('bg-light', 'text-muted', 'border');
                            stepNumber.classList.add('bg-primary', 'text-white');
                        }
                        if (stepTitle) {
                            stepTitle.classList.remove('text-muted');
                        }
                    } else if (index < currentStep) {
                        step.classList.add('completed');
                        if (stepNumber) {
                            stepNumber.classList.remove('bg-light', 'text-muted', 'border', 'bg-primary', 'text-white');
                            stepNumber.classList.add('bg-success', 'text-white');
                        }
                    } else {
                        if (stepNumber) {
                            stepNumber.classList.remove('bg-primary', 'text-white', 'bg-success', 'text-white');
                            stepNumber.classList.add('bg-light', 'text-muted', 'border');
                        }
                        if (stepTitle) {
                            stepTitle.classList.add('text-muted');
                        }
                    }
                });
                
                // Update step content visibility
                stepContents.forEach((content, index) => {
                    content.classList.remove('active', 'show');
                    if (index === currentStep) {
                        content.classList.add('active', 'show');
                    }
                });
                
                // Update current step display
                if (currentStepDisplay) {
                    currentStepDisplay.textContent = currentStep + 1;
                }
                
                // Update button text on last step
                if (nextBtn) {
                    if (currentStep === totalSteps - 1) {
                        nextBtn.innerHTML = 'Submit <i class="fas fa-paper-plane ms-2"></i>';
                        nextBtn.classList.remove('btn-primary');
                        nextBtn.classList.add('btn-success');
                    } else {
                        nextBtn.innerHTML = 'Next <i class="fas fa-arrow-right ms-2"></i>';
                        nextBtn.classList.remove('btn-success');
                        nextBtn.classList.add('btn-primary');
                    }
                }
            }
            
            function updateProgressBar() {
                const progress = ((currentStep + 1) / totalSteps) * 100;
                if (progressBar) {
                    progressBar.style.width = `${progress}%`;
                }
            }
            
            function updateNavigationButtons() {
                if (prevBtn) {
                    prevBtn.disabled = currentStep === 0;
                }
                
                // On first step, only enable next if instructions are acknowledged
                if (nextBtn) {
                    if (currentStep === 0 && acknowledgeCheckbox) {
                        nextBtn.disabled = !acknowledgeCheckbox.checked;
                    } else {
                        nextBtn.disabled = false;
                    }
                }
            }
            
            function validateStep(stepIndex) {
                // Simple validation for demonstration
                // In a real application, you would implement more comprehensive validation
                
                if (stepIndex === 1) { // Section A validation
                    const title = document.getElementById('title');
                    const piDetails = document.getElementById('pi-details');
                    
                    if (title && !title.value.trim()) {
                        alert('Please enter the title of the proposal');
                        return false;
                    }
                    
                    if (piDetails && !piDetails.value.trim()) {
                        alert('Please enter Principal Investigator details');
                        return false;
                    }
                }
                
                if (stepIndex === 2) { // Section B validation
                    const abstract = document.getElementById('abstract');
                    const aims = document.getElementById('aims');
                    
                    if (abstract && !abstract.value.trim()) {
                        alert('Please enter an abstract/executive summary');
                        return false;
                    }
                    
                    if (abstract && countWords(abstract.value) > 250) {
                        alert('Abstract should not exceed 250 words');
                        return false;
                    }
                    
                    if (aims && !aims.value.trim()) {
                        alert('Please enter the aims or objectives of the study');
                        return false;
                    }
                }
                
                if (stepIndex === 3) { // Section C validation
                    const piName = document.getElementById('pi-name');
                    const piSignature = document.getElementById('pi-signature');
                    
                    if ((piName && !piName.value.trim()) || (piSignature && !piSignature.value.trim())) {
                        alert('Principal Investigator name and signature are required');
                        return false;
                    }
                }
                
                if (stepIndex === 4) { // Review validation
                    const finalPdf = document.getElementById('final-pdf');
                    
                    if (finalPdf && finalPdf.files.length === 0) {
                        alert('Please upload the final PDF document');
                        return false;
                    }
                }
                
                return true;
            }
            
            function countWords(text) {
                if (!text) return 0;
                return text.trim().split(/\s+/).filter(word => word.length > 0).length;
            }
        });
    </script>
