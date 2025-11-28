<?php

/**
 * Database Functions for UG IRB Portal
 * Contains helper functions for retrieving data from the database
 */
// require_once "/config.php";
require_once __DIR__ . '/../config/database.php';

/**
 * Execute a count query and return the count
 * @param string $query
 * @param array $params
 * @return int
 */
function executeCountQuery($query, $params = []) {
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return 0;
    try {
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return (int) $result['count'];
    } catch (PDOException $e) {
        error_log("Error executing count query: " . $e->getMessage());
        return 0;
    }
}

/**
 * Execute a list query and return array of values
 * @param string $query
 * @param array $params
 * @return array
 */
function executeListQuery($query, $params = []) {
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return [];
    try {
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error executing list query: " . $e->getMessage());
        return [];
    }
}

/**
 * Execute a query and return associative array
 * @param string $query
 * @param array $params
 * @return array
 */
function executeAssocQuery($query, $params = []) {
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return [];
    try {
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error executing assoc query: " . $e->getMessage());
        return [];
    }
}

/**
 * Check if admin is logged in
 * @return bool
 */
function is_admin_logged_in() {
    if (session_status() === PHP_SESSION_NONE) {
        session_name('admin_session');
        session_start();
    }
    return isset($_SESSION['logged_in']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}



function getActiveStudiesCount() {
    return executeCountQuery("SELECT COUNT(*) as count FROM studies WHERE study_status = 'open'");
}

function getCPATypesCount() {
    return executeCountQuery("SELECT COUNT(*) as count FROM cpa_types");
}

function getInvestigatorCount() {
    return executeCountQuery("SELECT COUNT(*) as count FROM investigator");
}

function getIRBMeetingsCount() {
    return executeCountQuery("SELECT COUNT(*) as count FROM irb_meetings");
}

function getIRBActionsCount() {
    return executeCountQuery("SELECT COUNT(*) as count FROM irb_action_codes");
}

function getSAETypesCount() {
    return executeCountQuery("SELECT COUNT(*) as count FROM sae_event_types");
}

function getCPAActionCount() {
    return executeCountQuery("SELECT COUNT(*) as count FROM cpa_action_codes");
}

function getStudyCodesCount() {
    return executeCountQuery("SELECT COUNT(*) as count FROM study_status_codes");
}

function getAgendaCategoriesCount() {
    return executeCountQuery("SELECT COUNT(*) as count FROM agenda_category");
}

function getIRBConditionCount() {
    return executeCountQuery("SELECT COUNT(*) as count FROM irb_condition");
}

function getAgendaCategoriesList() {
    return executeListQuery("SELECT category_name FROM agenda_category ORDER BY category_name ASC");
}

function getStudyStatus() {
    return executeListQuery("SELECT status_name FROM study_status ORDER BY status_name ASC");
}

function getReviewTypes() {
    return executeListQuery("SELECT name FROM review_types ORDER BY name ASC");
}

function getActiveCodes() {
    return executeListQuery("SELECT code_name FROM active_codes ORDER BY code_name ASC");
}

/**
 * Get the count of pending reviews (status = 'pending')
 * @return int
 */
function getPendingReviewsCount() {
    return executeCountQuery("SELECT COUNT(*) as count FROM studies WHERE study_status = 'pending'");
}

/**
 * Get the count of overdue actions (studies where expiration_date < current date and status = 'open')
 * @return int
 */
function getOverdueActionsCount() {
    return executeCountQuery("SELECT COUNT(*) as count FROM studies WHERE study_status = 'open' AND expiration_date < CURDATE()");
}

/**
 * Get the count of new SAE reports (placeholder - assuming a separate table or field; for now, return 0 or implement based on schema)
 * @return int
 */
function getNewSAEReportsCount() {
    // Placeholder: Assuming SAE reports are in a separate table. For now, return 0.
    // In a real implementation, query a sae_reports table or similar.
    return 0;
}

/**
 * Get recent activities (last 5 studies with their status and last activity)
 * @return array
 */
function getRecentActivities() {
    return executeAssocQuery("SELECT title, study_status, pi, updated_at FROM studies ORDER BY updated_at DESC LIMIT 5");
}


function getMeetingDates() {
    return executeListQuery("SELECT meeting_date FROM irb_meetings ORDER BY meeting_date DESC");
}

function getConditions() {
    return executeListQuery("SELECT condition_name FROM irb_condition ORDER BY condition_name ASC");
}

function getAgendaRecords() {
    return executeAssocQuery("SELECT irb_code, meeting_date, agenda_heading FROM agenda_records ORDER BY meeting_date DESC");
}

/**
 * Get studies with filtering capabilities
 * @param string $status Filter by status (all, open, closed, pending)
 * @param string $review_type Filter by review type (all, full_board, expedited, exempt)
 * @param string $pi_name Filter by PI name (empty for all)
 * @param string $sort_by Sort by (protocol_number, approval_date, title)
 * @return array
 */
function getStudies($status = 'all', $review_type = 'all', $pi_name = '', $sort_by = 'protocol_number')
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        $query = "SELECT s.*, sp.name as pi_name FROM studies s LEFT JOIN study_personnel sp ON s.id = sp.study_id AND sp.role = 'PI' WHERE 1=1";
        $params = [];

        if ($status !== 'all') {
            $query .= " AND s.status = ?";
            $params[] = $status;
        }

        if ($review_type !== 'all') {
            $query .= " AND s.review_type = ?";
            $params[] = $review_type;
        }

        if (!empty($pi_name)) {
            $query .= " AND sp.name LIKE ?";
            $params[] = '%' . $pi_name . '%';
        }

        $order_by = 's.protocol_number';
        switch ($sort_by) {
            case 'approval_date':
                $order_by = 's.approval_date';
                break;
            case 'title':
                $order_by = 's.title';
                break;
        }
        $query .= " ORDER BY $order_by";

        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching studies: " . $e->getMessage());
        return [];
    }
}

