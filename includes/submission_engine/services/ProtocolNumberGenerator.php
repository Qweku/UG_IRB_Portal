<?php

/**
 * ProtocolNumberGenerator Class
 *
 * Generates unique protocol numbers for IRB applications.
 * Supports multiple application types with configurable prefixes.
 *
 * Protocol Format: PREFIX-YEAR-SEQUENCE
 * Examples: STU-2026-0001, NIRB-2026-0001, EXT-2026-0001
 *
 * @package UGIRB\SubmissionEngine\Services
 */

namespace UGIRB\SubmissionEngine\Services;

class ProtocolNumberGenerator
{
    /** @var array Application type to prefix mapping */
    private const PREFIX_MAP = [
        'student' => 'STU',
        'nmimr' => 'NIRB',
        'non_nmimr' => 'EXT'
    ];

    /** @var int Sequence number padding length */
    private const SEQUENCE_PAD_LENGTH = 4;

    /** @var string Year format */
    private const YEAR_FORMAT = 'Y';

    /** @var int Sequence starting number */
    private const SEQUENCE_START = 1;

    /** @var \PDO Database connection */
    private ?\PDO $db = null;

    /** @var array Custom prefixes set at runtime */
    private array $customPrefixes = [];

    /**
     * Generate a unique protocol number
     *
     * @param \PDO $db Database connection
     * @param string $applicationType Application type (student, nmimr, non_nmimr)
     * @return string Generated protocol number
     * @throws \InvalidArgumentException If application type is unknown
     */
    public function generate(\PDO $db, string $applicationType): string
    {
        $this->db = $db;
        $type = strtolower($applicationType);

        $prefix = $this->getPrefix($type);
        $year = date(self::YEAR_FORMAT);
        $sequence = $this->getNextSequence($type, $year);

        return $prefix . '-' . $year . '-' . $this->formatSequence($sequence);
    }

    /**
     * Generate protocol number without database query
     *
     * Use this when you already know the next sequence number.
     *
     * @param string $applicationType Application type
     * @param int|null $year Year (defaults to current year)
     * @param int|null $sequence Sequence number (defaults to 1)
     * @return string Protocol number
     */
    public function generateManual(string $applicationType, ?int $year = null, ?int $sequence = null): string
    {
        $type = strtolower($applicationType);
        $prefix = $this->getPrefix($type);
        $year = $year ?? (int) date(self::YEAR_FORMAT);
        $sequence = $sequence ?? self::SEQUENCE_START;

        return $prefix . '-' . $year . '-' . $this->formatSequence($sequence);
    }

