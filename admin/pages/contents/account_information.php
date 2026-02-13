<?php
// session_start();
// if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true)
// {
//     header('Location: /login');
//     exit;
// }

$userName = $_SESSION['full_name'] ?? 'User';
$organization = '';
$organizationEmail = '';
$userEmail = $_SESSION['email'] ?? '';
$dateCreated = $_SESSION['date_created'] ?? '2020-01-01';

try{
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    // Fetch organization name based on user's institution_id
    if (isset($_SESSION['institution_id'])) {
        $institutionId = (int)$_SESSION['institution_id'];
        $stmt = $conn->prepare("SELECT institution_name, institution_email FROM institutions WHERE id = ?");
        $stmt->execute([$institutionId]);
        $institution = $stmt->fetch(PDO::FETCH_ASSOC);
        $organization = $institution ? $institution['institution_name'] : '';
        $organizationEmail = $institution ? $institution['institution_email'] : '';
    }

}
catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

?>

<!-- Account Information Content -->
<div class="account-information p-5">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Account Information</h2>
            <p class="text-muted mb-0">Manage your UG Hares details</p>
        </div>
        <div class="badge bg-primary fs-6">
            <i class="fas fa-crown me-1"></i> UG Hares Office
        </div>
    </div>

    <!-- Account Overview Cards -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card account-card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user-circle me-2 text-primary"></i>
                        Account Overview
                    </h5>
                    <span class="badge bg-success">Active</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="account-detail-item">
                                <label class="detail-label">User</label>
                                <div class="detail-value"><?php echo $userName ?></div>
                            </div>
                            <div class="account-detail-item">
                                <label class="detail-label">Organization</label>
                                <div class="detail-value"><?php echo $organization ?></div>
                            </div>
                            <div class="account-detail-item">
                                <label class="detail-label">Date Created</label>
                                <div class="detail-value"><?php echo $dateCreated ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="account-detail-item">
                                <label class="detail-label">Email</label>
                                <div class="detail-value">
                                    <i class="fas fa-envelope me-2 text-muted"></i>
                                    <?php echo $userEmail ?>
                                </div>
                            </div>
                            
                           
                        </div>
                    </div>
                </div>
            </div>
        </div>
       
    </div>

    <!-- Support & Contact Section -->
    <div class="row">
        <div class="col-md-6">
            <div class="card account-card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-headset me-2 text-info"></i>
                        Support & Contact
                    </h5>
                </div>
                <div class="card-body">
                    <div class="support-info">
                        <p class="text-muted mb-4">
                            To update account information or cancel ProIRB, please contact our support team.
                        </p>
                        
                        <div class="contact-methods">
                            <div class="contact-item">
                                <div class="contact-icon bg-primary">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="contact-details">
                                    <h6 class="mb-1">Phone Support</h6>
                                    <p class="mb-0 text-muted">731-421-4622</p>
                                    <small class="text-muted">Available Mon-Fri, 9AM-5PM EST</small>
                                </div>
                            </div>
                            
                            <div class="contact-item">
                                <div class="contact-icon bg-success">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="contact-details">
                                    <h6 class="mb-1">Email Support</h6>
                                    <p class="mb-0 text-muted"><?php echo $organizationEmail ?></p>
                                    <small class="text-muted">Typically responds within 2 hours</small>
                                </div>
                            </div>
                            
                           
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card account-card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs me-2 text-purple"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="quick-actions">
                        <div class="action-grid">
                            <button class="action-btn">
                                <div class="action-icon bg-primary">
                                    <i class="fas fa-user-edit"></i>
                                </div>
                                <span>Update Profile</span>
                            </button>
                            
                            <button class="action-btn">
                                <div class="action-icon bg-success">
                                    <i class="fas fa-lock"></i>
                                </div>
                                <span>Change Password</span>
                            </button>
                            
                            <button class="action-btn">
                                <div class="action-icon bg-info">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <span>Notification Settings</span>
                            </button>
                            
                            <button class="action-btn">
                                <div class="action-icon bg-warning">
                                    <i class="fas fa-download"></i>
                                </div>
                                <span>Export Data</span>
                            </button>
                            
                            <button class="action-btn">
                                <div class="action-icon bg-secondary">
                                    <i class="fas fa-users"></i>
                                </div>
                                <span>Team Members</span>
                            </button>
                            
                            <button class="action-btn">
                                <div class="action-icon bg-danger">
                                    <i class="fas fa-file-contract"></i>
                                </div>
                                <span>Service Agreement</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

  
</div>

<style>
.account-information {
    padding: 20px 0;
}

.account-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.account-card:hover {
    transform: translateY(-2px);
}

.account-detail-item {
    margin-bottom: 20px;
}

.detail-label {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
}

.detail-value {
    font-size: 1.1rem;
    color: #495057;
    font-weight: 500;
}

.status-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    color: white;
    font-size: 1.5rem;
}

.contact-item {
    display: flex;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #f0f0f0;
}

.contact-item:last-child {
    border-bottom: none;
}

.contact-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    color: white;
    font-size: 1.2rem;
}

.contact-details h6 {
    margin-bottom: 5px;
    color: #495057;
}

.action-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
}

.action-btn {
    background: none;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 20px 10px;
    text-align: center;
    transition: all 0.3s;
    background: white;
}

.action-btn:hover {
    border-color: var(--royal-blue);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.action-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    color: white;
    font-size: 1.3rem;
}

.action-btn span {
    font-size: 0.9rem;
    font-weight: 500;
    color: #495057;
}

.stat-item {
    padding: 15px;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.9rem;
    color: #6c757d;
    font-weight: 500;
}

.billing-progress {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 10px;
}

.progress {
    border-radius: 4px;
}

.bg-purple {
    background-color: #6f42c1 !important;
}

.bg-orange {
    background-color: #fd7e14 !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .action-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .contact-item {
        flex-direction: column;
        text-align: center;
    }
    
    .contact-icon {
        margin-right: 0;
        margin-bottom: 10px;
    }
}
</style>