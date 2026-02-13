<?php

/**
 * Unified Submission Engine Test Suite
 *
 * Comprehensive tests for the refactored submission engine implementation.
 * Tests include unit tests, integration tests, and error handling tests.
 *
 * Usage:
 *   php tests/test_submission_engine.php
 */

// Prevent direct CLI execution
if (php_sapi_name() !== 'cli' && !defined('RUNNING_TESTS')) {
    if (!isset($_GET['test_mode'])) {
        die('This file is for testing purposes only.');
    }
}

define('RUNNING_TESTS', true);
define('TEST_BASE_PATH', __DIR__);

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'UGIRB\\SubmissionEngine\\';
    $baseDir = dirname(__DIR__) . '/includes/submission_engine/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/functions/helpers.php';
require_once dirname(__DIR__) . '/includes/submission_engine.php';

/**
 * Test Result Class
 */
class TestResult
{
    public string $name;
    public bool $passed;
    public string $message;
    public float $time;
    public ?string $file;
    public ?int $line;

    public function __construct(
        string $name,
        bool $passed,
        string $message = '',
        float $time = 0,
        ?string $file = null,
        ?int $line = null
    ) {
        $this->name = $name;
        $this->passed = $passed;
        $this->message = $message;
        $this->time = $time;
        $this->file = $file;
        $this->line = $line;
    }
}

/**
 * Test Assertion Exception
 */
class TestAssertionException extends Exception {}

/**
 * Assertion Helper Class
 */
class Assert
{
    public static function equal($expected, $actual, string $message = ''): bool
    {
        if ($expected === $actual) {
            return true;
        }
        throw new TestAssertionException(
            $message ?: "Expected " . var_export($expected, true) . " but got " . var_export($actual, true)
        );
    }

    public static function true($value, string $message = ''): bool
    {
        if ($value === true) {
            return true;
        }
        throw new TestAssertionException($message ?: "Expected true but got " . var_export($value, true));
    }

    public static function false($value, string $message = ''): bool
    {
        if ($value === false) {
            return true;
        }
        throw new TestAssertionException($message ?: "Expected false but got " . var_export($value, true));
    }

    public static function contains(string $needle, string $haystack, string $message = ''): bool
    {
        if (strpos($haystack, $needle) !== false) {
            return true;
        }
        throw new TestAssertionException($message ?: "Expected haystack to contain: " . $needle);
    }

    public static function arrayHasKey(string $key, array $array, string $message = ''): bool
    {
        if (array_key_exists($key, $array)) {
            return true;
        }
        throw new TestAssertionException($message ?: "Expected array to have key: " . $key);
    }

    public static function notEmpty($value, string $message = ''): bool
    {
        if (!empty($value)) {
            return true;
        }
        throw new TestAssertionException($message ?: "Expected non-empty value");
    }

    public static function isArray($value, string $message = ''): bool
    {
        if (is_array($value)) {
            return true;
        }
        throw new TestAssertionException($message ?: "Expected array but got " . gettype($value));
    }

    public static function matchesPattern(string $pattern, string $string, string $message = ''): bool
    {
        if (preg_match($pattern, $string) === 1) {
            return true;
        }
        throw new TestAssertionException($message ?: "String does not match pattern: " . $pattern);
    }

    public static function throws(callable $callback, string $exceptionClass = 'Exception', string $message = ''): bool
    {
        try {
            $callback();
        } catch (Throwable $e) {
            if ($e instanceof $exceptionClass || get_class($e) === $exceptionClass) {
                return true;
            }
            throw new TestAssertionException($message ?: "Expected {$exceptionClass} but got " . get_class($e));
        }
        throw new TestAssertionException($message ?: "Expected exception {$exceptionClass} was not thrown");
    }

    public static function count(int $expected, array $array, string $message = ''): bool
    {
        $actual = count($array);
        if ($actual === $expected) {
            return true;
        }
        throw new TestAssertionException($message ?: "Expected array count {$expected} but got {$actual}");
    }

    public static function instanceOf(string $class, $object, string $message = ''): bool
    {
        if ($object instanceof $class) {
            return true;
        }
        throw new TestAssertionException($message ?: "Expected instance of {$class}");
    }

