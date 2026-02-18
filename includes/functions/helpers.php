<?php

/**
 * Database Functions for UG IRB Portal
 * Contains helper functions for retrieving data from the database
 */
// require_once "/config.php";
require_once __DIR__ . '/../config/database.php';

// Use consistent session name across entire application
defined('CSRF_SESSION_NAME') || define('CSRF_SESSION_NAME', 'ug_irb_session');


// Fetch user instition id from session
function get_user_institution_id()
{
    // Session is already started in index.php, just return the value
    return isset($_SESSION['institution_id']) ? $_SESSION['institution_id'] : null;
}

/**
 * Execute a count query and return the count
 * @param string $query
 * @param array $params
 * @return int
 */
function executeCountQuery($query, $params = [])
{
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
function executeListQuery($query, $params = [])
{
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
 * @param array $types Optional PDO::PARAM_* types for each parameter (e.g., [PDO::PARAM_INT, PDO::PARAM_STR])
 * @return array
 */
function executeAssocQuery($query, $params = [], $types = [])
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return [];
    try {
        $stmt = $conn->prepare($query);
        
        // Bind parameters with explicit types if provided
        if (!empty($types)) {
            foreach ($params as $index => $value) {
                $paramIndex = $index + 1; // PDO uses 1-based indexing
                $paramType = $types[$index] ?? PDO::PARAM_STR;
                $stmt->bindValue($paramIndex, $value, $paramType);
            }
            $stmt->execute();
        } else {
            $stmt->execute($params);
        }
        
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
function is_admin_logged_in()
{
    // Session is already started in index.php, just verify it's active
    if (session_status() === PHP_SESSION_ACTIVE) {
        return isset($_SESSION['logged_in']) && isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'super_admin');
    }
    // Fallback: check if session variables exist (for included files before index.php)
    return isset($_SESSION['logged_in']) && isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'super_admin');
}


function is_applicant_logged_in()
{
    // Session is already started in index.php, just verify it's active
    if (session_status() === PHP_SESSION_ACTIVE) {
        return isset($_SESSION['logged_in']) && isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'applicant';
    }
    // Fallback: check if session variables exist
    return isset($_SESSION['logged_in']) && isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'applicant';
}



function getActiveStudiesCount()
{
    $institutionId = get_user_institution_id();
    if ($institutionId) {
        return executeCountQuery("SELECT COUNT(*) as count FROM studies WHERE study_status = 'open' AND institution_id = ?", [$institutionId]);
    }
    return executeCountQuery("SELECT COUNT(*) as count FROM studies WHERE study_status = 'open'");
}

function getContactsCount()
{
    return executeCountQuery("SELECT COUNT(*) as count FROM contacts");
}

function getUsersCount()
{
    return executeCountQuery("SELECT COUNT(*) as count FROM users");
}

/**
 * Get paginated users from the database
 * @param int $limit Number of records per page
 * @param int $offset Starting position
 * @return array
 */
function getUsers($limit = 10, $offset = 0)
{
    $limit = (int)$limit;
    $offset = (int)$offset;
    return executeAssocQuery("SELECT id, full_name, email, role, status, created_at FROM users ORDER BY id ASC LIMIT $limit OFFSET $offset");
}

function getTemplatesCount()
{
    return executeCountQuery("SELECT COUNT(*) as count FROM irb_templates");
}

function getCPATypesCount()
{
    return executeCountQuery("SELECT COUNT(*) as count FROM cpa_types");
}

function getInvestigatorCount()
{
    return executeCountQuery("SELECT COUNT(*) as count FROM investigator");
}

function getIRBMeetingsCount()
{
    return executeCountQuery("SELECT COUNT(*) as count FROM irb_meetings");
}

function getIRBActionsCount()
{
    return executeCountQuery("SELECT COUNT(*) as count FROM irb_action_codes");
}

function getSAETypesCount()
{
    return executeCountQuery("SELECT COUNT(*) as count FROM sae_event_types");
}

function getCPAActionCount()
{
    return executeCountQuery("SELECT COUNT(*) as count FROM cpa_action_codes");
}

function getStudyCodesCount()
{
    return executeCountQuery("SELECT COUNT(*) as count FROM study_status_codes");
}

function getAgendaCategoriesCount()
{
    return executeCountQuery("SELECT COUNT(*) as count FROM agenda_category");
}

function getIRBConditionCount()
{
    return executeCountQuery("SELECT COUNT(*) as count FROM irb_condition");
}

function getAgendaCategoriesList()
{
    return executeListQuery("SELECT category_name FROM agenda_category ORDER BY category_name ASC");
}

function getStudyStatus()
{
    return executeListQuery("SELECT status_name FROM study_status ORDER BY status_name ASC");
}

function getReviewTypes()
{
    return executeListQuery("SELECT name FROM review_types ORDER BY name ASC");
}

function getActiveCodes()
{
    return executeListQuery("SELECT code_name FROM active_codes ORDER BY code_name ASC");
}

/**
 * Get the count of pending reviews (status = 'pending')
 * @return int
 */
function getPendingReviewsCount()
{
    $institutionId = get_user_institution_id();
    if ($institutionId) {
        return executeCountQuery("SELECT COUNT(*) as count FROM studies WHERE study_status = 'pending' AND institution_id = ?", [$institutionId]);
    }
    return executeCountQuery("SELECT COUNT(*) as count FROM studies WHERE study_status = 'pending'");
}

/**
 * Get the count of overdue actions (studies where expiration_date < current date and status = 'open')
 * @return int
 */
function getOverdueActionsCount()
{
    $institutionId = get_user_institution_id();
    if ($institutionId) {
        return executeCountQuery("SELECT COUNT(*) as count FROM studies WHERE study_status = 'open' AND expiration_date < CURDATE() AND institution_id = ?", [$institutionId]);
    }
    return executeCountQuery("SELECT COUNT(*) as count FROM studies WHERE study_status = 'open' AND expiration_date < CURDATE()");
}

/**
 * Get the count of new SAE reports (placeholder - assuming a separate table or field; for now, return 0 or implement based on schema)
 * @return int
 */
function getNewReportsCount()
{
    $institutionId = get_user_institution_id();

    if ($institutionId) {
        return executeCountQuery("SELECT COUNT(*) as count FROM reports WHERE institution_id = ?", [$institutionId]);
    }

    return executeCountQuery("SELECT COUNT(*) as count FROM reports");
}

/**
 * Get recent activities (last 5 studies with their status and last activity)
 * @return array
 */
function getRecentActivities()
{
    return executeAssocQuery("SELECT title, study_status, pi, updated_at FROM studies ORDER BY updated_at DESC LIMIT 5");
}


function getMeetingDates()
{
    return executeListQuery("SELECT meeting_date FROM irb_meetings WHERE meeting_date <= LAST_DAY(DATE_ADD(CURDATE(), INTERVAL 1 MONTH)) ORDER BY meeting_date DESC");
}

function getConditions()
{
    return executeListQuery("SELECT condition_name FROM irb_condition ORDER BY condition_name ASC");
}

function getIRBActions()
{
    return executeListQuery("SELECT irb_action FROM irb_action_codes ORDER BY irb_action ASC");
}

function getLetterTypes()
{
    return executeListQuery("SELECT type_name FROM letter_types ORDER BY type_name ASC");
}

function getActionLetters()
{
    return executeAssocQuery("SELECT letter_name, letter_type, file_path, closing, signatory FROM irb_templates WHERE letter_type = 'IRBAction' ORDER BY letter_name ASC");
}


function getAgendaRecords($limit = null, $offset = null)
{
    $institutionId = get_user_institution_id();
    if ($limit !== null && $offset !== null) {
        if ($institutionId) {
            return executeAssocQuery(
                "SELECT irb_code, meeting_date, agenda_heading FROM agenda_records WHERE institution_id = ? ORDER BY meeting_date DESC LIMIT ? OFFSET ?", 
                [$institutionId, (int)$limit, (int)$offset],
                [PDO::PARAM_STR, PDO::PARAM_INT, PDO::PARAM_INT]
            );
        }
        return executeAssocQuery(
            "SELECT irb_code, meeting_date, agenda_heading FROM agenda_records ORDER BY meeting_date DESC LIMIT ? OFFSET ?", 
            [(int)$limit, (int)$offset],
            [PDO::PARAM_INT, PDO::PARAM_INT]
        );
    }
    if ($institutionId) {
        return executeAssocQuery("SELECT irb_code, meeting_date, agenda_heading FROM agenda_records WHERE institution_id = ? ORDER BY meeting_date DESC", [$institutionId]);
    }
    return executeAssocQuery("SELECT irb_code, meeting_date, agenda_heading FROM agenda_records ORDER BY meeting_date DESC");
}

function getLetterTemplates()
{
    return executeAssocQuery("SELECT * FROM irb_templates ORDER BY letter_type ASC");
}

function getAllInstitutions($limit = null, $offset = null)
{
    if ($limit !== null && $offset !== null) {
        return executeAssocQuery(
            "SELECT * FROM institutions ORDER BY id ASC LIMIT ? OFFSET ?", 
            [(int)$limit, (int)$offset],
            [PDO::PARAM_INT, PDO::PARAM_INT]
        );
    }
    return executeAssocQuery("SELECT * FROM institutions ORDER BY id ASC");
}

/**
 * Get studies with filtering capabilities
 * @param string $status Filter by status (all, open, closed, pending)
 * @param string $review_type Filter by review type (all, full_board, expedited, exempt)
 * @param string $pi_name Filter by PI name (empty for all)
 * @param string $sort_by Sort by (protocol_number, approval_date, title)
 * @return array
 */
function getStudies($status = 'all', $review_type = 'all', $pi_name = '', $sort_by = 'protocol_number', $limit = 0, $offset = 0)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }



    try {
        $institutionId = get_user_institution_id();
        if ($institutionId) {
            $query = "SELECT s.*, sp.name as pi_name FROM studies s LEFT JOIN study_personnel sp ON s.id = sp.study_id AND sp.role = 'PI' WHERE s.institution_id = ?";
            $params = [$institutionId];
        } else {
            $query = "SELECT s.*, sp.name as pi_name FROM studies s LEFT JOIN study_personnel sp ON s.id = sp.study_id AND sp.role = 'PI' WHERE 1=1";
            $params = [];
        }


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

        // Whitelist validation for ORDER BY clause
        $allowed_columns = ['s.protocol_number', 's.approval_date', 's.title'];
        if (!in_array($order_by, $allowed_columns, true)) {
            $order_by = 's.protocol_number'; // Safe fallback
        }
        $query .= " ORDER BY " . $order_by;

        // Add LIMIT and OFFSET for pagination
        if ($limit > 0) {
            $query .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }

        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching studies: " . $e->getMessage());
        return [];
    }
}

