<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/CustomSessionHandler.php';
require_once __DIR__ . '/Logger.php';
require_once __DIR__ . '/Admin.php';
require_once __DIR__ . '/LoginDebugger.php';

class Elections {
    // Election status constants
    const STATUS_SETUP = 'setup';
    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_PAUSED = 'paused';
    const STATUS_COMPLETED = 'completed';

    /**
     * Enforce redirect to completed.php if election is completed
     */
    public static function enforceCompletedRedirect()
    {
        $instance = self::getInstance();
        $current = $instance->getCurrentElection();
        $isCompleted = $current && isset($current['status']) && $current['status'] === self::STATUS_COMPLETED;
        $onCompletedPage = (basename($_SERVER['PHP_SELF']) === 'completed.php');
        if ($isCompleted && !$onCompletedPage) {
            header("Location: " . BASE_URL . "administrator/completed");
            exit();
        }
    }

    /**
     * Enforce redirect to setup page if election status is setup
     * Meant to be called on admin pages that should not be accessible during setup
     */
    public static function enforceSetupRedirect()
    {
        $instance = self::getInstance();
        $current = $instance->getCurrentElection();
        $isSetup = $current && isset($current['status']) && $current['status'] === self::STATUS_SETUP;
        $onSetupPage = (basename($_SERVER['PHP_SELF']) === 'setup.php');
        
        if ($isSetup && !$onSetupPage) {
            header("Location: " . BASE_URL . "administrator/setup");
            exit();
        }
    }

    private static $instance = null;
    private $db;
    private $logger;

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
        $debug = LoginDebugger::getInstance();
        
        $query = "SELECT * FROM election_status WHERE id = 1";
        $result = $this->db->query($query);
        
        $debug->log("getCurrentElection query executed", [
            'query' => $query,
            'has_result' => ($result !== false),
            'num_rows' => ($result ? $result->num_rows : 0)
        ]);
        
        if ($result && $result->num_rows > 0) {
            $data = $result->fetch_assoc();
            $debug->log("getCurrentElection found data", $data);
            return $data;
        }
        
