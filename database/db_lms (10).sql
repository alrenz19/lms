-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 16, 2025 at 09:59 AM
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
  `removed` tinyint(1) NOT NULL DEFAULT 0
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
  `department` int(10) DEFAULT NULL,
  `division` int(10) DEFAULT NULL,
  `section` int(10) DEFAULT NULL,
  `company_group` tinyint(11) DEFAULT NULL,
  `position` tinyint(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `has_video` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_final` tinyint(1) NOT NULL DEFAULT 0,
  `removed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `description`, `department`, `division`, `section`, `company_group`, `position`, `group_id`, `created_by`, `has_video`, `created_at`, `updated_at`, `is_final`, `removed`) VALUES
(293, 'Safety and Health', 'Safety and health refer to the practices and policies designed to prevent accidents, injuries, and illnesses in various environments, ensuring the well-being of individuals.', NULL, NULL, NULL, NULL, NULL, NULL, 10, 2, '2025-09-22 03:09:02', '2025-09-22 05:17:18', 0, 1),
(294, 'TEst', 'test', NULL, NULL, NULL, NULL, NULL, NULL, 10, 1, '2025-09-22 03:19:04', '2025-09-22 03:22:58', 0, 1),
(295, 'test', 'test', NULL, NULL, NULL, NULL, NULL, NULL, 10, 1, '2025-09-22 03:19:34', '2025-09-22 03:22:56', 0, 1),
(296, 'test', 'test', NULL, NULL, NULL, NULL, NULL, NULL, 10, 1, '2025-09-22 03:20:17', '2025-09-22 03:22:54', 0, 1),
(297, 'sdasdas', 'dasdas', NULL, NULL, NULL, NULL, NULL, NULL, 10, 1, '2025-09-22 03:24:31', '2025-09-22 05:08:51', 0, 1),
(298, 'dasdasdas', 'dasdasd', NULL, NULL, NULL, NULL, NULL, NULL, 10, 1, '2025-09-22 04:34:01', '2025-09-22 05:08:49', 0, 1),
(299, 'test', 'test', NULL, NULL, NULL, NULL, NULL, NULL, 10, 7, '2025-09-22 04:39:41', '2025-09-22 05:08:47', 0, 1),
(300, 'dasdasd', 'asdasdsa', NULL, NULL, NULL, NULL, NULL, NULL, 10, 1, '2025-09-22 04:41:51', '2025-09-22 05:08:43', 0, 1),
(301, 'cxcccxzczx', 'cxzczx', NULL, NULL, NULL, NULL, NULL, NULL, 10, 1, '2025-09-22 05:15:30', '2025-09-22 05:17:11', 0, 1),
(302, 'cxcccxzczx', 'cxzczx', NULL, NULL, NULL, NULL, NULL, NULL, 10, 1, '2025-09-22 05:15:31', '2025-09-22 05:17:09', 0, 1),
(303, 'cxcxcxcx', 'cxxc', NULL, NULL, NULL, NULL, NULL, NULL, 10, 1, '2025-09-22 05:17:42', '2025-09-22 05:18:25', 0, 1),
(304, 'Safety and Health', 'Safety and health refer to the practices and policies designed to prevent accidents, injuries, and illnesses in various environments, ensuring the well-being of individuals.', 2, 7, 15, NULL, NULL, NULL, 32, 1, '2025-09-22 05:33:30', '2025-10-16 07:41:55', 0, 0),
(305, 'Hazardous Waste Management', 'Training for the proper disposal of hazardous wastes', NULL, NULL, NULL, NULL, NULL, NULL, 30, 0, '2025-09-22 05:33:53', '2025-09-22 05:33:53', 0, 0),
(306, 'Wastewater Management', 'To establish a standardized procedure for testing wastewater parameters, ensuring consistency, accuracy, and reliability in sampling, analysis, and report.', NULL, NULL, NULL, NULL, NULL, NULL, 28, 1, '2025-09-22 05:34:47', '2025-09-23 06:32:31', 0, 1),
(307, 'Wastewater Management', 'To establish a standardized procedure for testing wastewater parameters, ensuring consistency, accuracy, and reliability in sampling, analysis, and report.', NULL, NULL, NULL, NULL, NULL, NULL, 28, 1, '2025-09-22 05:34:51', '2025-09-22 05:35:15', 0, 1),
(309, '8 HOURS MANDATORY', 'DOLE Mandated course intended for all employees', NULL, NULL, NULL, NULL, NULL, NULL, 23, 1, '2025-09-22 05:40:30', '2025-09-23 04:55:28', 0, 1),
(310, 'Proper Handling of Hazardous Wastes', 'This course is to provide knowledge on the proper handling of hazardous wastes from generation to endorsement to ensure hazardous wastes are properly classified and handled properly and prevent any hazards that may arise due to inappropriate or mishandling of such wastes.', NULL, NULL, NULL, NULL, NULL, NULL, 10, 1, '2025-09-23 05:31:00', '2025-09-23 05:31:00', 0, 0),
(311, 'Wastewater Mgt', 'To establish a standardized procedure for testing wastewater parameters, ensuring consistency, accuracy, and reliability in sampling, analysis, and report.', NULL, NULL, NULL, NULL, NULL, NULL, 28, 1, '2025-09-23 05:53:50', '2025-09-23 05:53:50', 0, 0),
(312, 'WASTEWATER', 'To establish a standardized procedure for testing wastewater parameters, ensuring consistency, accuracy, and reliability in sampling, analysis, and report.', NULL, NULL, NULL, NULL, NULL, NULL, 28, 2, '2025-09-23 07:00:53', '2025-09-23 07:00:53', 0, 0),
(313, 'SOP for Testing WW', 'To establish a standardized procedure for testing wastewater parameters, ensuring consistency, accuracy, and reliability in sampling, analysis, and report.', NULL, NULL, NULL, NULL, NULL, NULL, 28, 1, '2025-09-23 07:13:35', '2025-09-23 07:13:36', 0, 0),
(314, 'Test', 'Testing', NULL, NULL, NULL, NULL, NULL, NULL, 46, 1, '2025-10-08 01:57:53', '2025-10-08 02:10:23', 0, 0),
(315, 'Test2', 'TEst', NULL, NULL, NULL, NULL, NULL, NULL, 46, 2, '2025-10-09 23:41:26', '2025-10-09 23:41:26', 0, 0),
(316, 'test3', 'test', NULL, NULL, NULL, NULL, NULL, NULL, 46, 2, '2025-10-09 23:44:01', '2025-10-09 23:44:01', 0, 0),
(317, 'test4', 'rwsta', NULL, NULL, NULL, NULL, NULL, NULL, 46, 2, '2025-10-09 23:46:37', '2025-10-09 23:46:37', 0, 0),
(318, 'test', 'test', NULL, NULL, NULL, NULL, NULL, NULL, 32, 2, '2025-10-10 00:00:49', '2025-10-10 00:00:49', 0, 0),
(319, 'test', 'test', NULL, NULL, NULL, NULL, NULL, NULL, 32, 1, '2025-10-10 00:11:30', '2025-10-10 00:11:30', 0, 0),
(320, '22', '22', NULL, NULL, NULL, NULL, NULL, NULL, 32, 2, '2025-10-10 00:39:43', '2025-10-10 00:39:43', 0, 0),
(321, 'dukaaa', 'dukaaaa', 3, 2, 2, NULL, NULL, NULL, 32, 1, '2025-10-10 05:36:25', '2025-10-10 05:37:26', 0, 0),
(322, '1', '123', NULL, NULL, NULL, NULL, NULL, NULL, 46, 2, '2025-10-13 01:25:13', '2025-10-13 01:25:13', 0, 0),
(323, '2', 'rani', NULL, NULL, NULL, NULL, NULL, NULL, 46, 2, '2025-10-13 02:04:29', '2025-10-13 02:04:29', 0, 0),
(324, '3', 'jj', NULL, NULL, NULL, NULL, NULL, NULL, 46, 2, '2025-10-13 02:41:37', '2025-10-13 02:41:37', 0, 0),
(325, 'Retake quiz', '22', NULL, NULL, NULL, NULL, NULL, NULL, 46, 1, '2025-10-13 08:42:33', '2025-10-13 08:42:33', 0, 0),
(326, 'retake quiz 2', 'asdas', NULL, NULL, NULL, NULL, NULL, NULL, 46, 1, '2025-10-13 08:58:24', '2025-10-13 08:58:24', 0, 0),
(327, 'kani kay retake quiz database', 'adgeag', NULL, NULL, NULL, NULL, NULL, NULL, 46, 1, '2025-10-15 06:23:59', '2025-10-15 06:23:59', 0, 0),
(328, 'kani kay retake quiz database 2', 'asdasd', NULL, NULL, NULL, NULL, NULL, NULL, 46, 1, '2025-10-15 06:52:41', '2025-10-15 06:52:41', 0, 0),
(329, 'kani kay retake quiz database 3', 'asdasdas', NULL, NULL, NULL, NULL, NULL, NULL, 46, 1, '2025-10-15 07:06:19', '2025-10-15 07:06:19', 0, 0),
(330, 'kani kay retake quiz database 4', 'asdasdas', NULL, NULL, NULL, NULL, NULL, NULL, 46, 1, '2025-10-15 07:31:40', '2025-10-15 07:31:40', 0, 0),
(331, 'kani kay retake quiz database 5', 'adasdas', NULL, NULL, NULL, NULL, NULL, NULL, 46, 1, '2025-10-15 08:36:21', '2025-10-15 08:36:21', 0, 0),
(332, 'kani kay retake quiz database 6', 'gsdfsd', NULL, NULL, NULL, NULL, NULL, NULL, 46, 1, '2025-10-15 08:49:25', '2025-10-15 08:49:25', 0, 0),
(333, 'kani kay retake quiz database 7', 'sdfsdfs', NULL, NULL, NULL, NULL, NULL, NULL, 46, 1, '2025-10-15 08:54:12', '2025-10-15 08:54:12', 0, 0),
(334, 'kani kay retake quiz database 8 LASSSTTT NANII', 'fafasdas', NULL, NULL, NULL, NULL, NULL, NULL, 46, 1, '2025-10-15 09:07:01', '2025-10-15 09:07:01', 0, 0),
(335, 'FINAL!!!', 'fasfasfasfas', NULL, NULL, NULL, NULL, NULL, NULL, 46, 1, '2025-10-15 09:41:36', '2025-10-15 09:41:36', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `course_collab`
--

CREATE TABLE `course_collab` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `removed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_collab`
--

INSERT INTO `course_collab` (`id`, `admin_id`, `course_id`, `created_at`, `updated_at`, `removed`) VALUES
(1, 46, 318, '2025-10-16 06:22:36', '2025-10-16 06:22:36', 0),
(2, 46, 304, '2025-10-16 06:48:45', '2025-10-16 06:48:45', 0);

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
  `removed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_videos`
--

INSERT INTO `course_videos` (`id`, `course_id`, `module_name`, `module_description`, `video_url`, `file_size`, `created_at`, `updated_at`, `removed`) VALUES
(268, 293, 'Safety', 'Introduction to safety practices', '/volume1/video/mod_68d0bdcee9dee7.01081157_789.mp4', 14809155, '2025-09-22 03:09:02', '2025-09-22 05:17:18', 1),
(269, 293, 'Health', 'Introduction to health risks', '/volume1/video/mod_68d0bdceec8360.85993740_mod_68a80dfd2a8536.17794567_mod_68a804ef73d9b1.07570332_mod_68a804e0a74bb5.73820018_mod_68940f7b7e5a75.36605703_JF96E7142511.pdf', 345521, '2025-09-22 03:09:02', '2025-09-22 05:17:18', 1),
(270, 294, 'test', 'test', '/volume1/video/mod_68d0c0286a11b5.48959698_screen_recording_1758511125322.webm', 343148, '2025-09-22 03:19:04', '2025-09-22 03:22:58', 1),
(271, 295, 'test', 'test', '/volume1/video/mod_68d0c046ad0b53.15231767_mod_68a80e6ddf52b1.47205471_mod_68a804e0a74bb5.73820018_mod_68940f7b7e5a75.36605703_JF96E7142511.pdf', 345521, '2025-09-22 03:19:34', '2025-09-22 03:22:56', 1),
(272, 296, 'asd', 'asd', '/volume1/video/mod_68d0c07147c9f6.50354585_mod_68a80dfd2a8536.17794567_mod_68a804ef73d9b1.07570332_mod_68a804e0a74bb5.73820018_mod_68940f7b7e5a75.36605703_JF96E7142511.pdf', 345521, '2025-09-22 03:20:17', '2025-09-22 03:22:54', 1),
(273, 297, 'dasdas', 'dasd', '/volume1/video/mod_68d0c16fe189a5.86556054_Modified_LMS_User_Guide.pdf', 586936, '2025-09-22 03:24:32', '2025-09-22 05:08:51', 1),
(274, 298, 'dasdasd', 'asdasd', '/volume1/video/mod_68d0d1b9e9e8b9.66578687_screen_recording_1758515636796.webm', 110865, '2025-09-22 04:34:02', '2025-09-22 05:08:49', 1),
(275, 299, '1', '', '/volume1/video/mod_68d0d30d383687.24367273_456.mp4', 14809155, '2025-09-22 04:39:41', '2025-09-22 05:08:47', 1),
(276, 299, '2', '', '/volume1/video/mod_68d0d30d96c4c6.14482683_789.mp4', 14809155, '2025-09-22 04:39:41', '2025-09-22 05:08:47', 1),
(277, 299, '3', '', '/volume1/video/mod_68d0d30dda9d97.40778787_mod_68a80dfd2a8536.17794567_mod_68a804ef73d9b1.07570332_mod_68a804e0a74bb5.73820018_mod_68940f7b7e5a75.36605703_JF96E7142511.pdf', 345521, '2025-09-22 04:39:41', '2025-09-22 05:08:47', 1),
(278, 299, '4', '', '/volume1/video/mod_68d0d30ddad636.99811755_mod_68a80e6dde16e0.43244465_mod_68a801ce7c4ec2.86565938_mod_68940f7b7e5a75.36605703_JF96E7142511.pdf', 345521, '2025-09-22 04:39:41', '2025-09-22 05:08:47', 1),
(279, 299, '5', '', '/volume1/video/mod_68d0d30ddb05e8.12402166_mod_68a80e6ddf52b1.47205471_mod_68a804e0a74bb5.73820018_mod_68940f7b7e5a75.36605703_JF96E7142511.pdf', 345521, '2025-09-22 04:39:41', '2025-09-22 05:08:47', 1),
(280, 299, '6', '', '/volume1/video/mod_68d0d30ddb3735.32728236_mod_68a80e9325eb74.79506166_mod_68a804e0a74bb5.73820018_mod_68940f7b7e5a75.36605703_JF96E7142511.pdf', 345521, '2025-09-22 04:39:41', '2025-09-22 05:08:47', 1),
(281, 299, '7', '', '/volume1/video/mod_68d0d30ddb6710.53113343_mod_68a80e93251408.73160174_mod_68a801ce7c4ec2.86565938_mod_68940f7b7e5a75.36605703_JF96E7142511.pdf', 345521, '2025-09-22 04:39:41', '2025-09-22 05:08:47', 1),
(282, 300, 'dasdas', 'dasdas', '/volume1/video/mod_68d0d38fb35ec7.56168103_Modified_LMS_User_Guide.pdf', 586936, '2025-09-22 04:41:52', '2025-09-22 05:08:43', 1),
(283, 301, 'cxzczxczxc', 'cxzczx', '/volume1/video/mod_68d0db728c6a88.39558508_Modified_LMS_User_Guide.pdf', 586936, '2025-09-22 05:15:31', '2025-09-22 05:17:11', 1),
(284, 302, 'cxzczxczxc', 'cxzczx', '/volume1/video/mod_68d0db73c29444.60361209_Modified_LMS_User_Guide.pdf', 586936, '2025-09-22 05:15:31', '2025-09-22 05:17:09', 1),
(285, 303, 'dfd', 'fdfd', '/volume1/video/mod_68d0dbf6366682.79514468_Modified_LMS_User_Guide.pdf', 586936, '2025-09-22 05:17:42', '2025-09-22 05:18:25', 1),
(286, 304, 'Health', '', '/volume1/video/mod_68d0dfaa858246.53511084_mod_68a80dfd2a8536.17794567_mod_68a804ef73d9b1.07570332_mod_68a804e0a74bb5.73820018_mod_68940f7b7e5a75.36605703_JF96E7142511.pdf', 345521, '2025-09-22 05:33:30', '2025-09-24 05:58:13', 1),
(287, 304, 'Recording', '', '/volume1/video/mod_68d0dfaae884d7.23145483_screen_recording_1758519149566.webm', 420539, '2025-09-22 05:33:30', '2025-09-24 05:58:19', 1),
(288, 306, 'SOP for Testing Wastewater Management', 'To establish a standardized procedure for testing wastewater parameters, ensuring consistency, accuracy, and reliability in sampling, analysis, and report.', '/volume1/video/mod_68d0dff75affe9.14025508_TMO-G0073E_VER.1.00_SOP_FOR_TESTING_WASTEWATER_PARAMETERS.pdf', 1456781, '2025-09-22 05:34:47', '2025-09-23 06:32:31', 1),
(289, 307, 'SOP for Testing Wastewater Management', 'To establish a standardized procedure for testing wastewater parameters, ensuring consistency, accuracy, and reliability in sampling, analysis, and report.', '/volume1/video/mod_68d0dffbc19d71.55672684_TMO-G0073E_VER.1.00_SOP_FOR_TESTING_WASTEWATER_PARAMETERS.pdf', 1456781, '2025-09-22 05:34:51', '2025-09-22 05:35:15', 1),
(290, 304, 'Add-on', '', '/volume1/video/mod_68d0e11d1fadb2.15397432_mod_68a80e9325eb74.79506166_mod_68a804e0a74bb5.73820018_mod_68940f7b7e5a75.36605703_JF96E7142511.pdf', 345521, '2025-09-22 05:39:41', '2025-10-08 05:07:38', 1),
(291, 309, 'Safety Orientation', 'Accident INvestigation', '/volume1/video/mod_68d0e2783407c1.45901153_What_Causes_Accidents_-_Safety_Training_Video_-_Preventing_Accidents_u0026_Injuries.mp4', 9448072, '2025-09-22 05:45:28', '2025-09-23 04:55:28', 1),
(292, 309, 'Accident Investigation', 'Accident Investigation', '/volume1/video/mod_68d0e54b579347.95967515_ACCIDENT_INVESTIGATION_POLICY_V2.pdf', 2302589, '2025-09-22 05:57:31', '2025-09-23 04:55:28', 1),
(293, 310, 'Hazardous Wastes', '', '/volume1/video/mod_68d23094a5fef8.97769723_Service_Invoice.pdf', 185267, '2025-09-23 05:31:00', '2025-09-23 05:31:00', 0),
(294, 311, 'SOP for Testing Wastewater Management', 'To establish a standardized procedure for testing wastewater parameters, ensuring consistency, accuracy, and reliability in sampling, analysis, and report.', '/volume1/video/mod_68d235ee6b2c80.02756266_TMO-G0073E_VER.1.00_SOP_FOR_TESTING_WASTEWATER_PARAMETERS.pdf', 1456781, '2025-09-23 05:53:50', '2025-09-23 05:53:50', 0),
(295, 312, 'SOP for Testing Wastewater Management', 'To establish a standardized procedure for testing wastewater parameters, ensuring consistency, accuracy, and reliability in sampling, analysis, and report.', '/volume1/video/mod_68d245a516af41.94371162_TMO-G0073E_VER.1.00_SOP_FOR_TESTING_WASTEWATER_PARAMETERS.pdf', 1456781, '2025-09-23 07:00:53', '2025-09-23 07:10:31', 1),
(296, 312, 'Recording 1', 'Trial', '/volume1/video/mod_68d245a5172404.05863090_screen_recording_1758610841154.webm', 2666495, '2025-09-23 07:00:53', '2025-09-23 07:00:53', 0),
(297, 313, 'Recording 2', 'trial 2', '/volume1/video/mod_68d2489fb6eb38.89141353_screen_recording_1758611574090.webm', 5597469, '2025-09-23 07:13:36', '2025-09-23 07:13:36', 0),
(298, 304, 'test', '', '/volume1/video/mod_68d38716b46d86.30895565_90.mp4', 14809155, '2025-09-24 05:52:22', '2025-10-08 05:28:45', 1),
(299, 314, 'Hatdog', '', '/volume1/video/mod_68e5c521f1f022.38470751_DataFlow.pdf', 81477, '2025-10-08 01:57:53', '2025-10-08 01:57:53', 0),
(300, 314, 'ungo', '', '/volume1/video/mod_68e5c521f2b5f8.70231369_screen_recording_1759888664481.webm', 304767, '2025-10-08 01:57:53', '2025-10-08 02:09:46', 1),
(301, 314, 'ungo2', '', '/volume1/video/mod_68e5c80fc52cd2.62150715_screen_recording_1759889413678.mp4', 120471, '2025-10-08 02:10:23', '2025-10-08 02:10:23', 0),
(302, 304, 'hatdoggo', '', '/volume1/video/mod_68e5f6c3254a81.43869506_screen_recording_1759901347721.mp4', 37072, '2025-10-08 05:29:39', '2025-10-08 05:56:45', 1),
(303, 304, 'owshi', '', '/volume1/video/mod_68e5f6c325fbe2.37623528_DataFlow.pdf', 81477, '2025-10-08 05:29:39', '2025-10-08 05:56:42', 1),
(304, 304, 'hattt', '', '/volume1/video/mod_68e5fdbd2098c0.43970740_DataFlow.pdf', 81477, '2025-10-08 05:59:25', '2025-10-08 06:01:25', 1),
(305, 304, 'dogggg', '', '/volume1/video/mod_68e5fdbd215823.26481352_screen_recording_1759903159305.mp4', 129316, '2025-10-08 05:59:25', '2025-10-08 06:01:23', 1),
(306, 304, 'hat', '', '/volume1/video/mod_68e5fe4eb1cd27.49994521_screen_recording_1759903298325.mp4', 80983, '2025-10-08 06:01:50', '2025-10-08 06:25:38', 1),
(307, 304, 'dog', '', '/volume1/video/mod_68e5fe4eb26877.43435834_DataFlow.pdf', 81477, '2025-10-08 06:01:50', '2025-10-08 06:25:36', 1),
(308, 304, 'weeee', '', '/volume1/video/mod_68e603f1e2a537.39346630_DataFlow.pdf', 81477, '2025-10-08 06:25:53', '2025-10-08 06:28:08', 1),
(309, 304, 'test', '', '/volume1/video/mod_68e6043568a6e4.28079265_sample.pdf', 18810, '2025-10-08 06:27:01', '2025-10-08 06:28:10', 1),
(310, 304, 'test', '', '/volume1/video/mod_68e6048a51a5b1.14072040_sample.pdf', 18810, '2025-10-08 06:28:26', '2025-10-08 06:28:26', 0),
(311, 304, 'a,mbot', '', '/volume1/video/mod_68e6048a5240e1.32251283_DataFlow.pdf', 81477, '2025-10-08 06:28:26', '2025-10-08 06:28:26', 0),
(312, 315, 'test1', 'testt', '/volume1/video/mod_68e848260b2410.22391572_sample.pdf', 18810, '2025-10-09 23:41:26', '2025-10-09 23:41:26', 0),
(313, 315, 'test2', 'intro', '/volume1/video/mod_68e848260bd596.15868771_FM-Module-5-Safety-and-Health-at-Work-1__1_.pdf', 2237397, '2025-10-09 23:41:26', '2025-10-09 23:41:26', 0),
(314, 316, '12', '', '/volume1/video/mod_68e848c1a41a44.00820889_sample.pdf', 18810, '2025-10-09 23:44:01', '2025-10-09 23:44:01', 0),
(315, 316, '34', '', '/volume1/video/mod_68e848c1a4bbb9.32905879_DataFlow.pdf', 81477, '2025-10-09 23:44:01', '2025-10-09 23:44:01', 0),
(316, 317, 'test', '', '/volume1/video/mod_68e8495d247219.29845179_sample.pdf', 18810, '2025-10-09 23:46:37', '2025-10-09 23:46:37', 0),
(317, 317, 'etete', '', '/volume1/video/mod_68e8495d254786.02176193_DataFlow.pdf', 81477, '2025-10-09 23:46:37', '2025-10-09 23:46:37', 0),
(318, 318, 'test', '', '/volume1/video/mod_68e84cb1dd5783.98901156_sample.pdf', 18810, '2025-10-10 00:00:49', '2025-10-10 00:00:49', 0),
(319, 318, 'test', '', '/volume1/video/mod_68e84cb1de25e8.48141143_DataFlow.pdf', 81477, '2025-10-10 00:00:49', '2025-10-10 00:00:49', 0),
(320, 319, 'TEST', '', '/volume1/video/mod_68e84f32342792.61821398_FM-Module-5-Safety-and-Health-at-Work-1.pdf', 2237397, '2025-10-10 00:11:30', '2025-10-10 00:11:30', 0),
(321, 320, '1', '', '/volume1/video/mod_68e855cf2a5a87.84223844_sample.pdf', 18810, '2025-10-10 00:39:43', '2025-10-10 00:39:43', 0),
(322, 320, '2', '', '/volume1/video/mod_68e855cf2b4811.88618387_screen_recording_1760056763356.mp4', 101900, '2025-10-10 00:39:43', '2025-10-10 00:39:43', 0),
(323, 321, 'haaaa', '', '/volume1/video/mod_68e89b592bc963.90640704_sample.pdf', 18810, '2025-10-10 05:36:25', '2025-10-10 05:36:25', 0),
(324, 322, 'test', '', '/volume1/video/mod_68ec54f9c94256.02047996_sample.pdf', 18810, '2025-10-13 01:25:13', '2025-10-13 01:25:13', 0),
(325, 322, 'test1', '', '/volume1/video/mod_68ec54f9ca4d28.47099999_Modified_LMS_User_Guide.pdf', 586936, '2025-10-13 01:25:13', '2025-10-13 01:25:13', 0),
(326, 323, 'test', '', '/volume1/video/mod_68ec5e2d67ad54.21249306_sample.pdf', 18810, '2025-10-13 02:04:29', '2025-10-13 02:04:29', 0),
(327, 323, 'test', '', '/volume1/video/mod_68ec5e2d689298.33311439_DataFlow.pdf', 81477, '2025-10-13 02:04:29', '2025-10-13 02:04:29', 0),
(328, 324, '212', '', '/volume1/video/mod_68ec66e10ec761.66773601_sample.pdf', 18810, '2025-10-13 02:41:37', '2025-10-13 02:41:37', 0),
(329, 324, '64646', '', '/volume1/video/mod_68ec66e1102f63.43814381_screen_recording_1760323284541.mp4', 397440, '2025-10-13 02:41:37', '2025-10-13 02:41:37', 0),
(330, 325, 'test', '', '/volume1/video/mod_68ecbb793d8849.05481191_sample.pdf', 18810, '2025-10-13 08:42:33', '2025-10-13 08:42:33', 0),
(331, 326, 'sdfsd', '', '/volume1/video/mod_68ecbf30d31598.57347288_sample.pdf', 18810, '2025-10-13 08:58:24', '2025-10-13 08:58:24', 0),
(332, 327, 'test', '', '/volume1/video/mod_68ef3dffa29d63.08839753_sample.pdf', 18810, '2025-10-15 06:23:59', '2025-10-15 06:23:59', 0),
(333, 328, 'adsdas', '', '/volume1/video/mod_68ef44b942e093.60748334_sample.pdf', 18810, '2025-10-15 06:52:41', '2025-10-15 06:52:41', 0),
(334, 329, 'asdasdasc', '', '/volume1/video/mod_68ef47eb26c063.15668541_sample.pdf', 18810, '2025-10-15 07:06:19', '2025-10-15 07:06:19', 0),
(335, 330, 'asdasdasxz', '', '/volume1/video/mod_68ef4ddcf04da2.51637568_sample.pdf', 18810, '2025-10-15 07:31:40', '2025-10-15 07:31:40', 0),
(336, 331, 'asdas', '', '/volume1/video/mod_68ef5d056b4a92.36353253_sample.pdf', 18810, '2025-10-15 08:36:21', '2025-10-15 08:36:21', 0),
(337, 332, 'sdfsdfs', '', '/volume1/video/mod_68ef6015baee51.50006389_sample.pdf', 18810, '2025-10-15 08:49:25', '2025-10-15 08:49:25', 0),
(338, 333, 'sdfsdf', '', '/volume1/video/mod_68ef6134a21e52.14425817_sample.pdf', 18810, '2025-10-15 08:54:12', '2025-10-15 08:54:12', 0),
(339, 334, 'asdasdas', '', '/volume1/video/mod_68ef643594beb6.82660510_sample.pdf', 18810, '2025-10-15 09:07:01', '2025-10-15 09:07:01', 0),
(340, 335, '5412', '', '/volume1/video/mod_68ef6c5001bc23.99330491_sample.pdf', 18810, '2025-10-15 09:41:36', '2025-10-15 09:41:36', 0),
(341, 304, 'colab', '', '/volume1/video/mod_68f0a1c3167207.61226280_screen_recording_1760600496629.mp4', 158530, '2025-10-16 07:41:55', '2025-10-16 07:41:55', 0);

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `id` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `removed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`id`, `Name`, `removed`) VALUES
(1, 'All Department', 0),
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
  `removed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `division`
--

INSERT INTO `division` (`Id`, `name`, `removed`) VALUES
(1, 'All Division', 0),
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
  `removed` tinyint(1) NOT NULL DEFAULT 0
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
  `removed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `course_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `created_at`, `updated_at`, `question_image`, `option_a_image`, `option_b_image`, `option_c_image`, `option_d_image`, `removed`) VALUES
