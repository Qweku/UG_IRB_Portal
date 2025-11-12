<?php
require_once '../../../includes/config/database.php';
header('Content-Type: application/json');

// New Study Input Form Content
$study_number = '';
$ref_number = '';
$exp_date = '';
$protocol_title = '';
$sponsor = '';
$active = '';
$review_type = '';
$status = '';
$risk_category = '';
$approval_patient_enrollment = '';
$current_enrolled = '';
$on_agenda_date = '';
$irb_of_record = '';
$cr_required = '';
$renewal_cycle = '';
$date_received = '';
$first_irb_review = '';
$original_approval = '';
$last_seen_by_irb = '';
$last_irb_renewal = '';
$number_of_saes = '';
$number_of_cpas = '';
$initial_summary_of_agenda = '';
$internal_notes = '';

// Study Personnel Input content
$name = '';
$staff_type = '';
$title = '';
$date_added = '';
$company_name = '';
$email = ''; '';
$main_phone = '';
$comments = '';

/**
 * Process form submission for adding a new study and personnel
 * Handles validation, database insertion in a transaction, and user feedback
 */
function processFormSubmission() {
    // Check for at least one personnel
    if (!isset($_POST['personnel']) || count($_POST['personnel']) == 0) {
        echo json_encode(['status' => 'error', 'message' => 'At least one study personnel must be added.']);
        return;
    }

    // Check if edit mode
    $is_edit = isset($_POST['study_id']) && !empty($_POST['study_id']);
    $study_id = $is_edit ? (int)$_POST['study_id'] : null;

    // Sanitize and validate input data
    $required_fields = ['study_number', 'ref_number', 'exp_date', 'protocol_title', 'sponsor', 'actv', 'dateReceived'];
    $data = [];

    foreach ($required_fields as $field) {
        $data[$field] = trim($_POST[$field] ?? '');
        if (empty($data[$field])) {
            echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
            return;
        }
    }

    // Additional optional fields
    $optional_fields = [
        'review_type', 'status', 'riskCat', 'ape', 'currentEnroll', 'oad', 'ior',
        'cRequired', 'rcm', 'fir', 'origApp', 'lsbi', 'lir', 'nos', 'noc', 'isoa', 'internalNotes'
    ];

    foreach ($optional_fields as $field) {
        $data[$field] = trim($_POST[$field] ?? '');
    }

    // Database operation with transaction
    try {
        $db = new Database();
        $conn = $db->connect();

        if (!$conn) {
            throw new Exception("Database connection failed");
        }

        $conn->beginTransaction();

        if ($is_edit) {
            // Update study
            $stmt = $conn->prepare("UPDATE studies SET
                protocol_number = :study_number, ref_num = :ref_number, expiration_date = :exp_date,
                title = :protocol_title, sponsor_displayname = :sponsor, study_active = :active,
                review_type = :review_type, study_status = :status, risk_category = :risk_category,
                patients_enrolled = :approval_patient_enrollment, init_enroll = :current_enrolled,
                on_agenda_date = :on_agenda_date, irb_of_record = :irb_of_record, cr_required = :cr_required,
                renewal_cycle = :renewal_cycle, date_received = :date_received, first_irb_review = :first_irb_review,
                approval_date = :original_approval, last_irb_review = :last_seen_by_irb,
                last_renewal_date = :last_irb_renewal, remarks = :internal_notes
                WHERE id = :study_id");

            $stmt->execute([
                ':study_number' => $data['study_number'],
                ':ref_number' => $data['ref_number'],
                ':exp_date' => $data['exp_date'],
                ':protocol_title' => $data['protocol_title'],
                ':sponsor' => $data['sponsor'],
                ':active' => $data['actv'],
                ':review_type' => $data['review_type'],
                ':status' => $data['status'],
                ':risk_category' => $data['riskCat'],
                ':approval_patient_enrollment' => $data['ape'],
                ':current_enrolled' => $data['currentEnroll'],
                ':on_agenda_date' => $data['oad'],
                ':irb_of_record' => $data['ior'],
                ':cr_required' => $data['cRequired'],
                ':renewal_cycle' => $data['rcm'],
                ':date_received' => $data['dateReceived'],
                ':first_irb_review' => $data['fir'],
                ':original_approval' => $data['origApp'],
                ':last_seen_by_irb' => $data['lsbi'],
                ':last_irb_renewal' => $data['lir'],
                ':internal_notes' => $data['internalNotes'],
                ':study_id' => $study_id
            ]);

            // Delete existing personnel
            $stmt = $conn->prepare("DELETE FROM study_personnel WHERE study_id = ?");
            $stmt->execute([$study_id]);
        } else {
            // Insert study
            $stmt = $conn->prepare("INSERT INTO studies (
                protocol_number, ref_num, expiration_date, title, sponsor_displayname,
                study_active, review_type, study_status, risk_category, patients_enrolled,
                init_enroll, on_agenda_date, irb_of_record, cr_required, renewal_cycle,
                date_received, first_irb_review, approval_date, last_irb_review,
                last_renewal_date, remarks
            ) VALUES (
                :study_number, :ref_number, :exp_date, :protocol_title, :sponsor,
                :active, :review_type, :status, :risk_category, :approval_patient_enrollment,
                :current_enrolled, :on_agenda_date, :irb_of_record, :cr_required,
                :renewal_cycle, :date_received, :first_irb_review, :original_approval,
                :last_seen_by_irb, :last_irb_renewal, :internal_notes
            )");

            $stmt->execute([
                ':study_number' => $data['study_number'],
                ':ref_number' => $data['ref_number'],
                ':exp_date' => $data['exp_date'],
                ':protocol_title' => $data['protocol_title'],
                ':sponsor' => $data['sponsor'],
                ':active' => $data['actv'],
                ':review_type' => $data['review_type'],
                ':status' => $data['status'],
                ':risk_category' => $data['riskCat'],
                ':approval_patient_enrollment' => $data['ape'],
                ':current_enrolled' => $data['currentEnroll'],
                ':on_agenda_date' => $data['oad'],
                ':irb_of_record' => $data['ior'],
                ':cr_required' => $data['cRequired'],
                ':renewal_cycle' => $data['rcm'],
                ':date_received' => $data['dateReceived'],
                ':first_irb_review' => $data['fir'],
                ':original_approval' => $data['origApp'],
                ':last_seen_by_irb' => $data['lsbi'],
                ':last_irb_renewal' => $data['lir'],
                ':internal_notes' => $data['internalNotes']
            ]);

            $study_id = $conn->lastInsertId();
        }

        // Insert personnel (for both insert and update, since we deleted for update)
        foreach ($_POST['personnel'] as $p_json) {
            $p = json_decode($p_json, true);
            if (!$p) continue;

            $p_data = [
                'name' => trim($p['name'] ?? ''),
                'role' => trim($p['staffType'] ?? ''),
                'title' => trim($p['title'] ?? ''),
                'start_date' => trim($p['dateAdded'] ?? ''),
                'company_name' => trim($p['companyName'] ?? ''),
                'email' => trim($p['email'] ?? ''),
                'phone' => trim($p['mainPhone'] ?? ''),
                'comments' => trim($p['comments'] ?? ''),
            ];

            if (empty($p_data['name']) || empty($p_data['role'])) {
                continue; // Skip invalid
            }

            $stmt = $conn->prepare("INSERT INTO study_personnel (
                study_id, name, role, title, start_date, company_name, email, phone, comments
            ) VALUES (
                :study_id, :name, :role, :title, :start_date, :company_name, :email, :phone, :comments
            )");

            $stmt->execute([
                ':study_id' => $study_id,
                ':name' => $p_data['name'],
                ':role' => $p_data['role'],
                ':title' => $p_data['title'],
                ':start_date' => $p_data['start_date'],
                ':company_name' => $p_data['company_name'],
                ':email' => $p_data['email'],
                ':phone' => $p_data['phone'],
                ':comments' => $p_data['comments']
            ]);
        }

        $conn->commit();
        $message = $is_edit ? 'Study and personnel have been updated successfully!' : 'New study and personnel have been saved successfully!';
        echo json_encode(['status' => 'success', 'message' => $message]);

    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Failed to save study. Please try again.']);
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("General error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'An error occurred. Please try again.']);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    processFormSubmission();
}

?>