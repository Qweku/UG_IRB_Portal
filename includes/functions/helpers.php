<?php

/**
 * Database Functions for UG IRB Portal
 * Contains helper functions for retrieving data from the database
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Get the count of active studies (status = 'open')
 * @return int
 */




function getActiveStudiesCount()
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return 0;
    }

    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM studies WHERE status = 'open'");
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) $result['count'];
    } catch (PDOException $e) {
        error_log("Error fetching active studies count: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get the count of pending reviews (status = 'pending')
 * @return int
 */
function getPendingReviewsCount()
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return 0;
    }

    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM studies WHERE status = 'pending'");
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) $result['count'];
    } catch (PDOException $e) {
        error_log("Error fetching pending reviews count: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get the count of overdue actions (studies where expiration_date < current date and status = 'open')
 * @return int
 */
function getOverdueActionsCount()
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return 0;
    }

    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM studies WHERE status = 'open' AND expiration_date < CURDATE()");
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) $result['count'];
    } catch (PDOException $e) {
        error_log("Error fetching overdue actions count: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get the count of new SAE reports (placeholder - assuming a separate table or field; for now, return 0 or implement based on schema)
 * @return int
 */
function getNewSAEReportsCount()
{
    // Placeholder: Assuming SAE reports are in a separate table. For now, return 0.
    // In a real implementation, query a sae_reports table or similar.
    return 0;
}

/**
 * Get recent activities (last 5 studies with their status and last activity)
 * @return array
 */
function getRecentActivities()
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        $stmt = $conn->prepare("SELECT title, status, updated_at FROM studies ORDER BY updated_at DESC LIMIT 5");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching recent activities: " . $e->getMessage());
        return [];
    }
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
        $stmt = $conn->prepare("SELECT report_name, generated_date, filters_applied, format FROM reports ORDER BY generated_date DESC LIMIT 5");
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
        $stmt = $conn->prepare("SELECT * FROM meetings ORDER BY meeting_date DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching meetings: " . $e->getMessage());
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
function getTasks()
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        // Assuming a tasks table exists
        $stmt = $conn->prepare("SELECT * FROM tasks ORDER BY priority DESC, due_date");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching tasks: " . $e->getMessage());
        return [];
    }
}

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
        $stmt = $conn->prepare("SELECT * FROM drugs_devices ORDER BY type, name");
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
function getStudyReviewers($study_id)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        $stmt = $conn->prepare("SELECT reviewer_name FROM reviewers WHERE study_id = ?");
        $stmt->execute([$study_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error fetching reviewers: " . $e->getMessage());
        return [];
    }
}

/**
 * Get classifications for a specific study
 * @param int $study_id
 * @return array
 */
function getStudyClassifications($study_id)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        $stmt = $conn->prepare("SELECT classification_value FROM classifications WHERE study_id = ?");
        $stmt->execute([$study_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error fetching classifications: " . $e->getMessage());
        return [];
    }
}

/**
 * Get sites for a specific study
 * @param int $study_id
 * @return array
 */
function getStudySites($study_id)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        $stmt = $conn->prepare("SELECT site_name FROM sites WHERE study_id = ?");
        $stmt->execute([$study_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error fetching sites: " . $e->getMessage());
        return [];
    }
}

/**
 * Get department groups for a specific study
 * @param int $study_id
 * @return array
 */
function getStudyDeptGroups($study_id)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        $stmt = $conn->prepare("SELECT department_name FROM department_groups WHERE study_id = ?");
        $stmt->execute([$study_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error fetching department groups: " . $e->getMessage());
        return [];
    }
}

/**
 * Get vulnerable populations for a specific study
 * @param int $study_id
 * @return array
 */
function getStudyVulPops($study_id)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        $stmt = $conn->prepare("SELECT population_type FROM vulnerable_populations WHERE study_id = ?");
        $stmt->execute([$study_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error fetching vulnerable populations: " . $e->getMessage());
        return [];
    }
}

/**
 * Get children data for a specific study
 * @param int $study_id
 * @return array
 */
function getStudyChildren($study_id)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        $stmt = $conn->prepare("SELECT age_range FROM children WHERE study_id = ?");
        $stmt->execute([$study_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error fetching children data: " . $e->getMessage());
        return [];
    }
}

/**
 * Get drugs for a specific study
 * @param int $study_id
 * @return array
 */
function getStudyDrugs($study_id)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        $stmt = $conn->prepare("SELECT drug_name FROM drugs WHERE study_id = ?");
        $stmt->execute([$study_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error fetching drugs: " . $e->getMessage());
        return [];
    }
}

/**
 * Get risks for a specific study
 * @param int $study_id
 * @return array
 */
function getStudyRisks($study_id)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        $stmt = $conn->prepare("SELECT risk_type FROM risks WHERE study_id = ?");
        $stmt->execute([$study_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error fetching risks: " . $e->getMessage());
        return [];
    }
}

/**
 * Get benefits for a specific study
 * @param int $study_id
 * @return array
 */
function getStudyBenefits($study_id)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        $stmt = $conn->prepare("SELECT benefit_type FROM benefits WHERE study_id = ?");
        $stmt->execute([$study_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error fetching benefits: " . $e->getMessage());
        return [];
    }
}

/**
 * Get divisions for a specific study
 * @param int $study_id
 * @return array
 */
function getStudyDivisions($study_id)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        $stmt = $conn->prepare("SELECT division_name FROM divisions WHERE study_id = ?");
        $stmt->execute([$study_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error fetching divisions: " . $e->getMessage());
        return [];
    }
}

/**
 * Get grant projects for a specific study
 * @param int $study_id
 * @return array
 */
function getStudyGrantProjects($study_id)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        $stmt = $conn->prepare("SELECT grant_name FROM grant_projects WHERE study_id = ?");
        $stmt->execute([$study_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error fetching grant projects: " . $e->getMessage());
        return [];
    }
}

/**
 * Get industries for a specific study
 * @param int $study_id
 * @return array
 */
function getStudyIndustries($study_id)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        $stmt = $conn->prepare("SELECT industry_name FROM industries WHERE study_id = ?");
        $stmt->execute([$study_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error fetching industries: " . $e->getMessage());
        return [];
    }
}

/**
 * Get undergrad/grad data for a specific study
 * @param int $study_id
 * @return array
 */
function getStudyUnderGradGrad($study_id)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        $stmt = $conn->prepare("SELECT level FROM undergrad_grad WHERE study_id = ?");
        $stmt->execute([$study_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error fetching undergrad/grad data: " . $e->getMessage());
        return [];
    }
}

/**
 * Get columns for a specific study
 * @param int $study_id
 * @return array
 */
function getStudyColumns($study_id)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        $stmt = $conn->prepare("SELECT column_name, column_value FROM columns WHERE study_id = ?");
        $stmt->execute([$study_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching columns: " . $e->getMessage());
        return [];
    }
}

/**
 * Get admins for a specific study
 * @param int $study_id
 * @return array
 */
function getStudyAdmins($study_id)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        $stmt = $conn->prepare("SELECT admin_name FROM admins WHERE study_id = ?");
        $stmt->execute([$study_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error fetching admins: " . $e->getMessage());
        return [];
    }
}
