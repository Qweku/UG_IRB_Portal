<!-- Administration Content -->
<div class="administration-dashboard">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Administration</h2>
            <p class="text-muted mb-0">Manage system settings, configurations, and templates</p>
        </div>
        <div class="badge bg-primary fs-6">
            <i class="fas fa-cog me-1"></i> Admin Panel
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary">
                            <i class="fas fa-address-book"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0">24</h4>
                            <span class="text-muted">Active Contacts</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-success">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0">156</h4>
                            <span class="text-muted">User Accounts</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-warning">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0">42</h4>
                            <span class="text-muted">Templates</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-info">
                            <i class="fas fa-database"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0">18</h4>
                            <span class="text-muted">Code Tables</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row">
        <!-- Left Column - Entities & Account -->
        <div class="col-md-6">
            <!-- Entities Card -->
            <div class="card admin-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-building me-2 text-primary"></i>
                        Entities
                    </h5>
                    <span class="badge bg-primary">2 Items</span>
                </div>
                <div class="card-body">
                    <div class="admin-list">
                        <div class="admin-list-item">
                            <div class="admin-icon">
                                <i class="fas fa-address-book"></i>
                            </div>
                            <div class="admin-content">
                                <h6 class="mb-1">Contacts</h6>
                                <p class="text-muted mb-0">Manage research contacts</p>
                            </div>
                            <a href="/contacts" class="btn btn-sm btn-outline-primary">Manage</a>
                        </div>
                        <div class="admin-list-item">
                            <div class="admin-icon">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <div class="admin-content">
                                <h6 class="mb-1">IRB License</h6>
                                <p class="text-muted mb-0">License configuration and renewal</p>
                            </div>
                            <button class="btn btn-sm btn-outline-primary">Configure</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Card -->
            <div class="card admin-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user-cog me-2 text-success"></i>
                        Account
                    </h5>
                    <span class="badge bg-success">Pro</span>
                </div>
                <div class="card-body">
                    <div class="admin-list">
                        <div class="admin-list-item">
                            <div class="admin-icon">
                                <i class="fas fa-crown text-warning"></i>
                            </div>
                            <div class="admin-content">
                                <h6 class="mb-1">Upgrade to Pro IRB</h6>
                                <p class="text-muted mb-0">Access advanced features and analytics</p>
                            </div>
                            <button class="btn btn-sm btn-warning">Upgrade</button>
                        </div>
                        <div class="admin-list-item">
                            <div class="admin-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="admin-content">
                                <h6 class="mb-1">Account Information</h6>
                                <p class="text-muted mb-0">View and update account details</p>
                            </div>
                            <a href="/account-information" class="btn btn-sm btn-outline-primary">View</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Templates Card -->
            <div class="card admin-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt me-2 text-info"></i>
                        Templates
                    </h5>
                    <span class="badge bg-info">Weekly</span>
                </div>
                <div class="card-body">
                    <div class="admin-list">
                        <div class="admin-list-item">
                            <div class="admin-icon">
                                <i class="fas fa-upload"></i>
                            </div>
                            <div class="admin-content">
                                <h6 class="mb-1">Templates Weekly Upload</h6>
                                <p class="text-muted mb-0">Manage weekly template uploads and schedules</p>
                            </div>
                            <button class="btn btn-sm btn-outline-primary">Manage</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Study Groupings & Codes -->
        <div class="col-md-6">
            <!-- Study Groupings Card -->
            <div class="card admin-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-project-diagram me-2 text-purple"></i>
                        Study Groupings
                    </h5>
                    <span class="badge bg-purple">12 Categories</span>
                </div>
                <div class="card-body">
                    <div class="category-grid">
                        <div class="category-item">
                            <i class="fas fa-tags"></i>
                            <span>Classification</span>
                        </div>
                        <div class="category-item">
                            <i class="fas fa-chart-pie"></i>
                            <span>Dividends</span>
                        </div>
                        <div class="category-item">
                            <i class="fas fa-sitemap"></i>
                            <span>Depth Groups</span>
                        </div>
                        <div class="category-item">
                            <i class="fas fa-toggle-on"></i>
                            <span>Status</span>
                        </div>
                        <div class="category-item">
                            <i class="fas fa-gift"></i>
                            <span>Benefits</span>
                        </div>
                        <div class="category-item">
                            <i class="fas fa-pills"></i>
                            <span>Drugs/Xylipuridic Devices</span>
                        </div>
                        <div class="category-item">
                            <i class="fas fa-ban"></i>
                            <span>Exempt Category</span>
                        </div>
                        <div class="category-item">
                            <i class="fas fa-bolt"></i>
                            <span>Expedited Category</span>
                        </div>
                        <div class="category-item">
                            <i class="fas fa-tags"></i>
                            <span>Greater Topics</span>
                        </div>
                        <div class="category-item">
                            <i class="fas fa-flask"></i>
                            <span>IND Topics</span>
                        </div>
                        <div class="category-item">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Risks</span>
                        </div>
                        <div class="category-item">
                            <i class="fas fa-child"></i>
                            <span>Child Category</span>
                        </div>
                        <div class="category-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Vulnerable Populations</span>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <button class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-cog me-1"></i> Configure All
                        </button>
                    </div>
                </div>
            </div>

            <!-- Codes and Tables Card -->
            <div class="card admin-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-code me-2 text-orange"></i>
                        Codes and Tables
                    </h5>
                    <span class="badge bg-orange">9 Tables</span>
                </div>
                <div class="card-body">
                    <div class="code-tables">
                        <div class="code-table-item">
                            <span class="code-name">CPA Types</span>
                            <span class="code-count">15 entries</span>
                        </div>
                        <div class="code-table-item">
                            <span class="code-name">Investigator Specialties</span>
                            <span class="code-count">28 entries</span>
                        </div>
                        <div class="code-table-item">
                            <span class="code-name">IRB Meeting Notes</span>
                            <span class="code-count">42 entries</span>
                        </div>
                        <div class="code-table-item">
                            <span class="code-name">IRB Action Codes</span>
                            <span class="code-count">18 entries</span>
                        </div>
                        <div class="code-table-item">
                            <span class="code-name">SAE Event Types</span>
                            <span class="code-count">12 entries</span>
                        </div>
                        <div class="code-table-item">
                            <span class="code-name">CPA Action Codes</span>
                            <span class="code-count">22 entries</span>
                        </div>
                        <div class="code-table-item">
                            <span class="code-name">Study Status Codes</span>
                            <span class="code-count">8 entries</span>
                        </div>
                        <div class="code-table-item">
                            <span class="code-name">Agenda Categories</span>
                            <span class="code-count">6 entries</span>
                        </div>
                        <div class="code-table-item">
                            <span class="code-name">IRB Conditions</span>
                            <span class="code-count">14 entries</span>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <button class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-database me-1"></i> Manage Tables
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.administration-dashboard {
    padding: 20px 0;
}