/**
 * Get total count of studies with filtering
 * @param string $status Filter by status (all, open, closed, pending)
 * @param string $review_type Filter by review type (all, full_board, expedited, exempt)
 * @param string $pi_name Filter by PI name (empty for all)
 * @return int
 */
function getStudiesCount($status = 'all', $review_type = 'all', $pi_name = '')
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return 0;
    }

    try {
        $institutionId = get_user_institution_id();
        if ($institutionId) {
            $query = "SELECT COUNT(*) as total FROM studies s LEFT JOIN study_personnel sp ON s.id = sp.study_id AND sp.role = 'PI' WHERE s.institution_id = ?";
            $params = [$institutionId];
        } else {
            $query = "SELECT COUNT(*) as total FROM studies s LEFT JOIN study_personnel sp ON s.id = sp.study_id AND sp.role = 'PI' WHERE 1=1";
            $params = [];
        }

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

        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return (int)($result['total'] ?? 0);
    } catch (PDOException $e) {
        error_log("Error counting studies: " . $e->getMessage());
        return 0;
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
        $institutionId = get_user_institution_id();
        if ($institutionId) {
            // Assuming a reports table exists with columns: report_name, generated_date, filters_applied, format
            $stmt = $conn->prepare("SELECT id, report_name, generated_date, filters_applied, doc_format FROM reports WHERE institution_id = ? ORDER BY generated_date DESC LIMIT 5");
            $stmt->execute([$institutionId]);
            return $stmt->fetchAll();
        }
        // Assuming a reports table exists with columns: report_name, generated_date, filters_applied, format
        $stmt = $conn->prepare("SELECT id, report_name, generated_date, filters_applied, doc_format FROM reports ORDER BY generated_date DESC LIMIT 5");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching recent reports: " . $e->getMessage());
        return [];
    }
}


