CREATE DATABASE IF NOT EXISTS db_lms;
USE db_lms;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE quizzes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT,
    title VARCHAR(100) NOT NULL,
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

CREATE TABLE questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quiz_id INT,
    question_text TEXT NOT NULL,
    option_a TEXT NOT NULL,
    option_b TEXT NOT NULL,
    option_c TEXT NOT NULL,
    option_d TEXT NOT NULL,
    correct_answer CHAR(1) NOT NULL,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id)
);

CREATE TABLE user_progress (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    course_id INT,
    quiz_id INT,
    score INT DEFAULT 0,
    completed BOOLEAN DEFAULT FALSE,
    progress_percentage FLOAT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id)
);

-- Clear all data with proper order
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE user_progress;
TRUNCATE TABLE questions;
TRUNCATE TABLE quizzes;
TRUNCATE TABLE courses;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

ALTER TABLE user_progress ADD INDEX idx_user_quiz (user_id, quiz_id);
ALTER TABLE quizzes ADD INDEX idx_course (course_id);
ALTER TABLE courses MODIFY COLUMN title VARCHAR(255) NOT NULL;
ALTER TABLE courses MODIFY COLUMN description TEXT NOT NULL;

-- Modify password field to store hashed passwords
ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NOT NULL;

-- Add indexes for security
CREATE INDEX idx_username ON users(username);
CREATE INDEX idx_email ON users(email);

-- Update default admin password to hashed version
UPDATE users SET password = '$2y$10$YourHashedPasswordHere' WHERE username = 'admin';

-- Add additional security-related columns
ALTER TABLE users 
ADD COLUMN failed_attempts INT DEFAULT 0,
ADD COLUMN last_failed_attempt TIMESTAMP NULL,
ADD COLUMN account_locked BOOLEAN DEFAULT FALSE;

-- Insert default admin account with simple password (admin123)
INSERT INTO users (username, password, email, role) VALUES 
('admin', 'admin123', 'admin@lms.com', 'admin');

-- Insert sample course for testing
INSERT INTO courses (title, description, created_by) VALUES
('Sample Course', 'This is a sample course', 1);

ALTER TABLE user_progress 
ADD COLUMN IF NOT EXISTS is_correct BOOLEAN DEFAULT FALSE;

-- Update foreign key constraints
ALTER TABLE questions DROP FOREIGN KEY questions_ibfk_1;
ALTER TABLE questions ADD CONSTRAINT questions_ibfk_1 
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) 
    ON DELETE CASCADE;

ALTER TABLE user_progress DROP FOREIGN KEY user_progress_ibfk_3;
ALTER TABLE user_progress ADD CONSTRAINT user_progress_ibfk_3 
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) 
    ON DELETE CASCADE;
