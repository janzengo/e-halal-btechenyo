<?php
require_once __DIR__ . '/../../classes/Database.php';

class Voter {
    private $db;
    private static $instance = null;

    private function __construct() {
        $this->db = Database::getInstance();
        $this->ensureVotedColumnExists();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function ensureVotedColumnExists() {
        // Check if voted column exists
        $query = "SHOW COLUMNS FROM voters LIKE 'voted'";
        $result = $this->db->query($query);
        
        if ($result->num_rows === 0) {
            // Add voted column if it doesn't exist
            $query = "ALTER TABLE voters ADD COLUMN voted TINYINT(1) NOT NULL DEFAULT 0";
            $this->db->query($query);
        }
    }

    public function getAllVoters() {
        $query = "SELECT * FROM voters ORDER BY lastname ASC, firstname ASC";
        $result = $this->db->query($query);
        $voters = [];
        while ($row = $result->fetch_assoc()) {
            $voters[] = $row;
        }
        return $voters;
    }

    public function getVoterCount() {
        $query = "SELECT COUNT(*) as count FROM voters";
        $result = $this->db->query($query);
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    public function getVotersWhoVoted() {
        try {
            $query = "SELECT COUNT(*) as count FROM voters WHERE voted = 1";
            $result = $this->db->query($query);
            $row = $result->fetch_assoc();
            return $row['count'];
        } catch (Exception $e) {
            // If there's an error (like missing column), return 0
            return 0;
        }
    }

    public function addVoter($voters_id, $password, $firstname, $lastname, $photo = '') {
        $voters_id = $this->db->escape($voters_id);
        $password = password_hash($password, PASSWORD_DEFAULT);
        $firstname = $this->db->escape($firstname);
        $lastname = $this->db->escape($lastname);
        $photo = $this->db->escape($photo);
        
        $query = "INSERT INTO voters (voters_id, password, firstname, lastname, photo) 
                 VALUES ('$voters_id', '$password', '$firstname', '$lastname', '$photo')";
        return $this->db->query($query);
    }

    public function updateVoter($id, $voters_id, $firstname, $lastname, $photo = null) {
        $id = (int)$id;
        $voters_id = $this->db->escape($voters_id);
        $firstname = $this->db->escape($firstname);
        $lastname = $this->db->escape($lastname);
        
        $photoClause = $photo ? ", photo = '" . $this->db->escape($photo) . "'" : "";
        
        $query = "UPDATE voters 
                 SET voters_id = '$voters_id', 
                     firstname = '$firstname', 
                     lastname = '$lastname'" . $photoClause . 
                 " WHERE id = $id";
        return $this->db->query($query);
    }

    public function deleteVoter($id) {
        $id = (int)$id;
        $query = "DELETE FROM voters WHERE id = $id";
        return $this->db->query($query);
    }

    public function markVoterAsVoted($id) {
        $id = (int)$id;
        $query = "UPDATE voters SET voted = 1 WHERE id = $id";
        return $this->db->query($query);
    }
}