.stat-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.admin-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.admin-card:hover {
    transform: translateY(-2px);
}

.admin-list-item {
    display: flex;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #f0f0f0;
}

.admin-list-item:last-child {
    border-bottom: none;
}

.admin-icon {
    width: 40px;
    height: 40px;
    background: #f8f9fa;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    color: var(--royal-blue);
}

.admin-content {
    flex: 1;
}

.category-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 10px;
    margin-bottom: 15px;
}

.category-item {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 8px;
    text-align: center;
    transition: all 0.2s;
    border: 1px solid #e9ecef;
}

.category-item:hover {
    background: var(--royal-blue);
    color: white;
    transform: translateY(-2px);
    cursor: pointer;
}

.category-item i {
    display: block;
    margin-bottom: 5px;
    font-size: 1.2rem;
}

.category-item span {
    font-size: 0.85rem;
    font-weight: 500;
}

.code-tables {
    max-height: 300px;
    overflow-y: auto;
}

.code-table-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    border-bottom: 1px solid #f0f0f0;
    transition: background-color 0.2s;
}

.code-table-item:hover {
    background-color: #f8f9fa;
    border-radius: 6px;
}

.code-name {
    font-weight: 500;
    color: #495057;
}

.code-count {
    font-size: 0.85rem;
    color: #6c757d;
    background: #e9ecef;
    padding: 2px 8px;
    border-radius: 12px;
}

.bg-purple {
    background-color: #6f42c1 !important;
}

.bg-orange {
    background-color: #fd7e14 !important;
}

/* Custom scrollbar */
.code-tables::-webkit-scrollbar {
    width: 6px;
}

.code-tables::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.code-tables::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.code-tables::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>