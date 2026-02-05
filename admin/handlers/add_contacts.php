<?php
require_once '../includes/auth_check.php';
error_log("Starting add_contacts.php script");
require_once '../../includes/config/database.php';
header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->connect();
    error_log("Database connection established");
    $conn->beginTransaction();
    error_log("Transaction started");

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $isUpdate = !empty($_POST['id']);

    // -----------------------------
    // Collect & sanitize input
    // -----------------------------
    $data = [
        'title'               => trim($_POST['title'] ?? null),
        'first_name'          => trim($_POST['first_name'] ?? ''),
        'middle_name'         => trim($_POST['middle_name'] ?? null),
        'last_name'           => trim($_POST['last_name'] ?? ''),
        'logon_name'         => trim($_POST['logon_name'] ?? null),
        'suffix'              => trim($_POST['suffix'] ?? null),
        'contact_type'        => trim($_POST['contact_type'] ?? null),
        'company_dept_name'   => trim($_POST['company_dept_name'] ?? null),
        'active'              => isset($_POST['active']) ? 1 : 0,
        'specialty_1'         => trim($_POST['specialty_1'] ?? null),
        'specialty_2'         => trim($_POST['specialty_2'] ?? null),
        'research_education'  => trim($_POST['research_education'] ?? null),
        'street_address_1'    => trim($_POST['street_address_1'] ?? null),
        'street_address_2'    => trim($_POST['street_address_2'] ?? null),
        'city'                => trim($_POST['city'] ?? null),
        'state'               => trim($_POST['state'] ?? null),
        'zip'                 => trim($_POST['zip'] ?? null),
        'main_phone'          => trim($_POST['main_phone'] ?? null),
        'ext'                 => trim($_POST['ext'] ?? null),
        'alt_phone'           => trim($_POST['alt_phone'] ?? null),
        'fax'                 => trim($_POST['fax'] ?? null),
        'alt_fax'             => trim($_POST['alt_fax'] ?? null),
        'cell_phone'          => trim($_POST['cell_phone'] ?? null),
        'pager'               => trim($_POST['pager'] ?? null),
        'email'               => trim($_POST['email'] ?? null),
        'updated_at'          => date('Y-m-d H:i:s')
    ];

    error_log("Input data processed for " . ($isUpdate ? "update" : "insert"));

    // Logon name handling
    if (empty($data['logon_name']) && !$isUpdate) {
        $data['logon_name'] = strtolower($data['first_name'][0] . $data['last_name']);
    }

    // -----------------------------
    // Validation
    // -----------------------------
    if ($data['first_name'] === '' || $data['last_name'] === '') {
        throw new Exception('First name and last name are required');
    }

    if ($data['email'] && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }

    // -----------------------------
    // UPDATE
    // -----------------------------
    if ($isUpdate) {

        $sql = "
            UPDATE contacts SET
                title=:title,
                first_name=:first_name,
                middle_name=:middle_name,
                last_name=:last_name,
                suffix=:suffix,
                contact_type=:contact_type,
                company_dept_name=:company_dept_name,
                active=:active,
                specialty_1=:specialty_1,
                specialty_2=:specialty_2,
                research_education=:research_education,
                street_address_1=:street_address_1,
                street_address_2=:street_address_2,
                city=:city,
                state=:state,
                zip=:zip,
                main_phone=:main_phone,
                ext=:ext,
                alt_phone=:alt_phone,
                fax=:fax,
                alt_fax=:alt_fax,
                cell_phone=:cell_phone,
                pager=:pager,
                email=:email,
                updated_at=:updated_at
            WHERE id=:id
        ";

        $data['id'] = (int)$_POST['id'];
        $contactId = $data['id'];

        if ($data['id'] <= 0) {
            error_log('Invalid contact ID for update: ' . $data['id']);
            throw new Exception('Invalid contact ID');
        }

        $fullname = trim(
            $data['last_name'] . ' ' .
                ($data['middle_name'] ? $data['middle_name'] . ' ' : '') .
                $data['first_name']
        );


        // Update study personnel
        error_log("Updating study_personnel for contact ID: " . $contactId);
        $stmt = $conn->prepare("UPDATE study_personnel SET name=:name, title=:title, company_name=:company_name, email = :email, phone = :phone WHERE contact_id=:contact_id");
        $stmt->execute([
            ':name' => $fullname,
            ':title' => $data['title'],
            ':company_name' => $data['company_dept_name'],
            ':email' => $data['email'],
            ':phone' => $data['main_phone'],
            ':contact_id' => $contactId
        ]);
        error_log("study_personnel updated successfully");

        // Fetch all study_ids associated with the contact
        $stmt = $conn->prepare("SELECT study_id, role FROM study_personnel WHERE contact_id=:contact_id");
        $stmt->execute([':contact_id' => $contactId]);
        $personnel = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get unique study_ids
        $studyIds = array_unique(array_column($personnel, 'study_id'));

        // For each study, rebuild the pi, reviewer, cols, admins fields
        foreach ($studyIds as $studyId) {
            // Fetch all personnel for this study
            $stmt = $conn->prepare("SELECT name, role FROM study_personnel WHERE study_id = :study_id");
            $stmt->execute([':study_id' => $studyId]);
            $studyPersonnel = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Build the fields
            $pi = '';
            $reviewers = [];
            $admins = [];
            $cols = [];

            foreach ($studyPersonnel as $p) {
                $role = $p['role'];
                $name = $p['name'];
                switch ($role) {
                    case 'PI':
                        $pi = $name;
                        break;
                    case 'Reviewer':
                        $reviewers[] = $name;
                        break;
                    case 'Admin':
                        $admins[] = $name;
                        break;
                    case 'Co-PI':
                        $cols[] = $name;
                        break;
                }
            }

            // Update the studies table
            $stmt = $conn->prepare("UPDATE studies SET pi=:pi, reviewer=:reviewer, cols=:cols, admins=:admins WHERE id=:id");
            $stmt->execute([
                ':pi' => $pi,
                ':reviewer' => implode(', ', $reviewers),
                ':cols' => implode(', ', $cols),
                ':admins' => implode(', ', $admins),
                ':id' => $studyId
            ]);
        }
        error_log("Studies updated successfully for contact ID: " . $contactId);


    } else {

        // -----------------------------
        // INSERT
        // -----------------------------
        $sql = "
            INSERT INTO contacts (
                title, first_name, middle_name, last_name,logon_name, suffix,
                contact_type, company_dept_name, active,
                specialty_1, specialty_2, research_education,
                street_address_1, street_address_2, city, state, zip,
                main_phone, ext, alt_phone, fax, alt_fax,
                cell_phone, pager, email,
                created_at, updated_at
            ) VALUES (
                :title, :first_name, :middle_name, :last_name, :logon_name, :suffix,
                :contact_type, :company_dept_name, :active,
                :specialty_1, :specialty_2, :research_education,
                :street_address_1, :street_address_2, :city, :state, :zip,
                :main_phone, :ext, :alt_phone, :fax, :alt_fax,
                :cell_phone, :pager, :email,
                NOW(), :updated_at
            )
        ";
    }

    if ($isUpdate) {
        $executeData = [
            'title' => $data['title'],
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'],
            'last_name' => $data['last_name'],
            'suffix' => $data['suffix'],
            'contact_type' => $data['contact_type'],
            'company_dept_name' => $data['company_dept_name'],
            'active' => $data['active'],
            'specialty_1' => $data['specialty_1'],
            'specialty_2' => $data['specialty_2'],
            'research_education' => $data['research_education'],
            'street_address_1' => $data['street_address_1'],
            'street_address_2' => $data['street_address_2'],
            'city' => $data['city'],
            'state' => $data['state'],
            'zip' => $data['zip'],
            'main_phone' => $data['main_phone'],
            'ext' => $data['ext'],
            'alt_phone' => $data['alt_phone'],
            'fax' => $data['fax'],
            'alt_fax' => $data['alt_fax'],
            'cell_phone' => $data['cell_phone'],
            'pager' => $data['pager'],
            'email' => $data['email'],
            'updated_at' => $data['updated_at'],
            'id' => (int)$_POST['id']
        ];
    } else {
        $executeData = $data;
    }

    error_log("Executing main " . ($isUpdate ? "UPDATE" : "INSERT") . " query");
    $stmt = $conn->prepare($sql);
    $stmt->execute($executeData);
    error_log("Main query executed successfully");

    if (!$isUpdate) {
        $contactId = (int)$conn->lastInsertId();
    }

    // -----------------------------
    // File uploads (same contact ID)
    // -----------------------------
    error_log("Checking for file uploads");
    if (!empty($_FILES['documents']['tmp_name'][0])) {
        error_log("Starting file uploads for contact ID: " . $contactId);

        $uploadDir = "../../uploads/contacts/{$contactId}/";

        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            throw new Exception('Failed to create upload directory');
        }

        if (!is_writable($uploadDir)) {
            throw new Exception('Upload directory is not writable');
        }

        foreach ($_FILES['documents']['tmp_name'] as $i => $tmp) {

            if ($_FILES['documents']['error'][$i] !== UPLOAD_ERR_OK) {
                throw new Exception('Upload error code: ' . $_FILES['documents']['error'][$i]);
            }

            if (!is_uploaded_file($tmp)) {
                throw new Exception('Invalid uploaded file');
            }

            $originalName = basename($_FILES['documents']['name'][$i]);
            $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
            $targetPath = $uploadDir . $safeName;

            error_log("Attempting to move uploaded file: " . $originalName);
            if (!move_uploaded_file($tmp, $targetPath)) {
                throw new Exception('File upload failed for: ' . $originalName);
            }
            error_log("File moved successfully: " . $originalName);

            error_log("Inserting document record for: " . $originalName);
            $conn->prepare("
            INSERT INTO contact_documents
            (contact_id, file_name, file_path, file_size, comments, uploaded_at)
            VALUES
            (:cid, :name, :path, :size, :comments, NOW())
        ")->execute([
                'cid' => $contactId,
                'name' => $originalName,
                'path' => $targetPath,
                'size' => $_FILES['documents']['size'][$i],
                'comments' => $_POST['file_comments'][$i] ?? null
            ]);
            error_log("Document record inserted for: " . $originalName);
        }
    }


    error_log("Committing transaction");
    $conn->commit();

    error_log("Contact " . ($isUpdate ? 'updated' : 'added') . " successfully with ID: " . $contactId);

    echo json_encode([
        'success' => true,
        'message' => $isUpdate ? 'Contact updated successfully' : 'Contact added successfully',
        'id' => $contactId
    ]);
} catch (Exception $e) {
    error_log("Exception caught: " . $e->getMessage());

    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
