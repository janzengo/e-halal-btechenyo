<?php

class LoginDebugger {
    private static $instance = null;
    private $logFile;
    
    private function __construct() {
        $this->logFile = __DIR__ . '/../../logs/login_debug.log';
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function log($message, $data = null) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}";
        
        if ($data !== null) {
            $logMessage .= "\nData: " . print_r($data, true);
        }
        
        $logMessage .= "\n" . str_repeat('-', 80) . "\n";
        
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }
    
    public function logSessionState($prefix = '') {
        $sessionData = [];
        if (isset($_SESSION)) {
            $sessionData = $_SESSION;
        }
        
        $this->log($prefix . " Session State", $sessionData);
    }
    
    public function clearLog() {
        file_put_contents($this->logFile, '');
    }
} 