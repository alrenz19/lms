-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 26, 2025 at 01:35 AM
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
-- Table structure for table `company_groups`
--

CREATE TABLE `company_groups` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `removed` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_groups`
--

INSERT INTO `company_groups` (`id`, `name`, `removed`) VALUES
(1, 'INVENTORY/SAP', 0),
(2, 'ACCOUNTING-EXPAT', 0),
(3, 'ADMIN-E', 0),
(4, 'APS', 0),
(5, 'APS & DDC', 0),
(6, 'BIO', 0),
(7, 'BT', 0),
(8, 'CAR', 0),
(9, 'COIL', 0),
(10, 'CORE', 0),
(11, 'D COIL', 0),
(12, 'DEV-E', 0),
(13, 'DEVICE GENERAL AFFAIRS', 0),
(14, 'DEVICE-QA SECTION', 0),
(15, 'DEVICE-QC SECTION', 0),
(16, 'ENGINEERING', 0),
(17, 'ENVIRONMENT', 0),
(18, 'EQUIPMENT', 0),
(19, 'FILMECC ASSY', 0),
(20, 'GA', 0),
(21, 'GENERAL AFFAIRS', 0),
(22, 'IMPORTATION', 0),
(23, 'IMPROVEMENT', 0),
(24, 'INDUSTRIAL DEVICE', 0),
(25, 'INFRASTRUCTURE', 0),
(26, 'INVENTORY/SAP', 0),
(27, 'IT', 0),
(28, 'LABOR MANAGEMENT', 0),
(29, 'M COIL', 0),
(30, 'M CORE', 0),
(31, 'MACHINE DESIGN', 0),
(32, 'MAINTENANCE', 0),
(33, 'MATERIAL CONTROL', 0),
(34, 'MD COIL', 0),
(35, 'MD CORE', 0),
(36, 'MED-E', 0),
(37, 'MEDICAL', 0),
(38, 'MEDICAL ASSY', 0),
(39, 'MEDICAL ASSY (M2)', 0),
(40, 'MEDICAL COMPONENT', 0),
(41, 'MEDICAL-ACCOUNTING SECTION', 0),
(42, 'MEDICAL-GA SECTION', 0),
(43, 'MEDICAL-SAFETY GROUP', 0),
(44, 'MOLD MAINTENANCE', 0),
(45, 'MU', 0),
(46, 'NURSE', 0),
(47, 'NYLON COATING', 0),
(48, 'PAYROLL', 0),
(49, 'PLANNING', 0),
(50, 'PLASTIC MOLDING', 0),
(51, 'PLASTIC MOLDING PRODUCTION AREA', 0),
(52, 'PROCESS ENGINEERING', 0),
(53, 'PRODUCTION', 0),
(54, 'PRODUCTION CONTROL', 0),
(55, 'PRODUCTION ENGINEERING', 0),
(56, 'PTFE', 0),
(57, 'PURCHASING', 0),
(58, 'PURCHASING & LOGISTICS', 0),
(59, 'QA', 0),
(60, 'QA QA-AREA', 0),
(61, 'QA QC-AREA', 0),
(62, 'RECRUITMENT & TRAINING', 0),
(63, 'SAFETY', 0),
(64, 'SHIPPING', 0),
(65, 'SHIPPING CONTROL', 0),
(66, 'STERILIZATION', 0),
(67, 'STRANDING', 0),
(68, 'SUPPORT', 0),
(69, 'T-ASSY PACKING-AREA', 0),
(70, 'T-ASSY PRODUCTION AREA', 0),
(71, 'T-ASSY TUNGSTEN PACKING-AREA', 0),
(72, 'T-ASSY TUNGSTEN PRODUCTION-AREA', 0),
(73, 'T-ASSY-S', 0),
(74, 'W/D', 0),
(75, 'WAREHOUSING', 0),
(76, 'WIRE DRAWING', 0),
(77, 'WIRE ROPE', 0);

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
  `company_group` tinyint(11) NOT NULL,
  `position` tinyint(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `has_video` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `removed` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `description`, `department`, `division`, `section`, `company_group`, `position`, `group_id`, `created_by`, `has_video`, `created_at`, `updated_at`, `removed`) VALUES
(207, 'Safety and Health', 'Introduction to safety and health', 0, 0, 0, 0, 0, 0, 10, 2, '2025-08-25 23:17:59', '2025-08-25 23:17:59', 0),
(208, 'Mission and Vision', 'Company\\\'s goals', 0, 0, 0, 0, 0, 0, 10, 3, '2025-08-25 23:22:29', '2025-08-25 23:22:29', 0);

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
(121, 207, 'Safety', 'Introduction to safety', '/uploads/modules/mod_68acef27e05948.15811293_mod_68a80e6dde16e0.43244465_mod_68a801ce7c4ec2.86565938_mod_68940f7b7e5a75.36605703_JF96E7142511.pdf', 345521, '2025-08-25 23:17:59', '2025-08-25 23:17:59', 0),
(122, 207, 'Health', 'Introduction to health', '/uploads/modules/mod_68acef27e206c4.19172643_mod_68a80e6ddf52b1.47205471_mod_68a804e0a74bb5.73820018_mod_68940f7b7e5a75.36605703_JF96E7142511.pdf', 345521, '2025-08-25 23:17:59', '2025-08-25 23:17:59', 0),
(123, 208, 'Our Mission', '', '/uploads/modules/mod_68acf03551ae11.58389207_mod_68a80e6dde16e0.43244465_mod_68a801ce7c4ec2.86565938_mod_68940f7b7e5a75.36605703_JF96E7142511.pdf', 345521, '2025-08-25 23:22:29', '2025-08-25 23:22:29', 0),
(124, 208, 'Our Vision', '', '/uploads/modules/mod_68acf03552abc6.30726181_mod_68a80e6ddf52b1.47205471_mod_68a804e0a74bb5.73820018_mod_68940f7b7e5a75.36605703_JF96E7142511.pdf', 345521, '2025-08-25 23:22:29', '2025-08-25 23:22:29', 0),
(125, 208, 'Our Goal', '', '/uploads/modules/mod_68acf0355314b1.51009759_mod_68a80e9325eb74.79506166_mod_68a804e0a74bb5.73820018_mod_68940f7b7e5a75.36605703_JF96E7142511.pdf', 345521, '2025-08-25 23:22:29', '2025-08-25 23:22:29', 0);

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
(0, 'All Department', 0),
(2, 'MEDICAL', 0),
(3, 'DEVICE', 0),
(4, 'ADMINISTRATION', 0);

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
(0, 'All Division', 0),
(2, 'ACCOUNTING-EXPAT', 0),
(3, 'ADMIN-EXPAT', 0),
(4, 'DEVICE-EXPAT', 0),
(5, 'DEVICE-GENERAL AFFAIRS', 0),
(6, 'DEVICE-HUMAN RESOURCE', 0),
(7, 'GENERAL AFFAIRS', 0),
(8, 'HUMAN RESOURCE', 0),
(9, 'MAINTENANCE', 0),
(10, 'MEDICAL-ACCOUNTING', 0),
(11, 'MEDICAL-EXPAT', 0),
(12, 'MEDICAL-GENERAL AFFAIRS', 0),
(13, 'MEDICAL-HUMAN RESOURCE', 0),
(14, 'M-PRO', 0),
(15, 'PRODUCTION', 0),
(16, 'PRODUCTION CONTROL', 0),
(17, 'PRODUCTION ENGINEERING', 0),
(18, 'PURCHASING & LOGISTICS', 0),
(19, 'QA', 0),
(20, 'QUALITY ASSURANCE', 0);

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
(82, 207, 'Test ', '1', '2', '3', '4', 'd', '2025-08-25 23:18:18', '2025-08-25 23:18:18', NULL, NULL, NULL, NULL, NULL, 0),
(83, 208, 'Test 1', '1', '2', '3', '4', 'b', '2025-08-25 23:23:06', '2025-08-25 23:23:06', NULL, NULL, NULL, NULL, NULL, 0),
(84, 208, 'Test 2', '1', '2', '3', '4', 'c', '2025-08-25 23:23:06', '2025-08-25 23:23:06', NULL, NULL, NULL, NULL, NULL, 0),
(85, 208, 'Test 3', '1', '2', '3', '4', 'b', '2025-08-25 23:23:06', '2025-08-25 23:23:06', NULL, NULL, NULL, NULL, NULL, 0);

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
(0, 'All Section', 0),
(2, 'ACCOUNTING', 0),
(3, 'ACCOUNTING-EXPAT', 0),
(4, 'ADMIN-E', 0),
(5, 'APS', 0),
(6, 'COIL', 0),
(7, 'CORE', 0),
(8, 'DEV-E', 0),
(9, 'ENVIRONMENT', 0),
(10, 'EQUIPMENT', 0),
(11, 'GENERAL AFFAIRS', 0),
(12, 'INDUSTRIAL DEVICE', 0),
(13, 'INFRASTRUCTURE', 0),
(14, 'INVENTORY/SAP', 0),
(15, 'IT', 0),
(16, 'LABOR MANAGEMENT', 0),
(17, 'LOGISTICS', 0),
(18, 'MACHINE DESIGN', 0),
(19, 'MAINTENANCE', 0),
(20, 'MED-E', 0),
(21, 'MEDICAL COMPONENT', 0),
(22, 'MEDICAL PRODUCTION', 0),
(23, 'NURSE', 0),
(24, 'PAYROLL', 0),
(25, 'PLASTIC MOLDING', 0),
(26, 'PROCESS ENGINEERING', 0),
(27, 'PRODUCTION', 0),
(28, 'PRODUCTION CONTROL', 0),
(29, 'PRODUCTION ENGINEERING', 0),
(30, 'PURCHASING', 0),
(31, 'PURCHASING & LOGISTICS', 0),
(32, 'QA', 0),
(33, 'QUALITY ASSURANCE', 0),
(34, 'QUALITY CONTROL', 0),
(35, 'RECRUITMENT & TRAINING', 0),
(36, 'SAFETY', 0),
(37, 'SUPPORT', 0),
(38, 'T-ASSY', 0),
(39, 'UTILITY', 0),
(40, 'WAREHOUSING', 0),
(41, 'WIRE ROPE', 0);

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
  `department` tinyint(11) NOT NULL,
  `division` tinyint(11) NOT NULL,
  `position` tinyint(11) NOT NULL,
  `section` tinyint(11) NOT NULL,
  `company_group` tinyint(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `active` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `full_name`, `password`, `email`, `role`, `department`, `division`, `position`, `section`, `company_group`, `created_at`, `updated_at`, `active`) VALUES
(10, 'admin', 'System Administrator', '$2y$10$lub2R8JVvSaFou9VAHMWf.nB44y0aTozMkQYWlNDz2oiy1aqfe4g6', 'system_admin@toyoflex.com', 'admin', 0, 0, 0, 0, 0, '2025-03-27 01:53:00', '2025-03-27 01:53:00', 0),
(14, '012728', 'MITCH VARGAS', '$2y$10$HFY/J5uDoYhtq6ClhTDQdO.4u4Dc/M1uMGy21K2lGAlu01kYy8bR2', 'it-staff9.ph@toyoflex.com', 'user', 0, 0, 0, 0, 0, '2025-04-16 00:44:07', '2025-04-16 00:44:07', 0),
(15, '01059', 'RODEL JAMES DUTERTE', '$2y$10$e9mr.JyQPWNllTcFk7F0iuJ9hi/T6Xid3uW6ZK6.B3xauImJWqSoO', 'sampleemail@toyoflex.com', 'user', 0, 0, 0, 0, 0, '2025-04-16 00:44:46', '2025-04-16 00:44:46', 0),
(17, '012077', 'SHEILA MALINGIN', '$2y$10$zGEVMhUx/jlA82R5d60WyOsvm5Sb0Dz5nDWJi2oO8Da/yG1yu0LU2', 'it-staff5.ph@toyoflex.com', 'user', 0, 0, 0, 0, 0, '2025-04-16 00:45:32', '2025-04-16 00:45:32', 0),
(18, '013426', 'ALMAHSOL A. TABIGUE', '$2y$10$ZVWmcx0.bb7IfWAMq1Blc.y5RWIW1fUiFzpqtA61zCnoSa3YgD12y', 'it-staff10.ph@toyoflex.con', 'user', 0, 0, 0, 0, 0, '2025-07-03 06:42:22', '2025-07-03 06:42:22', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_progress`
--

