<?php
session_name('admin_session');
session_start();

require_once __DIR__ . '/../../../includes/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $db = new Database();
    $conn = $db->connect();

    if ($conn) {
        $stmt = $conn->prepare("SELECT id, full_name, email, password_hash, is_first FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = 'admin'; // Assuming all are admin for now
            $_SESSION['login_time'] = time();
            $_SESSION['is_first'] = $user['is_first'];

            // Always redirect to dashboard, modal will be shown there if first login
            header('Location: /dashboard');
            exit;
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
