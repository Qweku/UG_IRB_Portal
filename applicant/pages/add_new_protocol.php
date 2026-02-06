<?php
// require_once '../../includes/functions/helpers.php';

// Include CSRF protection
// require_once '../../includes/functions/csrf.php';

// Fetch dropdown data
// $sponsors = getSponsors();
// $sites = getStudyLocationsList();

// error_log("SPONSORS: " . print_r($sponsors, true));
// error_log("SITES: " . print_r($sites, true));
?>

<div class="add-new-protocol container-fluid mt-4 mb-4">
    <!-- Header -->
    <div class="welcome-header text-white p-4 rounded mb-4 position-relative overflow-hidden">
        <div class="header-gradient"></div>
        <div class="d-flex align-items-center position-relative z-1">
            <!-- <img src="../../admin/assets/images/ug-nmimr-logo.jpg" alt="UG NMIMR Logo" class="me-3 logo-shadow" style="height: 60px;"> -->
            <div>
                <h2 class="mb-1 fw-bold">Add New Protocol</h2>
                <p class="mb-0 opacity-75">Submit a new research protocol for IRB review</p>
            </div>
        </div>
        <div class="header-decoration">
            <i class="fas fa-flask"></i>
            <i class="fas fa-dna"></i>
            <i class="fas fa-microscope"></i>
        </div>
    </div>

    <!-- Progress Indicator -->
    <div class="progress mb-4" style="height: 8px;">
        <div class="progress-bar bg-success progress-animated" role="progressbar" style="width: 0%" id="formProgress"></div>
    </div>

    <form id="protocolForm">
        <?php echo csrf_field(); ?>
        <div class="row">
            <div class="col-md-6">
                <!-- Basic Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="protocol_title" class="form-label">Protocol Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="protocol_title" name="protocol_title" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="protocol_number" class="form-label">Protocol Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="protocol_number" name="protocol_number" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="protocol_date" class="form-label">Protocol Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="protocol_date" name="protocol_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="version_number" class="form-label">Protocol Version Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="version_number" name="version_number" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <!-- Sponsor Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-building me-2"></i>Sponsor Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="sponsor_protocol_number" class="form-label">Sponsor Protocol Number</label>
                                <input type="text" class="form-control" id="sponsor_protocol_number" name="sponsor_protocol_number">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="sponsor_name" class="form-label">Sponsor Name <span class="text-danger">*</span></label>
                               
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="sites" class="form-label">Study Sites <span class="text-danger">*</span></label>
                               
                                <div class="form-text">Hold Ctrl (Cmd on Mac) to select multiple sites</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>




        <!-- Proposed Dates -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Proposed Study Dates</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="start_date" class="form-label">Proposed Start Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="end_date" class="form-label">Proposed End Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="end_date" name="end_date" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="d-flex justify-content-between">
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane me-2"></i>Submit Protocol
            </button>
        </div>
    </form>
</div>

<script>
    // Form validation
    document.getElementById('protocolForm').addEventListener('submit', function(e) {
        const startDate = new Date(document.getElementById('start_date').value);
        const endDate = new Date(document.getElementById('end_date').value);

        if (startDate >= endDate) {
            e.preventDefault();
            alert('Proposed end date must be after the start date.');
            return false;
        }

        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
    });

    // Initialize select2 for better multi-select if available
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('#sites').select2({
            placeholder: 'Select study sites',
            allowClear: true
        });
    }

    // Update progress bar
    function updateProgress() {
        const requiredFields = document.querySelectorAll('input[required], select[required]');
        const filledFields = Array.from(requiredFields).filter(field => field.value.trim() !== '');
        const progress = (filledFields.length / requiredFields.length) * 100;
        document.getElementById('formProgress').style.width = progress + '%';
    }

    // Add event listeners for progress update
    document.querySelectorAll('input, select').forEach(field => {
        field.addEventListener('input', updateProgress);
        field.addEventListener('change', updateProgress);
    });

    // Initialize progress
    updateProgress();
</script>

<style>
/* Header Styles */
.welcome-header {
    background: linear-gradient(135deg, var(--royal-blue) 0%, var(--royal-blue-light) 100%);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border: none;
}

.header-gradient {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
    z-index: 0;
}

.logo-shadow {
    filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
    transition: transform 0.3s ease;
}

.logo-shadow:hover {
    transform: scale(1.05);
}

.header-decoration {
    position: absolute;
    top: 20px;
    right: 20px;
    opacity: 0.3;
    font-size: 2rem;
}

.header-decoration i {
    margin-left: 10px;
    animation: float 3s ease-in-out infinite;
}

.header-decoration i:nth-child(1) { animation-delay: 0s; }
.header-decoration i:nth-child(2) { animation-delay: 1s; }
.header-decoration i:nth-child(3) { animation-delay: 2s; }

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

/* Card Enhancements */
.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    overflow: hidden;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
}

.card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid rgba(0,0,0,0.05);
    padding: 1.5rem;
}

.card-header h5 {
    color: #495057;
    font-weight: 600;
    margin: 0;
}

.card-body {
    padding: 2rem;
}

/* Form Controls */
.form-control, .form-select {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
    background: rgba(255,255,255,0.8);
}

.form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    background: white;
}

.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

/* Button Styles */
.btn {
    border-radius: 10px;
    padding: 0.75rem 2rem;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
}

.btn-primary {
    background: linear-gradient(135deg, var(--royal-blue) 0%, var(--royal-blue-light) 100%);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: #6c757d;
    box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
}

.btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(108, 117, 125, 0.4);
}

/* Progress Bar */
.progress {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
}

.progress-bar {
    background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
    transition: width 0.3s ease;
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeInUp 0.6s ease-out;
}

.card:nth-child(1) { animation-delay: 0.1s; }
.card:nth-child(2) { animation-delay: 0.2s; }
.card:nth-child(3) { animation-delay: 0.3s; }

/* Responsive adjustments */
@media (max-width: 768px) {
    .welcome-header {
        text-align: center;
    }

    .header-decoration {
        display: none;
    }

    .card-body {
        padding: 1.5rem;
    }
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
}
</style>