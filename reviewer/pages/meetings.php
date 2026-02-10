<?php

// NOTE: Authentication is already handled by index.php router
// No duplicate auth check needed here

$userId = $_SESSION['user_id'] ?? 0;
$userName = $_SESSION['full_name'] ?? 'Reviewer';

// Get IRB meetings
$meetings = getReviewerMeetings();

?>
<style>
/* Meetings Page Specific Styles */
.meeting-header-section {
    background: linear-gradient(135deg, #1a5276 0%, #2980b9 100%);
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 24px;
    box-shadow: 0 10px 40px rgba(41, 128, 185, 0.2);
}

.meeting-card {
    border: none;
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    margin-bottom: 20px;
}

.meeting-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

.meeting-card .card-header {
    background: linear-gradient(135deg, #1a5276 0%, #2980b9 100%);
    color: white;
    border: none;
    padding: 24px;
}

.meeting-card .meeting-date-large {
    font-size: 32px;
    font-weight: 700;
    line-height: 1;
}

.meeting-card .meeting-day {
    font-size: 14px;
    opacity: 0.9;
    margin-top: 4px;
}

.meeting-card .meeting-time {
    font-size: 18px;
    font-weight: 600;
    margin-top: 8px;
}

.meeting-card .card-body {
    padding: 24px;
}

.meeting-info-item {
    display: flex;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #e9ecef;
}

.meeting-info-item:last-child {
    border-bottom: none;
}

.meeting-info-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 16px;
    color: #2980b9;
}

.meeting-info-content h6 {
    margin: 0;
    font-weight: 600;
    color: #2c3e50;
}

.meeting-info-content p {
    margin: 0;
    font-size: 13px;
    color: #6c757d;
}

/* Agenda List */
.agenda-item {
    display: flex;
    align-items: center;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 12px;
    margin-bottom: 12px;
}

.agenda-number {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1a5276 0%, #2980b9 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    margin-right: 16px;
    flex-shrink: 0;
}

.agenda-details h6 {
    margin: 0 0 4px 0;
    font-weight: 600;
    color: #2c3e50;
}

.agenda-details p {
    margin: 0;
    font-size: 13px;
    color: #6c757d;
}

/* Animations */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in {
    animation: fadeIn 0.5s ease forwards;
}
</style>

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="content-section col-lg-10 col-md-9 ms-sm-auto px-4 py-3">

            <!-- Page Header -->
            <div class="meeting-header-section text-white fade-in">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-1"><i class="fas fa-calendar-alt me-3"></i>IRB Meetings</h2>
                        <p class="mb-0">View upcoming IRB meetings and review schedules</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <span class="badge bg-light text-dark" style="border-radius: 25px; padding: 10px 20px; font-size: 14px;">
                            <i class="fas fa-clock me-2"></i>
                            <?php echo count($meetings); ?> Upcoming
                        </span>
                    </div>
                </div>
            </div>

            <!-- Meetings List -->
            <div class="row">
                <?php if (!empty($meetings)): ?>
                    <?php foreach ($meetings as $index => $meeting): ?>
                        <div class="col-lg-6 fade-in" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                            <div class="meeting-card">
                                <div class="card-header">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="meeting-date-large">
                                                <?php echo date('d', strtotime($meeting['meeting_date'])); ?>
                                            </div>
                                            <div class="meeting-day">
                                                <?php echo date('l', strtotime($meeting['meeting_date'])); ?>
                                            </div>
                                        </div>
                                        <div class="col text-end">
                                            <div class="meeting-time">
                                                <i class="fas fa-clock me-2"></i>
                                                <?php echo date('M Y', strtotime($meeting['meeting_date'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="meeting-info-item">
                                        <div class="meeting-info-icon">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </div>
                                        <div class="meeting-info-content">
                                            <h6>Location</h6>
                                            <p><?php echo htmlspecialchars($meeting['location'] ?? 'Noguchi Memorial Institute'); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="meeting-info-item">
                                        <div class="meeting-info-icon">
                                            <i class="fas fa-bell"></i>
                                        </div>
                                        <div class="meeting-info-content">
                                            <h6>Agenda Deadline</h6>
                                            <p><?php echo date('M d, Y', strtotime($meeting['meeting_date'] . ' -7 days')); ?></p>
                                        </div>
                                    </div>

                                    <div class="meeting-info-item">
                                        <div class="meeting-info-icon">
                                            <i class="fas fa-file-alt"></i>
                                        </div>
                                        <div class="meeting-info-content">
                                            <h6>Review Materials</h6>
                                            <p>Available 1 week before meeting</p>
                                        </div>
                                    </div>

                                    <!-- Sample Agenda Preview -->
                                    <div class="mt-4">
                                        <h6 class="fw-semibold mb-3">
                                            <i class="fas fa-list me-2"></i>Sample Agenda
                                        </h6>
                                        <div class="agenda-item">
                                            <div class="agenda-number">1</div>
                                            <div class="agenda-details">
                                                <h6>New Protocol Reviews</h6>
                                                <p>Review of submitted applications</p>
                                            </div>
                                        </div>
                                        <div class="agenda-item">
                                            <div class="agenda-number">2</div>
                                            <div class="agenda-details">
                                                <h6>Continuing Review</h6>
                                                <p>Annual progress reports</p>
                                            </div>
                                        </div>
                                        <div class="agenda-item">
                                            <div class="agenda-number">3</div>
                                            <div class="agenda-details">
                                                <h6>Amendment Requests</h6>
                                                <p>Protocol modifications</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <button class="btn btn-outline-primary w-100" style="border-radius: 25px;">
                                            <i class="fas fa-download me-2"></i>Download Agenda
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                            <div class="card-body text-center py-5">
                                <div style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
                                    <i class="fas fa-calendar-alt" style="font-size: 48px; color: #adb5bd;"></i>
                                </div>
                                <h5 style="color: #2c3e50; margin-bottom: 8px;">No Upcoming Meetings</h5>
                                <p class="text-muted mb-4">There are no IRB meetings scheduled at the moment. Check back later for updates.</p>
                                <a href="/reviewer-dashboard" class="btn btn-primary" style="border-radius: 25px; padding: 12px 30px;">
                                    <i class="fas fa-home me-2"></i>Back to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Meeting Guidelines -->
            <div class="card mt-4 fade-in" style="border-radius: 16px; border: none; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);">
                <div class="card-header" style="background: linear-gradient(135deg, #1a5276 0%, #2980b9 100%); color: white; border-radius: 16px 16px 0 0; border: none;">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Reviewer Guidelines for Meetings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="d-flex align-items-start">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); display: flex; align-items: center; justify-content: center; margin-right: 12px; flex-shrink: 0;">
                                    <i class="fas fa-book-open" style="color: #155724;"></i>
                                </div>
                                <div>
                                    <h6 style="font-weight: 600; color: #2c3e50;">Review Materials</h6>
                                    <p class="mb-0 text-muted" style="font-size: 13px;">Review all applications at least 3 days before the meeting</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="d-flex align-items-start">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #cce5ff 0%, #b8daff 100%); display: flex; align-items: center; justify-content: center; margin-right: 12px; flex-shrink: 0;">
                                    <i class="fas fa-pen" style="color: #004085;"></i>
                                </div>
                                <div>
                                    <h6 style="font-weight: 600; color: #2c3e50;">Submit Comments</h6>
                                    <p class="mb-0 text-muted" style="font-size: 13px;">Provide written comments before the meeting</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="d-flex align-items-start">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%); display: flex; align-items: center; justify-content: center; margin-right: 12px; flex-shrink: 0;">
                                    <i class="fas fa-users" style="color: #856404;"></i>
                                </div>
                                <div>
                                    <h6 style="font-weight: 600; color: #2c3e50;">Attend Meeting</h6>
                                    <p class="mb-0 text-muted" style="font-size: 13px;">Participate in discussions and vote on applications</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
