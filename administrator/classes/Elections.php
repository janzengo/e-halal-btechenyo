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

    // Updated valid election statuses
    const STATUS_SETUP = 'setup';
    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_PAUSED = 'paused';
    const STATUS_COMPLETED = 'completed';

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
     * Check if election is active and modifications are not allowed
     * @return bool
     */
    public function isModificationLocked() {
        $current = $this->getCurrentElection();
        return $current && $current['status'] === self::STATUS_ACTIVE;
    }

    /**
     * Validate election status change
     * @param string $newStatus
     * @param array $currentElection
     * @return array ['valid' => bool, 'message' => string]
     */
    private function validateStatusChange($newStatus, $currentElection) {
        $validStatuses = [
            self::STATUS_SETUP,
            self::STATUS_PENDING,
            self::STATUS_ACTIVE,
            self::STATUS_PAUSED,
            self::STATUS_COMPLETED
        ];
        
        if (!in_array($newStatus, $validStatuses)) {
            return ['valid' => false, 'message' => 'Invalid status provided'];
        }

        // Validate status transitions
        if ($currentElection) {
            $currentStatus = $currentElection['status'];
            $validTransitions = [
                self::STATUS_SETUP => [self::STATUS_SETUP, self::STATUS_PENDING],
                self::STATUS_PENDING => [self::STATUS_SETUP, self::STATUS_PENDING, self::STATUS_ACTIVE],
                self::STATUS_ACTIVE => [self::STATUS_ACTIVE, self::STATUS_PAUSED, self::STATUS_COMPLETED],
                self::STATUS_PAUSED => [self::STATUS_PAUSED, self::STATUS_ACTIVE, self::STATUS_COMPLETED, self::STATUS_PENDING],
                self::STATUS_COMPLETED => [self::STATUS_COMPLETED, self::STATUS_SETUP]
            ];

            if (!isset($validTransitions[$currentStatus]) || 
                !in_array($newStatus, $validTransitions[$currentStatus])) {
                return [
                    'valid' => false, 
                    'message' => "Invalid status transition from {$currentStatus} to {$newStatus}"
                ];
            }
        }

        // Validate end time when activating election
        if ($newStatus === self::STATUS_ACTIVE) {
            if (empty($currentElection['end_time'])) {
                return ['valid' => false, 'message' => 'End time must be set before activating the election'];
            }

            $now = new DateTime();
            $endTime = new DateTime($currentElection['end_time']);

            if ($now >= $endTime) {
                return ['valid' => false, 'message' => 'Cannot activate election: End time must be in the future'];
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

            // Get current election to check if this is an update or new creation
            $currentElection = $this->getCurrentElection();
            
            // Validate status change
            $validation = $this->validateStatusChange($data['status'], $currentElection);
            if (!$validation['valid']) {
                throw new Exception($validation['message']);
            }

            if ($currentElection) {
                // Update existing election
                $query = "UPDATE election_status SET 
                         election_name = ?,
                         status = ?,
                         end_time = ?
                         WHERE id = 1";
            } else {
                // Create new election
                $query = "INSERT INTO election_status (id, election_name, status, end_time) 
                         VALUES (1, ?, ?, ?)";
            }

            $stmt = $this->db->prepare($query);
            $stmt->bind_param("sss", 
                $data['election_name'],
                $data['status'],
                $data['end_time']
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to " . ($currentElection ? "update" : "create") . " election");
            }

            // Get admin info from session
            $admin = Admin::getInstance();
            
            // Log the configuration change
            $logMessage = ($currentElection ? "Updated" : "Created") . " election configuration:";
            $logMessage .= " Name: {$data['election_name']},";
            $logMessage .= " Status: {$data['status']},";
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
        $validStatuses = [
            self::STATUS_SETUP,
            self::STATUS_PENDING,
            self::STATUS_ACTIVE,
            self::STATUS_PAUSED,
            self::STATUS_COMPLETED
        ];

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
            if ($current && $current['status'] !== self::STATUS_COMPLETED) {
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
            
            // Reset election status to setup
            $this->db->query("UPDATE election_status SET status = 'setup', end_time = NULL WHERE id = 1");

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Election reset error: " . $e->getMessage());
            return false;
        }
    }
}