/**
 * Get distinct PI names for filter dropdown
 * @return array
 */
function getDistinctPINames()
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        // Assuming PI names are in study_personnel with role='PI'
        $stmt = $conn->prepare("SELECT DISTINCT name FROM study_personnel WHERE role = 'PI' ORDER BY name");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $results;
    } catch (PDOException $e) {
        error_log("Error fetching PI names: " . $e->getMessage());
        return [];
    }
}

/**
 * Get recent reports
 * @return array
 */
function getRecentReports()
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        // Assuming a reports table exists with columns: report_name, generated_date, filters_applied, format
        $stmt = $conn->prepare("SELECT report_name, generated_date, filters_applied, doc_format FROM reports ORDER BY generated_date DESC LIMIT 5");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching recent reports: " . $e->getMessage());
        return [];
    }
}

/**
 * Get administration stats
 * @return array
 */
function getAdministrationStats()
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        // Get active users count
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE 1");
        $stmt->execute();
        $activeUsers = $stmt->fetch()['count'];

        // Get system modules count (placeholder)
        $systemModules = 8; // Static for now

        // Get uptime (placeholder)
        $uptime = '99.9%'; // Static

        // Get storage used (placeholder)
        $storageUsed = '2.1GB'; // Static

        return [
            'active_users' => $activeUsers,
            'system_modules' => $systemModules,
            'uptime' => $uptime,
            'storage_used' => $storageUsed
        ];
    } catch (PDOException $e) {
        error_log("Error fetching administration stats: " . $e->getMessage());
        return [];
    }
}

/**
 * Get recent administrative activity
 * @return array
 */
function getRecentAdminActivity()
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        // Assuming an admin_activity table exists
        $stmt = $conn->prepare("SELECT action, user_name, timestamp, details FROM admin_activity ORDER BY timestamp DESC LIMIT 10");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching recent admin activity: " . $e->getMessage());
        return [];
    }
}

/**
 * Get education courses
 * @return array
 */
function getEducationCourses()
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        // Assuming an education_courses table exists
        $stmt = $conn->prepare("SELECT * FROM education_courses ORDER BY category, title");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching education courses: " . $e->getMessage());
        return [];
    }
}

/**
 * Get meetings
 * @return array
 */
function getMeetings()
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        // Assuming a meetings table exists
        $stmt = $conn->prepare("SELECT * FROM agenda_items ORDER BY meeting_date DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching agenda_items: " . $e->getMessage());
        return [];
    }
}

/**
 * Get follow-ups
 * @return array
 */
function getFollowUps()
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        // Assuming a follow_ups table exists
        $stmt = $conn->prepare("SELECT * FROM follow_ups ORDER BY due_date");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching follow-ups: " . $e->getMessage());
        return [];
    }
}

/**
 * Get tasks
 * @return array
 */
// function getTasks()
// {
//     $db = new Database();
//     $conn = $db->connect();
//     if (!$conn) {
//         return [];
//     }

//     try {
//         // Assuming a tasks table exists
//         $stmt = $conn->prepare("SELECT * FROM tasks ORDER BY priority DESC, due_date");
//         $stmt->execute();
//         return $stmt->fetchAll();
//     } catch (PDOException $e) {
//         error_log("Error fetching tasks: " . $e->getMessage());
//         return [];
//     }
// }

/**
 * Get drugs/devices
 * @return array
 */
function getDrugsDevices()
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        // Assuming a drugs_devices table exists
        $stmt = $conn->prepare("SELECT * FROM device_types ORDER BY type, device_name");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching drugs/devices: " . $e->getMessage());
        return [];
    }
}

/**
 * Get correspondence/letters
 * @return array
 */
