-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 27, 2025 at 03:10 AM
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `firstname`, `lastname`, `photo`, `created_on`, `role`, `gender`) VALUES
(1, 'wallysabangan2024', '$2y$10$Z2JnQG/8o3lzk.CmLLr5yuL.focykBBCWcZEPLSBn6k.gY5hkRfwq', 'Wally', 'Sabangan', 'assets/images/administrators/superadmin_sabangan_wally.jpg', '2024-06-06', 'superadmin', 'Male'),
(2, 'dejesus', '$2y$10$aUdUxJ/vc8Gm/Sc8NhLDouuHZo0DWXIDQDvueQ3byii815n0Xn85W', 'Reanne', 'De Jesus', 'profile.jpg', '2024-06-06', 'officer', 'Female'),
(4, 'juandcruz', '$2y$10$fDvGDLZGY3OcMG6qhPha..BkVCF2SBUMafaat8sHprQXiQ98L.6Iy', 'Juan', 'Dela Cruz', 'profile.jpg', '2024-06-11', 'officer', 'Male'),
(8, 'johndoe', '$2y$10$JJyv3iRlgpbEeHKlyRZIOeRvtuLcskovhJn8vZvExSLNmrOj2uyVO', 'John', 'Doe', 'profile.jpg', '2025-03-12', 'officer', 'Male');

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
  `platform` text NOT NULL,
  `votes` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`id`, `position_id`, `firstname`, `lastname`, `partylist_id`, `photo`, `platform`, `votes`) VALUES
(59, 20, 'Stephen', 'Curry', 12, 'assets/images/profile.jpg', 'Steph Curry', 2),
(60, 21, 'Jimmy', 'Buttler', 12, 'assets/images/profile.jpg', 'Jimmy Buttler', 1),
(61, 22, 'Moses', 'Moody', 12, 'assets/images/profile.jpg', 'Moses Moody', 1),
(62, 20, 'Lebron', 'James', 11, 'assets/images/candidates/candidate_james_lebron.jpg', 'King James', 4),
(63, 21, 'Luka', 'Doncic', 11, 'assets/images/profile.jpg', 'Magic Doncic', 2),
(64, 22, 'Bronny', 'James', 11, 'assets/images/profile.jpg', 'Prince James', 1),
(65, 20, 'Nikola', 'Jokic', NULL, 'assets/images/profile.jpg', 'Nikola Jokic', 0),
(66, 21, 'Ja', 'Morant', NULL, 'assets/images/profile.jpg', 'Ja Morant', 3),
(67, 22, 'Klay', 'Thompson', NULL, 'assets/images/profile.jpg', 'Klay Thompson', 3),
(68, 23, 'Yuki', 'Kawamura', 12, 'assets/images/profile.jpg', 'Yuki Kawamura', 0),
(69, 23, 'Victor', 'Wembanayama', 11, 'assets/images/profile.jpg', 'Victor Wemby', 0),
(70, 23, 'Chris', 'Paul', NULL, 'assets/images/profile.jpg', 'Ringless Paul', 0);

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, '2024 Student Council Election', '2024-08-01 08:00:00', '2024-08-02 18:00:00', '/2024-student-council-election/details.pdf', '/2024-student-council-election/results.pdf', '2024-09-04 03:30:46');

-- --------------------------------------------------------

--
-- Table structure for table `election_status`
--

