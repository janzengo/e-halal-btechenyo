<?php

class CustomSessionHandler {
    private $db;
    private static $instance = null;

    private function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = Database::getInstance();
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
    }

    public function setSession($key, $value) {
        $_SESSION[$key] = $value;
    }

    public function getSession($key) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    public function unsetSession($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public function destroySession() {
        session_destroy();
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