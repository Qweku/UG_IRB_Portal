<?php
require_once '../../includes/config/database.php';
header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->connect();
    $conn->beginTransaction();

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

    $stmt = $conn->prepare($sql);
    $stmt->execute($data);

    if (!$isUpdate) {
        $contactId = (int)$conn->lastInsertId();
    }

    // -----------------------------
    // File uploads (same contact ID)
    // -----------------------------
    if (!empty($_FILES['documents']['tmp_name'][0])) {

        $uploadDir = "../../uploads/contacts/{$contactId}/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        foreach ($_FILES['documents']['tmp_name'] as $i => $tmp) {
            if ($_FILES['documents']['error'][$i] !== UPLOAD_ERR_OK) continue;

            $name = basename($_FILES['documents']['name'][$i]);
            $safe = time() . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);

            move_uploaded_file($tmp, $uploadDir . $safe);

            $conn->prepare("
                INSERT INTO contact_documents
                (contact_id, file_name, file_path, file_size, comments, uploaded_at)
                VALUES
                (:cid, :name, :path, :size, :comments, NOW())
            ")->execute([
                'cid' => $contactId,
                'name' => $name,
                'path' => $uploadDir . $safe,
                'size' => $_FILES['documents']['size'][$i],
                'comments' => $_POST['file_comments'][$i] ?? null
            ]);
        }
    }

    $conn->commit();

    error_log("Contact " . ($isUpdate ? 'updated' : 'added') . " successfully with ID: " . $contactId);

    echo json_encode([
        'success' => true,
        'message' => $isUpdate ? 'Contact updated successfully' : 'Contact added successfully',
        'id' => $contactId
    ]);
} catch (Exception $e) {

    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
