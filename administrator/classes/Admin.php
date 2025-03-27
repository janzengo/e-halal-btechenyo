<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/CustomSessionHandler.php';

class Admin {
    private static $instance = null;
    private $db;
    private $session;
    private $id;
    private $username;
    private $firstname;
    private $lastname;
    private $photo;
    private $created_on;
    private $role;
    private $gender;

    const ROLE_SUPERADMIN = 'superadmin';
    const ROLE_OFFICER = 'officer';

    // Restricted pages for officers
    private $restrictedPages = [
        'admin_logs.php',
        'officers.php',
        'configure.php'
    ];

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

    /**
     * Get full name of admin
     * @return string
     */
    public function getFullName() {
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
     * Get all admin users from the database
     * 
     * @return array Array of admin users
     */
    public function getAllAdmins() {
        $query = "SELECT * FROM admin ORDER BY created_on DESC";
        $result = $this->db->query($query);
        $admins = [];
        while ($row = $result->fetch_assoc()) {
            $admins[] = $row;
        }
        return $admins;
    }

    /**
     * Check if the logged-in user is an admin (superadmin)
     * 
     * @return bool True if user is admin, false otherwise
     */
    public function isAdmin() {
        return $this->isLoggedIn() && $this->role === 'superadmin';
    }

    /**
     * Check if the logged-in user is an officer
     * 
     * @return bool True if user is officer, false otherwise
     */
    public function isOfficer() {
        return $this->isLoggedIn() && $this->role === 'officer';
    }

    public function getAdminId() {
        if (isset($_SESSION['admin'])) {
            return $_SESSION['admin'];
        }
        return null;
    }

    /**
     * Check if current admin is superadmin
     * @return bool
     */
    public function isSuperAdmin() {
        return $this->getRole() === self::ROLE_SUPERADMIN;
    }

    /**
     * Check if current admin has access to a page
     * @param string $page
     * @return bool
     */
    public function hasPageAccess($page) {
        if ($this->getRole() === self::ROLE_SUPERADMIN) {
            return true;
        }

        return !in_array($page, $this->restrictedPages);
    }

    /**
     * Update admin profile information
     * @param string $username New username
     * @param string $firstname New firstname
     * @param string $lastname New lastname
     * @param string $photo Photo path
     * @return bool Success status
     */
    public function updateProfile($username, $firstname, $lastname, $photo = null) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        try {
            // Check if username is taken by another admin
            $sql = "SELECT id FROM admin WHERE username = ? AND id != ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bind_param("si", $username, $this->id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                throw new Exception("Username is already taken");
            }
            
            // Update admin profile
            $sql = "UPDATE admin SET username = ?, firstname = ?, lastname = ?";
            $params = [$username, $firstname, $lastname];
            $types = "sss";
            
            if ($photo) {
                $sql .= ", photo = ?";
                $params[] = $photo;
                $types .= "s";
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $this->id;
            $types .= "i";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                // Update local properties
                $this->username = $username;
                $this->firstname = $firstname;
                $this->lastname = $lastname;
                if ($photo) {
                    $this->photo = $photo;
                }
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error updating admin profile: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update admin password
     * @param string $currentPassword Current password for verification
     * @param string $newPassword New password
     * @return bool Success status
     */
    public function updatePassword($currentPassword, $newPassword) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        try {
            // Get current admin data
            $sql = "SELECT password FROM admin WHERE id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bind_param("i", $this->id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return false;
            }
            
            $row = $result->fetch_assoc();
            
            // Verify current password
            if (!password_verify($currentPassword, $row['password'])) {
                return false;
            }
            
            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password
            $sql = "UPDATE admin SET password = ? WHERE id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bind_param("si", $hashedPassword, $this->id);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating admin password: " . $e->getMessage());
            return false;
        }
    }

    public function getOfficer($id) {
        $id = (int)$id;
        $query = "SELECT * FROM admin WHERE id = ? AND role = 'officer'";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return false;
    }

    public function checkUsername($username, $excludeId = null) {
        $query = "SELECT id FROM admin WHERE username = ?";
        $params = [$username];
        $types = "s";

        if ($excludeId !== null) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
            $types .= "i";
        }

        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    public function addOfficer($firstname, $lastname, $username, $password, $gender) {
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO admin (username, password, firstname, lastname, gender, role, created_on) 
                 VALUES (?, ?, ?, ?, ?, 'officer', NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("sssss", 
            $username,
            $hashedPassword,
            $firstname,
            $lastname,
            $gender
        );
        
        return $stmt->execute();
    }

    public function updateOfficer($id, $firstname, $lastname, $username, $password, $gender) {
        // Prevent updating self
        if ($id == $this->getId()) {
            return false;
        }

        $query = "UPDATE admin SET username = ?, firstname = ?, lastname = ?, gender = ?";
        $params = [$username, $firstname, $lastname, $gender];
        $types = "ssss";

        // Update password if provided
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $query .= ", password = ?";
            $params[] = $hashedPassword;
            $types .= "s";
        }

        $query .= " WHERE id = ? AND role = 'officer'";
        $params[] = $id;
        $types .= "i";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        return $stmt->execute();
    }

    public function deleteOfficer($id) {
        // Prevent deleting self
        if ($id == $this->getId()) {
            return false;
        }

        $query = "DELETE FROM admin WHERE id = ? AND role = 'officer'";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}