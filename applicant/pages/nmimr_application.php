<div class="add-new-protocol container-fluid mt-4 mb-4 p-4">
    <!-- Header -->
    <div class="welcome-header text-white p-4 rounded mb-4 position-relative overflow-hidden" 
         style="background: linear-gradient(135deg, #2c3e50, #4a6491);">
        <div class="header-gradient"></div>
        <div class="d-flex align-items-center position-relative z-1">
            <div>
                <h2 class="mb-1 fw-bold">Initial Submission Form A - NMIMR Researchers</h2>
                <p class="mb-0 opacity-75">Complete all sections for ethics review consideration</p>
            </div>
        </div>
        <div class="header-decoration">
            <i class="fas fa-flask"></i>
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
                    <li class="mb-1">Complete all sections before submission for ethics review</li>
                    <li class="mb-1">Download the NMIMR-IRB Researchers Checklist for further instructions</li>
                    <li class="mb-1">Proposal and consent form should be paged separately</li>
                    <li class="mb-1">Download the NMIMR-IRB Submission guide for further information</li>
                </ol>
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
                        <div class="step active" data-step="1">
                            <div class="step-header d-flex align-items-center mb-2">
                                <div class="step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                    1
                                </div>
                                <div class="step-title ms-3">
                                    <h6 class="fw-semibold mb-0">Instructions & Header</h6>
                                    <small class="text-muted">Protocol details</small>
                                </div>
                            </div>
                            <div class="step-progress ms-4 ps-3">
                                <div class="step-line"></div>
                            </div>
                        </div>

                        <!-- Step 2: Section A -->
                        <div class="step" data-step="2">
                            <div class="step-header d-flex align-items-center mb-2">
                                <div class="step-number bg-light text-muted border rounded-circle d-flex align-items-center justify-content-center">
                                    2
                                </div>
                                <div class="step-title ms-3">
                                    <h6 class="fw-semibold mb-0 text-muted">Section A: Background</h6>
                                    <small class="text-muted">PI details & project info</small>
                                </div>
                            </div>
                            <div class="step-progress ms-4 ps-3">
                                <div class="step-line"></div>
                            </div>
                        </div>

                        <!-- Step 3: Section B Part 1 -->
                        <div class="step" data-step="3">
                            <div class="step-header d-flex align-items-center mb-2">
                                <div class="step-number bg-light text-muted border rounded-circle d-flex align-items-center justify-content-center">
                                    3
                                </div>
                                <div class="step-title ms-3">
                                    <h6 class="fw-semibold mb-0 text-muted">Section B - Part 1</h6>
                                    <small class="text-muted">Abstract to Aims</small>
                                </div>
                            </div>
                            <div class="step-progress ms-4 ps-3">
                                <div class="step-line"></div>
                            </div>
                        </div>

                        <!-- Step 4: Section B Part 2 -->
                        <div class="step" data-step="4">
                            <div class="step-header d-flex align-items-center mb-2">
                                <div class="step-number bg-light text-muted border rounded-circle d-flex align-items-center justify-content-center">
                                    4
                                </div>
                                <div class="step-title ms-3">
                                    <h6 class="fw-semibold mb-0 text-muted">Section B - Part 2</h6>
                                    <small class="text-muted">Methodology & Ethics</small>
                                </div>
                            </div>
                            <div class="step-progress ms-4 ps-3">
                                <div class="step-line"></div>
                            </div>
                        </div>

                        <!-- Step 5: Section B Part 3 -->
                        <div class="step" data-step="5">
                            <div class="step-header d-flex align-items-center mb-2">
                                <div class="step-number bg-light text-muted border rounded-circle d-flex align-items-center justify-content-center">
                                    5
                                </div>
                                <div class="step-title ms-3">
                                    <h6 class="fw-semibold mb-0 text-muted">Section B - Part 3</h6>
                                    <small class="text-muted">Additional Materials</small>
                                </div>
                            </div>
                            <div class="step-progress ms-4 ps-3">
                                <div class="step-line"></div>
                            </div>
                        </div>

                        <!-- Step 6: Section C -->
                        <div class="step" data-step="6">
                            <div class="step-header d-flex align-items-center">
                                <div class="step-number bg-light text-muted border rounded-circle d-flex align-items-center justify-content-center">
                                    6
                                </div>
                                <div class="step-title ms-3">
                                    <h6 class="fw-semibold mb-0 text-muted">Section C: Signatures</h6>
                                    <small class="text-muted">Declarations & submission</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Indicator -->
                    <div class="progress mt-4" style="height: 6px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" id="stepperProgress"></div>
                    </div>
                    <div class="text-center mt-2">
                        <small class="text-muted">Step <span id="currentStep">1</span> of 6</small>
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
            <form id="nmimrProtocolForm" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="application_type" value="nmimr">
                
                <!-- Step 1: Instructions & Protocol Information -->
                <div class="step-content active" data-step="1">
                    <div class="card mb-4">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><i class="fas fa-file-signature me-2"></i>Protocol Identification</h5>
                                <p class="text-muted mb-0 small">Step 1 of 6 - Basic study information</p>
                            </div>
                            <span class="badge bg-primary">Required</span>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="protocolNumber" class="form-label fw-semibold">Protocol Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="protocolNumber" name="protocol_number" required>
                                    <small class="text-muted">Unique identifier for your study</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="versionNumber" class="form-label fw-semibold">Version Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="versionNumber" name="version_number" placeholder="e.g., 1.0" required>
                                    <small class="text-muted">Document version (start with 1.0)</small>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="submissionDate" class="form-label fw-semibold">Submission Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="submissionDate" name="submission_date" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Section A - Background Information -->
                <div class="step-content" data-step="2">
                    <div class="card mb-4 border-primary">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><i class="fas fa-address-card me-2"></i>SECTION A - BACKGROUND INFORMATION</h5>
                                <p class="mb-0 opacity-75 small">Step 2 of 6 - Principal Investigator details</p>
                            </div>
                            <span class="badge bg-white text-primary">Required</span>
                        </div>
                        <div class="card-body">
                            
                            <!-- PI Information -->
                            <div class="pi-info mb-4 p-3 border rounded">
                                <h6 class="fw-semibold mb-3"><i class="fas fa-user me-2"></i>Principal Investigator</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="pi_name" class="form-label fw-semibold">Full Name (Surname First) <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="pi_name" name="pi_name" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="pi_institution" class="form-label fw-semibold">Institution & Department <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="pi_institution" name="pi_institution" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="pi_address" class="form-label fw-semibold">Postal Address <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="pi_address" name="pi_address" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="pi_phone" class="form-label fw-semibold">Telephone <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="pi_phone" name="pi_phone" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="pi_email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="pi_email" name="pi_email" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Co-Principal Investigators -->
                            <div class="co-pi-info mb-4">
                                <h6 class="fw-semibold mb-3"><i class="fas fa-users me-2"></i>Co-Principal Investigator(s)</h6>
                                
                                <div class="co-pi-entry mb-3 p-3 border rounded">
                                    <h6 class="fw-semibold mb-3">Co-Principal Investigator 1</h6>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="copi1_name" class="form-label fw-semibold">Name</label>
                                            <input type="text" class="form-control" id="copi1_name" name="copi1_name">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="copi1_qualification" class="form-label fw-semibold">Qualification</label>
                                            <input type="text" class="form-control" id="copi1_qualification" name="copi1_qualification">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="copi1_details" class="form-label fw-semibold">Department & Email</label>
                                            <input type="text" class="form-control" id="copi1_details" name="copi1_details">
                                        </div>
                                    </div>
                                </div>

                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addCoInvestigator()">
                                    <i class="fas fa-plus me-1"></i>Add Another Co-Principal Investigator
                                </button>
                            </div>

                            <!-- Project Information -->
                            <div class="project-info mb-4 p-3 border rounded">
                                <h6 class="fw-semibold mb-3"><i class="fas fa-clipboard-list me-2"></i>Project Information</h6>
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="proposalTitle" class="form-label fw-semibold">Title of Proposal <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="proposalTitle" name="proposal_title" rows="2" required></textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="stcNumber" class="form-label fw-semibold">NMIMR STC Number</label>
                                        <input type="text" class="form-control" id="stcNumber" name="stc_number" placeholder="If applicable">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="stcDate" class="form-label fw-semibold">NMIMR STC Approval Date</label>
                                        <input type="date" class="form-control" id="stcDate" name="stc_date">
                                        <small class="text-muted">Attach Letter of Approval in final submission</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Type of Research <span class="text-danger">*</span></label>
                                        <div class="mt-2">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="research_type_biomedical" id="typeBiomedical" value="Biomedical">
                                                <label class="form-check-label" for="typeBiomedical">Biomedical</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="research_type_social" id="typeSocial" value="Social/Behavioural">
                                                <label class="form-check-label" for="typeSocial">Social/Behavioural</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="research_type_other" id="typeOther" value="Other">
                                                <label class="form-check-label" for="typeOther">Others</label>
                                            </div>
                                        </div>
                                        <input type="text" class="form-control mt-2" id="typeOtherSpecify" name="research_type_other_specify" placeholder="Please specify" style="display: none;">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="projectDuration" class="form-label fw-semibold">Duration of Project <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="projectDuration" name="project_duration" placeholder="e.g., 12 months" required>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="fundingSource" class="form-label fw-semibold">Source(s) of Funding</label>
                                        <textarea class="form-control" id="fundingSource" name="funding_source" rows="2" placeholder="Name and address of funding source(s)"></textarea>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="priorIRB" class="form-label fw-semibold">Prior IRB Review</label>
                                        <textarea class="form-control" id="priorIRB" name="prior_irb" rows="3" placeholder="Name any other IRB this proposal has been submitted to. Attach approval letter if applicable. In case of rejection, state reasons."></textarea>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="collaboratingInstitutions" class="form-label fw-semibold">Collaborating Institutions</label>
                                        <textarea class="form-control" id="collaboratingInstitutions" name="collaborating_institutions" rows="2" placeholder="List collaborating institutions. Attach Letter(s) of Approval."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Section B Part 1 -->
                <div class="step-content" data-step="3">
                    <div class="card mb-4 border-info">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>SECTION B - PROPOSAL OUTLINE - PART 1</h5>
                                <p class="mb-0 opacity-75 small">Step 3 of 6 - Abstract, Introduction, Literature Review & Aims</p>
                            </div>
                            <span class="badge bg-white text-info">Required</span>
                        </div>
                        <div class="card-body">
                            
                            <!-- Abstract/Executive Summary -->
                            <div class="mb-4">
                                <label for="abstract" class="form-label fw-semibold">ABSTRACT/EXECUTIVE SUMMARY <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="abstract" name="abstract" rows="5" maxlength="250" required></textarea>
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-muted">Not more than 250 words</small>
                                    <small class="text-muted"><span id="abstract-count">0</span>/250 words</small>
                                </div>
                            </div>

                            <!-- Introduction/Rationale -->
                            <div class="mb-4">
                                <label for="introduction" class="form-label fw-semibold">INTRODUCTION/RATIONALE <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="introduction" name="introduction" rows="8" required></textarea>
                                <small class="text-muted">Not more than 5 pages</small>
                            </div>

                            <!-- Literature Review -->
                            <div class="mb-4">
                                <label for="literatureReview" class="form-label fw-semibold">LITERATURE REVIEW <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="literatureReview" name="literature_review" rows="8" required></textarea>
                                <small class="text-muted">Not more than 5 pages</small>
                            </div>

                            <!-- Aims or Objectives -->
                            <div class="mb-4">
                                <label for="studyAims" class="form-label fw-semibold">AIMS OR OBJECTIVES OF STUDY <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="studyAims" name="study_aims" rows="4" required></textarea>
                                <small class="text-muted">List the main aims and objectives of your study</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Section B Part 2 -->
                <div class="step-content" data-step="4">
                    <div class="card mb-4 border-info">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><i class="fas fa-microscope me-2"></i>SECTION B - PROPOSAL OUTLINE - PART 2</h5>
                                <p class="mb-0 opacity-75 small">Step 4 of 6 - Methodology & Ethical Considerations</p>
                            </div>
                            <span class="badge bg-white text-info">Required</span>
                        </div>
                        <div class="card-body">
                            
                            <!-- Methodology -->
                            <div class="mb-4">
                                <label for="methodology" class="form-label fw-semibold">METHODOLOGY <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="methodology" name="methodology" rows="8" required></textarea>
                                <small class="text-muted">Include Inclusion and Exclusion Criteria</small>
                            </div>

                            <!-- Ethical Considerations -->
                            <div class="mb-4">
                                <label for="ethicalConsiderations" class="form-label fw-semibold">ETHICAL CONSIDERATIONS <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="ethicalConsiderations" name="ethical_considerations" rows="6" required></textarea>
                                <small class="text-muted">Consent procedures, confidentiality, privacy, risks and benefits, etc.</small>
                            </div>

                            <!-- Expected Outcome/Results -->
                            <div class="mb-4">
                                <label for="expectedOutcomes" class="form-label fw-semibold">EXPECTED OUTCOME/RESULTS <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="expectedOutcomes" name="expected_outcomes" rows="4" required></textarea>
                                <small class="text-muted">Describe expected outcomes and results</small>
                            </div>

                            <!-- References -->
                            <div class="mb-4">
                                <label for="references" class="form-label fw-semibold">REFERENCES <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="references" name="references" rows="6" required></textarea>
                                <small class="text-muted">List all references in appropriate format</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Section B Part 3 -->
                <div class="step-content" data-step="5">
                    <div class="card mb-4 border-warning">
                        <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><i class="fas fa-paperclip me-2"></i>SECTION B - PROPOSAL OUTLINE - PART 3</h5>
                                <p class="mb-0 opacity-75 small">Step 5 of 6 - Additional Materials & Documents</p>
                            </div>
                            <span class="badge bg-white text-warning">Required</span>
                        </div>
                        <div class="card-body">
                            
                            <!-- Work Plan -->
                            <div class="mb-4">
                                <label for="workPlan" class="form-label fw-semibold">WORK PLAN</label>
                                <textarea class="form-control" id="workPlan" name="work_plan" rows="4"></textarea>
                                <small class="text-muted">Outline your work plan/timeline</small>
                            </div>

                            <!-- Budget -->
                            <div class="mb-4">
                                <label for="budget" class="form-label fw-semibold">BUDGET AND BUDGET JUSTIFICATION</label>
                                <textarea class="form-control" id="budget" name="budget" rows="5"></textarea>
                                <small class="text-muted">Provide detailed budget and justification</small>
                            </div>

                            <!-- Required Attachments -->
                            <div class="attachments-section p-3 border rounded">
                                <h6 class="fw-semibold mb-3"><i class="fas fa-paperclip me-2"></i>Required Attachments</h6>
                                
                                <!-- Consent Form -->
                                <div class="mb-3">
                                    <label for="consentForm" class="form-label fw-semibold">CONSENT FORM <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="consentForm" name="consent_form" accept=".pdf,.doc,.docx" required>
                                    <small class="text-muted">Download NMIMR-IRB Consent form template</small>
                                </div>

                                <!-- Assent Form -->
                                <div class="mb-3">
                                    <label for="assentForm" class="form-label fw-semibold">ASSENT FORM AND PARENTAL CONSENT FORM</label>
                                    <input type="file" class="form-control" id="assentForm" name="assent_form" accept=".pdf,.doc,.docx">
                                    <small class="text-muted">Only applicable where children (12-17) are recruited</small>
                                </div>

                                <!-- Data Collection Instruments -->
                                <div class="mb-3">
                                    <label for="dataInstruments" class="form-label fw-semibold">DATA COLLECTION INSTRUMENTS <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="dataInstruments" name="data_instruments" accept=".pdf,.doc,.docx,.xls,.xlsx" multiple required>
                                    <small class="text-muted">Interview Guide, Questionnaire, etc.</small>
                                </div>

                                <!-- Additional Documents -->
                                <div class="mb-3">
                                    <label for="additionalDocs" class="form-label fw-semibold">Additional Supporting Documents</label>
                                    <input type="file" class="form-control" id="additionalDocs" name="additional_documents[]" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" multiple>
                                    <small class="text-muted">Any other relevant documents (maximum 10 files, 10MB each)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 6: Section C - Signatures -->
                <div class="step-content" data-step="6">
                    <div class="card mb-4 border-success">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><i class="fas fa-signature me-2"></i>SECTION C - SIGNATURES & SUBMISSION</h5>
                                <p class="mb-0 opacity-75 small">Step 6 of 6 - Declarations & final submission</p>
                            </div>
                            <span class="badge bg-white text-success">Required</span>
                        </div>
                        <div class="card-body">
                            
                            <!-- PI Declaration -->
                            <div class="declaration-card mb-4 p-4 border rounded bg-light">
                                <h6 class="fw-bold mb-3">DECLARATION BY PRINCIPAL INVESTIGATOR / CO-INVESTIGATOR</h6>
                                <p class="mb-3">By signing below, I confirm that:</p>
                                
                                <div class="mb-3">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="declaration1" name="declarations[]" value="1" required>
                                        <label class="form-check-label" for="declaration1">
                                            I will ensure all procedures comply with relevant policies and regulations
                                        </label>
                                    </div>
                                    
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="declaration2" name="declarations[]" value="2" required>
                                        <label class="form-check-label" for="declaration2">
                                            I will submit amendments for review prior to implementation
                                        </label>
                                    </div>
                                    
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="declaration3" name="declarations[]" value="3" required>
                                        <label class="form-check-label" for="declaration3">
                                            I will report serious adverse events within specified timelines
                                        </label>
                                    </div>
                                    
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="declaration4" name="declarations[]" value="4" required>
                                        <label class="form-check-label" for="declaration4">
                                            I will submit annual progress reports for review and renewal
                                        </label>
                                    </div>
                                    
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="declaration5" name="declarations[]" value="5" required>
                                        <label class="form-check-label" for="declaration5">
                                            I will submit a final report at the end of the study
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="row mt-4">
                                    <div class="col-md-6 mb-3">
                                        <label for="piSignature" class="form-label fw-semibold">Name of Principal Investigator <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="piSignature" name="pi_signature" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="piDate" class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="piDate" name="pi_date" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Co-PI Declaration -->
                            <div class="declaration-card mb-4 p-4 border rounded bg-light">
                                <h6 class="fw-bold mb-3">DECLARATION BY CO-PRINCIPAL INVESTIGATOR</h6>
                                
                                <div class="row mt-3">
                                    <div class="col-md-6 mb-3">
                                        <label for="coPiSignature" class="form-label fw-semibold">Name of Co-Principal Investigator</label>
                                        <input type="text" class="form-control" id="coPiSignature" name="copi_signature">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="coPiDate" class="form-label fw-semibold">Date</label>
                                        <input type="date" class="form-control" id="coPiDate" name="copi_date">
                                    </div>
                                </div>
                            </div>

                            <!-- Final Confirmation -->
                            <div class="alert alert-warning mt-4">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-exclamation-triangle me-3 mt-1"></i>
                                    <div>
                                        <h6 class="alert-heading">Important Notice</h6>
                                        <p class="mb-2">By submitting this form, you certify that all information provided is accurate and complete. Any false information may result in rejection of your application.</p>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="finalConfirmation" name="final_confirmation" required>
                                            <label class="form-check-label fw-semibold" for="finalConfirmation">
                                                I confirm that all information provided is accurate and complete to the best of my knowledge
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons (Mobile) -->
                <div class="stepper-navigation-mobile d-lg-none mt-4">
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-outline-secondary" id="prevStepBtnMobile" disabled>
                            <i class="fas fa-arrow-left me-2"></i>Previous
                        </button>
                        <button class="btn btn-primary" id="nextStepBtnMobile">
                            Next <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                <!-- Final Submission Buttons -->
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top d-none" id="finalActions">
                    <div>
                        <button type="button" class="btn btn-outline-secondary" id="editFormBtn">
                            <i class="fas fa-edit me-2"></i>Edit Form
                        </button>
                    </div>
                    <div class="d-flex gap-3">
                        <button type="button" class="btn btn-outline-secondary" id="saveDraftBtn">
                            <i class="fas fa-save me-2"></i>Save Draft
                        </button>
                        <button type="button" class="btn btn-light" onclick="window.history.back();">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-paper-plane me-2"></i>Submit Protocol
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .welcome-header {
        position: relative;
        overflow: hidden;
    }
    
    .header-decoration {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        opacity: 0.1;
        font-size: 4rem;
    }
    
    .header-decoration i {
        margin-left: 20px;
    }
    
    .stepper-sidebar {
        background: #fff;
        border-radius: 12px;
    }
    
    .stepper-vertical {
        position: relative;
    }
    
    .step {
        margin-bottom: 1.5rem;
        position: relative;
    }
    
    .step:last-child {
        margin-bottom: 0;
    }
    
    .step-number {
        width: 36px;
        height: 36px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }
    
    .step.active .step-number {
        background-color: #0d6efd !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
    }
    
    .step.completed .step-number {
        background-color: #198754 !important;
        color: white !important;
    }
    
    .step.active .step-title h6 {
        color: #0d6efd !important;
    }
    
    .step.completed .step-title h6 {
        color: #198754 !important;
    }
    
    .step-progress {
        position: relative;
    }
    
    .step-line {
        position: absolute;
        left: 0;
        top: 0;
        width: 2px;
        height: calc(100% + 1.5rem);
        background: #e9ecef;
    }
    
    .step:last-child .step-line {
        display: none;
    }
    
    .step.active .step-line {
        background: linear-gradient(to bottom, #0d6efd, #e9ecef);
    }
    
    .step.completed .step-line {
        background: #198754;
    }
    
    .step-content {
        display: none;
        animation: fadeIn 0.5s ease;
    }
    
    .step-content.active {
        display: block;
    }
    
    .declaration-card {
        background-color: #f8f9fa;
        border-left: 4px solid #0d6efd;
    }
    
    .pi-info, .co-pi-entry, .project-info {
        border: 1px solid #dee2e6;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script>
    // Current step tracking
    let currentStep = 1;
    const totalSteps = 6;
    const completedSteps = new Set();
    
    // Initialize form
    document.addEventListener('DOMContentLoaded', function() {
        updateProgressBar();
        setupEventListeners();
    });
    
    // Setup event listeners
    function setupEventListeners() {
        // Step click navigation
        document.querySelectorAll('.step').forEach(step => {
            step.addEventListener('click', function() {
                const stepNum = parseInt(this.dataset.step);
                if (completedSteps.has(stepNum - 1) || stepNum === 1) {
                    goToStep(stepNum);
                }
            });
        });
        
        // Character counters
        setupCharacterCounters();
        
        // Other checkbox toggle
        document.getElementById('typeOther').addEventListener('change', function() {
            document.getElementById('typeOtherSpecify').style.display = this.checked ? 'block' : 'none';
        });
        
        // Navigation buttons
        document.getElementById('nextStepBtn').addEventListener('click', nextStep);
        document.getElementById('prevStepBtn').addEventListener('click', prevStep);
        document.getElementById('nextStepBtnMobile').addEventListener('click', nextStep);
        document.getElementById('prevStepBtnMobile').addEventListener('click', prevStep);
        
        // Form submission
        document.getElementById('nmimrProtocolForm').addEventListener('submit', submitForm);
    }
    
    // Navigate to specific step
    function goToStep(stepNumber) {
        // Hide all steps
        document.querySelectorAll('.step-content').forEach(section => {
            section.classList.remove('active');
        });
        
        // Update stepper states
        document.querySelectorAll('.step').forEach((step, index) => {
            const stepNum = index + 1;
            step.classList.remove('active');
            if (stepNum < stepNumber) {
                step.classList.add('completed');
            } else {
                step.classList.remove('completed');
            }
            if (stepNum === stepNumber) {
                step.classList.add('active');
            }
        });
        
        // Show target step
        document.querySelector(`.step-content[data-step="${stepNumber}"]`).classList.add('active');
        
        currentStep = stepNumber;
        updateProgressBar();
        updateNavigationButtons();
        scrollToTop();
    }
    
    // Next step
    function nextStep() {
        if (validateCurrentStep()) {
            completedSteps.add(currentStep);
            markStepCompleted(currentStep);
            
            if (currentStep < totalSteps) {
                goToStep(currentStep + 1);
            } else {
                // Show final actions on last step
                document.getElementById('finalActions').classList.remove('d-none');
                document.getElementById('nextStepBtn').classList.add('d-none');
                document.getElementById('nextStepBtnMobile').classList.add('d-none');
            }
        }
    }
    
    // Previous step
    function prevStep() {
        if (currentStep > 1) {
            goToStep(currentStep - 1);
        }
    }
    
    // Update navigation buttons
    function updateNavigationButtons() {
        const prevBtn = document.getElementById('prevStepBtn');
        const prevBtnMobile = document.getElementById('prevStepBtnMobile');
        const nextBtn = document.getElementById('nextStepBtn');
        const nextBtnMobile = document.getElementById('nextStepBtnMobile');
        
        prevBtn.disabled = currentStep === 1;
        prevBtnMobile.disabled = currentStep === 1;
        
        if (currentStep === totalSteps) {
            nextBtn.classList.add('d-none');
            nextBtnMobile.classList.add('d-none');
            document.getElementById('finalActions').classList.remove('d-none');
        } else {
            nextBtn.classList.remove('d-none');
            nextBtnMobile.classList.remove('d-none');
            document.getElementById('finalActions').classList.add('d-none');
        }
    }
    
    // Validate current step
    function validateCurrentStep() {
        const currentSection = document.querySelector(`.step-content[data-step="${currentStep}"]`);
        const requiredFields = currentSection.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            alert('Please fill in all required fields before proceeding.');
            scrollToTop();
        }
        
        return isValid;
    }
    
    // Update progress bar
    function updateProgressBar() {
        const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
        document.getElementById('stepperProgress').style.width = `${progress}%`;
        document.getElementById('currentStep').textContent = currentStep;
    }
    
    // Mark step as completed
    function markStepCompleted(stepNumber) {
        const stepElement = document.querySelector(`.step[data-step="${stepNumber}"]`);
        if (stepElement) {
            stepElement.classList.add('completed');
        }
    }
    
    // Add co-investigator
    function addCoInvestigator() {
        const container = document.querySelector('.co-pi-info');
        const count = container.querySelectorAll('.co-pi-entry').length + 1;
        
        const newEntry = document.createElement('div');
        newEntry.className = 'co-pi-entry mb-3 p-3 border rounded';
        newEntry.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-semibold mb-0">Co-Principal Investigator ${count}</h6>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.co-pi-entry').remove(); updateCoPiLabels();">
                    <i class="fas fa-times me-1"></i>Remove
                </button>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-semibold">Name</label>
                    <input type="text" class="form-control" name="copi${count}_name">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-semibold">Qualification</label>
                    <input type="text" class="form-control" name="copi${count}_qualification">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-semibold">Department & Email</label>
                    <input type="text" class="form-control" name="copi${count}_details">
                </div>
            </div>
        `;
        container.appendChild(newEntry);
    }
    
    // Update Co-PI labels after removal
    function updateCoPiLabels() {
        const entries = document.querySelectorAll('.co-pi-entry');
        entries.forEach((entry, index) => {
            const label = entry.querySelector('h6');
            if (label && index > 0) {
                label.textContent = `Co-Principal Investigator ${index}`;
            }
        });
    }
    
    // Setup character counters
    function setupCharacterCounters() {
        const abstractTextarea = document.getElementById('abstract');
        if (abstractTextarea) {
            abstractTextarea.addEventListener('input', function() {
                const words = this.value.trim().split(/\s+/).filter(word => word.length > 0);
                document.getElementById('abstract-count').textContent = words.length;
            });
        }
    }
    
    // Scroll to top
    function scrollToTop() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    
    // Submit form
    function submitForm(e) {
        e.preventDefault();
        
        if (!document.getElementById('finalConfirmation').checked) {
            alert('Please confirm that all information is accurate and complete.');
            return;
        }
        
        // Show loading overlay
        document.body.insertAdjacentHTML('beforeend', `
            <div class="loading-overlay" style="
                position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
                background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(5px);
                z-index: 9999; display: flex; align-items: center; justify-content: center;
            ">
                <div class="text-center">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                    <h4 class="text-primary">Submitting Form</h4>
                    <p class="text-muted">Please wait while we process your submission...</p>
                </div>
            </div>
        `);
        
        // Simulate API call
        setTimeout(() => {
            document.querySelector('.loading-overlay').remove();
            
            alert('Form submitted successfully! A confirmation email will be sent to you.\n\nPlease remember to send the complete PDF to nirb@noguchi.ug.edu.gh');
            
            window.location.href = '/applicant-dashboard';
        }, 2000);
    }
</script>
