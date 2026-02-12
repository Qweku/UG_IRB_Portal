-- ============================================================================
-- Application Reviews Database Table
-- ============================================================================
-- This SQL script creates the application_reviews table for storing reviewer
-- feedback and decisions for IRB applications (student, NMIMR, and non-NMIMR).
-- ============================================================================

-- Drop table if it exists (for clean recreation)
DROP TABLE IF EXISTS `application_reviews`;

-- ============================================================================
-- Main Application Reviews Table
-- ============================================================================
CREATE TABLE `application_reviews` (
    -- Primary identifier
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Application reference (supports multiple application types)
    `application_type` ENUM('student', 'nmimr', 'non_nmimr') NOT NULL,
    `application_id` INT UNSIGNED NOT NULL,

    -- Reviewer information
    `reviewer_id` INT UNSIGNED NOT NULL,

    -- Review status workflow
    `review_status` ENUM('pending', 'assigned', 'in_progress', 'completed', 'reassigned') NOT NULL DEFAULT 'pending',

    -- Review decision
    `review_decision` ENUM('approved', 'rejected', 'needs_revision', 'deferred', 'expedited') NULL,

    -- Review scores/ratings (optional scoring system)
    `scientific_quality_score` TINYINT UNSIGNED NULL COMMENT 'Score 1-5 for scientific quality',
    `ethical_considerations_score` TINYINT UNSIGNED NULL COMMENT 'Score 1-5 for ethical considerations',
    `methodology_score` TINYINT UNSIGNED NULL COMMENT 'Score 1-5 for methodology',
    `overall_score` DECIMAL(5,2) NULL COMMENT 'Overall score/percentage',

    -- Review comments and feedback
    `summary_comments` TEXT NULL COMMENT 'Overall summary of the review',
    `major_issues` TEXT NULL COMMENT 'Major issues identified',
    `minor_issues` TEXT NULL COMMENT 'Minor issues identified',
    `recommendations` TEXT NULL COMMENT 'Recommendations for improvement',
    `confidential_notes` TEXT NULL COMMENT 'Confidential notes for IRB committee',

    -- Decision-specific fields
    `approval_effective_date` DATE NULL,
    `approval_expiration_date` DATE NULL,
    `rejection_reason` TEXT NULL,
    `revision_required_date` DATE NULL,

    -- Review timeline
    `assigned_at` DATETIME NULL DEFAULT NULL,
    `started_at` DATETIME NULL DEFAULT NULL,
    `submitted_at` DATETIME NULL DEFAULT NULL,

    -- Metadata
    `version_number` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Version of the review (for revisions)',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Foreign key constraints
    -- Note: application_id references different tables based on application_type
    -- This is handled at application level, not database level for MySQL
    CONSTRAINT `fk_review_reviewer` FOREIGN KEY (`reviewer_id`)
        REFERENCES `personnel` (`id`) ON DELETE RESTRICT,

    -- Indexes for frequently queried columns
    INDEX `idx_application_type_id` (`application_type`, `application_id`),
    INDEX `idx_reviewer_id` (`reviewer_id`),
    INDEX `idx_review_status` (`review_status`),
    INDEX `idx_review_decision` (`review_decision`),
    INDEX `idx_assigned_at` (`assigned_at`),
    INDEX `idx_submitted_at` (`submitted_at`),
    INDEX `idx_application_status` (`application_type`, `application_id`, `review_status`),
    INDEX `idx_reviewer_status` (`reviewer_id`, `review_status`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Review Checklist Items Table (for structured review criteria)
-- ============================================================================
DROP TABLE IF EXISTS `application_review_checklist`;

CREATE TABLE `application_review_checklist` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `review_id` INT UNSIGNED NOT NULL,
    `category` VARCHAR(100) NOT NULL COMMENT 'e.g., informed_consent, methodology, risks',
    `item_description` VARCHAR(255) NOT NULL,
    `is_approved` TINYINT UNSIGNED NULL COMMENT '1=yes, 0=no, NULL=not applicable',
    `reviewer_comments` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Foreign key constraint
    CONSTRAINT `fk_checklist_review` FOREIGN KEY (`review_id`)
        REFERENCES `application_reviews` (`id`) ON DELETE CASCADE,

    -- Indexes
    INDEX `idx_checklist_review_id` (`review_id`),
    INDEX `idx_checklist_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Review History/Audit Log Table
-- ============================================================================
DROP TABLE IF EXISTS `application_review_history`;

CREATE TABLE `application_review_history` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `review_id` INT UNSIGNED NOT NULL,
    `action` VARCHAR(50) NOT NULL COMMENT 'e.g., assigned, started, submitted, updated',
    `previous_value` TEXT NULL,
    `new_value` TEXT NULL,
    `performed_by` INT UNSIGNED NOT NULL COMMENT 'User who performed the action',
    `performed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `notes` TEXT NULL,

    -- Indexes
    INDEX `idx_history_review_id` (`review_id`),
    INDEX `idx_history_action` (`action`),
    INDEX `idx_history_performed_at` (`performed_at`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Sample Data (Optional - for testing)
-- ============================================================================

-- Sample review entry
-- INSERT INTO `application_reviews`
--     (application_type, application_id, reviewer_id, review_status, review_decision,
--      summary_comments, assigned_at, started_at, submitted_at)
-- VALUES
--     ('non_nmimr', 1, 5, 'completed', 'needs_revision',
--      'The application shows good scientific merit but requires revisions to the informed consent document.',
--      NOW() - INTERVAL 7 DAY, NOW() - INTERVAL 5 DAY, NOW() - INTERVAL 2 DAY);

-- Sample checklist items
-- INSERT INTO `application_review_checklist`
--     (review_id, category, item_description, is_approved, reviewer_comments)
-- VALUES
--     (1, 'informed_consent', 'Is the consent form clearly written?', 0, 'Language is too technical for participants'),
--     (1, 'methodology', 'Is the study design appropriate?', 1, NULL),
--     (1, 'risks', 'Are risks adequately described?', 1, NULL);