CREATE TABLE `election_status` (
  `id` int(11) NOT NULL,
  `status` enum('setup','pending','active','paused','completed') NOT NULL DEFAULT 'setup',
  `election_name` varchar(255) NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `last_status_change` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `election_status`
--

INSERT INTO `election_status` (`id`, `status`, `election_name`, `end_time`, `last_status_change`) VALUES
(1, 'active', 'Sangguniang Mag-aaral Elections', '2025-03-26 07:55:00', '2025-03-26 12:10:19');

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
(0, '2025-03-11 07:27:27', 'wallysabangan2024', 'Successful login', 'superadmin'),
(0, '2025-03-11 08:45:37', 'wallysabangan2024', 'Added position: President with max vote: 1', 'superadmin'),
(0, '2025-03-11 08:45:51', 'wallysabangan2024', 'Deleted position: President', 'superadmin'),
(0, '2025-03-11 08:55:06', 'wallysabangan2024', 'Added voter: 202320023', 'superadmin'),
(0, '2025-03-11 08:55:34', 'wallysabangan2024', 'Added voter: 202320024', 'superadmin'),
(0, '2025-03-11 08:55:58', 'wallysabangan2024', 'Added voter: 202120023', 'superadmin'),
(0, '2025-03-11 08:57:54', 'wallysabangan2024', 'Added voter: 202351599', 'superadmin'),
(0, '2025-03-11 08:59:07', 'wallysabangan2024', 'Added voter: 202312026', 'superadmin'),
(0, '2025-03-11 08:59:24', 'wallysabangan2024', 'Added position: President with max vote: 1', 'superadmin'),
(0, '2025-03-11 09:00:15', 'wallysabangan2024', 'Added voter: 201216528', 'superadmin'),
(0, '2025-03-11 09:09:54', 'wallysabangan2024', 'Deleted voter: 202351599', 'superadmin'),
(0, '2025-03-11 09:10:38', 'wallysabangan2024', 'Added voter: 202320045', 'superadmin'),
(0, '2025-03-11 09:10:45', 'wallysabangan2024', 'Updated voter from \'202320045\' to \'202320046\'', 'superadmin'),
(0, '2025-03-11 09:10:59', 'wallysabangan2024', 'Added voter: 8951919', 'superadmin'),
(0, '2025-03-11 09:11:18', 'wallysabangan2024', 'Deleted voter: 202320046', 'superadmin'),
(0, '2025-03-11 09:11:30', 'wallysabangan2024', 'Deleted voter: 8951919', 'superadmin'),
(0, '2025-03-11 10:06:59', 'wallysabangan2024', 'Added position: Vice President with max vote: 1', 'superadmin'),
(0, '2025-03-11 10:07:09', 'wallysabangan2024', 'Added voter: 20232126919', 'superadmin'),
(0, '2025-03-11 10:34:01', 'wallysabangan2024', 'Error in candidate management: Candidate not found', 'superadmin'),
(0, '2025-03-11 10:35:23', 'wallysabangan2024', 'Error in candidate management: Candidate not found', 'superadmin'),
(0, '2025-03-11 10:40:49', 'wallysabangan2024', 'Error in candidate management: Candidate not found', 'superadmin'),
(0, '2025-03-11 10:40:57', 'wallysabangan2024', 'Error in candidate management: Candidate not found', 'superadmin'),
(0, '2025-03-11 10:41:04', 'wallysabangan2024', 'Updated candidate:  ', 'superadmin'),
(0, '2025-03-11 10:41:09', 'wallysabangan2024', 'Added candidate: asd dada', 'superadmin'),
(0, '2025-03-11 10:41:20', 'wallysabangan2024', 'Error in candidate management: Candidate not found', 'superadmin'),
(0, '2025-03-11 10:41:27', 'wallysabangan2024', 'Updated candidate:  ', 'superadmin'),
(0, '2025-03-11 10:42:48', 'wallysabangan2024', 'Error in candidate management: Candidate not found', 'superadmin'),
(0, '2025-03-11 10:42:58', 'wallysabangan2024', 'Error in candidate management: Candidate not found', 'superadmin'),
(0, '2025-03-11 10:43:03', 'wallysabangan2024', 'Error in candidate management: Candidate not found', 'superadmin'),
(0, '2025-03-11 10:44:41', 'wallysabangan2024', 'Error in candidate management: Candidate not found', 'superadmin'),
(0, '2025-03-11 10:44:52', 'wallysabangan2024', 'Added candidate: Janzen Go', 'superadmin'),
(0, '2025-03-11 10:45:10', 'wallysabangan2024', 'Error in candidate management: Candidate not found', 'superadmin'),
(0, '2025-03-11 10:45:17', 'wallysabangan2024', 'Error in candidate management: Candidate not found', 'superadmin'),
(0, '2025-03-11 10:46:41', 'wallysabangan2024', 'Deleted candidate: dad asd', 'superadmin'),
(0, '2025-03-11 10:46:45', 'wallysabangan2024', 'Deleted candidate: Asda asdas', 'superadmin'),
(0, '2025-03-11 10:47:06', 'wallysabangan2024', 'Updated candidate: asd dada', 'superadmin'),
(0, '2025-03-11 22:54:32', 'wallysabangan2024', 'Successful login', 'superadmin'),
(0, '2025-03-11 23:02:54', 'wallysabangan2024', 'Added new President position with 1 maximum vote', 'superadmin'),
(0, '2025-03-11 23:03:24', 'wallysabangan2024', 'Added new partylist: Sandigan', 'superadmin'),
(0, '2025-03-11 23:04:05', 'wallysabangan2024', 'Added new candidate: Juan Dela Cruz for President under Sandigan partylist', 'superadmin'),
(0, '2025-03-11 23:17:55', 'wallysabangan2024', 'Added new President position with 1 maximum vote', 'superadmin'),
(0, '2025-03-11 23:18:05', 'wallysabangan2024', 'Added new partylist: Sandigan', 'superadmin'),
(0, '2025-03-11 23:18:36', 'wallysabangan2024', 'Added new candidate: Juan Dela Cruz for President under Sandigan partylist', 'superadmin'),
(0, '2025-03-11 23:31:08', 'wallysabangan2024', 'Added new President position with 1 maximum vote', 'superadmin'),
(0, '2025-03-11 23:31:44', 'wallysabangan2024', 'Added new candidate: Juan Dela Cruz for President under Sandigan partylist', 'superadmin'),
(0, '2025-03-25 06:09:27', 'wallysabangan2024', 'Failed login attempt: incorrect password', 'superadmin'),
(0, '2025-03-25 06:09:32', 'wallysabangan2024', 'Failed login attempt: incorrect password', 'superadmin');

-- --------------------------------------------------------

--
-- Table structure for table `otp_requests`
--

CREATE TABLE `otp_requests` (
  `id` int(11) NOT NULL,
  `student_number` varchar(20) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `attempts` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `otp_requests`
--
DELIMITER $$
CREATE TRIGGER `before_otp_insert` BEFORE INSERT ON `otp_requests` FOR EACH ROW BEGIN
    DECLARE attempt_count INT;
    SELECT COUNT(*) INTO attempt_count 
    FROM otp_requests 
    WHERE student_number = NEW.student_number 
    AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR);
    
    IF attempt_count >= 5 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Rate limit exceeded. Please try again later.';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_delete_max_attempts` AFTER UPDATE ON `otp_requests` FOR EACH ROW BEGIN
    IF NEW.attempts >= 5 THEN
        -- We can't delete directly in an AFTER UPDATE trigger
        -- So we'll use a signal to indicate this record should be deleted
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'MAX_ATTEMPTS_REACHED';
    END IF;
END
$$
DELIMITER ;

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
(11, 'Sandigan'),
(12, 'Kanlungan');

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `id` int(11) NOT NULL,
  `description` varchar(50) NOT NULL,
  `max_vote` int(11) NOT NULL,
  `priority` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`id`, `description`, `max_vote`, `priority`) VALUES
