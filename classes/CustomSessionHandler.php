<?php
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Logger.php';

class CustomSessionHandler {
    private $db;
    private $logger;
    private static $instance = null;

    private function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            // Set secure session parameters
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
            
            session_start();
        }
        $this->db = Database::getInstance();
        $this->logger = Logger::getInstance();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function startSession() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->validateSession();
    }

    private function validateSession() {
        if (isset($_SESSION['voter'])) {
            // Validate IP hasn't changed drastically
            if (isset($_SESSION['ip']) && $_SESSION['ip'] !== $_SERVER['REMOTE_ADDR']) {
                $this->destroySession();
                return false;
            }
            
            // Validate session age
            if (isset($_SESSION['created']) && time() - $_SESSION['created'] > 3600) {
                $this->destroySession();
                return false;
            }
        }
        return true;
    }

    public function setSession($key, $value) {
        $_SESSION[$key] = $value;
        if ($key === 'voter') {
            // Set security markers
            $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION['created'] = time();
            
            // Regenerate session ID
            session_regenerate_id(true);
            
            $this->logger->generateLog(
                'voters',
                date('Y-m-d H:i:s'),
                $_SESSION['student_number'] ?? 'unknown',
                ['action' => 'New session created']
            );
        }
    }

    public function getSession($key) {
        if ($this->validateSession()) {
            return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
        }
        return null;
    }

    public function unsetSession($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public function destroySession() {
        if (isset($_SESSION['student_number'])) {
            $this->logger->generateLog(
                'voters',
                date('Y-m-d H:i:s'),
                $_SESSION['student_number'],
                ['action' => 'Session destroyed']
            );
        }
        
        session_unset();
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/');
    }

    public function setError($message) {
        $_SESSION['error'] = is_array($message) ? $message : [$message];
    }

    public function getError() {
        return isset($_SESSION['error']) ? $_SESSION['error'] : [];
    }

    public function hasError() {
        return isset($_SESSION['error']) && !empty($_SESSION['error']);
    }

    public function clearError() {
        unset($_SESSION['error']);
    }

    public function setSuccess($message) {
        $_SESSION['success'] = $message;
    }

    public function getSuccess() {
        return isset($_SESSION['success']) ? $_SESSION['success'] : '';
    }

    public function hasSuccess() {
        return isset($_SESSION['success']) && !empty($_SESSION['success']);
    }

    public function clearSuccess() {
        unset($_SESSION['success']);
    }
}