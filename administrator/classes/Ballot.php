<?php
require_once __DIR__ . '/../../classes/Database.php';

class AdminBallot {
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
        
        if (!$result) {
            error_log("Query failed in getAllPositions: " . $this->db->error);
            return [];
        }
        
        $positions = [];
        while ($row = $result->fetch_assoc()) {
            $positions[] = $row;
        }
        
        if (empty($positions)) {
            error_log("No positions found in the database");
        }
        
        return $positions;
    }

    public function getCandidatesForPosition($position_id) {
        $position_id = (int)$position_id;
        $query = "SELECT c.*, p.name AS partylist_name 
                 FROM candidates c 
                 LEFT JOIN partylists p ON c.partylist_id = p.id 
                 WHERE c.position_id = ?";
        $stmt = $this->db->prepare($query);
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("i", $position_id);
        
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return [];
        }
        
        $result = $stmt->get_result();
        $candidates = [];
        
        while ($row = $result->fetch_assoc()) {
            $candidates[] = $row;
        }
        
        return $candidates;
    }

    public function movePositionUp($id) {
        try {
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
            if (!$this->db->beginTransaction()) {
                return ['error' => true, 'message' => 'Could not start transaction: ' . $this->db->getError()];
            }

        try {
            // Update the position that's currently at the target priority
            $query = "UPDATE positions SET priority = priority + 1 WHERE priority = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $priority);
                if (!$stmt->execute()) {
                    throw new Exception("Error executing query: " . $stmt->error);
                }

            // Update the current position's priority
            $query = "UPDATE positions SET priority = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ii", $priority, $id);
                if (!$stmt->execute()) {
                    throw new Exception("Error executing query: " . $stmt->error);
                }

            $this->db->commit();
                return ['error' => false, 'message' => 'Position moved successfully', 'id' => $id];
            } catch (Exception $e) {
                $this->db->rollback();
                return ['error' => true, 'message' => 'Database error: ' . $e->getMessage()];
            }
        } catch (Exception $e) {
            return ['error' => true, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function movePositionDown($id) {
        try {
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
            if (!$this->db->beginTransaction()) {
                return ['error' => true, 'message' => 'Could not start transaction: ' . $this->db->getError()];
            }

        try {
            // Update the position that's currently at the target priority
            $query = "UPDATE positions SET priority = priority - 1 WHERE priority = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $priority);
                if (!$stmt->execute()) {
                    throw new Exception("Error executing query: " . $stmt->error);
                }

            // Update the current position's priority
            $query = "UPDATE positions SET priority = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ii", $priority, $id);
                if (!$stmt->execute()) {
                    throw new Exception("Error executing query: " . $stmt->error);
                }

            $this->db->commit();
                return ['error' => false, 'message' => 'Position moved successfully', 'id' => $id];
            } catch (Exception $e) {
                $this->db->rollback();
                return ['error' => true, 'message' => 'Database error: ' . $e->getMessage()];
            }
        } catch (Exception $e) {
            return ['error' => true, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function reorderPositions() {
        try {
        $positions = $this->getAllPositions();
        $priority = 1;
        
            if (!$this->db->beginTransaction()) {
                return false;
            }
            
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
                error_log("Error in reorderPositions: " . $e->getMessage());
                return false;
            }
        } catch (Exception $e) {
            error_log("Error in reorderPositions: " . $e->getMessage());
            return false;
        }
    }
    
    public function renderAdminBallot() {
        $positions = $this->getAllPositions();
        $total_positions = $this->getTotalPositions();
        $output = '';
        
        // Debug output
        if (empty($positions)) {
            return '<div class="alert alert-warning">No positions found in the database. Please add positions in the Positions Management page first.</div>';
        }
        
        foreach ($positions as $row) {
            // Determine if up/down buttons should be disabled
            $updisable = ($row['priority'] == 1) ? 'disabled' : '';
            $downdisable = ($row['priority'] == $total_positions) ? 'disabled' : '';
            
            $candidates_html = '';
            $candidates = $this->getCandidatesForPosition($row['id']);
            
            foreach ($candidates as $candidate) {
                // Fix image paths to use absolute path from root
                $photo = !empty($candidate['photo']) ? $candidate['photo'] : 'assets/images/profile.jpg';
                $partylist = !empty($candidate['partylist_name']) ? $candidate['partylist_name'] : 'Independent';
                
                $candidates_html .= '
                <div class="candidate-card">
                    <div class="card-content">
                        <div class="candidate-photo-container">
                            <img src="' . $photo . '" alt="Candidate Photo" class="candidate-photo">
                        </div>
                        <div class="candidate-info">
                            <strong class="candidate-name">' . $candidate['firstname'] . ' ' . $candidate['lastname'] . '</strong>
                            <p class="candidate-party">' . $partylist . '</p>
                            ' . (!empty($candidate['platform']) ? '
                            <button type="button" class="btn btn-primary btn-sm platform" 
                                    data-platform="' . htmlspecialchars($candidate['platform']) . '"
                                    data-fullname="' . htmlspecialchars($candidate['firstname'] . ' ' . $candidate['lastname']) . '"
                                    data-image="' . $candidate['photo'] . '">
                                <i class="fa fa-search"></i> Platform
                            </button>' : '') . '
                        </div>
                    </div>
                </div>';
            }
            
            $instruct = ($row['max_vote'] > 1) ? 'You may select up to ' . $row['max_vote'] . ' candidates' : 'Select only one candidate';
            
            $output .= '
            <div class="position-section" id="position-' . $row['id'] . '">
                <div class="position-header">
                    <div class="title-and-instruction">
                        <h3 class="position-title">' . $row['description'] . '</h3>
                        <p class="position-instruction">' . $instruct . '</p>
                    </div>
                    <div class="position-controls">
                        <button type="button" class="btn btn-default moveup" data-id="' . $row['id'] . '" ' . $updisable . '>
                            <i class="fa fa-arrow-up"></i> Move Up
                        </button>
                        <button type="button" class="btn btn-default movedown" data-id="' . $row['id'] . '" ' . $downdisable . '>
                            <i class="fa fa-arrow-down"></i> Move Down
                        </button>
                    </div>
                </div>
                <div class="candidates-grid">
                    ' . $candidates_html . '
                </div>
            </div>';
        }
        
        return $output . '
        <style>
        .position-section {
            margin-bottom: 30px;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .position-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .title-and-instruction {
            flex: 1;
        }
        
        .position-title {
            margin-top: 0;
            color: #333;
        }
        
        .position-instruction {
            color: #666;
            margin-bottom: 0;
        }
        
        .position-controls {
            display: flex;
            gap: 10px;
        }
        
        .candidates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .candidate-card {
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        
        .card-content {
            padding: 15px;
        }
        
        .candidate-photo-container {
            width: 150px;
            height: 150px;
            margin: 0 auto 15px;
            border-radius: 50%;
            overflow: hidden;
        }
        
        .candidate-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .candidate-info {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .candidate-name {
            display: block;
            font-size: 1.1em;
            margin-bottom: 5px;
            color: #333;
        }
        
        .candidate-party {
            color: #666;
            margin: 0;
        }
        
        .platform {
            width: 100%;
            margin-top: 10px;
        }
        </style>';
    }
}
