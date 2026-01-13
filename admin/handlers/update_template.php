<?php
require_once '../../includes/config/database.php';

header('Content-Type: application/json');

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$id = $_POST['id'] ?? null;
$letter_type = $_POST['letter_type'] ?? null;
$closing = $_POST['closing'] ?? null;
$signatory = $_POST['signatory'] ?? null;
$title = $_POST['title'] ?? null;
$email_subject = $_POST['email_subject'] ?? null;
$email_body = $_POST['email_body'] ?? null;

// Validate required fields
if (!$id || !is_numeric($id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid template ID']);
    exit;
}

if (!$letter_type) {
    echo json_encode(['success' => false, 'message' => 'Letter type is required']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Prepare update data
    $updateData = [
        'letter_type' => $letter_type,
        'closing' => $closing,
        'signatory' => $signatory,
        'title' => $title,
        'email_subject' => $email_subject,
        'email_message' => $email_body
    ];

    // Handle file upload if provided
    if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['document'];
        $allowedTypes = ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf'];
        $maxSize = 10 * 1024 * 1024; // 10MB

        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only .doc, .docx, and .pdf files are allowed.']);
            exit;
        }

        if ($file['size'] > $maxSize) {
            echo json_encode(['success' => false, 'message' => 'File size too large. Maximum size is 10MB.']);
            exit;
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('template_', true) . '.' . $extension;
        $uploadPath = '../../uploads/templates/' . $filename;

        // Ensure upload directory exists
        if (!is_dir('../../uploads/templates/')) {
            mkdir('../../uploads/templates/', 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $updateData['file_path'] = '../../uploads/templates/' . $filename;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
            exit;
        }
    }

    // Build update query
    $setParts = [];
    $params = [];
    foreach ($updateData as $key => $value) {
        $setParts[] = "$key = ?";
        $params[] = $value;
    }
    $params[] = $id;

    $sql = "UPDATE irb_templates SET " . implode(', ', $setParts) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or template not found']);
    }

} catch (Exception $e) {
    error_log("Error updating template: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>