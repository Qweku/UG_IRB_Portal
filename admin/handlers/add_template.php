<?php
require_once '../../includes/config/database.php';
header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->connect();
    $conn->beginTransaction();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // -----------------------------
    // Collect & sanitize input
    // -----------------------------
    $data = [
        'irb_code' => trim($_POST['irb_code'] ?? 'NOGUCHI MEMORIAL INSTITUTE FOR MEDICAL RESEARCH-IRB'),
        'letter_type' => trim($_POST['letter_type'] ?? ''),
        'closing' => trim($_POST['closing'] ?? ''),
        'signatory' => trim($_POST['signatory'] ?? ''),
        'title' => trim($_POST['title'] ?? ''),
        'email_subject' => trim($_POST['email_subject'] ?? ''),
        'email_message' => trim($_POST['email_message'] ?? ''),
        'updated_at' => date('Y-m-d H:i:s')
    ];

    // -----------------------------
    // Validation
    // -----------------------------
    if (empty($data['letter_type'])) {
        throw new Exception('Letter type is required');
    }

    if (empty($_FILES['document']['tmp_name'])) {
        throw new Exception('Document upload is required');
    }

    // -----------------------------
    // File upload handling
    // -----------------------------
    $uploadDir = "../../uploads/templates/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $file = $_FILES['document'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error: ' . $file['error']);
    }

    // Validate file type
    $allowedTypes = ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf'];
    $fileType = mime_content_type($file['tmp_name']);
    if (!in_array($fileType, $allowedTypes)) {
        throw new Exception('Invalid file type. Only .doc, .docx, and .pdf files are allowed.');
    }

    // Generate safe filename
    $originalName = basename($file['name']);
    $safeName = time() . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
    $filePath = $uploadDir . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        throw new Exception('Failed to save uploaded file');
    }

    // -----------------------------
    // INSERT
    // -----------------------------
    $sql = "
        INSERT INTO irb_templates (
            irb_code, letter_type, letter_name, file_path, closing, signatory, title,
            email_subject, email_message, created_at, updated_at
        ) VALUES (
            :irb_code, :letter_type, :letter_name, :file_path, :closing, :signatory, :title,
            :email_subject, :email_message, NOW(), :updated_at
        )
    ";

    $data['letter_name'] = $originalName;
    $data['file_path'] = $filePath;

    $stmt = $conn->prepare($sql);
    $stmt->execute($data);

    $templateId = (int)$conn->lastInsertId();

    $conn->commit();

    error_log("Template added successfully with ID: " . $templateId);

    echo json_encode([
        'success' => true,
        'message' => 'Template added successfully',
        'id' => $templateId
    ]);

} catch (Exception $e) {

    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }

    // Clean up uploaded file if it exists
    if (isset($filePath) && file_exists($filePath)) {
        unlink($filePath);
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>