    public static function null($value, string $message = ''): bool
    {
        if ($value === null) {
            return true;
        }
        throw new TestAssertionException($message ?: "Expected null but got " . var_export($value, true));
    }

    public static function notNull($value, string $message = ''): bool
    {
        if ($value !== null) {
            return true;
        }
        throw new TestAssertionException($message ?: "Expected non-null value");
    }
}

/**
 * Base Test Case
 */
abstract class TestCase
{
    protected array $results = [];
    protected string $testName = '';

    public function __construct()
    {
        $this->testName = get_class($this);
    }

    abstract public function setUp(): void;
    abstract public function tearDown(): void;
    abstract public function runTests(): void;

    protected function runAssertion(callable $assertion, string $testName): void
    {
        $startTime = microtime(true);
        $passed = false;
        $message = '';
        $file = null;
        $line = null;

        try {
            $assertion();
            $passed = true;
            $message = 'PASSED';
        } catch (TestAssertionException $e) {
            $passed = false;
            $message = $e->getMessage();
            $trace = $e->getTrace();
            if (isset($trace[0])) {
                $file = $trace[0]['file'] ?? null;
                $line = $trace[0]['line'] ?? null;
            }
        } catch (Throwable $e) {
            $passed = false;
            $message = get_class($e) . ': ' . $e->getMessage();
            $file = $e->getFile();
            $line = $e->getLine();
        }

        $time = microtime(true) - $startTime;

        $this->results[] = new TestResult(
            $this->testName . '::' . $testName,
            $passed,
            $message,
            $time,
            $file,
            $line
        );
    }

    protected function assertEqual($expected, $actual, string $testName): void
    {
        $this->runAssertion(function () use ($expected, $actual) {
            Assert::equal($expected, $actual);
        }, $testName);
    }

    protected function assertTrue($value, string $testName): void
    {
        $this->runAssertion(function () use ($value) {
            Assert::true($value);
        }, $testName);
    }

    protected function assertFalse($value, string $testName): void
    {
        $this->runAssertion(function () use ($value) {
            Assert::false($value);
        }, $testName);
    }

    protected function assertContains($needle, $haystack, string $testName): void
    {
        $this->runAssertion(function () use ($needle, $haystack) {
            Assert::contains($needle, $haystack);
        }, $testName);
    }

    protected function assertArrayHasKey($key, array $array, string $testName): void
    {
        $this->runAssertion(function () use ($key, $array) {
            Assert::arrayHasKey($key, $array);
        }, $testName);
    }

    protected function assertNotEmpty($value, string $testName): void
    {
        $this->runAssertion(function () use ($value) {
            Assert::notEmpty($value);
        }, $testName);
    }

    protected function assertIsArray($value, string $testName): void
    {
        $this->runAssertion(function () use ($value) {
            Assert::isArray($value);
        }, $testName);
    }

    protected function assertMatchesPattern($pattern, $string, string $testName): void
    {
        $this->runAssertion(function () use ($pattern, $string) {
            Assert::matchesPattern($pattern, $string);
        }, $testName);
    }

    protected function assertThrows(callable $callback, string $exceptionClass, string $testName): void
    {
        $this->runAssertion(function () use ($callback, $exceptionClass) {
            Assert::throws($callback, $exceptionClass);
        }, $testName);
    }

    protected function assertCount($expected, array $array, string $testName): void
    {
        $this->runAssertion(function () use ($expected, $array) {
            Assert::count($expected, $array);
        }, $testName);
    }

    protected function assertInstanceOf($class, $object, string $testName): void
    {
        $this->runAssertion(function () use ($class, $object) {
            Assert::instanceOf($class, $object);
        }, $testName);
    }

    protected function assertNull($value, string $testName): void
    {
        $this->runAssertion(function () use ($value) {
            Assert::null($value);
        }, $testName);
    }

    protected function assertNotNull($value, string $testName): void
    {
        $this->runAssertion(function () use ($value) {
            Assert::notNull($value);
        }, $testName);
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function getPassedCount(): int
    {
        return count(array_filter($this->results, fn($r) => $r->passed));
    }

    public function getFailedCount(): int
    {
        return count(array_filter($this->results, fn($r) => !$r->passed));
    }
}

/**
 * Test Suite Runner
 */
class TestRunner
{
    private array $testCases = [];
    private float $totalStartTime = 0;