(109, 293, 'What is the purpose of this course?', 'To understand the importance of risk', 'All of the above', 'To maintain our knowledge on safety and health course', 'To be aware all the time', 'b', '2025-09-22 03:17:26', '2025-09-22 05:17:18', NULL, NULL, NULL, NULL, NULL, 1),
(110, 303, 'dfsdf', 'fdsfds', 'fdsf', 'fdsfdsfdsfds', 'fdsfds', 'd', '2025-09-22 05:18:16', '2025-09-22 05:18:25', NULL, NULL, NULL, NULL, NULL, 1),
(111, 310, '1st question', 'answer 1', 'answer 2', 'answer 3', 'answer 4', 'a', '2025-09-23 05:31:58', '2025-09-23 05:31:58', NULL, NULL, NULL, NULL, NULL, 0),
(112, 311, 'How many drops of phosphate reagent is needed?', '8', '10', '6', '5', 'C', '2025-09-23 05:55:11', '2025-09-23 06:41:25', NULL, NULL, NULL, NULL, NULL, 0),
(113, 311, 'What are the parameters needed to test using the test kit?', 'Phosphate, Nitrate, and Ammonia', 'Phosphate, Nickel, and Ammonia', 'Phosphate, Nitrite, and Ammonia', 'Phosphate, Zinc, and Ammonia', 'a', '2025-09-23 05:55:12', '2025-09-23 05:55:12', NULL, NULL, NULL, NULL, NULL, 0),
(114, 312, '1 + 1 =', '2', '4', '6', '8', 'a', '2025-09-23 07:02:36', '2025-09-23 07:02:36', NULL, NULL, NULL, NULL, NULL, 0),
(115, 312, '2 + 2 =', '2', '4', '6', '8', 'b', '2025-09-23 07:02:37', '2025-09-23 07:02:37', NULL, NULL, NULL, NULL, NULL, 0),
(116, 312, 'What is the derivative of 12x^2+4x+8', '24x+4', '12x+8', '24x+8', '12x+4', 'a', '2025-09-23 07:02:37', '2025-09-23 07:02:37', NULL, NULL, NULL, NULL, NULL, 0),
(117, 313, '1 + 1 = ?', '2', '4', '6', '8', 'a', '2025-09-23 07:14:35', '2025-09-23 07:14:35', NULL, NULL, NULL, NULL, NULL, 0),
(118, 313, '2 + 2 =', '2', '4', '6', '8', 'b', '2025-09-23 07:14:35', '2025-09-23 07:14:35', NULL, NULL, NULL, NULL, NULL, 0),
(145, 317, 'Mic Test', 'a', 'b', 'c', 'd', 'B', '2025-10-10 02:44:29', '2025-10-10 04:33:48', '1760070828_asahi.jpg', '1760070828_A_asahi intecc img.webp', NULL, NULL, NULL, 0),
(146, 317, 'Question Test', 'a', 'b', 'c', 'd', 'C', '2025-10-10 02:44:29', '2025-10-10 03:03:56', '', '', NULL, NULL, NULL, 0),
(147, 317, 'Image test', 'a', 'b', 'c', 'd', 'C', '2025-10-10 02:46:14', '2025-10-10 03:04:09', '', NULL, NULL, '', NULL, 0),
(148, 317, '212', 'a', 'b', 'c', 'd', 'D', '2025-10-10 02:53:09', '2025-10-10 03:07:26', '1760065583_candidate details modal.png', NULL, NULL, '1760065646_C_image (4).png', '', 0),
(149, 317, 'nimal', 'a', 'b', 'c', 'd', 'C', '2025-10-10 03:06:11', '2025-10-10 03:11:21', '1760065866_image (10).png', NULL, '1760065881_B_Recruitment Board (1).png', NULL, NULL, 0),
(150, 317, 'test', 'a', 'b', 'c', 'd', 'B', '2025-10-10 03:12:14', '2025-10-10 04:33:30', '', NULL, NULL, NULL, '', 0),
(151, 321, 'asd', 'asdas', 'dasdas', 'ads', 'ads', 'D', '2025-10-10 05:36:32', '2025-10-10 05:36:32', NULL, NULL, NULL, NULL, NULL, 0),
(152, 320, 'test', 'test', 'test', 'test', 'test', 'D', '2025-10-13 00:09:07', '2025-10-13 00:09:07', NULL, NULL, NULL, NULL, NULL, 0),
(153, 322, 'asdasd', 'asdas', 'das', 'asdasda', 'sdas', 'D', '2025-10-13 01:25:20', '2025-10-13 01:25:20', NULL, NULL, NULL, NULL, NULL, 0),
(154, 323, '2', 'sada', 'asdas', 'asda', 'sad', 'B', '2025-10-13 02:04:54', '2025-10-13 02:04:54', NULL, NULL, NULL, NULL, NULL, 0),
(155, 324, '454', 'hrhfg', 'gfhfg', 'fgh', 'fgh', 'B', '2025-10-13 02:41:49', '2025-10-13 02:41:49', NULL, NULL, NULL, NULL, NULL, 0),
(156, 325, 'exam', '1', 'asd', 'ads', 'asd', 'D', '2025-10-13 08:42:42', '2025-10-13 08:42:42', NULL, NULL, NULL, NULL, NULL, 0),
(157, 326, 'sfsd', 'fsdf', 'sdf', 'sdfs', 'dfsdf', 'D', '2025-10-13 08:58:31', '2025-10-13 08:58:31', NULL, NULL, NULL, NULL, NULL, 0),
(158, 327, 'asdfsadf', 'sadfsdfa', 'kani B', 'sadfsadf', 'adfasef', 'B', '2025-10-15 06:24:54', '2025-10-15 06:24:54', NULL, NULL, NULL, NULL, NULL, 0),
(159, 327, 'sdfgsdfs', 'sdfsdfs', 'sdfsd', 'sdfsd', 'kani D', 'D', '2025-10-15 06:24:54', '2025-10-15 06:24:54', NULL, NULL, NULL, NULL, NULL, 0),
(160, 328, 'aasa', 'kani', 'asdas', 'zxc', 'asdfa', 'A', '2025-10-15 06:53:05', '2025-10-15 06:53:05', NULL, NULL, NULL, NULL, NULL, 0),
(161, 328, 'fasfasf', 'asdas', 'vaas', 'kani', 'asdas', 'C', '2025-10-15 06:53:05', '2025-10-15 06:53:05', NULL, NULL, NULL, NULL, NULL, 0),
(162, 329, 'fasfas', 'kani', 'sdfgdf', 'dsfgsf', 'sdfgds', 'A', '2025-10-15 07:06:36', '2025-10-15 07:06:36', NULL, NULL, NULL, NULL, NULL, 0),
(163, 329, 'dfsgdf', 'gsdfgsdf', 'gfds', 'kani', 'fsadfas', 'C', '2025-10-15 07:06:36', '2025-10-15 07:06:36', NULL, NULL, NULL, NULL, NULL, 0),
(164, 330, 'asfdasfsa', 'kani', 'sdfsd', 'sdfsd', 'sdfsd', 'A', '2025-10-15 07:32:10', '2025-10-15 07:32:10', NULL, NULL, NULL, NULL, NULL, 0),
(165, 330, 'sdgdsgasd', 'gsdgsdgsd', 'gsdgsa', 'asdfsda', 'kani', 'D', '2025-10-15 07:32:10', '2025-10-15 07:32:10', NULL, NULL, NULL, NULL, NULL, 0),
(166, 331, 'asdas', 'asdas', 'kani', 'asdas', 'asdas', 'B', '2025-10-15 08:36:40', '2025-10-15 08:36:40', NULL, NULL, NULL, NULL, NULL, 0),
(167, 331, 'gsagdgs', 'adgsadg', 'asdgsd', 'kani', 'fsdfsd', 'C', '2025-10-15 08:36:40', '2025-10-15 08:36:40', NULL, NULL, NULL, NULL, NULL, 0),
(168, 332, 'fsdfsd', 'kani', 'sdfsd', 'sdfsd', 'dsfsd', 'A', '2025-10-15 08:49:48', '2025-10-15 08:49:48', NULL, NULL, NULL, NULL, NULL, 0),
(169, 332, 'sdfs', 'dfsdf', 'sdfs', 'kani', 'fsdfsd', 'C', '2025-10-15 08:49:48', '2025-10-15 08:49:48', NULL, NULL, NULL, NULL, NULL, 0),
(170, 333, 'adgsfg', 'dfg', 'sdfg', 'sdfg', 'kani', 'D', '2025-10-15 08:55:14', '2025-10-15 08:55:14', NULL, NULL, NULL, NULL, NULL, 0),
(171, 333, 'dgfsdf', 'gdsfgds', 'kani', 'dfgsdf', 'gdsfg', 'B', '2025-10-15 08:55:14', '2025-10-15 08:55:14', NULL, NULL, NULL, NULL, NULL, 0),
(172, 334, 'regsfgsd', 'kani', 'dfgdfs', 'gdsfgdsf', 'dsfgds', 'A', '2025-10-15 09:08:29', '2025-10-15 09:08:29', NULL, NULL, NULL, NULL, NULL, 0),
(173, 334, 'nfgnfdgnfd', 'nfgnfdgn', 'sdgsdg', 'asdg', 'kani', 'D', '2025-10-15 09:08:30', '2025-10-15 09:08:30', NULL, NULL, NULL, NULL, NULL, 0),
(174, 334, 'sadgasdg', 'sdgasdg', 'kani', 'asdfsdf', 'sdfasd', 'B', '2025-10-15 09:08:30', '2025-10-15 09:08:30', NULL, NULL, NULL, NULL, NULL, 0),
(175, 335, 'asdas', 'sdgzdsfgd', 'fgdgd', 'sdfgdsf', 'kani', 'D', '2025-10-15 09:42:01', '2025-10-15 09:42:01', NULL, NULL, NULL, NULL, NULL, 0),
(176, 335, 'sdgdfgsd', 'fgdsfg', 'sdfgdg', 'kani', 'fsdfsd', 'C', '2025-10-15 09:42:01', '2025-10-15 09:42:01', NULL, NULL, NULL, NULL, NULL, 0);

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
  `removed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `section`
--

CREATE TABLE `section` (
  `Id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `removed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `section`
--

INSERT INTO `section` (`Id`, `name`, `removed`) VALUES
(1, 'All Section', 0),
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
  `role` enum('admin','user','super_admin') DEFAULT 'user',
  `department` tinyint(11) UNSIGNED DEFAULT NULL,
  `division` tinyint(11) UNSIGNED DEFAULT NULL,
  `position` tinyint(11) UNSIGNED DEFAULT NULL,
  `section` tinyint(11) UNSIGNED DEFAULT NULL,
  `company_group` tinyint(11) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `active` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `full_name`, `password`, `email`, `role`, `department`, `division`, `position`, `section`, `company_group`, `created_at`, `updated_at`, `active`) VALUES
(10, 'admin', 'System Administrator', '$2y$10$lub2R8JVvSaFou9VAHMWf.nB44y0aTozMkQYWlNDz2oiy1aqfe4g6', 'system_admin@toyoflex.com', 'super_admin', 0, 0, 0, 0, 0, '2025-03-27 01:53:00', '2025-09-22 05:02:50', 0),
(21, 'Sarbasa', 'Lady Nila Sarbasa', '$2y$10$aMdD276F89ce8SvCj5GMkeQ8ZPVyA03j6K6ZF3P4ELL8TO28R/PGW', 'ladynila.sarsaba.ph@toyoflex.com', 'admin', NULL, NULL, NULL, NULL, NULL, '2025-09-22 05:07:41', '2025-09-22 05:07:55', 0),
(22, 'AILENESINGSON', 'AILENE SINGSON', '$2y$10$yHGq2NdfJ33hNEU7dJyvEOZASB9lZlbRf3eHoKBBEysEiKK13zM1i', 'ailene.singson.ph@toyoflex.com', 'admin', NULL, NULL, NULL, NULL, NULL, '2025-09-22 05:26:15', '2025-09-22 05:27:08', 0),
(23, 'chrisaleger@88', 'Christopher R. Alegre', '$2y$10$GnuZwEqPxkZlSmfxShU2i.9iswtBhs0RE1fGOMIKZQROoeXU8Om5u', 'christopher.alegre.ph@toyoflex.com', 'admin', NULL, NULL, NULL, NULL, NULL, '2025-09-22 05:26:20', '2025-09-22 05:27:00', 0),
(24, 'giami', 'Gia Mae Oring', '$2y$10$Yau.3QXonTtqUz1uTnut/uPocY18rBqWgj3hpn2SYiDsHb1RSIF7C', 'giamae.oring.ph@toyoflex.com', 'admin', NULL, NULL, NULL, NULL, NULL, '2025-09-22 05:26:24', '2025-09-22 05:26:48', 0),
(25, 'jcasul', 'Joan Casul', '$2y$10$hmSFgE5h92H73lJxa6njhOUc3pMejtdTbjpbvkOOqJw8iW3UExUfC', 'joan.casul.ph@toyoflex.com', 'admin', NULL, NULL, NULL, NULL, NULL, '2025-09-22 05:26:25', '2025-09-22 05:26:46', 0),
(26, 'ybamie_ybanez', 'Ybamie Miel Ybañez', '$2y$10$awZt4WAzmqqWJm0YbzBJ5eeRnlJJmubAIoaxZJn56CASdd.CUzF6S', 'ybamie.ybanez.ph@toyoflex.com', 'admin', NULL, NULL, NULL, NULL, NULL, '2025-09-22 05:26:36', '2025-09-22 05:27:26', 0),
(27, 'rejie2025', 'Rejieson Indoc', '$2y$10$wutAfD2yuO3LL2.eCjLe6uGJ3qNCPgIJQVYhAPtmW5pqcGtI0HxFG', 'rejieson.indoc.ph@toyoflex.com', 'admin', NULL, NULL, NULL, NULL, NULL, '2025-09-22 05:26:51', '2025-09-22 05:55:13', 0),
(28, 'Amyville', 'Amyville Ascura', '$2y$10$smOiagx1ZJtH0WW7EQBUT.2R/sMXQkpR6QK/B8jop1YI5yLOeoV7S', 'amyville.ascura.ph@toyoflex.com', 'admin', NULL, NULL, NULL, NULL, NULL, '2025-09-22 05:26:53', '2025-09-22 05:26:53', 0),
(29, 'Jaclyn', 'Jaclyn Anne Arrieta', '$2y$10$k3.2fZg.OwLVEnKgrCdblOuu24pyU4S0TUVeCq/9uqWxRBS8ENZja', 'jaclyn.arrieta.ph@toyoflex.com', 'admin', NULL, NULL, NULL, NULL, NULL, '2025-09-22 05:26:56', '2025-09-22 05:26:56', 0),
(30, 'Grazzy', 'Grace Booc', '$2y$10$1ArJUOE5m9PfJd0HMz/a3eKoZtRksG0GLFkBuEgRVdRjA0KWsHF/e', 'grace.booc.ph@toyoflex.com', 'admin', NULL, NULL, NULL, NULL, NULL, '2025-09-22 05:27:02', '2025-09-22 06:06:53', 0),
(32, 'Jezel', 'Jezel Lahoylahoy', '$2y$10$hsUGQj6GtFh727pgFwxA.ueHbMOD6ddy5LTooRxsgw8Mt2H87AgYG', 'it-staff11.ph@toyoflex.com', 'admin', 1, 2, NULL, 2, NULL, '2025-09-22 05:27:14', '2025-10-16 01:55:20', 0),
(33, 'Regine', 'Regine Elemino', '$2y$10$twrzqzPgmWQrV4KpPk4ZOea0bl.6gfMBFC5Gq84Rj.H3kePSf7Soa', 'regine.elemino.ph@toyoflex.com', 'admin', NULL, NULL, NULL, NULL, NULL, '2025-09-22 05:27:16', '2025-09-22 05:27:16', 0),
(34, 'Ceasar', 'Chris Ceasar Ycot', '$2y$10$0TFUz0PFeu4vvpCXHCYmy.HmghXaK9re/hQL4Qxp5Q1GpkVygSACS', 'chris.ycot.ph@toyoflex.com', 'admin', NULL, NULL, NULL, NULL, NULL, '2025-09-22 05:27:33', '2025-09-22 05:28:57', 0),
(35, 'Emerald', 'Emerald Abaniel', '$2y$10$BtQFBpVRpNycqAiggyZkdOX18fYj3oG8pSR7N8p0nwlSyxWkJMYMm', 'emerald.abaniel.ph@toyoflex.com', 'admin', NULL, NULL, NULL, NULL, NULL, '2025-09-22 05:27:38', '2025-09-22 05:27:38', 0),
(36, 'Elene', 'Elene Pasaporte', '$2y$10$Vy9WQgujplhEf2zQ/qmCj.v5NrQ0y5UsFmO3Jj7ROFGPVRUdmnIaG', 'elene.pasaporte.ph@toyoflex.com', 'admin', NULL, NULL, NULL, NULL, NULL, '2025-09-22 05:28:46', '2025-09-22 05:29:57', 0),
(37, 'Devine', 'Devine Grace Martel', '$2y$10$TzmyNVF2WmTnHyaAuAI3rOpDwH/2dlv3XEjHRJQ.ZE.T7TD.YdL96', 'devine.avenido.ph@toyoflex.com', 'admin', NULL, NULL, NULL, NULL, NULL, '2025-09-22 05:28:58', '2025-09-22 05:40:04', 0),
(39, 'Lowela', 'Lowela Pepito', '$2y$10$Kra7C4sFzPiuTDSJiBg/ZOlpbIgmGWQyBVeOWMcepzmeWo0SvbWLG', 'lowela.pepito.ph@toyoflex.com', 'admin', NULL, NULL, NULL, NULL, NULL, '2025-09-22 05:34:13', '2025-09-22 05:34:13', 0),
(40, '013426', 'ALMAHSOL A. TABIGUE', '$2y$10$QJyu68FEvztkJ.tJSI7x/e7jTs7tYSdTwbh.4hwAdGle8RCHkmbke', 'it-staff10.ph@toyoflex.com', 'user', NULL, NULL, NULL, NULL, NULL, '2025-09-22 05:46:10', '2025-09-22 05:46:10', 0),
(41, 'juvieb.', 'JUVIE BALLASO', '$2y$10$1DU7XReUNYe/QR6iwT6o4OIsc/4Odqi5p8kAGI1R/SYgmcAVCnaNW', 'envi.worker1.ph@toyoflex.com', 'user', NULL, NULL, NULL, NULL, NULL, '2025-09-23 05:08:51', '2025-09-23 05:14:51', 0),
(43, 'elaizagula', 'Elaiza Gula', '$2y$10$5.ANXzOL4zj7TEg.Hsgpx.kIvZXFyrc.Bk8Jta7kU9UshlwmHEDfq', 'elaiza.gula.ph@toyoflex.com', 'admin', NULL, NULL, NULL, NULL, NULL, '2025-09-23 05:09:30', '2025-09-23 05:09:30', 0),
(44, 'roldan88', 'Roldan Escarpe', '$2y$10$BteTurm70C6Po43M9yD/aOer/haah.lYdUSCPTXHrdGoQmDM2jgkq', 'roldan.escarpe.ph@toyoflex.com', 'user', NULL, NULL, NULL, NULL, NULL, '2025-09-23 06:58:09', '2025-09-23 06:58:09', 0),
(45, 'RyvyM', 'Ryvy Manabit', '$2y$10$JZyvYxSlkJgxZKqxM8PaV.ToEJycRZJTtnpIoanfmBmBBMjIdfo/m', 'ryvy.manabit.ph@toyoflex.com', 'user', NULL, NULL, NULL, NULL, NULL, '2025-09-23 06:59:49', '2025-09-23 06:59:49', 0),
(46, 'Knaven', 'Knaven Jade Paran', '$2y$10$po/OorzBPYaxu/14qguzoe5aeYM/bunJyvwpqRdkaF8/0B9a4SIFS', 'it-tech9.ph@toyoflex.com', 'admin', 1, 7, NULL, 15, NULL, '2025-10-08 01:52:59', '2025-10-09 05:36:58', 0),
(58, 'Lating', 'Shiela Mae Malingin', '$2y$10$jLe.QYfihxFB7vgVa0.Yee8vye7S9Tx0p26jJ06gMHw0g01I3nZZy', 'it-staff5.ph@toyoflex.com', 'user', 4, 1, NULL, 1, NULL, '2025-10-09 05:25:10', '2025-10-16 02:40:28', 0),
(59, 'Test', 'Test User', '$2y$10$wEqDHWF3gBN.BIZcxUpPH.nXVIeY8sl.SmWtjK5bMGX6DAuPbeqWG', 'hrAdmin.local.ph@toyoflex.com', 'user', 3, 16, NULL, 18, NULL, '2025-10-16 07:52:45', '2025-10-16 07:53:18', 0),
(61, 'Test2', 'Test User 2', '$2y$10$u0pCRUIvmJYAsiqOVnIIn.1wf2W8pVncaLwX7hTtIuFDOU3LGyVP2', 'hrAdmin1.local.ph@toyoflex.com', 'user', 1, 15, NULL, 7, NULL, '2025-10-16 07:55:14', '2025-10-16 07:55:14', 0),
(62, 'Test3', 'Test User 3', '$2y$10$YewcmHSpAz3XMOJ3V1W9AO2uLzrAK4BMfaVyF9RZVj6.1wy0EXJKG', 'hrAdmin2.local.ph@toyoflex.com', 'user', 1, 7, NULL, 6, NULL, '2025-10-16 07:57:01', '2025-10-16 07:57:01', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_courses`
--

