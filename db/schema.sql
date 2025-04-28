-- phpMyAdmin SQL Dump
-- Fresh Schema for E-Halal System

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
CREATE DATABASE IF NOT EXISTS `e-halal` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `e-halal`;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(60) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `photo` varchar(150) NOT NULL,
  `created_on` date NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'officer',
  `gender` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Pre-insert head admin
--

INSERT INTO `admin` (`id`, `username`, `email`, `password`, `firstname`, `lastname`, `photo`, `created_on`, `role`, `gender`) VALUES
(1, 'janzengo', 'janneiljanzen.go@gmail.com', '$2y$10$ucPqJTKE1TNakVubE3clfuMjfr9CAYF/MAh78ZjTsam2u2l.aNpqi', 'Janzen', 'Go', 'assets/images/administrators/head_go_janzen.png', '2024-06-06', 'head', 'Male');

-- --------------------------------------------------------

--
-- Table structure for table `admin_otp_requests`
--

CREATE TABLE `admin_otp_requests` (
  `id` int(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `otp` varchar(6) NOT NULL,
  `attempts` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `election_history`
--

CREATE TABLE `election_history` (
  `id` int(11) NOT NULL,
  `election_name` varchar(255) NOT NULL,
  `status` enum('setup','pending','active','paused','completed') NOT NULL DEFAULT 'completed',
  `end_time` datetime NOT NULL,
  `last_status_change` datetime DEFAULT NULL,
  `details_pdf` varchar(255) NOT NULL,
  `results_pdf` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `control_number` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `election_status`
--

CREATE TABLE `election_status` (
  `id` int(11) NOT NULL,
  `status` enum('setup','pending','active','paused','completed') NOT NULL DEFAULT 'setup',
  `election_name` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `end_time` datetime DEFAULT NULL,
  `last_status_change` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `control_number` varchar(20) NOT NULL DEFAULT concat('E-',year(current_timestamp()),'-',lpad(floor(rand() * 10000),4,'0'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Pre-insert initial election status
--

INSERT INTO `election_status` (`id`, `status`, `election_name`, `created_at`, `end_time`, `last_status_change`, `control_number`) VALUES
(1, 'setup', '', CURRENT_TIMESTAMP(), NULL, CURRENT_TIMESTAMP(), CONCAT('E', DATE_FORMAT(NOW(), '%y%m%d'), LPAD(FLOOR(RAND() * 10000), 4, '0')));

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

-- --------------------------------------------------------

--
-- Table structure for table `partylists`
--

CREATE TABLE `partylists` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Triggers
--

DELIMITER $$

-- Admin OTP Triggers
CREATE TRIGGER `before_admin_otp_insert` BEFORE INSERT ON `admin_otp_requests` FOR EACH ROW BEGIN
    DECLARE attempt_count INT;
    SELECT COUNT(*) INTO attempt_count 
    FROM admin_otp_requests 
    WHERE email = NEW.email 
    AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR);
    
    IF attempt_count >= 5 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Rate limit exceeded. Please try again later.';
    END IF;
END$$

CREATE TRIGGER `tr_delete_admin_max_attempts` AFTER UPDATE ON `admin_otp_requests` FOR EACH ROW BEGIN
    IF NEW.attempts >= 5 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'MAX_ATTEMPTS_REACHED';
    END IF;
END$$

-- Voter OTP Triggers
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
END$$

CREATE TRIGGER `tr_delete_max_attempts` AFTER UPDATE ON `otp_requests` FOR EACH ROW BEGIN
    IF NEW.attempts >= 5 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'MAX_ATTEMPTS_REACHED';
    END IF;
END$$

-- Votes Trigger
CREATE TRIGGER `before_vote_insert` BEFORE INSERT ON `votes` FOR EACH ROW BEGIN
    IF NEW.vote_ref IS NULL OR NEW.vote_ref = '' THEN
        SET NEW.vote_ref = CONCAT(
            'VOTE-',
            DATE_FORMAT(NOW(), '%y%m%d'),
            '-',
            LPAD(FLOOR(RAND() * 10000), 4, '0')
        );
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Indexes
--

ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_admin_email` (`email`);

ALTER TABLE `admin_otp_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_otp` (`otp`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `fk_admin_otp_email` (`email`);

ALTER TABLE `candidates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `position_id` (`position_id`),
  ADD KEY `fk_candidates_partylist` (`partylist_id`);

ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `election_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `control_number` (`control_number`),
  ADD KEY `idx_election_history_control` (`control_number`),
  ADD KEY `idx_status_history` (`status`,`end_time`),
  ADD KEY `idx_election_end_status` (`end_time`,`status`);

ALTER TABLE `election_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `control_number` (`control_number`),
  ADD KEY `idx_status` (`status`,`end_time`),
  ADD KEY `idx_election_status_control` (`control_number`);

ALTER TABLE `otp_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student_otp` (`student_number`,`otp`),
  ADD KEY `idx_expiry` (`expires_at`),
  ADD KEY `idx_expires_at` (`expires_at`);

ALTER TABLE `partylists`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `voters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_number` (`student_number`),
  ADD KEY `fk_voters_course` (`course_id`);

ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vote_ref` (`vote_ref`),
  ADD KEY `idx_election_time` (`election_id`,`created_at`);

-- --------------------------------------------------------

--
-- AUTO_INCREMENT
--

ALTER TABLE `admin` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `admin_otp_requests` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `candidates` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `courses` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `election_history` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `election_status` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `otp_requests` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `partylists` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `positions` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `voters` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `votes` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Constraints
--

ALTER TABLE `admin_otp_requests`
  ADD CONSTRAINT `fk_admin_otp_email` FOREIGN KEY (`email`) REFERENCES `admin` (`email`);

ALTER TABLE `candidates`
  ADD CONSTRAINT `fk_candidates_partylist` FOREIGN KEY (`partylist_id`) REFERENCES `partylists` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_candidates_position` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE;

ALTER TABLE `otp_requests`
  ADD CONSTRAINT `fk_otp_voter` FOREIGN KEY (`student_number`) REFERENCES `voters` (`student_number`) ON DELETE CASCADE;

ALTER TABLE `voters`
  ADD CONSTRAINT `fk_voters_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

ALTER TABLE `votes`
  ADD CONSTRAINT `fk_vote_election` FOREIGN KEY (`election_id`) REFERENCES `election_status` (`id`);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */; 