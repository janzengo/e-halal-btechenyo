<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/CustomSessionHandler.php';

class Admin {
    private static $instance = null;
    private $db;
    private $session;
    protected $id;
    protected $username;
    protected $firstname;
    protected $lastname;
    protected $photo;
    protected $role;
    protected $gender;

    private function __construct() {
        $this->db = Database::getInstance();
        $this->session = CustomSessionHandler::getInstance();
        
        // Load admin data if logged in
        if ($this->isLoggedIn()) {
            $this->loadAdminData();
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadAdminData() {
        $admin_id = $this->session->getSession('admin');
        if ($admin_id) {
            $sql = "SELECT * FROM admin WHERE id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bind_param("i", $admin_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $admin = $result->fetch_assoc();
                $this->id = $admin['id'];
                $this->username = $admin['username'];
                $this->firstname = $admin['firstname'];
                $this->lastname = $admin['lastname'];
                $this->photo = $admin['photo'];
                $this->created_on = $admin['created_on'];
                $this->role = $admin['role'];
                $this->gender = $admin['gender'];
            }
        }
    }

    public function login($username, $password) {
        $sql = "SELECT * FROM admin WHERE username = ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows < 1) {
            return false;
        }
    
        $admin = $result->fetch_assoc();
    
        if (password_verify($password, $admin['password'])) {
            $this->id = $admin['id'];
            $this->username = $admin['username'];
            $this->firstname = $admin['firstname'];
            $this->lastname = $admin['lastname'];
            $this->photo = $admin['photo'];
            $this->role = $admin['role'];
            $this->gender = $admin['gender'];
            
            $this->session->setSession('admin', $this->id);
            return true;
        }
        
        return false;
    }

    public function logout() {
        $this->session->unsetSession('admin');
        $this->id = null;
        $this->username = null;
        $this->firstname = null;
        $this->lastname = null;
        $this->photo = null;
        $this->role = null;
        $this->gender = null;
        
        // Instead of redirecting, just return true
        return true;
    }

    public function isLoggedIn() {
        $admin_id = $this->session->getSession('admin');
        if ($admin_id) {
            // Reload admin data if we have a session but no data
            if (!$this->id) {
                $this->loadAdminData();
            }
            return true;
        }
        return false;
    }

    public function getAdminData() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        // Reload admin data to ensure it's fresh
        $this->loadAdminData();

        return [
            'id' => $this->id,
            'username' => $this->username,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'photo' => $this->photo,
            'role' => $this->role,
            'gender' => $this->gender,
            'created_on' => $this->created_on
        ];
    }

    public function getAdminDataFromSession() {
        if ($this->session->has('admin')) {
            $admin_id = $this->session->get('admin');
            $sql = "SELECT * FROM admin WHERE id = " . (int)$admin_id;
            $result = $this->db->getConnection()->query($sql);
            
            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            }
        }
        return null;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getUsername() { return $this->username; }
    public function getFirstname() { return $this->firstname; }
    public function getLastname() { return $this->lastname; }
    public function getPhoto() { return $this->photo; }
    public function getRole() { return $this->role; }
    public function getGender() { return $this->gender; }
}
