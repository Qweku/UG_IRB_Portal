<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UG HARES Software</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="/admin/assets/css/admin-commons.css" rel="stylesheet">
    <link href="/admin/assets/css/sidebar.css" rel="stylesheet">
    <link href="/admin/assets/css/profile.css" rel="stylesheet">
    <link href="/admin/assets/css/applicant-dashboard.css" rel="stylesheet">
    <link href="/admin/assets/css/style.css" rel="stylesheet">
    <link href="/admin/assets/css/header.css" rel="stylesheet">
    <link href="/admin/assets/css/dashboard.css" rel="stylesheet">
    <link href="/admin/assets/css/administration.css" rel="stylesheet">
    <link href="/admin/assets/css/follow-up.css" rel="stylesheet">
    <link href="/admin/assets/css/letter-manager.css" rel="stylesheet">
    <link href="/admin/assets/css/shared-forms.css" rel="stylesheet">
    <link href="/admin/assets/css/create-contact.css" rel="stylesheet">
    <link href="/admin/assets/css/account-information.css" rel="stylesheet">
    <link href="/admin/assets/css/post-meeting.css" rel="stylesheet">
    <link href="/admin/assets/css/reviewer-dashboard.css" rel="stylesheet">
</head>

<body>
    <!-- Premium Navbar -->
    <?php
    $navbar_display = is_admin_logged_in() || is_applicant_logged_in() || is_reviewer_logged_in() ? 'block' : 'none';
    $home_path = is_applicant_logged_in() ? '/applicant-dashboard' : (is_reviewer_logged_in() ? '/reviewer-dashboard' : '/dashboard');
    $userName = $_SESSION['full_name'] ?? 'User';
    $userRole = $_SESSION['role'] ?? 'admin';
    ?>

    <nav class="navbar navbar-expand-lg premium-navbar" style="display: <?= htmlspecialchars($navbar_display); ?>;">
        <div class="container-fluid">
            <!-- Sidebar Toggle (Mobile) -->
            <button class="sidebar-toggle me-3 d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Brand -->
            <a class="navbar-brand-premium" href="<?= htmlspecialchars($home_path); ?>">
                <img src="/admin/assets/images/ug_logo_white.png" alt="Noguchi Logo" class="brand-logo-img">
                <div class="brand-text-premium">
                    <span class="brand-title">UG HARES</span>
                    <span class="brand-subtitle">Ethics Portal</span>
                </div>
            </a>

            <!-- Mobile Toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Content -->
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <div class="nav-actions">
                    <!-- Notifications -->
                    <a href="#" class="nav-btn" title="Notifications">
                        <i class="fas fa-bell"></i>
                    </a>

                    <!-- Settings -->
                    <a href="#" class="nav-btn" title="Settings">
                        <i class="fas fa-cog"></i>
                    </a>

                    <!-- User Info -->
                    <!-- <div class="user-info-premium">
                        <div class="user-avatar-nav">
                            <?php //echo strtoupper(substr($userName, 0, 1)); 
                            ?>
                        </div>
                        <div class="user-text-nav">
                            <span class="user-name-nav"><?php //echo htmlspecialchars($userName); 
                                                        ?></span>
                            <span class="user-role-nav"><?php //echo htmlspecialchars($userRole); 
                                                        ?></span>
                        </div>
                    </div> -->

                    <!-- Logout -->
                    <a href="/logout" class="nav-btn logout" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>