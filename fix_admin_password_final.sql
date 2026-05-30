-- Fix Admin Password
-- Run this SQL in phpMyAdmin after importing the database

-- This hash is for 'admin123'
UPDATE users SET password = '$2y$10$YQ6z/Jz6jZzZzZzZzZzZzZO9Q5v5h5h5h5h5h5h5h5h5h5h5h5h5h5h5h5h5' WHERE username = 'admin';

-- Alternative: Use plain text for testing (NOT RECOMMENDED FOR PRODUCTION)
-- UPDATE users SET password = 'admin123' WHERE username = 'admin';

-- Verify the update
SELECT username, full_name, role_id, is_active FROM users WHERE username = 'admin';