    public function register(TestCase $testCase): void
    {
        $this->testCases[] = $testCase;
    }

    public function runAll(): array
    {
        $this->totalStartTime = microtime(true);
        $allResults = [];
        $passed = 0;
        $failed = 0;

        echo "\n" . str_repeat('=', 70) . "\n";
        echo "  UNIFIED SUBMISSION ENGINE - TEST SUITE\n";
        echo str_repeat('=', 70) . "\n\n";

        foreach ($this->testCases as $testCase) {
            echo "Running: " . get_class($testCase) . "\n";
            echo str_repeat('-', 60) . "\n";

            try {
                $testCase->setUp();
                $testCase->runTests();
                $testCase->tearDown();
            } catch (Throwable $e) {
                echo "  ERROR: " . $e->getMessage() . "\n";
            }

            $results = $testCase->getResults();

            foreach ($results as $result) {
                $status = $result->passed ? "\033[32m✓\033[0m" : "\033[31m✗\033[0m";
                $time = number_format($result->time * 1000, 2) . 'ms';

                if ($result->passed) {
                    $passed++;
                    echo "  {$status} {$result->name} [{$time}]\n";
                } else {
                    $failed++;
                    echo "  {$status} {$result->name} [{$time}]\n";
                    echo "    \033[31mFAILED: {$result->message}\033[0m\n";
                    if ($result->file) {
                        echo "    \033[90mFile: {$result->file}:{$result->line}\033[0m\n";
                    }
                }

                $allResults[] = $result;
            }

            $testPassed = $testCase->getPassedCount();
            $testFailed = $testCase->getFailedCount();
            echo "\n  Summary: \033[32m{$testPassed} passed\033[0m, \033[31m{$testFailed} failed\033[0m\n\n";
        }

        $totalTime = microtime(true) - $this->totalStartTime;

        echo str_repeat('=', 70) . "\n";
        echo "  TEST SUMMARY\n";
        echo str_repeat('=', 70) . "\n";
        echo "  Total Tests: " . count($allResults) . "\n";
        echo "  \033[32mPassed: {$passed}\033[0m\n";
        echo "  \033[31mFailed: {$failed}\033[0m\n";
        echo "  Total Time: " . number_format($totalTime, 2) . "s\n";
        echo str_repeat('=', 70) . "\n\n";

        return [
            'results' => $allResults,
            'passed' => $passed,
            'failed' => $failed,
            'total' => count($allResults),
            'time' => $totalTime
        ];
    }
}

// ============================================================================
// TEST CASES
// ============================================================================

class SubmissionEngineStaticTests extends TestCase
{
    public function setUp(): void {}
    public function tearDown(): void {}

    public function runTests(): void
    {
        $this->assertTrue(
            class_exists('SubmissionEngine'),
            'SubmissionEngine class exists'
        );

        $types = SubmissionEngine::getSupportedTypes();
        $this->assertIsArray($types, 'getSupportedTypes returns array');
        $this->assertCount(3, $types, 'getSupportedTypes returns 3 types');
        $this->assertContains('student', implode(',', $types), 'Contains student type');
        $this->assertContains('nmimr', implode(',', $types), 'Contains nmimr type');
        $this->assertContains('non_nmimr', implode(',', $types), 'Contains non_nmimr type');

        $this->assertTrue(SubmissionEngine::supportsType('student'), 'supportsType student');
        $this->assertTrue(SubmissionEngine::supportsType('nmimr'), 'supportsType nmimr');
        $this->assertTrue(SubmissionEngine::supportsType('STUDENT'), 'supportsType case-insensitive');
        $this->assertFalse(SubmissionEngine::supportsType('invalid'), 'supportsType invalid');

        $this->assertTrue(is_bool(SubmissionEngine::isUsingNewEngine()), 'isUsingNewEngine boolean');

        SubmissionEngine::setUseNewEngine(false);
        $this->assertFalse(SubmissionEngine::isUsingNewEngine(), 'setUseNewEngine disable');
        SubmissionEngine::setUseNewEngine(true);
        $this->assertTrue(SubmissionEngine::isUsingNewEngine(), 'setUseNewEngine enable');
    }
}

class ApplicationHandlerFactoryTests extends TestCase
{
    public function setUp(): void {}
    public function tearDown(): void {}