/**
 * Get Contact documents
 * @return array
 */
function getContactDocs($contact_id = null)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        // Assuming a reports table exists with columns: report_name, generated_date, filters_applied, format
        $stmt = $conn->prepare("SELECT id, contact_id, file_name, file_path, file_size, comments, uploaded_at FROM contact_documents WHERE contact_id = :contact_id ORDER BY uploaded_at DESC LIMIT 5");
        $stmt->execute(['contact_id' => $contact_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching contact documents: " . $e->getMessage());
        return [];
    }
}


/**
 * Get continue review studies
 * @param int|null $limit
 * @param int|null $offset
 * @return array
 */
function getContinueReviewStudies($limit = null, $offset = null)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        $institutionId = get_user_institution_id();
        if ($limit !== null && $offset !== null) {
            if ($institutionId) {
                $stmt = $conn->prepare("SELECT * FROM studies WHERE expiration_date <= NOW() AND institution_id = ? ORDER BY expiration_date ASC LIMIT ? OFFSET ?");
                $stmt->bindValue(1, $institutionId, PDO::PARAM_STR);
                $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
                $stmt->bindValue(3, (int)$offset, PDO::PARAM_INT);
                $stmt->execute();
            } else {
                $stmt = $conn->prepare("SELECT * FROM studies WHERE expiration_date <= NOW() ORDER BY expiration_date ASC LIMIT ? OFFSET ?");
                $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
                $stmt->bindValue(2, (int)$offset, PDO::PARAM_INT);
                $stmt->execute();
            }
        } else {
            if ($institutionId) {
                $stmt = $conn->prepare("SELECT * FROM studies WHERE expiration_date <= NOW() AND institution_id = ? ORDER BY expiration_date ASC");
                $stmt->execute([$institutionId]);
            } else {
                $stmt = $conn->prepare("SELECT * FROM studies WHERE expiration_date <= NOW() ORDER BY expiration_date ASC");
                $stmt->execute();
            }
        }
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching continue review studies: " . $e->getMessage());
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
 * @param int|null $limit
 * @param int|null $offset
 * @return array
 */
function getMeetings($limit = null, $offset = null)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        $institutionId = get_user_institution_id();
        if ($institutionId) {
            if ($limit !== null && $offset !== null) {
                $stmt = $conn->prepare("SELECT * FROM agenda_items WHERE institution_id = ? ORDER BY meeting_date DESC LIMIT ? OFFSET ?");
                $stmt->bindValue(1, $institutionId, PDO::PARAM_STR);
                $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
                $stmt->bindValue(3, (int)$offset, PDO::PARAM_INT);
                $stmt->execute();
            } else {
                $stmt = $conn->prepare("SELECT * FROM agenda_items WHERE institution_id = ? ORDER BY meeting_date DESC");
                $stmt->execute([$institutionId]);
            }
            return $stmt->fetchAll();
        }
        if ($limit !== null && $offset !== null) {
            $stmt = $conn->prepare("SELECT * FROM agenda_items ORDER BY meeting_date DESC LIMIT ? OFFSET ?");
            $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(2, (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("SELECT * FROM agenda_items ORDER BY meeting_date DESC");
            $stmt->execute();
        }
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
        $institutionId = get_user_institution_id();
        if ($institutionId) {
            // Assuming a follow_ups table exists
            $stmt = $conn->prepare("SELECT * FROM follow_ups WHERE institution_id = ? ORDER BY due_date");
            $stmt->execute([$institutionId]);
            return $stmt->fetchAll();
        }
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
 * Get sae types
 * @return array
 */
function getSAETypesList()
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        // Assuming a drugs_devices table exists
        $stmt = $conn->prepare("SELECT event_type FROM sae_event_types");
        $stmt->execute();
        // error_log("Fetching SAE Types: " . print_r($stmt->fetchAll(), true));
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error fetching sae types: " . $e->getMessage());
        return [];
    }
}


