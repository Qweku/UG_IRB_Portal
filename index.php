<?php

declare(strict_types=1);

/* ==========================================================
| BOOTSTRAP
========================================================== */
require_once 'config.php';
require_once 'includes/functions/csrf.php';

define('APP_SESSION_NAME', 'ug_irb_session');

session_name(APP_SESSION_NAME);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ==========================================================
| BASE PATHS
========================================================== */
$ADMIN_BASE     = 'admin/pages/';
$APPLICANT_BASE = 'applicant/pages/';
$USER_BASE      = 'user/';
$ERROR_404      = 'admin/404.php';
$ERROR_403      = 'admin/403.php';

/* ==========================================================
| REQUEST PARSING
========================================================== */
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptDir   = dirname($_SERVER['SCRIPT_NAME']);

$path     = trim(str_replace($scriptDir, '', $requestPath), '/');
$segments = array_values(array_filter(explode('/', $path)));

$section = $segments[0] ?? 'dashboard';
$subpage = $segments[1] ?? null;

/* ==========================================================
| ROUTE DEFINITIONS
========================================================== */
$routes = [

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
        '_'       => ['file' => 'index.php', 'roles' => ['applicant']],
        'profile' => ['file' => 'profile.php', 'roles' => ['applicant']],
        'applications' => ['file' => 'applications.php', 'roles' => ['applicant']],
    ],

    'add-protocol' => [
        'student-application' => ['file' => 'student_application.php', 'roles' => ['applicant']],
        'nmimr-application' => ['file' => 'nmimr_application.php', 'roles' => ['applicant']],
        'non-nmimr-application' => ['file' => 'non_nmimr_application.php', 'roles' => ['applicant']],
    ],
];

/* ==========================================================
| AUTH HELPERS
========================================================== */
function requireRole(array $roles): bool
{
    return isset($_SESSION['logged_in'], $_SESSION['role'])
        && in_array($_SESSION['role'], $roles, true);
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
if ($roles && !requireRole($roles)) {
    http_response_code(403);
    require $ERROR_403;
    exit;
}

/* ==========================================================
| FILE RESOLUTION
========================================================== */
$file = $pageConfig['file'];

$resolvedFile = null;

if (file_exists($ADMIN_BASE . $file)) {
    $resolvedFile = $ADMIN_BASE . $file;
} elseif (file_exists($APPLICANT_BASE . $file)) {
    $resolvedFile = $APPLICANT_BASE . $file;
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
