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
        $_SESSION['user_email'] = $email;
        header('Location: /dashboard');
        exit;
    } else {
        // Invalid credentials, redirect back with error
        header('Location: login.php?error=1');
        exit;
    }
} else {
    // Not a POST request, redirect to login
    header('Location: /login');
    exit;
}
?>