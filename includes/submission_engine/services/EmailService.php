<?php

/**
 * EmailService Class
 *
 * Handles email notifications for the submission engine.
 * Provides methods for sending confirmation and notification emails.
 *
 * @package UGIRB\SubmissionEngine\Services
 */

namespace UGIRB\SubmissionEngine\Services;

class EmailService
{
    /** @var string Sender name */
    private string $senderName;

    /** @var string Sender email */
    private string $senderEmail;

    /** @var string Application name */
    private string $appName;

    /**
     * Constructor
     *
     * @param string|null $senderEmail Sender email address
     * @param string|null $senderName Sender name
     * @param string|null $appName Application name
     */
    public function __construct(
        ?string $senderEmail = null,
        ?string $senderName = null,
        ?string $appName = null
    ) {
        $this->senderEmail = $senderEmail ?? getenv('EMAIL_FROM') ?: 'noreply@ug-irb.edu';
        $this->senderName = $senderName ?? getenv('EMAIL_FROM_NAME') ?: 'UG IRB Portal';
        $this->appName = $appName ?? 'UG IRB Portal';
    }

    /**
     * Send application submission confirmation
     *
     * @param string $recipientEmail Recipient email
     * @param string $protocolNumber Protocol number
     * @param string $applicationType Application type
     * @return bool True if sent successfully
     */
    public function sendSubmissionConfirmation(
        string $recipientEmail,
        string $protocolNumber,
        string $applicationType
    ): bool {
        $subject = "Application Submitted - {$protocolNumber}";
        $typeLabel = $this->getTypeLabel($applicationType);

        $body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #1a4d80; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .protocol-box { background-color: #fff; border: 2px solid #1a4d80; padding: 15px; text-align: center; margin: 20px 0; }
        .protocol-number { font-size: 24px; font-weight: bold; color: #1a4d80; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{$this->appName}</h1>
        </div>
        <div class="content">
            <p>Dear Applicant,</p>
            
            <p>Your {$typeLabel} application has been successfully submitted to the University of Ghana Institutional Review Board.</p>
            
            <div class="protocol-box">
                <p>Your Protocol Number:</p>
                <p class="protocol-number">{$protocolNumber}</p>
            </div>
            
            <p><strong>Next Steps:</strong></p>
            <ul>
                <li>Your application will be reviewed by our staff</li>
                <li>You will receive updates on the status of your application</li>
                <li>Please quote your protocol number in all communications</li>
            </ul>
            
            <p>If you have any questions, please contact our office.</p>
        </div>
        <div class="footer">
            <p>This is an automated message from {$this->appName}</p>
            <p>Please do not reply to this email</p>
        </div>
    </div>
</body>
</html>
HTML;

        return $this->send($recipientEmail, $subject, $body);
    }

    /**
     * Send application received notification to reviewers
     *
     * @param array $reviewerEmails Reviewer email addresses
     * @param string $protocolNumber Protocol number
     * @param string $applicationType Application type
     * @param string $applicantName Applicant name
     * @return bool True if sent successfully
     */
    public function sendReviewNotification(
        array $reviewerEmails,
        string $protocolNumber,
        string $applicationType,
        string $applicantName
    ): bool {
        $subject = "New Application for Review - {$protocolNumber}";
        $typeLabel = $this->getTypeLabel($applicationType);

        $body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #1a4d80; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .protocol-box { background-color: #fff; border: 2px solid #1a4d80; padding: 15px; text-align: center; margin: 20px 0; }
        .protocol-number { font-size: 24px; font-weight: bold; color: #1a4d80; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{$this->appName} - Review Notification</h1>
        </div>
        <div class="content">
            <p>Dear Reviewer,</p>
            
            <p>A new {$typeLabel} application has been submitted and is ready for your review.</p>
            
            <div class="protocol-box">
                <p>Protocol Number:</p>
                <p class="protocol-number">{$protocolNumber}</p>
            </div>
            
            <p><strong>Applicant:</strong> {$applicantName}</p>
            <p><strong>Type:</strong> {$typeLabel}</p>
            
            <p>Please log in to the reviewer portal to access this application.</p>
        </div>
    </div>
</body>
</html>
HTML;

        return $this->sendToMultiple($reviewerEmails, $subject, $body);
    }

    /**
     * Send application status update notification
     *
     * @param string $recipientEmail Recipient email
     * @param string $protocolNumber Protocol number
     * @param string $status New status
     * @param string|null $comments Reviewer comments
     * @return bool True if sent successfully
     */
    public function sendStatusUpdate(
        string $recipientEmail,
        string $protocolNumber,
        string $status,
        ?string $comments = null
    ): bool {
        $subject = "Application Status Update - {$protocolNumber}";
        $statusLabel = $this->getStatusLabel($status);

        $body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #1a4d80; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .status-box { background-color: #fff; border: 2px solid #1a4d80; padding: 15px; text-align: center; margin: 20px 0; }
        .status { font-size: 24px; font-weight: bold; color: #1a4d80; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{$this->appName} - Status Update</h1>
        </div>
        <div class="content">
            <p>Dear Applicant,</p>
            
            <p>The status of your application has been updated.</p>
            
            <div class="status-box">
                <p>Protocol Number:</p>
                <p class="protocol-number">{$protocolNumber}</p>
                <p>New Status: <strong>{$statusLabel}</strong></p>
            </div>
HTML;

        if (!empty($comments)) {
            $body .= <<<HTML
            <p><strong>Comments:</strong></p>
            <p>{$comments}</p>
HTML;
        }

        $body .= <<<HTML
            <p>Please log in to your account to view more details.</p>
        </div>
    </div>
</body>
</html>
HTML;

        return $this->send($recipientEmail, $subject, $body);
    }

    /**
     * Send plain text email
     *
     * @param string $recipientEmail Recipient email
     * @param string $subject Email subject
     * @param string $body Email body (plain text)
     * @return bool True if sent successfully
     */
    public function sendPlain(string $recipientEmail, string $subject, string $body): bool
    {
        $headers = [
            'From' => $this->formatAddress($this->senderName, $this->senderEmail),
            'Reply-To' => $this->formatAddress($this->senderName, $this->senderEmail),
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/plain; charset=UTF-8',
            'X-Mailer' => 'PHP/' . phpversion()
        ];

        return mail($recipientEmail, $subject, $body, $headers);
    }

    /**
     * Send HTML email
     *
     * @param string $recipientEmail Recipient email
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @return bool True if sent successfully
     */
    public function send(string $recipientEmail, string $subject, string $body): bool
    {
        $headers = [
            'From' => $this->formatAddress($this->senderName, $this->senderEmail),
            'Reply-To' => $this->formatAddress($this->senderName, $this->senderEmail),
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Mailer' => 'PHP/' . phpversion()
        ];

        return @mail($recipientEmail, $subject, $body, $headers);
    }

    /**
     * Send email to multiple recipients
     *
     * @param array $recipientEmails Array of email addresses
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @return bool True if sent successfully to all
     */
    public function sendToMultiple(array $recipientEmails, string $subject, string $body): bool
    {
        $headers = [
            'From' => $this->formatAddress($this->senderName, $this->senderEmail),
            'Reply-To' => $this->formatAddress($this->senderName, $this->senderEmail),
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Mailer' => 'PHP/' . phpversion()
        ];

        $success = true;
        foreach ($recipientEmails as $email) {
            if (!empty(trim($email))) {
                if (!mail($email, $subject, $body, $headers)) {
                    $success = false;
                }
            }
        }

        return $success;
    }

    /**
     * Format email address with name
     *
     * @param string $name Name
     * @param string $email Email
     * @return string Formatted address
     */
    private function formatAddress(string $name, string $email): string
    {
        return '"' . addslashes($name) . '" <' . $email . '>';
    }

    /**
     * Get human-readable application type label
     *
     * @param string $type Application type
     * @return string Label
     */
    private function getTypeLabel(string $type): string
    {
        return match (strtolower($type)) {
            'student' => 'Student Research',
            'nmimr' => 'NMIMR',
            'non_nmimr' => 'External (Non-NMIMR)',
            default => ucfirst($type)
        };
    }

    /**
     * Get human-readable status label
     *
     * @param string $status Application status
     * @return string Label
     */
    private function getStatusLabel(string $status): string
    {
        return match (strtolower($status)) {
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'under_review' => 'Under Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'withdrawn' => 'Withdrawn',
            'revision_required' => 'Revision Required',
            default => ucfirst(str_replace('_', ' ', $status))
        };
    }

    /**
     * Get sender name
     *
     * @return string Sender name
     */
    public function getSenderName(): string
    {
        return $this->senderName;
    }

    /**
     * Get sender email
     *
     * @return string Sender email
     */
    public function getSenderEmail(): string
    {
        return $this->senderEmail;
    }

    /**
     * Set sender name
     *
     * @param string $name Sender name
     */
    public function setSenderName(string $name): void
    {
        $this->senderName = $name;
    }

    /**
     * Set sender email
     *
     * @param string $email Sender email
     */
    public function setSenderEmail(string $email): void
    {
        $this->senderEmail = $email;
    }

    /**
     * Set application name
     *
     * @param string $appName Application name
     */
    public function setAppName(string $appName): void
    {
        $this->appName = $appName;
    }
}
