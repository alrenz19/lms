-- Disable foreign key checks
SET FOREIGN_KEY_CHECKS = 0;

-- First, update any courses created by admin to prevent constraint issues
UPDATE courses SET created_by = NULL WHERE created_by IN (SELECT id FROM users WHERE username = 'admin');

-- Delete the existing admin user
DELETE FROM users WHERE username = 'admin';

-- Insert admin with properly hashed password (admin123)
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@lms.com', '$2y$10$YM0mTq4TRyFBCmtDoTLPjOxZEse7L7Q6NKOG90zk90OTEwoy2Vwle', 'admin');

-- Update the courses to link back to the new admin user
UPDATE courses SET created_by = (SELECT id FROM users WHERE username = 'admin') WHERE created_by IS NULL;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;
