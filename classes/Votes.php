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
        $sql = "SELECT has_voted FROM voters WHERE id = ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bind_param('i', $voterId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result && $result['has_voted'] == 1;
    }

    /**
     * Submit votes for a voter
     * @param int $voterId The voter's ID
     * @param array $votesData Array of position_id => candidate_id pairs
     * @return bool True if successful, false otherwise
     */
    public function submitVotes($voterId, $votesData) {
        if ($this->hasVoted($voterId)) {
            return false;
        }

        $this->db->getConnection()->begin_transaction();

        try {
            // Generate a unique vote reference
            $voteRef = 'VOTE-' . time() . '-' . $voterId;
            
            // Convert votes data to JSON
            $votesJson = json_encode($votesData);
            
            // Insert into votes table
            $sql = "INSERT INTO votes (vote_ref, votes_data) VALUES (?, ?)";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bind_param('ss', $voteRef, $votesJson);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert vote: " . $stmt->error);
            }
            
            // Update voter's has_voted status
            $sql = "UPDATE voters SET has_voted = 1 WHERE id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->bind_param('i', $voterId);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update voter status: " . $stmt->error);
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
     * @return array|false Array of votes with position and candidate details, or false if not found
     */
    public function getVoterVotes($voterId) {
        // First check if voter has voted
        if (!$this->hasVoted($voterId)) {
            return false;
        }
        
        // Get the vote data from the votes table
        $sql = "SELECT v.* FROM votes v 
                JOIN voters vt ON v.created_at = (
                    SELECT MAX(created_at) FROM votes 
                    WHERE created_at <= (
                        SELECT created_at FROM voters WHERE id = ? AND has_voted = 1
                    )
                )
                WHERE vt.id = ? AND vt.has_voted = 1
                LIMIT 1";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bind_param('ii', $voterId, $voterId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if (!$result) {
            return false;
        }
        
        // Decode the JSON votes data
        $votesData = json_decode($result['votes_data'], true);
        
        // If we need to get the actual candidate and position details
        $formattedVotes = [];
        
        foreach ($votesData as $positionId => $candidateIds) {
            // Get position details
            $positionSql = "SELECT description FROM positions WHERE id = ?";
            $posStmt = $this->db->getConnection()->prepare($positionSql);
            $posStmt->bind_param('i', $positionId);
            $posStmt->execute();
            $position = $posStmt->get_result()->fetch_assoc();
            
            if (!is_array($candidateIds)) {
                $candidateIds = [$candidateIds];
            }
            
            foreach ($candidateIds as $candidateId) {
                // Get candidate details
                $candidateSql = "SELECT c.firstname, c.lastname, p.name as partylist 
                                FROM candidates c 
                                LEFT JOIN partylists p ON c.partylist_id = p.id 
                                WHERE c.id = ?";
                $candStmt = $this->db->getConnection()->prepare($candidateSql);
                $candStmt->bind_param('i', $candidateId);
                $candStmt->execute();
                $candidate = $candStmt->get_result()->fetch_assoc();
                
                if ($candidate) {
                    $formattedVotes[] = [
                        'position' => $position['description'],
                        'candidate' => $candidate['firstname'] . ' ' . $candidate['lastname'],
                        'partylist' => $candidate['partylist']
                    ];
                }
            }
        }
        
        return $formattedVotes;
    }
}