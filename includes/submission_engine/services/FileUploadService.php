<?php

/**
 * FileUploadService Class
 *
 * Handles secure file uploads with validation, safe filename generation,
 * and directory management. Used across all application handlers.
 *
 * @package UGIRB\SubmissionEngine\Services
 */

namespace UGIRB\SubmissionEngine\Services;

class FileUploadService
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
        'application/x-zip-compressed',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/vnd.ms-powerpoint'
    ];

    /** @var int Maximum file size in bytes (10MB) */
    private const MAX_FILE_SIZE = 10 * 1024 * 1024;

    /** @var string Default upload directory permissions */
    private const DIRECTORY_PERMISSIONS = '0755';

    /** @var string Base upload path */
    private string $baseUploadPath;

    /**
     * Constructor
     *
     * @param string|null $baseUploadPath Base path for uploads (defaults to ../../uploads)
     */
    public function __construct(?string $baseUploadPath = null)
    {
        $this->baseUploadPath = $baseUploadPath ?? dirname(__DIR__, 2) . '/uploads';
    }

    /**
     * Upload a single file
     *
     * @param array $file Uploaded file array (from $_FILES)
     * @param string $fieldName Field name for naming
     * @param string $uploadDir Upload directory (relative to base)
     * @return array ['success' => bool, 'path' => string|null, 'error' => string|null]
     */
    public function upload(array $file, string $fieldName, string $uploadDir): array
    {
        // Check if no file was uploaded
        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return [
                'success' => true,
                'path' => null,
                'error' => null
            ];
        }

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'path' => null,
                'error' => $this->getErrorMessage($file['error'])
            ];
        }

        // Validate file
        $validation = $this->validateFile($file);
        if (!$validation['success']) {
            return $validation;
        }

        // Create upload directory
        $fullPath = $this->baseUploadPath . '/' . $uploadDir;
        if (!$this->createDirectory($fullPath)) {
            return [
                'success' => false,
                'path' => null,
                'error' => 'Failed to create upload directory'
            ];
        }

        // Generate safe filename
        $newFilename = $this->generateSafeFilename($file['name']);
        $destination = $fullPath . '/' . $newFilename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            // Store path relative to base for database
            $relativePath = $uploadDir . '/' . $newFilename;
            return [
                'success' => true,
                'path' => $relativePath,
                'full_path' => $destination,
                'error' => null
            ];
        }

        return [
            'success' => false,
            'path' => null,
            'error' => 'Failed to move uploaded file'
        ];
    }

    /**
     * Upload multiple files
     *
     * @param array $files Uploaded files array (from $_FILES)
     * @param string $fieldName Field name
     * @param string $uploadDir Upload directory (relative to base)
     * @return array ['success' => bool, 'paths' => array, 'error' => string|null]
     */
    public function uploadMultiple(array $files, string $fieldName, string $uploadDir): array
    {
        $normalizedFiles = $this->normalizeFilesArray($files);

        if (empty($normalizedFiles)) {
            return [
                'success' => true,
                'paths' => [],
                'error' => null
            ];
        }

        $uploadedPaths = [];
        $uploadErrors = [];

        foreach ($normalizedFiles as $i => $singleFile) {
            $result = $this->upload($singleFile, $fieldName . '_' . $i, $uploadDir);

            if (!$result['success']) {
                return [
                    'success' => false,
                    'paths' => null,
                    'error' => $result['error']
                ];
            }

            if ($result['path'] !== null) {
                $uploadedPaths[] = $result['path'];
            }
        }

        return [
            'success' => true,
            'paths' => $uploadedPaths,
            'error' => null
        ];
    }

    /**
     * Validate a single file
     *
     * @param array $file File array
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function validateFile(array $file): array
    {
        // Check if file exists
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return [
                'success' => false,
                'error' => 'No file data provided'
            ];
        }

        // Check file size
        if ($file['size'] > self::MAX_FILE_SIZE) {
            return [
                'success' => false,
                'error' => 'File exceeds maximum size of 10MB'
            ];
        }

        // Check minimum file size (prevent empty files)
        if ($file['size'] === 0) {
            return [
                'success' => false,
                'error' => 'File is empty'
            ];
        }

        // Verify uploaded file
        if (!is_uploaded_file($file['tmp_name'])) {
            return [
                'success' => false,
                'error' => 'File was not uploaded via HTTP POST'
            ];
        }

        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
            return [
                'success' => false,
                'error' => 'Invalid file type: ' . $mimeType
            ];
        }

        return [
            'success' => true,
            'error' => null,
            'mime_type' => $mimeType
        ];
    }

    /**
     * Create directory recursively
     *
     * @param string $path Directory path
     * @param string $permissions Directory permissions
     * @return bool True if directory exists or was created
     */
    public function createDirectory(string $path, string $permissions = self::DIRECTORY_PERMISSIONS): bool
    {
        if (!is_dir($path)) {
            return mkdir($path, octdec($permissions), true);
        }
        return true;
    }

    /**
     * Generate a safe filename
     *
     * Removes special characters and adds timestamp to prevent conflicts.
     *
     * @param string $originalName Original filename
     * @return string Safe filename
     */
    public function generateSafeFilename(string $originalName): string
    {
        // Get file info
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);

        // Remove special characters, keeping only alphanumeric, underscore, hyphen, and spaces
        $safeName = preg_replace('/[^\w\s\-]/u', '', $baseName);

        // Replace multiple spaces/underscores with single underscore
        $safeName = preg_replace('/[\s_]+/', '_', $safeName);

        // Remove leading/trailing underscores
        $safeName = trim($safeName, '_');

        // Limit length
        if (strlen($safeName) > 100) {
            $safeName = substr($safeName, 0, 100);
        }

        // Add timestamp to prevent conflicts
        $timestamp = time();
        $randomSuffix = bin2hex(random_bytes(4));

        // Build filename
        $filename = $safeName . '_' . $timestamp . '_' . $randomSuffix;

        // Add extension if exists
        if (!empty($extension)) {
            $filename .= '.' . $extension;
        }

        return $filename;
    }

    /**
     * Delete a file
     *
     * @param string $path File path (relative to base or absolute)
     * @return bool True if deleted or doesn't exist
     */
    public function delete(string $path): bool
    {
        $fullPath = $this->resolvePath($path);

        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }

        return true;
    }

    /**
     * Delete multiple files
     *
     * @param array $paths Array of file paths
     * @return array ['success' => bool, 'deleted' => array, 'failed' => array]
     */
    public function deleteMultiple(array $paths): array
    {
        $deleted = [];
        $failed = [];

        foreach ($paths as $path) {
            if ($this->delete($path)) {
                $deleted[] = $path;
            } else {
                $failed[] = $path;
            }
        }

        return [
            'success' => empty($failed),
            'deleted' => $deleted,
            'failed' => $failed
        ];
    }

    /**
     * Copy a file
     *
     * @param string $source Source path
     * @param string $destination Destination path
     * @return bool True if copied successfully
     */
    public function copy(string $source, string $destination): bool
    {
        $fullSource = $this->resolvePath($source);
        $fullDest = $this->resolvePath($destination);

        return copy($fullSource, $fullDest);
    }

    /**
     * Get file info
     *
     * @param string $path File path
     * @return array|null File info or null if not found
     */
    public function getFileInfo(string $path): ?array
    {
        $fullPath = $this->resolvePath($path);

        if (!file_exists($fullPath)) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fullPath);
        finfo_close($finfo);

        return [
            'path' => $path,
            'full_path' => $fullPath,
            'exists' => true,
            'size' => filesize($fullPath),
            'size_formatted' => $this->formatFileSize(filesize($fullPath)),
            'mime_type' => $mimeType,
            'extension' => pathinfo($fullPath, PATHINFO_EXTENSION),
            'modified' => date('Y-m-d H:i:s', filemtime($fullPath)),
            'is_readable' => is_readable($fullPath),
            'is_writable' => is_writable($fullPath)
        ];
    }

    /**
     * Check if file exists
     *
     * @param string $path File path
     * @return bool True if exists
     */
    public function exists(string $path): bool
    {
        return file_exists($this->resolvePath($path));
    }

    /**
     * Resolve path to absolute path
     *
     * @param string $path Relative or absolute path
     * @return string Absolute path
     */
    public function resolvePath(string $path): string
    {
        // If already absolute, return as-is
        if ($this->isAbsolutePath($path)) {
            return $path;
        }

        // If path starts with base upload path, resolve it
        if (strpos($path, $this->baseUploadPath) === 0) {
            return $path;
        }

        // Otherwise, prepend base upload path
        return $this->baseUploadPath . '/' . ltrim($path, '/');
    }

    /**
     * Get base upload path
     *
     * @return string Base upload path
     */
    public function getBasePath(): string
    {
        return $this->baseUploadPath;
    }

    /**
     * Set base upload path
     *
     * @param string $path Base upload path
     */
    public function setBasePath(string $path): void
    {
        $this->baseUploadPath = rtrim($path, '/');
    }

    /**
     * Normalize files array to always be an array of files
     *
     * PHP's $_FILES format varies between single and multiple file uploads.
     *
     * @param array $files Files array from $_FILES
     * @return array Normalized array of file arrays
     */
    private function normalizeFilesArray(array $files): array
    {
        // If 'name' is not an array, it's a single file upload
        if (!is_array($files['name'])) {
            return [[
                'name' => $files['name'] ?? '',
                'type' => $files['type'] ?? '',
                'tmp_name' => $files['tmp_name'] ?? '',
                'error' => $files['error'] ?? UPLOAD_ERR_NO_FILE,
                'size' => $files['size'] ?? 0
            ]];
        }

        // Multiple file upload - normalize each file
        $normalized = [];
        $fileCount = count($files['name']);

        for ($i = 0; $i < $fileCount; $i++) {
            $normalized[] = [
                'name' => $files['name'][$i] ?? '',
                'type' => $files['type'][$i] ?? '',
                'tmp_name' => $files['tmp_name'][$i] ?? '',
                'error' => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size' => $files['size'][$i] ?? 0
            ];
        }

        return $normalized;
    }

    /**
     * Check if path is absolute
     *
     * @param string $path Path to check
     * @return bool True if absolute path
     */
    private function isAbsolutePath(string $path): bool
    {
        // Windows paths
        if (preg_match('/^[A-Za-z]:/', $path)) {
            return true;
        }

        // Unix-style absolute paths
        return str_starts_with($path, '/');
    }

    /**
     * Format file size for display
     *
     * @param int $bytes File size in bytes
     * @return string Formatted size
     */
    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get human-readable error message
     *
     * @param int $errorCode Upload error code
     * @return string Error message
     */
    private function getErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'File exceeds server maximum size (' . $this->formatFileSize(self::MAX_FILE_SIZE) . ')',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form maximum size',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder for uploads',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk - check permissions',
            UPLOAD_ERR_EXTENSION => 'Upload blocked by server extension',
            default => 'Unknown upload error (code: ' . $errorCode . ')'
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
}
