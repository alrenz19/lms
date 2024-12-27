-- Add completion_date column to user_progress table
ALTER TABLE user_progress 
ADD COLUMN completion_date TIMESTAMP NULL DEFAULT NULL;

-- Update existing completion dates based on completed status
UPDATE user_progress 
SET completion_date = updated_at 
WHERE completed = TRUE;
