-- Active: 1735260498590@@127.0.0.1@3306@db_lms
-- Active: 1735260498590@@127.0.0.1@3306
-- First, make sure there's no existing admin user
DELETE FROM users WHERE username = 'admin';

-- Insert default admin with hashed password (admin123) and full name
INSERT INTO users (username, full_name, email, password, role) VALUES 
('admin', 
 'System Administrator', 
 'admin@lms.com',
 '$2y$10$8F.az1Cd2rPPf1HpGZ/CPU0THJzHwx5OyKwZJr1bU26.gXRmpX6p2', -- hashed 'admin123'
 'admin'
);

-- Insert a sample course created by admin
INSERT INTO courses (title, description, created_by) 
SELECT 'Welcome Course', 'Introduction to the Learning Management System', id 
FROM users WHERE username = 'admin';
