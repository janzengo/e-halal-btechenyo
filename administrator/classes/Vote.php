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

    public function getVotesByPosition($position_id) {
        $position_id = (int)$position_id;
        $query = "SELECT v.*, c.firstname, c.lastname, p.description as position 
                 FROM votes v 
                 LEFT JOIN candidates c ON v.candidate_id = c.id 
                 LEFT JOIN positions p ON c.position_id = p.id 
                 WHERE p.id = $position_id";
        $result = $this->db->query($query);
        $votes = [];
        while ($row = $result->fetch_assoc()) {
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

    public function getCandidateVotes($candidate_id) {
        $candidate_id = (int)$candidate_id;
        $query = "SELECT COUNT(*) as count FROM votes WHERE candidate_id = $candidate_id";
        $result = $this->db->query($query);
        $row = $result->fetch_assoc();
        return $row['count'];
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

    public function getVotingStatistics() {
        // Get total number of voters
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
            'not_voted' => $total - $voted
        ];
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

    public function getVoteTimeline() {
        $query = "SELECT DATE(v.vote_timestamp) as date, COUNT(*) as count 
                  FROM voters v 
                  WHERE v.has_voted = 1 
                  GROUP BY DATE(v.vote_timestamp) 
                  ORDER BY date";
        $result = $this->db->query($query);
        
        $timeline = [
            'labels' => [],
            'data' => []
        ];
        
        while ($row = $result->fetch_assoc()) {
            $timeline['labels'][] = $row['date'];
            $timeline['data'][] = (int)$row['count'];
        }
        
        return $timeline;
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
}