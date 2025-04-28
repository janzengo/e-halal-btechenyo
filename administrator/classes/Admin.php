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
    private $email;

    const ROLE_SUPERADMIN = 'head';
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
                $this->email = $admin['email'];
            }
        }
    }

    public function login($username, $password) {
        require_once __DIR__ . '/LoginDebugger.php';
        $debug = LoginDebugger::getInstance();
        
        // Ensure log directory exists and is writable
        $logDir = __DIR__ . '/../../logs';
        $debug->log("Setting up log directory", ['path' => $logDir]);
        
        try {
            if (!file_exists($logDir)) {
                if (!@mkdir($logDir, 0777, true)) {
                    $debug->log("Failed to create log directory", [
                        'path' => $logDir,
                        'error' => error_get_last()
                    ]);
                }
            }
            
            if (!is_writable($logDir)) {
                if (!@chmod($logDir, 0777)) {
                    $debug->log("Failed to make log directory writable", [
                        'path' => $logDir,
                        'error' => error_get_last()
                    ]);
                }
            }
            
            $logFile = $logDir . '/login_debug.log';
            if (!file_exists($logFile)) {
                if (!@touch($logFile)) {
                    $debug->log("Failed to create log file", [
                        'path' => $logFile,
                        'error' => error_get_last()
                    ]);
                }
            }
            
            if (file_exists($logFile) && !is_writable($logFile)) {
                if (!@chmod($logFile, 0666)) {
                    $debug->log("Failed to make log file writable", [
                        'path' => $logFile,
                        'error' => error_get_last()
                    ]);
                }
            }
        } catch (Exception $e) {
            $debug->log("Error setting up log files", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        $debug->log("Login attempt started", ['username' => $username]);
        $debug->logSessionState("Before login attempt");

        try {
            // Get database connection
            $conn = $this->db->getConnection();
            $debug->log("Database connection obtained");

            $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
            if (!$stmt) {
                $debug->log("Failed to prepare statement", ['error' => $conn->error]);
                throw new Exception('System error occurred. Please try again.');
            }
            
            $stmt->bind_param("s", $username);
            $debug->log("Statement prepared with username", ['username' => $username]);
            
            if (!$stmt->execute()) {
                $debug->log("Failed to execute statement", ['error' => $stmt->error]);
                throw new Exception('System error occurred. Please try again.');
            }
            
            $result = $stmt->get_result();
            if ($result === false) {
                $debug->log("Failed to get result", ['error' => $stmt->error]);
                throw new Exception('System error occurred. Please try again.');
            }

            if ($result->num_rows === 0) {
                $debug->log("No user found for username", ['username' => $username]);
                throw new Exception('Incorrect username or password');
            }
    
            $row = $result->fetch_assoc();
            $debug->log("User found", ['role' => $row['role'], 'username' => $username]);
            
            if (!password_verify($password, $row['password'])) {
                $debug->log("Password verification failed for user", ['username' => $username]);
                throw new Exception('Incorrect username or password');
            }
            
            $debug->log("Password verified successfully for user", ['username' => $username, 'role' => $row['role']]);

            // Check if user is an officer and handle election status
            if ($row['role'] === 'officer') {
                $debug->log("User is an officer, checking election status");
                require_once __DIR__ . '/Elections.php';
                $elections = Elections::getInstance();
                $currentStatus = $elections->getCurrentStatus();
                $debug->log("Retrieved election status for officer login", [
                    'username' => $username,
                    'current_status' => $currentStatus,
                    'raw_status_check' => [
                        'is_pending' => ($currentStatus === Elections::STATUS_PENDING),
                        'is_active' => ($currentStatus === Elections::STATUS_ACTIVE),
                        'status_value' => $currentStatus,
                        'pending_const' => Elections::STATUS_PENDING,
                        'active_const' => Elections::STATUS_ACTIVE
                    ]
                ]);

                // Officers can access during pending or active election
                if ($currentStatus === Elections::STATUS_PENDING || $currentStatus === Elections::STATUS_ACTIVE) {
                    $debug->log("Officer access granted - election status is valid", [
                        'username' => $username,
                        'current_status' => $currentStatus
                    ]);
                } else {
                    $debug->log("Officer access denied - election is not pending or active", [
                        'username' => $username,
                        'current_status' => $currentStatus,
                        'expected_statuses' => [Elections::STATUS_PENDING, Elections::STATUS_ACTIVE]
                    ]);
                    throw new Exception('Access denied. Officers can only login during pending or active elections.');
                }
            } else {
                $debug->log("User is head admin, proceeding with login", ['username' => $username]);
            }

            $debug->log("Setting session variables", [
                'admin_id' => $row['id'],
                'role' => $row['role']
            ]);
            
            $_SESSION['admin'] = $row['id'];
            $_SESSION['role'] = $row['role'];
            
            // Log the successful login
            require_once __DIR__ . '/Logger.php';
            $logger = AdminLogger::getInstance();
            $logger->logAdminAction($row['username'], $row['role'], 'Logged in successfully');
            
            $debug->log("Login successful", [
                'username' => $username,
                'role' => $row['role']
            ]);
            $debug->logSessionState("After successful login");
            
            return true;
            
        } catch (Exception $e) {
            $debug->log("Login error occurred", [
                'username' => $username,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
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
        $this->email = null;
        
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
            'created_on' => $this->created_on,
            'email' => $this->email
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
    // Public getter for db
    public function getDbInstance() { return $this->db; }
    public function getPhoto() { return $this->photo; }
    public function getRole() { return $this->role; }
    public function getGender() { return $this->gender; }
    public function getEmail() { return $this->email; }

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
     * Check if the logged-in user is an admin (head)
     * 
     * @return bool True if user is admin, false otherwise
     */
    public function isAdmin() {
        return $this->isLoggedIn() && $this->role === 'head';
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
     * Get admin data by username
     * @param string $username Username to look up
     * @return array|null Admin data array or null if not found
     */
    public function getAdminByUsername($username) {
        try {
            $stmt = $this->db->getConnection()->prepare("SELECT * FROM admin WHERE username = ?");
            if (!$stmt) {
                throw new Exception('Failed to prepare statement');
            }
            
            $stmt->bind_param("s", $username);
            if (!$stmt->execute()) {
                throw new Exception('Failed to execute statement');
            }
            
            $result = $stmt->get_result();
            if ($result === false) {
                throw new Exception('Failed to get result');
            }
            
            if ($result->num_rows === 0) {
                return null;
            }
            
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error in getAdminByUsername: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if current admin is head
     * @return bool
     */
    public function isHead() {
        return $this->getRole() === self::ROLE_SUPERADMIN;
    }

    /**
     * Alias for isHead() to maintain compatibility with previous code references.
     * @return bool
     */
    public function isElectoralHead() {
        return $this->isHead();
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
     * @param string|null $email New email
     * @param string|null $photo Photo path
     * @return bool Success status
     */
    public function updateProfile($username, $firstname, $lastname, $email = null, $photo = null) {
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
            $sql = "UPDATE admin SET username = ?, firstname = ?, lastname = ?, email = ?";
            $params = [$username, $firstname, $lastname, $email];
            $types = "ssss";
            
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
                $this->email = $email;
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

    /**
     * Add a new officer
     * @param string $firstname First name
     * @param string $lastname Last name
     * @param string $username Username
     * @param string $password Password
     * @param string $gender Gender
     * @param string|null $email Email (optional)
     * @return bool Success status
     */
    public function addOfficer($firstname, $lastname, $username, $password, $gender, $email = null) {
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO admin (username, email, password, firstname, lastname, gender, role, created_on) 
                 VALUES (?, ?, ?, ?, ?, ?, 'officer', NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ssssss", 
            $username,
            $email,
            $hashedPassword,
            $firstname,
            $lastname,
            $gender
        );
        
        return $stmt->execute();
    }

    /**
     * Update an officer's information
     * @param int $id Officer ID
     * @param string $firstname First name
     * @param string $lastname Last name
     * @param string $username Username
     * @param string $password Password (optional)
     * @param string $gender Gender
     * @param string|null $email Email (optional)
     * @return bool Success status
     */
    public function updateOfficer($id, $firstname, $lastname, $username, $password, $gender, $email = null) {
        // Prevent updating self
        if ($id == $this->getId()) {
            return false;
        }

        $query = "UPDATE admin SET username = ?, firstname = ?, lastname = ?, gender = ?, email = ?";
        $params = [$username, $firstname, $lastname, $gender, $email];
        $types = "sssss";

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

    /**
     * Validate email format
     * @param string|null $email Email to validate
     * @return bool True if valid or null, false otherwise
     */
    public function validateEmail($email) {
        if ($email === null || $email === '') {
            return true;
        }
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Check if email exists (excluding specific ID)
     * @param string $email Email to check
     * @param int|null $excludeId ID to exclude from check
     * @return bool True if email exists
     */
    public function checkEmail($email, $excludeId = null) {
        if ($email === null || $email === '') {
            return false;
        }

        $query = "SELECT id FROM admin WHERE email = ?";
        $params = [$email];
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

    /**
     * Verify admin credentials without completing login
     * 
     * @param string $username Username to verify
     * @param string $password Password to verify
     * @return bool True if credentials are valid
     */
    public function verifyCredentials($username, $password) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM admin WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                if (password_verify($password, $admin['password'])) {
                    return true;
                }
            }
            return false;
        } catch (Exception $e) {
            error_log("Error in verifyCredentials: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Complete the login process after OTP verification
     * 
     * @param string $username Username to login
     * @return bool True if login successful
     */
    public function completeLogin($username) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM admin WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                
                // Set all required admin session variables
                $this->session->setSession('admin', $admin['id']);
                $this->session->setSession('admin_username', $admin['username']);
                $this->session->setSession('admin_role', $admin['role']);
                
                // Set instance properties
                $this->id = $admin['id'];
                $this->username = $admin['username'];
                $this->firstname = $admin['firstname'];
                $this->lastname = $admin['lastname'];
                $this->photo = $admin['photo'];
                $this->role = $admin['role'];
                $this->gender = $admin['gender'];
                $this->email = $admin['email'];
                
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error in completeLogin: " . $e->getMessage());
            return false;
        }
    }
}