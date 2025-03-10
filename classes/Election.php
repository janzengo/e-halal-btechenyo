<?php

require_once 'Database.php';
require_once 'CustomSessionHandler.php';

class Election {
    private $db;
    private $session;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->session = CustomSessionHandler::getInstance();
    }

    /**
     * Get current election status
     * @return array|false Election status data or false if no election exists
     */
    public function getCurrentElection() {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM election_status 
                WHERE status IN ('on', 'paused') 
                ORDER BY id DESC LIMIT 1
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error getting current election: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if election is currently active
     * @return bool True if election is active, false otherwise
     */
    public function isElectionActive() {
        $election = $this->getCurrentElection();
        return $election && $election['status'] === 'on' && 
               strtotime($election['start_time']) <= time() && 
               strtotime($election['end_time']) > time();
    }

    /**
     * Start the election
     * @return bool True if successful, false otherwise
     */
    public function startElection() {
        $now = new DateTime();
        $sql = "UPDATE election_status SET 
                status = 'on',
                start_time = ? 
                WHERE id = 1";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $datetime = $now->format('Y-m-d H:i:s');
        $stmt->bind_param('s', $datetime);
        
        if ($stmt->execute()) {
            $this->logAction("Election started");
            return true;
        }
        return false;
    }

    /**
     * Pause the election
     * @return bool True if successful, false otherwise
     */
    public function pauseElection() {
        $sql = "UPDATE election_status SET status = 'paused' WHERE id = 1";
        if ($this->db->query($sql)) {
            $this->logAction("Election paused");
            return true;
        }
        return false;
    }

    /**
     * End the election
     * @return bool True if successful, false otherwise
     */
    public function endElection() {
        $sql = "UPDATE election_status SET status = 'off' WHERE id = 1";
        if ($this->db->query($sql)) {
            $this->logAction("Election ended");
            return true;
        }
        return false;
    }

    /**
     * Set election schedule
     * @param string $name Election name
     * @param string $endTime End time in Y-m-d H:i:s format
     * @return bool True if successful, false otherwise
     */
    public function setElectionSchedule($name, $endTime) {
        $sql = "UPDATE election_status SET 
                election_name = ?,
                end_time = ?,
                status = 'pending' 
                WHERE id = 1";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bind_param('ss', $name, $endTime);
        
        if ($stmt->execute()) {
            $this->logAction("Election schedule set: $name until $endTime");
            return true;
        }
        return false;
    }

    /**
     * Archive current election to history
     * @param string $detailsPdf Path to details PDF
     * @param string $resultsPdf Path to results PDF
     * @return bool True if successful, false otherwise
     */
    public function archiveElection($election_id) {
        try {
            $this->db->beginTransaction();

            // Get election details
            $stmt = $this->db->prepare("
                SELECT * FROM election_status WHERE id = ?
            ");
            $stmt->bind_param("i", $election_id);
            $stmt->execute();
            $election = $stmt->get_result()->fetch_assoc();

            if (!$election) {
                throw new Exception("Election not found");
            }

            // Insert into election_history
            $stmt = $this->db->prepare("
                INSERT INTO election_history 
                (election_name, start_date, end_date, details_pdf, results_pdf) 
                VALUES (?, ?, ?, '', '')
            ");
            $stmt->bind_param("sss", 
                $election['election_name'],
                $election['start_time'],
                $election['end_time']
            );
            $stmt->execute();

            // Update election status to 'off'
            $stmt = $this->db->prepare("
                UPDATE election_status 
                SET status = 'off' 
                WHERE id = ?
            ");
            $stmt->bind_param("i", $election_id);
            $stmt->execute();

            $this->db->commit();
            $this->logAction("Election archived: {$election['election_name']}");
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error archiving election: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get election history
     * @param int $limit Number of records to return
     * @return array Election history records
     */
    public function getElectionHistory() {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM election_history 
                ORDER BY start_date DESC
            ");
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting election history: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Log election-related actions
     * @param string $details Action details
     */
    private function logAction($details) {
        $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'system';
        $role = isset($_SESSION['role']) ? $_SESSION['role'] : 'system';
        
        $sql = "INSERT INTO logs (username, details, role) 
                VALUES (?, ?, ?)";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bind_param('sss', $username, $details, $role);
        $stmt->execute();
    }

    /**
     * Get time remaining until election ends
     * @return array|false Array with days, hours, minutes or false if no active election
     */
    public function getTimeRemaining() {
        $election = $this->getCurrentElection();
        if (!$election) return false;

        $end_time = strtotime($election['end_time']);
        $now = time();
        $remaining = $end_time - $now;

        if ($remaining <= 0) return false;

        return [
            'days' => floor($remaining / (60 * 60 * 24)),
            'hours' => floor(($remaining % (60 * 60 * 24)) / (60 * 60)),
            'minutes' => floor(($remaining % (60 * 60)) / 60)
        ];
    }

    /**
     * Get election statistics
     * @return array Statistics about the election
     */
    public function getElectionStats() {
        // Get total voters
        $sql = "SELECT COUNT(*) as total FROM voters";
        $result = $this->db->query($sql);
        $totalVoters = $result->fetch_assoc()['total'];

        // Get voters who have voted
        $sql = "SELECT COUNT(DISTINCT voters_id) as voted FROM votes";
        $result = $this->db->query($sql);
        $votedCount = $result->fetch_assoc()['voted'];

        // Calculate percentage
        $turnout = $totalVoters > 0 ? ($votedCount / $totalVoters) * 100 : 0;

        return [
            'total_voters' => $totalVoters,
            'votes_cast' => $votedCount,
            'turnout_percentage' => round($turnout, 2),
            'remaining_voters' => $totalVoters - $votedCount
        ];
    }

    /**
     * Check if election has ended
     * @return bool True if election has ended, false otherwise
     */
    public function hasEnded() {
        $election = $this->getCurrentElection();
        return $election && strtotime($election['end_time']) <= time();
    }
}
