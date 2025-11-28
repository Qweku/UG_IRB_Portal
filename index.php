<?php


// Include configuration
require_once 'config.php';

include 'admin/includes/header.php'; 
// Get the requested path
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];

// Remove query string and script name to get clean path
$path = str_replace(dirname($script_name), '', $request_uri);
$path = parse_url($path, PHP_URL_PATH);
$path = trim($path, '/');

// Split path into segments
$segments = array_filter(explode('/', $path));

// Route the request
try {
    if (empty($segments)) {
        // Home page
        require_once 'admin/pages/dashboard.php';
    } elseif ($segments[0] === 'admin') {
        // Admin routes
        handleAdminRoutes($segments);
    } elseif ($segments[0] === 'api') {
        // API routes
        handleApiRoutes($segments);
    } else {
        // Frontend routes
        handleFrontendRoutes($segments);
    }
} catch (Exception $e) {
    // Handle errors gracefully
    error_log("Routing error: " . $e->getMessage());
    require_once 'admin/404.php';
}

/**
 * Handle admin routes
 */
function handleAdminRoutes($segments) {
    $admin_path = 'admin/';

    // Remove 'admin' from segments
    array_shift($segments);

    if (empty($segments)) {
        // Admin dashboard
        require_once $admin_path . 'pages/dashboard.php';
    } elseif ($segments[0] === 'login') {
        require_once $admin_path . 'login.php';
    } elseif ($segments[0] === 'dashboard') {
        require_once $admin_path . 'pages/dashboard.php';
    } elseif ($segments[0] === 'products') {
        require_once $admin_path . 'pages/products/view-products.php';
    } elseif ($segments[0] === 'categories') {
        require_once $admin_path . 'pages/products/categories.php';
    } elseif ($segments[0] === 'orders') {
        require_once $admin_path . 'pages/orders/view-orders.php';
    } else {
        // 404 for admin
        require_once 'admin/404.php';
    }
}

/**
 * Handle API routes
 */
function handleApiRoutes($segments) {
    $api_path = 'frontend/includes/api/';

    // Remove 'api' from segments
    array_shift($segments);

    if (empty($segments)) {
        // API root - could show API documentation
        header('Content-Type: application/json');
        echo json_encode(['error' => 'API endpoint required']);
        exit;
    }

    $endpoint = $segments[0];

    switch ($endpoint) {
        case 'newsletter':
            require_once $api_path . 'newsletter.php';
            break;
        case 'cart':
            require_once $api_path . 'cart.php';
            break;
        case 'wishlist':
            require_once $api_path . 'wishlist.php';
            break;
        case 'products':
            require_once $api_path . 'products.php';
            break;
        default:
            header('Content-Type: application/json');
            echo json_encode(['error' => 'API endpoint not found']);
            exit;
    }
}

/**
 * Handle frontend routes
 */
function handleFrontendRoutes($segments) {
    $frontend_path = 'admin/pages/';

    // Define direct route mappings
    $routes = [
        'dashboard' => 'dashboard.php',
        'authenticate' => 'user/authenticate.php',
        'add-study' => 'contents/add_new_study.php',
        'prepare-agenda' => 'contents/prepare_agenda.php',
        'minutes' => 'contents/minutes_preparation.php',
        'login' => 'user/login.php',
        // 'logout' => 'user/login.php',
        'register' => 'user/register.php',
        'generate-letter' => 'contents/general_letters_content.php',
        'contacts' => 'contents/create_contact.php',
        'logout' => 'user/logout.php',
        'about' => 'static/about.php',
        'account-information' => 'contents/account_information.php',
        'shipping' => 'static/shipping.php',
        'returns' => 'static/returns.php',
        'terms' => 'static/terms.php',
        'privacy' => 'static/privacy.php',
        'size-guide' => 'static/size-guide.php',
        'careers' => 'static/careers.php',
        'press' => 'static/press.php',
        'blog' => 'static/blog.php',
        'sustainability' => 'static/sustainability.php',
    ];

    $route = $segments[0] ?? '';

    if (array_key_exists($route, $routes)) {
        require_once $frontend_path . $routes[$route];
    } elseif ($route === 'products') {
        if (count($segments) === 1) {
            require_once $frontend_path . 'products/category.php';
        } elseif (count($segments) === 2) {
            $_GET['cat'] = $segments[1];
            require_once $frontend_path . 'products/category.php';
        } else {
            require_once 'admin/404.php';
        }
    } elseif ($route === 'product') {
        if (count($segments) >= 2) {
            $slug_parts = explode('-', $segments[1]);
            if (is_numeric($slug_parts[0])) {
                $_GET['id'] = intval($slug_parts[0]);
                require_once $frontend_path . 'products/product-detail.php';
            } else {
                require_once 'admin/404.php';
            }
        } else {
            require_once 'admin/404.php';
        }
    } else {
        require_once 'admin/404.php';
    }
}

include 'admin/includes/footer.php';