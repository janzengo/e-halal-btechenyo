<?php
require_once __DIR__ . '/../../classes/Database.php';

class Partylist {
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

    public function getAllPartylists() {
        $query = "SELECT * FROM partylists ORDER BY name ASC";
        $result = $this->db->query($query);
        $partylists = [];
        while ($row = $result->fetch_assoc()) {
            $partylists[] = $row;
        }
        return $partylists;
    }

    public function getPartylist($id) {
        $id = (int)$id;
        $query = "SELECT * FROM partylists WHERE id = $id";
        $result = $this->db->query($query);
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return false;
    }

    public function addPartylist($name) {
        $query = "INSERT INTO partylists (name) VALUES (?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $name);
        return $stmt->execute();
    }

    public function updatePartylist($id, $name) {
        $query = "UPDATE partylists SET name = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("si", $name, $id);
        return $stmt->execute();
    }

    public function deletePartylist($id) {
        // First check if there are any candidates in this partylist
        $query = "SELECT COUNT(*) as count FROM candidates WHERE partylist_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['count'] > 0) {
            return false; // Cannot delete if there are candidates
        }

        $query = "DELETE FROM partylists WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getPartylistCount() {
        $query = "SELECT COUNT(*) as count FROM partylists";
        $result = $this->db->query($query);
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    /**
     * Get the number of candidates under a partylist
     * @param int $partylistId
     * @return int
     */
    public function getCandidateCount($partylistId) {
        $query = "SELECT COUNT(*) as count FROM candidates WHERE partylist_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $partylistId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    /**
     * Get all partylists with candidate counts
     * @return array
     */
    public function getAllPartylistsWithCounts() {
        $sql = "SELECT p.*, COUNT(c.id) as candidate_count 
                FROM partylists p 
                LEFT JOIN candidates c ON p.id = c.partylist_id 
                GROUP BY p.id 
                ORDER BY p.name";
        $result = $this->db->query($sql);
        $partylists = [];
        while ($row = $result->fetch_assoc()) {
            $partylists[] = $row;
        }
        return $partylists;
    }
} 