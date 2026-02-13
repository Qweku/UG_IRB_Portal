<?php

/**
 * ValidationService Class
 *
 * Centralized validation logic for form inputs, files, and data integrity.
 * Provides comprehensive validation methods used across all application handlers.
 *
 * @package UGIRB\SubmissionEngine\Services
 */

namespace UGIRB\SubmissionEngine\Services;

class ValidationService
{
    /** @var array Allowed MIME types for file uploads */
    private const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'image/jpeg',
        'image/png',
        'application/zip',
        'application/x-zip-compressed'
    ];

    /** @var int Maximum file size in bytes (10MB) */
    private const MAX_FILE_SIZE = 10 * 1024 * 1024;

    /** @var array Valid student statuses */
    private const STUDENT_STATUSES = [
        'undergraduate',
        'masters',
        'phd',
        'postdoctoral',
        'other'
    ];

    /** @var array Valid research types */
    private const RESEARCH_TYPES = [
        'biomedical',
        'social',
        'behavioral',
        'clinical',
        'epidemiological',
        'other'
    ];

    /**
     * Validate email format
     *
     * @param string $email Email to validate
     * @return bool True if valid email
     */
    public function validateEmail(string $email): bool
    {
        return filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Sanitize string input
     *
     * Removes HTML tags and special characters to prevent XSS.
     *
     * @param string|null $input Input string
     * @return string Sanitized string
     */
    public function sanitizeString(?string $input): string
    {
        if ($input === null) {
            return '';
        }
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize array of strings
     *
     * @param array $data Data to sanitize
     * @return array Sanitized data
     */
    public function sanitizeArray(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
            } else {
                $sanitized[$key] = $this->sanitizeString($value);
            }
        }
        return $sanitized;
    }

    /**
     * Validate required fields
     *
     * @param array $data Form data
     * @param array $fields List of required field names
     * @return array ['success' => bool, 'missing' => array]
     */
    public function validateRequired(array $data, array $fields): array
    {
        $missing = [];

        foreach ($fields as $field) {
            if (!isset($data[$field]) || $this->isEmpty($data[$field])) {
                $missing[] = $field;
            }
        }

        return [
            'success' => empty($missing),
            'missing' => $missing
        ];
    }

    /**
     * Validate version number format
     *
     * Accepts formats like: 1.0, 2.1, 10, 1.2.3
     *
     * @param string $version Version number
     * @return bool True if valid
     */
    public function validateVersionNumber(string $version): bool
    {
        return preg_match('/^\d+(\.\d+)*$/', trim($version)) === 1;
    }

    /**
     * Validate date format
     *
     * @param string $date Date string
     * @param string $format Expected format (default: Y-m-d)
     * @return bool True if valid date
     */
    public function validateDate(string $date, string $format = 'Y-m-d'): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Validate date is in the past
     *
     * @param string $date Date string
     * @return bool True if date is in the past
     */
    public function validateDateInPast(string $date): bool
    {
        $d = new \DateTime($date);
        return $d < new \DateTime();
    }

    /**
     * Validate date is in the future
     *
     * @param string $date Date string
     * @return bool True if date is in the future
     */
    public function validateDateInFuture(string $date): bool
    {
        $d = new \DateTime($date);
        return $d > new \DateTime();
    }

    /**
     * Validate date range
     *
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return bool True if end date is after start date
     */
    public function validateDateRange(string $startDate, string $endDate): bool
    {
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        return $end > $start;
    }

    /**
     * Validate phone number format
     *
     * @param string $phone Phone number
     * @return bool True if valid format
     */
    public function validatePhone(string $phone): bool
    {
        // Accept various phone formats
        $cleaned = preg_replace('/[\s\-\(\)]+/', '', $phone);
        return preg_match('/^\+?[0-9]{7,15}$/', $cleaned) === 1;
    }

    /**
     * Validate URL format
     *
     * @param string $url URL to validate
     * @return bool True if valid URL
     */
    public function validateUrl(string $url): bool
    {
        return filter_var(trim($url), FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validate student status
     *
     * @param string $status Student status
     * @return bool True if valid status
     */
    public function validateStudentStatus(string $status): bool
    {
        return in_array(strtolower($status), self::STUDENT_STATUSES, true);
    }

    /**
     * Validate research type
     *
     * @param string $type Research type
     * @return bool True if valid type
     */
    public function validateResearchType(string $type): bool
    {
        return in_array(strtolower($type), self::RESEARCH_TYPES, true);
    }

    /**
     * Validate uploaded files
     *
     * @param array $files Uploaded files from $_FILES
     * @param array $requirements Field requirements ['field' => ['required' => bool, 'label' => string]]
     * @param bool $isSubmit Whether this is a final submission
     * @return array ['success' => bool, 'errors' => array]
     */
    public function validateFiles(array $files, array $requirements, bool $isSubmit = true): array
    {
        $errors = [];
        
        // DEBUG: Log file structure for debugging
        error_log("=== FILE VALIDATION DEBUG ===");
        error_log("Files received: " . json_encode(array_keys($files)));
        error_log("Requirements: " . json_encode($requirements));
        
        // Check required files
        foreach ($requirements as $field => $config) {
            error_log("Checking field: $field, required: " . ($config['required'] ?? false));
            if ($config['required'] && $isSubmit) {
                if (!isset($files[$field]) || $files[$field]['error'] === UPLOAD_ERR_NO_FILE) {
                    $errors[] = "Required file missing: {$config['label']}";
                    error_log("MISSING required file: $field");
                }
            }
        }

        // Check file types and sizes for all uploaded files
        foreach ($files as $field => $file) {
            error_log("Processing file field: $field");
            error_log("File data keys: " . json_encode(array_keys($file ?? [])));
            
            // Skip csrf_token if present
            if ($field === 'csrf_token') {
                continue;
            }
            
            // Handle multiple file uploads (array structure)
            if (isset($file['error']) && is_array($file['error'])) {
                error_log("MULTIPLE file upload detected for field: $field");
                // Process each file in the array
                for ($i = 0; $i < count($file['error']); $i++) {
                    $singleFile = [
                        'name' => $file['name'][$i] ?? '',
                        'type' => $file['type'][$i] ?? '',
                        'tmp_name' => $file['tmp_name'][$i] ?? '',
                        'error' => $file['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                        'size' => $file['size'][$i] ?? 0
                    ];
                    
                    $error = $this->validateSingleFile($singleFile, $field, $requirements);
                    if ($error) {
                        $errors[] = $error;
                    }
                }
                continue;
            }
            
            if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errorMsg = $this->getFileErrorMessage($file['error']);
                error_log("File error for $field: $errorMsg");
                $errors[] = $errorMsg;
                continue;
            }

            // Skip empty file entries
            if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
                continue;
            }

            // Check file size
            if ($file['size'] > self::MAX_FILE_SIZE) {
                $label = $requirements[$field]['label'] ?? $field;
                $errors[] = "File too large for {$label} (max 10MB)";
            }

            // Check MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
                $label = $requirements[$field]['label'] ?? $field;
                $errors[] = "Invalid file type for {$label}";
            }
        }

        error_log("=== END FILE VALIDATION DEBUG ===");
        
        return [
            'success' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Validate a single file
     *
     * @param array $file Single file data
     * @param string $field Field name
     * @param array $requirements Field requirements
     * @return string|null Error message or null if valid
     */
    private function validateSingleFile(array $file, string $field, array $requirements): ?string
    {
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return null; // Skip empty files in multiple upload
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return $this->getFileErrorMessage($file['error']);
        }
        
        // Skip empty file entries
        if (empty($file['tmp_name'])) {
            return null;
        }

        // Check file size
        if ($file['size'] > self::MAX_FILE_SIZE) {
            $label = $requirements[$field]['label'] ?? $field;
            return "File too large for {$label} (max 10MB)";
        }

        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
            $label = $requirements[$field]['label'] ?? $field;
            return "Invalid file type for {$label}";
        }
        
        return null;
    }

    /**
     * Validate JSON string
     *
     * @param string $json JSON string
     * @return bool True if valid JSON
     */
    public function validateJson(string $json): bool
    {
        json_decode($json);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Decode JSON with error handling
     *
     * @param string $json JSON string
     * @return array|null Decoded array or null on error
     */
    public function decodeJson(string $json): ?array
    {
        $decoded = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }
        return $decoded;
    }

    /**
     * Validate array is not empty
     *
     * @param mixed $value Value to check
     * @return bool True if empty
     */
    private function isEmpty(mixed $value): bool
    {
        if (is_array($value)) {
            return empty($value);
        }
        return trim((string) $value) === '';
    }

    /**
     * Get human-readable file upload error message
     *
     * @param mixed $errorCode Upload error code (can be int or array)
     * @return string Error message
     */
    private function getFileErrorMessage(mixed $errorCode): string
    {
        // Handle array error codes (e.g., from multiple file uploads)
        if (is_array($errorCode)) {
            return 'Invalid file upload configuration';
        }
        
        // Handle non-numeric error codes
        if (!is_int($errorCode)) {
            return 'Unknown upload error';
        }
        
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'File exceeds server maximum size (10MB)',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form maximum size',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload blocked by extension',
            default => 'Unknown upload error'
        };
    }

    /**
     * Get allowed MIME types
     *
     * @return array Allowed MIME types
     */
    public function getAllowedMimeTypes(): array
    {
        return self::ALLOWED_MIME_TYPES;
    }

    /**
     * Get maximum file size
     *
     * @return int Maximum file size in bytes
     */
    public function getMaxFileSize(): int
    {
        return self::MAX_FILE_SIZE;
    }

    /**
     * Get valid student statuses
     *
     * @return array Valid statuses
     */
    public function getStudentStatuses(): array
    {
        return self::STUDENT_STATUSES;
    }

    /**
     * Get valid research types
     *
     * @return array Valid research types
     */
    public function getResearchTypes(): array
    {
        return self::RESEARCH_TYPES;
    }

    /**
     * Validate array contains only expected keys
     *
     * @param array $data Input data
     * @param array $allowedKeys Allowed keys
     * @return bool True if all keys are allowed
     */
    public function validateKeys(array $data, array $allowedKeys): bool
    {
        $providedKeys = array_keys($data);
        $invalidKeys = array_diff($providedKeys, $allowedKeys);
        return empty($invalidKeys);
    }

    /**
     * Validate string length range
     *
     * @param string $string String to validate
     * @param int $min Minimum length
     * @param int $max Maximum length
     * @return bool True if within range
     */
    public function validateLength(string $string, int $min = 0, int $max = 10000): bool
    {
        $length = strlen($string);
        return $length >= $min && $length <= $max;
    }

    /**
     * Validate numeric range
     *
     * @param mixed $number Number to validate
     * @param float|null $min Minimum value
     * @param float|null $max Maximum value
     * @return bool True if within range
     */
    public function validateNumber(mixed $number, ?float $min = null, ?float $max = null): bool
    {
        if (!is_numeric($number)) {
            return false;
        }

        $num = (float) $number;

        if ($min !== null && $num < $min) {
            return false;
        }

        if ($max !== null && $num > $max) {
            return false;
        }

        return true;
    }

    /**
     * Validate integer range
     *
     * @param mixed $number Number to validate
     * @param int|null $min Minimum value
     * @param int|null $max Maximum value
     * @return bool True if within range
     */
    public function validateInteger(mixed $number, ?int $min = null, ?int $max = null): bool
    {
        if (!is_numeric($number) || strpos((string) $number, '.') !== false) {
            return false;
        }

        $num = (int) $number;

        if ($min !== null && $num < $min) {
            return false;
        }

        if ($max !== null && $num > $max) {
            return false;
        }

        return true;
    }
}