    /**
     * Get next available sequence number
     *
     * @param string $applicationType Application type
     * @param string $year Year
     * @return int Next sequence number
     */
    public function getNextSequence(string $applicationType, string $year): int
    {
        if ($this->db === null) {
            throw new \RuntimeException('Database connection not set. Call setDb() first.');
        }

        $type = strtolower($applicationType);
        $prefix = $this->getPrefix($type);

        $stmt = $this->db->prepare("
            SELECT MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(protocol_number, '-', -1), '-', 1) AS UNSIGNED)) as max_seq
            FROM applications
            WHERE application_type = :type
            AND protocol_number LIKE :prefix
        ");

        $stmt->execute([
            ':type' => $type,
            ':prefix' => $prefix . '-' . $year . '%'
        ]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $maxSequence = (int) ($result['max_seq'] ?? 0);

        return $maxSequence + 1;
    }

    /**
     * Get protocol number prefix for application type
     *
     * @param string $applicationType Application type
     * @return string Protocol prefix
     * @throws \InvalidArgumentException If application type is unknown
     */
    public function getPrefix(string $applicationType): string
    {
        $type = strtolower($applicationType);

        // Check for custom prefix first
        if (isset($this->customPrefixes[$type])) {
            return $this->customPrefixes[$type];
        }

        // Check default prefix map
        if (!isset(self::PREFIX_MAP[$type])) {
            throw new \InvalidArgumentException(
                "Unknown application type: {$applicationType}. Valid types: " .
                implode(', ', array_keys(self::PREFIX_MAP))
            );
        }

        return self::PREFIX_MAP[$type];
    }

    /**
     * Set custom prefix for application type
     *
     * @param string $applicationType Application type
     * @param string $prefix Custom prefix
     */
    public function setPrefix(string $applicationType, string $prefix): void
    {
        $this->customPrefixes[strtolower($applicationType)] = strtoupper($prefix);
    }

    /**
     * Remove custom prefix for application type
     *
     * @param string $applicationType Application type
     */
    public function removePrefix(string $applicationType): void
    {
        unset($this->customPrefixes[strtolower($applicationType)]);
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
     * Get all registered prefixes
     *
     * @return array Application type to prefix mapping
     */
    public function getAllPrefixes(): array
    {
        return array_merge(self::PREFIX_MAP, $this->customPrefixes);
    }

    /**
     * Check if protocol number format is valid
     *
     * @param string $protocolNumber Protocol number to validate
     * @return bool True if valid format
     */
    public function isValidFormat(string $protocolNumber): bool
    {
        return preg_match('/^[A-Z]+-[0-9]{4}-[0-9]{4,}$/', $protocolNumber) === 1;
    }

    /**
     * Parse protocol number into components
     *
     * @param string $protocolNumber Protocol number
     * @return array|null ['prefix' => string, 'year' => int, 'sequence' => int] or null if invalid
     */
    public function parse(string $protocolNumber): ?array
    {
        if (!$this->isValidFormat($protocolNumber)) {
            return null;
        }

        $parts = explode('-', $protocolNumber);
        if (count($parts) !== 3) {
            return null;
        }

        return [
            'prefix' => $parts[0],
            'year' => (int) $parts[1],
            'sequence' => (int) $parts[2]
        ];
    }

    /**
     * Extract application type from protocol number
     *
     * @param string $protocolNumber Protocol number
     * @return string|null Application type or null if unknown
     */
    public function getApplicationType(string $protocolNumber): ?string
    {
        $parsed = $this->parse($protocolNumber);
        if ($parsed === null) {
            return null;
        }

        // Find matching application type by prefix
        $allPrefixes = $this->getAllPrefixes();
        $prefix = $parsed['prefix'];

        foreach ($allPrefixes as $type => $p) {
            if ($p === $prefix) {
                return $type;
            }
        }

        return null;
    }

    /**
     * Format sequence number with zero-padding
     *
     * @param int $sequence Sequence number
     * @return string Formatted sequence
     */
    private function formatSequence(int $sequence): string
    {
        return str_pad((string) $sequence, self::SEQUENCE_PAD_LENGTH, '0', STR_PAD_LEFT);
    }

    /**
     * Check if protocol number is for current year
     *
     * @param string $protocolNumber Protocol number
     * @return bool True if from current year
     */
    public function isCurrentYear(string $protocolNumber): bool
    {
        $parsed = $this->parse($protocolNumber);
        if ($parsed === null) {
            return false;
        }

        return $parsed['year'] === (int) date(self::YEAR_FORMAT);
    }

    /**
     * Get sequence from protocol number
     *
     * @param string $protocolNumber Protocol number
     * @return int|null Sequence number or null if invalid
     */
    public function getSequence(string $protocolNumber): ?int
    {
        $parsed = $this->parse($protocolNumber);
        return $parsed['sequence'] ?? null;
    }

    /**
     * Get year from protocol number
     *
     * @param string $protocolNumber Protocol number
     * @return int|null Year or null if invalid
     */
    public function getYear(string $protocolNumber): ?int
    {
        $parsed = $this->parse($protocolNumber);
        return $parsed['year'] ?? null;
    }

    /**
     * Get all protocol numbers for a year
     *
     * @param \PDO $db Database connection
     * @param string $year Year
     * @return array Protocol numbers
     */
    public function getProtocolsForYear(\PDO $db, string $year): array
    {
        $stmt = $db->prepare("
            SELECT protocol_number
            FROM applications
            WHERE protocol_number LIKE :year
            ORDER BY protocol_number ASC
        ");

        $stmt->execute([':year' => '%-' . $year . '-%']);

        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Get all protocol numbers for an application type
     *
     * @param \PDO $db Database connection
     * @param string $applicationType Application type
     * @return array Protocol numbers
     */
    public function getProtocolsForType(\PDO $db, string $applicationType): array
    {
        $stmt = $db->prepare("
            SELECT protocol_number
            FROM applications
            WHERE application_type = :type
            ORDER BY id DESC
        ");

        $stmt->execute([':type' => $applicationType]);

        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Check if protocol number already exists
     *
     * @param \PDO $db Database connection
     * @param string $protocolNumber Protocol number to check
     * @return bool True if exists
     */
    public function exists(\PDO $db, string $protocolNumber): bool
    {
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM applications WHERE protocol_number = :protocol
        ");

        $stmt->execute([':protocol' => $protocolNumber]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Generate next protocol number for existing application update
     *
     * Increments the sequence while keeping the original prefix and year.
     *
     * @param \PDO $db Database connection
     * @param string $existingProtocol Original protocol number
     * @return string New protocol number
     */
    public function generateNextVersion(\PDO $db, string $existingProtocol): string
    {
        $parsed = $this->parse($existingProtocol);
        if ($parsed === null) {
            // If can't parse, generate new from scratch
            return $this->generate($db, 'student');
        }

        $prefix = $parsed['prefix'];
        $year = $parsed['year'];
        $currentSequence = $parsed['sequence'];

        // Find the application type
        $type = $this->getApplicationType($existingProtocol) ?? 'student';

        // Find the next available sequence for this prefix/year
        $stmt = $db->prepare("
            SELECT MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(protocol_number, '-', -1), '-', 1) AS UNSIGNED)) as max_seq
            FROM applications
            WHERE protocol_number LIKE :prefix
        ");

        $stmt->execute([':prefix' => $prefix . '-' . $year . '%']);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $maxSequence = (int) ($result['max_seq'] ?? $currentSequence);

        return $prefix . '-' . $year . '-' . $this->formatSequence($maxSequence + 1);
    }
}