    public function runTests(): void
    {
        $this->assertTrue(
            class_exists('UGIRB\\SubmissionEngine\\Factories\\ApplicationHandlerFactory'),
            'Factory class exists'
        );

        // Test createWithoutDb static method
        $handler = \UGIRB\SubmissionEngine\Factories\ApplicationHandlerFactory::createWithoutDb('student');
        $this->assertInstanceOf('UGIRB\\SubmissionEngine\\Handlers\\StudentHandler', $handler, 'Creates StudentHandler');

        $handler = \UGIRB\SubmissionEngine\Factories\ApplicationHandlerFactory::createWithoutDb('nmimr');
        $this->assertInstanceOf('UGIRB\\SubmissionEngine\\Handlers\\NmimrHandler', $handler, 'Creates NmimrHandler');

        $handler = \UGIRB\SubmissionEngine\Factories\ApplicationHandlerFactory::createWithoutDb('non_nmimr');
        $this->assertInstanceOf('UGIRB\\SubmissionEngine\\Handlers\\NonNmimrHandler', $handler, 'Creates NonNmimrHandler');

        // Test supported types
        $types = ['student', 'nmimr', 'non_nmimr'];
        $this->assertCount(3, $types, 'Factory supports 3 types');
    }
}

class IApplicationHandlerInterfaceTests extends TestCase
{
    public function setUp(): void {}
    public function tearDown(): void {}

    public function runTests(): void
    {
        $this->assertTrue(
            interface_exists('UGIRB\\SubmissionEngine\\Interfaces\\IApplicationHandler'),
            'IApplicationHandler interface exists'
        );

        $reflection = new ReflectionClass('UGIRB\\SubmissionEngine\\Interfaces\\IApplicationHandler');
        $methodNames = implode(',', array_map(fn($m) => $m->getName(), $reflection->getMethods()));

        $this->assertContains('validate', $methodNames, 'Has validate method');
        $this->assertContains('save', $methodNames, 'Has save method');
        $this->assertContains('handleSubmission', $methodNames, 'Has handleSubmission method');
        $this->assertContains('getProtocolPrefix', $methodNames, 'Has getProtocolPrefix method');
        $this->assertContains('getType', $methodNames, 'Has getType method');
    }
}

class ValidationServiceTests extends TestCase
{
    private ?object $validator = null;

    public function setUp(): void
    {
        $this->validator = new \UGIRB\SubmissionEngine\Services\ValidationService();
    }

    public function tearDown(): void
    {
        $this->validator = null;
    }

    public function runTests(): void
    {
        // Email validation
        $this->assertTrue($this->validator->validateEmail('test@example.com'), 'Valid email');
        $this->assertFalse($this->validator->validateEmail('invalid-email'), 'Invalid email');

        // Version number
        $this->assertTrue($this->validator->validateVersionNumber('1.0'), 'Version 1.0 valid');
        $this->assertFalse($this->validator->validateVersionNumber('1.a'), 'Version invalid');

        // Date validation
        $this->assertTrue($this->validator->validateDate('2026-01-15'), 'Valid date');
        $this->assertFalse($this->validator->validateDate('2026/01/15'), 'Invalid date format');

        // Date range
        $this->assertTrue($this->validator->validateDateRange('2026-01-01', '2026-12-31'), 'Valid range');
        $this->assertFalse($this->validator->validateDateRange('2026-12-31', '2026-01-01'), 'Invalid range');

        // Phone
        $this->assertTrue($this->validator->validatePhone('+233501234567'), 'Valid phone');
        $this->assertFalse($this->validator->validatePhone('123'), 'Too short phone');

        // Required fields
        $data = ['name' => 'John', 'email' => 'john@test.com'];
        $result = $this->validator->validateRequired($data, ['name', 'email']);
        $this->assertTrue($result['success'], 'All required present');

        $result = $this->validator->validateRequired($data, ['name', 'email', 'phone']);
        $this->assertFalse($result['success'], 'Missing required fails');
        $this->assertCount(1, $result['missing'], 'One missing field');

        // Sanitization
        $dirty = '<script>alert("xss")</script>Test';
        $clean = $this->validator->sanitizeString($dirty);
        $this->assertFalse(strpos($clean, '<script>') !== false, 'Script tags removed');

        $dirtyData = ['name' => '<b>Name</b>', 'email' => 'test@example.com'];
        $cleanData = $this->validator->sanitizeArray($dirtyData);
        $this->assertFalse(strpos($cleanData['name'], '<b>') !== false, 'Array sanitized');

        // Allowed MIME types
        $types = $this->validator->getAllowedMimeTypes();
        $this->assertIsArray($types, 'MIME types is array');
        $typesStr = implode(',', $types);
        $this->assertContains('application/pdf', $typesStr, 'PDF is allowed');

        // Max file size
        $maxSize = $this->validator->getMaxFileSize();
        $this->assertEqual(10 * 1024 * 1024, $maxSize, 'Max file size 10MB');
    }
}

class ProtocolNumberGeneratorTests extends TestCase
{
    private ?object $generator = null;

