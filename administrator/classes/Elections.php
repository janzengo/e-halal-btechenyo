<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/CustomSessionHandler.php';
require_once __DIR__ . '/Logger.php';
require_once __DIR__ . '/Admin.php';

class Elections {
    private $db;
    private $logger;
    private static $instance = null;

    // Valid election statuses
    const STATUS_ON = 'on';
    const STATUS_OFF = 'off';
    const STATUS_PAUSED = 'paused';

    private function __construct() {
        $this->db = Database::getInstance();
        $this->logger = AdminLogger::getInstance();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get current election status
     * @return array|null
     */
    public function getCurrentElection() {
        $query = "SELECT * FROM election_status WHERE id = 1";
        $result = $this->db->query($query);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }

    /**
     * Validate election status change
     * @param string $newStatus
     * @param array $currentElection
     * @return array ['valid' => bool, 'message' => string]
     */
    private function validateStatusChange($newStatus, $currentElection) {
        $validStatuses = [self::STATUS_ON, self::STATUS_OFF, self::STATUS_PAUSED, 'pending'];
        
        if (!in_array($newStatus, $validStatuses)) {
            return ['valid' => false, 'message' => 'Invalid status provided'];
        }

        // Only validate end time when turning election ON
        if ($newStatus === self::STATUS_ON) {
            $now = new DateTime();
            $endTime = new DateTime($currentElection['end_time']);

            if ($now > $endTime) {
                return ['valid' => false, 'message' => 'Cannot start election after ' . $endTime->format('F j, Y g:i A')];
            }
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * Create or update election configuration
     * @param array $data
     * @return array ['success' => bool, 'message' => string]
     */
    public function configureElection($data) {
        try {
            $this->db->beginTransaction();

            // Validate dates only if status is not pending
            if ($data['status'] !== 'pending') {
                $startTime = new DateTime($data['start_time']);
                $endTime = new DateTime($data['end_time']);

                if ($endTime <= $startTime) {
                    throw new Exception("End time must be after start time");
                }
            }

            // Get current election to check if this is an update or new creation
            $currentElection = $this->getCurrentElection();
            
            // For status validation, use the new data since we're validating the new configuration
            $validation = $this->validateStatusChange($data['status'], $currentElection);
            if (!$validation['valid']) {
                throw new Exception($validation['message']);
            }

            // If turning on manually, set start_time to current time
            if ($data['status'] === self::STATUS_ON && (!$currentElection || $currentElection['status'] !== self::STATUS_ON)) {
                $data['start_time'] = (new DateTime())->format('Y-m-d H:i:s');
            }

            // Prepare variables for binding
            $startTimeParam = isset($data['start_time']) ? $data['start_time'] : null;
            $endTimeParam = isset($data['end_time']) ? $data['end_time'] : null;

            if ($currentElection) {
                // Update existing election
                $query = "UPDATE election_status SET 
                         election_name = ?,
                         status = ?,
                         start_time = ?,
                         end_time = ?
                         WHERE id = 1";
            } else {
                // Create new election
                $query = "INSERT INTO election_status (id, election_name, status, start_time, end_time) 
                         VALUES (1, ?, ?, ?, ?)";
            }

            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ssss", 
                $data['election_name'],
                $data['status'],
                $startTimeParam,
                $endTimeParam
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to " . ($currentElection ? "update" : "create") . " election");
            }

            // Get admin info from session
            $admin = Admin::getInstance();
            
            // Log the configuration change with more detailed information
            $logMessage = ($currentElection ? "Updated" : "Created") . " election configuration:";
            $logMessage .= " Name: {$data['election_name']},";
            $logMessage .= " Status: {$data['status']},";
            $logMessage .= " Start: " . ($data['start_time'] ?? 'Not set');
            $logMessage .= " End: " . ($data['end_time'] ?? 'Not set');

            $this->logger->logAdminAction(
                $admin->getUsername(),
                $admin->getRole(),
                $logMessage
            );

            $this->db->commit();
            return ['success' => true, 'message' => 'Election ' . ($currentElection ? "updated" : "created") . ' successfully'];
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Election configuration error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Update election status
     * @param string $status
     * @return bool
     */
    public function updateStatus($status) {
        $validStatuses = [self::STATUS_ON, self::STATUS_OFF, self::STATUS_PAUSED];
        if (!in_array($status, $validStatuses)) {
            return false;
        }

        $query = "UPDATE election_status SET status = ? WHERE id = 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('s', $status);
        return $stmt->execute();
    }

    /**
     * Archive current election
     * @param array $files PDF file paths
     * @return bool
     */
    public function archiveElection($files) {
        try {
            $this->db->beginTransaction();

            // Get current election details
            $current = $this->getCurrentElection();
            if (!$current) {
                throw new Exception("No active election to archive");
            }

            // Insert into election history
            $query = "INSERT INTO election_history 
                     (election_name, start_date, end_date, details_pdf, results_pdf, created_at) 
                     VALUES (?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($query, [
                $current['election_name'],
                $current['start_time'],
                $current['end_time'],
                $files['details_pdf'],
                $files['results_pdf']
            ]);

            if (!$stmt->execute()) {
                throw new Exception("Failed to archive election");
            }

            // Clear current election status
            $this->updateStatus('off');

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Election archiving error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get election history
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getElectionHistory($limit = 10, $offset = 0) {
        // Validate and sanitize parameters
        $limit = max(1, (int)$limit);
        $offset = max(0, (int)$offset);

        if ($limit === 0) {
            // Special case: return all records for counting
            $query = "SELECT * FROM election_history ORDER BY created_at DESC";
            $result = $this->db->query($query);
            if ($result) {
                $history = [];
                while ($row = $result->fetch_assoc()) {
                    $history[] = $row;
                }
                return $history;
            }
            return [];
        }

        $query = "SELECT * FROM election_history 
                 ORDER BY created_at DESC 
                 LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($query);
        if ($stmt) {
            $stmt->bind_param('ii', $limit, $offset);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $history = [];
                while ($row = $result->fetch_assoc()) {
                    $history[] = $row;
                }
                return $history;
            }
        }
        return [];
    }

    /**
     * Get election statistics
     * @return array
     */
    public function getStatistics() {
        $stats = [];
        
        // Get voter turnout
        $query = "SELECT 
                    COUNT(*) as total_voters,
                    SUM(has_voted = 1) as voted_count,
                    (COUNT(*) - SUM(has_voted = 1)) as not_voted_count
                 FROM voters";
        
        $result = $this->db->query($query);
        if ($result) {
            $stats['voters'] = $result->fetch_assoc();
        }

        // Get position-wise vote counts
        $query = "SELECT 
                    p.description as position,
                    COUNT(DISTINCT v.id) as vote_count
                 FROM positions p
                 LEFT JOIN candidates c ON c.position_id = p.id
                 LEFT JOIN votes v ON v.votes_data LIKE CONCAT('%\"', c.id, '\"%')
                 GROUP BY p.id
                 ORDER BY p.priority";

        $result = $this->db->query($query);
        if ($result) {
            $stats['positions'] = [];
            while ($row = $result->fetch_assoc()) {
                $stats['positions'][] = $row;
            }
        }

        return $stats;
    }

    /**
     * Reset election
     * @return bool
     */
    public function resetElection() {
        try {
            $this->db->beginTransaction();

            // Archive current election first
            $current = $this->getCurrentElection();
            if ($current && $current['status'] !== 'off') {
                // Generate archive files
                $files = [
                    'details_pdf' => 'election_details_' . date('Y-m-d_H-i-s') . '.pdf',
                    'results_pdf' => 'election_results_' . date('Y-m-d_H-i-s') . '.pdf'
                ];
                $this->archiveElection($files);
            }

            // Reset votes
            $this->db->query("TRUNCATE TABLE votes");
            
            // Reset voter status
            $this->db->query("UPDATE voters SET has_voted = 0");
            
            // Reset candidate vote counts
            $this->db->query("UPDATE candidates SET votes = 0");
            
            // Reset election status
            $this->db->query("UPDATE election_status SET status = 'pending', start_time = NULL WHERE id = 1");

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Election reset error: " . $e->getMessage());
            return false;
        }
    }
}