/**
 * Get sites
 * @return array
 */
function getStudyLocationsList()
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        // Assuming a drugs_devices table exists
        $stmt = $conn->prepare("SELECT site_name FROM sites");
        $stmt->execute();
        // error_log("Fetching SAE Types: " . print_r($stmt->fetchAll(), true));
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error fetching sites: " . $e->getMessage());
        return [];
    }
}


/**
 * Get specialty
 * @return array
 */
function getSpecialties()
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        // Assuming a drugs_devices table exists
        $stmt = $conn->prepare("SELECT specialty_name FROM specialty");
        $stmt->execute();
        // error_log("Fetching SAE Types: " . print_r($stmt->fetchAll(), true));
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error fetching specialty: " . $e->getMessage());
        return [];
    }
}

/**
 * Get Contacts
 * @return array
 */
function getAllContacts()
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return [];
    }

    try {
        // Assuming a contacts table exists
        $stmt = $conn->prepare("SELECT * FROM contacts");
        $stmt->execute();
        $contacts = $stmt->fetchAll();
        error_log("Fetched " . count($contacts) . " contacts from database");
        // error_log("Fetching SAE Types: " . print_r($stmt->fetchAll(), true));
        return $contacts;
    } catch (PDOException $e) {
        error_log("Error fetching contacts: " . $e->getMessage());
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
    return executeListQuery("SELECT reviewer_name FROM reviewers WHERE study_id = ?", [$study_id]);
}

/**
 * Get classifications for a specific study
 * @return array
 */
function getStudyClassifications()
{
    return executeListQuery("SELECT classification_type FROM classifications");
}

/**
 * Get sites for a specific study
 * @return array
 */
function getStudySites()
{
    return executeListQuery("SELECT site_name FROM sites");
}

/**
 * Get department groups for a specific study
 * @return array
 */
function getStudyDeptGroups()
{
    return executeListQuery("SELECT department_name FROM department_groups");
}

/**
 * Get vulnerable populations for a specific study
 * @return array
 */
function getStudyVulPops()
{
    return executeListQuery("SELECT population_type FROM vulnerable_populations");
}

/**
 * Get children data for a specific study
 * @return array
 */
function getStudyChildren()
{
    return executeListQuery("SELECT age_range FROM children");
}

/**
 * Get drugs for a specific study
 * @return array
 */
function getStudyDrugs()
{
    return executeListQuery("SELECT drug_name FROM drugs");
}

/**
 * Get risks for a specific study
 * @return array
 */
function getStudyRisks()
{
    return executeListQuery("SELECT category_name FROM risks_category");
}

/**
 * Get benefits for a specific study
 * @return array
 */
function getStudyBenefits()
{
    return executeListQuery("SELECT benefit_type FROM benefits");
}

/**
 * Get divisions for a specific study
 * @return array
 */
function getStudyDivisions()
{
    return executeListQuery("SELECT division_name FROM divisions");
}

/**
 * Get grant projects for a specific study
 * @return array
 */
function getStudyGrantProjects()
{
    return executeListQuery("SELECT grant_name FROM grant_projects");
}

/**
 * Get industries for a specific study
 * @param int $study_id
 * @return array
 */
function getStudyIndustries($study_id)
{
    return executeListQuery("SELECT industry_name FROM industries WHERE study_id = ?", [$study_id]);
}

/**
 * Get undergrad/grad data for a specific study
 * @param int $study_id
 * @return array
 */
function getStudyUnderGradGrad($study_id)
{
    return executeListQuery("SELECT level FROM undergrad_grad WHERE study_id = ?", [$study_id]);
}

/**
 * Get columns for a specific study
 * @param int $study_id
 * @return array
 */
function getStudyColumns($study_id)
{
    return executeAssocQuery("SELECT column_name, column_value FROM columns WHERE study_id = ?", [$study_id]);
}

/**
 * Get admins for a specific study
 * @param int $study_id
 * @return array
 */

function getStaffTypes()
{
    return executeListQuery("SELECT type_name FROM staff_types ORDER BY type_name ASC");
}

function getSponsors()
{
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
function getReviewTypesList()
{
    return executeListQuery("SELECT type_name FROM review_types ORDER BY type_name ASC");
}

/**
 * Get study statuses list
 * @return array
 */
function getStudyStatusesList()
{
    return executeListQuery("SELECT status_name FROM study_status ORDER BY status_name ASC");
}

/**
 * Get risk categories list
 * @return array
 */
function getRiskCategoriesList()
{
    return executeListQuery("SELECT category_name FROM risks_category ORDER BY category_name ASC");
}
    // return executeListQuery("SELECT admin_name FROM admins WHERE study_id = ?", [$study_id]);
// }

/**
 * Get personnel emails for a study
 * @param int $study_id
 * @return array List of unique emails
 */
function getPersonnelEmails($study_id)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return [];
    try {
        // Get study personnel
        $stmt = $conn->prepare("SELECT name FROM study_personnel WHERE study_id = ?");
        $stmt->execute([$study_id]);
        $personnel = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $personnelNames = [];
        foreach ($personnel as $name) {
            $names = explode(',', $name);
            foreach ($names as $n) {
                $personnelNames[] = trim($n);
            }
        }
        // Remove duplicates
        $personnelNames = array_unique($personnelNames);
        // Get contacts
        $contacts = getAllContacts();
        $emails = [];
        foreach ($personnelNames as $name) {
            // Match by full name: first_name + middle_name + last_name
            foreach ($contacts as $contact) {
                $fullName = trim($contact['last_name'] . ' ' . ($contact['first_name'] ?? ''));
                // error_log("Comparing personnel '$name' with contact full name '$fullName'");
                if (strtolower($fullName) === strtolower($name) && !empty($contact['email'])) {
                    $emails[] = $contact['email'];
                    error_log("Matched personnel '$name' to email: " . $contact['email']);
                    break; // Found match, no need to check further
                }
            }
        }
        return array_unique($emails);
    } catch (PDOException $e) {
        error_log("Error fetching personnel emails: " . $e->getMessage());
        return [];
    }
}

