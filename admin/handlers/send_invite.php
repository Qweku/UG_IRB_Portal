<?php
require_once '../../includes/config/database.php';
require_once '../../config.php'; // For email config if any
header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->connect();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $contactId = $input['contact_id'] ?? null;

    if (!$contactId || !is_numeric($contactId)) {
        throw new Exception('Invalid contact ID');
    }

    // Get contact details
    $stmt = $conn->prepare("SELECT id, first_name, last_name, logon_name, email FROM contacts WHERE id = ?");
    $stmt->execute([$contactId]);
    $contact = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$contact) {
        throw new Exception('Contact not found');
    }

    if (empty($contact['email'])) {
        throw new Exception('Contact has no email address');
    }

    if (empty($contact['logon_name'])) {
        throw new Exception('Contact has no logon name');
    }

    // Generate random password
    $password = bin2hex(random_bytes(8)); // 16 character password

    // Hash the password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Full name
    $fullName = $contact['first_name'] . ' ' . $contact['last_name'];

    // Insert into users table
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password_hash, created_at) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $fullName,
        $contact['email'],
        $passwordHash,
        date('Y-m-d H:i:s')
    ]);

    
    // Send email
    $to = $contact['email'];
    $subject = 'UG IRB Portal Invitation';
    $message = "Dear {$contact['first_name']} {$contact['last_name']},\n\n" .
               "You have been invited to join the UG IRB Portal.\n\n" .
               "Username: {$contact['email']}\n" .
               "Password: {$password}\n\n" .
               "Please log in and change your password upon first access.\n\n" .
               "Best regards,\nUG IRB Team";
    $headers = 'From: no-reply@ug.edu.gh' . "\r\n" .
               'Reply-To: no-reply@ug.edu.gh' . "\r\n" .
               'X-Mailer: PHP/' . phpversion();

    if (!mail($to, $subject, $message, $headers)) {
        throw new Exception('Failed to send email');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Invitation sent successfully'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>