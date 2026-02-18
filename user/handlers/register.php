<?php

/**
 * User Registration Handler
 * Handles new applicant registration with CSRF protection and validation
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_name('ug_irb_session');
    session_start();
}

// Sync CSRF token between form and session
// Handle session state inconsistencies by accepting the form's token
if (isset($_POST['csrf_token']) && !empty($_POST['csrf_token'])) {
    $postToken = $_POST['csrf_token'];
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    
    // If tokens differ, sync session token with form token
    if ($postToken !== $sessionToken) {
        $_SESSION['csrf_token'] = $postToken;
    }
}

// Set content type to JSON
header('Content-Type: application/json');

// Include required files
require_once __DIR__ . '/../../includes/functions/csrf.php';
require_once __DIR__ . '/../../includes/config/database.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => ''
];

try {
    // Validate CSRF token
    if (!csrf_validate()) {
        $response['message'] = 'Invalid CSRF token. Please refresh the page and try again.';
        echo json_encode($response);
        exit;
    }

    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response['message'] = 'Invalid request method.';
        echo json_encode($response);
        exit;
    }

    // Get and sanitize input
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $application_type = trim($_POST['application_type'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = trim($_POST['role'] ?? 'applicant');
    $studentId =trim($_POST['student_id'] ?? '');

    // Validate required fields
    $errors = [];

    if (empty($first_name)) {
        $errors[] = 'First name is required.';
    } elseif (strlen($first_name) < 2) {
        $errors[] = 'First name must be at least 2 characters.';
    } elseif (!preg_match('/^[a-zA-Z\s\-]+$/', $first_name)) {
        $errors[] = 'First name can only contain letters, spaces, and hyphens.';
    }

    if (!empty($middle_name) && strlen($middle_name) < 2) {
        $errors[] = 'Middle name must be at least 2 characters.';
    } elseif (!empty($middle_name) && !preg_match('/^[a-zA-Z\s\-]+$/', $middle_name)) {
        $errors[] = 'Middle name can only contain letters, spaces, and hyphens.';
    }

    if (empty($last_name)) {
        $errors[] = 'Last name is required.';
    } elseif (strlen($last_name) < 2) {
        $errors[] = 'Last name must be at least 2 characters.';
    } elseif (!preg_match('/^[a-zA-Z\s\-]+$/', $last_name)) {
        $errors[] = 'Last name can only contain letters, spaces, and hyphens.';
    }

    if (empty($phone_number)) {
        $errors[] = 'Phone number is required.';
    } elseif (!preg_match('/^[\d\s\+\-\(\)]{10,}$/', $phone_number)) {
        $errors[] = 'Please enter a valid phone number.';
    }

    if (empty($email)) {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (empty($application_type)) {
        $errors[] = 'Application type is required.';
    } elseif (!in_array($application_type, ['student', 'nmimr', 'non_nmimr'], true)) {
        $errors[] = 'Invalid application type selected.';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number.';
    } elseif (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\'\\:"|,.<>\/?]/', $password)) {
        $errors[] = 'Password must contain at least one special character.';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    // Validate role (only allow applicant for self-registration)
    if ($role !== 'applicant') {
        $errors[] = 'Invalid role specified.';
    }

    // If there are validation errors, return them
    if (!empty($errors)) {
        $response['message'] = implode(' ', $errors);
        echo json_encode($response);
        exit;
    }

    // Connect to database
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        $response['message'] = 'Database connection failed. Please try again later.';
        echo json_encode($response);
        exit;
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        $response['message'] = 'An account with this email already exists. Please use a different email or login.';
        echo json_encode($response);
        exit;
    }

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Prepare full name
    $full_name = trim($first_name . ' ' . $middle_name . ' ' . $last_name);
    $full_name = preg_replace('/\s+/', ' ', $full_name); // Remove extra spaces

    // Insert new user
    $stmt = $conn->prepare("
        INSERT INTO users (           
            full_name,
            phone_number,
            email,
            password_hash,
            role,
            status,
            is_first,
            created_at,
            updated_at
        ) VALUES (
             ?, ?, ?, ?, 'applicant', 'active', 0, NOW(), NOW()
        )
    ");

    $result = $stmt->execute([
        $full_name,
        $phone_number,
        $email,
        $password_hash
    ]);

    if (!$result) {
        $response['message'] = 'Failed to create account. Please try again.';
        echo json_encode($response);
        exit;
    }

    // Add user to applicant user table
    $stmt = $conn->prepare("
        INSERT INTO applicant_users (user_id, first_name, middle_name, last_name, phone_number, email,
            student_id, applicant_type, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    $stmt->execute([
        $conn->lastInsertId(),
        $first_name,
        $middle_name,
        $last_name,
        $phone_number,
        $email,
        $studentId,
        $application_type
    ]);

    // Get the new user ID
    $user_id = $conn->lastInsertId();

    // Log the registration (optional - for audit trail)
    error_log("New user registered: ID=$user_id, Email=$email, Role=applicant");

    // Success response
    $response['success'] = true;
    $response['message'] = 'Your account has been created successfully! Redirecting to login...';
    $response['redirect'] = '/login?registered=1';
} catch (PDOException $e) {
    // Log the error
    error_log("Registration error: " . $e->getMessage());
    $response['message'] = 'A database error occurred. Please try again later.';
} catch (Exception $e) {
    // Log any other error
    error_log("Registration error: " . $e->getMessage());
    $response['message'] = 'An unexpected error occurred. Please try again.';
}

// Return JSON response
echo json_encode($response);
exit;