/**
 * Get the next upcoming IRB meeting date
 * Generates all first Fridays for the upcoming 12 months and updates the irb_meetings table.
 * Handles new year transitions by generating dates for the next year when approaching year-end.
 * @return string|null Next meeting date in Y-m-d format or null if none
 */
function getNextMeetingDate()
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return null;

    try {
        $today = new DateTime();

        // Find the next first Friday
        $startYear = (int)$today->format('Y');
        $startMonth = (int)$today->format('m');
        $firstFriday = getFirstFridayOfMonth($startYear, $startMonth);

        if ($firstFriday <= $today) {
            $firstFriday->modify('+1 month');
            $firstFriday = getFirstFridayOfMonth((int)$firstFriday->format('Y'), (int)$firstFriday->format('m'));
        }

        // Check existing future meeting dates
        $stmt = $conn->prepare("SELECT meeting_date FROM irb_meetings WHERE meeting_date > CURDATE()");
        $stmt->execute();
        $existingDates = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $existingCount = count($existingDates);

        if ($existingCount >= 12) {
            // Enough future dates, return the next one
            return $firstFriday->format('Y-m-d');
        }

        // Generate 12 future first Fridays
        $datesToInsert = [];
        $current = clone $firstFriday;
        for ($i = 0; $i < 12; $i++) {
            $datesToInsert[] = $current->format('Y-m-d');
            $current->modify('+1 month');
            $current = getFirstFridayOfMonth((int)$current->format('Y'), (int)$current->format('m'));
        }

        // Filter to only new dates not already existing
        $datesToInsert = array_filter($datesToInsert, function ($date) use ($existingDates) {
            return !in_array($date, $existingDates);
        });

        // Insert them
        foreach ($datesToInsert as $date) {
            $stmt = $conn->prepare("INSERT INTO irb_meetings (meeting_date) VALUES (?)");
            $stmt->execute([$date]);
        }

        // Return the next meeting date
        return $firstFriday->format('Y-m-d');
    } catch (PDOException $e) {
        error_log("Error in getNextMeetingDate: " . $e->getMessage());
        return null;
    }
}

/**
 * Update study personnel fields in studies table based on current personnel
 * @param int $study_id
 * @return bool
 */
function updateStudyPersonnel($study_id)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return false;
    try {
        // Fetch personnel
        $stmt = $conn->prepare("SELECT name, role FROM study_personnel WHERE study_id = ?");
        $stmt->execute([$study_id]);
        $personnel = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $new_pi = '';
        $reviewer_names = '';
        $admin_names = '';
        $col_names = '';

        foreach ($personnel as $p) {
            $role = trim($p['role']);
            $name = trim($p['name']);
            if ($role === 'PI') {
                $new_pi = $name;
            } elseif ($role === 'Reviewer') {
                $reviewer_names .= $name . ', ';
            } elseif ($role === 'Admin') {
                $admin_names .= $name . ', ';
            } elseif ($role === 'Co-PI') {
                $col_names .= $name . ', ';
            }
        }

        // Trim trailing commas
        $reviewer_names = rtrim($reviewer_names, ', ');
        $admin_names = rtrim($admin_names, ', ');
        $col_names = rtrim($col_names, ', ');

        // Update studies
        $stmt = $conn->prepare("UPDATE studies SET pi = ?, reviewers = ?, admins = ?, cols = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$new_pi, $reviewer_names, $admin_names, $col_names, $study_id]);

        return true;
    } catch (PDOException $e) {
        error_log("Error updating study personnel: " . $e->getMessage());
        return false;
    }
}

/**
 * Get the first Friday of a given month and year
 * @param int $year
 * @param int $month
 * @return DateTime
 */
function getFirstFridayOfMonth($year, $month)
{
    $date = new DateTime("$year-$month-01");
    $dayOfWeek = (int)$date->format('N'); // 1=Monday, 7=Sunday
    $daysToAdd = (5 - $dayOfWeek + 7) % 7;
    $date->modify("+$daysToAdd days");
    return $date;
}

// ===========================================
// APPLICANT HELPER FUNCTIONS
// ===========================================

/**
 * Get applicant's applications count
 * @param int $userId
 * @return int
 */
function getApplicantApplicationsCount($userId)
{
    return executeCountQuery(
        "SELECT COUNT(*) as count FROM applications WHERE applicant_id = ?",
        [$userId]
    );
}

/**
 * Get applicant's studies
 * @param int $userId
 * @return array
 */
