<?php
require_once '../../includes/functions/helpers.php';
require_once '../includes/auth_check.php';

$pdo = (new Database())->connect();

// Integer validation for ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("Invalid file ID");
}

$stmt = $pdo->prepare("SELECT file_path, report_name FROM reports WHERE id = :id");
$stmt->execute([':id' => $id]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

// Directory validation - ensure file is within allowed directory
$allowed_dir = '../../uploads/reports/';
if ($report && file_exists($report['file_path'])) {
    $real_path = realpath($report['file_path']);
    $real_allowed_dir = realpath($allowed_dir);
    
    if ($real_path === false || $real_allowed_dir === false || strpos($real_path, $real_allowed_dir) !== 0) {
        http_response_code(403);
        die('Invalid file path');
    }
    
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($report['file_path']) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($report['file_path']));
    readfile($report['file_path']);
    exit;
} else {
    die("File not found");
}