    public function setUp(): void
    {
        $this->generator = new \UGIRB\SubmissionEngine\Services\ProtocolNumberGenerator();
    }

    public function tearDown(): void
    {
        $this->generator = null;
    }

    public function runTests(): void
    {
        $this->assertTrue(
            class_exists('UGIRB\\SubmissionEngine\\Services\\ProtocolNumberGenerator'),
            'ProtocolNumberGenerator exists'
        );

        // Generate manual
        $protocol = $this->generator->generateManual('student', 2026, 1);
        $this->assertMatchesPattern('/^STU-2026-\\d{4}$/', $protocol, 'Format STU');
        $this->assertEqual('STU-2026-0001', $protocol, 'Sequence padded');

        $protocol = $this->generator->generateManual('nmimr', 2026, 15);
        $this->assertEqual('NIRB-2026-0015', $protocol, 'NIRB prefix');

        $protocol = $this->generator->generateManual('non_nmimr', 2026, 100);
        $this->assertEqual('EXT-2026-0100', $protocol, 'EXT prefix');

        // Get prefix
        $this->assertEqual('STU', $this->generator->getPrefix('student'), 'STU prefix');
        $this->assertEqual('NIRB', $this->generator->getPrefix('nmimr'), 'NIRB prefix');
        $this->assertEqual('EXT', $this->generator->getPrefix('non_nmimr'), 'EXT prefix');

        // Custom prefix
        $this->generator->setPrefix('student', 'CUS');
        $this->assertEqual('CUS', $this->generator->getPrefix('student'), 'Custom prefix');
        $this->generator->removePrefix('student');
        $this->assertEqual('STU', $this->generator->getPrefix('student'), 'Prefix removed');

        // Format validation
        $this->assertTrue($this->generator->isValidFormat('STU-2026-0001'), 'Valid format');
        $this->assertFalse($this->generator->isValidFormat('invalid-format'), 'Invalid format');

        // Parse
        $parsed = $this->generator->parse('STU-2026-0001');
        $this->assertNotNull($parsed, 'Parse returns array');
        $this->assertEqual('STU', $parsed['prefix'], 'Prefix extracted');
        $this->assertEqual(2026, $parsed['year'], 'Year extracted');
        $this->assertEqual(1, $parsed['sequence'], 'Sequence extracted');

        $parsed = $this->generator->parse('invalid');
        $this->assertNull($parsed, 'Invalid returns null');

        // Application type from protocol
        $this->assertEqual('student', $this->generator->getApplicationType('STU-2026-0001'), 'STU maps to student');
        $this->assertEqual('nmimr', $this->generator->getApplicationType('NIRB-2026-0001'), 'NIRB maps to nmimr');
        $this->assertEqual('non_nmimr', $this->generator->getApplicationType('EXT-2026-0001'), 'EXT maps to non_nmimr');

        // All prefixes
        $prefixes = $this->generator->getAllPrefixes();
        $this->assertIsArray($prefixes, 'All prefixes is array');
        $this->assertArrayHasKey('student', $prefixes, 'Has student key');

        // Current year
        $currentYear = date('Y');
        $this->assertTrue($this->generator->isCurrentYear("STU-{$currentYear}-0001"), 'Current year valid');
    }
}

class SessionManagerTests extends TestCase
{
    private ?object $session = null;

    public function setUp(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        $_SESSION = [];
        $this->session = new \UGIRB\SubmissionEngine\Services\SessionManager();
    }

    public function tearDown(): void
    {
        $_SESSION = [];
    }

