<?php

class Session {
    private static $instance = null;
    private const SESSION_NAME = 'ADMINISTRATOR_SESSION';
    private const SESSION_LIFETIME = 3600; // 1 hour
    private const REGENERATE_TIME = 300; // 5 minutes
    
    private function __construct() {
        $this->initializeSession();
    }
    
    /**
     * Initialize session with secure settings
     */
    private function initializeSession() {
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session parameters
            ini_set('session.use_strict_mode', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
            ini_set('session.cookie_samesite', 'Lax');
            ini_set('session.gc_maxlifetime', self::SESSION_LIFETIME);
            
            // Set session name
            session_name(self::SESSION_NAME);
            
            // Start session
            session_start();
            
            // Initialize session if new
            if (!isset($_SESSION['created'])) {
                $this->initNewSession();
            }
            
            // Validate and regenerate session
            $this->validateSession();
        }
    }
    
    /**
     * Initialize a new session
     */
    private function initNewSession() {
        $_SESSION['created'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['last_regenerated'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    }
    
    /**
     * Validate session and regenerate if needed
     */
    private function validateSession() {
        // Check session lifetime
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity'] > self::SESSION_LIFETIME)) {
            $this->destroy();
            throw new Exception('Session has expired');
        }
        
        // Validate IP and user agent
        if ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR'] ||
            $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
            $this->destroy();
            throw new Exception('Session authentication failed');
        }
        
        // Regenerate session ID periodically
        if (time() - $_SESSION['last_regenerated'] > self::REGENERATE_TIME) {
            $this->regenerateId();
        }
        
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Get session instance (Singleton pattern)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Regenerate session ID
     */
    public function regenerateId() {
        session_regenerate_id(true);
        $_SESSION['last_regenerated'] = time();
    }
    
    /**
     * Set a session value
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get a session value
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if a session key exists
     * @param string $key
     * @return bool
     */
    public function has($key) {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove a session value
     * @param string $key
     */
    public function remove($key) {
        unset($_SESSION[$key]);
    }
    
    /**
     * Set a flash message
     * @param string $key
     * @param mixed $value
     */
    public function setFlash($key, $value) {
        $_SESSION['flash'][$key] = $value;
    }
    
    /**
     * Get a flash message
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getFlash($key, $default = null) {
        $value = $_SESSION['flash'][$key] ?? $default;
        unset($_SESSION['flash'][$key]);
        return $value;
    }
    
    /**
     * Check if a flash message exists
     * @param string $key
     * @return bool
     */
    public function hasFlash($key) {
        return isset($_SESSION['flash'][$key]);
    }
    
    /**
     * Set error message
     * @param string|array $message
     */
    public function setError($message) {
        $this->setFlash('error', $message);
    }
    
    /**
     * Set success message
     * @param string $message
     */
    public function setSuccess($message) {
        $this->setFlash('success', $message);
    }
    
    /**
     * Get error message
     * @return string|array|null
     */
    public function getError() {
        return $this->getFlash('error');
    }
    
    /**
     * Get success message
     * @return string|null
     */
    public function getSuccess() {
        return $this->getFlash('success');
    }
    
    /**
     * Check if there are any error messages
     * @return bool
     */
    public function hasError() {
        return $this->hasFlash('error');
    }
    
    /**
     * Check if there are any success messages
     * @return bool
     */
    public function hasSuccess() {
        return $this->hasFlash('success');
    }
    
    /**
     * Clear all session data
     */
    public function clear() {
        $_SESSION = [];
    }
    
    /**
     * Destroy the session
     */
    public function destroy() {
        $this->clear();
        
        // Destroy the session cookie
        if (isset($_COOKIE[session_name()])) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        session_destroy();
    }
    
    /**
     * Prevent cloning of the instance (Singleton)
     */
    private function __clone() {}
    
    /**
     * Prevent unserialize of the instance (Singleton)
     */
    private function __wakeup() {}
} 