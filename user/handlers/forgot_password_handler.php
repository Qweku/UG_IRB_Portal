<?php
/**
 * Forgot Password Handler
 * Handles AJAX requests to send password reset links
 */

require_once __DIR__ . '/../../includes/config/database.php';
require_once __DIR__ . '/../../includes/functions/csrf.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Verify CSRF token
$csrf_token = $_POST['csrf_token'] ?? '';
if (!validateCsrfToken($csrf_token)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
    exit;
}

// Get and validate email
$email = trim($_POST['email'] ?? '');

if (empty($email)) {
    echo json_encode(['status' => 'error', 'message' => 'Email is required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    }

    // Check if user exists
    $stmt = $conn->prepare("SELECT id, full_name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Always show success message to prevent email enumeration
    // This is a security best practice - don't reveal whether email exists or not
    if (!$user) {
        echo json_encode([
            'status' => 'success',
            'message' => 'If an account exists with this email, a password reset link has been sent.'
        ]);
        exit;
    }

    // Generate secure token
    $token = bin2hex(random_bytes(32));
    
    // Set expiration (1 hour from now)
    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Insert token into database
    $stmt = $conn->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$user['id'], $token, $expires_at]);

    if ($stmt->rowCount() > 0) {
        // Send reset email
        $reset_link = BASE_URL . 'reset-password?token=' . $token;
        $email_sent = sendPasswordResetEmail($email, $user['full_name'], $reset_link);

        if ($email_sent) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Password reset link has been sent to your email address.'
            ]);
        } else {
            // Even if email fails, show success to prevent enumeration
            // In production, you might want to log this failure
            error_log("Failed to send password reset email to: $email");
            echo json_encode([
                'status' => 'success',
                'message' => 'If an account exists with this email, a password reset link has been sent.'
            ]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to generate reset token. Please try again.']);
    }

} catch (PDOException $e) {
    error_log("Database error in forgot_password_handler: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'An error occurred. Please try again later.']);
}

/**
 * Send password reset email to user
 */
function sendPasswordResetEmail($to, $name, $reset_link) {
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
        $mail->Subject = 'Password Reset Request - UG IRB Portal';
        
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f8f9fa; }
                .button { display: inline-block; padding: 12px 24px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 15px 0; }
                .footer { text-align: center; padding: 20px; color: #6c757d; font-size: 12px; }
                .warning { background-color: #fff3cd; border: 1px solid #ffc107; padding: 10px; border-radius: 5px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Password Reset Request</h2>
                </div>
                <div class='content'>
                    <p>Dear {$name},</p>
                    <p>We received a request to reset your password for the UG Hares Ethics Portal.</p>
                    <p>Click the button below to reset your password:</p>
                    <p style='text-align: center;'>
                        <a href='{$reset_link}' class='button'>Reset Password</a>
                    </p>
                    <p>Or copy and paste this link in your browser:</p>
                    <p style='word-break: break-all; font-size: 12px;'>{$reset_link}</p>
                    <div class='warning'>
                        <strong>Note:</strong> This link will expire in 1 hour. If you didn't request a password reset, please ignore this email.
                    </div>
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
        $mail->AltBody = "Dear {$name},\n\nWe received a request to reset your password for the UG IRB Portal.\n\nClick the link below to reset your password:\n{$reset_link}\n\nThis link will expire in 1 hour.\n\nIf you didn't request a password reset, please ignore this email.\n\n&copy; " . date('Y') . " UG IRB Portal.";

        $mail->send();
        error_log("Password reset email sent successfully to: $to");
        return true;
    } catch (Exception $e) {
        error_log("Failed to send password reset email to: $to. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
