<?php
require_once __DIR__ . '/../../../classes/View.php';
require_once __DIR__ . '/../../../classes/Admin.php';
require_once __DIR__ . '/../../../classes/Elections.php';

class BaseController {
    protected $view;
    protected $admin;
    protected $election;
    
    public function __construct() {
        $this->view = View::getInstance();
        $this->admin = Admin::getInstance();
        $this->election = Elections::getInstance();
        
        // Check if user is logged in
        if (!$this->admin->isLoggedIn()) {
            $this->sendError('Unauthorized access');
            exit();
        }
        
        // Check if modifications are allowed
        if (!$this->view->isModificationAllowed()) {
            $this->sendError('Modifications are not allowed at this time');
            exit();
        }
    }
    
    protected function sendError($message) {
        echo json_encode([
            'error' => true,
            'message' => $message
        ]);
        exit();
    }
    
    protected function sendSuccess($data = null) {
        echo json_encode([
            'error' => false,
            'data' => $data
        ]);
        exit();
    }
} 