CREATE TABLE `user_courses` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_date` timestamp NULL DEFAULT current_timestamp(),
  `updated_date` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `removed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_courses`
--

INSERT INTO `user_courses` (`id`, `course_id`, `user_id`, `created_date`, `updated_date`, `removed`) VALUES
(37, 304, 58, '2025-10-09 08:16:56', '2025-10-09 08:16:56', 0),
(39, 304, 46, '2025-10-09 08:28:14', '2025-10-16 06:47:58', 1),
(40, 320, 46, '2025-10-10 04:43:22', '2025-10-10 04:43:22', 0),
(41, 320, 58, '2025-10-10 04:43:22', '2025-10-10 04:43:22', 0),
(42, 321, 30, '2025-10-10 05:38:01', '2025-10-10 05:38:01', 0),
(43, 321, 58, '2025-10-10 05:38:13', '2025-10-10 05:38:13', 0),
(44, 315, 32, '2025-10-10 09:07:28', '2025-10-10 09:07:28', 0),
(45, 304, 32, '2025-10-12 23:20:00', '2025-10-12 23:20:36', 1),
(46, 321, 46, '2025-10-12 23:51:53', '2025-10-12 23:51:53', 0),
(47, 318, 46, '2025-10-13 01:04:08', '2025-10-13 01:04:08', 0),
(48, 319, 46, '2025-10-13 01:04:49', '2025-10-13 01:04:49', 0),
(49, 322, 32, '2025-10-13 01:25:48', '2025-10-13 01:25:48', 0),
(50, 323, 32, '2025-10-13 02:05:31', '2025-10-13 02:05:31', 0),
(51, 324, 32, '2025-10-13 02:42:01', '2025-10-13 02:42:01', 0),
(52, 325, 32, '2025-10-13 08:43:01', '2025-10-13 08:43:01', 0),
(53, 325, 58, '2025-10-13 08:43:01', '2025-10-13 08:43:01', 0),
(54, 326, 58, '2025-10-13 08:58:41', '2025-10-13 08:58:41', 0),
(55, 327, 32, '2025-10-15 06:25:05', '2025-10-15 06:25:05', 0),
(56, 328, 32, '2025-10-15 06:53:18', '2025-10-15 06:53:18', 0),
(57, 329, 32, '2025-10-15 07:07:28', '2025-10-15 07:07:28', 0),
(58, 330, 32, '2025-10-15 07:34:17', '2025-10-15 07:34:17', 0),
(59, 331, 32, '2025-10-15 08:36:52', '2025-10-15 08:36:52', 0),
(60, 332, 32, '2025-10-15 08:50:04', '2025-10-15 08:50:04', 0),
(61, 333, 32, '2025-10-15 08:55:22', '2025-10-15 08:55:22', 0),
(62, 334, 32, '2025-10-15 09:08:57', '2025-10-15 09:08:57', 0),
(63, 335, 32, '2025-10-15 09:42:16', '2025-10-15 09:42:16', 0);

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
  `removed` tinyint(1) NOT NULL DEFAULT 0,
  `attempts` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_progress`
