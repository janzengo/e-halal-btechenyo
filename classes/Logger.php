<?php
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/Database.php';

class Logger {
    protected $logPath;
    private static $instance = null;
    private $db;
    
    protected function __construct() {
        $this->db = Database::getInstance();
        $this->logPath = __DIR__ . '/../administrator/logs/';
        $this->ensureLogDirectoryExists();
    }

    /**
     * Get Logger instance (Singleton)
     * 
     * @return Logger
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Prevent cloning of the instance
    protected function __clone() {}

    // Prevent unserialization of the instance
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }

    protected function ensureLogDirectoryExists() {
        if (!file_exists($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }

    public function generateLog($type, $timestamp, $user_id, $action) {
        $logFile = $this->logPath . $type . '_logs.json';
        
        // Create log entry
        $logEntry = [
            'timestamp' => $timestamp,
            'user_id' => $user_id,
            'action' => $action
        ];

        // Read existing logs
        $logs = [];
        if (file_exists($logFile)) {
            $jsonContent = file_get_contents($logFile);
            if (!empty($jsonContent)) {
                $logs = json_decode($jsonContent, true) ?? [];
            }
        }

        // Add new log entry
        $logs[] = $logEntry;

        // Write back to file with proper formatting
        return file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    protected function readLogs($type, $limit = 0) {
        $logFile = $this->logPath . $type . '_logs.json';
        $logs = [];

        if (file_exists($logFile)) {
            $jsonContent = file_get_contents($logFile);
            if (!empty($jsonContent)) {
                $logs = json_decode($jsonContent, true) ?? [];
            }
        }

        // Sort logs by timestamp in descending order
        usort($logs, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        // Apply limit if specified
        if ($limit > 0 && count($logs) > $limit) {
            $logs = array_slice($logs, 0, $limit);
        }

        return $logs;
    }

    protected function clearLogs($type) {
        $logFile = $this->logPath . $type . '_logs.json';
        return file_put_contents($logFile, json_encode([], JSON_PRETTY_PRINT));
    }

    public function logVoteSubmission($student_number, $vote_ref) {
        return $this->generateLog(
            'voters',
            date('Y-m-d H:i:s'),
            $student_number,
            ['action' => "Vote submitted successfully. Reference: {$vote_ref}"]
        );
    }

    public function logLoginAttempt($student_number, $success, $details = '') {
        return $this->generateLog(
            'voters',
            date('Y-m-d H:i:s'),
            $student_number,
            ['action' => $success ? "Login successful" : "Login failed - {$details}"]
        );
    }

    public function logLogout($student_number) {
        return $this->generateLog(
            'voters',
            date('Y-m-d H:i:s'),
            $student_number,
            ['action' => "User logged out"]
        );
    }
}
?>