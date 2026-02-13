<?php
// session_start();
// if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
//     header('Location: /login');
//     exit;
// }

// Include CSRF protection
// require_once '../../includes/functions/csrf.php';

// Pagination parameters
$limit = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Get total count of institutions
$db = new Database();
$conn = $db->connect();
$total_records = 0;
if ($conn) {
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM institutions");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_records = $result['count'] ?? 0;
    } catch (PDOException $e) {
        error_log("Error counting institutions: " . $e->getMessage());
    }
}
$total_pages = ceil($total_records / $limit);

// Fetch institutions from the database with pagination
$institutions = getAllInstitutions($limit, $offset);

// Build query string for pagination links (preserve filter params)
function buildQueryString($exclude = [])
{
    $params = $_GET;
    foreach ($exclude as $key) {
        unset($params[$key]);
    }
    return http_build_query($params);
}

?>

<div class="institustions-dashboard">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Institutions Dashboard</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createInstitutionModal">
            Create New Institution
        </button>
    </div>
    <!-- Institutions Table -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th scope="col">Institution ID</th>
                    <th scope="col">Institution Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($institutions as $institution): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($institution['id']); ?></td>
                        <td><?php echo htmlspecialchars($institution['institution_name']); ?></td>
                        <td><?php echo htmlspecialchars($institution['email']); ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning edit-institution-btn" data-id="<?php echo $institution['id']; ?>">Edit</button>
                            <button class="btn btn-sm btn-danger delete-institution-btn" data-id="<?php echo $institution['id']; ?>">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination-wrapper mt-3">
                                <nav aria-label="Institutions pagination">
                                    <ul class="pagination justify-content-center mb-0">
                                        <?php
                                        $queryString = buildQueryString(['page']);
                                        $currentUrl = strtok($_SERVER['REQUEST_URI'], '?');
                                        $separator = empty($queryString) ? '?' : '?' . $queryString . '&';
                                        ?>
                                        <!-- First Page -->
                                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="<?php echo $currentUrl . $separator . 'page=1'; ?>" aria-label="First">
                                                <i class="fas fa-angle-double-left"></i>
                                            </a>
                                        </li>
                                        <!-- Previous Page -->
                                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="<?php echo $currentUrl . $separator . 'page=' . max(1, $page - 1); ?>" aria-label="Previous">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>

                                        <?php
                                        // Show limited page numbers around current page
                                        $startPage = max(1, min($page - 2, $total_pages - 4));
                                        $endPage = min($total_pages, max(5, $page + 2));

                                        if ($startPage > 1):
                                        ?>
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        <?php endif; ?>

                                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="<?php echo $currentUrl . $separator . 'page=' . $i; ?>">
                                                    <?php echo $i; ?>
                                                    <?php if ($i == $page): ?>
                                                        <span class="visually-hidden">(current)</span>
                                                    <?php endif; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($endPage < $total_pages): ?>
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        <?php endif; ?>

                                        <!-- Next Page -->
                                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="<?php echo $currentUrl . $separator . 'page=' . min($total_pages, $page + 1); ?>" aria-label="Next">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                        <!-- Last Page -->
                                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="<?php echo $currentUrl . $separator . 'page=' . $total_pages; ?>" aria-label="Last">
                                                <i class="fas fa-angle-double-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                                <div class="text-center mt-2">
                                    <span class="text-muted small">
                                        Showing page <?php echo $page; ?> of <?php echo $total_pages; ?>
                                        (<?php echo $total_records; ?> total institutions)
                                    </span>
                                </div>
                            </div>
                        <?php elseif ($total_records > 0): ?>
                            <div class="text-center mt-3">
                                <span class="text-muted small">
                                    Showing all <?php echo $total_records; ?> institutions
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>

    <!-- Create New Institution Modal -->
    <div class="modal fade" id="createInstitutionModal" tabindex="-1" aria-labelledby="createInstitutionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createInstitutionModalLabel">Create New Institution</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Institution creation form -->
                    <form id="institutionForm">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        <div class="mb-3">
                            <label for="institutionName" class="form-label">Institution Name</label>
                            <input type="text" class="form-control" id="institutionName" name="institutionName" required>
                        </div>
                        <div class="mb-3">
                            <label for="institutionEmail" class="form-label">Institution Email</label>
                            <input type="email" class="form-control" id="institutionEmail" name="institutionEmail">
                        </div>
                        <!-- Add more fields as needed -->
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="institutionForm" class="btn btn-success">Create Institution</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Utility functions
        function showToast(type, message) {
            // Check if toast container exists
            let toastContainer = document.querySelector('.toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                toastContainer.style.zIndex = '1060';
                document.body.appendChild(toastContainer);
            }

            // Create toast
            const toastId = 'toast-' + Date.now();
            const toast = document.createElement('div');
            toast.id = toastId;
            toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');

            toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

            toastContainer.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();

            // Remove toast after hidden
            toast.addEventListener('hidden.bs.toast', function() {
                toast.remove();
            });
        }
        // JavaScript to handle edit and delete actions
        document.querySelectorAll('.edit-institution-btn').forEach(button => {
            button.addEventListener('click', async function() {
                const institutionId = this.getAttribute('data-id');
                // Implement edit functionality here
                try {
                    const response = await fetch('/admin/handlers/update_institution.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: institutionId
                        })
                    });
                    if (data.status === 'success') {
                        row.remove();
                        showToast('success', data.message);
                    } else {
                        showToast('error', data.message);
                    }
                } catch (error) {
                    showToast('error', 'Error updating institution.');
                    console.error('Error:', error);
                }

            });
        });

        document.querySelectorAll('.delete-institution-btn').forEach(button => {
            button.addEventListener('click', function() {
                const institutionId = this.getAttribute('data-id');
                // Implement delete functionality here
                if (confirm('Are you sure you want to delete this institution?')) {
                    fetch('/admin/handlers/delete_institution.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                id: institutionId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Remove the institution row from the table
                                const row = button.closest('tr');
                                row.remove();
                                showToast('success', 'Institution deleted successfully!');
                            } else {
                                showToast('error', data.message || 'Failed to delete institution.');
                            }
                        })
                        .catch(error => {
                            showToast('error', 'Error deleting institution.');
                            console.error('Error:', error);
                        });
                }

            });
        });

        // Handle institution creation form submission
        document.getElementById('institutionForm').addEventListener('submit', async function(event) {
            event.preventDefault();
            const institutionName = document.getElementById('institutionName').value;
            const institutionEmail = document.getElementById('institutionEmail').value;

            try {
                const response = await fetch('/admin/handlers/add_institution.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        institution_name: institutionName,
                        email: institutionEmail
                    })
                });
                const data = await response.json();
                if (data.success) {
                    // Close modal
                    const createInstitutionModal = bootstrap.Modal.getInstance(document.getElementById('createInstitutionModal'));
                    createInstitutionModal.hide();
                    showToast('success', 'Institution created successfully!');
                    // Optionally, refresh the page or update the table dynamically
                } else {
                    showToast('error', data.message || 'Failed to create institution.');
                }
            } catch (error) {
                showToast('error', 'Error creating institution.');
                console.error('Error:', error);
            }
        });
    </script>