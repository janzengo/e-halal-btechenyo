<?php
require_once __DIR__ . '/../../classes/Database.php';

class Ballot {
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

    public function getTotalPositions() {
        $query = "SELECT COUNT(*) as count FROM positions";
        $result = $this->db->query($query);
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    public function getPosition($id) {
        $id = (int)$id;
        $query = "SELECT * FROM positions WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
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

    public function getCandidatesForPosition($position_id) {
        $query = "SELECT c.*, p.name AS partylist_name 
                 FROM candidates c 
                 LEFT JOIN partylists p ON c.partylist_id = p.id 
                 WHERE c.position_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $position_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $candidates = [];
        while ($row = $result->fetch_assoc()) {
            $candidates[] = $row;
        }
        return $candidates;
    }

    public function movePositionUp($id) {
        $id = (int)$id;
        $position = $this->getPosition($id);
        
        if (!$position) {
            return ['error' => true, 'message' => 'Position not found'];
        }

        $priority = $position['priority'] - 1;
        
        if ($priority == 0) {
            return ['error' => true, 'message' => 'This position is already at the top'];
        }

        // Start transaction
        $this->db->begin_transaction();
        try {
            // Update the position that's currently at the target priority
            $query = "UPDATE positions SET priority = priority + 1 WHERE priority = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $priority);
            $stmt->execute();

            // Update the current position's priority
            $query = "UPDATE positions SET priority = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ii", $priority, $id);
            $stmt->execute();

            $this->db->commit();
            return ['error' => false];
        } catch (Exception $e) {
            $this->db->rollback();
            return ['error' => true, 'message' => $e->getMessage()];
        }
    }

    public function movePositionDown($id) {
        $id = (int)$id;
        $position = $this->getPosition($id);
        $total_positions = $this->getTotalPositions();
        
        if (!$position) {
            return ['error' => true, 'message' => 'Position not found'];
        }

        $priority = $position['priority'] + 1;
        
        if ($priority > $total_positions) {
            return ['error' => true, 'message' => 'This position is already at the bottom'];
        }

        // Start transaction
        $this->db->begin_transaction();
        try {
            // Update the position that's currently at the target priority
            $query = "UPDATE positions SET priority = priority - 1 WHERE priority = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $priority);
            $stmt->execute();

            // Update the current position's priority
            $query = "UPDATE positions SET priority = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ii", $priority, $id);
            $stmt->execute();

            $this->db->commit();
            return ['error' => false];
        } catch (Exception $e) {
            $this->db->rollback();
            return ['error' => true, 'message' => $e->getMessage()];
        }
    }

    public function reorderPositions() {
        $positions = $this->getAllPositions();
        $priority = 1;
        
        $this->db->begin_transaction();
        try {
            foreach ($positions as $position) {
                $query = "UPDATE positions SET priority = ? WHERE id = ?";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("ii", $priority, $position['id']);
                $stmt->execute();
                $priority++;
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
} 