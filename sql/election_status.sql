-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 12, 2025 at 01:48 AM
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
-- First, disable foreign key checks
--
SET FOREIGN_KEY_CHECKS=0;

--
-- Drop existing triggers and events
--
DROP TRIGGER IF EXISTS before_election_update;
DROP EVENT IF EXISTS check_election_start;
DROP EVENT IF EXISTS check_election_end;

--
-- Drop existing table if exists
--
DROP TABLE IF EXISTS `election_status`;

--
-- Table structure for table `election_status`
--
CREATE TABLE `election_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` enum('pending','on','off','paused') NOT NULL DEFAULT 'pending',
  `election_name` varchar(255) NOT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `last_status_change` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`,`start_time`,`end_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Re-enable foreign key checks
--
SET FOREIGN_KEY_CHECKS=1;

--
-- Triggers for automatic election status management
--
DELIMITER //

CREATE TRIGGER before_election_update 
BEFORE UPDATE ON election_status
FOR EACH ROW
BEGIN
    -- If status is being turned on manually, set start_time to current time
    IF NEW.status = 'on' AND OLD.status != 'on' THEN
        IF NEW.start_time = OLD.start_time THEN
            SET NEW.start_time = CURRENT_TIMESTAMP;
        END IF;
    END IF;
END //

DELIMITER ;

--
-- Events for automatic election management
--
SET GLOBAL event_scheduler = ON;

DELIMITER //

CREATE EVENT check_election_start
ON SCHEDULE EVERY 1 MINUTE
DO
BEGIN
    UPDATE election_status 
    SET status = 'on'
    WHERE id = 1 
    AND status != 'on'
    AND status != 'off'
    AND status != 'pending'
    AND start_time <= CURRENT_TIMESTAMP
    AND end_time > CURRENT_TIMESTAMP;
END //

CREATE EVENT check_election_end
ON SCHEDULE EVERY 1 MINUTE
DO
BEGIN
    UPDATE election_status 
    SET status = 'off'
    WHERE id = 1 
    AND status != 'off'
    AND status != 'pending'
    AND end_time <= CURRENT_TIMESTAMP;
END //

DELIMITER ;

--
-- Insert default pending election
--
INSERT INTO `election_status` (`id`, `status`, `election_name`) VALUES
(1, 'pending', 'New Election');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
