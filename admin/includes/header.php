<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UG HARES Software</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="/admin/assets/style.css" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
    <?php
    $navbar_display = is_admin_logged_in() || is_applicant_logged_in() ? 'block' : 'none';
    $home_path =  is_applicant_logged_in() ? '/applicant-dashboard' : '/';
    ?>
    <nav class="navbar navbar-expand-lg navbar-dark" style="display: <?= htmlspecialchars($navbar_display); ?>;">
        
    <div class="container-fluid">
            <button class="btn btn-outline-light me-2 d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand d-flex" href="<?= htmlspecialchars($home_path); ?>">
                <img src="/admin/assets/images/ug_logo_white.png" alt="Noguchi Logo" height="70" class="d-inline-block align-text-top me-2">
                <h2 class="m-auto"><strong>UG HARES Software</strong></h2>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end ms-auto" id="navbarNav">
                <div class="d-flex ">
                    <!-- <span class="navbar-text me-3" id="session-timer">
                        <i class="fas fa-clock me-1"></i>
                        <span id="timer-display">30:00</span>
                    </span> -->
                </div>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-bell"></i></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-cog"></i></a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/logout"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
                    </li>
                </ul>

            </div>
        </div>
    </nav>

    <!-- <script>
        // Session timer variables
        // const loginTime = <?php 
        //echo isset($_SESSION['login_time']) ? $_SESSION['login_time'] : 'null'; ?>;
        // const sessionDuration = <?php 
        //echo ini_get('session.gc_maxlifetime'); ?>; // Session lifetime in seconds from PHP config
    </script> -->