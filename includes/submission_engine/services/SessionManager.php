<?php

/**
 * SessionManager Class
 *
 * Manages user sessions and CSRF token protection for the submission engine.
 * Provides secure session handling with CSRF validation.
 *
 * @package UGIRB\SubmissionEngine\Services
 */

namespace UGIRB\SubmissionEngine\Services;

class SessionManager
{
    /** @var string Session name for UG IRB application */
    private const SESSION_NAME = 'ug_irb_session';

    /** @var string CSRF token session key */
    private const CSRF_TOKEN_KEY = 'csrf_token';

    /** @var string Login status key */
    private const LOGIN_KEY = 'logged_in';

    /** @var string User ID key */
    private const USER_ID_KEY = 'user_id';

    /** @var bool Whether session has been started */
    private static bool $started = false;

    /**
     * Constructor
     *
     * Starts the session if not already started.
     */
    public function __construct()
    {
        $this->startSession();
    }

    /**
     * Start PHP session if not already started
     */
    private function startSession(): void
    {
        if (self::$started === false && session_status() === PHP_SESSION_NONE) {
            session_name(self::SESSION_NAME);
            session_start();
            self::$started = true;
        } elseif (session_status() === PHP_SESSION_NONE) {
            session_name(self::SESSION_NAME);
            session_start();
            self::$started = true;
        }
    }

    /**
     * Check if user is logged in
     *
     * @return bool True if user is authenticated
     */
    public function isLoggedIn(): bool
    {
        return isset($_SESSION[self::LOGIN_KEY]) &&
               $_SESSION[self::LOGIN_KEY] === true;
    }

    /**
     * Get current user ID
     *
     * @return int User ID or 0 if not logged in
     */
    public function getUserId(): int
    {
        return (int) ($_SESSION[self::USER_ID_KEY] ?? 0);
    }

    /**
     * Set login status
     *
     * @param int $userId User ID
     * @param bool $loggedIn Login status
     */
    public function setLogin(int $userId, bool $loggedIn = true): void
    {
        $_SESSION[self::LOGIN_KEY] = $loggedIn;
        $_SESSION[self::USER_ID_KEY] = $userId;
    }

    /**
     * Log out current user
     */
    public function logout(): void
    {
        $_SESSION[self::LOGIN_KEY] = false;
        $_SESSION[self::USER_ID_KEY] = 0;
    }

    /**
     * Generate CSRF token
     *
     * Creates a cryptographically secure CSRF token and stores it in session.
     *
     * @return string CSRF token
     */
    public function generateCsrfToken(): string
    {
        if (!isset($_SESSION[self::CSRF_TOKEN_KEY])) {
            $_SESSION[self::CSRF_TOKEN_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::CSRF_TOKEN_KEY];
    }

    /**
     * Validate CSRF token
     *
     * Uses timing-safe comparison to prevent timing attacks.
     *
     * @param string $token Token to validate
     * @return bool True if token is valid
     */
    public function validateCsrfToken(string $token): bool
    {
        if (!isset($_SESSION[self::CSRF_TOKEN_KEY])) {
            return false;
        }

        return hash_equals($_SESSION[self::CSRF_TOKEN_KEY], $token);
    }

    /**
     * Set session value
     *
     * @param string $key Session key
     * @param mixed $value Value to store
     */
    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get session value
     *
     * @param string $key Session key
     * @param mixed $default Default value if key not found
     * @return mixed Session value or default
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if session key exists
     *
     * @param string $key Session key
     * @return bool True if key exists
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session key
     *
     * @param string $key Session key
     */
    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Regenerate session ID
     *
     * Call this after login to prevent session fixation attacks.
     *
     * @param bool $deleteOldSession Whether to delete old session data
     */
    public function regenerateId(bool $deleteOldSession = false): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id($deleteOldSession);
        }
    }

    /**
     * Destroy session
     *
     * Clears all session data.
     */
    public function destroy(): void
    {
        $_SESSION = [];
        session_destroy();
        self::$started = false;
    }

    /**
     * Get session ID
     *
     * @return string Session ID
     */
    public function getSessionId(): string
    {
        return session_id();
    }

    /**
     * Get all session data
     *
     * @return array All session data
     */
    public function getAll(): array
    {
        return $_SESSION;
    }

    /**
     * Flash message for next request
     *
     * Stores a message that will be available for one request only.
     *
     * @param string $key Message key
     * @param mixed $value Message value
     */
    public function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    /**
     * Get flash message
     *
     * Retrieves and clears a flash message.
     *
     * @param string $key Message key
     * @param mixed $default Default value
     * @return mixed Flash message or default
     */
    public function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    /**
     * Check if flash message exists
     *
     * @param string $key Message key
     * @return bool True if flash message exists
     */
    public function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }

    /**
     * Get session status
     *
     * @return bool True if session is active
     */
    public static function isActive(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }
}
