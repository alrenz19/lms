-- Active: 1736124649896@@127.0.0.1@3306@db_lms
ALTER TABLE user_progress 
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
ADD COLUMN is_correct BOOLEAN DEFAULT FALSE;

ALTER TABLE user_progress ADD UNIQUE KEY unique_user_question (user_id, quiz_id);
UPDATE user_progress SET is_correct = FALSE WHERE is_correct IS NULL;



USE db_lms;

-- Create course_videos table
CREATE TABLE course_videos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT,
    file_path VARCHAR(255),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Add has_video column to courses table
ALTER TABLE courses ADD COLUMN has_video BOOLEAN DEFAULT FALSE;

-- Create user_video_progress table
CREATE TABLE user_video_progress (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    video_id INT,
    watched BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (video_id) REFERENCES course_videos(id) ON DELETE CASCADE
);
