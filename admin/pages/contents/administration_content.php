<!-- Administration Content -->
<div class="">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Administration</h2>
        <button class="btn btn-primary">
            <i class="fas fa-cog me-1"></i> System Settings
        </button>
    </div>

    <!-- Admin Dashboard -->
    <div class="row mb-4">
        <?php
        // require_once '../database/db_functions.php';
        $adminStats = getAdministrationStats();
        ?>
        <div class="col-md-3">
            <div class="card-dashboard text-center p-3">
                <div class="stats-number"><?php echo htmlspecialchars($adminStats['active_users'] ?? 15); ?></div>
                <div class="stats-label">Active Users</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-dashboard text-center p-3">
                <div class="stats-number"><?php echo htmlspecialchars($adminStats['system_modules'] ?? 8); ?></div>
                <div class="stats-label">System Modules</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-dashboard text-center p-3">
                <div class="stats-number"><?php echo htmlspecialchars($adminStats['uptime'] ?? '99.9%'); ?></div>
                <div class="stats-label">Uptime</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-dashboard text-center p-3">
                <div class="stats-number"><?php echo htmlspecialchars($adminStats['storage_used'] ?? '2.1GB'); ?></div>
                <div class="stats-label">Storage Used</div>
            </div>
        </div>
    </div>

    <!-- Admin Functions -->
    <div class="main-content">
        <h4 class="section-title">Administrative Functions</h4>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-users me-2"></i>User Management</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">Manage user accounts, roles, and permissions</p>
                        <button class="btn btn-outline-primary w-100">Manage Users</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-database me-2"></i>Database Management</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">Backup, restore, and maintain system database</p>
                        <button class="btn btn-outline-primary w-100">Database Tools</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Security Settings</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">Configure security policies and access controls</p>
                        <button class="btn btn-outline-primary w-100">Security Config</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-file-alt me-2"></i>Audit Logs</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">Review system activity and audit trails</p>
                        <button class="btn btn-outline-primary w-100">View Logs</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>System Reports</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">Generate system performance and usage reports</p>
                        <button class="btn btn-outline-primary w-100">Generate Reports</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-tools me-2"></i>System Maintenance</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">Perform system updates and maintenance tasks</p>
                        <button class="btn btn-outline-primary w-100">Maintenance</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <h4 class="section-title mt-4">Recent Administrative Activity</h4>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>User</th>
                        <th>Timestamp</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $recentActivity = getRecentAdminActivity();
                    if (empty($recentActivity)) {
                        // Fallback to static data if no DB data
                        echo '<tr>
                            <td>User Login</td>
                            <td>admin@example.com</td>
                            <td>2024-09-17 10:30 AM</td>
                            <td>Successful login from IP 192.168.1.100</td>
                        </tr>
                        <tr>
                            <td>Database Backup</td>
                            <td>System</td>
                            <td>2024-09-17 02:00 AM</td>
                            <td>Automated daily backup completed</td>
                        </tr>
                        <tr>
                            <td>User Created</td>
                            <td>admin@example.com</td>
                            <td>2024-09-16 03:45 PM</td>
                            <td>New user account created for Dr. Sarah Johnson</td>
                        </tr>
                        <tr>
                            <td>Security Update</td>
                            <td>System</td>
                            <td>2024-09-16 01:00 AM</td>
                            <td>Security patches applied successfully</td>
                        </tr>';
                    } else {
                        foreach ($recentActivity as $activity) {
                            echo '<tr>
                                <td>' . htmlspecialchars($activity['action']) . '</td>
                                <td>' . htmlspecialchars($activity['user_name']) . '</td>
                                <td>' . htmlspecialchars($activity['timestamp']) . '</td>
                                <td>' . htmlspecialchars($activity['details']) . '</td>
                            </tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>