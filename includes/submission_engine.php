<?php

/**
 * Unified Submission Engine Entry Point
 *
 * This file provides a unified interface for submitting applications
 * of any type using the new submission engine architecture.
 *
 * Usage:
 *   require_once 'includes/submission_engine.php';
 *   $result = SubmissionEngine::submit('student', $_POST, $_FILES);
 *
 * @package UGIRB\SubmissionEngine
 */

// Define consistent session name across entire application
defined('CSRF_SESSION_NAME') || define('CSRF_SESSION_NAME', 'ug_irb_session');

// Set custom session name BEFORE starting session (only if session not already active)
if (session_status() === PHP_SESSION_NONE) {
    session_name(CSRF_SESSION_NAME);
    session_start();
} elseif (session_name() !== CSRF_SESSION_NAME) {
    // Session already active with different name - log warning but continue
    // Can't change session name mid-request
}

// Load submission engine classes
require_once __DIR__ . '/submission_engine/interfaces/IApplicationHandler.php';
require_once __DIR__ . '/submission_engine/services/SessionManager.php';
require_once __DIR__ . '/submission_engine/services/ValidationService.php';
require_once __DIR__ . '/submission_engine/services/FileUploadService.php';
require_once __DIR__ . '/submission_engine/services/ProtocolNumberGenerator.php';
require_once __DIR__ . '/submission_engine/services/EmailService.php';
require_once __DIR__ . '/submission_engine/handlers/BaseAbstractHandler.php';
require_once __DIR__ . '/submission_engine/handlers/StudentHandler.php';
require_once __DIR__ . '/submission_engine/handlers/NmimrHandler.php';
require_once __DIR__ . '/submission_engine/handlers/NonNmimrHandler.php';
require_once __DIR__ . '/submission_engine/factories/ApplicationHandlerFactory.php';

/**
 * Unified SubmissionEngine Class
 *
 * Provides a simple interface for submitting applications.
 */
class SubmissionEngine
{
    /** @var bool Whether to use new engine or legacy handlers */
    private static bool $useNewEngine = true;

    /** @var ApplicationHandlerFactory|null Factory instance */
    private static ?object $factory = null;

    /**
     * Submit an application
     *
     * @param string $applicationType Application type (student, nmimr, non_nmimr)
     * @param array $postData POST data
     * @param array $filesData FILES data
     * @return array Result with success status and message
     */
    public static function submit(
        string $applicationType,
        array $postData = [],
        array $filesData = []
    ): array {
        // Store original POST and FILES
        self::storeOriginalData($postData, $filesData);

        // Check if new engine is enabled
        if (!self::$useNewEngine) {
            return self::handleLegacy($applicationType);
        }

        try {
            // Initialize database connection
            $db = self::getDatabaseConnection();
            if (!$db) {
                return [
                    'success' => false,
                    'message' => 'Database connection failed. Please try again later.'
                ];
            }

            // Create factory and handler
            $factory = new \UGIRB\SubmissionEngine\Factories\ApplicationHandlerFactory($db);
            $handler = $factory->createHandler($applicationType);

            // Set draft mode if specified
            if (($postData['save_draft'] ?? false) === '1' ||
                ($postData['save_draft'] ?? false) === 'true') {
                $handler->setDraftMode(true);
            }

            // Handle submission
            $result = $handler->handleSubmission();

            // Set JSON response header
            header('Content-Type: application/json');

            return $result;

        } catch (\Exception $e) {
            error_log('SubmissionEngine Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'An error occurred while processing your application.',
                'errors' => [
                    'system' => $e->getMessage()
                ]
            ];
        }
    }

    /**
     * Submit student application
     *
     * @param array $postData POST data
     * @param array $filesData FILES data
     * @return array Result
     */
    public static function submitStudent(array $postData = [], array $filesData = []): array
    {
        return self::submit('student', $postData, $filesData);
    }

    /**
     * Submit NMIMR application
     *
     * @param array $postData POST data
     * @param array $filesData FILES data
     * @return array Result
     */
    public static function submitNmimr(array $postData = [], array $filesData = []): array
    {
        return self::submit('nmimr', $postData, $filesData);
    }

    /**
     * Submit Non-NMIMR application
     *
     * @param array $postData POST data
     * @param array $filesData FILES data
     * @return array Result
     */
    public static function submitNonNmimr(array $postData = [], array $filesData = []): array
    {
        return self::submit('non_nmimr', $postData, $filesData);
    }

