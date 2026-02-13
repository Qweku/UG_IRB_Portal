<?php
require_once '../includes/auth_check.php';
require_once '../../includes/functions/helpers.php';

header('Content-Type: application/json');



// Admin-only access check
if (
    !isset($_SESSION['logged_in']) || !isset($_SESSION['role']) ||
    ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'super_admin')
) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    }

    $applicationId = $_GET['id'] ?? null;

    if (empty($applicationId)) {
        echo json_encode(['status' => 'error', 'message' => 'Application ID is required']);
        exit;
    }

    // Get main application data
    $stmt = $conn->prepare("SELECT * FROM applications WHERE id = ?");
    $stmt->execute([$applicationId]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$application) {
        echo json_encode(['status' => 'error', 'message' => 'Application not found']);
        exit;
    }

    // Get assigned reviewers
    $stmt = $conn->prepare("
        SELECT ar.*, u.full_name, u.email
        FROM application_reviews ar
        JOIN users u ON ar.reviewer_id = u.id
        WHERE ar.application_id = ?
        ORDER BY ar.created_at DESC
    ");
    $stmt->execute([$applicationId]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Build HTML output
    $html = buildApplicationDetailsHtml($application, $reviews);

    echo json_encode([
        'status' => 'success',
        'html' => $html,
        'data' => $application
    ]);
} catch (PDOException $e) {
    error_log(__FILE__ . " - Database error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}

function buildApplicationDetailsHtml($application, $reviews)
{
    ob_start();
?>
    <div class="row">
        <div class="col-md-6">
            <h6 class="text-muted mb-3">Application Information</h6>
            <table class="table table-sm table-bordered">
                <tr>
                    <td><strong>Protocol Number</strong></td>
                    <td><?= htmlspecialchars($application['protocol_number'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td><strong>Study Title</strong></td>
                    <td><?= htmlspecialchars($application['study_title'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td><strong>Principal Investigator</strong></td>
                    <td><?= htmlspecialchars($application['pi_name'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td><strong>PI Email</strong></td>
                    <td><?= htmlspecialchars($application['pi_email'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td><strong>Submission Date</strong></td>
                    <td><?= !empty($application['submission_date']) ? date('d M Y', strtotime($application['submission_date'])) : 'N/A' ?></td>
                </tr>
                <tr>
                    <td><strong>Status</strong></td>
                    <td><?= htmlspecialchars($application['status'] ?? 'Unknown') ?></td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <h6 class="text-muted mb-3">Reviewer Assignments</h6>
            <?php if (empty($reviews)): ?>
                <p class="text-muted">No reviewers assigned yet</p>
            <?php else: ?>
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Reviewer</th>
                            <th>Status</th>
                            <th>Due Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $review): ?>
                            <tr>
                                <td><?= htmlspecialchars($review['full_name']) ?></td>
                                <td><?= htmlspecialchars($review['status']) ?></td>
                                <td><?= !empty($review['due_date']) ? date('d M Y', strtotime($review['due_date'])) : 'N/A' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-12">
            <h6 class="text-muted mb-3">Abstract</h6>
            <div class="p-3 bg-light rounded"><?= nl2br(htmlspecialchars($application['abstract'] ?? 'No abstract provided')) ?></div>
        </div>
    </div>
<?php
    return ob_get_clean();
}
