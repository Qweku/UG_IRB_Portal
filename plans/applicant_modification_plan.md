# Applicant Section Modification Plan

## Overview
Modify the applicant section of the UG IRB Portal to include:
- A sidebar with Dashboard, Studies, and Profile menus
- Dashboard with 3 application type selection cards
- Studies table with submission details and status
- Profile page with applicant information
- Limit of 3 applications per user

## Architecture

### Page Structure
```
applicant/pages/
├── index.php                    # Dashboard with 3 application cards
├── sidebar.php                  # Applicant sidebar component
├── studies.php                  # Studies table view
├── profile.php                  # Applicant profile page
├── forms/
│   ├── form_student.php         # Initial Submission Form A - Students
│   ├── form_nmimr.php           # Initial Submission Form A - NMIMR Researchers
│   └── form_non_nmimr.php       # Initial Submission Form A - Non-NMIMR Researchers
└── partials/
    ├── application_card.php     # Reusable card component
    └── status_badge.php         # Status badge component
```

### Routing Updates (index.php)
```php
'applicant-dashboard' => [
    '_'        => ['file' => 'index.php', 'roles' => ['applicant', 'reviewer']],
    'studies'  => ['file' => 'studies.php', 'roles' => ['applicant', 'reviewer']],
    'profile'  => ['file' => 'profile.php', 'roles' => ['applicant', 'reviewer']],
    'new'      => ['file' => 'new_application.php', 'roles' => ['applicant', 'reviewer']],
    'submit'   => ['file' => 'submit_application.php', 'roles' => ['applicant', 'reviewer']]
]
```

---

## Implementation Tasks

### Phase 1: Sidebar Component
**File:** `applicant/pages/sidebar.php`

```php
<?php
$current_page = basename($_SERVER['PHP_SELF']);
$userRole = $_SESSION['role'] ?? 'applicant';
?>

<div id="sidebar" class="col-lg-2 col-md-3 d-md-block sidebar collapse">
    <div class="sidebar-sticky">
        <!-- Dashboard -->
        <ul class="nav flex-column mb-3">
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'index.php') ? 'active' : '' ?>" 
                   href="/applicant-dashboard">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
        </ul>

        <!-- Applications Section -->
        <div class="sidebar-section mb-3">
            <h6 class="sidebar-header ms-4">
                <i class="fas fa-file-medical me-2"></i>Applications
            </h6>
            <ul class="nav flex-column submenu-nav">
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'studies.php') ? 'active' : '' ?>" 
                       href="/applicant-dashboard/studies">
                        <i class="fas fa-list me-2"></i>My Studies
                    </a>
                </li>
            </ul>
        </div>

        <!-- Account Section -->
        <div class="sidebar-section mb-3">
            <h6 class="sidebar-header ms-4">
                <i class="fas fa-user me-2"></i>Account
            </h6>
            <ul class="nav flex-column submenu-nav">
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'profile.php') ? 'active' : '' ?>" 
                       href="/applicant-dashboard/profile">
                        <i class="fas fa-id-card me-2"></i>Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/logout">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
```

---

### Phase 2: Dashboard with Application Cards
**File:** `applicant/pages/index.php`

**Features:**
- Welcome header with user info
- Stats cards (Total Applications, Under Review, Approved, Rejected)
- 3 Application Type Cards:
  1. Initial Submission Form A - Students
  2. Initial Submission Form A - NMIMR Researchers
  3. Initial Submission Form A - Non-NMIMR Researchers
- Application limit check (max 3 applications per user)

**Application Card Component:**
```php
<div class="col-md-4 mb-4">
    <div class="card application-card h-100">
        <div class="card-body text-center">
            <i class="fas fa-user-graduate fa-3x mb-3 text-primary"></i>
            <h5 class="card-title">Initial Submission Form A</h5>
            <p class="card-text">Students</p>
            <a href="/applicant-dashboard/new?type=student" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Start Application
            </a>
        </div>
    </div>
</div>
```

---

### Phase 3: Studies Table Page
**File:** `applicant/pages/studies.php`