(20, 'President', 1, 1),
(21, 'Vice President', 1, 2),
(22, 'Secretary', 1, 3),
(23, 'PIO', 2, 4);

-- --------------------------------------------------------

--
-- Table structure for table `voters`
--

CREATE TABLE `voters` (
  `id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `student_number` varchar(20) NOT NULL,
  `has_voted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `voters`
--

INSERT INTO `voters` (`id`, `course_id`, `student_number`, `has_voted`, `created_at`) VALUES
(74, 11, '202320023', 1, '2025-03-12 00:17:17'),
(76, 6, '202411986', 1, '2025-03-25 01:43:35'),
(77, 1, '202110615', 1, '2025-03-25 03:04:47'),
(78, 1, '202111184', 1, '2025-03-25 03:05:28');

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `id` int(11) NOT NULL,
  `election_id` int(11) NOT NULL,
  `vote_ref` varchar(20) NOT NULL,
  `votes_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`votes_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `votes`
--

INSERT INTO `votes` (`id`, `election_id`, `vote_ref`, `votes_data`, `created_at`) VALUES
(213, 1, 'VOTE-250324-2572', '{\"20\":\"57\",\"22\":\"55\"}', '2025-03-24 14:56:21'),
(214, 1, 'VOTE-250325-9835', '{\"20\":\"59\",\"21\":\"63\",\"22\":\"64\"}', '2025-03-25 01:40:14'),
(215, 1, 'VOTE-250325-3210', '{\"20\":\"59\",\"21\":\"66\",\"22\":\"61\"}', '2025-03-25 03:33:54'),
(216, 1, 'VOTE-250325-8720', '{\"20\":\"62\",\"21\":\"60\",\"22\":\"67\"}', '2025-03-25 03:33:54'),
(217, 1, 'VOTE-250325-3730', '{\"20\":\"62\",\"21\":\"63\"}', '2025-03-25 03:36:56'),
(218, 1, 'VOTE-250325-4325', '{\"20\":\"62\",\"21\":\"66\",\"22\":\"67\"}', '2025-03-25 03:47:05'),
(219, 1, 'VOTE-250325-5743', '{\"20\":\"62\",\"21\":\"66\",\"22\":\"67\"}', '2025-03-25 03:48:08');

--
-- Triggers `votes`
--
DELIMITER $$
CREATE TRIGGER `before_vote_insert` BEFORE INSERT ON `votes` FOR EACH ROW BEGIN
    -- Generate vote reference if not provided
    IF NEW.vote_ref IS NULL OR NEW.vote_ref = '' THEN
        SET NEW.vote_ref = CONCAT(
            'VOTE-',
            DATE_FORMAT(NOW(), '%y%m%d'),
            '-',
            LPAD(FLOOR(RAND() * 10000), 4, '0')
        );
    END IF;
END
$$
DELIMITER ;

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `position_id` (`position_id`),
  ADD KEY `fk_candidates_partylist` (`partylist_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `election_history`
--
ALTER TABLE `election_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_election_dates` (`start_date`,`end_date`);

--
-- Indexes for table `election_status`
--
ALTER TABLE `election_status`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`,`end_time`);

--
-- Indexes for table `otp_requests`
--
ALTER TABLE `otp_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student_otp` (`student_number`,`otp`),
  ADD KEY `idx_expiry` (`expires_at`),
  ADD KEY `idx_expires_at` (`expires_at`);

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
  ADD UNIQUE KEY `student_number` (`student_number`),
  ADD KEY `fk_voters_course` (`course_id`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vote_ref` (`vote_ref`),
  ADD KEY `idx_election_time` (`election_id`,`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `election_history`
--
ALTER TABLE `election_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `election_status`
--
ALTER TABLE `election_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `otp_requests`
--
ALTER TABLE `otp_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `partylists`
--
ALTER TABLE `partylists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `voters`
--
ALTER TABLE `voters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=220;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `candidates`
--
ALTER TABLE `candidates`
  ADD CONSTRAINT `fk_candidates_partylist` FOREIGN KEY (`partylist_id`) REFERENCES `partylists` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_candidates_position` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `otp_requests`
--
ALTER TABLE `otp_requests`
  ADD CONSTRAINT `fk_otp_voter` FOREIGN KEY (`student_number`) REFERENCES `voters` (`student_number`) ON DELETE CASCADE;

--
-- Constraints for table `voters`
--
ALTER TABLE `voters`
  ADD CONSTRAINT `fk_voters_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `fk_vote_election` FOREIGN KEY (`election_id`) REFERENCES `election_status` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
