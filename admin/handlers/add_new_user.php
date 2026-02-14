<?php
/**
 * Add New User Handler
 * Handles AJAX requests to add new users and send email notification
 */

require_once '../includes/auth_check.php';
require_once '../../includes/config/database.php';
require_once '../../includes/functions/notification_functions.php';
require_once '../../vendor/autoload.php';
require_once '../../config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// Use centralized role check
require_role('admin');

// Validate request method
// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit;
}

// Validate required fields
$required_fields = ['full_name', 'email', 'password', 'role', 'institution_id'];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty(trim($data[$field]))) {
        echo json_encode(['status' => 'error', 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
        exit;
    }
}

$full_name = trim($data['full_name']);
$email = trim($data['email']);
$phone = isset($data['phone']) ? trim($data['phone']) : '';
$password = $data['password'];
$role = trim($data['role']);
$institution_id = isset($data['institution_id']) ? (int)$data['institution_id'] : null;

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
    exit;
}

// Validate role
$valid_roles = ['admin', 'super_admin', 'applicant', 'reviewer'];
if (!in_array($role, $valid_roles)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid role']);
    exit;
}

// Validate password strength (minimum 8 characters)
if (strlen($password) < 8) {
    echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters long']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Email already registered']);
        exit;
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if phone column exists
    $stmt = $conn->query("SHOW COLUMNS FROM users LIKE 'phone'");
    $phoneColumnExists = $stmt->fetch();
    
    if ($phoneColumnExists) {
        // Insert user with phone
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone_number, password_hash, role, institution_id, status, is_first, created_at) VALUES (?, ?, ?, ?, ?, ?, 'active', 0, NOW())");
        $stmt->execute([$full_name, $email, $phone, $hashed_password, $role, $institution_id]);
    } else {
        // Insert user without phone
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password_hash, role, institution_id, status, is_first, created_at) VALUES (?, ?, ?, ?, ?, 'active', 0, NOW())");
        $stmt->execute([$full_name, $email, $hashed_password, $role, $institution_id]);
    }

    if ($stmt->rowCount() > 0) {
        $user_id = $conn->lastInsertId();

        // Create in-app notification for the new user
        $createdByName = $_SESSION['full_name'] ?? 'Administrator';
        createNewUserCreatedNotification($user_id, $role, $createdByName);

        // Send email with credentials
        $email_sent = sendCredentialsEmail($email, $full_name, $email, $password, $role);

        if ($email_sent) {
            echo json_encode([
                'status' => 'success', 
                'message' => 'User added successfully. Login credentials have been sent to the user via email.',
                'user_id' => $user_id
            ]);
        } else {
            echo json_encode([
                'status' => 'warning', 
                'message' => 'User added successfully but failed to send email notification. Please inform the user manually.',
                'user_id' => $user_id
            ]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add user']);
    }

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
}

/**
 * Send credentials email to user
 */
function sendCredentialsEmail($to, $name, $email, $password, $role) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port = SMTP_PORT;

        // Recipients
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($to, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your UG IRB Portal Account Credentials';
        
        $role_display = ucfirst($role);
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f8f9fa; }
                .credentials { background-color: #ffffff; border: 1px solid #dee2e6; padding: 15px; margin: 15px 0; border-radius: 5px; }
                .footer { text-align: center; padding: 20px; color: #6c757d; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>UG IRB Portal Account Created</h2>
                </div>
                <div class='content'>
                    <p>Dear {$name},</p>
                    <p>Your account has been created successfully on the UG IRB Portal as a <strong>{$role_display}</strong>.</p>
                    <div class='credentials'>
                        <p><strong>Login Credentials:</strong></p>
                        <p>Email: <strong>{$email}</strong></p>
                        <p>Password: <strong>{$password}</strong></p>
                    </div>
                    <p>Please login at your earliest convenience and change your password after first login.</p>
                    <p>If you have any questions, please contact the system administrator.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from UG IRB Portal.</p>
                    <p>&copy; " . date('Y') . " UG IRB Portal. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->Body = $message;
        $mail->AltBody = "Dear {$name},\n\nYour account has been created successfully on the UG IRB Portal as a {$role_display}.\n\nLogin Credentials:\nEmail: {$email}\nPassword: {$password}\n\nPlease login at your earliest convenience and change your password after first login.\n\nIf you have any questions, please contact the system administrator.";

        $mail->send();
        error_log("Credentials email sent successfully to: $to");
        return true;
    } catch (Exception $e) {
        error_log("Failed to send credentials email to: $to. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
