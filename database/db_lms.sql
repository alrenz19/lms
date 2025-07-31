-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 31, 2025 at 07:57 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_lms`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `department` int(10) NOT NULL,
  `division` int(10) NOT NULL,
  `section` int(10) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `has_video` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `removed` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `description`, `department`, `division`, `section`, `created_by`, `has_video`, `created_at`, `updated_at`, `removed`) VALUES
(167, 'dasda', 'sdasdas', 0, 0, 0, 10, 1, '2025-07-31 05:30:39', '2025-07-31 05:30:39', 0),
(168, 'dasdasdasdas', 'asdasdsaasdas', 0, 0, 0, 10, 2, '2025-07-31 05:31:58', '2025-07-31 05:31:58', 0),
(169, 'dasdasd', 'asdasdas', 0, 0, 0, 10, 1, '2025-07-31 05:41:21', '2025-07-31 05:41:21', 0),
(170, 'dasdas', 'dasdsa', 0, 0, 0, 10, 1, '2025-07-31 05:42:12', '2025-07-31 05:42:12', 0),
(171, 'dasdasd', 'asdasdas', 0, 0, 0, 10, 1, '2025-07-31 05:43:18', '2025-07-31 05:43:18', 0),
(172, 'dasd', 'asdasdas', 0, 0, 0, 10, 1, '2025-07-31 05:44:58', '2025-07-31 05:44:58', 0),
(173, 'dasdas', 'dasdas', 0, 0, 0, 10, 1, '2025-07-31 05:45:15', '2025-07-31 05:45:15', 0),
(174, 'dasd', 'asdasdas', 0, 0, 0, 10, 1, '2025-07-31 05:46:32', '2025-07-31 05:46:32', 0),
(175, 'fddsf', 'sdfsdfds', 0, 0, 0, 10, 1, '2025-07-31 05:47:03', '2025-07-31 05:47:03', 0);

-- --------------------------------------------------------

--
-- Table structure for table `course_videos`
--

CREATE TABLE `course_videos` (
  `id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `module_name` varchar(100) NOT NULL,
  `module_description` varchar(255) NOT NULL,
  `video_url` varchar(255) NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `removed` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_videos`
--

INSERT INTO `course_videos` (`id`, `course_id`, `module_name`, `module_description`, `video_url`, `file_size`, `created_at`, `updated_at`, `removed`) VALUES
(51, 167, 'dasdas', 'dasd', '/uploads/modules/mod_688aff7f994948.88036875_JF96E7142511.pdf', 345521, '2025-07-31 05:30:39', '2025-07-31 05:30:39', 0),
(52, 168, 'dasdasdas', 'dasdasd', '/uploads/modules/mod_688affce55c624.04358397_4K_Sample_Video___Alpha_6700___Sony_____.mp4', 31077402, '2025-07-31 05:31:58', '2025-07-31 05:31:58', 0),
(53, 168, 'asdasdasd', 'asdasd', '/uploads/modules/mod_688affce571504.85704718_JF96E7142511.pdf', 345521, '2025-07-31 05:31:58', '2025-07-31 05:31:58', 0),
(54, 169, 'dasdas', 'dasdsa', '/uploads/modules/mod_688b02017090e0.21422402_JF96E7142511.pdf', 345521, '2025-07-31 05:41:21', '2025-07-31 05:41:21', 0),
(55, 170, 'dasdas', 'dasd', '/uploads/modules/mod_688b0234b010e9.92585853_JF96E7142511.pdf', 345521, '2025-07-31 05:42:12', '2025-07-31 05:42:12', 0),
(56, 171, 'dasdasd', 'asdas', '/uploads/modules/mod_688b027616ac58.59425871_JF96E7142511.pdf', 345521, '2025-07-31 05:43:18', '2025-07-31 05:43:18', 0),
(57, 172, 'dasdas', 'dasd', '/uploads/modules/mod_688b02dabb2578.59272117_4K_Sample_Video___Alpha_6700___Sony_____.mp4', 31077402, '2025-07-31 05:44:58', '2025-07-31 05:44:58', 0),
(58, 173, 'dasdas', 'dasd', '/uploads/modules/mod_688b02ebb9ca08.20217811_4K_Sample_Video___Alpha_6700___Sony_____.mp4', 31077402, '2025-07-31 05:45:15', '2025-07-31 05:45:15', 0),
(59, 174, 'dasdasd', 'asdasd', '/uploads/modules/mod_688b03384f1b15.90301524_4K_Sample_Video___Alpha_6700___Sony_____.mp4', 31077402, '2025-07-31 05:46:32', '2025-07-31 05:46:32', 0),
(60, 175, 'fsdafds', 'fsdfdsf', '/uploads/modules/mod_688b035739f727.77247632_4K_Sample_Video___Alpha_6700___Sony_____.mp4', 31077402, '2025-07-31 05:47:03', '2025-07-31 05:47:03', 0);

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `id` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `removed` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`id`, `Name`, `removed`) VALUES
(0, 'All Department', 0);

-- --------------------------------------------------------

--
-- Table structure for table `division`
--

CREATE TABLE `division` (
  `Id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `removed` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `division`
