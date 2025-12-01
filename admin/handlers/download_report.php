
<?php
require_once '../../includes/functions/helpers.php';
$pdo = (new Database())->connect();


$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT file_path, report_name FROM reports WHERE id = :id");
$stmt->execute([':id' => $id]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

if ($report && file_exists($report['file_path'])) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.basename($report['file_path']).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($report['file_path']));
    readfile($report['file_path']);
    exit;
} else {
    die("File not found");
}
