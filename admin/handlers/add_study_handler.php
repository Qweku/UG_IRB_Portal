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

// Handle save draft action
if (isset($_POST['action']) && $_POST['action'] === 'save_draft') {
    handleSaveDraft($conn);
}

// Handle get user draft action
if (isset($_POST['action']) && $_POST['action'] === 'get_user_draft') {
    handleGetUserDraft($conn);
}

$nextMeeting = '';


/* ==========================================================
 | SAVE DRAFT HANDLER
 ========================================================== */
function handleSaveDraft(PDO $conn): void
{
    $personnelRaw = $_POST['personnel'] ?? [];
    $personnel = decodePersonnel($personnelRaw);
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
    
    $isEdit = !empty($_POST['study_id']);
    $studyId = $isEdit ? (int)$_POST['study_id'] : null;
    $currentStep = isset($_POST['current_step']) ? (int)$_POST['current_step'] : 1;
    
    // Check if user already has an existing draft
    if (!$isEdit && isset($_SESSION['user_id'])) {
        $userId = (int)$_SESSION['user_id'];
        $checkStmt = $conn->prepare("
            SELECT id FROM studies 
            WHERE is_draft = 1 
            AND created_by = ?
            LIMIT 1
        ");
        $checkStmt->execute([$userId]);
        $existingDraft = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingDraft) {
            // User has an existing draft - update it instead of inserting new
            $isEdit = true;
            $studyId = (int)$existingDraft['id'];
        }
    }
    
    try {
        $conn->beginTransaction();
        
        if ($isEdit) {
            /* ---------------- UPDATE STUDY ---------------- */
            // Ensure created_by column exists - use conditional check instead of ALTER to avoid auto-commit
            $checkStmt = $conn->prepare("SHOW COLUMNS FROM studies LIKE 'created_by'");
            $checkStmt->execute();
            if (!$checkStmt->fetch()) {
                $conn->exec("ALTER TABLE studies ADD COLUMN created_by INT UNSIGNED NULL");
            }
            
            $createdBy = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
            
            $stmt = $conn->prepare("
                UPDATE studies SET
                    protocol_number=?, ref_num=?, expiration_date=?, title=?, sponsor_displayname=?,
                    study_active=?, review_type=?, study_status=?, risk_category=?,
                    patients_enrolled=?, init_enroll=?, on_agenda_date=?, irb_of_record=?,
                    cr_required=?, renewal_cycle=?, date_received=?, first_irb_review=?,
                    approval_date=?, last_irb_review=?, last_renewal_date=?, remarks=?,
                    pi=?, reviewers=?, admins=?, cols=?, current_step=?, created_by=?
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
                $currentStep,
                $createdBy,
                $studyId
            ]);
            
            // Update personnel
            if (!empty($personnel)) {
                $conn->prepare("DELETE FROM study_personnel WHERE study_id=?")->execute([$studyId]);
            }
        } else {
            /* ---------------- INSERT STUDY (DRAFT) ---------------- */
            // Ensure created_by column exists - use conditional check instead of ALTER to avoid auto-commit
            $checkStmt = $conn->prepare("SHOW COLUMNS FROM studies LIKE 'created_by'");
            $checkStmt->execute();
            if (!$checkStmt->fetch()) {
                $conn->exec("ALTER TABLE studies ADD COLUMN created_by INT UNSIGNED NULL");
            }
            
            $createdBy = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
            
            $stmt = $conn->prepare("
                INSERT INTO studies (
                    protocol_number, ref_num, expiration_date, title, sponsor_displayname,
                    study_active, review_type, study_status, risk_category, patients_enrolled,
                    init_enroll, on_agenda_date, irb_of_record, irb_code, cr_required,
                    renewal_cycle, date_received, first_irb_review, approval_date,
                    last_irb_review, last_renewal_date, remarks,
                    pi, reviewers, admins, cols, current_step, is_draft, created_by
                ) VALUES (
                    ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1,?
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
                $data['last_irb_review'],
                $data['last_renewal_date'],
                $data['internal_notes'],
                $roles['pi'],
                $roles['reviewers'],
                $roles['admins'],
                $roles['cols'],
                $currentStep,
                $createdBy
            ]);
            
            $studyId = (int)$conn->lastInsertId();
        }
        
        /* ---------------- PERSONNEL INSERT (DRAFT) ---------------- */
        if (!empty($personnel)) {
            $stmt = $conn->prepare("
                INSERT INTO study_personnel
                (study_id, contact_id, name, role, title, start_date, company_name, email, phone, comments)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($personnel as $p) {
                $stmt->execute([
                    $studyId,
                    $p['contact_id'] ?? null,
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
        }
        
        // Commit the transaction - only if there's an active transaction
        if ($conn->inTransaction()) {
            $conn->commit();
        }
        
        // Return success response with proper format
        echo json_encode([
            'status' => 'success', 
            'success' => true,
            'message' => 'Draft saved successfully',
            'study_id' => $studyId,
            'current_step' => $currentStep
        ]);
        exit;
        
    } catch (Exception $e) {
        // Only rollback if there's an active transaction
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Save draft error: " . $e->getMessage());
        // Return error response with proper format
        echo json_encode([
            'status' => 'error', 
            'success' => false,
            'message' => 'Error saving draft: ' . $e->getMessage()
        ]);
        exit;
    }
}

/* ==========================================================
 | GET USER DRAFT HANDLER
 ========================================================== */
function handleGetUserDraft(PDO $conn): void
{
    // Check if user is logged in
    if (empty($_SESSION['user_id'])) {
        jsonError("User not logged in", 401);
    }
    
    $userId = (int)$_SESSION['user_id'];
    
    try {
        // First ensure the created_by column exists
        try {
            $conn->exec("ALTER TABLE studies ADD COLUMN created_by INT UNSIGNED NULL");
        } catch (Exception $e) {
            // Column might already exist, ignore error
        }
        
        // Fetch the current user's draft
        $stmt = $conn->prepare("
            SELECT * FROM studies 
            WHERE is_draft = 1 
            AND created_by = ?
            ORDER BY id DESC 
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        $draft = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$draft) {
            echo json_encode([
                'status' => 'success',
                'has_draft' => false,
                'message' => 'No draft found'
            ]);
            exit;
        }
        
        // Fetch personnel for this draft
        $personnelStmt = $conn->prepare("
            SELECT * FROM study_personnel WHERE study_id = ?
        ");
        $personnelStmt->execute([$draft['id']]);
        $personnel = $personnelStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Map study data to form fields
        $studyData = [
            // Step 1 fields
            'study_number' => $draft['protocol_number'] ?? '',
            'ref_number' => $draft['ref_num'] ?? '',
            'exp_date' => $draft['expiration_date'] ?? '',
            'protocol_title' => $draft['title'] ?? '',
            
            // Step 3 fields
            'sponsor' => $draft['sponsor_displayname'] ?? '',
            'status' => $draft['study_status'] ?? '',
            'actv' => $draft['study_active'] ?? '',
            'review_type' => $draft['review_type'] ?? '',
            'riskCat' => $draft['risk_category'] ?? '',
            'ape' => $draft['patients_enrolled'] ?? '',
            'currentEnroll' => $draft['init_enroll'] ?? '',
            'ior' => $draft['irb_of_record'] ?? '',
            'oad' => $draft['on_agenda_date'] ?? '',
            'cRequired' => $draft['cr_required'] ?? '',
            
            // Step 4 fields
            'rcm' => $draft['renewal_cycle'] ?? '',
            'date_received' => $draft['date_received'] ?? '',
            'first_irb_review' => $draft['first_irb_review'] ?? '',
            'original_approval' => $draft['approval_date'] ?? '',
            'last_seen_by_irb' => $draft['last_irb_review'] ?? '',
            'last_irb_renewal' => $draft['last_renewal_date'] ?? '',
            'internal_notes' => $draft['remarks'] ?? '',
            'lsbr' => $draft['last_seen_by_irb'] ?? '',
            
            // Hidden fields
            'study_id' => $draft['id'] ?? '',
            'current_step' => $draft['current_step'] ?? 1
        ];
        
        echo json_encode([
            'status' => 'success',
            'has_draft' => true,
            'study_id' => $draft['id'],
            'current_step' => (int)($draft['current_step'] ?? 1),
            'study_data' => $studyData,
            'personnel' => $personnel
        ]);
        exit;
        
    } catch (Exception $e) {
        error_log("Get user draft error: " . $e->getMessage());
        echo json_encode([
            'status' => 'error', 
            'message' => 'Error fetching draft: ' . $e->getMessage()
        ]);
        exit;
    }
}



$db = new Database();
$conn = $db->connect();
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);



$personnelRaw = $_POST['personnel'] ?? [];
if (!$personnelRaw) {
    // Allow saving draft without personnel, but personnel is required for full submission
    $personnelRaw = [];
}

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

// Check if user has an existing draft when submitting a new study
if (!$isEdit && isset($_SESSION['user_id'])) {
    $userId = (int)$_SESSION['user_id'];
    $checkStmt = $conn->prepare("
        SELECT id FROM studies 
        WHERE is_draft = 1 
        AND created_by = ?
        LIMIT 1
    ");
    $checkStmt->execute([$userId]);
    $existingDraft = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingDraft) {
        // User has an existing draft - update it instead of inserting new
        $isEdit = true;
        $studyId = (int)$existingDraft['id'];
    }
}

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
                pi=?, reviewers=?, admins=?, cols=?, is_draft=0
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
                pi, reviewers, admins, cols, is_draft
            ) VALUES (
                ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?
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
            $roles['cols'],
            0
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