--

INSERT INTO `division` (`Id`, `name`, `removed`) VALUES
(0, 'All Division', 0);

-- --------------------------------------------------------

--
-- Table structure for table `position`
--

CREATE TABLE `position` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `removed` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `option_a` text NOT NULL,
  `option_b` text NOT NULL,
  `option_c` text NOT NULL,
  `option_d` text NOT NULL,
  `correct_answer` char(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `question_image` varchar(255) DEFAULT NULL,
  `option_a_image` varchar(255) DEFAULT NULL,
  `option_b_image` varchar(255) DEFAULT NULL,
  `option_c_image` varchar(255) DEFAULT NULL,
  `option_d_image` varchar(255) DEFAULT NULL,
  `removed` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `course_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `created_at`, `updated_at`, `question_image`, `option_a_image`, `option_b_image`, `option_c_image`, `option_d_image`, `removed`) VALUES
(44, 174, 'dasdasdasd', 'dasdasd', 'dasdasdas', 'dasdas', 'dasdasdas', 'a', '2025-07-31 05:46:41', '2025-07-31 05:46:41', NULL, NULL, NULL, NULL, NULL, 0),
(45, 175, 'fsadfsdafdsf', 'dsfsdfds', 'dsafsdf', 'dsfdsf', 'fasdfdsf', 'c', '2025-07-31 05:47:11', '2025-07-31 05:47:11', NULL, NULL, NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `removed` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `section`
--

CREATE TABLE `section` (
  `Id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `removed` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `section`
--

INSERT INTO `section` (`Id`, `name`, `removed`) VALUES
(0, 'All Section', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `active` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `full_name`, `password`, `email`, `role`, `created_at`, `updated_at`, `active`) VALUES
(10, 'admin', 'System Administrator', '$2y$10$lub2R8JVvSaFou9VAHMWf.nB44y0aTozMkQYWlNDz2oiy1aqfe4g6', 'system_admin@toyoflex.com', 'admin', '2025-03-27 01:53:00', '2025-03-27 01:53:00', 0),
(14, '012728', 'MITCH VARGAS', '$2y$10$HFY/J5uDoYhtq6ClhTDQdO.4u4Dc/M1uMGy21K2lGAlu01kYy8bR2', 'it-staff9.ph@toyoflex.com', 'user', '2025-04-16 00:44:07', '2025-04-16 00:44:07', 0),
(15, '01059', 'RODEL JAMES DUTERTE', '$2y$10$e9mr.JyQPWNllTcFk7F0iuJ9hi/T6Xid3uW6ZK6.B3xauImJWqSoO', 'sampleemail@toyoflex.com', 'user', '2025-04-16 00:44:46', '2025-04-16 00:44:46', 0),
(17, '012077', 'SHEILA MALINGIN', '$2y$10$zGEVMhUx/jlA82R5d60WyOsvm5Sb0Dz5nDWJi2oO8Da/yG1yu0LU2', 'it-staff5.ph@toyoflex.com', 'user', '2025-04-16 00:45:32', '2025-04-16 00:45:32', 0),
(18, '013426', 'ALMAHSOL A. TABIGUE', '$2y$10$ZVWmcx0.bb7IfWAMq1Blc.y5RWIW1fUiFzpqtA61zCnoSa3YgD12y', 'it-staff10.ph@toyoflex.con', 'user', '2025-07-03 06:42:22', '2025-07-03 06:42:22', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_progress`
--

CREATE TABLE `user_progress` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `quiz_id` int(11) DEFAULT NULL,
  `score` int(11) DEFAULT 0,
  `completed` tinyint(1) DEFAULT 0,
  `user_answers` text DEFAULT NULL,
  `progress_percentage` float DEFAULT 0,
  `is_correct` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `removed` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_video_progress`
--

CREATE TABLE `user_video_progress` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `video_id` int(11) DEFAULT NULL,
  `watched` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `removed` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `course_videos`
--
ALTER TABLE `course_videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `division`
--
ALTER TABLE `division`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `position`
--
ALTER TABLE `position`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `section`
--
ALTER TABLE `section`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_progress`
--
ALTER TABLE `user_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_quiz` (`user_id`,`quiz_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `user_video_progress`
--
ALTER TABLE `user_video_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `video_id` (`video_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=176;

--
-- AUTO_INCREMENT for table `course_videos`
--
ALTER TABLE `course_videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `division`
--
ALTER TABLE `division`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `position`
--
ALTER TABLE `position`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `section`
--
ALTER TABLE `section`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `user_progress`
--
ALTER TABLE `user_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_video_progress`
--
ALTER TABLE `user_video_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `course_videos`
--
ALTER TABLE `course_videos`
  ADD CONSTRAINT `course_videos_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_progress`
--
ALTER TABLE `user_progress`
  ADD CONSTRAINT `user_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_progress_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_progress_ibfk_3` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_video_progress`
--
ALTER TABLE `user_video_progress`
  ADD CONSTRAINT `user_video_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_video_progress_ibfk_2` FOREIGN KEY (`video_id`) REFERENCES `course_videos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
