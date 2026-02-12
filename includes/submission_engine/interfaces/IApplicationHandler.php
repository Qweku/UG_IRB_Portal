<?php

/**
 * IApplicationHandler Interface
 *
 * Defines the contract that all application handlers must implement.
 * This interface ensures consistent behavior across different application types.
 *
 * @package UGIRB\SubmissionEngine\Interfaces
 */

namespace UGIRB\SubmissionEngine\Interfaces;

interface IApplicationHandler
{
    /**
     * Validate form data
     *
     * @param array $data Form data to validate
     * @return array ['success' => bool, 'errors' => array]
     */
    public function validate(array $data): array;

    /**
     * Save application to database
     *
     * @param array $data Form data
     * @param array $files Uploaded files
     * @return array ['success' => bool, 'message' => string, 'application_id' => int]
     */
    public function save(array $data, array $files = []): array;

    /**
     * Handle the complete submission process
     *
     * This is the main entry point for processing form submissions.
     *
     * @return array Result with success status and message
     */
    public function handleSubmission(): array;

    /**
     * Get protocol number prefix for this application type
     *
     * @return string Protocol prefix (e.g., 'STU', 'NIRB', 'EXT')
     */
    public function getProtocolPrefix(): string;

    /**
     * Get application type identifier
     *
     * @return string Application type (e.g., 'student', 'nmimr', 'non_nmimr')
     */
    public function getType(): string;

    /**
     * Get type-specific required fields
     *
     * @return array List of required field names
     */
    public function getRequiredFields(): array;

    /**
     * Get file upload requirements
     *
     * @return array ['field_name' => ['required' => bool, 'label' => string]]
     */
    public function getFileRequirements(): array;

    /**
     * Set handler options after instantiation
     *
     * @param array $options Handler-specific options
     */
    public function setOptions(array $options): void;

    /**
     * Set draft mode for submission
     *
     * When in draft mode, some validations are relaxed (e.g., file uploads become optional).
     *
     * @param bool $isDraft Whether this is a draft submission
     */
    public function setDraftMode(bool $isDraft): void;
}
