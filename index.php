<?php

declare(strict_types=1);

// --------------------------------------------------
// Bootstrap
// --------------------------------------------------
require_once 'config.php';

// include 'admin/includes/header.php'; 

$pageBase = 'admin/pages/';
$applicantBase = 'applicant/pages/';
$userBase = 'user/';
$errorPage = 'admin/404.php';
$forbiddenPage = 'admin/403.php';

// --------------------------------------------------
// Get clean path (supports query strings)
// --------------------------------------------------
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = dirname($_SERVER['SCRIPT_NAME']);

$path = trim(str_replace($scriptName, '', $requestUri), '/');
$segments = array_values(array_filter(explode('/', $path)));

// Defaults
$section = $segments[0] ?? 'dashboard';
$subpage = $segments[1] ?? null;

// Set session name based on request path
$session_name = in_array($section, ['dashboard', 'admin']) ? 'admin_session' : 'applicant_session';
session_name($session_name);

// --------------------------------------------------
// Route map (supports sub-routes)
// --------------------------------------------------
$routes = [
    'authenticate' => [
        '_' => ['file' => 'authenticate.php', 'roles' => []]
    ],
    'login' => [
        '_' => ['file' => 'login.php', 'roles' => []]
    ],
    'logout' => [
        '_' => ['file' => 'logout.php', 'roles' => []]
    ],

    'dashboard' => [
        '_'        => ['file' => 'dashboard/index.php', 'roles' => ['admin', 'super_admin']],          // /dashboard
        'studies'  => ['file' => 'dashboard/study_content.php', 'roles' => ['admin', 'super_admin']],  // /dashboard/studies
        'preliminary-agenda'  => ['file' => 'dashboard/preliminary_agenda_content.php', 'roles' => ['admin', 'super_admin']],  // /dashboard/preliminary-agenda
        'continue-review'  => ['file' => 'dashboard/due_continue_review_content.php', 'roles' => ['admin', 'super_admin']],  // /dashboard/continue-review
        'post-irb-meeting'  => ['file' => 'dashboard/post_meeting_content.php', 'roles' => ['admin', 'super_admin']],  // /dashboard/post-irb-meeting
        'agenda-records'  => ['file' => 'dashboard/agenda_records_content.php', 'roles' => ['admin', 'super_admin']],  // /dashboard/agenda-records
        'reports'  => ['file' => 'dashboard/reports_content.php', 'roles' => ['admin', 'super_admin']],  // /dashboard/reports
        'follow-up'  => ['file' => 'dashboard/follow_up_content.php', 'roles' => ['admin', 'super_admin']],  // /dashboard/follow-up
        'administration'  => ['file' => 'dashboard/administration_content.php', 'roles' => ['admin', 'super_admin']],  // /dashboard/administration
        'institutions'  => ['file' => 'dashboard/institutions_content.php', 'roles' => ['super_admin']],  // /dashboard/institutions
    ],

    'studies' => [
        'add-study' => ['file' => 'contents/add_new_study.php', 'roles' => ['admin', 'super_admin']]
    ],

    'agenda' => [
        'prepare-agenda' => ['file' => 'contents/prepare_agenda.php', 'roles' => ['admin', 'super_admin']],
        'minutes' => ['file' => 'contents/minutes_preparation.php', 'roles' => ['admin', 'super_admin']],
    ],

    'contacts' => [
        '_' => ['file' => 'contents/create_contact.php', 'roles' => ['admin', 'super_admin']]
    ],
    'account-information' => [
        '_' => ['file' => 'contents/account_information.php', 'roles' => ['admin', 'super_admin']]
    ],

    'generate-letter' => [
        '_' => ['file' => 'contents/general_letters_content.php', 'roles' => ['admin', 'super_admin']]
    ],

    // APPLICANT ROUTES CAN BE ADDED HERE
    'applicant-dashboard' => [
        '_' => ['file' => 'index.php', 'roles' => ['applicant', 'reviewer']],
        'add-protocol' => ['file' => 'add_new_protocol.php', 'roles' => ['applicant', 'reviewer']]
    ],


];

// --------------------------------------------------
// Resolve route config
// --------------------------------------------------
$pageConfig = null;

if (isset($routes[$section])) {
    if ($subpage && isset($routes[$section][$subpage])) {
        $pageConfig = $routes[$section][$subpage];
    } elseif (!$subpage && isset($routes[$section]['_'])) {
        $pageConfig = $routes[$section]['_'];
    }
}

// --------------------------------------------------
// Role check functions

function isUserAdmin(): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name('admin_session');
        session_start();
    }
    return isset($_SESSION['logged_in']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isUserSuperAdmin(): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name('admin_session');
        session_start();
    }
    return isset($_SESSION['logged_in']) && isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin';
}

function isUserApplicant(): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name('applicant_session');
        session_start();
    }
    return isset($_SESSION['logged_in']) && isset($_SESSION['role']) && ($_SESSION['role'] === 'applicant' || $_SESSION['role'] === 'reviewer');
}

function requireRole(array $allowedRoles): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        if (in_array('applicant', $allowedRoles) || in_array('reviewer', $allowedRoles)) {
            session_name('applicant_session');
        } else {
            session_name('admin_session');
        }
        session_start();
    }
    return isset($_SESSION['logged_in']) && isset($_SESSION['role']) && in_array($_SESSION['role'], $allowedRoles);
}


try {

    include 'admin/includes/header.php';

    if ($pageConfig) {
        $file = $pageConfig['file'];
        $roles = $pageConfig['roles'] ?? [];



        error_log("USER STATUS: " . (isUserAdmin() ? "Admin" : (isUserSuperAdmin() ? "Super Admin" : "Applicant")));
        error_log("Applicant Path: {$applicantBase}{$file}");
        if (!empty($roles) && !requireRole($roles)) {
            http_response_code(403);
            require_once $forbiddenPage;
        }
        elseif (file_exists($pageBase . $file) ) {
            error_log("Admin Path: {$pageBase}{$file}");
            require_once $pageBase . $file;
        } 
        elseif (file_exists($applicantBase . $file)) {
            error_log("Applicant Path: {$applicantBase}{$file}");
            require_once $applicantBase . $file;
        } elseif (file_exists($userBase . $file)) {
            error_log("User Path: {$userBase}{$file}");
            require_once $userBase . $file;
        } else {
            http_response_code(404);
            require_once $errorPage;
        }
    } else {
        http_response_code(404);
        require_once $errorPage;
    }

    include 'admin/includes/footer.php';
} catch (Throwable $e) {
    error_log('[Router Error] ' . $e->getMessage());
    http_response_code(500);
    require_once $errorPage;
}



// include 'admin/includes/footer.php';
