-- Add created_by column to studies table for user-specific drafts
-- This column tracks which user created a draft study

-- Add created_by column (will fail silently if column already exists in some MySQL versions)
ALTER TABLE studies ADD COLUMN created_by INT UNSIGNED NULL;

-- Add index for faster lookups (will fail silently if index already exists)
ALTER TABLE studies ADD INDEX idx_created_by_draft (created_by, is_draft);
