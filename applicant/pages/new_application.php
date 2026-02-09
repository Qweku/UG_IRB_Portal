<?php

// Check if applicant is logged in
if (!is_applicant_logged_in()) {
    header('Location: /login');
    exit;
}

$userId = $_SESSION['user_id'] ?? 0;

// Check if user can submit new application (max 3)
if (!canSubmitNewApplication($userId)) {
    header('Location: /applicant-dashboard?error=max_applications');
    exit;
}

// Get application type from URL
$type = $_GET['type'] ?? '';

// Redirect to appropriate form based on type
switch ($type) {
    case 'student':
        // Redirect to student form (existing add_new_protocol.php)
        header('Location: /applicant-dashboard/add-protocol?type=student');
        exit;
    
    case 'nmimr':
        // Redirect to NMIMR researcher form (to be created)
        header('Location: /applicant-dashboard/add-protocol?type=nmimr');
        exit;
    
    case 'non_nmimr':
        // Redirect to Non-NMIMR researcher form (to be created)
        header('Location: /applicant-dashboard/add-protocol?type=non_nmimr');
        exit;
    
    default:
        // No type specified - redirect to dashboard
        header('Location: /applicant-dashboard');
        exit;
}
