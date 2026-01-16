<?php
require_once '../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../../includes/config/database.php';
require_once '../../config.php';
header('Content-Type: application/json');

function sendEmailWithAttachment($to, $subject, $message, $attachment = null) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        // $mail->SMTPOptions = array(
        //     'ssl' => array(
        //         'verify_peer' => false,
        //         'verify_peer_name' => false,
        //         'allow_self_signed' => true
        //     )
        // );
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port = SMTP_PORT;

        // Recipients
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($to);

        // Attachments
        if ($attachment && isset($_FILES['attachment']) && $_FILES['attachment']['error'] == UPLOAD_ERR_OK) {
            $mail->addAttachment($_FILES['attachment']['tmp_name'], $_FILES['attachment']['name']);
        }

        // Content
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $message;

        error_log("Attempting to send email to: $to with subject: $subject");
        $mail->send();
        error_log("Email sent successfully to: $to");
        return true;
    } catch (Exception $e) {
        error_log("Failed to send email to: $to. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $studyId = $_POST['study_id'] ?? null;
    $recipients = $_POST['recipients'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    if (!$studyId || !$recipients || !$subject || !$message) {
        throw new Exception('All fields are required');
    }

    $recipientList = array_map('trim', explode(',', $recipients));
    $errors = [];

    foreach ($recipientList as $email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email: $email";
        }
    }

    if (!empty($errors)) {
        throw new Exception(implode(', ', $errors));
    }

    // Send to each recipient
    $successCount = 0;
    foreach ($recipientList as $email) {
        if (sendEmailWithAttachment($email, $subject, $message)) {
            $successCount++;
        }
    }

    if ($successCount > 0) {
        echo json_encode([
            'success' => true,
            'message' => "Email sent to $successCount recipient(s)"
        ]);
    } else {
        throw new Exception('Failed to send emails');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>