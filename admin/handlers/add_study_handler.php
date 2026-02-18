<?php

declare(strict_types=1);

// Load configuration FIRST - before any session operations
require_once '../../config.php';

// Start session with consistent session name
if (session_status() === PHP_SESSION_NONE) {
    $session_name = 'ug_irb_session';
    session_name($session_name);
    session_start();
}

require_once '../includes/auth_check.php';
require_once '../../includes/functions/csrf.php';

header('Content-Type: application/json');


/* ==========================================================
| SAE SUBMISSION HANDLER (EDIT ONLY)
========================================================== */
function processSAESubmission(PDO $conn, int $studyId): void
{
    // Check if SAE section was submitted at all
    if (empty($_POST['sae_description']) || empty($_POST['sae_type_of_event'])) {
        return; // No SAE submission → silently skip
    }

    // Required SAE fields
    $required = [
        'sae_description' => 'description',
        'sae_type_of_event' => 'type_of_event',
    ];

    $data = [
        'protocol_id' => $studyId
    ];

    foreach ($required as $postKey => $dbKey) {
        $value = trim($_POST[$postKey] ?? '');
        if ($value === '') {
            throw new Exception("Missing required SAE field");
        }
        $data[$dbKey] = $value;
    }

    // Optional SAE fields
    $optionalFields = [
        'follow_up_report',
        'original_sae_number',
        'secondary_sae',
        'internal_sae_number',
        'ind_report_number',
        'medwatch_report_filed',
        'medwatch_number',
        'local_event',
        'location',
        'study_related',
        'patient_status',
        'age',
        'sex',
        'patient_identifier',
        'date_of_event',
        'date_received',
        'date_pi_aware',
        'signed_by_pi',
        'date_signed',
        'risks_altered',
        'new_consent_required'
    ];

    foreach ($optionalFields as $field) {
        $data[$field] = clean($_POST[$field] ?? '', '');
    }

    // Build dynamic insert
    $columns = array_keys($data);
    $placeholders = array_fill(0, count($columns), '?');

    $sql = "
        INSERT INTO saes (" . implode(',', $columns) . ", created_at, updated_at)
        VALUES (" . implode(',', $placeholders) . ", NOW(), NOW())
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute(array_values($data));

    error_log("SAE added for study ID: {$studyId}");
}


