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
     * @param string $role Admin role
     * @param string $action Action performed
     * @return bool Success status
     */
    public function logAdminAction($admin_id, $role, $action) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $admin_id,
            'role' => $role,
            'action' => $action
        ];

        $logFile = $this->logPath . 'admin_logs.json';
        
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

        // Write back to file
        return file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Log a voter action
     * 
     * @param string $voter_id Voter identifier
     * @param string $action Action performed
     * @return bool Success status
     */
    public function logVoterAction($voter_id, $action) {
        return $this->generateLog(
            'voters',
            date('Y-m-d H:i:s'),
            $voter_id,
            ['action' => $action]
        );
    }

    /**
     * Get admin logs
     * 
     * @param int $limit Number of logs to retrieve (0 for all)
     * @return array Log entries
     */
    public function getAdminLogs($limit = 100) {
        return $this->readLogs('admin', $limit);
    }

    /**
     * Get voter logs
     * 
     * @param int $limit Number of logs to retrieve (0 for all)
     * @return array Log entries
     */
    public function getVoterLogs($limit = 100) {
        return $this->readLogs('voters', $limit);
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
            $success &= parent::clearLogs('admin');
        }
        if ($type === 'all' || $type === 'voters') {
            $success &= parent::clearLogs('voters');
        }
        
        return $success;
    }
} 