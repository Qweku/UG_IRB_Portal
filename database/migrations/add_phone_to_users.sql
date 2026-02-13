-- Add phone column to users table
-- This migration adds phone number support for users

ALTER TABLE users ADD COLUMN phone VARCHAR(50) NULL AFTER email;
