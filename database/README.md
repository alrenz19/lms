# Database Setup Instructions

This folder contains the database setup files for the Learning Management System (LMS).

## Initial Setup

1. Make sure MySQL server is running
2. Run the setup script:
   ```bash
   mysql -u root < setup.sql
   ```

## Default Accounts

After setup, the following accounts are available:

1. Admin Account:
   - Username: admin
   - Password: admin123
   - Email: admin@lms.com

2. Default User Account:
   - Username: user
   - Password: admin123
   - Email: user@lms.com

## Database Structure

The database includes the following tables:
- users: User accounts and authentication
- courses: Course information
- quizzes: Quiz information for courses
- questions: Quiz questions and answers
- user_progress: Track user progress in courses and quizzes
- course_videos: Course video information
- user_video_progress: Track user progress in watching videos

## Notes

- All passwords are hashed using PHP's password_hash() function
- The setup script will create the database if it doesn't exist
- All tables use InnoDB engine and UTF-8 character set 