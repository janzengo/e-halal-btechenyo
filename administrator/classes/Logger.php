<?php
require_once __DIR__ . '/../../classes/Logger.php';

/**
 * Administrator Logger class that extends the main Logger
 * This ensures consistent logging across the application while allowing
 * for administrator-specific logging functionality if needed
 */
class AdminLogger extends Logger {
    private static $instance = null;

    private function __construct() {
        parent::__construct();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Log an admin action
     * 
     * @param string $admin_id Admin identifier
     * @param string $action Action performed
     * @param string $details Additional details
     * @return bool Success status
     */
    public function logAdminAction($admin_id, $action, $details = '') {
        return $this->generateLog(
            'admin',
            date('Y-m-d H:i:s'),
            $admin_id,
            $action . ($details ? " | {$details}" : '')
        );
    }

    /**
     * Log a voter action
     * 
     * @param string $voter_id Voter identifier
     * @param string $action Action performed
     * @param string $details Additional details
     * @return bool Success status
     */
    public function logVoterAction($voter_id, $action, $details = '') {
        return $this->generateLog(
            'student',
            date('Y-m-d H:i:s'),
            $voter_id,
            $action . ($details ? " | {$details}" : '')
        );
    }

    /**
     * Get admin logs
     * 
     * @param int $limit Number of logs to retrieve (0 for all)
     * @return array Log entries
     */
    public function getAdminLogs($limit = 100) {
        $logs = $this->readLogs('admin', $limit);
        return array_merge($logs['database_logs'], $logs['file_logs']);
    }

    /**
     * Get voter logs
     * 
     * @param int $limit Number of logs to retrieve (0 for all)
     * @return array Log entries
     */
    public function getVoterLogs($limit = 100) {
        $logs = $this->readLogs('student', $limit);
        return array_merge($logs['database_logs'], $logs['file_logs']);
    }

    /**
     * Clear logs by type
     * 
     * @param string $type Type of logs to clear ('all', 'admin', or 'voters')
     * @return bool Success status
     */
    public function clearLogs($type = 'all') {
        $success = true;
        
        if ($type === 'all' || $type === 'admin') {
            $success &= $this->clearLogs('admin');
        }
        if ($type === 'all' || $type === 'voters') {
            $success &= $this->clearLogs('student');
        }
        
        return $success;
    }
} 