--

INSERT INTO `user_progress` (`id`, `user_id`, `course_id`, `score`, `total_score`, `completed`, `user_answers`, `progress_percentage`, `is_correct`, `created_at`, `updated_at`, `removed`, `attempts`) VALUES
(6, 41, 310, 1, 1, 1, '{\"111\":\"A\"}', 100, 0, '2025-09-23 05:43:07', '2025-09-23 05:43:07', 0, 0),
(7, 10, 310, 1, 1, 1, '{\"111\":\"A\"}', 100, 0, '2025-09-23 05:46:29', '2025-09-23 05:46:29', 0, 0),
(8, 41, 311, 2, 2, 1, '{\"113\":\"A\",\"112\":\"B\"}', 100, 0, '2025-09-23 06:35:28', '2025-09-23 06:35:28', 0, 0),
(9, 32, 314, 0, 1, 1, '{\"122\":\"B\"}', 0, 0, '2025-10-08 02:23:37', '2025-10-08 02:23:37', 0, 0),
(10, 10, 314, 0, 1, 1, '{\"122\":\"A\"}', 0, 0, '2025-10-08 09:25:46', '2025-10-08 09:25:46', 0, 0),
(11, 40, 314, 1, 1, 1, '{\"122\":\"C\"}', 100, 0, '2025-10-08 23:22:59', '2025-10-08 23:22:59', 0, 0),
(14, 58, 314, 0, 1, 1, '{\"122\":\"A\"}', 0, 0, '2025-10-09 07:17:23', '2025-10-09 07:17:23', 0, 0),
(15, 58, 304, 2, 4, 1, '{\"119\":\"A\",\"123\":\"B\",\"121\":\"C\",\"120\":\"D\"}', 50, 0, '2025-10-09 07:23:08', '2025-10-09 07:23:08', 0, 0),
(16, 58, 315, 1, 4, 1, '{\"126\":\"A\",\"127\":\"A\",\"124\":\"A\",\"125\":\"A\"}', 25, 0, '2025-10-09 23:43:15', '2025-10-09 23:43:15', 0, 0),
(17, 58, 316, 0, 4, 1, '{\"129\":\"A\",\"128\":\"B\",\"131\":\"C\",\"130\":\"A\"}', 0, 0, '2025-10-09 23:45:33', '2025-10-09 23:45:33', 0, 0),
(18, 58, 317, 3, 4, 1, '{\"135\":\"D\",\"132\":\"A\",\"134\":\"C\",\"133\":\"A\"}', 75, 0, '2025-10-09 23:48:23', '2025-10-09 23:48:23', 0, 0),
(19, 46, 318, 1, 1, 1, '{\"136\":\"A\"}', 100, 0, '2025-10-10 00:09:27', '2025-10-10 00:09:27', 0, 0),
(20, 46, 319, 0, 1, 1, '{\"138\":\"A\"}', 0, 0, '2025-10-10 00:12:14', '2025-10-10 00:12:14', 0, 0),
(21, 58, 321, 0, 1, 1, '{\"151\":\"A\"}', 0, 0, '2025-10-10 06:05:43', '2025-10-10 06:05:43', 0, 0),
(22, 32, 322, 0, 1, 1, '{\"153\":\"B\"}', 0, 0, '2025-10-13 02:29:40', '2025-10-13 02:29:40', 0, 0),
(23, 58, 320, 0, 1, 1, '{\"152\":\"C\"}', 0, 0, '2025-10-13 06:23:48', '2025-10-13 06:23:48', 0, 0),
(24, 58, 325, 1, 1, 1, '0', 100, 0, '2025-10-13 08:45:09', '2025-10-15 05:23:21', 0, 3),
(25, 58, 326, 0, 1, 0, '0', 0, 0, '2025-10-13 08:58:51', '2025-10-13 08:59:04', 0, 3),
(26, 32, 327, 2, 2, 1, '0', 100, 0, '2025-10-15 06:26:05', '2025-10-15 06:28:10', 0, 3),
(27, 32, 328, 2, 2, 1, '0', 100, 0, '2025-10-15 06:54:03', '2025-10-15 06:55:01', 0, 3),
(28, 32, 329, 1, 2, 0, '{\"163\":\"C\",\"162\":\"D\"}', 50, 0, '2025-10-15 07:10:04', '2025-10-15 07:15:11', 0, 3),
(29, 32, 330, 2, 2, 1, '{\"165\":\"D\",\"164\":\"A\"}', 100, 0, '2025-10-15 07:35:27', '2025-10-15 07:35:56', 0, 2),
(30, 32, 331, 1, 2, 0, '{\"167\":\"C\",\"166\":\"A\"}', 50, 0, '2025-10-15 08:38:43', '2025-10-15 08:39:09', 0, 3),
(31, 32, 332, 0, 2, 1, '{\"169\":\"A\",\"168\":\"C\"}', 0, 0, '2025-10-15 08:50:29', '2025-10-15 08:51:35', 0, 3),
(32, 32, 333, 2, 2, 1, '{\"170\":\"D\",\"171\":\"B\"}', 100, 0, '2025-10-15 09:04:14', '2025-10-15 09:05:23', 0, 2),
(33, 32, 334, 3, 3, 1, '{\"173\":\"D\",\"172\":\"A\",\"174\":\"B\"}', 100, 0, '2025-10-15 09:33:31', '2025-10-15 09:41:18', 0, 2),
(34, 32, 335, 0, 2, 1, '{\"176\":\"D\",\"175\":\"C\"}', 0, 0, '2025-10-15 09:42:42', '2025-10-15 09:42:57', 0, 3);

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
  `removed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_video_progress`
