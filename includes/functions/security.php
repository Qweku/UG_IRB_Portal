<?php
/**
 * Security Helper Functions
 * Provides common security utilities for input sanitization, output encoding, and validation
 */

/**
 * Sanitize input to prevent XSS attacks
 * @param mixed $data Input data to sanitize
 * @return string Sanitized string
 */
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Escape output for safe HTML rendering
 * @param string $string String to escape
 * @return string Escaped string
 */
function escape_output($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Validate that a string contains only allowed characters
 * @param string $input Input to validate
 * @param string $pattern Regex pattern of allowed characters
 * @return bool True if valid, false otherwise
 */
function validate_pattern($input, $pattern) {
    return preg_match($pattern, $input) === 1;
}

/**
 * Validate email address format
 * @param string $email Email to validate
 * @return bool True if valid email format
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate URL format
 * @param string $url URL to validate
 * @return bool True if valid URL format
 */
function validate_url($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Validate integer input
 * @param mixed $input Input to validate
 * @param int $min Minimum allowed value (optional)
 * @param int $max Maximum allowed value (optional)
 * @return int|null Validated integer or null if invalid
 */
function validate_int($input, $min = null, $max = null) {
    $value = filter_var($input, FILTER_VALIDATE_INT);
    if ($value === false) {
        return null;
    }
    if ($min !== null && $value < $min) {
        return null;
    }
    if ($max !== null && $value > $max) {
        return null;
    }
    return $value;
}

/**
 * Validate file upload
 * @param array $file $_FILES array element
 * @param array $allowed_types Array of allowed MIME types
 * @param int $max_size Maximum file size in bytes
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validate_file_upload($file, $allowed_types = [], $max_size = 0) {
    $result = ['valid' => true, 'error' => null];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $result['valid'] = false;
        $result['error'] = 'File upload error: ' . $file['error'];
        return $result;
    }
    
    // Check file size if specified
    if ($max_size > 0 && $file['size'] > $max_size) {
        $result['valid'] = false;
        $result['error'] = 'File size exceeds maximum allowed';
        return $result;
    }
    
    // Check MIME type if allowed types specified
    if (!empty($allowed_types)) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed_types, true)) {
            $result['valid'] = false;
            $result['error'] = 'File type not allowed';
            return $result;
        }
    }
    
    return $result;
}

/**
 * Validate file path is within allowed directory (prevent path traversal)
 * @param string $file_path The file path to validate
 * @param string $allowed_dir The allowed base directory
 * @return bool True if path is valid
 */
function validate_file_path($file_path, $allowed_dir) {
    $real_path = realpath($file_path);
    $real_allowed_dir = realpath($allowed_dir);
    
    if ($real_path === false || $real_allowed_dir === false) {
        return false;
    }
    
    return strpos($real_path, $real_allowed_dir) === 0;
}

/**
 * Hash password securely using bcrypt
 * @param string $password Password to hash
 * @return string Hashed password
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password against hash
 * @param string $password Password to verify
 * @param string $hash Hash to verify against
 * @return bool True if password matches
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate a secure random token
 * @param int $length Length of token in bytes
 * @return string Hexadecimal token string
 */
function generate_secure_token($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Whitelist validation for dynamic values (e.g., ORDER BY columns)
 * @param mixed $value Value to validate
 * @param array $allowed_values Array of allowed values
 * @param mixed $default Default value if not in whitelist
 * @return mixed Validated value
 */
function whitelist($value, $allowed_values, $default) {
    if (in_array($value, $allowed_values, true)) {
        return $value;
    }
    return $default;
}

/**
 * Secure redirect with URL encoding
 * @param string $url URL to redirect to
 * @param array $params Optional parameters to append
 */
function secure_redirect($url, $params = []) {
    if (!empty($params)) {
        $url .= (strpos($url, '?') !== false ? '&' : '?') . http_build_query($params);
    }
    header('Location: ' . $url);
    exit;
}

/**
 * Set secure HTTP headers
 */
function set_security_headers() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

/**
 * Get client IP address securely (with proxy support)
 * @return string Client IP address
 */
function get_client_ip() {
    $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) && !empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            // Handle comma-separated IPs in X-Forwarded-For
            if (strpos($ip, ',') !== false) {
                $ips = array_map('trim', explode(',', $ip));
                $ip = $ips[0];
            }
            // Validate IP format
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    
    return '0.0.0.0';
}
