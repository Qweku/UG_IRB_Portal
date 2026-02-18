<?php



declare(strict_types=1);


require_once 'config.php';



/* ==========================================================
 | BOOTSTRAP
 ========================================================== */
require_once 'includes/functions/csrf.php';

// Use consistent session name across entire application
// This must be set BEFORE session_start()
defined('CSRF_SESSION_NAME') || define('CSRF_SESSION_NAME', 'ug_irb_session');
session_name(CSRF_SESSION_NAME);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ==========================================================
 | MAINTENANCE MODE CHECK (moved after session start)
 ========================================================== */
// Check if maintenance mode is enabled and we're NOT on the maintenance page
$maintenance_mode = getenv('MAINTENANCE_MODE') === 'true';
if ($maintenance_mode) {
    $current_uri = $_SERVER['REQUEST_URI'] ?? '';
    $maintenance_page = '/maintenance';
    
    // If we're on the maintenance page, continue normally
    if (strpos($current_uri, $maintenance_page) !== false) {
        // Allow access to maintenance page - continue rendering
    } else {
        // Destroy all sessions to log out users
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = array();
            session_destroy();
        }
        // Redirect to maintenance page
        header('Location: ' . $maintenance_page);
        exit;
    }
}

/* ==========================================================
 | DATABASE CONNECTION (only if not in maintenance mode)
 ========================================================== */
require_once 'includes/config/database.php';
$db = new Database();
$conn = $db->connect();

/* ==========================================================
 | BASE PATHS
 ========================================================== */
$ADMIN_BASE     = 'admin/pages/';
$APPLICANT_BASE = 'applicant/pages/';
$REVIEWER_BASE  = 'reviewer/pages/';
$USER_BASE      = 'user/';
$ERROR_404      = 'admin/404.php';
$ERROR_403      = 'admin/403.php';
$MAINTENANCE_PAGE = 'admin/pages/maintenance.php';

/* ==========================================================
 | REQUEST PARSING
 ========================================================== */
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove trailing slash
$requestPath = rtrim($requestPath, '/');

// Get base directory dynamically
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

if ($basePath && strpos($requestPath, $basePath) === 0) {
    $requestPath = substr($requestPath, strlen($basePath));
}

$requestPath = trim($requestPath, '/');

$segments = array_values(array_filter(explode('/', $requestPath)));

$section = $segments[0] ?? null;
$subpage = $segments[1] ?? null;

// If no route provided (visiting "/"), redirect properly
if ($section === null) {
    if (is_authenticated()) {
        switch ($_SESSION['role']) {
            case 'admin':
            case 'super_admin':
                header('Location: /dashboard');
                break;
            case 'applicant':
                header('Location: /applicant-dashboard');
                break;
            case 'reviewer':
                header('Location: /reviewer-dashboard');
                break;
        }
    } else {
        header('Location: /login');
    }
    exit;
}


/* ==========================================================
 | AUTH PAGE GUARD (PREVENT LOGIN LOOP)
 ========================================================== */
$authPages = ['login', 'register', 'forgot-password'];

if (in_array($section, $authPages, true) && is_authenticated()) {
    switch ($_SESSION['role']) {
        case 'admin':
        case 'super_admin':
            header('Location: /dashboard');
            break;
        case 'applicant':
            header('Location: /applicant-dashboard');
            break;
        case 'reviewer':
            header('Location: /reviewer-dashboard');
            break;
        default:
            header('Location: /dashboard');
    }
    exit;
}

/* ==========================================================
 | ROUTE DEFINITIONS
 ========================================================== */
