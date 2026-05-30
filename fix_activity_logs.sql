-- Add Missing Activity Logs Table
-- Run this script in phpMyAdmin to fix the missing table error

CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Verify table creation
SHOW TABLES LIKE 'activity_logs';

-- Test the logActivity function
-- This should work after running the above CREATE TABLE statement
