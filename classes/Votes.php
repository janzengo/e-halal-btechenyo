<?php
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/CustomSessionHandler.php';

class Votes {
    private $db;
    private $session;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->session = CustomSessionHandler::getInstance();
    }

    /**
     * Check if a voter has already voted
     * @param int $voterId The voter's ID
     * @return bool True if voter has voted, false otherwise
     */
    public function hasVoted($voterId) {
        $sql = "SELECT COUNT(*) as count FROM votes WHERE voters_id = ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bind_param('i', $voterId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] > 0;
    }

    /**
     * Submit votes for a voter
     * @param int $voterId The voter's ID
     * @param array $votes Array of position_id => candidate_id pairs
     * @return bool True if successful, false otherwise
     */
    public function submitVotes($voterId, $votes) {
        if ($this->hasVoted($voterId)) {
            return false;
        }

        $this->db->getConnection()->begin_transaction();

        try {
            $sql = "INSERT INTO votes (voters_id, candidate_id, position_id) VALUES (?, ?, ?)";
            $stmt = $this->db->getConnection()->prepare($sql);

            foreach ($votes as $positionId => $candidates) {
                if (!is_array($candidates)) {
                    $candidates = [$candidates];
                }
                
                foreach ($candidates as $candidateId) {
                    $stmt->bind_param('iii', $voterId, $candidateId, $positionId);
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to insert vote: " . $stmt->error);
                    }
                }
            }

            $this->db->getConnection()->commit();
            return true;

        } catch (Exception $e) {
            $this->db->getConnection()->rollback();
            error_log("Vote submission error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get votes cast by a voter
     * @param int $voterId The voter's ID
     * @return array Array of votes with candidate and position details
     */
    public function getVoterVotes($voterId) {
        $sql = "SELECT v.*, c.firstname, c.lastname, c.photo, c.platform, 
                       p.description as position, pl.name as partylist 
                FROM votes v 
                JOIN candidates c ON v.candidate_id = c.id 
                JOIN positions p ON v.position_id = p.id 
                LEFT JOIN partylists pl ON c.partylist_id = pl.id 
                WHERE v.voters_id = ? 
                ORDER BY p.priority ASC";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bind_param('i', $voterId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}