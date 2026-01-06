<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('admin_session');
    session_start();
}

// Hardcoded credentials for demo (replace with database check in production)
$valid_email = 'admin@irb.com';
$valid_password = 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($email === $valid_email && $password === $valid_password) {
        $_SESSION['logged_in'] = true;
        $_SESSION['role'] = 'admin';
        $_SESSION['user_email'] = $email;
        $_SESSION['login_time'] = time();

        if (is_admin_logged_in()) {
            header('Location: /dashboard');
            exit;
        }
    } else {
        // Invalid credentials, redirect back with error
        header('Location: /login?error=1');
        exit;
    }
} else {
    // Not a POST request, redirect to login
    header('Location: /login');
    exit;
}
