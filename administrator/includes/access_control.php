<?php


class AccessControl {
    private static $instance = null;
    private $admin;
    private $elections;
    
    private function __construct() {
        $this->admin = Admin::getInstance();
        $this->elections = Elections::getInstance();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function enforceLogin() {
        if (!$this->admin->isLoggedIn()) {
            $_SESSION['error'] = 'Please log in to access this page.';
            header('Location: ' . BASE_URL . 'administrator/login.php');
            exit();
        }
    }
    
    public function enforceHeadAccess() {
        $this->enforceLogin();
        if (!$this->admin->isHead()) {
            $_SESSION['error'] = 'Access Denied. This page is restricted to Electoral Heads only.';
            header('Location: ' . BASE_URL . 'administrator/pages/home.php');
            exit();
        }
    }
    
    public function enforceElectionStatus($allowedStatuses = []) {
        $currentStatus = $this->elections->getCurrentStatus();
        
        if (!in_array($currentStatus, $allowedStatuses)) {
            switch ($currentStatus) {
                case 'setup':
                    if ($this->admin->isHead()) {
                        header('Location: ' . BASE_URL . 'administrator/pages/setup.php');
                    } else {
                        $_SESSION['error'] = 'Access denied. The election is still being set up.';
                        header('Location: ' . BASE_URL . 'administrator/login.php');
                    }
                    exit();
                    
                case 'completed':
                    if ($this->admin->isHead()) {
                        header('Location: ' . BASE_URL . 'administrator/pages/results.php');
                    } else {
                        $_SESSION['error'] = 'Access denied. The election has been completed.';
                        header('Location: ' . BASE_URL . 'administrator/login.php');
                    }
                    exit();
                    
                default:
                    if (!$this->admin->isHead()) {
                        header('Location: ' . BASE_URL . 'administrator/pages/home.php');
                        exit();
                    }
            }
        }
    }
    
    public function checkPageAccess($page) {
        $this->enforceLogin();
        
        // Define page access rules
        $pageRules = [
            'setup.php' => ['head_only' => true, 'allowed_status' => ['setup']],
            'configure.php' => ['head_only' => true, 'allowed_status' => ['pending', 'active', 'paused']],
            'officers.php' => ['head_only' => true],
            'log_admin.php' => ['head_only' => true],
            'ballot.php' => ['allowed_status' => ['pending', 'active', 'paused']],
            'candidates.php' => ['allowed_status' => ['pending', 'active', 'paused']],
            'voters.php' => ['allowed_status' => ['pending', 'active', 'paused']],
            'positions.php' => ['allowed_status' => ['pending', 'active', 'paused']],
            'votes.php' => ['allowed_status' => ['active', 'paused']],
            'partylists.php' => ['allowed_status' => ['pending', 'active', 'paused']],
            'courses.php' => ['allowed_status' => ['pending', 'active', 'paused']]
        ];
        
        if (isset($pageRules[$page])) {
            $rules = $pageRules[$page];
            
            // Check head-only access
            if (isset($rules['head_only']) && $rules['head_only'] && !$this->admin->isHead()) {
                $_SESSION['error'] = 'Access Denied. This page is restricted to Electoral Heads only.';
                header('Location: ' . BASE_URL . 'administrator/pages/home.php');
                exit();
            }
            
            // Check allowed status
            if (isset($rules['allowed_status'])) {
                $this->enforceElectionStatus($rules['allowed_status']);
            }
        }
    }
} 