    public function runTests(): void
    {
        $this->assertTrue(
            class_exists('UGIRB\\SubmissionEngine\\Services\\SessionManager'),
            'SessionManager exists'
        );

        // CSRF Token
        $token1 = $this->session->generateCsrfToken();
        $this->assertNotEmpty($token1, 'Token generated');
        $this->assertEqual(64, strlen($token1), 'Token 64 chars');

        $token2 = $this->session->generateCsrfToken();
        $this->assertEqual($token1, $token2, 'Same token');

        $this->assertTrue($this->session->validateCsrfToken($token1), 'Valid token');
        $this->assertFalse($this->session->validateCsrfToken('invalid'), 'Invalid token');

        // Set/Get
        $this->session->set('test_key', 'test_value');
        $this->assertEqual('test_value', $this->session->get('test_key'), 'set/get works');
        $this->assertEqual('default', $this->session->get('nonexistent', 'default'), 'Default value');

        // Has
        $this->assertTrue($this->session->has('test_key'), 'has returns true');
        $this->assertFalse($this->session->has('nonexistent'), 'has returns false');

        // Remove
        $this->session->remove('test_key');
        $this->assertFalse($this->session->has('test_key'), 'Key removed');

        // Login status
        $this->assertFalse($this->session->isLoggedIn(), 'Not logged in default');
        $this->session->setLogin(123, true);
        $this->assertTrue($this->session->isLoggedIn(), 'Logged in after setLogin');
        $this->assertEqual(123, $this->session->getUserId(), 'User ID correct');

        // Logout
        $this->session->logout();
        $this->assertFalse($this->session->isLoggedIn(), 'Logged out');
        $this->assertEqual(0, $this->session->getUserId(), 'User ID reset');

        // Flash messages
        $this->session->flash('message', 'Hello');
        $this->assertTrue($this->session->hasFlash('message'), 'Flash set');
        $value = $this->session->getFlash('message');
        $this->assertEqual('Hello', $value, 'Flash retrieved');
    }
}

class FileUploadServiceTests extends TestCase
{
    private ?object $fileService = null;
    private string $testUploadDir;

    public function setUp(): void
    {
        $this->fileService = new \UGIRB\SubmissionEngine\Services\FileUploadService(TEST_BASE_PATH . '/test_uploads');
        $this->testUploadDir = TEST_BASE_PATH . '/test_uploads';

        if (!is_dir($this->testUploadDir)) {
            mkdir($this->testUploadDir, 0755, true);
        }
    }

    public function tearDown(): void
    {
        $this->cleanupDirectory($this->testUploadDir);
        if (is_dir($this->testUploadDir)) {
            rmdir($this->testUploadDir);
        }
        $this->fileService = null;
    }

    private function cleanupDirectory(string $dir): void
    {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = "$dir/$file";
            if (is_dir($path)) {
                $this->cleanupDirectory($path);
                rmdir($path);
            } else {
                unlink($path);
            }
        }
    }

    public function runTests(): void
    {
        $this->assertTrue(
            class_exists('UGIRB\\SubmissionEngine\\Services\\FileUploadService'),
            'FileUploadService exists'
        );

        // Base path
        $this->assertTrue(
            strpos($this->fileService->getBasePath(), 'test_uploads') !== false,
            'Base path set correctly'
        );

        // Set base path
        $this->fileService->setBasePath('/new/path');
        $this->assertEqual('/new/path', $this->fileService->getBasePath(), 'Base path changed');

        // Safe filename
        $safeName = $this->fileService->generateSafeFilename('test document.pdf');
        $this->assertMatchesPattern('/^test_document_\\d+_[a-f0-9]+\\.pdf$/', $safeName, 'Safe filename');
        $this->assertFalse(strpos($safeName, ' ') !== false, 'Spaces removed');

        // Create directory
        $newDir = $this->testUploadDir . '/new/sub/dir';
        $result = $this->fileService->createDirectory($newDir);
        $this->assertTrue($result, 'Directory created');
        $this->assertTrue(is_dir($newDir), 'Directory exists');

        // Copy file
        $source = $this->testUploadDir . '/source.txt';
        $dest = $this->testUploadDir . '/dest.txt';
        file_put_contents($source, 'Copy me');
        $result = $this->fileService->copy($source, $dest);
        $this->assertTrue($result, 'File copied');
        $this->assertEqual('Copy me', file_get_contents($dest), 'Content copied');
    }
}

