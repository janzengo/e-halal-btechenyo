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

    public function getCandidate($id) {
        $id = (int)$id;
        $query = "SELECT c.*, p.description as position_name 
                 FROM candidates c 
                 LEFT JOIN positions p ON p.id = c.position_id 
                 WHERE c.id = $id";
        $result = $this->db->query($query);
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return false;
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
        $query = "INSERT INTO candidates (firstname, lastname, position_id, platform, photo) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ssiss", $firstname, $lastname, $position_id, $platform, $photo);
        return $stmt->execute();
    }

    public function updateCandidate($id, $firstname, $lastname, $position_id, $platform, $photo = null) {
        if ($photo !== null) {
            $query = "UPDATE candidates 
                     SET firstname = ?, lastname = ?, position_id = ?, platform = ?, photo = ? 
                     WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ssissi", $firstname, $lastname, $position_id, $platform, $photo, $id);
        } else {
            $query = "UPDATE candidates 
                     SET firstname = ?, lastname = ?, position_id = ?, platform = ? 
                     WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ssisi", $firstname, $lastname, $position_id, $platform, $id);
        }
        return $stmt->execute();
    }

    public function deleteCandidate($id) {
        $query = "DELETE FROM candidates WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}