        $debug->log("getCurrentElection found no data");
        return null;
    }

    /**
     * Get current election status string
     * @return string|null Returns the status string or null if no election exists
     */
    public function getCurrentStatus() {
        $debug = LoginDebugger::getInstance();
        
        try {
            $debug->log("Fetching current election status...");
            
            // Use getCurrentElection for consistency
            $currentElection = $this->getCurrentElection();
            
            $debug->log("getCurrentStatus retrieved election data", [
                'election_data' => $currentElection
            ]);
            
            if (!$currentElection) {
                $debug->log("No election data found");
                return null;
            }
            
            $debug->log("Returning status from election data", [
                'status' => $currentElection['status']
            ]);
            
            return $currentElection['status'];
            
        } catch (Exception $e) {
            $debug->log("Error getting election status", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
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
                self::STATUS_SETUP => [self::STATUS_SETUP, self::STATUS_ACTIVE],
                self::STATUS_PENDING => [self::STATUS_PENDING, self::STATUS_ACTIVE],
                self::STATUS_PAUSED => [self::STATUS_PAUSED, self::STATUS_ACTIVE, self::STATUS_COMPLETED],
                self::STATUS_ACTIVE => [self::STATUS_ACTIVE, self::STATUS_PAUSED, self::STATUS_COMPLETED],
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
            self::STATUS_PAUSED,
            self::STATUS_ACTIVE,
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
     * Check if current election is already archived
     * @return bool True if election is archived, false otherwise
     */
    public function isCurrentElectionArchived() {
        $current = $this->getCurrentElection();
        
        if (!$current || empty($current['control_number'])) {
            return false;
        }
        
        $query = "SELECT id FROM election_history WHERE control_number = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $current['control_number']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0;
    }

    /**
     * Archive current election to election_history
     * @param array $files Array of file paths for PDF documents (details_pdf, results_pdf)
     * @return array ['success' => bool, 'message' => string]
     */
    public function archiveElection($files = []) {
        try {
            $this->db->beginTransaction();
            
            $current = $this->getCurrentElection();
            if (!$current) {
                throw new Exception('No election to archive');
            }
            
            if ($current['status'] !== self::STATUS_COMPLETED) {
                throw new Exception('Only completed elections can be archived');
            }
            
            // Check if already archived
            if ($this->isCurrentElectionArchived()) {
                throw new Exception('This election is already archived');
            }
            
            // Generate file paths if not provided
            $details_pdf = isset($files['details_pdf']) ? $files['details_pdf'] : '/archives/' . $current['control_number'] . '/details.pdf';
            $results_pdf = isset($files['results_pdf']) ? $files['results_pdf'] : '/archives/' . $current['control_number'] . '/results.pdf';
            
            // Insert into election_history with updated field structure (removed start_time)
            $query = "INSERT INTO election_history (
                    election_name, 
                    status,
                    end_time, 
                    last_status_change,
                    details_pdf, 
                    results_pdf,
                    control_number
                ) VALUES (?, ?, ?, ?, ?, ?, ?)";
                
            $stmt = $this->db->prepare($query);
            
            // Correct file paths to use relative paths with summary.pdf instead of details.pdf
            $details_pdf = str_replace('/archives/', '/archives/', $details_pdf);
            $details_pdf = str_replace('details.pdf', 'summary.pdf', $details_pdf);
            $results_pdf = str_replace('/archives/', '/archives/', $results_pdf);
            
            // Strip absolute paths from the file paths if they exist
            if (strpos($details_pdf, $_SERVER['DOCUMENT_ROOT']) === 0) {
                $details_pdf = substr($details_pdf, strlen($_SERVER['DOCUMENT_ROOT']));
            }
            if (strpos($results_pdf, $_SERVER['DOCUMENT_ROOT']) === 0) {
                $results_pdf = substr($results_pdf, strlen($_SERVER['DOCUMENT_ROOT']));
            }
            
            // Remove any references to /e-halal/ in the paths
            $details_pdf = str_replace('/e-halal/', '/', $details_pdf);
            $results_pdf = str_replace('/e-halal/', '/', $results_pdf);
            
            // Normalize paths to ensure consistent format
            $details_pdf = preg_replace('#/+#', '/', $details_pdf); // Replace multiple slashes with single
            $results_pdf = preg_replace('#/+#', '/', $results_pdf);
            
            // Remove leading slash from administrator if present
            $details_pdf = preg_replace('#^/administrator/#', 'administrator/', $details_pdf);
            $results_pdf = preg_replace('#^/administrator/#', 'administrator/', $results_pdf);
            
            // Ensure the paths start correctly
            if (strpos($details_pdf, 'archives/') !== 0 && strpos($details_pdf, '/archives/') !== 0) {
                $details_pdf = '/archives/' . $current['control_number'] . '/summary.pdf';
            }
            if (strpos($results_pdf, 'archives/') !== 0 && strpos($results_pdf, '/archives/') !== 0) {
                $results_pdf = '/archives/' . $current['control_number'] . '/results.pdf';
            }
            
            // Final format check - remove leading slash for database storage
            $details_pdf = ltrim($details_pdf, '/');
            $results_pdf = ltrim($results_pdf, '/');
            
            // Debug log for troubleshooting
            error_log("Final PDF paths - Summary: $details_pdf, Results: $results_pdf");
                
            $stmt->bind_param("sssssss", 
                $current['election_name'],
                $current['status'],
                $current['end_time'],
                $current['last_status_change'],
                $details_pdf,
                $results_pdf,
                $current['control_number']
            );
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to archive election: ' . $stmt->error);
            }
            
            // Get admin info from session
            $admin = Admin::getInstance();
            
            // Log the archive action
            $this->logger->logAdminAction(
                $admin->getUsername(),
                $admin->getRole(),
                "Archived election: {$current['election_name']} (Control #: {$current['control_number']})"
            );
            
            $this->db->commit();
            return [
                'success' => true, 
                'message' => 'Election archived successfully',
                'control_number' => $current['control_number']
            ];
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Election archiving error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
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
            $query = "SELECT id, election_name, status, end_time, 
                      last_status_change, details_pdf, results_pdf, 
                      control_number, created_at 
                      FROM election_history 
                      ORDER BY created_at DESC";
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

        $query = "SELECT id, election_name, status, end_time, 
                 last_status_change, details_pdf, results_pdf, 
                 control_number, created_at 
                 FROM election_history 
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
     * Reset election for a new one
     * @return array ['success' => bool, 'message' => string]
     */
    public function resetElection() {
        try {
            $this->db->beginTransaction();
            
            $current = $this->getCurrentElection();
            
            // Check if current election is archived before reset
            if (!$this->isCurrentElectionArchived()) {
                throw new Exception('Current election must be archived before starting a new one');
            }
            
            // Generate a new control number
            $new_control_number = 'E' . date('ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Reset the election_status table
            $query = "UPDATE election_status SET 
                     status = ?,
                     election_name = NULL,
                     end_time = NULL,
                     control_number = ?
                     WHERE id = 1";
                     
            $stmt = $this->db->prepare($query);
            $status = self::STATUS_SETUP;
            $stmt->bind_param("ss", $status, $new_control_number);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to reset election status');
            }
            
            // Archive and move current logs before clearing tables
            $this->archiveLogs($current['control_number']);
            
            // Clear all election-related tables
            $tables_to_truncate = [
                'votes',
                'voters',
                'candidates',
                'courses',
                'partylists',
                'positions'
            ];
            
            foreach ($tables_to_truncate as $table) {
                if (!$this->db->query("TRUNCATE TABLE $table")) {
                    throw new Exception("Failed to truncate table: $table");
                }
            }
            
            // Remove all officers (keep head admin)
            $query = "DELETE FROM admin WHERE role = 'officer'";
            if (!$this->db->query($query)) {
                throw new Exception('Failed to remove officers');
            }
            
            // Get admin info from session
            $admin = Admin::getInstance();
            
            // Log the reset action
            $this->logger->logAdminAction(
                $admin->getUsername(),
                $admin->getRole(),
                "Reset election system for new election (Control #: {$new_control_number})"
            );
            
            $this->db->commit();
            return [
                'success' => true, 
                'message' => 'Election system reset successfully',
                'control_number' => $new_control_number
            ];
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Election reset error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Archive logs for completed election
     * @param string $control_number The control number of the completed election
     * @return bool Success status
     */
    private function archiveLogs($control_number) {
        try {
            // Create archive directory if it doesn't exist
            $archive_dir = __DIR__ . '/../archives/' . $control_number . '/logs';
            if (!file_exists($archive_dir)) {
                mkdir($archive_dir, 0755, true);
            }

            // Archive admin logs
            $admin_logs = __DIR__ . '/../logs/admin_logs.json';
            if (file_exists($admin_logs)) {
                $admin_archive = $archive_dir . '/admin_logs.json';
                copy($admin_logs, $admin_archive);
                
                // Reset admin logs to empty array
                file_put_contents($admin_logs, "[]");
            }

            // Archive voter logs (optional - for internal record only, never exposed)
            $voter_logs = __DIR__ . '/../logs/voters_logs.json';
            if (file_exists($voter_logs)) {
                $voter_archive = $archive_dir . '/voters_logs.json';
                copy($voter_logs, $voter_archive);
                
                // Reset voter logs to empty array
                file_put_contents($voter_logs, "[]");
            }

            return true;
        } catch (Exception $e) {
            error_log("Log archiving error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates the election settings for an election in setup phase
     * @param string $electionName The name of the election
     * @param string $endTime The end time of the election
     * @return bool True if update successful, false otherwise
     */
    public function updateElectionSettings($electionName, $endTime) {
        $sql = "UPDATE election_status SET 
                election_name = ?, 
                end_time = ?
                WHERE status = 'setup'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $electionName, $endTime);
        
        if (!$stmt->execute()) {
            return false;
        }
        
        return $stmt->affected_rows > 0;
    }

    /**
     * Get the current election status
     * @return string|null The current election status or null if no election exists
     */
    public function getCurrentElectionStatus() {
        try {
            $sql = "SELECT status FROM election_status WHERE id = 1";
            $result = $this->db->query($sql);
            
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row['status'];
            }
            
            return null;
        } catch (Exception $e) {
            $this->logger->log("Error getting current election status: " . $e->getMessage());
            throw new Exception("Failed to retrieve election status");
        }
    }
}