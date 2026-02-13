<?php
require_once '../../includes/functions/helpers.php';
require_once '../includes/auth_check.php';

// ----- INPUTS -----
$reportType = $_POST['reportType'] ?? '';
$filters    = $_POST['filters'] ?? '[]';
$filters    = json_decode($filters, true) ?: [];
$format     = $_POST['format'] ?? 'pdf';

if (!$reportType) {
    die("Missing report type");
}

// ---------------------------------------------
// 1. DEFINE HEADERS
// ---------------------------------------------
$studyHeads = [
    'IRB Code',
    'Study Number',
    'Protocol/Title',
    'Active?',
    'Study Status',
    'Received',
    'Approved',
    'Renewed',
    'Expire',
    'Sponsor',
    'Investigator'
];

$contactHeads = [
    'Last Name',
    'First Name',
    'Company/Dept Name',
    'Email Address',
    'Main Phone',
    'Specialty 1',
    'Specialty 2'
];

// Column to label mappings
$studyColumns = ['irb_code', 'protocol_number', 'title', 'study_active', 'study_status', 'date_received', 'approval_date', 'last_irb_review', 'expiration_date', 'sponsor_displayname', 'pi'];
$studyMapping = array_combine($studyColumns, $studyHeads);

$contactColumns = ['last', 'first', 'company_dept_name', 'email', 'main_phone', 'specialty_1', 'specialty_2'];
$contactMapping = array_combine($contactColumns, $contactHeads);

// ---------------------------------------------
// 2. FETCH DATA BASED ON REPORT TYPE
// ---------------------------------------------
$dataRows = [];

$db = new Database();
$conn = $db->connect();

if ($reportType === 'Study Search') {

    $sql = "SELECT 
                irb_code,
                protocol_number,
                title,
                study_active,
                study_status,
                date_received,
                approval_date,
                last_irb_review,
                expiration_date,
                sponsor_displayname,
                pi
            FROM studies WHERE 1=1";

    // OPTIONAL FILTER LOGIC - Whitelist validation for columns
    $allowed_study_columns = ['irb_code', 'protocol_number', 'title', 'study_active', 'study_status', 'date_received', 'approval_date', 'last_irb_review', 'expiration_date', 'sponsor_displayname', 'pi'];
    $allowed_contact_columns = ['last', 'first', 'company_dept_name', 'email', 'main_phone', 'specialty_1', 'specialty_2'];
    
    $allowed_columns = ($reportType === 'Study Search') ? $allowed_study_columns : $allowed_contact_columns;
    
    foreach ($filters as $filter) {
        $col = $filter['column'];
        if (!in_array($col, $allowed_columns, true)) {
            continue; // Skip invalid columns
        }
        $val = $filter['value'];
        $sql .= " AND $col LIKE :$col";
        $params[":$col"] = "%$val%";
    }



    $stmt = $conn->prepare($sql);
    $stmt->execute($params ?? []);
    $dataRows = $stmt->fetchAll(PDO::FETCH_NUM);

    $headers = $studyHeads;
} elseif ($reportType === 'Contact Search') {

    $sql = "SELECT 
                last,
                first,
                company_dept_name,
                email,
                main_phone,
                specialty_1,
                specialty_2
            FROM contacts WHERE 1=1";

    foreach ($filters as $filter) {
        $col = $filter['column'];
        $val = $filter['value'];
        $sql .= " AND $col LIKE :$col";
        $params[":$col"] = "%$val%";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params ?? []);
    $dataRows = $stmt->fetchAll(PDO::FETCH_NUM);

    $headers = $contactHeads;
} else {
    die("Unsupported report type (SAE & CPA coming later)");
}


// ---------------------------------------------
// 4. LOG REPORT GENERATION INTO DATABASE
// ---------------------------------------------
$reportName = $reportType;
$generatedDate = date("Y-m-d H:i:s");
$docFormat = $format;
$filePath = '';

// Format filters as human-readable string
if ($reportType === 'Study Search') {
    $mapping = $studyMapping;
} elseif ($reportType === 'Contact Search') {
    $mapping = $contactMapping;
} else {
    $mapping = [];
}

