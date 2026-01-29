<?php
if (is_admin_logged_in()) {
    header('Location: /dashboard');
    exit;
} elseif (is_applicant_logged_in()) {
    header('Location: /applicant-dashboard');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UG HARES Software</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="/admin/assets/style.css" rel="stylesheet">
    <style>
        body {
            /* background: linear-gradient(135deg, var(--royal-blue) 0%, var(--royal-blue-light) 100%); */
            min-height: 100vh;
        }

        .login-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 100%;
        }

        .login-header {
            background: var(--royal-blue);
            color: white;
            border-radius: 8px 8px 0 0;
            padding: 20px;
            text-align: center;
        }

        .login-header i {
            font-size: 48px;
            margin-bottom: 10px;
        }

        .form-control:focus {
            border-color: var(--royal-blue);
            box-shadow: 0 0 0 0.2rem rgba(36, 63, 129, 0.25);
        }
    </style>
</head>

<body>
    <div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center">
        <div class="login-card">
            <div class="login-header">
                <img src="/admin/assets/images/ug_logo_white.png" alt="Noguchi Logo" height="70" class="d-inline-block align-text-top me-2">
                <h4>UG HARES Software</h4>
                <p>Please sign in to continue</p>
            </div>
            <div class="card-body p-4">
                <form action="/authenticate" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Sign In</button>
                    </div>
                </form>
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger mt-3">Invalid email or password. Please try again.</div>
                <?php endif; ?>
                <div class="text-center mt-3">
                    <a href="#" class="text-decoration-none">Forgot password?</a>
                </div>
            </div>
        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>