--

INSERT INTO `user_video_progress` (`id`, `user_id`, `video_id`, `watched`, `created_at`, `updated_at`, `removed`) VALUES
(24, 40, 288, 1, '2025-09-22 05:56:10', '2025-09-22 05:56:10', 0),
(25, 10, 291, 1, '2025-09-22 06:06:08', '2025-09-22 06:06:08', 0),
(26, 41, 288, 1, '2025-09-23 05:19:12', '2025-09-23 05:19:12', 0),
(27, 41, 286, 1, '2025-09-23 05:32:00', '2025-09-24 05:58:13', 1),
(28, 10, 293, 1, '2025-09-23 05:40:54', '2025-09-23 05:40:54', 0),
(29, 41, 293, 1, '2025-09-23 05:41:54', '2025-09-23 05:41:54', 0),
(30, 40, 286, 1, '2025-09-23 05:44:23', '2025-09-24 05:58:13', 1),
(31, 10, 288, 1, '2025-09-23 05:48:26', '2025-09-23 05:48:26', 0),
(32, 10, 286, 1, '2025-09-23 05:48:51', '2025-09-24 05:58:13', 1),
(33, 41, 294, 1, '2025-09-23 06:33:15', '2025-09-23 06:33:15', 0),
(34, 41, 295, 1, '2025-09-23 07:07:34', '2025-09-23 07:10:31', 1),
(35, 40, 290, 1, '2025-09-24 06:00:12', '2025-10-08 05:07:38', 1),
(36, 40, 298, 1, '2025-09-24 06:01:48', '2025-10-08 05:28:45', 1),
(37, 32, 299, 1, '2025-10-08 02:00:29', '2025-10-08 07:25:22', 0),
(38, 32, 301, 1, '2025-10-08 02:11:11', '2025-10-08 02:24:33', 0),
(39, 46, 302, 1, '2025-10-08 05:30:00', '2025-10-08 05:56:45', 1),
(40, 46, 303, 1, '2025-10-08 05:30:11', '2025-10-08 05:56:42', 1),
(41, 46, 304, 1, '2025-10-08 05:59:47', '2025-10-08 06:01:25', 1),
(42, 46, 305, 1, '2025-10-08 06:01:14', '2025-10-08 06:01:23', 1),
(43, 46, 306, 1, '2025-10-08 06:02:58', '2025-10-08 06:25:38', 1),
(44, 46, 307, 1, '2025-10-08 06:03:11', '2025-10-08 06:25:36', 1),
(45, 46, 308, 1, '2025-10-08 06:27:18', '2025-10-08 06:28:08', 1),
(46, 46, 309, 1, '2025-10-08 06:27:25', '2025-10-08 06:28:10', 1),
(47, 46, 310, 1, '2025-10-08 06:33:13', '2025-10-09 23:39:06', 0),
(48, 46, 311, 1, '2025-10-08 06:33:23', '2025-10-08 06:33:23', 0),
(49, 10, 299, 1, '2025-10-08 09:25:07', '2025-10-08 09:25:07', 0),
(50, 10, 301, 1, '2025-10-08 09:25:17', '2025-10-08 09:25:17', 0),
(51, 40, 299, 1, '2025-10-08 23:22:36', '2025-10-08 23:22:36', 0),
(52, 40, 301, 1, '2025-10-08 23:22:44', '2025-10-08 23:22:44', 0),
(53, 58, 310, 1, '2025-10-09 06:26:58', '2025-10-09 06:26:58', 0),
(54, 58, 299, 1, '2025-10-09 07:17:11', '2025-10-09 07:17:11', 0),
(55, 58, 301, 1, '2025-10-09 07:17:18', '2025-10-09 07:17:18', 0),
(56, 58, 311, 1, '2025-10-09 07:19:23', '2025-10-09 07:19:23', 0),
(57, 58, 312, 1, '2025-10-09 23:42:44', '2025-10-09 23:42:44', 0),
(58, 58, 313, 1, '2025-10-09 23:42:59', '2025-10-09 23:42:59', 0),
(59, 58, 314, 1, '2025-10-09 23:45:11', '2025-10-09 23:45:11', 0),
(60, 58, 315, 1, '2025-10-09 23:45:14', '2025-10-09 23:45:14', 0),
(61, 58, 316, 1, '2025-10-09 23:47:51', '2025-10-09 23:47:51', 0),
(62, 58, 317, 1, '2025-10-09 23:47:55', '2025-10-09 23:47:55', 0),
(63, 46, 318, 1, '2025-10-10 00:06:03', '2025-10-10 00:06:03', 0),
(64, 46, 319, 1, '2025-10-10 00:06:08', '2025-10-10 00:06:08', 0),
(65, 46, 320, 1, '2025-10-10 00:12:03', '2025-10-10 00:12:03', 0),
(66, 46, 321, 1, '2025-10-10 00:41:10', '2025-10-10 00:41:10', 0),
(67, 46, 322, 1, '2025-10-10 00:41:17', '2025-10-10 00:41:17', 0),
(68, 32, 316, 1, '2025-10-10 02:45:26', '2025-10-10 04:37:12', 0),
(69, 32, 317, 1, '2025-10-10 02:45:30', '2025-10-10 02:45:30', 0),
(70, 58, 321, 1, '2025-10-10 05:11:08', '2025-10-13 06:26:13', 0),
(71, 58, 323, 1, '2025-10-10 05:51:33', '2025-10-10 05:51:33', 0),
(72, 32, 312, 1, '2025-10-10 09:07:46', '2025-10-13 01:21:57', 0),
(73, 32, 313, 1, '2025-10-10 09:08:14', '2025-10-10 09:08:14', 0),
(74, 46, 323, 1, '2025-10-12 23:52:50', '2025-10-12 23:52:50', 0),
(75, 32, 326, 1, '2025-10-13 02:05:42', '2025-10-13 02:05:42', 0),
(76, 32, 327, 1, '2025-10-13 02:05:52', '2025-10-13 02:05:52', 0),
(77, 32, 324, 1, '2025-10-13 02:23:37', '2025-10-13 02:23:37', 0),
(78, 32, 325, 1, '2025-10-13 02:29:35', '2025-10-13 02:29:35', 0),
(79, 58, 322, 1, '2025-10-13 04:53:37', '2025-10-13 06:26:21', 0),
(80, 58, 330, 1, '2025-10-13 08:43:20', '2025-10-13 08:43:20', 0),
(81, 32, 332, 1, '2025-10-15 06:25:31', '2025-10-15 06:25:31', 0),
(82, 32, 333, 1, '2025-10-15 06:53:51', '2025-10-15 06:53:51', 0),
(83, 32, 334, 1, '2025-10-15 07:07:46', '2025-10-15 07:07:46', 0),
(84, 32, 335, 1, '2025-10-15 07:52:13', '2025-10-15 07:52:13', 0),
(85, 32, 337, 1, '2025-10-15 08:50:14', '2025-10-15 08:50:14', 0),
(86, 32, 338, 1, '2025-10-15 09:02:59', '2025-10-15 09:04:02', 0),
(87, 32, 339, 1, '2025-10-15 09:29:08', '2025-10-15 09:29:08', 0),
(88, 32, 340, 1, '2025-10-15 09:42:24', '2025-10-15 23:09:06', 0);

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
-- Indexes for table `course_collab`
--
ALTER TABLE `course_collab`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_collab_ibfk_1` (`course_id`),
  ADD KEY `course_collab_ibfk_2` (`admin_id`);

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
-- Indexes for table `user_courses`
--
ALTER TABLE `user_courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_courses_ibfk_1` (`course_id`),
  ADD KEY `user_courses_ibfk_2` (`user_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=336;

--
-- AUTO_INCREMENT for table `course_collab`
--
ALTER TABLE `course_collab`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `course_videos`
--
ALTER TABLE `course_videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=342;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=177;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `section`
--
ALTER TABLE `section`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `user_courses`
--
ALTER TABLE `user_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `user_progress`
--
ALTER TABLE `user_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `user_video_progress`
--
ALTER TABLE `user_video_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `course_collab`
--
ALTER TABLE `course_collab`
  ADD CONSTRAINT `course_collab_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_collab_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `user_courses`
--
ALTER TABLE `user_courses`
  ADD CONSTRAINT `user_courses_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_courses_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_progress`
--
ALTER TABLE `user_progress`
  ADD CONSTRAINT `user_progress_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_video_progress`
--
ALTER TABLE `user_video_progress`
  ADD CONSTRAINT `user_video_progress_ibfk_2` FOREIGN KEY (`video_id`) REFERENCES `course_videos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