$filtersApplied = '';
foreach ($filters as $filter) {
    $col = $filter['column'];
    $label = $mapping[$col] ?? ucfirst(str_replace('_', ' ', $col));
    $filtersApplied .= "$label: {$filter['value']}; ";
}
$filtersApplied = rtrim($filtersApplied, '; ');

$logSQL = "INSERT INTO reports (report_name, generated_date, doc_format, filters_applied, file_path)
           VALUES (:report_name, :generated_date, :doc_format, :filters_applied, :file_path)";

$logStmt = $conn->prepare($logSQL);
$logStmt->execute([
    ':report_name'    => $reportName,
    ':generated_date' => $generatedDate,
    ':doc_format'     => $docFormat,
    ':filters_applied' => $filtersApplied,
    ':file_path'      => $filePath
]);

$reportId = $conn->lastInsertId();

// ---------------------------------------------
// 3. EXPORT LOGIC
// ---------------------------------------------

$generatedFilePath = '';
if ($format === 'csv') {
    $generatedFilePath = exportCSV($headers, $dataRows);
} elseif ($format === 'excel') {
    $generatedFilePath = exportExcel($headers, $dataRows);
} elseif ($format === 'pdf') {
    $generatedFilePath = exportPDF($headers, $dataRows);
}

// Update the file_path in the database
if ($generatedFilePath) {
    $updateSQL = "UPDATE reports SET file_path = :file_path WHERE id = :id";
    $updateStmt = $conn->prepare($updateSQL);
    $updateStmt->execute([
        ':file_path' => $generatedFilePath,
        ':id' => $reportId
    ]);
}

exit;


// ---------------------------------------------
//  CSV EXPORT
// ---------------------------------------------
function exportCSV($headers, $rows, $filename = null) {
    if (!$filename) {
        $filename = 'report_' . time() . '.csv';
    }
    $filePath = __DIR__ . '/../../uploads/' . $filename; // full path

    $f = fopen($filePath, "w");
    fputcsv($f, $headers);

    foreach ($rows as $r) {
        fputcsv($f, $r);
    }
    fclose($f);

    return $filePath;
}



// ---------------------------------------------
//  EXCEL EXPORT (OpenXLSX)
// ---------------------------------------------
function exportExcel($headers, $rows, $filename = null)
{
    require_once '../../vendor/autoload.php';
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $col = 1;

    foreach ($headers as $head) {
        $coord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '1';
        $sheet->setCellValue($coord, $head);
        $col++;
    }

    $rowNum = 2;
    foreach ($rows as $r) {
        $col = 1;
        foreach ($r as $cell) {
            $coord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $rowNum;
            $sheet->setCellValue($coord, $cell);
            $col++;
        }
        $rowNum++;
    }

    if (!$filename) {
        $filename = 'report_' . time() . '.xlsx';
    }
    $filePath = __DIR__ . '/../../uploads/' . $filename;

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

     $writer->save($filePath);

    return $filePath;
}


// ---------------------------------------------
//  PDF EXPORT (Dompdf)
// ---------------------------------------------
function exportPDF($headers, $rows, $filename = null) {
    require_once '../../vendor/autoload.php';
    $dompdf = new Dompdf\Dompdf();

    $html = "<h3>Generated Report</h3><table border='1' cellpadding='5' cellspacing='0' width='100%'>";
    $html .= "<tr style='background:#efefef;'>";
    foreach ($headers as $h) {
        $html .= "<th>$h</th>";
    }
    $html .= "</tr>";

    foreach ($rows as $r) {
        $html .= "<tr>";
        foreach ($r as $cell) {
            $html .= "<td>" . htmlspecialchars($cell) . "</td>";
        }
        $html .= "</tr>";
    }
    $html .= "</table>";

    $dompdf->loadHtml($html);
    $dompdf->setPaper("A4", "landscape");
    $dompdf->render();

    if (!$filename) {
        $filename = 'report_' . time() . '.pdf';
    }
    $filePath = __DIR__ . '/../../uploads/' . $filename;

    file_put_contents($filePath, $dompdf->output());

    return $filePath;
}

