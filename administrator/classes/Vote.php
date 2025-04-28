<?php
require_once __DIR__ . '/../../classes/Database.php';

class Vote {
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

    /**
     * Get votes by position
     * @param int $position_id
     * @return array
     */
    public function getVotesByPosition($position_id) {
        $position_id = (int)$position_id;
        $query = "SELECT 
                    c.firstname, 
                    c.lastname,
                    c.photo,
                    c.votes, 
                    pl.name as partylist_name
                FROM candidates c
                LEFT JOIN partylists pl ON c.partylist_id = pl.id
                WHERE c.position_id = ?
                ORDER BY c.votes DESC, c.lastname ASC, c.firstname ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $position_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $votes = [];
        while ($row = $result->fetch_assoc()) {
            error_log("Candidate votes: " . json_encode($row));
            $votes[] = $row;
        }
        return $votes;
    }

    public function getTotalVotes() {
        $query = "SELECT COUNT(*) as count FROM votes";
        $result = $this->db->query($query);
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    /**
     * Get the number of votes for a specific candidate
     * @param int $candidateId
     * @return int
     */
    public function getCandidateVotes($candidateId) {
        // First get the position ID for this candidate
        $query = "SELECT position_id FROM candidates WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $candidateId);
        $stmt->execute();
        $result = $stmt->get_result();
        $candidate = $result->fetch_assoc();
        
        if (!$candidate) {
            return 0;
        }
        
        $positionId = $candidate['position_id'];
        
        // Now count votes where this candidate is selected for their position
        $query = "SELECT COUNT(*) as vote_count FROM votes 
                 WHERE JSON_EXTRACT(votes_data, ?) = ?";
        
        $path = '$."' . $positionId . '"';
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ss', $path, $candidateId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        // Debug information
        error_log("Vote count for candidate ID {$candidateId} in position {$positionId}: {$row['vote_count']}");
        
        return intval($row['vote_count']);
    }

    public function addVote($voter_id, $candidate_id) {
        $voter_id = (int)$voter_id;
        $candidate_id = (int)$candidate_id;
        
        $query = "INSERT INTO votes (voter_id, candidate_id, created_at) 
                 VALUES ($voter_id, $candidate_id, NOW())";
        
        // Mark voter as having voted
        $updateVoter = "UPDATE voters SET has_voted = 1 WHERE id = $voter_id";
        
        try {
            $this->db->getConnection()->begin_transaction();
            
            $result1 = $this->db->query($query);
            $result2 = $this->db->query($updateVoter);
            
            if ($result1 && $result2) {
                $this->db->getConnection()->commit();
                return true;
            } else {
                $this->db->getConnection()->rollback();
                return false;
            }
        } catch (Exception $e) {
            $this->db->getConnection()->rollback();
            throw $e;
        }
    }

    /**
     * Get voting statistics
     * @return array
     */
    public function getVotingStatistics() {
        try {
            // Get total voters
            $query = "SELECT COUNT(*) as total FROM voters";
            $result = $this->db->query($query);
            $total = $result->fetch_assoc()['total'];

            // Get number of voters who have voted
            $query = "SELECT COUNT(*) as voted FROM voters WHERE has_voted = 1";
            $result = $this->db->query($query);
            $voted = $result->fetch_assoc()['voted'];

            return [
                'total_voters' => $total,
                'voted' => $voted,
                'not_voted' => $total - $voted,
                'percentage' => $total > 0 ? round(($voted / $total) * 100, 2) : 0
            ];
        } catch (Exception $e) {
            error_log("Error getting voting statistics: " . $e->getMessage());
            return [
                'total_voters' => 0,
                'voted' => 0,
                'not_voted' => 0,
                'percentage' => 0
            ];
        }
    }

    public function getCandidateVoteHistory($candidate_id) {
        $candidate_id = (int)$candidate_id;
        
        // Get initial vote count (0)
        $initialVotes = 0;
        
        // Get current vote count
        $query = "SELECT COUNT(*) as count FROM votes WHERE candidate_id = $candidate_id";
        $result = $this->db->query($query);
        $row = $result->fetch_assoc();
        $currentVotes = (int)$row['count'];
        
        // Return array of [initial, current] votes
        return [$initialVotes, $currentVotes];
    }

    /**
     * Get vote timeline
     * @return array
     */
    public function getVoteTimeline() {
        try {
            // Since we're not tracking individual votes for anonymity,
            // we'll use the voters' voted_at timestamp instead
            $query = "
                SELECT 
                    DATE_FORMAT(voted_at, '%Y-%m-%d %H:00:00') as hour,
                    COUNT(*) as vote_count
                FROM voters
                WHERE has_voted = 1
                GROUP BY hour
                ORDER BY hour ASC";

            $result = $this->db->query($query);
            
            $timeline = [
                'labels' => [],
                'data' => []
            ];

            while ($row = $result->fetch_assoc()) {
                $timeline['labels'][] = date('M d, H:i', strtotime($row['hour']));
                $timeline['data'][] = (int)$row['vote_count'];
            }

            return $timeline;
        } catch (Exception $e) {
            error_log("Error getting vote timeline: " . $e->getMessage());
            return ['labels' => [], 'data' => []];
        }
    }

    public function hasVoted($voterId) {
        $query = "SELECT has_voted FROM voters WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $voterId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? (bool)$row['has_voted'] : false;
    }

    public function markAsVoted($voterId) {
        $query = "UPDATE voters SET has_voted = 1 WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $voterId);
        return $stmt->execute();
    }

    public function getVotesByPartylist() {
        $query = "SELECT 
                    COALESCE(pl.name, 'Independent') as partylist_name,
                    SUM(
                        (
                            SELECT COUNT(*) 
                            FROM votes v 
                            WHERE JSON_EXTRACT(v.votes_data, CONCAT('$.\"', c.position_id, '\"')) = CAST(c.id AS CHAR)
                        )
                    ) as total_votes
                FROM candidates c
                LEFT JOIN partylists pl ON c.partylist_id = pl.id
                GROUP BY pl.id, pl.name
                ORDER BY total_votes DESC";
        
        $result = $this->db->query($query);
        $partylistVotes = [];
        while ($row = $result->fetch_assoc()) {
            error_log("Partylist votes: " . json_encode($row));
            $partylistVotes[] = [
                'partylist_name' => $row['partylist_name'],
                'total_votes' => intval($row['total_votes'])
            ];
        }
        return $partylistVotes;
    }
}