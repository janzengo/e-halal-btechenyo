<?php
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/CustomSessionHandler.php';

class User {
    private $db;
    private $session;
    protected $id;
    protected $firstname;
    protected $lastname;
    protected $photo;
    protected $voters_id;
    protected $password;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->session = CustomSessionHandler::getInstance();
    }

    public function login($voters_id, $password) {
        $sql = "SELECT * FROM voters WHERE voters_id = '" . $this->db->escape($voters_id) . "'";
        $query = $this->db->query($sql);

        if ($query->num_rows < 1) {
            return false;
        }

        $voter = $query->fetch_assoc();

        if (password_verify($password, $voter['password'])) {
            $this->id = $voter['id'];
            $this->firstname = $voter['firstname'];
            $this->lastname = $voter['lastname'];
            $this->photo = $voter['photo'];
            $this->voters_id = $voter['voters_id'];
            
            $this->session->setSession('voter', $this->id);
            return true;
        }

        return false;
    }

    public function logout() {
        $this->session->unsetSession('voter');
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
        $sql = "SELECT * FROM voters WHERE id = '" . $this->db->escape($voter_id) . "'";
        $query = $this->db->query($sql);

        if ($query->num_rows < 1) {
            return null;
        }

        return $query->fetch_assoc();
    }

    public function hasVoted() {
        if (!$this->isLoggedIn()) {
            return false;
        }

        $voter_id = $this->session->getSession('voter');
        $sql = "SELECT * FROM votes WHERE voters_id = '" . $this->db->escape($voter_id) . "'";
        $query = $this->db->query($sql);

        return $query->num_rows > 0;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getFirstname() { return $this->firstname; }
    public function getLastname() { return $this->lastname; }
    public function getPhoto() { return $this->photo; }
    public function getVotersId() { return $this->voters_id; }
}