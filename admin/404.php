<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found - UG HARES Software</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="/admin/assets/css/style.css" rel="stylesheet">
    <style>
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f5f7fb 0%, #e8ecf4 100%);
        }

        .error-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            padding: 60px 40px;
            text-align: center;
            max-width: 500px;
            animation: fadeIn 0.5s ease-out;
        }

        .error-icon {
            font-size: 80px;
            color: #243f81;
            margin-bottom: 30px;
            animation: bounce 2s infinite;
        }

        .error-code {
            font-size: 72px;
            font-weight: 800;
            color: #243f81;
            line-height: 1;
            margin-bottom: 15px;
        }

        .error-title {
            font-size: 28px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 15px;
        }

        .error-message {
            font-size: 16px;
            color: #6c757d;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .error-actions .btn {
            padding: 14px 28px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 5px;
        }

        .error-actions .btn-primary {
            background: linear-gradient(135deg, #243f81 0%, #325bc5 100%);
            border: none;
            color: white;
            box-shadow: 0 4px 15px rgba(36, 63, 129, 0.3);
        }

        .error-actions .btn-primary:hover {
            background: linear-gradient(135deg, #1a3369 0%, #243f81 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(36, 63, 129, 0.4);
            color: white;
        }

        .error-actions .btn-outline {
            background: transparent;
            border: 2px solid #243f81;
            color: #243f81;
        }

        .error-actions .btn-outline:hover {
            background: #243f81;
            color: white;
            transform: translateY(-2px);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-20px);
            }
            60% {
                transform: translateY(-10px);
            }
        }

        @media (max-width: 576px) {
            .error-card {
                margin: 20px;
                padding: 40px 25px;
            }

            .error-icon {
                font-size: 60px;
            }

            .error-code {
                font-size: 56px;
            }

            .error-title {
                font-size: 22px;
            }

            .error-actions .btn {
                width: 100%;
                margin: 5px 0;
            }
        }
    </style>
</head>

<body class="error-page">
    <div class="error-card">
        <div class="error-icon">
            <i class="fas fa-compass"></i>
        </div>
        <div class="error-code">404</div>
        <h1 class="error-title">Page Not Found</h1>
        <p class="error-message">
            Oops! The page you're looking for doesn't exist or has been moved.
            Let's get you back on track.
        </p>
        <div class="error-actions">
            <a href="/dashboard" class="btn btn-primary">
                <i class="fas fa-home me-2"></i>Go to Dashboard
            </a>
            <a href="javascript:history.back()" class="btn btn-outline">
                <i class="fas fa-arrow-left me-2"></i>Go Back
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
