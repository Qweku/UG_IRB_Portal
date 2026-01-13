<?php
require_once '../../vendor/autoload.php'; // Assuming composer autoload
require_once '../../includes/config/database.php';

use PhpOffice\PhpWord\TemplateProcessor;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$studyId = $_POST['study_id'] ?? null;
$templatePath = $_POST['template_path'] ?? null;

if (!$studyId || !$templatePath) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Study ID and template path are required']);
    exit;
}

if (!file_exists($templatePath)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Template file not found']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    $sql = "SELECT
    /* ===== SYSTEM ===== */
    DATE_FORMAT(CURDATE(), '%M %e, %Y') AS This_Letter_Date_L,

    /* ===== CONTACTS ===== */
    c.first_name            AS First_Name,
    c.middle_name           AS Middle_Name,
    c.last_name             AS Last_Name,
    c.suffix                AS Suffix,
    c.company_dept_name     AS Company_Name,
    c.street_address_1      AS Street_Address_1,
    c.street_address_2      AS Street_Address_2,
    c.city                  AS City,
    c.state                 AS State,
    c.zip                   AS Zip,
    c.title                 AS Addressee_Title,

    /* ===== STUDIES ===== */
    s.protocol_number          AS Study_Number,
    DATE_FORMAT(s.meeting_date, '%M %e, %Y') AS Meeting_Date_L,
    s.irb_code              AS IRB_Code,
    s.title                 AS Protocol_Number_Title,
    DATE_FORMAT(s.expiration_date, '%M %e, %Y') AS Expiration_Date_L,

    /* ===== AGENDA ITEMS ===== */
    a.internal_number       AS Source_Number,
    a.agenda_category       AS Agenda_Category,
    a.condition_1           AS Agenda_Condition1,
    a.condition_2           AS Agenda_Condition2,
    a.agenda_explanation    AS Agenda_Explanation,
    a.action_explanation    AS Action_Explanation

    FROM studies s
    LEFT JOIN contacts c      ON CONCAT(c.last_name, ' ', c.first_name) = s.pi
    LEFT JOIN agenda_items a  ON a.study_id = s.id

    WHERE s.id = :study_id
    LIMIT 1;
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute(['study_id' => $studyId]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        throw new Exception('No data found for document generation');
    }

    // $placeholders = [];

    // foreach ($data as $key => $value) {
    //     $placeholders['$_' . $key . '$'] = $value ?? '';
    // }

    $template = new TemplateProcessor($templatePath);

    foreach ($data as $key => $value) {
        $template->setValue($key, $value ?? '');
        error_log("Replacing {$key} with {$value}");
    }

    $outputFile = tempnam(sys_get_temp_dir(), 'letter_') . '.docx';
    $template->saveAs($outputFile);

    // Send file to browser
    $timestamp = date('Ymd_His');
    $filename = "IRBActionLetter_{$timestamp}.docx";

    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($outputFile));
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    readfile($outputFile);
    unlink($outputFile); // Clean up temp file

    exit;
} catch (Exception $e) {
    error_log('Letter generation error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to generate letter: ' . $e->getMessage()
    ]);
}
