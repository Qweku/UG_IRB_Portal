-- Add session_token column to users table
-- This migration adds single-session-per-user support

ALTER TABLE users ADD COLUMN session_token VARCHAR(64) NULL AFTER password_hash;

-- Add session_expires_at column for timeout handling
ALTER TABLE users ADD COLUMN session_expires_at DATETIME NULL AFTER session_token;

-- Add last_activity column for activity tracking
ALTER TABLE users ADD COLUMN last_activity DATETIME NULL AFTER session_expires_at;

-- Add index for faster session token lookups
CREATE INDEX idx_session_token ON users(session_token);
CREATE INDEX idx_user_session ON users(id, session_token);
