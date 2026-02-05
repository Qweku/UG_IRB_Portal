<?php
declare(strict_types=1);

require_once '../includes/auth_check.php';
require_once '../../includes/config/database.php';
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
        $data[$field] = clean($_POST[$field] ?? null);
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

function clean(?string $value): ?string
{
    return isset($value) ? trim($value) : null;
}

/* ==========================================================
| STUDY DATA NORMALIZATION
========================================================== */
function mapStudyData(array $post): array
{
    return [
        'study_number' => clean($post['study_number'] ?? null),
        'ref_number' => clean($post['ref_number'] ?? null),
        'expiration_date' => clean($post['exp_date'] ?? null),
        'protocol_title' => clean($post['protocol_title'] ?? null),
        'sponsor' => clean($post['sponsor'] ?? null),
        'active' => ($post['actv'] ?? ''),
        'review_type' => clean($post['review_type'] ?? null),
        'status' => clean($post['status'] ?? null),
        'risk_category' => clean($post['riskCat'] ?? null),
        'patients_enrolled' => clean($post['ape'] ?? null),
        'init_enroll' => clean($post['currentEnroll'] ?? null),
        'on_agenda_date' => clean($post['oad'] ?? null),
        'irb_of_record' => clean($post['ior'] ?? null),
        'cr_required' => clean($post['cRequired'] ?? null),
        'renewal_cycle' => clean($post['rcm'] ?? null),
        'date_received' => clean($post['date_received'] ?? null),
        'first_irb_review' => clean($post['first_irb_review'] ?? null),
        'approval_date' => clean($post['original_approval'] ?? null),
        'last_irb_review' => clean($post['last_seen_by_irb'] ?? null),
        'last_seen_by_irb' => clean($post['lsbr'] ?? null),
        'last_renewal_date' => clean($post['last_irb_renewal'] ?? null),
        'internal_notes' => clean($post['internal_notes'] ?? null),
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
            'name' => clean($p['name']),
            'role' => clean($p['role']),
            'title' => clean($p['title']),
            'start_date' => clean($p['start_date']),
            'company_name' => clean($p['company_name']),
            'email' => clean($p['email']),
            'phone' => clean($p['phone']),
            'comments' => clean($p['comments']),
            'contact_id' => ($p['contact_id'] ?? null),
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

// CSRF validation
if (!isset($_POST['csrf_token']) || !csrf_validate_token($_POST['csrf_token'])) {
    jsonError("Invalid CSRF token", 403);
}

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

foreach (['study_number', 'ref_number', 'expiration_date', 'protocol_title', 'sponsor', 'date_received'] as $field) {
    if (empty($data[$field])) jsonError("Missing required field: $field");
}

$isEdit = !empty($_POST['study_id']);
$studyId = $isEdit ? (int)$_POST['study_id'] : null;

try {
    $conn->beginTransaction();

    // Get next meeting date
    $stmt = $conn->prepare("SELECT meeting_date FROM irb_meetings WHERE meeting_date > NOW() ORDER BY meeting_date ASC LIMIT 1");
    $stmt->execute();
    $nextMeeting = $stmt->fetchColumn();

    error_log("Next meeting date: " . $nextMeeting);

    if ($isEdit) {
        /* ---------------- UPDATE STUDY ---------------- */
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

        if (!empty($personnel)) {
            $conn->prepare("DELETE FROM study_personnel WHERE study_id=?")->execute([$studyId]);
        }
    } else {
        /* ---------------- INSERT STUDY ---------------- */
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

        $studyId = (int)$conn->lastInsertId();
    }

    /* ---------------- PERSONNEL INSERT ---------------- */
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
    }

     // ================= SAE (EDIT ONLY) =================
    if ($isEdit) {
        processSAESubmission($conn, $studyId);
    }

    handleUploads($conn, $studyId);

    $conn->commit();
    jsonSuccess($isEdit ? 'Study updated successfully' : 'Study created successfully');
} catch (Exception $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    error_log("Study Handler Error: " . $e->getMessage());
    jsonError("Failed to save study. Please try again.");
}
