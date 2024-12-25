# Learning Management System (LMS)

A web-based learning management system built with PHP and MySQL.

## Features
- User Management (Admin/Student roles)
- Course Management
- Quiz System
- Progress Tracking
- Responsive Design

## Installation
1. Install XAMPP (version 7.4 or higher)
2. Clone this repository to `c:/xampp/htdocs/lms_project`
3. Import `database/db_lms.sql` to phpMyAdmin
4. Configure database connection in `config.php`
5. Access the system at `http://localhost/lms_project`

## Default Login
- Admin Account:
  - Username: admin
  - Password: admin123

## Requirements
- PHP 7.4+
- MySQL 5.7+
- Apache Server
- Modern web browser

# Administrator Guide

## User Management
- Add/edit/delete users
- Manage user roles
- Reset user passwords

## Course Management
1. Create new courses
2. Add quizzes to courses
3. Create quiz questions
4. Monitor student progress

## Progress Tracking
- View overall course completion rates
- Track individual student progress
- Generate progress reports

# Student User Guide

## Getting Started
1. Login with your provided credentials
2. View available courses on dashboard
3. Start or continue courses
4. Take quizzes to track progress

## Taking Quizzes
1. Select a course
2. Choose a quiz
3. Answer all questions
4. Submit for immediate results

## Tracking Progress
- View overall progress on dashboard
- Check individual course completion
- Review quiz scores and answers

# Security Guidelines

## Password Management
- All passwords are hashed using PHP's password_hash()
- Minimum password length: 8 characters
- Password reset functionality available

## Database Security
- Prepared statements for all queries
- Input sanitization
- XSS prevention measures

## Session Security
- Session timeout after 30 minutes
- CSRF protection
- Secure session handling