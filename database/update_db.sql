ALTER TABLE user_progress 
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
ADD COLUMN is_correct BOOLEAN DEFAULT FALSE;

ALTER TABLE user_progress ADD UNIQUE KEY unique_user_question (user_id, quiz_id);
UPDATE user_progress SET is_correct = FALSE WHERE is_correct IS NULL;
