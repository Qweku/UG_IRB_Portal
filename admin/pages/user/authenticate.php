<?php
session_start();

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

        if (is_admin_logged_in()) {
            error_log("Admin logged in: " . $email);
            error_log("Admin Session Role: " . $_SESSION['role']);
            error_log("Admin Session Logged In: " . ($_SESSION['logged_in'] ? 'true' : 'false'));
            header('Location: /dashboard');
            exit;
        } else {
            error_log("Non-admin logged in: " . $email);
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
