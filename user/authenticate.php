<?php
require_once __DIR__ . '/../includes/config/database.php';

// Use consistent session name across entire application
defined('CSRF_SESSION_NAME') || define('CSRF_SESSION_NAME', 'ug_irb_session');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $db = new Database();
    $conn = $db->connect();

    if ($conn) {
        $stmt = $conn->prepare("SELECT id, full_name, email, role, password_hash, is_first, institution_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Use consistent session name for all users
            session_name(CSRF_SESSION_NAME);
            session_start();

            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['phone_number'] = $user['phone_number'] ?? 'Not provided';
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_time'] = time();
            $_SESSION['is_first'] = $user['is_first'];
            $_SESSION['institution_id'] = $user['institution_id'];

            // Redirect to dashboard for admin and super_admin
            if ($user['role'] === 'admin' || $user['role'] === 'super_admin') {
                header('Location: /dashboard');
                exit;
            } else {
                header('Location: /applicant-dashboard');
                exit;
            }
        } else {
            // Invalid credentials, redirect back with error
            header('Location: /login?error=1');
            exit;
        }
    } else {
        // Database error
        header('Location: /login?error=1');
        exit;
    }
} else {
    // Not a POST request, redirect to login
    header('Location: /login');
    exit;
}
