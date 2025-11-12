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

    $route = $segments[0];

    switch ($route) {
        case 'products':
            if (count($segments) === 1) {
                // All products
                require_once $frontend_path . 'products/category.php';
            } elseif (count($segments) === 2) {
                // Category products
                $_GET['cat'] = $segments[1];
                require_once $frontend_path . 'products/category.php';
            } else {
                require_once 'admin/404.php';
            }
            break;

        case 'product':
            if (count($segments) >= 2) {
                // Product detail - extract ID from slug
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
            break;

        case 'dashboard':
            require_once $frontend_path . 'dashboard.php';
            break;

        case 'authenticate':
            require_once $frontend_path . 'user/authenticate.php';
            break;

        case 'add-study':
            require_once $frontend_path . 'contents/add_new_study.php';
            break;

        case 'prepare-agenda':
            require_once $frontend_path . 'contents/prepare_agenda.php';
            break;

        case 'minutes':
            require_once $frontend_path . 'contents/minutes_preparation.php';
            break;

        case 'login':
            require_once $frontend_path . 'user/login.php';
            break;

        case 'register':
            require_once $frontend_path . 'user/register.php';
            break;

        case 'generate-letter':
            require_once $frontend_path . 'contents/general_letters_content.php';
            break;

        case 'contacts':
            require_once $frontend_path . 'contents/create_contact.php';
            break;

        case 'logout':
            require_once $frontend_path . 'user/logout.php';
            break;

        case 'about':
            require_once $frontend_path . 'static/about.php';
            break;


        case 'account-information':
            require_once $frontend_path . 'contents/account_information.php';
            break;

        case 'shipping':
            require_once $frontend_path . 'static/shipping.php';
            break;

        case 'returns':
            require_once $frontend_path . 'static/returns.php';
            break;

        case 'terms':
            require_once $frontend_path . 'static/terms.php';
            break;

        case 'privacy':
            require_once $frontend_path . 'static/privacy.php';
            break;

        case 'size-guide':
            require_once $frontend_path . 'static/size-guide.php';
            break;

        case 'careers':
            require_once $frontend_path . 'static/careers.php';
            break;

        case 'press':
            require_once $frontend_path . 'static/press.php';
            break;

        case 'blog':
            require_once $frontend_path . 'static/blog.php';
            break;

        case 'sustainability':
            require_once $frontend_path . 'static/sustainability.php';
            break;

        default:
            // 404 for unknown routes
            require_once 'admin/404.php';
            break;
    }
}

include 'admin/includes/footer.php';