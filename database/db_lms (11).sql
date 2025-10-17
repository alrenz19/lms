-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 17, 2025 at 09:58 AM
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
(340, 'dasasd', 'dasdas', NULL, NULL, NULL, NULL, NULL, NULL, 32, 4, '2025-10-17 07:39:43', '2025-10-17 07:39:43', 0, 0),
(341, 'test', 'test', NULL, NULL, NULL, NULL, NULL, NULL, 32, 1, '2025-10-17 07:42:51', '2025-10-17 07:42:51', 0, 0);

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
(346, 340, '1', '', '/volume1/video/mod_68f1f2bf7562d4.88070904_screen_recording_1760686724013.mp4', 398338, '2025-10-17 07:39:43', '2025-10-17 07:39:43', 0),
(347, 340, '2', '', '/volume1/video/mod_68f1f2bf77f3c6.02300275_screen_recording_1760686750183.mp4', 366093, '2025-10-17 07:39:43', '2025-10-17 07:39:43', 0),
(348, 340, '3', '', '/volume1/video/mod_68f1f2bf78d956.03991443_screen_recording_1760686769019.mp4', 469736, '2025-10-17 07:39:43', '2025-10-17 07:39:43', 0),
(349, 340, '4', '', '/volume1/video/mod_68f1f2bf7974f3.23510372_sample.pdf', 18810, '2025-10-17 07:39:43', '2025-10-17 07:39:43', 0),
(350, 341, 'reerer', '', '/volume1/video/mod_68f1f37beb0650.52432214_screen_recording_1760686968153.mp4', 178914, '2025-10-17 07:42:51', '2025-10-17 07:42:51', 0);

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
(40, '013426', 'ALMAHSOL A. TABIGUE', '$2y$10$QJyu68FEvztkJ.tJSI7x/e7jTs7tYSdTwbh.4hwAdGle8RCHkmbke', 'it-staff10.ph@toyoflex.com', 'user', 3, 7, NULL, 15, NULL, '2025-09-22 05:46:10', '2025-10-17 04:55:58', 0),
(41, 'juvieb.', 'JUVIE BALLASO', '$2y$10$1DU7XReUNYe/QR6iwT6o4OIsc/4Odqi5p8kAGI1R/SYgmcAVCnaNW', 'envi.worker1.ph@toyoflex.com', 'user', NULL, NULL, NULL, NULL, NULL, '2025-09-23 05:08:51', '2025-09-23 05:14:51', 0),
(43, 'elaizagula', 'Elaiza Gula', '$2y$10$5.ANXzOL4zj7TEg.Hsgpx.kIvZXFyrc.Bk8Jta7kU9UshlwmHEDfq', 'elaiza.gula.ph@toyoflex.com', 'admin', NULL, NULL, NULL, NULL, NULL, '2025-09-23 05:09:30', '2025-09-23 05:09:30', 0),
(44, 'roldan88', 'Roldan Escarpe', '$2y$10$BteTurm70C6Po43M9yD/aOer/haah.lYdUSCPTXHrdGoQmDM2jgkq', 'roldan.escarpe.ph@toyoflex.com', 'user', NULL, NULL, NULL, NULL, NULL, '2025-09-23 06:58:09', '2025-09-23 06:58:09', 0),
(45, 'RyvyM', 'Ryvy Manabit', '$2y$10$JZyvYxSlkJgxZKqxM8PaV.ToEJycRZJTtnpIoanfmBmBBMjIdfo/m', 'ryvy.manabit.ph@toyoflex.com', 'user', NULL, NULL, NULL, NULL, NULL, '2025-09-23 06:59:49', '2025-09-23 06:59:49', 0),
(46, 'Knaven', 'Knaven Jade Paran', '$2y$10$po/OorzBPYaxu/14qguzoe5aeYM/bunJyvwpqRdkaF8/0B9a4SIFS', 'it-tech9.ph@toyoflex.com', 'admin', 1, 7, NULL, 15, NULL, '2025-10-08 01:52:59', '2025-10-09 05:36:58', 0),
(58, 'Sheila', 'Sheila Malingin', '$2y$10$jLe.QYfihxFB7vgVa0.Yee8vye7S9Tx0p26jJ06gMHw0g01I3nZZy', 'it-staff5.ph@toyoflex.com', 'user', 1, 7, NULL, 15, NULL, '2025-10-09 05:25:10', '2025-10-17 02:04:42', 0),
(63, 'Rodel', 'Rodel James Duterte', '$2y$10$Da9Z3hcvh0xdpS2i1kyAWuV23ERXoLHgmUKpyaaDoziEBHCyTFAHi', 'it-tech4.ph@toyoflex.com', 'user', 1, 7, NULL, 15, NULL, '2025-10-17 04:57:38', '2025-10-17 04:57:38', 0);

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
(73, 340, 40, '2025-10-17 07:40:08', '2025-10-17 07:40:08', 0);

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
(97, 40, 346, 1, '2025-10-17 07:41:15', '2025-10-17 07:41:15', 0),
(98, 40, 347, 1, '2025-10-17 07:41:34', '2025-10-17 07:41:34', 0),
(99, 40, 348, 1, '2025-10-17 07:43:13', '2025-10-17 07:43:13', 0),
(100, 40, 349, 1, '2025-10-17 07:43:15', '2025-10-17 07:43:15', 0);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=342;

--
-- AUTO_INCREMENT for table `course_collab`
--
ALTER TABLE `course_collab`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `course_videos`
--
ALTER TABLE `course_videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=351;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=186;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `user_courses`
--
ALTER TABLE `user_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `user_progress`
--
ALTER TABLE `user_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `user_video_progress`
--
ALTER TABLE `user_video_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

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
