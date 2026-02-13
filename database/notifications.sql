-- ============================================================================
-- Notifications Database Table
-- ============================================================================
-- This SQL script creates the notifications table for storing user
-- notifications in the UG IRB Portal system.
-- ============================================================================

-- Drop table if exists (for clean recreation)
DROP TABLE IF EXISTS `notifications`;

-- ============================================================================
-- Main Notifications Table
-- ============================================================================
CREATE TABLE `notifications` (
    -- Primary identifier
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- User reference (who receives the notification)
    `user_id` INT UNSIGNED NOT NULL COMMENT 'References personnel table for the notification recipient',

    -- User role for role-based notification filtering
    `role` ENUM('admin', 'applicant', 'reviewer') NULL COMMENT 'User role for filtering notifications by user type',

    -- Notification content
    `title` VARCHAR(255) NOT NULL COMMENT 'Notification title/type',
    `message` TEXT NOT NULL COMMENT 'Main notification message',
    `details` TEXT NULL COMMENT 'Extended details about the notification',

    -- Notification classification
    `type` ENUM('application', 'review', 'meeting', 'system', 'general') NOT NULL DEFAULT 'general'
        COMMENT 'Type of notification: application, review, meeting, system, or general',

    -- Read/unread status
    `is_read` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0=unread, 1=read',

    -- Optional action URL (for clickable notifications)
    `action_url` VARCHAR(500) NULL COMMENT 'URL to navigate when notification is clicked',
    `action_text` VARCHAR(100) NULL COMMENT 'Text for the action button',
    `action_type` VARCHAR(50) NULL COMMENT 'Type of action: review, assign, view, agenda, acknowledge, etc.',

    -- Priority level
    `priority` ENUM('low', 'normal', 'high', 'urgent') NOT NULL DEFAULT 'normal'
        COMMENT 'Notification priority level',

    -- Metadata for action buttons (stored as JSON)
    `actions_json` JSON NULL COMMENT 'JSON array of action buttons with text, primary flag, and action type',

    -- Timestamps
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `read_at` DATETIME NULL COMMENT 'When the notification was marked as read',

    -- Foreign key constraints
    CONSTRAINT `fk_notification_user`
        FOREIGN KEY (`user_id`)
        REFERENCES `personnel` (`id`)
        ON DELETE CASCADE,

    -- Indexes for frequently queried columns
    INDEX `idx_notification_user_role` (`user_id`, `role`),
    INDEX `idx_notification_user_unread_role` (`user_id`, `is_read`, `role`),
    INDEX `idx_notification_type` (`type`),
    INDEX `idx_notification_priority` (`priority`),
    INDEX `idx_notification_role` (`role`),
    INDEX `idx_notification_created` (`created_at`),
    INDEX `idx_notification_user_role_created` (`user_id`, `role`, `created_at` DESC),
    INDEX `idx_notification_user_unread_type_role` (`user_id`, `is_read`, `type`, `role`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Stores user notifications for the UG IRB Portal system';

-- ============================================================================
-- Sample Data (Optional - for testing)
-- ============================================================================

-- Sample notification entry
-- INSERT INTO `notifications`
--     (user_id, title, message, details, type, is_read, priority, action_url, action_text, action_type)
-- VALUES
--     (5, 'New Application Received', 'Study protocol #2024-001 has been submitted for review.',
--      'This is a new research application seeking approval for a clinical trial on diabetes treatment.',
--      'application', 0, 'high', '/admin/pages/contents/review_content.php?id=1', 'Review', 'review');

-- Sample notification with actions
-- INSERT INTO `notifications`
--     (user_id, title, message, type, is_read, priority, actions_json)
-- VALUES
--     (5, 'Review Completed', 'Dr. Jane Doe has completed the review for protocol #2023-156.',
--      'review', 0, 'normal',
--      '[{"text": "View Review", "primary": true, "action": "view"}, {"text": "Prepare Response", "primary": false, "action": "respond"}]');

-- ============================================================================
-- Usage Examples
-- ============================================================================

-- Get all unread notifications for a user
-- SELECT * FROM `notifications` WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC;

-- Mark a notification as read
-- UPDATE `notifications` SET is_read = 1, read_at = NOW() WHERE id = ?;

-- Mark all notifications as read for a user
-- UPDATE `notifications` SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0;

-- Get unread count for a user
-- SELECT COUNT(*) as unread_count FROM `notifications` WHERE user_id = ? AND is_read = 0;

-- Get notifications with pagination
-- SELECT * FROM `notifications` WHERE user_id = ? ORDER BY created_at DESC LIMIT ?, ?;
