<?php

// Redirect to file path for viewing/downloading
if (!isset($_GET['file_path']) || empty($_GET['file_path'])) {
    echo "No file specified.";
    exit;
}
$filePath = $_GET['file_path'];
if (!file_exists($filePath)) {
    echo "File not found.";
    exit;
}
// Log file access for auditing
error_log("File accessed: " . $filePath . ", accessed_by_user_id=" 
. (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET') . ", session_id=" . session_id() 
. ", logged_in=" . (isset($_SESSION['logged_in']) ? $_SESSION['logged_in'] : 'NOT SET'));
      
error_log("Session info: " . "session_name=" . session_name() .
    ", session_id=" . session_id() .
    ", logged_in=" . (isset($_SESSION['logged_in']) ? $_SESSION['logged_in'] : 'NOT SET') .
    ", user_id=" . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET'));
// Serve the file for download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize("/includes/uploads/".$filePath));
readfile("/includes/uploads/".$filePath);
exit;