$routes = [

    /* ----------- MAINTENANCE ROUTE -------------- */
    'maintenance' => [
        '_' => ['file' => $MAINTENANCE_PAGE, 'roles' => [], 'type' => '']
    ],

    /* ---------- AUTH / ACTION ROUTES (NO HEADER) ---------- */
    'login' => [
        '_' => ['file' => 'login.php', 'roles' => [], 'type' => 'page']
    ],
    'register' => [
        '_' => ['file' => 'register.php', 'roles' => [], 'type' => 'page']
    ],
    'logout' => [
        '_' => ['file' => 'logout.php', 'roles' => [], 'type' => 'action']
    ],
    'authenticate' => [
        '_' => ['file' => 'authenticate.php', 'roles' => [], 'type' => 'action']
    ],
    'forgot-password' => [
        '_' => ['file' => 'forgot_password.php', 'roles' => [], 'type' => 'page']
    ],

    /* ---------- DASHBOARD ---------- */
    'dashboard' => [
        '_'          => ['file' => 'dashboard/index.php', 'roles' => ['admin', 'super_admin']],
        'applications'    => ['file' => 'dashboard/applications_content.php', 'roles' => ['admin', 'super_admin']],
        'studies'    => ['file' => 'dashboard/study_content.php', 'roles' => ['admin', 'super_admin']],
        'preliminary-agenda'  => ['file' => 'dashboard/preliminary_agenda_content.php', 'roles' => ['admin', 'super_admin']],  // /dashboard/preliminary-agenda
        'continue-review'  => ['file' => 'dashboard/due_continue_review_content.php', 'roles' => ['admin', 'super_admin']],  // /dashboard/continue-review
        'post-irb-meeting'  => ['file' => 'dashboard/post_meeting_content.php', 'roles' => ['admin', 'super_admin']],  // /dashboard/post-irb-meeting
        'agenda-records'  => ['file' => 'dashboard/agenda_records_content.php', 'roles' => ['admin', 'super_admin']],  // /dashboard/agenda-records
        'reports'  => ['file' => 'dashboard/reports_content.php', 'roles' => ['admin', 'super_admin']],  // /dashboard/reports
        'follow-up'  => ['file' => 'dashboard/follow_up_content.php', 'roles' => ['admin', 'super_admin']],  // /dashboard/follow-up
        'administration'  => ['file' => 'dashboard/administration_content.php', 'roles' => ['admin', 'super_admin']],  // /dashboard/administration
        'institutions'  => ['file' => 'dashboard/institutions_content.php', 'roles' => ['super_admin']],  // /dashboard/institutions
    ],

    /* ---------- STUDIES ---------- */
    'studies' => [
        'add-study' => ['file' => 'contents/add_new_study.php', 'roles' => ['admin', 'super_admin']]
    ],

    /* ---------- CONTACTS ---------- */
    'contacts' => [
        '_' => ['file' => 'contents/create_contact.php', 'roles' => ['admin', 'super_admin']]
    ],

    /* ---------- LETTERS ---------- */
    'generate-letter' => [
        '_' => ['file' => 'contents/general_letters_content.php', 'roles' => ['admin', 'super_admin']]
    ],

    /* ---------- APPLICANT ---------- */
    'applicant-dashboard' => [
        '_'       => ['file' => 'applicant/pages/index.php', 'roles' => ['applicant']],
        'profile' => ['file' => 'applicant/pages/profile.php', 'roles' => ['applicant']],
        'applications' => ['file' => 'applicant/pages/applications.php', 'roles' => ['applicant']],
    ],

    'add-protocol' => [
        'student-application' => ['file' => 'applicant/pages/student_application.php', 'roles' => ['applicant']],
        'nmimr-application' => ['file' => 'applicant/pages/nmimr_application.php', 'roles' => ['applicant']],
        'non-nmimr-application' => ['file' => 'applicant/pages/non_nmimr_application.php', 'roles' => ['applicant']],
    ],

    /* ---------- REVIEWER ---------- */
    'reviewer-dashboard' => [
        '_' => ['file' => 'reviewer/pages/index.php', 'roles' => ['reviewer']],
        'reviews' => ['file' => 'reviewer/pages/reviews.php', 'roles' => ['reviewer']],
        'review' => ['file' => 'reviewer/pages/review_detail.php', 'roles' => ['reviewer']],
        'meetings' => ['file' => 'reviewer/pages/meetings.php', 'roles' => ['reviewer']],
        'workload' => ['file' => 'reviewer/pages/workload.php', 'roles' => ['reviewer']],
        'profile' => ['file' => 'reviewer/pages/profile.php', 'roles' => ['reviewer']],
    ],
];




