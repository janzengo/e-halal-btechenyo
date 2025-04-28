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
        $query = "SELECT id, description, max_vote, priority FROM positions ORDER BY priority ASC";
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

    /**
     * Check if a position with the given description already exists
     * @param string $description Position description
     * @param int|null $excludeId Optional ID to exclude from the check (for updates)
     * @return bool
     */
    public function positionExists($description, $excludeId = null) {
        $query = "SELECT COUNT(*) as count FROM positions WHERE LOWER(description) = LOWER(?)";
        $params = [$description];
        $types = "s";
        
        if ($excludeId !== null) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
            $types .= "i";
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            return $row['count'] > 0;
        }
        return false;
    }

    /**
     * Add a new position
     * @param string $description Position description
     * @param int $max_vote Maximum votes allowed
     * @return bool|array
     */
    public function addPosition($description, $max_vote) {
        try {
            // Check for duplicate position
            if ($this->positionExists($description)) {
                throw new Exception("A position with this name already exists");
            }

            // Get current highest priority
            $query = "SELECT MAX(priority) as max_priority FROM positions";
            $result = $this->db->query($query);
            $row = $result->fetch_assoc();
            $priority = ($row['max_priority'] ?? 0) + 1;
            
            // Insert new position
            $query = "INSERT INTO positions (description, max_vote, priority) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("sii", $description, $max_vote, $priority);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to add position");
            }
            
            return ['id' => $stmt->insert_id, 'priority' => $priority];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Update an existing position
     * @param int $id Position ID
     * @param string $description Position description
     * @param int $max_vote Maximum votes allowed
     * @param int $priority Position priority (lower number = higher priority)
     * @return bool
     */
    public function updatePosition($id, $description, $max_vote, $priority = 1) {
        // Check for duplicate position, excluding current position
        if ($this->positionExists($description, $id)) {
            throw new Exception("A position with this name already exists");
        }

        $query = "UPDATE positions SET description = ?, max_vote = ?, priority = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("siii", $description, $max_vote, $priority, $id);
        return $stmt->execute();
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

    /**
     * Get all positions sorted by priority (lowest number first)
     * @return array
     */
    public function getAllPositionsByPriority() {
        $sql = "SELECT * FROM positions ORDER BY priority ASC";
        $result = $this->db->query($sql);
        $positions = [];
        while ($row = $result->fetch_assoc()) {
            $positions[] = $row;
        }
        return $positions;
    }
}