<?php

/**
 * ApplicationHandlerFactory Class
 *
 * Creates appropriate application handlers based on application type.
 * Uses the Factory Pattern to instantiate the correct handler.
 *
 * @package UGIRB\SubmissionEngine\Factories
 */

namespace UGIRB\SubmissionEngine\Factories;

use UGIRB\SubmissionEngine\Interfaces\IApplicationHandler;

class ApplicationHandlerFactory
{
    /** @var array Handler class mappings */
    private const HANDLERS = [
        'student' => \UGIRB\SubmissionEngine\Handlers\StudentHandler::class,
        'nmimr' => \UGIRB\SubmissionEngine\Handlers\NmimrHandler::class,
        'non_nmimr' => \UGIRB\SubmissionEngine\Handlers\NonNmimrHandler::class
    ];

    /** @var \PDO Database connection */
    private \PDO $db;

    /** @var array Configuration options */
    private array $config;

    /** @var array Custom handler registrations */
    private static array $customHandlers = [];

    /**
     * Constructor
     *
     * @param \PDO $db Database connection
     * @param array $config Configuration options
     */
    public function __construct(\PDO $db, array $config = [])
    {
        $this->db = $db;
        $this->config = $config;
    }

    /**
     * Create handler for specified application type
     *
     * @param string $type Application type (student, nmimr, non_nmimr)
     * @return IApplicationHandler Concrete handler instance
     * @throws \InvalidArgumentException If application type is unknown
     */
    public function createHandler(string $type): IApplicationHandler
    {
        $type = strtolower($type);

        // Check for custom handler first
        if (isset(self::$customHandlers[$type])) {
            $handlerClass = self::$customHandlers[$type];
            return $this->instantiateHandler($handlerClass);
        }

        // Check if type is supported
        if (!isset(self::HANDLERS[$type])) {
            throw new \InvalidArgumentException(
                "Unknown application type: {$type}. Valid types: " .
                implode(', ', $this->getSupportedTypes())
            );
        }

        $handlerClass = self::HANDLERS[$type];
        return $this->instantiateHandler($handlerClass);
    }

    /**
     * Create handler with custom configuration
     *
     * @param string $type Application type
     * @param array $options Handler-specific options
     * @return IApplicationHandler
     */
    public function createHandlerWithOptions(string $type, array $options): IApplicationHandler
    {
        $handler = $this->createHandler($type);

        // Allow setting options on handler if supported
        if (method_exists($handler, 'setOptions')) {
            $handler->setOptions($options);
        }

        return $handler;
    }

    /**
     * Get all supported application types
     *
     * @return array List of supported types
     */
    public function getSupportedTypes(): array
    {
        return array_keys(self::HANDLERS);
    }

    /**
     * Check if application type is supported
     *
     * @param string $type Application type
     * @return bool True if supported
     */
    public function supportsType(string $type): bool
    {
        $type = strtolower($type);
        return isset(self::HANDLERS[$type]) || isset(self::$customHandlers[$type]);
    }

    /**
     * Register a custom handler for an application type
     *
     * @param string $type Application type
     * @param string $handlerClass Handler class name
     * @throws \InvalidArgumentException If handler doesn't implement interface
     */
    public static function register(string $type, string $handlerClass): void
    {
        // Validate that handler implements IApplicationHandler
        if (!in_array(IApplicationHandler::class, class_implements($handlerClass))) {
            throw new \InvalidArgumentException(
                "Handler '{$handlerClass}' must implement IApplicationHandler interface"
            );
        }

        self::$customHandlers[strtolower($type)] = $handlerClass;
    }

    /**
     * Unregister a custom handler
     *
     * @param string $type Application type
     */
    public static function unregister(string $type): void
    {
        unset(self::$customHandlers[strtolower($type)]);
    }

    /**
     * Get all registered handlers
     *
     * @return array Type to class mapping
     */
    public static function getRegisteredHandlers(): array
    {
        return self::$customHandlers;
    }

    /**
     * Get handler class for type
     *
     * @param string $type Application type
     * @return string|null Handler class or null if not found
     */
    public function getHandlerClass(string $type): ?string
    {
        $type = strtolower($type);

        if (isset(self::$customHandlers[$type])) {
            return self::$customHandlers[$type];
        }

        return self::HANDLERS[$type] ?? null;
    }

    /**
     * Create handler without database dependency
     *
     * Useful for testing or read-only operations.
     *
     * @param string $type Application type
     * @return IApplicationHandler
     * @throws \InvalidArgumentException If type unknown
     */
    public static function createWithoutDb(string $type): IApplicationHandler
    {
        $type = strtolower($type);

        // Check for custom handler first
        if (isset(self::$customHandlers[$type])) {
            $handlerClass = self::$customHandlers[$type];
            return new $handlerClass(null, []);
        }

        if (!isset(self::HANDLERS[$type])) {
            throw new \InvalidArgumentException("Unknown application type: {$type}");
        }

        $handlerClass = self::HANDLERS[$type];

        return new $handlerClass(null, []);
    }

    /**
     * Check if handler exists for type
     *
     * @param string $type Application type
     * @return bool True if handler exists
     */
    public function hasHandler(string $type): bool
    {
        return $this->supportsType($type);
    }

    /**
     * Instantiate handler class with dependencies
     *
     * @param string $handlerClass Handler class name
     * @return IApplicationHandler Handler instance
     */
    private function instantiateHandler(string $handlerClass): IApplicationHandler
    {
        return new $handlerClass($this->db, $this->config);
    }

    /**
     * Get database connection
     *
     * @return \PDO Database connection
     */
    public function getDb(): \PDO
    {
        return $this->db;
    }

    /**
     * Set database connection
     *
     * @param \PDO $db Database connection
     */
    public function setDb(\PDO $db): void
    {
        $this->db = $db;
    }

    /**
     * Get configuration
     *
     * @return array Configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set configuration
     *
     * @param array $config Configuration
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * Get config value by key
     *
     * @param string $key Config key
     * @param mixed $default Default value
     * @return mixed Config value
     */
    public function getConfigValue(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Set config value
     *
     * @param string $key Config key
     * @param mixed $value Value
     */
    public function setConfigValue(string $key, mixed $value): void
    {
        $this->config[$key] = $value;
    }
}