/* ==========================================================
| Utility Helpers
========================================================== */
function jsonError(string $message, int $code = 400): void
{
    http_response_code($code);
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

function jsonSuccess(string $message): void
{
    echo json_encode(['status' => 'success', 'message' => $message]);
    exit;
}

function clean(?string $value, string $default = ''): string
{
    $value = isset($value) ? trim($value) : '';
    return $value === '' ? $default : $value;
}


/* ==========================================================
| STUDY DATA NORMALIZATION
========================================================== */
function mapStudyData(array $post): array
{
    return [
        'study_number'        => clean($post['study_number'] ?? '', 'N/A'),
        'ref_number'          => clean($post['ref_number'] ?? '', 'N/A'),
        'expiration_date'     => clean($post['exp_date'] ?? '', date('Y-m-d')),
        'protocol_title'      => clean($post['protocol_title'] ?? '', 'Untitled Study'),
        'sponsor'             => clean($post['sponsor'] ?? '', 'Unknown Sponsor'),
        'active'              => clean($post['actv'] ?? '', 'No'),
        'review_type'         => clean($post['review_type'] ?? '', 'Expedited'),
        'status'              => clean($post['status'] ?? '', 'Active'),
        'risk_category'       => clean($post['riskCat'] ?? '', 'Minimal Risk'),
        'patients_enrolled'   => clean($post['ape'] ?? '', '0'),
        'init_enroll'         => clean($post['currentEnroll'] ?? '', '0'),
        'on_agenda_date'      => clean($post['oad'] ?? '', date('Y-m-d')),
        'irb_of_record'       => clean($post['ior'] ?? '', ''),
        'cr_required'         => clean($post['cRequired'] ?? '', '0'),
        'renewal_cycle'       => clean($post['rcm'] ?? '', 'Annual'),
        'date_received'       => clean($post['date_received'] ?? '', date('Y-m-d')),
        'first_irb_review'    => clean($post['first_irb_review'] ?? '', ''),
        'approval_date'       => clean($post['original_approval'] ?? '', ''),
        'last_irb_review'     => clean($post['last_seen_by_irb'] ?? '', ''),
        'last_seen_by_irb'    => clean($post['lsbr'] ?? '', ''),
        'last_renewal_date'   => clean($post['last_irb_renewal'] ?? '', ''),
        'internal_notes'      => clean($post['internal_notes'] ?? '', ''),
    ];
}


/* ==========================================================
| PERSONNEL PARSING
========================================================== */
function decodePersonnel(array $raw): array
{
    return array_values(array_filter(array_map(function ($row) {
        $p = json_decode($row, true);
        return $p && !empty($p['name']) ? [
            'name' => clean($p['name'], 'Unknown'),
            'role' => clean($p['role'], 'PI'),
            'title' => clean($p['title'], ''),
            'start_date' => clean($p['start_date'], date('Y-m-d')),
            'company_name' => clean($p['company_name'], ''),
            'email' => clean($p['email'], ''),
            'phone' => clean($p['phone'], ''),
            'comments' => clean($p['comments'], ''),
            'contact_id' => $p['contact_id'] ?? null,
        ] : null;
    }, $raw)));
}

function extractRoles(array $personnel): array
{
    $roles = [
        'pi' => '',
        'reviewers' => [],
        'admins' => [],
        'cols' => [],
    ];

    foreach ($personnel as $p) {
        match ($p['role']) {
            'PI' => $roles['pi'] = $p['name'],
            'Reviewer' => $roles['reviewers'][] = $p['name'],
            'Admin' => $roles['admins'][] = $p['name'],
            'Co-PI' => $roles['cols'][] = $p['name'],
            default => null
        };
    }

    return [
        'pi' => $roles['pi'],
        'reviewers' => implode(', ', $roles['reviewers']),
        'admins' => implode(', ', $roles['admins']),
        'cols' => implode(', ', $roles['cols']),
    ];
}

/* ==========================================================
| FILE UPLOAD HANDLER
========================================================== */
function handleUploads(PDO $conn, int $studyId): void
{
    if (!isset($_FILES['initialApplication'])) return;

    $allowedTypes = [
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];

    $uploadDir = '../../uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    foreach ($_FILES['initialApplication']['tmp_name'] as $i => $tmp) {
        if ($_FILES['initialApplication']['error'][$i] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload failed");
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $type = finfo_file($finfo, $tmp);
        finfo_close($finfo);

        if (!in_array($type, $allowedTypes)) {
            throw new Exception("Invalid file type uploaded");
        }

        $original = preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['initialApplication']['name'][$i]);
        $path = $uploadDir . uniqid('doc_') . '_' . $original;

        move_uploaded_file($tmp, $path);

        $stmt = $conn->prepare("
            INSERT INTO documents (study_id, document_type, file_name, file_path, uploaded_at)
            VALUES (?, 'initial_application', ?, ?, NOW())
        ");
        $stmt->execute([$studyId, $original, $path]);
    }
}

/* ==========================================================
| MAIN HANDLER
========================================================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError("Invalid request method", 405);
}

// CSRF validation already done above (lines 32-37)
// Note: csrf_validate() clears the token after validation to prevent reuse

$nextMeeting = '';



$db = new Database();
$conn = $db->connect();
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);



$personnelRaw = $_POST['personnel'] ?? [];
if (!$personnelRaw) jsonError("At least one personnel is required");

error_log("Raw personnel input: " . json_encode($personnelRaw));

$personnel = decodePersonnel($personnelRaw);
error_log("Decoded personnel: " . json_encode($personnel));

$roles = extractRoles($personnel);

$data = mapStudyData($_POST);

$irb_code = '';

// Fetch IRB code based on selected user institution id
if (isset($_SESSION['institution_id'])) {
    $institutionId = (int)$_SESSION['institution_id'];
    $stmt = $conn->prepare("SELECT institution_name FROM institutions WHERE id = ?");
    $stmt->execute([$institutionId]);
    $irb_code = $stmt->fetchColumn() ?: '';
}

// foreach (['study_number', 'ref_number', 'expiration_date', 'protocol_title', 'sponsor', 'date_received'] as $field) {
//     if (empty($data[$field])) jsonError("Missing required field: $field");
// }

$isEdit = !empty($_POST['study_id']);
$studyId = $isEdit ? (int)$_POST['study_id'] : null;

try {
    $conn->beginTransaction();

    error_log("DEBUG: Starting study insert, isEdit=" . ($isEdit ? 'true' : 'false'));

    // Get next meeting date
    $stmt = $conn->prepare("SELECT meeting_date FROM irb_meetings WHERE meeting_date > NOW() ORDER BY meeting_date ASC LIMIT 1");
    $stmt->execute();
    $nextMeeting = $stmt->fetchColumn();

    error_log("DEBUG: Next meeting date: " . $nextMeeting);

    if ($isEdit) {
        /* ---------------- UPDATE STUDY ---------------- */
        error_log("DEBUG: About to UPDATE studies table, studyId: " . $studyId);
        $stmt = $conn->prepare("
            UPDATE studies SET
                protocol_number=?, ref_num=?, expiration_date=?, title=?, sponsor_displayname=?,
                study_active=?, review_type=?, study_status=?, risk_category=?,
                patients_enrolled=?, init_enroll=?, on_agenda_date=?, irb_of_record=?,
                cr_required=?, renewal_cycle=?, date_received=?, first_irb_review=?,
                approval_date=?, last_irb_review=?, last_renewal_date=?, remarks=?,
                pi=?, reviewers=?, admins=?, cols=?
            WHERE id=?
        ");

        $stmt->execute([
            $data['study_number'],
            $data['ref_number'],
            $data['expiration_date'],
            $data['protocol_title'],
            $data['sponsor'],
            $data['active'],
            $data['review_type'],
            $data['status'],
            $data['risk_category'],
            $data['patients_enrolled'],
            $data['init_enroll'],
            $data['on_agenda_date'],
            $data['irb_of_record'],
            $data['cr_required'],
            $data['renewal_cycle'],
            $data['date_received'],
            $data['first_irb_review'],
            $data['approval_date'],
            $data['last_irb_review'],
            $data['last_renewal_date'],
            $data['internal_notes'],
            $roles['pi'],
            $roles['reviewers'],
            $roles['admins'],
            $roles['cols'],
            $studyId
        ]);
        error_log("DEBUG: studies UPDATE complete");

        if (!empty($personnel)) {
            $conn->prepare("DELETE FROM study_personnel WHERE study_id=?")->execute([$studyId]);
        }
    } else {
        /* ---------------- INSERT STUDY ---------------- */
        error_log("DEBUG: About to insert into studies table");
        $stmt = $conn->prepare("
            INSERT INTO studies (
                protocol_number, ref_num, expiration_date, title, sponsor_displayname,
                study_active, review_type, study_status, risk_category, patients_enrolled,
                init_enroll, on_agenda_date, irb_of_record, irb_code, cr_required,
                renewal_cycle, date_received, first_irb_review, approval_date, meeting_date,
                last_irb_review, last_renewal_date, remarks,
                pi, reviewers, admins, cols
            ) VALUES (
                ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?
            )
        ");

        $stmt->execute([
            $data['study_number'],
            $data['ref_number'],
            $data['expiration_date'],
            $data['protocol_title'],
            $data['sponsor'],
            $data['active'],
            $data['review_type'],
            $data['status'],
            $data['risk_category'],
            $data['patients_enrolled'],
            $data['init_enroll'],
            $data['on_agenda_date'],
            $data['irb_of_record'],
            $irb_code,
            $data['cr_required'],
            $data['renewal_cycle'],
            $data['date_received'],
            $data['first_irb_review'],
            $data['approval_date'],
            $nextMeeting,
            $data['last_irb_review'],
            $data['last_renewal_date'],
            $data['internal_notes'],
            $roles['pi'],
            $roles['reviewers'],
            $roles['admins'],
            $roles['cols']
        ]);
        error_log("DEBUG: studies insert complete, lastInsertId: " . $conn->lastInsertId());

        $studyId = (int)$conn->lastInsertId();
    }

    /* ---------------- PERSONNEL INSERT ---------------- */
    error_log("DEBUG: About to insert into study_personnel table, count: " . count($personnel));
    $stmt = $conn->prepare("
        INSERT INTO study_personnel
        (study_id, contact_id, name, role, title, start_date, company_name, email, phone, comments)
        VALUES (?, ?, ?,?,?,?,?,?,?,?)
    ");

    foreach ($personnel as $p) {
        $stmt->execute([
            $studyId,
            $p['contact_id'],
            $p['name'],
            $p['role'],
            $p['title'],
            $p['start_date'],
            $p['company_name'],
            $p['email'],
            $p['phone'],
            $p['comments']
        ]);
    }
    error_log("DEBUG: study_personnel insert complete");



    // Handle agenda items: Update if exists (for edits), insert if not
    $agendaExists = false;
    if ($isEdit) {
        // Check if agenda item exists for this study
        $stmt = $conn->prepare("SELECT id FROM agenda_items WHERE study_id = ?");
        $stmt->execute([$studyId]);
        $agendaExists = $stmt->fetchColumn();
    }

    if ($agendaExists) {
        // Update existing agenda item
        error_log("DEBUG: About to UPDATE agenda_items table");
        $stmt = $conn->prepare("
            UPDATE agenda_items SET
                irb_number = ?, agenda_category = 'Expedited', agenda_group = 'Expedited', expedite = 1,
                title = ?, renewal = ?, review = ?, meeting_date = ?, reference_number = ?, pi = ?
            WHERE study_id = ?
        ");
        $stmt->execute([
            $data['study_number'],
            $data['protocol_title'],
            $data['last_renewal_date'],
            $data['last_irb_review'],
            $nextMeeting,
            $data['ref_number'],
            $roles['pi'],
            $studyId
        ]);
    } else {
        // Insert new agenda item (for adds or if none exists for edits)
        error_log("DEBUG: About to INSERT into agenda_items table");
        $stmt = $conn->prepare("
            INSERT INTO agenda_items (
                irb_number, agenda_category, agenda_group, expedite, title, study_id,
                renewal, review, meeting_date, reference_number, pi
            ) VALUES (?, 'Expedited', 'Expedited', 1, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['study_number'],
            $data['protocol_title'],
            $studyId,
            $data['last_renewal_date'],
            $data['last_irb_review'],
            $nextMeeting,
            $data['ref_number'],
            $roles['pi']
        ]);
        error_log("DEBUG: agenda_items insert complete");
    }

    // ================= SAE (EDIT ONLY) =================
    if ($isEdit) {
        processSAESubmission($conn, $studyId);
    }

    handleUploads($conn, $studyId);

    $conn->commit();
    
    // Log the final response for debugging
    error_log("DEBUG: Handler completed successfully, returning success response");
    jsonSuccess($isEdit ? 'Study updated successfully' : 'Study created successfully');
} catch (Exception $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    error_log("Study Handler Error: " . $e->getMessage());
    error_log("Study Handler Stack Trace: " . $e->getTraceAsString());
    jsonError("Failed to save study. Please try again. Error: " . $e->getMessage());
}
