<?php
/**
 * Notification Functions for UG IRB Portal
 * Functions for creating and managing user notifications
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Create notification for assigned reviewer
 * @param int $reviewerId The reviewer user ID
 * @param int $applicationId The application ID
 * @param string $studyTitle The study title
 * @param string $assignedByName Name of person who assigned
 * @return bool
 */
function createReviewerAssignmentNotification($reviewerId, $applicationId, $studyTitle, $assignedByName)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return false;

    try {
        $title = 'New Application Review Assigned';
        $message = "You have been assigned to review the application: \"$studyTitle\"";
        $details = "Assigned by: $assignedByName\nApplication ID: $applicationId\n\nPlease log in to your reviewer dashboard to access the application materials.";
        $actionUrl = '/reviewer/pages/reviews.php?id=' . $applicationId;
        $actionText = 'Review Application';
        $actionType = 'review';

        $stmt = $conn->prepare("
            INSERT INTO notifications 
            (user_id, role, title, message, details, type, action_url, action_text, action_type, priority, created_at)
            VALUES (?, 'reviewer', ?, ?, ?, 'review', ?, ?, ?, 'high', NOW())
        ");
        return $stmt->execute([$reviewerId, $title, $message, $details, $actionUrl, $actionText, $actionType]);
    } catch (PDOException $e) {
        error_log("Error creating reviewer assignment notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Create notification for admin/IRB staff about reviewer assignment
 * @param int $applicationId The application ID
 * @param string $studyTitle The study title
 * @param string $reviewerName Name of assigned reviewer
 * @param int $assignedById User ID of person who made the assignment
 * @return bool
 */
function createAdminAssignmentNotification($applicationId, $studyTitle, $reviewerName, $assignedById)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return false;

    try {
        // Get the assigner's name
        $stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
        $stmt->execute([$assignedById]);
        $assigner = $stmt->fetch(PDO::FETCH_ASSOC);
        $assignerName = $assigner['full_name'] ?? 'System';

        $title = 'Reviewer Assigned to Application';
        $message = "Reviewer \"$reviewerName\" has been assigned to review the application: \"$studyTitle\"";
        $details = "Assigned by: $assignerName\nApplication ID: $applicationId";
        $actionUrl = '/admin/pages/contents/applications_content.php?id=' . $applicationId;
        $actionText = 'View Application';
        $actionType = 'view';

        // Get admin users to notify
        $stmt = $conn->prepare("
            INSERT INTO notifications 
            (user_id, role, title, message, details, type, action_url, action_text, action_type, priority, created_at)
            SELECT id, role, ?, ?, ?, 'review', ?, ?, ?, 'normal', NOW()
            FROM users 
            WHERE role IN ('admin', 'super_admin') AND id != ?
        ");
        return $stmt->execute([$title, $message, $details, $actionUrl, $actionText, $actionType, $assignedById]);
    } catch (PDOException $e) {
        error_log("Error creating admin assignment notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Create notification for IRB chair about new application submission
 * @param int $applicationId The application ID
 * @param string $studyTitle The study title
 * @param string $piName Principal Investigator name
 * @return bool
 */
function createChairNewApplicationNotification($applicationId, $studyTitle, $piName)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return false;

    try {
        $title = 'New Application Awaiting Review';
        $message = "A new IRB application has been submitted and is ready for review: \"$studyTitle\"";
        $details = "Principal Investigator: $piName\nApplication ID: $applicationId\n\nPlease log in to the admin dashboard to assign reviewers.";
        $actionUrl = '/admin/pages/contents/applications_content.php?id=' . $applicationId;
        $actionText = 'Assign Reviewers';
        $actionType = 'assign';

        // Notify IRB chairs (could be admin or super_admin with chair role)
        $stmt = $conn->prepare("
            INSERT INTO notifications 
            (user_id, role, title, message, details, type, action_url, action_text, action_type, priority, created_at)
            SELECT id, role, ?, ?, ?, 'application', ?, ?, ?, 'urgent', NOW()
            FROM users 
            WHERE role IN ('admin', 'super_admin')
        ");
        return $stmt->execute([$title, $message, $details, $actionUrl, $actionText, $actionType]);
    } catch (PDOException $e) {
        error_log("Error creating chair notification: " . $e->getMessage());
        return false;
    }
}

// ============================================================================
// APPLICANT NOTIFICATION FUNCTIONS
// ============================================================================

/**
 * Create notification for applicant about application status change
 * @param int $userId The applicant user ID
 * @param int $applicationId The application ID
 * @param string $studyTitle The study title
 * @param string $status The new status (approved, rejected, revisions_required, etc.)
 * @param string $statusMessage Human-readable message about the status change
 * @return bool
 */
function createApplicationStatusNotification($userId, $applicationId, $studyTitle, $status, $statusMessage)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return false;

    try {
        // Determine priority and title based on status
        $statusConfig = [
            'approved' => ['title' => 'Application Approved', 'priority' => 'high'],
            'rejected' => ['title' => 'Application Not Approved', 'priority' => 'high'],
            'revisions_required' => ['title' => 'Revisions Required', 'priority' => 'high'],
            'under_review' => ['title' => 'Application Under Review', 'priority' => 'normal'],
            'pending' => ['title' => 'Application Pending', 'priority' => 'normal'],
            'withdrawn' => ['title' => 'Application Withdrawn', 'priority' => 'normal']
        ];
        
        $config = $statusConfig[$status] ?? ['title' => 'Status Update', 'priority' => 'normal'];
        $title = $config['title'];
        $priority = $config['priority'];
        
        $message = "Your application status has been updated: \"$studyTitle\"";
        $details = "Status: " . ucwords(str_replace('_', ' ', $status)) . "\n$statusMessage\nApplication ID: $applicationId";
        $actionUrl = '/applicant/pages/my_applications.php?id=' . $applicationId;
        $actionText = 'View Application';
        $actionType = 'view';

        $stmt = $conn->prepare("
            INSERT INTO notifications 
            (user_id, role, title, message, details, type, action_url, action_text, action_type, priority, created_at)
            VALUES (?, 'applicant', ?, ?, ?, 'status', ?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([$userId, $title, $message, $details, $actionUrl, $actionText, $actionType, $priority]);
    } catch (PDOException $e) {
        error_log("Error creating application status notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Create notification for applicant that their application is under review
 * @param int $userId The applicant user ID
 * @param int $applicationId The application ID
 * @param string $studyTitle The study title
 * @return bool
 */
function createApplicationUnderReviewNotification($userId, $applicationId, $studyTitle)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return false;

    try {
        $title = 'Application Under Review';
        $message = "Your application is now being reviewed by the IRB: \"$studyTitle\"";
        $details = "Application ID: $applicationId\n\nThe IRB review committee will evaluate your application. You will be notified of any updates or requests for additional information.";
        $actionUrl = '/applicant/pages/my_applications.php?id=' . $applicationId;
        $actionText = 'View Status';
        $actionType = 'view';

        $stmt = $conn->prepare("
            INSERT INTO notifications 
            (user_id, role, title, message, details, type, action_url, action_text, action_type, priority, created_at)
            VALUES (?, 'applicant', ?, ?, ?, 'review', ?, ?, ?, 'normal', NOW())
        ");
        return $stmt->execute([$userId, $title, $message, $details, $actionUrl, $actionText, $actionType]);
    } catch (PDOException $e) {
        error_log("Error creating under review notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Create notification for applicant when a reviewer submits their review
 * @param int $userId The applicant user ID
 * @param int $applicationId The application ID
 * @param string $studyTitle The study title
 * @return bool
 */
function createReviewSubmittedNotification($userId, $applicationId, $studyTitle)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return false;

    try {
        $title = 'Review Submitted for Your Application';
        $message = "A reviewer has submitted their evaluation for your application: \"$studyTitle\"";
        $details = "Application ID: $applicationId\n\nThe IRB will now compile all reviews and make a decision. You will be notified when the review process is complete.";
        $actionUrl = '/applicant/pages/my_applications.php?id=' . $applicationId;
        $actionText = 'View Application';
        $actionType = 'view';

        $stmt = $conn->prepare("
            INSERT INTO notifications 
            (user_id, role, title, message, details, type, action_url, action_text, action_type, priority, created_at)
            VALUES (?, 'applicant', ?, ?, ?, 'review', ?, ?, ?, 'normal', NOW())
        ");
        return $stmt->execute([$userId, $title, $message, $details, $actionUrl, $actionText, $actionType]);
    } catch (PDOException $e) {
        error_log("Error creating review submitted notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Create notification for applicant that follow-up is required
 * @param int $userId The applicant user ID
 * @param int $applicationId The application ID
 * @param string $studyTitle The study title
 * @param string $followUpDate The date by which follow-up is required
 * @return bool
 */
function createFollowUpRequiredNotification($userId, $applicationId, $studyTitle, $followUpDate)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return false;

    try {
        $title = 'Follow-Up Required for Your Application';
        $message = "The IRB requires additional information for your application: \"$studyTitle\"";
        $details = "Required by: $followUpDate\nApplication ID: $applicationId\n\nPlease log in to your dashboard and provide the requested information to avoid delays in your application review.";
        $actionUrl = '/applicant/pages/follow_up.php?id=' . $applicationId;
        $actionText = 'Submit Follow-Up';
        $actionType = 'action';

        $stmt = $conn->prepare("
            INSERT INTO notifications 
            (user_id, role, title, message, details, type, action_url, action_text, action_type, priority, created_at)
            VALUES (?, 'applicant', ?, ?, ?, 'followup', ?, ?, ?, 'high', NOW())
        ");
        return $stmt->execute([$userId, $title, $message, $details, $actionUrl, $actionText, $actionType]);
    } catch (PDOException $e) {
        error_log("Error creating follow-up required notification: " . $e->getMessage());
        return false;
    }
}

// ============================================================================
// REVIEWER NOTIFICATION FUNCTIONS
// ============================================================================

/**
 * Create deadline reminder notification for reviewer
 * @param int $reviewerId The reviewer user ID
 * @param int $applicationId The application ID
 * @param string $studyTitle The study title
 * @param string $deadlineDate The review deadline date
 * @return bool
 */
function createReviewerDeadlineReminderNotification($reviewerId, $applicationId, $studyTitle, $deadlineDate)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return false;

    try {
        $title = 'Review Deadline Approaching';
        $message = "Your review for \"$studyTitle\" is due soon";
        $details = "Deadline: $deadlineDate\nApplication ID: $applicationId\n\nPlease complete and submit your review before the deadline to ensure timely processing.";
        $actionUrl = '/reviewer/pages/reviews.php?id=' . $applicationId;
        $actionText = 'Complete Review';
        $actionType = 'review';

        $stmt = $conn->prepare("
            INSERT INTO notifications 
            (user_id, role, title, message, details, type, action_url, action_text, action_type, priority, created_at)
            VALUES (?, 'reviewer', ?, ?, ?, 'deadline', ?, ?, ?, 'high', NOW())
        ");
        return $stmt->execute([$reviewerId, $title, $message, $details, $actionUrl, $actionText, $actionType]);
    } catch (PDOException $e) {
        error_log("Error creating deadline reminder notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Create notification for admin when a reviewer completes their review
 * @param int $adminId The admin user ID
 * @param int $applicationId The application ID
 * @param string $studyTitle The study title
 * @param string $reviewerName Name of the reviewer who completed
 * @return bool
 */
function createReviewCompletedNotification($adminId, $applicationId, $studyTitle, $reviewerName)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return false;

    try {
        $title = 'Review Completed';
        $message = "$reviewerName has submitted their review for \"$studyTitle\"";
        $details = "Application ID: $applicationId\n\nThe review is now available for your consideration.";
        $actionUrl = '/admin/pages/contents/applications_content.php?id=' . $applicationId;
        $actionText = 'View Review';
        $actionType = 'review';

        $stmt = $conn->prepare("
            INSERT INTO notifications 
            (user_id, role, title, message, details, type, action_url, action_text, action_type, priority, created_at)
            VALUES (?, 'admin', ?, ?, ?, 'review', ?, ?, ?, 'normal', NOW())
        ");
        return $stmt->execute([$adminId, $title, $message, $details, $actionUrl, $actionText, $actionType]);
    } catch (PDOException $e) {
        error_log("Error creating review completed notification: " . $e->getMessage());
        return false;
    }
}

// ============================================================================
// ADMIN NOTIFICATION FUNCTIONS
// ============================================================================

/**
 * Create notification for admin when a reviewer completes their review
 * @param int $adminId The admin user ID
 * @param int $applicationId The application ID
 * @param string $studyTitle The study title
 * @param string $reviewerName Name of the reviewer who completed
 * @return bool
 */
function createReviewerReviewCompletedNotification($adminId, $applicationId, $studyTitle, $reviewerName)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return false;

    try {
        $title = 'Reviewer Review Completed';
        $message = "$reviewerName has completed their review of \"$studyTitle\"";
        $details = "Application ID: $applicationId\n\nYou can now view the submitted review and take appropriate action.";
        $actionUrl = '/admin/pages/contents/applications_content.php?id=' . $applicationId;
        $actionText = 'View Review';
        $actionType = 'review';

        $stmt = $conn->prepare("
            INSERT INTO notifications 
            (user_id, role, title, message, details, type, action_url, action_text, action_type, priority, created_at)
            VALUES (?, 'admin', ?, ?, ?, 'review', ?, ?, ?, 'normal', NOW())
        ");
        return $stmt->execute([$adminId, $title, $message, $details, $actionUrl, $actionText, $actionType]);
    } catch (PDOException $e) {
        error_log("Error creating reviewer review completed notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Create notification for user when an IRB meeting is scheduled
 * @param int $userId The user ID to notify
 * @param string $role The user's role
 * @param int $meetingId The meeting ID
 * @param string $meetingDate The scheduled meeting date/time
 * @param int $applicationId Optional application ID related to meeting
 * @return bool
 */
function createMeetingScheduledNotification($userId, $role, $meetingId, $meetingDate, $applicationId = null)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return false;

    try {
        $title = 'IRB Meeting Scheduled';
        $message = "An IRB meeting has been scheduled";
        $details = "Meeting Date: $meetingDate\nMeeting ID: $meetingId";
        if ($applicationId) {
            $details .= "\nRelated Application ID: $applicationId";
        }
        $actionUrl = '/admin/pages/contents/meetings.php?id=' . $meetingId;
        $actionText = 'View Meeting Details';
        $actionType = 'meeting';

        $stmt = $conn->prepare("
            INSERT INTO notifications 
            (user_id, role, title, message, details, type, action_url, action_text, action_type, priority, created_at)
            VALUES (?, ?, ?, ?, ?, 'meeting', ?, ?, ?, 'normal', NOW())
        ");
        return $stmt->execute([$userId, $role, $title, $message, $details, $actionUrl, $actionText, $actionType]);
    } catch (PDOException $e) {
        error_log("Error creating meeting scheduled notification: " . $e->getMessage());
        return false;
    }
}

// ============================================================================
// USER MANAGEMENT NOTIFICATION FUNCTIONS
// ============================================================================

/**
 * Create notification for user about account status change
 * @param int $userId The user ID
 * @param string $role The user's role
 * @param bool $isActive Whether the account is now active
 * @param string $reason Reason for status change (optional)
 * @return bool
 */
function createAccountStatusNotification($userId, $role, $isActive, $reason = '')
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return false;

    try {
        $statusText = $isActive ? 'activated' : 'deactivated';
        $priority = $isActive ? 'normal' : 'high';
        
        $title = 'Account Status Update';
        $message = "Your account has been $statusText";
        $details = "Status: " . ucfirst($statusText);
        if (!empty($reason)) {
            $details .= "\nReason: $reason";
        }
        $details .= "\n\nIf you have questions, please contact the IRB office.";
        
        $actionUrl = '/profile.php';
        $actionText = 'View Profile';
        $actionType = 'account';

        $stmt = $conn->prepare("
            INSERT INTO notifications 
            (user_id, role, title, message, details, type, action_url, action_text, action_type, priority, created_at)
            VALUES (?, ?, ?, ?, ?, 'account', ?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([$userId, $role, $title, $message, $details, $actionUrl, $actionText, $actionType, $priority]);
    } catch (PDOException $e) {
        error_log("Error creating account status notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Create notification for new user about their account creation
 * @param int $userId The new user ID
 * @param string $role The user's role
 * @param string $createdByName Name of the admin who created the account
 * @return bool
 */
function createNewUserCreatedNotification($userId, $role, $createdByName)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return false;

    try {
        $title = 'Welcome to UG IRB Portal';
        $message = "Your account has been created in the UG IRB Portal";
        $details = "Account Type: " . ucwords(str_replace('_', ' ', $role)) . "\nCreated by: $createdByName\n\nPlease log in using the credentials provided to access your dashboard.";
        $actionUrl = '/login.php';
        $actionText = 'Log In';
        $actionType = 'account';

        $stmt = $conn->prepare("
            INSERT INTO notifications 
            (user_id, role, title, message, details, type, action_url, action_text, action_type, priority, created_at)
            VALUES (?, ?, ?, ?, ?, 'account', ?, ?, ?, 'normal', NOW())
        ");
        return $stmt->execute([$userId, $role, $title, $message, $details, $actionUrl, $actionText, $actionType]);
    } catch (PDOException $e) {
        error_log("Error creating new user notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Notify all relevant users about a scheduled IRB meeting
 * Sends notifications to admins, super_admins, and reviewers
 * @param PDOConnection $conn Database connection
 * @param int $meetingId The meeting ID
 * @param string $meetingDate The scheduled meeting date
 * @return bool
 */
function notifyMeetingScheduled($conn, $meetingId, $meetingDate)
{
    try {
        $title = 'IRB Meeting Scheduled';
        $message = "A new IRB meeting has been scheduled";
        $details = "Meeting Date: $meetingDate\nMeeting ID: $meetingId\n\nPlease review the meeting details and update your schedules accordingly.";
        $actionUrl = '/admin/pages/contents/agenda_records_content.php?meeting_id=' . $meetingId;
        $actionText = 'View Meeting Details';
        $actionType = 'meeting';

        // Notify admins and super_admins
        $stmt = $conn->prepare("
            INSERT INTO notifications 
            (user_id, role, title, message, details, type, action_url, action_text, action_type, priority, created_at)
            SELECT id, role, ?, ?, ?, 'meeting', ?, ?, ?, 'normal', NOW()
            FROM users 
            WHERE role IN ('admin', 'super_admin')
        ");
        $stmt->execute([$title, $message, $details, $actionUrl, $actionText, $actionType]);

        // Notify reviewers
        $stmt = $conn->prepare("
            INSERT INTO notifications 
            (user_id, role, title, message, details, type, action_url, action_text, action_type, priority, created_at)
            SELECT id, role, ?, ?, ?, 'meeting', ?, ?, ?, 'normal', NOW()
            FROM users 
            WHERE role = 'reviewer'
        ");
        return $stmt->execute([$title, $message, $details, $actionUrl, $actionText, $actionType]);

    } catch (PDOException $e) {
        error_log("Error notifying meeting scheduled: " . $e->getMessage());
        return false;
    }
}

/**
 * Notify applicant about required follow-up
 * @param PDOConnection $conn Database connection
 * @param int $studyId The study/application ID
 * @param string $dueBy The follow-up deadline date
 * @return bool
 */
function notifyFollowUpRequired($conn, $studyId, $dueBy)
{
    try {
        // Get study info and applicant
        $stmt = $conn->prepare("
            SELECT s.title AS study_title, s.applicant_id, u.full_name AS applicant_name
            FROM studies s
            LEFT JOIN users u ON s.applicant_id = u.id
            WHERE s.id = ?
        ");
        $stmt->execute([$studyId]);
        $study = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$study || !$study['applicant_id']) {
            return false;
        }

        $title = 'Follow-Up Required for Your Application';
        $message = "The IRB requires additional information for your application: \"{$study['study_title']}\"";
        $details = "Required by: $dueBy\nStudy ID: $studyId\n\nPlease log in to your dashboard and provide the requested information to avoid delays in your application review.";
        $actionUrl = '/applicant/pages/follow_up.php?id=' . $studyId;
        $actionText = 'Submit Follow-Up';
        $actionType = 'followup';

        $stmt = $conn->prepare("
            INSERT INTO notifications 
            (user_id, role, title, message, details, type, action_url, action_text, action_type, priority, created_at)
            VALUES (?, 'applicant', ?, ?, ?, 'followup', ?, ?, ?, 'high', NOW())
        ");
        return $stmt->execute([
            $study['applicant_id'],
            $title,
            $message,
            $details,
            $actionUrl,
            $actionText,
            $actionType
        ]);

    } catch (PDOException $e) {
        error_log("Error notifying follow-up required: " . $e->getMessage());
        return false;
    }
}
