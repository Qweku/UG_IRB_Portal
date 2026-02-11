-- ============================================================================
-- Non-NMIMR Application Form Database Tables
-- ============================================================================
-- This SQL script creates the necessary tables for storing non-NMIMR 
-- IRB application form data including applications, documents, and checklists.
-- ============================================================================

-- Drop tables if they exist (for clean recreation)
DROP TABLE IF EXISTS `non_nmimr_checklist`;
DROP TABLE IF EXISTS `non_nmimr_application_documents`;
DROP TABLE IF EXISTS `non_nmimr_applications`;

-- ============================================================================
-- Main Applications Table
-- ============================================================================
CREATE TABLE `non_nmimr_applications` (
    -- Hidden fields
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `csrf_token` VARCHAR(255) NULL,
    `application_type` VARCHAR(50) NOT NULL DEFAULT 'non_nmimr',
    `application_id` VARCHAR(100) NULL,
    `initial_step` TINYINT UNSIGNED NOT NULL DEFAULT 1,

    -- Step 1: Study Identification
    `protocol_number` VARCHAR(100) NULL,
    `version_number` VARCHAR(20) NULL,
    `study_title` TEXT NULL,

    -- Step 2: Principal Investigator & Study Details (new individual fields)
    `pi_name` VARCHAR(100) NULL,
    `pi_institution` VARCHAR(100) NULL,
    `pi_address` VARCHAR(100) NULL,
    `pi_phone_number` VARCHAR(50) NULL,
    `pi_fax` VARCHAR(50) NULL,
    `pi_email` VARCHAR(100) NULL,
    `co_pi_name` VARCHAR(100) NULL,
    `co_pi_qualification` VARCHAR(100) NULL,
    `co_pi_department` VARCHAR(100) NULL,
    `co_pi_address` VARCHAR(100) NULL,
    `co_pi_phone_number` VARCHAR(50) NULL,
    `co_pi_fax` VARCHAR(50) NULL,
    `co_pi_email` VARCHAR(100) NULL,
    `prior_scientific_review` ENUM('yes', 'no', 'pending') NULL,
    `prior_irb_review` ENUM('yes', 'no', 'pending') NULL,
    `collaborating_institutions` TEXT NULL,
    `funding_source` VARCHAR(255) NULL,
    `research_type` VARCHAR(100) NULL,
    `research_type_other` VARCHAR(255) NULL,
    `duration` VARCHAR(100) NULL,

    -- Step 3: Study Protocol
    `abstract` TEXT NULL,
    `introduction` TEXT NULL,
    `literature_review` TEXT NULL,
    `aims` TEXT NULL,
    `methodology` TEXT NULL,
    `ethical_considerations` TEXT NULL,
    `expected_outcomes` TEXT NULL,
    `references` TEXT NULL,
    `work_plan` TEXT NULL,
    `budget` TEXT NULL,
    `consent_form` TEXT NULL,
    `assent_form` TEXT NULL,
    `data_instruments` TEXT NULL,

    -- Step 4: Signatures
    `pi_name` VARCHAR(255) NULL,
    `pi_signature` VARCHAR(255) NULL,
    `pi_date` DATE NULL,
    `co_pi_name` VARCHAR(255) NULL,
    `co_pi_signature` VARCHAR(255) NULL,
    `co_pi_date` DATE NULL,

    -- Step 5: Submission Checklist
    `check_complete` TINYINT(1) DEFAULT 0,
    `check_font` TINYINT(1) DEFAULT 0,
    `check_consent` TINYINT(1) DEFAULT 0,
    `check_pdf` TINYINT(1) DEFAULT 0,
    `check_signed` TINYINT(1) DEFAULT 0,
    `check_checklist` TINYINT(1) DEFAULT 0,
    `submission_notes` TEXT NULL,

    -- Metadata
    `user_id` INT UNSIGNED NOT NULL,
    `status` ENUM('draft', 'submitted', 'under_review', 'approved', 'rejected', 'withdrawn') NOT NULL DEFAULT 'draft',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes for frequently queried columns
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_application_type` (`application_type`),
    INDEX `idx_protocol_number` (`protocol_number`(191)),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_user_status` (`user_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Documents Table for Uploaded Files
-- ============================================================================
CREATE TABLE `non_nmimr_application_documents` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `application_id` INT UNSIGNED NOT NULL,
    `document_type` ENUM('approval_letters', 'required_forms', 'final_pdf', 'supporting_docs', 'other') NOT NULL DEFAULT 'other',
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `file_size` INT UNSIGNED NOT NULL DEFAULT 0,
    `mime_type` VARCHAR(100) NULL,
    `uploaded_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Foreign key constraint with ON DELETE CASCADE
    CONSTRAINT `fk_application_documents_application` FOREIGN KEY (`application_id`)
        REFERENCES `non_nmimr_applications` (`id`) ON DELETE CASCADE,

    -- Indexes
    INDEX `idx_doc_application_id` (`application_id`),
    INDEX `idx_doc_document_type` (`document_type`),
    INDEX `idx_doc_uploaded_at` (`uploaded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Checklist Items Table
-- ============================================================================
CREATE TABLE `non_nmimr_checklist` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `application_id` INT UNSIGNED NOT NULL,
    `checklist_item` VARCHAR(100) NOT NULL,
    `is_checked` TINYINT(1) NOT NULL DEFAULT 0,
    `checked_at` DATETIME NULL,

    -- Foreign key constraint with ON DELETE CASCADE
    CONSTRAINT `fk_checklist_application` FOREIGN KEY (`application_id`)
        REFERENCES `non_nmimr_applications` (`id`) ON DELETE CASCADE,

    -- Unique constraint to prevent duplicate checklist items per application
    UNIQUE KEY `uk_checklist_item` (`application_id`, `checklist_item`),

    -- Indexes
    INDEX `idx_checklist_application_id` (`application_id`),
    INDEX `idx_checklist_is_checked` (`is_checked`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Insert Default Checklist Items
-- ============================================================================
INSERT INTO `non_nmimr_checklist` (`checklist_item`) VALUES
('check_complete'),
('check_font'),
('check_consent'),
('check_pdf'),
('check_signed'),
('check_checklist');

-- ============================================================================
-- Trigger to set checked_at timestamp when is_checked is updated
-- ============================================================================
DELIMITER //

CREATE TRIGGER `trg_checklist_checked_at` BEFORE UPDATE ON `non_nmimr_checklist`
FOR EACH ROW
BEGIN
    IF NEW.is_checked = 1 AND OLD.is_checked = 0 THEN
        SET NEW.checked_at = CURRENT_TIMESTAMP;
    END IF;
END//

DELIMITER ;

-- ============================================================================
-- View for Application Summary (Optional)
-- ============================================================================
-- CREATE OR REPLACE VIEW `view_non_nmimr_applications_summary` AS
-- SELECT 
--     a.id,
--     a.application_id,
--     a.protocol_number,
--     a.study_title,
--     a.research_type,
--     a.status,
--     a.user_id,
--     a.created_at,
--     a.updated_at,
--     COUNT(d.id) AS document_count,
--     COUNT(c.id) AS checklist_count,
--     SUM(c.is_checked) AS checklist_completed
-- FROM `non_nmimr_applications` a
-- LEFT JOIN `non_nmimr_application_documents` d ON a.id = d.application_id
-- LEFT JOIN `non_nmimr_checklist` c ON a.id = c.application_id
-- GROUP BY a.id;