    /**
     * Enable or disable new engine
     *
     * @param bool $useNewEngine Whether to use new engine
     */
    public static function setUseNewEngine(bool $useNewEngine): void
    {
        self::$useNewEngine = $useNewEngine;
    }

    /**
     * Check if new engine is enabled
     *
     * @return bool
     */
    public static function isUsingNewEngine(): bool
    {
        return self::$useNewEngine;
    }

    /**
     * Get supported application types
     *
     * @return array
     */
    public static function getSupportedTypes(): array
    {
        return ['student', 'nmimr', 'non_nmimr'];
    }

    /**
     * Check if application type is supported
     *
     * @param string $type Application type
     * @return bool
     */
    public static function supportsType(string $type): bool
    {
        return in_array(strtolower($type), self::getSupportedTypes());
    }

    /**
     * Generate CSRF token
     *
     * @return string
     */
    public static function generateCsrfToken(): string
    {
        $session = new \UGIRB\SubmissionEngine\Services\SessionManager();
        return $session->generateCsrfToken();
    }

    /**
     * Validate CSRF token
     *
     * @param string $token Token to validate
     * @return bool
     */
    public static function validateCsrfToken(string $token): bool
    {
        $session = new \UGIRB\SubmissionEngine\Services\SessionManager();
        return $session->validateCsrfToken($token);
    }

    /**
     * Store original POST and FILES data
     *
     * @param array $postData POST data
     * @param array $filesData FILES data
     */
    private static function storeOriginalData(array $postData, array $filesData): void
    {
        if (!empty($postData)) {
            $_POST = $postData;
        }

        if (!empty($filesData)) {
            $_FILES = $filesData;
        }
    }

    /**
     * Handle legacy submission
     *
     * @param string $applicationType Application type
     * @return array Result
     */
    private static function handleLegacy(string $applicationType): array
    {
        // This will include the legacy handler
        $handlerFile = self::getLegacyHandlerPath($applicationType);

        if (!file_exists($handlerFile)) {
            return [
                'success' => false,
                'message' => 'Legacy handler not found for type: ' . $applicationType
            ];
        }

        // Include and execute legacy handler
        // Note: Legacy handlers define their own functions, so we can't just call them
        ob_start();
        include $handlerFile;
        $output = ob_get_clean();

        // Try to parse the output or return a success message
        $result = json_decode($output, true);

        if ($result === null) {
            return [
                'success' => false,
                'message' => 'Legacy handler did not return valid JSON'
            ];
        }

        return $result;
    }

    /**
     * Get legacy handler path
     *
     * @param string $applicationType Application type
     * @return string
     */
    private static function getLegacyHandlerPath(string $applicationType): string
    {
        return __DIR__ . '/applicant/handlers/' . $applicationType . '_application_handler.php';
    }

    /**
     * Get database connection
     *
     * @return \PDO|null
     */
    private static function getDatabaseConnection(): ?\PDO
    {
        // Include database configuration
        require_once dirname(__DIR__) . '/config.php';
        require_once dirname(__DIR__) . '/includes/config/database.php';

        try {
            $database = new \Database();
            return $database->connect();
        } catch (\Exception $e) {
            error_log('Database connection error: ' . $e->getMessage());
            return null;
        }
    }
}

/**
 * Legacy compatibility function
 *
 * This function provides backward compatibility with existing handlers.
 * It can be used in existing code without changes.
 *
 * @param string $type Application type
 * @return \UGIRB\SubmissionEngine\Factories\ApplicationHandlerFactory
 */
function getApplicationHandlerFactory(string $type): \UGIRB\SubmissionEngine\Factories\ApplicationHandlerFactory
{
    static $factory = null;
    static $cachedType = null;

    $currentType = strtolower($type);

    if ($factory === null || $cachedType !== $currentType) {
        $db = SubmissionEngine::isUsingNewEngine() ? getDbConnection() : null;
        $factory = new \UGIRB\SubmissionEngine\Factories\ApplicationHandlerFactory($db);
        $cachedType = $currentType;
    }

    return $factory;
}

/**
 * Get database connection helper
 *
 * @return \PDO|null
 */
function getDbConnection(): ?\PDO
{
    static $db = null;

    if ($db === null) {
        require_once dirname(__DIR__) . '/config.php';
        require_once dirname(__DIR__) . '/includes/config/database.php';

        try {
            $database = new \Database();
            $db = $database->connect();
        } catch (\Exception $e) {
            error_log('Database connection error: ' . $e->getMessage());
        }
    }

    return $db;
}
