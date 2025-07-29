-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 29, 2025 at 05:51 AM
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
-- Database: `sisaket_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `healthstaff_shelters`
--

CREATE TABLE `healthstaff_shelters` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `shelter_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `healthstaff_shelters`
--

INSERT INTO `healthstaff_shelters` (`id`, `user_id`, `shelter_id`, `created_at`) VALUES
(37, 6, 68, '2025-07-28 10:23:49'),
(38, 6, 15, '2025-07-28 10:23:49'),
(39, 6, 33, '2025-07-28 10:23:49');

-- --------------------------------------------------------

--
-- Table structure for table `hospital_daily_reports`
--

CREATE TABLE `hospital_daily_reports` (
  `id` int(11) NOT NULL,
  `shelter_id` int(11) NOT NULL,
  `report_date` date NOT NULL,
  `total_patients` int(11) DEFAULT 0,
  `male_patients` int(11) DEFAULT 0,
  `female_patients` int(11) DEFAULT 0,
  `pregnant_women` int(11) DEFAULT 0,
  `disabled_patients` int(11) DEFAULT 0,
  `bedridden_patients` int(11) DEFAULT 0,
  `elderly_patients` int(11) DEFAULT 0,
  `child_patients` int(11) DEFAULT 0,
  `chronic_disease_patients` int(11) DEFAULT 0,
  `diabetes_patients` int(11) DEFAULT 0,
  `hypertension_patients` int(11) DEFAULT 0,
  `heart_disease_patients` int(11) DEFAULT 0,
  `mental_health_patients` int(11) DEFAULT 0,
  `kidney_disease_patients` int(11) DEFAULT 0,
  `other_monitored_diseases` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hospital_daily_reports`
--

INSERT INTO `hospital_daily_reports` (`id`, `shelter_id`, `report_date`, `total_patients`, `male_patients`, `female_patients`, `pregnant_women`, `disabled_patients`, `bedridden_patients`, `elderly_patients`, `child_patients`, `chronic_disease_patients`, `diabetes_patients`, `hypertension_patients`, `heart_disease_patients`, `mental_health_patients`, `kidney_disease_patients`, `other_monitored_diseases`, `notes`, `created_at`, `updated_at`, `created_by`) VALUES
(3, 68, '2025-07-28', 2278, 512, 1236, 504, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-07-28 07:11:36', '2025-07-28 12:21:05', 2),
(4, 68, '2025-07-29', 2291, 7, 6, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, NULL, '2025-07-29 02:12:24', '2025-07-29 02:51:52', 6);

-- --------------------------------------------------------

--
-- Table structure for table `hospital_update_logs`
--

CREATE TABLE `hospital_update_logs` (
  `id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `shelter_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `field_name` varchar(100) NOT NULL,
  `old_value` int(11) NOT NULL,
  `new_value` int(11) NOT NULL,
  `change_amount` int(11) NOT NULL,
  `operation_type` enum('add','subtract') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hospital_update_logs`
--

INSERT INTO `hospital_update_logs` (`id`, `report_id`, `shelter_id`, `user_id`, `field_name`, `old_value`, `new_value`, `change_amount`, `operation_type`, `created_at`) VALUES
(1, 3, 68, 2, 'total_patients', 4, 526, 522, 'add', '2025-07-28 10:21:00'),
(2, 3, 68, 2, 'female_patients', 2, 524, 522, 'add', '2025-07-28 10:21:00'),
(3, 3, 68, 2, 'total_patients', 526, 726, 200, 'add', '2025-07-28 10:21:42'),
(4, 3, 68, 2, 'female_patients', 524, 724, 200, 'add', '2025-07-28 10:21:42');

-- --------------------------------------------------------

--
-- Table structure for table `occupant_update_logs`
--

CREATE TABLE `occupant_update_logs` (
  `log_id` int(11) NOT NULL,
  `shelter_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `log_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `operation_type` enum('add','subtract') NOT NULL,
  `total_change` int(11) DEFAULT 0,
  `male_change` int(11) DEFAULT 0,
  `female_change` int(11) DEFAULT 0,
  `pregnant_change` int(11) DEFAULT 0,
  `disabled_change` int(11) DEFAULT 0,
  `bedridden_change` int(11) DEFAULT 0,
  `elderly_change` int(11) DEFAULT 0,
  `child_change` int(11) DEFAULT 0,
  `chronic_disease_change` int(11) DEFAULT 0,
  `diabetes_change` int(11) DEFAULT 0,
  `hypertension_change` int(11) DEFAULT 0,
  `heart_disease_change` int(11) DEFAULT 0,
  `mental_health_change` int(11) DEFAULT 0,
  `kidney_disease_change` int(11) DEFAULT 0,
  `other_monitored_diseases_change` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`, `active`) VALUES
('menu_dashboard', '1', 1),
('menu_settings', '1', 1),
('menu_shelters', '1', 1),
('menu_users', '1', 1),
('system_status', '1', 1);

-- --------------------------------------------------------

--
-- Table structure for table `shelters`
--

CREATE TABLE `shelters` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(100) NOT NULL,
  `capacity` int(11) DEFAULT 0,
  `current_occupancy` int(11) DEFAULT 0,
  `coordinator` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `amphoe` varchar(100) DEFAULT NULL,
  `tambon` varchar(100) DEFAULT NULL,
  `latitude` varchar(50) DEFAULT NULL,
  `longitude` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `requires_detailed_report` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shelters`
--

INSERT INTO `shelters` (`id`, `name`, `type`, `capacity`, `current_occupancy`, `coordinator`, `phone`, `amphoe`, `tambon`, `latitude`, `longitude`, `created_at`, `requires_detailed_report`) VALUES
(1, 'หอประชุมที่ว่าการ อ.น้ำเกลี้ยง', 'ศูนย์พักพิง', 120, 0, '', '0639307182', 'น้ำเกลี้ยง', '', '', '', '2025-07-28 06:34:15', 0),
(2, 'วัดบ้านหนองนาเวียง', 'ศูนย์พักพิง', 300, 0, '', '', 'น้ำเกลี้ยง', '', '', '', '2025-07-28 06:34:15', 0),
(3, 'วัดสว่างวารีรัตนาราม', 'ศูนย์พักพิง', 600, 0, '', '', 'น้ำเกลี้ยง', '', '', '', '2025-07-28 06:34:16', 0),
(4, 'วัดยางน้อย', 'ศูนย์พักพิง', 200, 0, '', '', 'น้ำเกลี้ยง', '', '', '', '2025-07-28 06:34:16', 0),
(5, 'ห้องประชุม อบต.น้ำเกลี้ยง', 'ศูนย์พักพิง', 80, 0, '', '0850557529', 'น้ำเกลี้ยง', '', '', '', '2025-07-28 06:34:16', 0),
(6, 'ห้องประชุม อบต.ละเอาะ', 'ศูนย์พักพิง', 80, 0, '', '0872602533', 'น้ำเกลี้ยง', '', '', '', '2025-07-28 06:34:16', 0),
(7, 'ห้องประชุม อบต.ตองปิด', 'ศูนย์พักพิง', 80, 0, '', '0612767533', 'น้ำเกลี้ยง', '', '', '', '2025-07-28 06:34:16', 0),
(8, 'หอประชุม อบต.เขิน', 'ศูนย์พักพิง', 80, 0, '', '0854977021', 'น้ำเกลี้ยง', '', '', '', '2025-07-28 06:34:16', 0),
(9, 'ห้องประชุม อบต.รุ่งระวี', 'ศูนย์พักพิง', 80, 0, '', '0985925499', 'น้ำเกลี้ยง', '', '', '', '2025-07-28 06:34:16', 0),
(10, 'อาคารอเนกประสงค์ อบต.คูบ', 'ศูนย์พักพิง', 80, 0, '', '0897174279', 'น้ำเกลี้ยง', '', '', '', '2025-07-28 06:34:16', 0),
(11, 'วัดบ้านคูบ', 'ศูนย์พักพิง', 0, 0, '', '', 'น้ำเกลี้ยง', '', '', '', '2025-07-28 06:34:16', 0),
(12, 'วัดบ้านสะพุง', 'ศูนย์พักพิง', 0, 0, '', '', 'น้ำเกลี้ยง', '', '', '', '2025-07-28 06:34:16', 0),
(13, 'วัดบ้านเขิน', 'ศูนย์พักพิง', 0, 0, '', '', 'น้ำเกลี้ยง', '', '', '', '2025-07-28 06:34:16', 0),
(14, 'สถานีวนวัฒน์วิจัยห้วยทา ม.8 ต.รุ่งระวี', 'ศูนย์พักพิง', 0, 0, '', '', 'น้ำเกลี้ยง', '', '', '', '2025-07-28 06:34:16', 0),
(15, 'ที่ว่าการ อ.กันทรารมย์', 'ศูนย์รับบริจาค', 0, 0, '', '', 'กันทรารมย์', 'ดูน', '', '', '2025-07-28 06:34:16', 0),
(16, 'วัดกันทรารมย์', 'ศูนย์พักพิง', 0, 0, '', '', 'กันทรารมย์', '', '', '', '2025-07-28 06:34:16', 0),
(17, 'วัดบ้านคำเมย', 'ศูนย์พักพิง', 0, 0, '', '', 'กันทรารมย์', '', '', '', '2025-07-28 06:34:16', 0),
(18, 'วัดบ้านอีต้อม', 'ศูนย์พักพิง', 0, 0, '', '', 'กันทรารมย์', '', '', '', '2025-07-28 06:34:16', 0),
(19, 'วัดบ้านโพธิ์ลังกา', 'ศูนย์พักพิง', 0, 0, '', '', 'กันทรารมย์', '', '', '', '2025-07-28 06:34:16', 0),
(20, 'วัดบ้านหัวช้าง', 'ศูนย์พักพิง', 0, 0, '', '', 'กันทรารมย์', '', '', '', '2025-07-28 06:34:16', 0),
(21, 'วัดบ้านหนองขามใหญ่', 'ศูนย์พักพิง', 0, 0, '', '', 'กันทรารมย์', '', '', '', '2025-07-28 06:34:16', 0),
(22, 'วัดบ้านหนองม่วง(หนองหัวช้าง) ต.หนองหัวช้าง', 'ศูนย์พักพิง', 0, 0, '', '', 'กันทรารมย์', '', '', '', '2025-07-28 06:34:16', 0),
(23, 'วัดบ้านหนองถ่ม ต.หนองหัวช้าง', 'ศูนย์พักพิง', 0, 0, '', '', 'กันทรารมย์', '', '', '', '2025-07-28 06:34:16', 0),
(24, 'วัดบ้านหนองถ่ม ต.ดู่', 'ศูนย์พักพิง', 0, 0, '', '', 'กันทรารมย์', '', '', '', '2025-07-28 06:34:16', 0),
(25, 'วัดบ้านหนองม่วง ต.ดู่', 'ศูนย์พักพิง', 0, 0, '', '', 'กันทรารมย์', '', '', '', '2025-07-28 06:34:16', 0),
(26, 'วัดบ้านนาดี ต.ผักแพว', 'ศูนย์พักพิง', 0, 0, '', '', 'กันทรารมย์', '', '', '', '2025-07-28 06:34:16', 0),
(27, 'วัดบ้านจาน ต.จาน', 'ศูนย์พักพิง', 0, 0, '', '', 'กันทรารมย์', '', '', '', '2025-07-28 06:34:16', 0),
(28, 'อบต. เมืองน้อย', 'ศูนย์พักพิง', 0, 0, '', '', 'กันทรารมย์', '', '', '', '2025-07-28 06:34:16', 0),
(29, 'วัดสระเยาว์ ต.สระเยาว์', 'ศูนย์พักพิง', 0, 0, '', '', 'ศรีรัตนะ', '', '', '', '2025-07-28 06:34:16', 0),
(30, 'วัดบ้านจอก ต.สะพุง', 'ศูนย์พักพิง', 0, 0, '', '', 'ศรีรัตนะ', '', '', '', '2025-07-28 06:34:16', 0),
(31, 'รร.ศรีรัตนวิทยา ต.สะพุง', 'ศูนย์พักพิง', 0, 0, '', '', 'ศรีรัตนะ', '', '', '', '2025-07-28 06:34:16', 0),
(32, 'วัดบ้านศรีแก้ว ต.ศรีแก้ว', 'ศูนย์พักพิง', 0, 0, '', '', 'ศรีรัตนะ', '', '', '', '2025-07-28 06:34:16', 0),
(33, 'รร.บ้านศรีแก้ว ต.ศรีแก้ว', 'ศูนย์พักพิง', 0, 0, '', '', 'ศรีรัตนะ', '', '', '', '2025-07-28 06:34:16', 0),
(34, 'วัดบ้านไฮ ต.ตูม', 'ศูนย์พักพิง', 0, 0, '', '', 'ศรีรัตนะ', '', '', '', '2025-07-28 06:34:16', 0),
(35, 'วัดป่าศรีสว่าง ต.ตูม', 'ศูนย์พักพิง', 0, 0, '', '', 'ศรีรัตนะ', '', '', '', '2025-07-28 06:34:16', 0),
(36, 'วัดสำโรงระวี (เขตเทศบาลตำบลศรีรัตนะ) ต.ศรีแก้ว', 'ศูนย์พักพิง', 0, 0, '', '', 'ศรีรัตนะ', '', '', '', '2025-07-28 06:34:16', 0),
(37, 'รร.พิงพวย(เสียงราษฎร์พัฒนา) ต.พิงพวย', 'ศูนย์พักพิง', 0, 0, '', '', 'ศรีรัตนะ', '', '', '', '2025-07-28 06:34:16', 0),
(38, 'รร.บ้านเสื่องข้าว ต.เสื่องข้าว', 'ศูนย์พักพิง', 0, 0, '', '', 'ศรีรัตนะ', '', '', '', '2025-07-28 06:34:16', 0),
(39, 'วัดตาเหมา ต.ศรีโนนงาม', 'ศูนย์พักพิง', 0, 0, '', '', 'ศรีรัตนะ', '', '', '', '2025-07-28 06:34:16', 0),
(40, 'วัดภูทอง', 'ศูนย์พักพิง', 0, 0, '', '', 'ขุนหาญ', '', '', '', '2025-07-28 06:34:16', 0),
(41, 'วัดหนองจิก', 'ศูนย์พักพิง', 0, 0, '', '', 'ขุนหาญ', '', '', '', '2025-07-28 06:34:16', 0),
(42, 'วัดบ้านพอก', 'ศูนย์พักพิง', 0, 0, '', '', 'ขุนหาญ', '', '', '', '2025-07-28 06:34:16', 0),
(43, 'ศูนย์วัดกระมัลพัฒนา', 'ศูนย์พักพิง', 0, 0, '', '', 'ขุนหาญ', '', '', '', '2025-07-28 06:34:16', 0),
(44, 'ศูนย์วัดบ้านเดื่อ', 'ศูนย์พักพิง', 0, 0, '', '', 'ขุนหาญ', '', '', '', '2025-07-28 06:34:16', 0),
(45, 'ศูนย์วัดระหาร', 'ศูนย์พักพิง', 0, 0, '', '', 'ขุนหาญ', '', '', '', '2025-07-28 06:34:16', 0),
(46, 'ศูนย์วัดกระมัลพัฒนา', 'ศูนย์พักพิง', 0, 0, '', '', 'ขุนหาญ', '', '', '', '2025-07-28 06:34:16', 0),
(47, 'ศุนย์วัดโพธิ์วงศ์', 'ศูนย์พักพิง', 0, 0, '', '', 'ขุนหาญ', '', '', '', '2025-07-28 06:34:16', 0),
(48, 'ศูนย์วัดกระเบาะเดื่อ', 'ศูนย์พักพิง', 0, 0, '', '', 'ขุนหาญ', '', '', '', '2025-07-28 06:34:16', 0),
(49, 'ศูนย์อรุณสว่าง', 'ศูนย์พักพิง', 0, 0, '', '', 'ขุนหาญ', '', '', '', '2025-07-28 06:34:16', 0),
(50, 'ศูนย์กุดนาแก้ว', 'ศูนย์พักพิง', 0, 0, '', '', 'ขุนหาญ', '', '', '', '2025-07-28 06:34:16', 0),
(51, 'ศูนย์โคกพยอม', 'ศูนย์พักพิง', 0, 0, '', '', 'ขุนหาญ', '', '', '', '2025-07-28 06:34:16', 0),
(52, 'ศุนย์วัดโคกระเวียง', 'ศูนย์พักพิง', 0, 0, '', '', 'ขุนหาญ', '', '', '', '2025-07-28 06:34:16', 0),
(53, 'ศูนย์วัดโพธิ์น้อย', 'ศูนย์พักพิง', 0, 0, '', '', 'ขุนหาญ', '', '', '', '2025-07-28 06:34:16', 0),
(54, 'ศูย์วัดบ้านปุน', 'ศูนย์พักพิง', 0, 0, '', '', 'ขุนหาญ', '', '', '', '2025-07-28 06:34:16', 0),
(55, 'หอประชุมโนนคูณ', 'ศูนย์พักพิง', 0, 0, '', '', 'โนนคูณ', '', '', '', '2025-07-28 06:34:16', 0),
(56, 'ศาลาวัดบ้านหนองดินดำ ต.บก', 'ศูนย์พักพิง', 0, 0, '', '', 'โนนคูณ', '', '', '', '2025-07-28 06:34:16', 0),
(57, 'ศาลาวัดทักษิณธรรมนิเวศน์ (หลังที่ 1)', 'ศูนย์พักพิง', 0, 0, '', '', 'โนนคูณ', '', '', '', '2025-07-28 06:34:16', 0),
(58, 'ศาลาวัดบ้านหนองกุง', 'ศูนย์พักพิง', 0, 0, '', '', 'โนนคูณ', '', '', '', '2025-07-28 06:34:16', 0),
(59, 'ศาลาวัดบ้านหนองปลาเข็ง ต.โพธิ์', 'ศูนย์พักพิง', 0, 0, '', '', 'โนนคูณ', '', '', '', '2025-07-28 06:34:16', 0),
(60, 'ศาลาวัดบ้านหนองสนม ต.เหล่ากวาง', 'ศูนย์พักพิง', 0, 0, '', '', 'โนนคูณ', '', '', '', '2025-07-28 06:34:16', 0),
(61, 'ศาลาวัดทักษิณธรรมนิเวศน์(หลังที่ 2)', 'ศูนย์พักพิง', 0, 0, '', '', 'โนนคูณ', '', '', '', '2025-07-28 06:34:16', 0),
(62, 'ศาลาวัดบ้านม่วงเป ต.หนองกุง', 'ศูนย์พักพิง', 0, 0, '', '', 'โนนคูณ', '', '', '', '2025-07-28 06:34:16', 0),
(63, 'ศาลาวัดบ้านโพธิ์ ต.โพธิ์', 'ศูนย์พักพิง', 0, 0, '', '', 'โนนคูณ', '', '', '', '2025-07-28 06:34:16', 0),
(64, 'ศาลาวัดบ้านเหล่าเสน ต.บก', 'ศูนย์พักพิง', 0, 0, '', '', 'โนนคูณ', '', '', '', '2025-07-28 06:34:16', 0),
(65, 'ศาลาวัดบ้านเวาะ ต.เหล่ากวาง', 'ศูนย์พักพิง', 0, 0, '', '', 'โนนคูณ', '', '', '', '2025-07-28 06:34:16', 0),
(66, 'ศาลาวัดบ้านปลาข่อ', 'ศูนย์พักพิง', 0, 0, '', '', 'โนนคูณ', '', '', '', '2025-07-28 06:34:16', 0),
(67, 'ศาลาวัดสุจันวราราม ม.16 ต.หนองกุง', 'ศูนย์พักพิง', 0, 0, '', '', 'โนนคูณ', '', '', '', '2025-07-28 06:34:16', 0),
(68, 'ที่พักสงฆ์ บ้านเหล่าเชือก ต.โนนค้อ', 'ศูนย์พักพิง', 0, 2291, '', '', 'โนนคูณ', '', '', '', '2025-07-28 06:34:16', 0),
(69, 'ศาลาวัดบ้านหนองสำราญ ต.โนนค้อ', 'ศูนย์พักพิง', 0, 0, '', '', 'โนนคูณ', '', '', '', '2025-07-28 06:34:16', 0),
(70, 'ศาลาวัดบ้านหัวเหล่า ต.บก', 'ศูนย์พักพิง', 0, 0, '', '', 'โนนคูณ', '', '', '', '2025-07-28 06:34:16', 0),
(71, 'วัดกระแซง ตำบลตำแย', 'ศูนย์พักพิง', 0, 0, '', '', 'พยุห์', '', '', '', '2025-07-28 06:34:16', 0),
(72, 'วัดสำโรง ตำบลพรหมสวัสดิ์', 'ศูนย์พักพิง', 0, 0, '', '', 'พยุห์', '', '', '', '2025-07-28 06:34:16', 0),
(73, 'วัดตำแย ตำบลตำแย', 'ศูนย์พักพิง', 0, 0, '', '', 'พยุห์', '', '', '', '2025-07-28 06:34:16', 0),
(74, 'วัดโนนพยอม ตำบลพยุห์', 'ศูนย์พักพิง', 0, 0, '', '', 'พยุห์', '', '', '', '2025-07-28 06:34:16', 0),
(75, 'วัดพยุห์มงคลัตนาราม ตำบลพยุห์', 'ศูนย์พักพิง', 0, 0, '', '', 'พยุห์', '', '', '', '2025-07-28 06:34:16', 0),
(76, 'วัดคูเมือง ตำบลพยุห์', 'ศูนย์พักพิง', 0, 0, '', '', 'พยุห์', '', '', '', '2025-07-28 06:34:16', 0),
(77, 'วัดหนองทุ่ม ตำบลพยุห์', 'ศูนย์พักพิง', 0, 0, '', '', 'พยุห์', '', '', '', '2025-07-28 06:34:16', 0),
(78, 'หอประชุมอำเภอพยุห์', 'ศูนย์พักพิง', 0, 0, '', '', 'พยุห์', '', '', '', '2025-07-28 06:34:16', 0),
(79, 'อาคารอเนกประสงค์', 'ศูนย์พักพิง', 0, 0, '', '', 'พยุห์', '', '', '', '2025-07-28 06:34:16', 0),
(80, 'อบต.พยุห์', 'ศูนย์พักพิง', 0, 0, '', '', 'พยุห์', '', '', '', '2025-07-28 06:34:16', 0),
(81, 'อาคารอเนกประสงค์', 'ศูนย์พักพิง', 0, 0, '', '', 'พยุห์', '', '', '', '2025-07-28 06:34:16', 0),
(82, 'อบต.ตำแย', 'ศูนย์พักพิง', 0, 0, '', '', 'พยุห์', '', '', '', '2025-07-28 06:34:16', 0),
(83, 'วัดโพธิ์ศรี ต.โนนเพ็ก', 'ศูนย์พักพิง', 0, 0, '', '', 'พยุห์', '', '', '', '2025-07-28 06:34:16', 0),
(84, 'วัดโนนเพ็ก ต.โนนเพ็ก', 'ศูนย์พักพิง', 0, 0, '', '', 'พยุห์', '', '', '', '2025-07-28 06:34:16', 0),
(85, 'วัดบ้านบก ต.หนองค้า', 'ศูนย์พักพิง', 0, 0, '', '', 'พยุห์', '', '', '', '2025-07-28 06:34:16', 0),
(86, 'วััดเสมอใจ ต.หนองค้า', 'ศูนย์พักพิง', 0, 0, '', '', 'พยุห์', '', '', '', '2025-07-28 06:34:16', 0),
(87, 'วัดโพธิ์น้อย ต.หนองค้า', 'ศูนย์พักพิง', 0, 0, '', '', 'พยุห์', '', '', '', '2025-07-28 06:34:16', 0);

-- --------------------------------------------------------

--
-- Table structure for table `shelter_logs`
--

CREATE TABLE `shelter_logs` (
  `id` int(11) NOT NULL,
  `shelter_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_unit` varchar(50) NOT NULL,
  `change_amount` int(11) NOT NULL,
  `log_type` enum('add','subtract') NOT NULL,
  `new_total` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shelter_logs`
--

INSERT INTO `shelter_logs` (`id`, `shelter_id`, `item_name`, `item_unit`, `change_amount`, `log_type`, `new_total`, `created_at`) VALUES
(30, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 2, 'add', 2, '2025-07-28 07:11:36'),
(31, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 2, 'add', 4, '2025-07-28 07:12:26'),
(32, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 522, 'add', 526, '2025-07-28 10:21:00'),
(33, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 200, 'add', 726, '2025-07-28 10:21:42'),
(34, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 3, 'add', 729, '2025-07-28 10:25:47'),
(35, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 1000, 'add', 1729, '2025-07-28 10:36:50'),
(36, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 1, 'add', 1730, '2025-07-28 10:38:20'),
(37, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 1, 'add', 1731, '2025-07-28 10:38:29'),
(38, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 1, 'subtract', 1730, '2025-07-28 10:38:51'),
(39, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 5, 'add', 1735, '2025-07-28 10:44:28'),
(40, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 5, 'add', 1740, '2025-07-28 10:44:31'),
(41, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 3, 'add', 1743, '2025-07-28 10:44:46'),
(42, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 3, 'add', 1746, '2025-07-28 10:45:02'),
(43, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 1, 'add', 1747, '2025-07-28 10:46:15'),
(44, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 2, 'add', 1749, '2025-07-28 10:46:55'),
(45, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 10, 'add', 1759, '2025-07-28 10:53:32'),
(46, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 10, 'add', 1769, '2025-07-28 10:53:34'),
(47, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 1, 'add', 1770, '2025-07-28 10:53:48'),
(48, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 1, 'subtract', 1769, '2025-07-28 11:00:52'),
(49, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 1, 'add', 1770, '2025-07-28 11:01:04'),
(50, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 1, 'add', 1771, '2025-07-28 11:01:07'),
(51, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 1, 'subtract', 1770, '2025-07-28 11:01:10'),
(52, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 1, 'subtract', 1769, '2025-07-28 11:01:23'),
(53, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 1, 'subtract', 1768, '2025-07-28 11:02:11'),
(54, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 1, 'subtract', 1767, '2025-07-28 11:02:14'),
(55, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 1, 'subtract', 1766, '2025-07-28 11:02:23'),
(56, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 1, 'add', 1767, '2025-07-28 11:06:40'),
(57, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 1, 'add', 1768, '2025-07-28 11:08:45'),
(58, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 2, 'add', 1770, '2025-07-28 11:12:59'),
(59, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 1, 'add', 1771, '2025-07-28 11:22:53'),
(60, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 1, 'add', 1772, '2025-07-28 11:23:11'),
(61, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 1, 'add', 1773, '2025-07-28 11:28:16'),
(62, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 2, 'add', 1775, '2025-07-28 11:37:21'),
(63, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 500, 'add', 2275, '2025-07-28 11:39:32'),
(64, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 1, 'add', 2276, '2025-07-28 11:52:02'),
(65, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 1, 'add', 2277, '2025-07-28 11:52:20'),
(66, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 1, 'add', 2278, '2025-07-28 11:55:33'),
(67, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 1, 'add', 2279, '2025-07-28 11:55:35'),
(68, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 1, 'add', 2280, '2025-07-28 11:56:08'),
(69, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 1, 'add', 2281, '2025-07-28 12:01:23'),
(70, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 1, 'subtract', 2280, '2025-07-28 12:01:26'),
(71, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 1, 'subtract', 2279, '2025-07-28 12:01:28'),
(72, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 2, 'add', 2281, '2025-07-28 12:08:42'),
(73, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 3, 'subtract', 2278, '2025-07-28 12:08:49'),
(74, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 2, 'add', 2280, '2025-07-29 02:12:24'),
(75, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 2, 'subtract', 2278, '2025-07-29 02:12:32'),
(76, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 11, 'add', 2289, '2025-07-29 02:29:35'),
(77, 68, 'ผู้เข้าพัก (ศูนย์พักพิง)', 'คน', 2, 'add', 2291, '2025-07-29 02:49:53');

-- --------------------------------------------------------

--
-- Table structure for table `shelter_occupant_details`
--

CREATE TABLE `shelter_occupant_details` (
  `id` int(11) NOT NULL,
  `shelter_id` int(11) DEFAULT NULL,
  `male` int(11) NOT NULL,
  `female` int(11) NOT NULL,
  `pregnant` int(11) DEFAULT 0,
  `disabled` int(11) DEFAULT 0,
  `bedridden` int(11) DEFAULT 0,
  `elderly` int(11) DEFAULT 0,
  `children` int(11) DEFAULT 0,
  `diabetes` int(11) DEFAULT 0,
  `hypertension` int(11) DEFAULT 0,
  `heart_disease` int(11) DEFAULT 0,
  `psychiatric` int(11) DEFAULT 0,
  `kidney_dialysis` int(11) DEFAULT 0,
  `other_conditions` text DEFAULT NULL,
  `status` enum('เพิ่ม','ลด') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_shelters`
--

CREATE TABLE `staff_shelters` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `shelter_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Coordinator','HealthStaff','User') NOT NULL DEFAULT 'User',
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `assigned_shelter_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `status`, `assigned_shelter_id`, `created_at`) VALUES
(2, 'ปฐวีกานต์ ศรีคราม', 'adminmax@gmail.com', '$2y$10$h5z6a4fhD3KMDcxKPApdMOTfk38eWbC/mYK5WsI4tblnCHggS2Xya', 'Admin', 'Active', NULL, '2025-07-27 07:02:49'),
(6, 'พยาบาล', 'nurse@mail.com', '$2y$10$jcX.89PPtIPqFrDgSWHLKOnX4qlf.qctqP7derxCWBw5pLnT77NRu', 'HealthStaff', 'Active', NULL, '2025-07-27 13:28:21'),
(7, 'PaPa', 'admin@gmail.com', '$2y$10$VbHrbefoXyUEqCExJskzm.EuAuXtZ7w0/M9eUymHog8l/wdJZeaMO', 'Coordinator', 'Active', NULL, '2025-07-28 04:32:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `healthstaff_shelters`
--
ALTER TABLE `healthstaff_shelters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_assignment` (`user_id`,`shelter_id`),
  ADD KEY `shelter_id` (`shelter_id`);

--
-- Indexes for table `hospital_daily_reports`
--
ALTER TABLE `hospital_daily_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_shelter_date` (`shelter_id`,`report_date`),
  ADD KEY `idx_report_date` (`report_date`),
  ADD KEY `idx_shelter_date` (`shelter_id`,`report_date`);

--
-- Indexes for table `hospital_update_logs`
--
ALTER TABLE `hospital_update_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_id` (`report_id`),
  ADD KEY `shelter_id` (`shelter_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `occupant_update_logs`
--
ALTER TABLE `occupant_update_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_shelter_id` (`shelter_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_log_timestamp` (`log_timestamp`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `shelters`
--
ALTER TABLE `shelters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shelter_logs`
--
ALTER TABLE `shelter_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shelter_id` (`shelter_id`);

--
-- Indexes for table `shelter_occupant_details`
--
ALTER TABLE `shelter_occupant_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shelter_id` (`shelter_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `staff_shelters`
--
ALTER TABLE `staff_shelters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `shelter_id` (`shelter_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `users_ibfk_1` (`assigned_shelter_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `healthstaff_shelters`
--
ALTER TABLE `healthstaff_shelters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `hospital_daily_reports`
--
ALTER TABLE `hospital_daily_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `hospital_update_logs`
--
ALTER TABLE `hospital_update_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `occupant_update_logs`
--
ALTER TABLE `occupant_update_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shelters`
--
ALTER TABLE `shelters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `shelter_logs`
--
ALTER TABLE `shelter_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `shelter_occupant_details`
--
ALTER TABLE `shelter_occupant_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_shelters`
--
ALTER TABLE `staff_shelters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `healthstaff_shelters`
--
ALTER TABLE `healthstaff_shelters`
  ADD CONSTRAINT `healthstaff_shelters_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `healthstaff_shelters_ibfk_2` FOREIGN KEY (`shelter_id`) REFERENCES `shelters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hospital_daily_reports`
--
ALTER TABLE `hospital_daily_reports`
  ADD CONSTRAINT `hospital_daily_reports_ibfk_1` FOREIGN KEY (`shelter_id`) REFERENCES `shelters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `occupant_update_logs`
--
ALTER TABLE `occupant_update_logs`
  ADD CONSTRAINT `fk_log_shelter` FOREIGN KEY (`shelter_id`) REFERENCES `shelters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `shelter_logs`
--
ALTER TABLE `shelter_logs`
  ADD CONSTRAINT `shelter_logs_ibfk_1` FOREIGN KEY (`shelter_id`) REFERENCES `shelters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `shelter_occupant_details`
--
ALTER TABLE `shelter_occupant_details`
  ADD CONSTRAINT `shelter_occupant_details_ibfk_1` FOREIGN KEY (`shelter_id`) REFERENCES `shelters` (`id`),
  ADD CONSTRAINT `shelter_occupant_details_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `staff_shelters`
--
ALTER TABLE `staff_shelters`
  ADD CONSTRAINT `staff_shelters_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `staff_shelters_ibfk_2` FOREIGN KEY (`shelter_id`) REFERENCES `shelters` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`assigned_shelter_id`) REFERENCES `shelters` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