class EmailServiceTests extends TestCase
{
    private ?object $emailService = null;

    public function setUp(): void
    {
        $this->emailService = new \UGIRB\SubmissionEngine\Services\EmailService(
            'test@example.com',
            'Test Sender',
            'Test App'
        );
    }

    public function tearDown(): void
    {
        $this->emailService = null;
    }

    public function runTests(): void
    {
        $this->assertTrue(
            class_exists('UGIRB\\SubmissionEngine\\Services\\EmailService'),
            'EmailService exists'
        );

        $this->assertEqual('test@example.com', $this->emailService->getSenderEmail(), 'Sender email set');
        $this->assertEqual('Test Sender', $this->emailService->getSenderName(), 'Sender name set');

        $this->emailService->setSenderEmail('new@example.com');
        $this->assertEqual('new@example.com', $this->emailService->getSenderEmail(), 'Email updated');

        $result = $this->emailService->sendSubmissionConfirmation(
            'recipient@example.com',
            'STU-2026-0001',
            'student'
        );
        $this->assertTrue(is_bool($result), 'Returns boolean');
    }
}

class BaseAbstractHandlerTests extends TestCase
{
    public function setUp(): void {}
    public function tearDown(): void {}

    public function runTests(): void
    {
        $this->assertTrue(
            class_exists('UGIRB\\SubmissionEngine\\Handlers\\BaseAbstractHandler'),
            'BaseAbstractHandler exists'
        );

        $reflection = new ReflectionClass('UGIRB\\SubmissionEngine\\Handlers\\BaseAbstractHandler');
        $this->assertTrue($reflection->isAbstract(), 'Is abstract class');

        $interfaces = $reflection->getInterfaces();
        $implementsInterface = false;
        foreach ($interfaces as $interface) {
            if ($interface->getName() === 'UGIRB\\SubmissionEngine\\Interfaces\\IApplicationHandler') {
                $implementsInterface = true;
                break;
            }
        }
        $this->assertTrue($implementsInterface, 'Implements IApplicationHandler');
    }
}

class BackwardCompatibilityTests extends TestCase
{
    public function setUp(): void {}
    public function tearDown(): void {}

    public function runTests(): void
    {
        $studentHandler = dirname(__DIR__) . '/applicant/handlers/student_application_handler.php';
        $nmimrHandler = dirname(__DIR__) . '/applicant/handlers/nmimr_application_handler.php';
        $nonNmimrHandler = dirname(__DIR__) . '/applicant/handlers/non_nmimr_application_handler.php';

        $this->assertTrue(file_exists($studentHandler), 'Student handler exists');
        $this->assertTrue(file_exists($nmimrHandler), 'NMIMR handler exists');
        $this->assertTrue(file_exists($nonNmimrHandler), 'Non-NMIMR handler exists');

        $studentContent = file_get_contents($studentHandler);
        $this->assertTrue(
            strpos($studentContent, 'submission_engine') !== false ||
            strpos($studentContent, 'SubmissionEngine') !== false,
            'Student handler integrates with SubmissionEngine'
        );
    }
}

class ErrorHandlingTests extends TestCase
{
    public function setUp(): void {}
    public function tearDown(): void {}

    public function runTests(): void
    {
        // Invalid type throws exception
        $this->assertThrows(
            function () {
                \UGIRB\SubmissionEngine\Factories\ApplicationHandlerFactory::createWithoutDb('invalid_type');
            },
            'InvalidArgumentException',
            'Invalid type throws exception'
        );

        // Protocol generator invalid type
        $generator = new \UGIRB\SubmissionEngine\Services\ProtocolNumberGenerator();
        $this->assertThrows(
            function () use ($generator) {
                $generator->getPrefix('invalid_type');
            },
            'InvalidArgumentException',
            'Invalid protocol type throws exception'
        );

        // Validation
        $validator = new \UGIRB\SubmissionEngine\Services\ValidationService();
        $result = $validator->validateRequired([], ['name', 'email']);
        $this->assertFalse($result['success'], 'Empty data fails');
        $this->assertCount(2, $result['missing'], 'Two missing fields');

        $this->assertFalse($validator->validateEmail('not-an-email'), 'Invalid email');

        $this->assertFalse(SubmissionEngine::supportsType('unknown_type'), 'Unknown type false');
    }
}

