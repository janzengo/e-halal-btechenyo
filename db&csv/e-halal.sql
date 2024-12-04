-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 02, 2024 at 07:05 AM
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
-- Database: `e-halal`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(60) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `photo` varchar(150) NOT NULL,
  `created_on` date NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'officer',
  `gender` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `firstname`, `lastname`, `photo`, `created_on`, `role`, `gender`) VALUES
(1, 'wallysabangan2024', '$2y$10$JJyv3iRlgpbEeHKlyRZIOeRvtuLcskovhJn8vZvExSLNmrOj2uyVO', 'Wally', 'Sabangan', 'profile.jpg', '2024-06-06', 'superadmin', 'Male'),
(2, 'dejesus', '$2y$10$aUdUxJ/vc8Gm/Sc8NhLDouuHZo0DWXIDQDvueQ3byii815n0Xn85W', 'Reanne', 'De Jesus', 'profile.jpg', '2024-06-06', 'officer', 'Female'),
(4, 'janzengo', '$2y$10$CAB8lAW3uxD.y1dYWGbodOkGRJigRk8WNaY35NjG.NVQpGElndHNC', 'Janzen', 'Go', 'profile.jpg', '2024-06-11', 'officer', 'Male');

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `firstname` varchar(30) NOT NULL,
  `lastname` varchar(30) NOT NULL,
  `partylist_id` int(11) DEFAULT NULL,
  `photo` varchar(150) NOT NULL,
  `platform` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`id`, `position_id`, `firstname`, `lastname`, `partylist_id`, `photo`, `platform`) VALUES
(2, 1, 'Janzen', 'Go', 4, '', 'I am Janzen Go.'),
(3, 1, 'Steph', 'Curry', 9, '', 'I\'m Stephen Curry.');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `description`) VALUES
(1, 'Bachelor of Science in Information Technology'),
(2, 'Bachelor of Science in Hospitality Management'),
(3, 'Bachelor of Science in Tourism Management'),
(4, 'Bachelor of Science in Accountancy'),
(5, 'BSBA Major in Financial Management'),
(6, 'BSBA Major in Business Economics'),
(7, 'BSBA Major in Marketing Management'),
(8, 'BSBA Major in Human Resource Management'),
(9, 'Bachelor of Science in Entrepreneurship'),
(10, 'Bachelor of Science in Mathematics'),
(11, 'Bachelor of Arts in History'),
(12, 'Bachelor of Secondary Education'),
(13, 'Bachelor of Elementary Education'),
(14, 'Bachelor of Science in Management Accounting'),
(15, 'Bachelor of Science in Internal Auditing');

-- --------------------------------------------------------

--
-- Table structure for table `election_history`
--

