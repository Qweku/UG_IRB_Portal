<?php
session_start();

// Refresh expiration time
$_SESSION['session_expire_time'] = time() + ini_get('session.gc_maxlifetime');

// Send confirmation
header('Content-Type: application/json');
echo json_encode(['status' => 'ok']);
