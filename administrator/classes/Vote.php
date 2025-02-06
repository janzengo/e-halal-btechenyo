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
        $updateVoter = "UPDATE voters SET voted = 1 WHERE id = $voter_id";
        
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
        $stats = [];
        
        // Get total registered voters
        $query = "SELECT COUNT(*) as total FROM voters";
        $result = $this->db->query($query);
        $row = $result->fetch_assoc();
        $stats['total_voters'] = $row['total'];
        
        // Get voters who have voted
        $query = "SELECT COUNT(*) as voted FROM voters WHERE voted = 1";
        $result = $this->db->query($query);
        $row = $result->fetch_assoc();
        $stats['voted'] = $row['voted'];
        
        // Get voters who haven't voted
        $stats['not_voted'] = $stats['total_voters'] - $stats['voted'];
        
        return $stats;
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
}
