<?php
require_once __DIR__ . '/../../classes/Database.php';

class Position {
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

    public function getAllPositions() {
        $query = "SELECT * FROM positions ORDER BY priority ASC";
        $result = $this->db->query($query);
        $positions = [];
        while ($row = $result->fetch_assoc()) {
            $positions[] = $row;
        }
        return $positions;
    }

    public function getPositionCount() {
        $query = "SELECT COUNT(*) as count FROM positions";
        $result = $this->db->query($query);
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    public function addPosition($description, $maxVote, $priority) {
        $description = $this->db->escape($description);
        $maxVote = (int)$maxVote;
        $priority = (int)$priority;
        
        $query = "INSERT INTO positions (description, max_vote, priority) 
                 VALUES ('$description', $maxVote, $priority)";
        return $this->db->query($query);
    }

    public function updatePosition($id, $description, $maxVote, $priority) {
        $id = (int)$id;
        $description = $this->db->escape($description);
        $maxVote = (int)$maxVote;
        $priority = (int)$priority;
        
        $query = "UPDATE positions 
                 SET description = '$description', 
                     max_vote = $maxVote, 
                     priority = $priority 
                 WHERE id = $id";
        return $this->db->query($query);
    }

    public function deletePosition($id) {
        $id = (int)$id;
        $query = "DELETE FROM positions WHERE id = $id";
        return $this->db->query($query);
    }
}