**Table Columns:**
| Column | Icon | Description |
|--------|------|-------------|
| Study Title | 📄 | Protocol title |
| Date Submitted | 📅 | Application submission date |
| Status | 📊 | Submitted/Under Review/Approved/Rejected |
| Actions | ⚡ | View, Edit, Delete |

**Status Badge Component:**
```php
<span class="badge bg-<?= getStatusColor($status) ?>">
    <?= getStatusLabel($status) ?>
</span>

// Mapping:
// submitted → bg-info (Submitted)
// under_review → bg-warning (Under Review)
// approved → bg-success (Approved)
// rejected → bg-danger (Rejected)
```

---

### Phase 4: Profile Page
**File:** `applicant/pages/profile.php`

**Fields to Display:**
- Full Name
- Email Address
- Phone Number
- Institution

**Layout:**
```php
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-user me-2"></i>Applicant Profile</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="fw-bold">Full Name</label>
                <p><?= htmlspecialchars($user['full_name']) ?></p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold">Email</label>
                <p><?= htmlspecialchars($user['email']) ?></p>
            </div>
            <!-- etc. -->
        </div>
    </div>
</div>
```

---

### Phase 5: Helper Functions (helpers.php additions)

```php
/**
 * Get applicant's applications count
 */
function getApplicantApplicationsCount($userId) {
    return executeCountQuery(
        "SELECT COUNT(*) as count FROM studies WHERE user_id = ?",
        [$userId]
    );
}

/**
 * Get applicant's studies
 */
function getApplicantStudies($userId) {
    return executeAssocQuery(
        "SELECT id, title, date_received, status 
         FROM studies 
         WHERE user_id = ? 
         ORDER BY date_received DESC",
        [$userId]
    );
}

/**
 * Get applicant profile
 */
function getApplicantProfile($userId) {
    $db = new Database();
    $conn = $db->connect();
    $stmt = $conn->prepare(
        "SELECT full_name, email, phone, institution 
         FROM users WHERE id = ?"
    );
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Check if user can submit new application (max 3)
 */
function canSubmitNewApplication($userId) {
    return getApplicantApplicationsCount($userId) < 3;
}

/**
 * Application status labels
 */
function getStatusLabel($status) {
    $labels = [
        'submitted' => 'Submitted',
        'under_review' => 'Under Review',
        'approved' => 'Approved',
        'rejected' => 'Rejected'
    ];
    return $labels[$status] ?? 'Unknown';
}

/**
 * Application status colors
 */
function getStatusColor($status) {
    $colors = [
        'submitted' => 'info',
        'under_review' => 'warning',
        'approved' => 'success',
        'rejected' => 'danger'
    ];
    return $colors[$status] ?? 'secondary';
}
```

---

### Phase 6: Database Considerations

**Existing tables to use:**
- `users` - Store applicant information
- `studies` - Store application records

**Fields needed in `studies` table:**
- `user_id` - Link to applicant
- `application_type` - 'student', 'nmimr', 'non_nmimr'
- `status` - 'submitted', 'under_review', 'approved', 'rejected'
- `date_received` - Submission date

---

### File Structure After Implementation

```
UG_IRB_Portal/
├── applicant/
│   ├── pages/
│   │   ├── index.php           # Dashboard with 3 app cards
│   │   ├── sidebar.php          # Applicant sidebar
│   │   ├── studies.php          # Studies table
│   │   ├── profile.php          # Profile page
│   │   ├── new_application.php # Application selector
│   │   └── forms/
│   │       ├── form_student.php      # Student form
│   │       ├── form_nmimr.php         # NMIMR form
│   │       └── form_non_nmimr.php     # Non-NMIMR form
├── index.php                     # Updated routing
└── includes/
    └── functions/
        └── helpers.php           # Updated functions
```

---

## Implementation Priority

1. Create applicant sidebar component
2. Modify dashboard to show application cards
3. Create studies table page
4. Create profile page
5. Update routing in index.php
6. Add helper functions
7. Add application limit logic

---

## Notes
- Forms for each application type will be created separately
- Each form type has unique fields based on applicant type
- Status workflow: Submitted → Under Review → Approved/Rejected
