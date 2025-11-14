<?php
require_once '../../includes/config/database.php';
// This modal is used for various administrative tasks in the admin panel
// It provides a dynamic content area that can be populated based on the action being performed

$classifications = [];
$divisions = [];
$departments = [];
$sites = [];
$benefits = [];
$drugs = [];
$devices = [];
$exemptions = [];
$expediteds = [];
$risks = [];
$grants = [];
$children = [];
$vulnerables = [];

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Fetch classification options
    $stmt = $conn->prepare("SELECT id, classification_type FROM classifications ORDER BY name ASC");
    $stmt->execute();
    $classifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch division options
    $stmt = $conn->prepare("SELECT id, division_name FROM divisions ORDER BY division_name ASC");
    $stmt->execute();
    $divisions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch department options
    $stmt = $conn->prepare("SELECT id, department_name FROM department_groups ORDER BY department_name ASC");
    $stmt->execute();
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch site options
    $stmt = $conn->prepare("SELECT id, site_name FROM sites ORDER BY site_name ASC");
    $stmt->execute();
    $sites = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch benefit options
    $stmt = $conn->prepare("SELECT id, benefit_type FROM benefits ORDER BY benefit_type ASC");
    $stmt->execute();
    $benefits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch drug options
    $stmt = $conn->prepare("SELECT id, drug_name FROM drugs ORDER BY drug_name ASC");
    $stmt->execute();
    $drugs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch device options
    $stmt = $conn->prepare("SELECT id, device_name FROM device_types ORDER BY device_name ASC");
    $stmt->execute();
    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch exemption options
    $stmt = $conn->prepare("SELECT id, exempt_cite, exempt_description FROM exempt_codes ORDER BY exempt_cite ASC");
    $stmt->execute();
    $exemptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch expedited options
    $stmt = $conn->prepare("SELECT id, expedite_cite, expedite_description FROM expedited_codes ORDER BY expedite_cite ASC");
    $stmt->execute();
    $expediteds = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch risk category options
    $stmt = $conn->prepare("SELECT id, category_name FROM risks_category ORDER BY category_name ASC");
    $stmt->execute();
    $risks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch grant options
    $stmt = $conn->prepare("SELECT id, grant_name FROM grant_projects ORDER BY grant_name ASC");
    $stmt->execute();
    $grants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch children options
    $stmt = $conn->prepare("SELECT id, age_range FROM children ORDER BY age_range ASC");
    $stmt->execute();
    $children = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch vulnerable population options
    $stmt = $conn->prepare("SELECT id, population_type FROM vulnerable_populations ORDER BY population_type ASC");
    $stmt->execute();
    $vulnerables = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $conn->rollBack();
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch items. Please try again.']);
}




?>

<div id="adminModal" class="modal fade" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="adminModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="col-12">
                    <div id="contentArea">
                        <!-- Dynamic content will be loaded here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" onclick="addItems()">Add</button>
            </div>
        </div>

    </div>
</div>
