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
        $stmt = $this->db->prepare($query);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $positions = [];
            while ($row = $result->fetch_assoc()) {
                $positions[] = $row;
            }
            return $positions;
        }
        return [];
    }

    public function getPositionCount() {
        $query = "SELECT COUNT(*) as count FROM positions";
        $stmt = $this->db->prepare($query);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            return (int)$row['count'];
        }
        return 0;
    }

    public function addPosition($description, $maxVote) {
        try {
            // Get current highest priority
            $query = "SELECT MAX(priority) as max_priority FROM positions";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $priority = ($row['max_priority'] ?? 0) + 1;

            // Start transaction
            $this->db->getConnection()->begin_transaction();

            // Insert new position
            $query = "INSERT INTO positions (description, max_vote, priority) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("sii", $description, $maxVote, $priority);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to add position");
            }

            $id = $stmt->insert_id;
            $this->db->getConnection()->commit();
            return ['id' => $id, 'priority' => $priority];
        } catch (Exception $e) {
            $this->db->getConnection()->rollback();
            throw $e;
        }
    }

    public function updatePosition($id, $description, $maxVote) {
        try {
            $query = "UPDATE positions SET description = ?, max_vote = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("sii", $description, $maxVote, $id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update position");
            }
            
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function deletePosition($id) {
        try {
            $this->db->getConnection()->begin_transaction();

            // Get current priority
            $query = "SELECT priority FROM positions WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $position = $result->fetch_assoc();
            
            if (!$position) {
                throw new Exception("Position not found");
            }

            // Delete the position
            $query = "DELETE FROM positions WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to delete position");
            }

            // Reorder remaining positions
            $query = "UPDATE positions SET priority = priority - 1 WHERE priority > ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $position['priority']);
            $stmt->execute();

            $this->db->getConnection()->commit();
            return true;
        } catch (Exception $e) {
            $this->db->getConnection()->rollback();
            throw $e;
        }
    }

    public function getPosition($id) {
        $query = "SELECT * FROM positions WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        }
        return null;
    }

    public function reorderPositions($positions) {
        try {
            $this->db->getConnection()->begin_transaction();
            
            $query = "UPDATE positions SET priority = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            
            foreach ($positions as $priority => $id) {
                $priority = $priority + 1; // Convert to 1-based index
                $stmt->bind_param("ii", $priority, $id);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update position order");
                }
            }
            
            $this->db->getConnection()->commit();
            return true;
        } catch (Exception $e) {
            $this->db->getConnection()->rollback();
            throw $e;
        }
    }

    public function getPositionsByPriority() {
        $query = "SELECT * FROM positions ORDER BY priority ASC";
        $stmt = $this->db->prepare($query);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }
}