function getStudentApplicantStudies($userId)
{
    return executeAssocQuery(
        "SELECT a.id, a.study_title, a.created_at, a.status, a.application_type 
         FROM applications a
            LEFT JOIN student_application_details sa ON a.id = sa.application_id
         WHERE applicant_id = ? 
         ORDER BY created_at DESC",
        [$userId]
    );
}
function getNMIMRApplicantStudies($userId)
{
    return executeAssocQuery(
        "SELECT a.id, a.study_title, a.created_at, a.status, a.application_type
         FROM applications a
         LEFT JOIN nmimr_application_details na ON a.id = na.application_id
         WHERE applicant_id = ? 
         ORDER BY created_at DESC",
        [$userId]
    );
}
function getNONNMIMRApplicantStudies($userId)
{
    return executeAssocQuery(
        "SELECT a.id, a.study_title, a.created_at, a.status, a.application_type 
         FROM applications a
         LEFT JOIN non_nmimr_application_details nna ON a.id = nna.application_id
         WHERE applicant_id = ? 
         ORDER BY created_at DESC",
        [$userId]
    );
}

/**
 * Get applicant profile
 * @param int $userId
 * @return array
 */
function getApplicantProfile($userId)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return [];


    try {
        $stmt = $conn->prepare(
            "SELECT * FROM applicant_users WHERE user_id = ?"
        );
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching applicant profile: " . $e->getMessage());
        return [];
    }
}


function getInstitutionById($institutionId)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return null;

    try {
        $stmt = $conn->prepare("SELECT * FROM institutions WHERE id = ?");
        $stmt->execute([$institutionId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching institution by ID: " . $e->getMessage());
        return null;
    }
}

/**
 * Check if user can submit new application (max 3)
 * @param int $userId
 * @return bool
 */
// function canSubmitNewApplication($userId)
// {
//     return getApplicantApplicationsCount($userId) < 3;
// }

/**
 * Get application type display name
 * @param string $type
 * @return string
 */
function getApplicationTypeName($type)
{
    $types = [
        'student' => 'Student',
        'nmimr' => 'NMIMR Researcher',
        'non_nmimr' => 'Non-NMIMR Researcher'
    ];
    return $types[$type] ?? 'Unknown';
}

/**
 * Application status labels
 * @param string $status
 * @return string
 */
function getStatusLabel($status)
{
    $labels = [
        'submitted' => 'Submitted',
        'under_review' => 'Under Review',
        'approved' => 'Approved',
        'rejected' => 'Rejected'
    ];
    return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
}

/**
 * Application status badge colors
 * @param string $status
 * @return string
 */
function getStatusColor($status)
{
    $colors = [
        'submitted' => 'info',
        'under_review' => 'warning',
        'approved' => 'success',
        'rejected' => 'danger'
    ];
    return $colors[$status] ?? 'secondary';
}

/**
 * Get applicant stats summary
 * @param int $userId
 * @return array
 */
function getApplicantStats($userId, $applicationType)
{
    $total =  getApplicantApplicationsCount($userId);
    $studies = $applicationType == "student" ? getStudentApplicantStudies($userId) : ($applicationType == "nmimr" ? getNMIMRApplicantStudies($userId) : getNONNMIMRApplicantStudies($userId));

    $underReview = count(array_filter($studies, fn($s) => ($s['status'] ?? '') === 'under_review'));
    $approved = count(array_filter($studies, fn($s) => ($s['status'] ?? '') === 'approved'));
    $rejected = count(array_filter($studies, fn($s) => ($s['status'] ?? '') === 'rejected'));

    return [
        'total' => $total,
        'under_review' => $underReview,
        'approved' => $approved,
        'rejected' => $rejected,
    ];
}

/**
 * Get draft application
 * @param int $userId
 * @return array|null
 */
function getDraftApplication(int $userId): ?array
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) {
        return null;
    }

    try {
        $stmt = $conn->prepare(
            "SELECT *
             FROM applications
             WHERE applicant_id = :applicant_id
               AND status = 'draft'
             ORDER BY created_at DESC
             LIMIT 1"
        );

        $stmt->execute(['applicant_id' => $userId]);
        $draft = $stmt->fetch(PDO::FETCH_ASSOC);

        error_log(
            $draft
                ? "Draft application found for user {$userId}"
                : "No draft application for user {$userId}"
        );

        return $draft ?: null;
    } catch (PDOException $e) {
        error_log("Draft fetch error: " . $e->getMessage());
        return null;
    }
}


/**
 * Check if user has ongoing application
 * @param int $userId
 * @return bool
 */
function hasOngoingApplication($userId)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return false;

    try {
        $stmt = $conn->prepare(
            "SELECT COUNT(*) as count FROM applications 
             WHERE applicant_id = ? AND status IN ('draft', 'submitted', 'under_review')"
        );
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log("Error checking ongoing application: " . $e->getMessage());
        return false;
    }
}

/**
 * Get application progress percentage
 * @param int $currentStep
 * @param int $totalSteps
 * @return int
 */
function getApplicationProgress($currentStep, $totalSteps = 5)
{
    if ($currentStep <= 0) return 0;
    if ($currentStep > $totalSteps) return 100;
    return (int) (($currentStep / $totalSteps) * 100);
}

// ===========================================
// REVIEWER HELPER FUNCTIONS
// ===========================================

/**
 * Check if reviewer is logged in
 * @return bool
 */
function is_reviewer_logged_in()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return isset($_SESSION['logged_in']) && isset($_SESSION['role']) && $_SESSION['role'] === 'reviewer';
    }
    return isset($_SESSION['logged_in']) && isset($_SESSION['role']) && $_SESSION['role'] === 'reviewer';
}

/**
 * Get total count of pending applications for review
 * @return int
 */