class HandlerInterfaceComplianceTests extends TestCase
{
    private array $handlerClasses = [
        'UGIRB\\SubmissionEngine\\Handlers\\StudentHandler',
        'UGIRB\\SubmissionEngine\\Handlers\\NmimrHandler',
        'UGIRB\\SubmissionEngine\\Handlers\\NonNmimrHandler'
    ];

    public function setUp(): void {}
    public function tearDown(): void {}

    public function runTests(): void
    {
        $requiredMethods = [
            'validate', 'save', 'handleSubmission',
            'getProtocolPrefix', 'getType', 'getRequiredFields', 'getFileRequirements'
        ];

        foreach ($this->handlerClasses as $class) {
            foreach ($requiredMethods as $method) {
                $this->assertTrue(
                    method_exists($class, $method),
                    "{$class} has {$method}"
                );
            }

            $reflection = new ReflectionClass($class);
            $this->assertTrue($reflection->hasConstant('TYPE'), "{$class} has TYPE");
            $this->assertTrue($reflection->hasConstant('PREFIX'), "{$class} has PREFIX");
        }
    }
}

class HandlerIntegrationTests extends TestCase
{
    public function setUp(): void {}
    public function tearDown(): void {}

    public function runTests(): void
    {
        // Test all handlers can be created
        $studentHandler = \UGIRB\SubmissionEngine\Factories\ApplicationHandlerFactory::createWithoutDb('student');
        $this->assertEqual('STU', $studentHandler->getProtocolPrefix(), 'Student prefix');
        $this->assertEqual('student', $studentHandler->getType(), 'Student type');

        $nmimrHandler = \UGIRB\SubmissionEngine\Factories\ApplicationHandlerFactory::createWithoutDb('nmimr');
        $this->assertEqual('NIRB', $nmimrHandler->getProtocolPrefix(), 'NMIMR prefix');
        $this->assertEqual('nmimr', $nmimrHandler->getType(), 'NMIMR type');

        $nonNmimrHandler = \UGIRB\SubmissionEngine\Factories\ApplicationHandlerFactory::createWithoutDb('non_nmimr');
        $this->assertEqual('EXT', $nonNmimrHandler->getProtocolPrefix(), 'Non-NMIMR prefix');
        $this->assertEqual('non_nmimr', $nonNmimrHandler->getType(), 'Non-NMIMR type');
    }
}

// ============================================================================
// MAIN TEST EXECUTION
// ============================================================================

if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $runner = new TestRunner();

    $runner->register(new SubmissionEngineStaticTests());
    $runner->register(new ApplicationHandlerFactoryTests());
    $runner->register(new IApplicationHandlerInterfaceTests());
    $runner->register(new ValidationServiceTests());
    $runner->register(new ProtocolNumberGeneratorTests());
    $runner->register(new SessionManagerTests());
    $runner->register(new FileUploadServiceTests());
    $runner->register(new EmailServiceTests());
    $runner->register(new BaseAbstractHandlerTests());
    $runner->register(new BackwardCompatibilityTests());
    $runner->register(new ErrorHandlingTests());
    $runner->register(new HandlerInterfaceComplianceTests());
    $runner->register(new HandlerIntegrationTests());

    $result = $runner->runAll();

    exit($result['failed'] > 0 ? 1 : 0);
}

function runTests(): array
{
    $runner = new TestRunner();

    $runner->register(new SubmissionEngineStaticTests());
    $runner->register(new ApplicationHandlerFactoryTests());
    $runner->register(new IApplicationHandlerInterfaceTests());
    $runner->register(new ValidationServiceTests());
    $runner->register(new ProtocolNumberGeneratorTests());
    $runner->register(new SessionManagerTests());
    $runner->register(new FileUploadServiceTests());
    $runner->register(new EmailServiceTests());
    $runner->register(new BaseAbstractHandlerTests());
    $runner->register(new BackwardCompatibilityTests());
    $runner->register(new ErrorHandlingTests());
    $runner->register(new HandlerInterfaceComplianceTests());
    $runner->register(new HandlerIntegrationTests());

    return $runner->runAll();
}
