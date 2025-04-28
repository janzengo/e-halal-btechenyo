-- Drop existing indexes that might include email
ALTER TABLE `admin`
  DROP INDEX IF EXISTS `email`,
  DROP INDEX IF EXISTS `idx_email`,
  DROP INDEX IF EXISTS `fk_admin_email`;

-- Create a new index with email as the first column
ALTER TABLE `admin`
  ADD UNIQUE KEY `idx_admin_email` (`email`); 