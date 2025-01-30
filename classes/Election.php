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
        $sql = "SELECT * FROM election_status WHERE id = 1";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_assoc() : false;
    }

    /**
     * Check if election is currently active
     * @return bool True if election is active, false otherwise
     */
    public function isElectionActive() {
        $election = $this->getCurrentElection();
        if (!$election) return false;

        $timezone = new DateTimeZone('Asia/Manila'); // Ensure correct timezone
    $now = new DateTime('now', $timezone);
    $start = $election['start_time'] ? new DateTime($election['start_time'], $timezone) : null;
    $end = new DateTime($election['end_time'], $timezone);


        return $election['status'] === 'on' && 
               $start && $now >= $start && 
               $now <= $end;
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
    public function archiveElection($detailsPdf = '', $resultsPdf = '') {
        $current = $this->getCurrentElection();
        if (!$current) return false;

        // Begin transaction
        $this->db->getConnection()->begin_transaction();

        try {
            // Insert into history
            $sql = "INSERT INTO election_history 
                    (election_name, start_date, end_date, details_pdf, results_pdf) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bind_param('sssss', 
                $current['election_name'],
                $current['start_time'],
                $current['end_time'],
                $detailsPdf,
                $resultsPdf
            );
            $stmt->execute();

            // Reset current election status
            $sql = "UPDATE election_status SET 
                    status = 'pending',
                    election_name = '',
                    start_time = NULL,
                    end_time = NULL 
                    WHERE id = 1";
            
            $this->db->query($sql);

            $this->db->getConnection()->commit();
            $this->logAction("Election archived: {$current['election_name']}");
            return true;

        } catch (Exception $e) {
            $this->db->getConnection()->rollback();
            error_log("Error archiving election: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get election history
     * @param int $limit Number of records to return
     * @return array Election history records
     */
    public function getElectionHistory($limit = 10) {
        $sql = "SELECT * FROM election_history 
                ORDER BY created_at DESC 
                LIMIT ?";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
        if (!$election || !$election['end_time']) return false;

        // Create DateTime objects with explicit timezone
        $timezone = new DateTimeZone('Asia/Manila');
        $now = new DateTime('now', $timezone);
        $end = new DateTime($election['end_time'], $timezone);
        
        $interval = $now->diff($end);

        if ($interval->invert) return false; // Election has ended

        return [
            'days' => $interval->d,
            'hours' => $interval->h,
            'minutes' => $interval->i
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
        if (!$election || !$election['end_time']) return false;

        $timezone = new DateTimeZone('Asia/Manila');
        $now = new DateTime('now', $timezone);
        $end = new DateTime($election['end_time'], $timezone);
        
        return $now > $end;
    }
}
