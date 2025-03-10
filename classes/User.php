<?php
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/CustomSessionHandler.php';
require_once __DIR__ . '/Logger.php';

class User {
    private $db;
    private $session;
    private $logger;
    protected $id;
    protected $student_number;
    protected $course_id;
    protected $has_voted;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->session = CustomSessionHandler::getInstance();
        $this->logger = new Logger();
    }

    /**
     * Authenticate a user using OTP verification
     * 
     * @param string $student_number Student's ID number
     * @return bool True if student exists, false otherwise
     */
    public function authenticateWithOTP($student_number) {
        $sql = "SELECT * FROM voters WHERE student_number = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $student_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows < 1) {
            // Log failed authentication attempt
            $this->logger->generateLog(
                'student',
                date('Y-m-d H:i:s'),
                $student_number,
                'Failed authentication attempt - Student not found'
            );
            return false;
        }

        $voter = $result->fetch_assoc();
        $this->id = $voter['id'];
        $this->student_number = $voter['student_number'];
        $this->course_id = $voter['course_id'];
        $this->has_voted = $voter['has_voted'];

        // Log successful authentication
        $this->logger->generateLog(
            'student',
            date('Y-m-d H:i:s'),
            $student_number,
            'Successful authentication'
        );
        
        return true;
    }

    /**
     * Set user session after successful OTP verification
     * 
     * @return void
     */
    public function setSession() {
        $this->session->setSession('voter', $this->id);
        $this->session->setSession('student_number', $this->student_number);
        session_regenerate_id(true);

        // Log session creation
        $this->logger->generateLog(
            'student',
            date('Y-m-d H:i:s'),
            $this->student_number,
            'Session created'
        );
    }

    public function logout() {
        // Log logout action
        if ($this->student_number) {
            $this->logger->generateLog(
                'student',
                date('Y-m-d H:i:s'),
                $this->student_number,
                'User logged out'
            );
        }

        $this->session->unsetSession('voter');
        $this->session->unsetSession('student_number');
        $this->session->destroySession();
    }

    public function isLoggedIn() {
        return $this->session->getSession('voter') !== null;
    }

    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $voter_id = $this->session->getSession('voter');
        $sql = "SELECT * FROM voters WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $voter_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows < 1) {
            return null;
        }

        return $result->fetch_assoc();
    }

    public function hasVoted() {
        if (!$this->isLoggedIn()) {
            return false;
        }

        $voter = $this->getCurrentUser();
        return $voter['has_voted'] == 1;
    }

    /**
     * Mark a voter as having voted
     * 
     * @return bool Success of operation
     */
    public function markAsVoted() {
        if (!$this->isLoggedIn()) {
            return false;
        }

        $voter_id = $this->session->getSession('voter');
        $sql = "UPDATE voters SET has_voted = 1 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $voter_id);
        $success = $stmt->execute();

        if ($success) {
            $this->logger->generateLog(
                'student',
                date('Y-m-d H:i:s'),
                $this->student_number,
                'Vote recorded'
            );
        }

        return $success;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getStudentNumber() { return $this->student_number; }
    public function getCourseId() { return $this->course_id; }
    public function getHasVoted() { return $this->has_voted; }
}