function getPendingApplicationsCount()
{
    return executeCountQuery(
        "SELECT COUNT(*) as count FROM applications WHERE status = 'submitted'",
        []
    );
}

/**
 * Get count of applications under review by current reviewer
 * @param int $reviewerId
 * @return int
 */
function getReviewerActiveReviewsCount($reviewerId)
{
    return executeCountQuery(
        "SELECT COUNT(*) as count FROM application_reviews WHERE reviewer_id = ? AND status IN ('in_progress', 'completed')",
        [$reviewerId]
    );
}

/**
 * Get count of completed reviews by reviewer
 * @param int $reviewerId
 * @return int
 */
function getReviewerCompletedReviewsCount($reviewerId)
{
    return executeCountQuery(
        "SELECT COUNT(*) as count FROM application_reviews WHERE reviewer_id = ? AND status = 'completed'",
        [$reviewerId]
    );
}

/**
 * Get all pending applications for review
 * @param int|null $limit
 * @param int|null $offset
 * @return array
 */
function getAllApplications($limit = null, $offset = null)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return [];

    try {

        $query = "
                   SELECT 
                        a.id,
                        a.applicant_id,
                        a.application_type,
                        a.protocol_number,
                        a.study_title,
                        a.status,
                        a.created_at AS submitted_at,
                        u.full_name AS applicant_name,
                        u.email AS applicant_email
                    FROM applications a
                    JOIN users u ON u.id = a.applicant_id
                    WHERE a.status IN ('under_review','submitted')
                    ORDER BY a.created_at ASC";


        if ($limit !== null && $offset !== null) {
            $query .= " LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($query);
            $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(2, (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare($query);
            $stmt->execute();
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching pending applications: " . $e->getMessage());
        return [];
    }
}

function getPendingApplications($userId, $limit = null, $offset = null)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return [];

    try {

        $query = "
            SELECT
                a.id,
                a.protocol_number,
                a.study_title,
                a.application_type,
                a.status,
                a.created_at AS submitted_at,

                ar.review_status,
                ar.assigned_at,

                u.full_name AS applicant_name,
                u.email AS applicant_email

            FROM application_reviews ar

            INNER JOIN applications a 
                ON a.id = ar.application_id

            INNER JOIN users u 
                ON u.id = a.applicant_id

            WHERE ar.reviewer_id = :reviewer_id
            AND ar.review_status = 'assigned'

            ORDER BY ar.assigned_at DESC
        ";

        if ($limit !== null && $offset !== null) {
            $query .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':reviewer_id', $userId, PDO::PARAM_INT);

        if ($limit !== null && $offset !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching pending applications: " . $e->getMessage());
        return [];
    }
}


/**
 * Get applications assigned to a specific reviewer
 * @param int $reviewerId
 * @return array
 */
function getReviewerAssignments($reviewerId)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return [];

    try {
        $stmt = $conn->prepare(
            "SELECT a.*, 
                    u.full_name as applicant_name,
                    ar.review_status as review_status,
                    ar.created_at as assigned_at,
                    ar.id as review_id
             FROM application_reviews ar
             LEFT JOIN applications a ON ar.application_id = a.id
             LEFT JOIN users u ON a.applicant_id = u.id
             WHERE ar.reviewer_id = ?
             ORDER BY ar.created_at DESC"
        );
        $stmt->execute([$reviewerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching reviewer assignments: " . $e->getMessage());
        return [];
    }
}

/**
 * Get single application details for review
 * @param int $applicationId
 * @return array|null
 */
function getApplicationForReview($applicationId)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return null;

    try {
        $stmt = $conn->prepare(
            "SELECT a.*, 
                    u.full_name as applicant_name,
                    u.email as applicant_email,
                    sa.created_at as submitted_at
             FROM applications a
             LEFT JOIN users u ON a.applicant_id = u.id
             WHERE a.id = ?"
        );
        $stmt->execute([$applicationId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (PDOException $e) {
        error_log("Error fetching application for review: " . $e->getMessage());
        return null;
    }
}

/**
 * Get all comments for an application review
 * @param int $applicationId
 * @return array
 */
function getApplicationComments($applicationId)
{
    return executeAssocQuery(
        "SELECT ac.*, u.full_name as reviewer_name
         FROM application_comments ac
         LEFT JOIN users u ON ac.reviewer_id = u.id
         WHERE ac.application_id = ?
         ORDER BY ac.created_at ASC",
        [$applicationId]
    );
}

/**
 * Get review details for an application
 * @param int $applicationId
 * @param int $reviewerId
 * @return array|null
 */
function getReviewDetails($applicationId, $reviewerId)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return null;

    try {
        $stmt = $conn->prepare("SELECT * FROM application_reviews WHERE application_id = ? AND reviewer_id = ?");
        $stmt->execute([$applicationId, $reviewerId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (PDOException $e) {
        error_log("Error fetching review details: " . $e->getMessage());
        return null;
    }
}

/**
 * Assign application to reviewer
 * @param int $applicationId
 * @param int $reviewerId
 * @return bool
 */
function assignApplicationToReviewer($applicationId, $reviewerId)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return false;

    try {
        $stmt = $conn->prepare(
            "INSERT INTO application_reviews (application_id, reviewer_id, status, created_at) 
             VALUES (?, ?, 'assigned', NOW())"
        );
        return $stmt->execute([$applicationId, $reviewerId]);
    } catch (PDOException $e) {
        error_log("Error assigning application: " . $e->getMessage());
        return false;
    }
}

/**
 * Add comment to application review
 * @param array $data
 * @return bool
 */
function addReviewComment($data)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return false;

    try {
        $stmt = $conn->prepare(
            "INSERT INTO application_comments (application_id, reviewer_id, section, comment, created_at) 
             VALUES (?, ?, ?, ?, NOW())"
        );
        return $stmt->execute([
            $data['application_id'],
            $data['reviewer_id'],
            $data['section'] ?? 'general',
            $data['comment']
        ]);
    } catch (PDOException $e) {
        error_log("Error adding comment: " . $e->getMessage());
        return false;
    }
}

/**
 * Update review status
 * @param int $reviewId
 * @param string $status
 * @return bool
 */
function updateReviewStatus($reviewId, $status)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return false;

    try {
        $stmt = $conn->prepare("UPDATE application_reviews SET status = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$status, $reviewId]);
    } catch (PDOException $e) {
        error_log("Error updating review status: " . $e->getMessage());
        return false;
    }
}

/**
 * Submit final review decision
 * @param array $data
 * @return bool
 */
function submitReviewDecision($data)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return false;

    try {
        $conn->beginTransaction();

        // Update review
        $stmt = $conn->prepare(
            "UPDATE application_reviews 
             SET decision = ?, decision_notes = ?, status = 'completed', updated_at = NOW() 
             WHERE id = ?"
        );
        $stmt->execute([$data['decision'], $data['decision_notes'], $data['review_id']]);

        // Update application status based on decision
        $appStatusMap = [
            'approved' => 'approved',
            'rejected' => 'rejected',
            'changes_requested' => 'revision_requested'
        ];
        $appStatus = $appStatusMap[$data['decision']] ?? 'under_review';

        $stmt = $conn->prepare("UPDATE applications SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$appStatus, $data['application_id']]);

        $conn->commit();
        return true;
    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("Error submitting review decision: " . $e->getMessage());
        return false;
    }
}

/**
 * Get reviewer statistics
 * @param int $reviewerId
 * @return array
 */
function getReviewerStats($reviewerId)
{
    $assignments = getReviewerAssignments($reviewerId);

    $pending = count(array_filter($assignments, fn($a) => ($a['review_status'] ?? '') === 'assigned'));
    $inProgress = count(array_filter($assignments, fn($a) => ($a['review_status'] ?? '') === 'in_progress'));
    $completed = count(array_filter($assignments, fn($a) => ($a['review_status'] ?? '') === 'completed'));

    // Calculate average review time (simplified)
    $avgReviewTime = $completed > 0 ? '2.5 days' : 'N/A';

    return [
        'pending' => $pending,
        'in_progress' => $inProgress,
        'completed' => $completed,
        'total_assigned' => count($assignments),
        'avg_review_time' => $avgReviewTime
    ];
}

/**
 * Get IRB meetings for reviewers
 * @return array
 */
function getReviewerMeetings()
{
    return executeAssocQuery(
        "SELECT * FROM irb_meetings WHERE meeting_date >= CURDATE() ORDER BY meeting_date ASC"
    );
}

/**
 * Get upcoming deadlines for reviewer
 * @param int $reviewerId
 * @return array
 */
function getReviewerDeadlines($reviewerId)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return [];

    try {
        $stmt = $conn->prepare(
            "SELECT a.*, ar.review_deadline, ar.id as review_id
             FROM application_reviews ar
             LEFT JOIN applications a ON ar.application_id = a.id
             WHERE ar.reviewer_id = ? 
               AND ar.review_deadline IS NOT NULL
               AND ar.review_deadline >= CURDATE()
             ORDER BY ar.review_deadline ASC"
        );
        $stmt->execute([$reviewerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching reviewer deadlines: " . $e->getMessage());
        return [];
    }
}

/**
 * Get reviewer profile
 * @param int $userId
 * @return array
 */
function getReviewerProfile($userId)
{
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return [];

    try {
        $stmt = $conn->prepare("SELECT * FROM reviewer_profiles WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        error_log("Error fetching reviewer profile: " . $e->getMessage());
        return [];
    }
}

/**
 * Send email notification to applicant
 * @param int $applicationId
 * @param string $decision
 * @param string $notes
 * @return bool
 */
function sendReviewNotification($applicationId, $decision, $notes = '')
{
    $application = getApplicationForReview($applicationId);
    if (!$application) return false;

    $db = new Database();
    $conn = $db->connect();
    if (!$conn) return false;

    try {
        $decisionLabels = [
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'changes_requested' => 'Changes Requested'
        ];

        $decisionLabel = $decisionLabels[$decision] ?? 'Updated';
        $subject = "IRB Application Decision: " . $decisionLabel;
        $studyTitle = $application['study_title'] ?? 'Your application';

        $message = "Dear " . $application['applicant_name'] . ",\n\n";
        $message .= "Your IRB application '" . $studyTitle . "' has been " . $decisionLabel . ".\n\n";

        if ($notes) {
            $message .= "Reviewer Notes:\n{$notes}\n\n";
        }

        $message .= "Please log in to your dashboard for more details.\n\n";
        $message .= "Best regards,\nNoguchi IRB Office";

        $stmt = $conn->prepare(
            "INSERT INTO notifications (user_id, subject, message, type, created_at) 
             VALUES (?, ?, ?, 'review_decision', NOW())"
        );
        return $stmt->execute([$application['applicant_id'], $subject, $message]);
    } catch (PDOException $e) {
        error_log("Error sending notification: " . $e->getMessage());
        return false;
    }
}
