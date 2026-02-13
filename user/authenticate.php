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

// Set session values WITHOUT clearing
$_SESSION['logged_in']     = true;
$_SESSION['user_id']       = (int)$user['id'];
$_SESSION['user_email']    = $user['email'];
$_SESSION['full_name']     = $user['full_name'];
$_SESSION['role']          = $user['role'];
$_SESSION['is_first']      = (int)$user['is_first'];
$_SESSION['institution_id'] = $user['institution_id'];
$_SESSION['login_time']    = time();

// ===========================================================
// SINGLE SESSION PER USER - Generate and store session token
// ===========================================================
try {
    // Generate unique session token
    $session_token = bin2hex(random_bytes(32));
    $session_expires = date('Y-m-d H:i:s', strtotime('+2 hours'));

    // Invalidate any existing session for this user (single-session enforcement)
    $stmt = $conn->prepare("UPDATE users SET session_token = NULL, session_expires_at = NULL, last_activity = NULL WHERE id = ?");
    $stmt->execute([$user['id']]);

    // Store new token
    $stmt = $conn->prepare("UPDATE users SET session_token = ?, session_expires_at = ?, last_activity = NOW() WHERE id = ?");
    $stmt->execute([$session_token, $session_expires, $user['id']]);

    // Add to session variables
    $_SESSION['session_token'] = $session_token;
    $_SESSION['last_activity'] = time();
} catch (Exception $e) {
    // Log error but don't block login - session token generation failed
    error_log('Session token generation failed: ' . $e->getMessage());
}

// Regenerate session ID (security)
session_regenerate_id(true);

// Redirect by role
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
