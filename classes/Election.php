<?php
require_once 'Database.php';
require_once 'CustomSessionHandler.php';

class Election {
    private $db;
    private $session;

    // Add status constants to match administrator side
    const STATUS_SETUP = 'setup';
    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_PAUSED = 'paused';
    const STATUS_COMPLETED = 'completed';

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
                WHERE status IN ('active', 'paused') 
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
        
        if (!$election) return false;
        
        $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
        $endTime = new DateTime($election['end_time'], new DateTimeZone('Asia/Manila'));
        
        return $election['status'] === self::STATUS_ACTIVE && $now < $endTime;
    }

    /**
     * Get time remaining until election ends
     * @return array|false Array with days, hours, minutes or false if no active election
     */
    public function getTimeRemaining() {
        $election = $this->getCurrentElection();
        if (!$election) return false;

        $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
        $endTime = new DateTime($election['end_time'], new DateTimeZone('Asia/Manila'));
        
        $remaining = $now->diff($endTime);
        
        // If end time is in the past, return false
        if ($remaining->invert) return false;
        
        return [
            'days' => $remaining->d,
            'hours' => $remaining->h,
            'minutes' => $remaining->i
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
        $sql = "SELECT COUNT(*) as voted FROM voters WHERE has_voted = 1";
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
        if (!$election) return false;
        
        $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
        $endTime = new DateTime($election['end_time'], new DateTimeZone('Asia/Manila'));
        
        return $now >= $endTime || $election['status'] === self::STATUS_COMPLETED;
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
}