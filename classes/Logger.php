<?php
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/Database.php';

class Logger {
    private $db;
    private $admin_log_file = __DIR__ . '/../administrator/logs/admin_logs.log';
    private $voters_log_file = __DIR__ . '/../administrator/logs/voters_logs.log';
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->ensureLogFiles();
    }

    /**
     * Ensure log files exist and are writable
     */
    private function ensureLogFiles() {
        $files = [$this->admin_log_file, $this->voters_log_file];
        foreach ($files as $file) {
            if (!file_exists($file)) {
                $dir = dirname($file);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                touch($file);
                chmod($file, 0644);
            }
        }
    }

    /**
     * Generate and write log entry based on role
     * 
     * @param string $role User role (superadmin, officer, student)
     * @param string $time Timestamp
     * @param string $username Username/Student number
     * @param string $details Action details
     * @return bool Success status
     */
    public function generateLog($role, $time, $username, $details) {
        // Format the log entry
        $log_entry = sprintf(
            "[%s] %s | %s | %s\n",
            $time,
            str_pad($username, 20),
            str_pad($role, 10),
            $details
        );

        try {
            if ($role == 'superadmin' || $role == 'officer') {
                // Write to admin logs
                return file_put_contents(
                    $this->admin_log_file, 
                    $log_entry, 
                    FILE_APPEND | LOCK_EX
                ) !== false;
            } 
            else if ($role == 'student') {
                // Write to voters logs
                return file_put_contents(
                    $this->voters_log_file, 
                    $log_entry, 
                    FILE_APPEND | LOCK_EX
                ) !== false;
            }
            
            // Log unknown role attempts
            error_log("Unknown role attempted to log: $role");
            return false;
        } catch (Exception $e) {
            error_log("Error writing to log file: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Read logs for a specific role
     * 
     * @param string $role Role to get logs for
     * @param int $lines Number of lines to read (0 for all)
     * @return array Log entries
     */
    public function readLogs($role, $lines = 0) {
        $file = ($role == 'superadmin' || $role == 'officer') ? 
                $this->admin_log_file : $this->voters_log_file;

        try {
            if (!file_exists($file)) {
                return [];
            }

            $logs = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (!$logs) {
                return [];
            }

            // Reverse array to get newest first
            $logs = array_reverse($logs);

            // Return specified number of lines or all if $lines = 0
            return $lines > 0 ? array_slice($logs, 0, $lines) : $logs;

        } catch (Exception $e) {
            error_log("Error reading log file: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Clear logs for a specific role
     * 
     * @param string $role Role whose logs to clear
     * @return bool Success status
     */
    public function clearLogs($role) {
        $file = ($role == 'superadmin' || $role == 'officer') ? 
                $this->admin_log_file : $this->voters_log_file;

        try {
            return file_put_contents($file, '') !== false;
        } catch (Exception $e) {
            error_log("Error clearing log file: " . $e->getMessage());
            return false;
        }
    }
}
?>