/* ==========================================================
 | AUTH HELPERS
 ========================================================== */

/**
 * Validate session token against database
 * @param PDO $conn Database connection
 * @return bool
 */
function validate_session_token($conn): bool {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_token'])) {
        return false;
    }
    
    try {
        $stmt = $conn->prepare("SELECT session_token, session_expires_at FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if token matches and hasn't expired
        if (!$user || empty($user['session_token']) || $user['session_token'] !== $_SESSION['session_token']) {
            return false;
        }
        
        if ($user['session_expires_at'] && strtotime($user['session_expires_at']) < time()) {
            return false;
        }
        
        // Update last_activity periodically (every 5 minutes)
        if (time() - ($_SESSION['last_activity'] ?? 0) > 300) {
            $stmt = $conn->prepare("UPDATE users SET last_activity = NOW() WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $_SESSION['last_activity'] = time();
        }
        
        return true;
    } catch (Exception $e) {
        error_log('Session validation error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Check if user is authenticated (returns boolean without redirect)
 * @return bool
 */
function is_authenticated(): bool
{
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        return false;
    }
    
    // Validate session token against database
    global $conn;
    if ($conn) {
        return validate_session_token($conn);
    }
    
    return true;
}

/**
 * Check if user is authenticated, redirect to login if not
 * @return bool
 */
function require_login(): bool
{
    error_log('require_login called - is_authenticated: ' . (is_authenticated() ? 'true' : 'false'));  // DEBUG
    if (!is_authenticated()) {
        error_log('User not authenticated, redirecting to login');
        header('Location: /login');
        exit;
    }
    return true;
}

/**
 * Require specific role(s) for access
 * @param array $roles Required roles
 * @return void
 */
function requireRole(array $roles): void
{
    if (!is_authenticated()) {
        header('Location: /login');
        exit;
    }

    if (!in_array($_SESSION['role'], $roles, true)) {
        http_response_code(403);
        require 'admin/403.php';
        exit;
    }
}


/* ==========================================================
 | ROUTE RESOLUTION
 ========================================================== */
$pageConfig = null;

if (isset($routes[$section])) {
    if ($subpage && isset($routes[$section][$subpage])) {
        $pageConfig = $routes[$section][$subpage];
    } elseif (isset($routes[$section]['_'])) {
        $pageConfig = $routes[$section]['_'];
    }
}

if (!$pageConfig) {
    http_response_code(404);
    require $ERROR_404;
    exit;
}

/* ==========================================================
 | ROLE CHECK
 ========================================================== */
$roles = $pageConfig['roles'] ?? [];

if ($roles) {
    if (!is_authenticated()) {
        // User not logged in at all, redirect to login
        header('Location: /login');
        exit;
    }

    if (!in_array($_SESSION['role'], $roles, true)) {
        // User logged in but wrong role, show 403
        http_response_code(403);
        require $ERROR_403;
        exit;
    }
}

/* ==========================================================
 | FILE RESOLUTION
 ========================================================== */
$file = $pageConfig['file'];

$resolvedFile = null;

// Check if the file path already exists directly (for routes with full paths like 'reviewer/pages/index.php')
if (file_exists($file)) {
    $resolvedFile = $file;
} elseif (file_exists($ADMIN_BASE . $file)) {
    $resolvedFile = $ADMIN_BASE . $file;
} elseif (file_exists($APPLICANT_BASE . $file)) {
    $resolvedFile = $APPLICANT_BASE . $file;
} elseif (file_exists($REVIEWER_BASE . $file)) {
    $resolvedFile = $REVIEWER_BASE . $file;
} elseif (file_exists($USER_BASE . $file)) {
    $resolvedFile = $USER_BASE . $file;
}

if (!$resolvedFile) {
    http_response_code(404);
    require $ERROR_404;
    exit;
}

/* ==========================================================
 | RENDER (SAFE HEADER HANDLING)
 ========================================================== */
$type = $pageConfig['type'] ?? 'page';

if ($type === 'page') {
    require 'admin/includes/header.php';
}

require $resolvedFile;

if ($type === 'page') {
    require 'admin/includes/footer.php';
}
