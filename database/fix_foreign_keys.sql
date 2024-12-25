-- First, disable foreign key checks
SET FOREIGN_KEY_CHECKS = 0;

-- Drop existing foreign keys
ALTER TABLE questions DROP FOREIGN KEY IF EXISTS questions_ibfk_1;
ALTER TABLE user_progress DROP FOREIGN KEY IF EXISTS user_progress_ibfk_3;

-- Create new foreign keys with CASCADE
ALTER TABLE questions
ADD CONSTRAINT questions_ibfk_1 
FOREIGN KEY (quiz_id) REFERENCES quizzes(id) 
ON DELETE CASCADE;

ALTER TABLE user_progress
ADD CONSTRAINT user_progress_ibfk_3 
FOREIGN KEY (quiz_id) REFERENCES quizzes(id) 
ON DELETE CASCADE;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;