CREATE TABLE `user_progress` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `score` int(11) DEFAULT 0,
  `total_score` int(100) NOT NULL,
  `completed` tinyint(1) DEFAULT 0,
  `user_answers` text DEFAULT NULL,
  `progress_percentage` float DEFAULT 0,
  `is_correct` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `removed` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_progress`
--

INSERT INTO `user_progress` (`id`, `user_id`, `course_id`, `score`, `total_score`, `completed`, `user_answers`, `progress_percentage`, `is_correct`, `created_at`, `updated_at`, `removed`) VALUES
(36, 14, 207, 0, 1, 1, '{\"82\":\"A\"}', 0, 0, '2025-08-25 23:31:00', '2025-08-25 23:31:00', 0);

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
-- Dumping data for table `user_video_progress`
--

INSERT INTO `user_video_progress` (`id`, `user_id`, `video_id`, `watched`, `created_at`, `updated_at`, `removed`) VALUES
(66, 14, 121, 1, '2025-08-25 23:20:48', '2025-08-25 23:20:48', 0),
(67, 14, 122, 1, '2025-08-25 23:30:54', '2025-08-25 23:30:54', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `company_groups`
--
ALTER TABLE `company_groups`
  ADD PRIMARY KEY (`id`);

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `questions_ibfk_1` (`course_id`);

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
  ADD UNIQUE KEY `unique_user_quiz` (`user_id`,`course_id`) USING BTREE,
  ADD KEY `course_id` (`course_id`);

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
-- AUTO_INCREMENT for table `company_groups`
--
ALTER TABLE `company_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=209;

--
-- AUTO_INCREMENT for table `course_videos`
--
ALTER TABLE `course_videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `division`
--
ALTER TABLE `division`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `position`
--
ALTER TABLE `position`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `section`
--
ALTER TABLE `section`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `user_progress`
--
ALTER TABLE `user_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `user_video_progress`
--
ALTER TABLE `user_video_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

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
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `user_progress_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

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
