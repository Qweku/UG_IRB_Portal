<?php
// require_once __DIR__ . '/../database/db_functions.php';

// echo '<pre> Admin Header Included </pre>';
// echo "\n";
// echo '<pre> User Logged In: ' . (is_admin_logged_in() ? 'Yes' : 'No') . ' </pre>';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Noguchi ProIRB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="/admin/assets/style.css" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
    <?php
    $navbar_display = is_admin_logged_in() ? 'block' : 'none';
    ?>
    <nav class="navbar navbar-expand-lg navbar-dark" style="display: <?= htmlspecialchars($navbar_display); ?>;">
        <div class="container-fluid">
            <button class="btn btn-outline-light me-2 d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand" href="/">
                <img src="/admin/assets/images/ug-nmimr-logo.jpg" alt="Noguchi Logo" height="50" class="d-inline-block align-text-top me-2">
                ProIRB
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
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