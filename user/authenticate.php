<?php
require_once __DIR__ . '/../includes/config/database.php';

defined('CSRF_SESSION_NAME') || define('CSRF_SESSION_NAME', 'ug_irb_session');

// Start session ONCE
session_name(CSRF_SESSION_NAME);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /login');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    header('Location: /login?error=1');
    exit;
}

$stmt = $conn->prepare(
    "SELECT id, full_name, email, role, password_hash, is_first, institution_id
     FROM users
     WHERE email = ?"
);
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user['password_hash'])) {
    header('Location: /login?error=1');
    exit;
}

// ✅ Set session values WITHOUT clearing
$_SESSION['logged_in']     = true;
$_SESSION['user_id']       = (int)$user['id'];
$_SESSION['user_email']    = $user['email'];
$_SESSION['full_name']     = $user['full_name'];
$_SESSION['role']          = $user['role'];
$_SESSION['is_first']      = (int)$user['is_first'];
$_SESSION['institution_id'] = $user['institution_id'];
$_SESSION['login_time']    = time();

// 🔐 Regenerate session ID (security)
session_regenerate_id(true);

// 🔀 Redirect by role
switch ($user['role']) {
    case 'admin':
    case 'super_admin':
        header('Location: /dashboard');
        break;

    case 'applicant':
        header('Location: /applicant-dashboard');
        break;
        
    case 'reviewer':
        header('Location: /reviewer-dashboard');
        break;

    default:
        header('Location: /login?error=role');
}
exit;
