<?php
require_once '../../includes/config/database.php';
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
$email = '';
$main_phone = '';
$comments = '';

// Meeting Dates
$irb_meetings = [];

/**
 * Process form submission for adding a new study and personnel
 * Handles validation, database insertion in a transaction, and user feedback
 */
function processFormSubmission()
{
    // Check for at least one personnel
    if (!isset($_POST['personnel']) || count($_POST['personnel']) == 0) {
        echo json_encode(['status' => 'error', 'message' => 'At least one study personnel must be added.']);
        return;
    }

    // Check if edit mode
    $is_edit = isset($_POST['study_id']) && !empty($_POST['study_id']);
    $study_id = $is_edit ? (int)$_POST['study_id'] : null;

    // Sanitize and validate input data
    $required_fields = ['study_number', 'ref_number', 'exp_date', 'protocol_title', 'sponsor', 'actv', 'date_received'];
    $data = [];
    $pi_names = [];

    foreach ($required_fields as $field) {
        $data[$field] = trim($_POST[$field] ?? '');
        if (empty($data[$field])) {
            echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
            return;
        }
    }

    // Additional optional fields
    $optional_fields = [
        'review_type',
        'status',
        'riskCat',
        'ape',
        'currentEnroll',
        'oad',
        'ior',
        'cRequired',
        'rcm',
        'first_irb_review',
        'original_approval',
        'last_seen_by_irb',
        'last_irb_renewal',        
        'nos',
        'noc',
        'isoa',
        'internal_notes'
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
                ':date_received' => $data['date_received'],
                ':first_irb_review' => $data['first_irb_review'],
                ':original_approval' => $data['original_approval'],
                ':last_seen_by_irb' => $data['last_seen_by_irb'],
                ':last_irb_renewal' => $data['last_irb_renewal'],
                ':internal_notes' => $data['internal_notes'],
                ':study_id' => $study_id
            ]);

            // Delete existing personnel
            $stmt = $conn->prepare("DELETE FROM study_personnel WHERE study_id = ?");
            $stmt->execute([$study_id]);

            // Insert personnel
            $pi_names = [];
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

                $pi_names[] = $p_data['name'];

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
            $pi_string = implode(', ', $pi_names);

            $stmt = $conn->prepare("UPDATE studies SET pi = :pi WHERE id = :study_id");

            $stmt->execute([

                ':pi' => $pi_string,

                ':study_id' => $study_id

            ]);

            $nextMeeting = getNextMeetingDate();

            // Check if agenda item exists
            $stmt = $conn->prepare("SELECT id FROM agenda_items WHERE irb_number = ?");
            $stmt->execute([$data['study_number']]);
            $agenda_id = $stmt->fetchColumn();

            if ($agenda_id) {
                // Update existing agenda item
                $stmt = $conn->prepare("UPDATE agenda_items SET
                    agenda_category = 'Expedited', agenda_group = 'Expedited', expedite = 1, title = :title,
                    renewal = :renewal, review = :review, meeting_date = :meeting_date, reference_number = :reference_number, pi = :pi
                    WHERE id = :id");

                $stmt->execute([
                    ':title' => $data['protocol_title'],
                    ':renewal' => $data['last_irb_renewal'],
                    ':review' => $data['last_seen_by_irb'],
                    ':meeting_date' => $nextMeeting,
                    ':reference_number' => $data['ref_number'],
                    ':pi' => $pi_string,
                    ':id' => $agenda_id
                ]);
            } else {
                // Insert new agenda item
                $stmt = $conn->prepare("INSERT INTO agenda_items (
                    irb_number, agenda_category, agenda_group, expedite, title,
                    renewal, review, meeting_date, reference_number, pi
                ) VALUES (
                    :irb_number, 'Expedited', 'Expedited', 1 , :title, :renewal, :review, :meeting_date, :reference_number, :pi
                )");

                $stmt->execute([
                    ':irb_number' => $data['study_number'],
                    ':title' => $data['protocol_title'],
                    ':renewal' => $data['last_irb_renewal'],
                    ':review' => $data['last_seen_by_irb'],
                    ':meeting_date' => $nextMeeting,
                    ':reference_number' => $data['ref_number'],
                    ':pi' => $pi_string,
                ]);
            }
        } else {
            $nextMeeting = getNextMeetingDate();
            // Insert study
            $stmt = $conn->prepare("INSERT INTO studies (
                protocol_number, ref_num, expiration_date, title, sponsor_displayname,
                study_active, review_type, study_status, risk_category, patients_enrolled,
                init_enroll, on_agenda_date, irb_of_record, cr_required, renewal_cycle,
                date_received, first_irb_review, approval_date, last_irb_review, meeting_date,
                last_renewal_date, remarks
            ) VALUES (
                :study_number, :ref_number, :exp_date, :protocol_title, :sponsor,
                :active, :review_type, :status, :risk_category, :approval_patient_enrollment,
                :current_enrolled, :on_agenda_date, :irb_of_record, :cr_required,
                :renewal_cycle, :date_received, :first_irb_review, :original_approval,
                :last_seen_by_irb, :meeting_date, :last_irb_renewal, :internal_notes
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
                ':date_received' => $data['date_received'],
                ':first_irb_review' => $data['first_irb_review'],
                ':original_approval' => $data['original_approval'],
                ':last_seen_by_irb' => $data['last_seen_by_irb'],
                ':meeting_date' => $nextMeeting,
                ':last_irb_renewal' => $data['last_irb_renewal'],
                ':internal_notes' => $data['internal_notes']
            ]);

            $study_id = $conn->lastInsertId();



            // Insert personnel
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

                if (empty($p_data['name'])) {
                    continue; // Skip invalid
                }


                $pi_names[] = $p_data['name'];


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

            $pi_string = implode(', ', $pi_names);

            // Insert into agenda items
            $stmt = $conn->prepare("INSERT INTO agenda_items (
                irb_number, agenda_category, agenda_group, expedite, title,
                 renewal, review, meeting_date, reference_number, pi
            ) VALUES (
                :irb_number, 'Expedited', 'Expedited', 1 , :title, :renewal, :review, :meeting_date, :reference_number, :pi
            )");

            $stmt->execute([
                ':irb_number' => $data['study_number'],
                ':title' => $data['protocol_title'],
                ':renewal' => $data['lir'],
                ':review' => $data['lsbi'],
                ':meeting_date' => $nextMeeting,
                ':reference_number' => $data['ref_number'],
                ':pi' => $pi_string,
            ]);


            // Update PI in studies
            $stmt = $conn->prepare("UPDATE studies SET pi = :pi WHERE id = :study_id");

            $stmt->execute([
                ':pi' => $pi_string,
                ':study_id' => $study_id
            ]);
        }

        // Handle file uploads
        if ($study_id && isset($_FILES['initialApplication'])) {
            $uploadDir = '../../uploads/';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    throw new Exception("Failed to create upload directory: $uploadDir");
                }
            }
            if (!is_writable($uploadDir)) {
                throw new Exception("Upload directory is not writable: $uploadDir");
            }
            $files = $_FILES['initialApplication'];
            $file_count = count($files['name']);
            for ($i = 0; $i < $file_count; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $fileName = basename($files['name'][$i]);
                    $filePath = $uploadDir . uniqid() . '_' . $fileName;
                    if (move_uploaded_file($files['tmp_name'][$i], $filePath)) {
                        $comment = isset($_POST['file_comments'][$i]) ? trim($_POST['file_comments'][$i]) : '';
                        $exclude = isset($_POST['dont_include'][$i]) ? 1 : 0;
                        $stmt = $conn->prepare("INSERT INTO documents (study_id, document_type, file_name, file_path, comments, uploaded_at, exclude_from_agenda) VALUES (?, 'initial_application', ?, ?, ?, NOW(), ?)");
                        if (!$stmt->execute([$study_id, $fileName, $filePath, $comment, $exclude])) {
                            throw new Exception("Failed to insert document record for file: $fileName");
                        }
                    } else {
                        throw new Exception("Failed to move uploaded file: $fileName");
                    }
                } else {
                    $uploadError = $files['error'][$i];
                    $message = "Upload error for file " . ($i + 1) . ": ";
                    switch ($uploadError) {
                        case UPLOAD_ERR_INI_SIZE:
                            $message .= "The uploaded file exceeds the maximum allowed size.";
                            break;
                        case UPLOAD_ERR_FORM_SIZE:
                            $message .= "The uploaded file exceeds the form's maximum file size limit.";
                            break;
                        case UPLOAD_ERR_PARTIAL:
                            $message .= "The file was only partially uploaded.";
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            $message .= "No file was uploaded.";
                            break;
                        case UPLOAD_ERR_NO_TMP_DIR:
                            $message .= "Missing temporary folder.";
                            break;
                        case UPLOAD_ERR_CANT_WRITE:
                            $message .= "Failed to write file to disk.";
                            break;
                        case UPLOAD_ERR_EXTENSION:
                            $message .= "File upload stopped by a PHP extension.";
                            break;
                        default:
                            $message .= "Unknown upload error (code: $uploadError).";
                            break;
                    }
                    throw new Exception($message);
                }
            }
        }

        // Update exclude_from_agenda for existing documents during edit
        if ($is_edit && isset($_POST['exclude_from_agenda'])) {
            foreach ($_POST['exclude_from_agenda'] as $doc_id => $value) {
                $exclude = $value ? 1 : 0;
                $stmt = $conn->prepare("UPDATE documents SET exclude_from_agenda = ? WHERE id = ?");
                $stmt->execute([$exclude, $doc_id]);
            }
        }

        $conn->commit();
        $message = $is_edit ? 'Study/Protocol has been updated successfully!' : 'New study/protocol have been saved successfully!';
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
