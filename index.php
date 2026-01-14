<?php

declare(strict_types=1);

// --------------------------------------------------
// Bootstrap
// --------------------------------------------------
require_once 'config.php';

// include 'admin/includes/header.php'; 

$pageBase = 'admin/pages/';
$errorPage = 'admin/404.php';

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

// --------------------------------------------------
// Route map (supports sub-routes)
// --------------------------------------------------
$routes = [
    'authenticate' => [
        '_' => 'user/authenticate.php'
    ],
    'login' => [
        '_' => 'user/login.php'
    ],
    'logout' => [
        '_' => 'user/logout.php'
    ],

    'dashboard' => [
        '_'        => 'dashboard/index.php',          // /dashboard
        'studies'  => 'dashboard/study_content.php',  // /dashboard/studies
        'preliminary-agenda'  => 'dashboard/preliminary_agenda_content.php',  // /dashboard/reports
        'continue-review'  => 'dashboard/due_continue_review_content.php',  // /dashboard/reports
        'post-irb-meeting'  => 'dashboard/post_meeting_content.php',  // /dashboard/reports
        'agenda-records'  => 'dashboard/agenda_records_content.php',  // /dashboard/reports
        'reports'  => 'dashboard/reports_content.php',  // /dashboard/reports
        'follow-up'  => 'dashboard/follow_up_content.php',  // /dashboard/reports
        'administration'  => 'dashboard/administration_content.php',  // /dashboard/reports
        'general-letters'  => 'dashboard/general_letters_content.php',  // /dashboard/reports
    ],

    'studies' => [
        'add-study' => 'contents/add_new_study.php'
    ],

    'agenda' => [
        'prepare-agenda' => 'contents/minutes_preparation.php',
        'minutes' => 'contents/minutes_preparation.php',
    ],

    'contacts' => [
        '_' => 'contents/create_contact.php'
    ],
    'account-info' => [
        '_' => 'contents/authenticate.php'
    ],

    'generate-letter' => [
        '_' => 'contents/general_letters_content.php'
    ],

  
];

// --------------------------------------------------
// Resolve file
// --------------------------------------------------
$pageFile = null;

if (isset($routes[$section])) {
    if ($subpage && isset($routes[$section][$subpage])) {
        $pageFile = $routes[$section][$subpage];
    } elseif (!$subpage && isset($routes[$section]['_'])) {
        $pageFile = $routes[$section]['_'];
    }
}

// // Default route
// $route = $path ?: 'dashboard';

// // --------------------------------------------------
// // Route map
// // --------------------------------------------------
// $routes = [
//     'authenticate' => 'user/authenticate.php',
//     'login' => 'user/login.php',
//     'logout' => 'user/logout.php',
//     'add-study' => 'contents/add_new_study.php',
//     'prepare-agenda' => 'contents/minutes_preparation.php',
//     'minutes' => 'contents/minutes_preparation.php',
//     'register' => 'contents/register.php',
//     'contacts' => 'contents/create_contact.php',
//     'account-information' => 'contents/authenticate.php',
//     'dashboard'       => 'dashboard/index.php',
//     'studies'         => 'contents/studies.php',
//     'generate-letter' => 'contents/general_letters_content.php',
// ];

// // --------------------------------------------------
// // Resolve route
// // --------------------------------------------------
// $pageFile = $routes[$route] ?? null;



try {

    include 'admin/includes/header.php';

    error_log("Path: {$pageBase}{$pageFile}");

    if ($pageFile && file_exists($pageBase . $pageFile)) {
        require_once $pageBase . $pageFile;
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