function getCorrespondence()
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        // Assuming a correspondence table exists
        $stmt = $conn->prepare("SELECT * FROM correspondence ORDER BY date_sent DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching correspondence: " . $e->getMessage());
        return [];
    }
}
/**
 * Get reviewers for a specific study
 * @param int $study_id
 * @return array
 */
function getStudyReviewers($study_id) {
    return executeListQuery("SELECT reviewer_name FROM reviewers WHERE study_id = ?", [$study_id]);
}

/**
 * Get classifications for a specific study
 * @return array
 */
function getStudyClassifications() {
    return executeListQuery("SELECT classification_type FROM classifications");
}

/**
 * Get sites for a specific study
 * @return array
 */
function getStudySites() {
    return executeListQuery("SELECT site_name FROM sites");
}

/**
 * Get department groups for a specific study
 * @return array
 */
function getStudyDeptGroups() {
    return executeListQuery("SELECT department_name FROM department_groups");
}

/**
 * Get vulnerable populations for a specific study
 * @return array
 */
function getStudyVulPops() {
    return executeListQuery("SELECT population_type FROM vulnerable_populations");
}

/**
 * Get children data for a specific study
 * @return array
 */
function getStudyChildren() {
    return executeListQuery("SELECT age_range FROM children");
}

/**
 * Get drugs for a specific study
 * @return array
 */
function getStudyDrugs() {
    return executeListQuery("SELECT drug_name FROM drugs");
}

/**
 * Get risks for a specific study
 * @return array
 */
function getStudyRisks() {
    return executeListQuery("SELECT category_name FROM risks_category");
}

/**
 * Get benefits for a specific study
 * @return array
 */
function getStudyBenefits() {
    return executeListQuery("SELECT benefit_type FROM benefits");
}

/**
 * Get divisions for a specific study
 * @return array
 */
function getStudyDivisions() {
    return executeListQuery("SELECT division_name FROM divisions");
}

/**
 * Get grant projects for a specific study
 * @return array
 */
function getStudyGrantProjects() {
    return executeListQuery("SELECT grant_name FROM grant_projects");
}

/**
 * Get industries for a specific study
 * @param int $study_id
 * @return array
 */
function getStudyIndustries($study_id) {
    return executeListQuery("SELECT industry_name FROM industries WHERE study_id = ?", [$study_id]);
}

/**
 * Get undergrad/grad data for a specific study
 * @param int $study_id
 * @return array
 */
function getStudyUnderGradGrad($study_id) {
    return executeListQuery("SELECT level FROM undergrad_grad WHERE study_id = ?", [$study_id]);
}

/**
 * Get columns for a specific study
 * @param int $study_id
 * @return array
 */
function getStudyColumns($study_id) {
    return executeAssocQuery("SELECT column_name, column_value FROM columns WHERE study_id = ?", [$study_id]);
}

/**
 * Get admins for a specific study
 * @param int $study_id
 * @return array
 */

function getStaffTypes() {
    return executeListQuery("SELECT type_name FROM staff_types ORDER BY type_name ASC");
}

function getSponsors() {
    return executeListQuery("SELECT sponsor_name FROM sponsors ORDER BY sponsor_name ASC");
}
// function getStudyAdmins($study_id) {
/**
 * Get staff types list
 * @return array
 */


/**
 * Get sponsors list
 * @return array
 */


/**
 * Get review types list
 * @return array
 */
function getReviewTypesList() {
    return executeListQuery("SELECT type_name FROM review_types ORDER BY type_name ASC");
}

/**
 * Get study statuses list
 * @return array
 */
function getStudyStatusesList() {
    return executeListQuery("SELECT status_name FROM study_status ORDER BY status_name ASC");
}

/**
 * Get risk categories list
 * @return array
 */
function getRiskCategoriesList() {
    return executeListQuery("SELECT category_name FROM risks_category ORDER BY category_name ASC");
}
    // return executeListQuery("SELECT admin_name FROM admins WHERE study_id = ?", [$study_id]);
// }

/**
 * Get the next upcoming IRB meeting date
 * @return string|null Next meeting date in Y-m-d format or null if none
 */
function getNextMeetingDate() {
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return null;

    try {
        $stmt = $conn->prepare("SELECT meeting_date FROM irb_meetings ORDER BY meeting_date ASC");
        $stmt->execute();
        $irbMeetings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $today = new DateTime();

        $nextMeeting = null;

        foreach ($irbMeetings as $meeting) {
            $date = new DateTime($meeting['meeting_date']);

            // If date is in the future
            if ($date > $today) {
                // If not set yet or this date is earlier than the currently stored one
                if ($nextMeeting === null || $date < $nextMeeting) {
                    $nextMeeting = $date;
                }
            }
        }

        return $nextMeeting ? $nextMeeting->format('Y-m-d') : null;
    } catch (PDOException $e) {
        error_log("Error fetching next meeting date: " . $e->getMessage());
        return null;
    }
}
