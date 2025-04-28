-- Create admin OTP requests table
CREATE TABLE `admin_otp_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) DEFAULT NULL,
  `otp` varchar(6) NOT NULL,
  `attempts` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_otp` (`otp`),
  KEY `idx_expires_at` (`expires_at`),
  KEY `fk_admin_otp_email` (`email`),
  CONSTRAINT `fk_admin_otp_email` FOREIGN KEY (`email`) REFERENCES `admin` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create rate limiting trigger for admin OTP
DELIMITER $$
CREATE TRIGGER `before_admin_otp_insert` BEFORE INSERT ON `admin_otp_requests` FOR EACH ROW
BEGIN
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
DELIMITER ;

-- Create max attempts trigger
DELIMITER $$
CREATE TRIGGER `tr_delete_admin_max_attempts` AFTER UPDATE ON `admin_otp_requests` FOR EACH ROW
BEGIN
    IF NEW.attempts >= 5 THEN
        -- We can't delete directly in an AFTER UPDATE trigger
        -- So we'll use a signal to indicate this record should be deleted
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'MAX_ATTEMPTS_REACHED';
    END IF;
END$$
DELIMITER ; 