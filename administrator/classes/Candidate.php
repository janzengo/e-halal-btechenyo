<?php
require_once __DIR__ . '/../../classes/Database.php';

class Candidate {
    private $db;
    private static $instance = null;

    private function __construct() {
        $this->db = Database::getInstance();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getAllCandidates() {
        $query = "SELECT c.*, p.description as position 
                 FROM candidates c 
                 LEFT JOIN positions p ON p.id = c.position_id 
                 ORDER BY p.priority ASC";
        $result = $this->db->query($query);
        $candidates = [];
        while ($row = $result->fetch_assoc()) {
            $candidates[] = $row;
        }
        return $candidates;
    }

    public function getCandidatesByPosition($position_id) {
        $position_id = (int)$position_id;
        $query = "SELECT c.* FROM candidates c 
                 WHERE c.position_id = $position_id 
                 ORDER BY c.lastname ASC, c.firstname ASC";
        $result = $this->db->query($query);
        $candidates = [];
        while ($row = $result->fetch_assoc()) {
            $candidates[] = $row;
        }
        return $candidates;
    }

    public function getCandidateCount() {
        $query = "SELECT COUNT(*) as count FROM candidates";
        $result = $this->db->query($query);
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    public function addCandidate($firstname, $lastname, $position_id, $platform, $photo = '') {
        $firstname = $this->db->escape($firstname);
        $lastname = $this->db->escape($lastname);
        $position_id = (int)$position_id;
        $platform = $this->db->escape($platform);
        $photo = $this->db->escape($photo);
        
        $query = "INSERT INTO candidates (firstname, lastname, position_id, platform, photo) 
                 VALUES ('$firstname', '$lastname', $position_id, '$platform', '$photo')";
        return $this->db->query($query);
    }

    public function updateCandidate($id, $firstname, $lastname, $position_id, $platform, $photo = null) {
        $id = (int)$id;
        $firstname = $this->db->escape($firstname);
        $lastname = $this->db->escape($lastname);
        $position_id = (int)$position_id;
        $platform = $this->db->escape($platform);
        
        $photoClause = $photo ? ", photo = '" . $this->db->escape($photo) . "'" : "";
        
        $query = "UPDATE candidates 
                 SET firstname = '$firstname', 
                     lastname = '$lastname', 
                     position_id = $position_id, 
                     platform = '$platform'" . $photoClause . 
                 " WHERE id = $id";
        return $this->db->query($query);
    }

    public function deleteCandidate($id) {
        $id = (int)$id;
        $query = "DELETE FROM candidates WHERE id = $id";
        return $this->db->query($query);
    }
}
