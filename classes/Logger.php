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
        try {
            $this->db->beginTransaction();

            // Log to database
            $stmt = $this->db->prepare("
                INSERT INTO logs (timestamp, username, details, role) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("ssss", $time, $username, $details, $role);
            $dbSuccess = $stmt->execute();

            // Format the log entry for file
            $log_entry = sprintf(
                "[%s] %s | %s | %s\n",
                $time,
                str_pad($username, 20),
                str_pad($role, 10),
                $details
            );

            // Determine which log file to use
            $log_file = ($role == 'superadmin' || $role == 'officer') ? 
                        $this->admin_log_file : $this->voters_log_file;

            // Write to appropriate log file
            $fileSuccess = file_put_contents(
                $log_file,
                $log_entry,
                FILE_APPEND | LOCK_EX
            ) !== false;

            if ($dbSuccess && $fileSuccess) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollback();
                return false;
            }
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error in generateLog: " . $e->getMessage());
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
        try {
            // Get logs from database
            $stmt = $this->db->prepare("
                SELECT * FROM logs 
                WHERE role = ? 
                ORDER BY timestamp DESC
                " . ($lines > 0 ? "LIMIT ?" : "")
            );

            if ($lines > 0) {
                $stmt->bind_param("si", $role, $lines);
        } else {
                $stmt->bind_param("s", $role);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $dbLogs = $result->fetch_all(MYSQLI_ASSOC);

            // Get logs from file
            $file = ($role == 'superadmin' || $role == 'officer') ? 
                    $this->admin_log_file : $this->voters_log_file;

            $fileLogs = [];
            if (file_exists($file)) {
                $fileLogs = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $fileLogs = array_reverse($fileLogs);
                if ($lines > 0) {
                    $fileLogs = array_slice($fileLogs, 0, $lines);
                }
            }

            return [
                'database_logs' => $dbLogs,
                'file_logs' => $fileLogs
            ];

        } catch (Exception $e) {
            error_log("Error reading logs: " . $e->getMessage());
            return [
                'database_logs' => [],
                'file_logs' => []
            ];
        }
    }

    /**
     * Clear logs for a specific role
     * 
     * @param string $role Role whose logs to clear
     * @return bool Success status
     */
    public function clearLogs($role) {
        try {
            $this->db->beginTransaction();

            // Clear database logs
            $stmt = $this->db->prepare("DELETE FROM logs WHERE role = ?");
            $stmt->bind_param("s", $role);
            $dbSuccess = $stmt->execute();

            // Clear file logs
            $file = ($role == 'superadmin' || $role == 'officer') ? 
                    $this->admin_log_file : $this->voters_log_file;
            $fileSuccess = file_put_contents($file, '') !== false;

            if ($dbSuccess && $fileSuccess) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollback();
                return false;
            }
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error clearing logs: " . $e->getMessage());
            return false;
        }
    }

    public function logVoteSubmission($student_number, $vote_ref) {
        return $this->generateLog(
            'student',
            date('Y-m-d H:i:s'),
            $student_number,
            "Vote submitted successfully. Reference: {$vote_ref}"
        );
    }

    public function logLoginAttempt($student_number, $success, $details = '') {
        return $this->generateLog(
            'student',
            date('Y-m-d H:i:s'),
            $student_number,
            $success ? "Login successful" : "Login failed - {$details}"
        );
    }

    public function logLogout($student_number) {
        return $this->generateLog(
            'student',
            date('Y-m-d H:i:s'),
            $student_number,
            "User logged out"
        );
    }
}
?>