CREATE TABLE `election_history` (
  `id` int(11) NOT NULL,
  `election_name` varchar(255) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `details_pdf` varchar(255) NOT NULL,
  `results_pdf` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `election_history`
--

INSERT INTO `election_history` (`id`, `election_name`, `start_date`, `end_date`, `details_pdf`, `results_pdf`, `created_at`) VALUES
(1, '2024 Student Council Election', '2024-08-01 08:00:00', '2024-08-02 18:00:00', '/2024-student-council-election/details.pdf', '/2024-student-council-election/results.pdf', '2024-09-04 11:30:46');

-- --------------------------------------------------------

--
-- Table structure for table `election_status`
--

CREATE TABLE `election_status` (
  `id` int(11) NOT NULL,
  `status` enum('on','off','paused','pending') NOT NULL,
  `election_name` varchar(255) NOT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `election_status`
--

INSERT INTO `election_status` (`id`, `status`, `election_name`, `start_time`, `end_time`) VALUES
(1, 'pending', '2026 Sangguaniang Mag-aaral Elections', NULL, '2024-09-15 14:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `username` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `role` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `timestamp`, `username`, `details`, `role`) VALUES
(1, '2024-08-03 02:20:23', 'wallysabangan2024', 'Successful login', 'superadmin'),
(2, '2024-08-03 03:15:55', 'wallysabangan2024', 'Successful login', 'superadmin'),
(3, '2024-08-03 06:20:37', 'wallysabangan2024', 'Successful login', 'superadmin'),
(4, '2024-08-03 14:27:11', 'wallysabangan2024', 'Successful login', 'superadmin'),
(5, '2024-08-03 14:27:25', 'wallysabangan2024', 'Deleted position: Vice President', 'superadmin'),
(6, '2024-08-03 14:43:57', 'wallysabangan2024', 'Added new President position with 1 maximum vote', 'superadmin'),
(7, '2024-08-03 14:46:32', 'wallysabangan2024', 'Deleted position: President', 'superadmin'),
(8, '2024-08-03 14:50:10', 'wallysabangan2024', 'Added new President position with 1 maximum vote', 'superadmin'),
(9, '2024-08-03 14:51:03', 'wallysabangan2024', 'Deleted position: President', 'superadmin'),
(10, '2024-08-03 14:54:47', 'wallysabangan2024', 'Added new President position with 1 maximum vote', 'superadmin'),
(11, '2024-08-03 14:55:57', 'wallysabangan2024', 'Deleted position: President', 'superadmin'),
(12, '2024-08-04 12:25:18', 'wallysabangan2024', 'Successful login', 'superadmin'),
(13, '2024-08-04 12:29:55', 'wallysabangan2024', 'Added new Vice President position with 2 maximum votes', 'superadmin'),
(14, '2024-08-04 13:17:47', 'wallysabangan2024', 'Successful login', 'superadmin'),
(15, '2024-08-04 13:20:09', 'wallysabangan2024', 'Successful login', 'superadmin'),
(16, '2024-08-04 13:29:19', 'wallysabangan2024', 'Logged out', 'superadmin'),
(17, '2024-08-04 13:29:22', 'wallysabangan2024', 'Successful login', 'superadmin'),
(18, '2024-08-04 13:31:35', 'wallysabangan2024', 'Logged out', 'superadmin'),
(19, '2024-08-04 13:31:38', 'wallysabangan2024', 'Successful login', 'superadmin'),
(20, '2024-08-04 13:32:38', 'wallysabangan2024', 'Successful login', 'superadmin'),
(21, '2024-08-06 02:06:25', 'wallysabangan2024', 'Successful login', 'superadmin'),
(22, '2024-08-06 02:07:21', 'wallysabangan2024', 'Deleted position: Vice President', 'superadmin'),
(23, '2024-08-06 02:40:46', 'wallysabangan2024', 'Added new President position with 1 maximum vote', 'superadmin'),
(24, '2024-08-06 02:50:55', 'wallysabangan2024', 'Deleted position: President', 'superadmin'),
(25, '2024-08-06 02:51:03', 'wallysabangan2024', 'Added new President position with 1 maximum vote', 'superadmin'),
(26, '2024-08-06 03:32:46', 'wallysabangan2024', 'Added new Vice President position with 1 maximum vote', 'superadmin'),
(27, '2024-08-06 03:32:52', 'wallysabangan2024', 'Added new Secretary position with 1 maximum vote', 'superadmin'),
(28, '2024-08-06 03:32:56', 'wallysabangan2024', 'Added new PIO position with 1 maximum vote', 'superadmin'),
(29, '2024-08-06 03:33:06', 'wallysabangan2024', 'Added new Treasurer position with 1 maximum vote', 'superadmin'),
(30, '2024-08-06 03:33:25', 'wallysabangan2024', 'Added new Auditor position with 1 maximum vote', 'superadmin'),
(31, '2024-08-06 03:33:34', 'wallysabangan2024', 'Added new 1st Year Representative position with 1 maximum vote', 'superadmin'),
(32, '2024-08-06 03:33:48', 'wallysabangan2024', 'Added new 2nd Year Representative position with 1 maximum vote', 'superadmin'),
(33, '2024-08-06 03:33:57', 'wallysabangan2024', 'Added new 3rd Year Representative position with 1 maximum vote', 'superadmin'),
(34, '2024-08-06 03:34:05', 'wallysabangan2024', 'Added new 4th Year Representative position with 1 maximum vote', 'superadmin'),
(35, '2024-08-06 03:43:02', 'wallysabangan2024', 'Added new candidate: Alona Samson for President under Sandigan partylist', 'superadmin'),
(36, '2024-08-06 04:12:30', 'wallysabangan2024', 'Successful login', 'superadmin'),
(37, '2024-08-06 07:12:03', 'wallysabangan2024', 'Deleted partylist: Independent', 'superadmin'),
(38, '2024-08-06 07:14:50', 'wallysabangan2024', 'Added new partylist: Independent', 'superadmin'),
(39, '2024-08-06 07:18:16', 'wallysabangan2024', 'Updated Partylist: Independent to Independence', 'superadmin'),
(40, '2024-08-06 07:18:47', 'wallysabangan2024', 'Updated Partylist: Independence to Independent', 'superadmin'),
(41, '2024-08-06 07:19:09', 'wallysabangan2024', 'Deleted partylist: Independent', 'superadmin'),
(42, '2024-08-06 07:19:17', 'wallysabangan2024', 'Added new partylist: Independent', 'superadmin'),
(43, '2024-08-06 07:23:01', 'wallysabangan2024', 'Added new partylist: Democrats', 'superadmin'),
(44, '2024-08-06 07:32:34', 'wallysabangan2024', 'Successful login', 'superadmin'),
(45, '2024-08-06 07:33:02', 'wallysabangan2024', 'Added new partylist: Republicans', 'superadmin'),
(46, '2024-08-06 07:33:10', 'wallysabangan2024', 'Deleted partylist: Democrats', 'superadmin'),
(47, '2024-08-06 07:33:16', 'wallysabangan2024', 'Deleted partylist: Republicans', 'superadmin'),
(48, '2024-08-06 07:52:11', 'wallysabangan2024', 'Added new partylist: Democratic', 'superadmin'),
(49, '2024-08-06 08:18:34', 'wallysabangan2024', 'Attempted to add partylist without filling up the form', 'superadmin'),
(50, '2024-08-06 08:21:55', 'wallysabangan2024', 'Successful login', 'superadmin'),
(51, '2024-08-06 08:22:13', 'wallysabangan2024', 'Deleted partylist: Democratic', 'superadmin'),
(52, '2024-08-06 08:32:55', 'wallysabangan2024', 'Successful login', 'superadmin'),
(53, '2024-08-06 08:38:14', 'wallysabangan2024', 'Successful login', 'superadmin'),
(54, '2024-08-06 08:38:27', 'unknown', 'Attempted to add position without filling up the form', 'unknown'),
(55, '2024-08-06 08:43:56', 'wallysabangan2024', 'Deleted partylist: Democrats', 'superadmin'),
(56, '2024-08-06 08:45:21', 'wallysabangan2024', 'Added new partylist: Democrats', 'superadmin'),
(57, '2024-08-08 03:30:05', 'wallysabangan2024', 'Successful login', 'superadmin'),
(58, '2024-09-03 01:51:53', 'wallysabangan24', 'Failed login attempt: username not found', 'unknown'),
(59, '2024-09-03 01:52:54', 'wallysabangan2024', 'Successful login', 'superadmin'),
(60, '2024-09-03 03:09:25', 'wallysabangan2024', 'Added new candidate: Janzen Go for President under Independent partylist', 'superadmin'),
(61, '2024-09-03 03:23:19', 'wallysabangan2024', 'Added new PO position with 2 maximum votes', 'superadmin'),
(62, '2024-09-03 03:30:25', 'wallysabangan2024', 'Updated Candidate: Alona Samson (Position: President, Partylist: Sandigan) to Thea Miranda (Position: President, Partylist: Sandigan)', 'superadmin'),
(63, '2024-09-03 03:30:57', 'wallysabangan2024', 'Updated Partylist: Democrats to Democrat', 'superadmin'),
(64, '2024-09-03 03:31:47', 'wallysabangan2024', 'Added new candidate: Steph Curry for President under Democrat partylist', 'superadmin'),
(65, '2024-09-03 03:31:53', 'wallysabangan2024', 'Updated Partylist: Democrat to Democrats', 'superadmin'),
(66, '2024-09-03 03:44:51', 'wallysabangan2024', 'Logged out', 'superadmin'),
(67, '2024-09-03 03:44:52', 'wallysabangan2024', 'Successful login', 'superadmin'),
(68, '2024-09-03 04:09:04', 'wallysabangan2024', 'Successful login', 'superadmin'),
(69, '2024-09-04 04:02:27', 'wallysabangan2024', 'Successful login', 'superadmin'),
(70, '2024-09-04 07:08:05', 'wallysabangan2024', 'Successful login', 'superadmin'),
(71, '2024-09-04 08:03:58', 'wallysabangan2024', 'Logged out', 'superadmin'),
(72, '2024-09-04 08:04:05', 'justinethea', 'Successful login', 'officer'),
(73, '2024-09-04 11:37:32', 'wallysabangan2024', 'Successful login', 'superadmin'),
(74, '2024-09-04 11:46:30', 'wallysabangan2024', 'Logged out', 'superadmin'),
(75, '2024-09-04 11:51:48', 'wallysabangan2024', 'Successful login', 'superadmin'),
(76, '2024-09-05 02:46:27', 'wallysabangan2024', 'Successful login', 'superadmin'),
(77, '2024-09-05 03:56:02', 'wallysabangan2024', 'Successful login', 'superadmin'),
(78, '2024-09-05 06:34:22', 'wallysabangan2024', 'Successful login', 'superadmin'),
(79, '2024-09-05 07:05:44', 'wallysabangan2024', 'Successful login', 'superadmin'),
(80, '2024-09-05 07:22:08', 'wallysabangan2024', 'Successful login', 'superadmin'),
(81, '2024-09-05 07:29:04', 'wallysabangan2024', 'Successful login', 'superadmin'),
(82, '2024-09-05 07:40:55', 'wallysabangan2024', 'Added several voters: Janzen Go (202320023), Alona Go (202320025), Justin Go (202320254), Mayeth Go (202520025), John Doe (202320001), Jane Smith (202320002), Bob Brown (202320003), Alice Johnson (202320004), Tom White (202320005), Emma Clark (202320006)', 'superadmin'),
(83, '2024-09-05 08:02:22', 'wallysabangan2024', 'Deleted several voters: Janzen Go (202320023), Alona Go (202320025), Justin Go (202320254), Mayeth Go (202520025), John Doe (202320001), Jane Smith (202320002), Bob Brown (202320003), Alice Johnson (202320004), Tom White (202320005), Emma Clark (202320006)', 'superadmin'),
(84, '2024-09-05 08:02:30', 'wallysabangan2024', 'Added several voters: Janzen Go (202320023), Justine Thea Go (202320025), Justin Go (202320254), Mayeth Go (202520025), John Doe (202320001), Jane Smith (202320002), Bob Brown (202320003), Alice Johnson (202320004), Tom White (202320005), Emma Clark (202320006)', 'superadmin'),
(85, '2024-09-05 08:04:47', 'wallysabangan2024', 'Deleted several voters: Janzen Go (202320023), Justine Thea Go (202320025), Justin Go (202320254), Mayeth Go (202520025), John Doe (202320001), Jane Smith (202320002), Bob Brown (202320003), Alice Johnson (202320004), Tom White (202320005), Emma Clark (202320006)', 'superadmin'),
(86, '2024-09-05 08:04:51', 'wallysabangan2024', 'Added several voters: Janzen Go (202320023), Justine Thea Go (202320025), Justin Go (202320254), Mayeth Go (202520025), John Doe (202320001), Jane Smith (202320002), Bob Brown (202320003), Alice Johnson (202320004), Tom White (202320005), Emma Clark (202320006)', 'superadmin'),
(87, '2024-09-05 08:05:17', 'wallysabangan2024', 'Deleted several voters: Janzen Go (202320023), Justine Thea Go (202320025), Justin Go (202320254), Mayeth Go (202520025), John Doe (202320001), Jane Smith (202320002), Bob Brown (202320003), Alice Johnson (202320004), Tom White (202320005), Emma Clark (202320006)', 'superadmin'),
(88, '2024-09-05 08:05:21', 'wallysabangan2024', 'Added several voters: Janzen Go (202320023), Justine Thea Go (202320025), Justin Go (202320254), Mayeth Go (202520025), John Doe (202320001), Jane Smith (202320002), Bob Brown (202320003), Alice Johnson (202320004), Tom White (202320005), Emma Clark (202320006)', 'superadmin'),
(89, '2024-09-05 08:10:55', 'wallysabangan2024', 'Deleted several voters: Janzen Go (202320023), Justine Thea Go (202320025), Justin Go (202320254), Mayeth Go (202520025), John Doe (202320001), Jane Smith (202320002), Bob Brown (202320003), Alice Johnson (202320004), Tom White (202320005), Emma Clark (202320006)', 'superadmin'),
(90, '2024-09-05 08:11:21', 'wallysabangan2024', 'Added several voters: Janzen Go (202320023), Justine Thea Go (202320025), Justin Go (202320254), Mayeth Go (202520025), John Doe (202320001), Jane Smith (202320002), Bob Brown (202320003), Alice Johnson (202320004), Tom White (202320005), Emma Clark (202320006)', 'superadmin'),
(91, '2024-09-05 08:35:36', 'wallysabangan2024', 'Deleted several voters: Janzen Go (202320023), Justine Thea Go (202320025), Justin Go (202320254), Mayeth Go (202520025), John Doe (202320001), Jane Smith (202320002), Bob Brown (202320003), Alice Johnson (202320004), Tom White (202320005), Emma Clark (202320006)', 'superadmin'),
(92, '2024-09-05 08:35:42', 'wallysabangan2024', 'Added several voters: Janzen Go (202320023), Justine Thea Go (202320025), Justin Go (202320254), Mayeth Go (202520025), John Doe (202320001), Jane Smith (202320002), Bob Brown (202320003), Alice Johnson (202320004), Tom White (202320005), Emma Clark (202320006)', 'superadmin'),
(93, '2024-09-05 13:24:20', 'wallysabangan2024', 'Successful login', 'superadmin'),
(94, '2024-09-05 13:27:29', 'wallysabangan2024', 'Successful login', 'superadmin'),
(95, '2024-09-06 01:29:33', 'wallysabangan2024', 'Successful login', 'superadmin'),
(96, '2024-09-06 01:36:51', 'wallysabangan2024', 'Logged out', 'superadmin'),
(97, '2024-09-06 01:36:54', 'wallysabangan2024', 'Successful login', 'superadmin'),
(98, '2024-09-06 01:40:24', 'wallysabangan2024', 'Successful login', 'superadmin'),
(99, '2024-09-06 01:42:48', 'wallysabangan2024', 'Successful login', 'superadmin'),
(100, '2024-09-06 05:34:53', 'wallysabangan2024', 'Successful login', 'superadmin'),
(101, '2024-11-02 05:57:11', 'wallysabangan2024', 'Successful login', 'superadmin'),
(102, '2024-11-02 06:04:11', 'wallysabangan2024', 'Added new President position with 14 maximum votes', 'superadmin'),
(103, '2024-11-02 06:04:14', 'wallysabangan2024', 'Updated Position: President (Max Vote: 14) to President (Max Vote: 1)', 'superadmin'),
(104, '2024-11-02 06:04:22', 'wallysabangan2024', 'Added new Vice President position with 1 maximum vote', 'superadmin'),
(105, '2024-11-02 06:04:31', 'wallysabangan2024', 'Deleted candidate:   (Position: , Partylist: )', 'superadmin'),
(106, '2024-11-02 06:04:40', 'wallysabangan2024', 'Updated Candidate:   (Position: , Partylist: ) to Janzen Go (Position: President, Partylist: Independent) | Platform changed from: \'\' to: \'I am Janzen Go.\'', 'superadmin'),
(107, '2024-11-02 06:04:49', 'wallysabangan2024', 'Updated Candidate:   (Position: , Partylist: ) to Steph Curry (Position: President, Partylist: Democrats) | Platform changed from: \'\' to: \'I\'m Stephen Curry.\'', 'superadmin');

-- --------------------------------------------------------

--
-- Table structure for table `partylists`
--

CREATE TABLE `partylists` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `partylists`
--

INSERT INTO `partylists` (`id`, `name`) VALUES
(1, 'Sandigan'),
(4, 'Independent'),
(9, 'Democrats');

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `id` int(11) NOT NULL,
  `description` varchar(50) NOT NULL,
  `max_vote` int(11) NOT NULL,
  `priority` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`id`, `description`, `max_vote`, `priority`) VALUES
(1, 'President', 1, 1),
(2, 'Vice President', 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `voters`
--

CREATE TABLE `voters` (
  `id` int(11) NOT NULL,
  `voters_id` varchar(15) NOT NULL,
  `course_id` int(11) NOT NULL,
  `password` varchar(60) NOT NULL,
  `firstname` varchar(30) NOT NULL,
  `lastname` varchar(30) NOT NULL,
  `photo` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `voters`
--

INSERT INTO `voters` (`id`, `voters_id`, `course_id`, `password`, `firstname`, `lastname`, `photo`) VALUES
(51, '202320023', 2, '$2y$10$iJoUSUZANK1DU2qfl2XWWOc0vssPdwy6D5u8GUn/p0wEXUmuw4Gw.', 'Janzen', 'Go', 'profile.jpg'),
(52, '202320025', 8, '$2y$10$mqd1nU9oli0nKLt4awOKKOXNjl8vAqFZEyRl/3IuDBT2yequDcOE2', 'Justine Thea', 'Go', 'profile.jpg'),
(53, '202320254', 3, '$2y$10$qY.b.SUrbKH9x2qDOy0uTexUrqXvI4pJLibCAhsBQ9XYl8bWfpOOC', 'Justin', 'Go', 'profile.jpg'),
(54, '202520025', 4, '$2y$10$MyTnLQR0BCkmEmhWY89P3OGUlCQz.xAfnAXaxwo3FbnYBVsxga8Ja', 'Mayeth', 'Go', 'profile.jpg'),
(55, '202320001', 1, '$2y$10$FnebMwW6WEEBDcZS2u3qRe5nng.9Dem.bTE3.DAkn3Ztroqa0Mg9y', 'John', 'Doe', 'profile.jpg'),
(56, '202320002', 1, '$2y$10$idj2ERi1oR.C6dN7L3icEOPe6ZcsHtoWhLzteuQ3V8zWS3/qmClmi', 'Jane', 'Smith', 'profile.jpg'),
(57, '202320003', 2, '$2y$10$.VPkkjDuiHZlR/ERwgX3d.biJWO04eCxamFuRE3fN.pEGtA8S4Oo6', 'Bob', 'Brown', 'profile.jpg'),
(58, '202320004', 2, '$2y$10$hdwuMZPkg2Ud3Amgh8T37uSMv4XACwt1DCNOe8nhFXgifwjpE2Zfy', 'Alice', 'Johnson', 'profile.jpg'),
(59, '202320005', 3, '$2y$10$NrlbVKGcS/irvknm0R7mZeTHaFPRP.sfe9hra3uMhLO6t03pHhm0S', 'Tom', 'White', 'profile.jpg'),
(60, '202320006', 4, '$2y$10$mgfaFZ5JdRozk.iCYSSOLubV6zTB0yzX0YlWXOJYEw9JDoEW4qOHS', 'Emma', 'Clark', 'profile.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `id` int(11) NOT NULL,
  `voters_id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `votes`
--

INSERT INTO `votes` (`id`, `voters_id`, `candidate_id`, `position_id`) VALUES
(1, 51, 1, 34);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `election_history`
--
ALTER TABLE `election_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `election_status`
--
ALTER TABLE `election_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `partylists`
--
ALTER TABLE `partylists`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `voters`
--
ALTER TABLE `voters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `voters_id` (`voters_id`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `election_history`
--
ALTER TABLE `election_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `election_status`
--
ALTER TABLE `election_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `partylists`
--
ALTER TABLE `partylists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `voters`
--
ALTER TABLE `voters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
