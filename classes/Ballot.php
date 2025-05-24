<?php
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/CustomSessionHandler.php';

class Ballot {
    private $db;
    private $session;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->session = CustomSessionHandler::getInstance();
    }

    public function getPositions() {
        $sql = "SELECT * FROM positions ORDER BY priority ASC";
        return $this->db->query($sql);
    }

    public function getCandidates($position_id) {
        $sql = "SELECT candidates.*, partylists.name AS partylist_name 
                FROM candidates 
                LEFT JOIN partylists ON candidates.partylist_id = partylists.id 
                WHERE position_id = '" . $this->db->escape($position_id) . "'";
        return $this->db->query($sql);
    }

    public function getCandidate($candidate_id) {
        $sql = "SELECT * FROM candidates WHERE id = '" . $this->db->escape($candidate_id) . "'";
        $query = $this->db->query($sql);
        return $query ? $query->fetch_assoc() : null;
    }

    public function getPosition($position_id) {
        $sql = "SELECT * FROM positions WHERE id = '" . $this->db->escape($position_id) . "'";
        $query = $this->db->query($sql);
        return $query->fetch_assoc();
    }

    public function validateVotes($votes) {
        $errors = [];
        $positions = $this->getPositions();
        
        while ($position = $positions->fetch_assoc()) {
            $pos_id = $position['id'];
            if (isset($votes[$pos_id])) {
                $candidate_votes = is_array($votes[$pos_id]) ? $votes[$pos_id] : [$votes[$pos_id]];
                
                if (count($candidate_votes) > $position['max_vote']) {
                    $errors[] = 'You may only choose ' . $position['max_vote'] . ' candidates for ' . $position['description'];
                    continue;
                }
                
                foreach ($candidate_votes as $candidate_id) {
                    $candidate = $this->getCandidate($candidate_id);
                    if (!$candidate || $candidate['position_id'] != $pos_id) {
                        $errors[] = 'Invalid candidate selected for ' . $position['description'];
                    }
                }
            }
        }
        
        return $errors;
    }

    public function submitVote($voter_id, $votes) {
        try {
            // Start transaction
            $this->db->query("START TRANSACTION");
            
            // Validate votes
            $errors = $this->validateVotes($votes);
            if (!empty($errors)) {
                throw new Exception(implode("\n", $errors));
            }
            
            // Check if voter has already voted
            $sql = "SELECT COUNT(*) as count FROM votes WHERE voters_id = '" . $this->db->escape($voter_id) . "'";
            $result = $this->db->query($sql);
            $row = $result->fetch_assoc();
            
            if ($row['count'] > 0) {
                throw new Exception("You have already submitted your votes.");
            }
            
            // Insert votes
            foreach ($votes as $position_id => $candidates) {
                $candidates = is_array($candidates) ? $candidates : [$candidates];
                foreach ($candidates as $candidate_id) {
                    $sql = "INSERT INTO votes (voters_id, position_id, candidate_id) 
                            VALUES ('" . $this->db->escape($voter_id) . "', 
                                    '" . $this->db->escape($position_id) . "', 
                                    '" . $this->db->escape($candidate_id) . "')";
                    if (!$this->db->query($sql)) {
                        throw new Exception($this->db->getError());
                    }
                }
            }
            
            // Commit transaction
            $this->db->query("COMMIT");
            return true;
            
        } catch (Exception $e) {
            // Rollback on error
            $this->db->query("ROLLBACK");
            $this->session->setError($e->getMessage());
            return false;
        }
    }

    public function getVoterVotes($voter_id) {
        $sql = "SELECT v.*, p.description, c.firstname, c.lastname, pl.name as partylist_name
                FROM votes v 
                LEFT JOIN positions p ON p.id = v.position_id 
                LEFT JOIN candidates c ON c.id = v.candidate_id 
                LEFT JOIN partylists pl ON pl.id = c.partylist_id
                WHERE v.voters_id = '" . $this->db->escape($voter_id) . "'";
        return $this->db->query($sql);
    }

    public function getElectionName() {
        $sql = "SELECT election_name FROM election_status WHERE id = 1";
        $result = $this->db->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['election_name'];
        }
        return "Election";
    }

    public function slugify($text) {
        // Remove non-letter or digits, replace with -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        // Transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // Remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        // Trim
        $text = trim($text, '-');
        // Remove duplicate -
        $text = preg_replace('~-+~', '-', $text);
        // Lowercase
        $text = strtolower($text);
        return $text;
    }

    public function renderBallot() {
        ?>
        <!-- Voting Ballot -->
        <form method="POST" id="ballotForm" action="submit_ballot.php">
            <?php
            $positions = $this->getPositions();
            while ($row = $positions->fetch_assoc()) {
                $pos_id = $row['id'];
                ?>
                <div class="position-section" data-max-vote="<?php echo $row['max_vote']; ?>">
                    <div class="position-header">
                        <div class="title-and-instruction">
                            <h3 class="position-title"><?php echo $row['description']; ?></h3>
                            <p class="position-instruction">
                                <?php echo $row['max_vote'] > 1 ? "You may select up to " . $row['max_vote'] . " candidates" : "Select only one candidate"; ?>
                            </p>
                        </div>
                        <button type="button" class="btn btn-danger btn-sm btn-flat reset" data-position="<?php echo $pos_id; ?>">
                            <i class="fa fa-refresh"></i> Reset
                        </button>
                    </div>
                    <div class="candidates-grid">
                        <?php
                        $candidates = $this->getCandidates($pos_id);
                        while ($candidate = $candidates->fetch_assoc()) {
                            ?>
                            <div class="candidate-card" data-candidate-id="<?php echo $candidate['id']; ?>">
                                <input type="<?php echo $row['max_vote'] > 1 ? 'checkbox' : 'radio'; ?>" 
                                       class="candidate-input" 
                                       name="votes[<?php echo $pos_id; ?>]<?php echo $row['max_vote'] > 1 ? '[]' : ''; ?>" 
                                       value="<?php echo $candidate['id']; ?>"
                                       style="display: none !important;visibility: hidden !important;">
                                <div class="card-content">
                                    <div class="mobile-flex">
                                        <div class="candidate-photo-container">
                                            <img src="<?php echo !empty($candidate['photo']) ? 'administrator/' . $candidate['photo'] : 'administrator/assets/images/profile.jpg'; ?>" 
                                                 alt="Candidate Photo" 
                                                 class="candidate-photo">
                                        </div>
                                        <div class="candidate-info">
                                            <strong class="candidate-name" title="<?php echo $candidate['firstname'] . ' ' . $candidate['lastname']; ?>">
                                                <?php echo $candidate['firstname'] . ' ' . $candidate['lastname']; ?>
                                            </strong>
                                            <p class="candidate-party">
                                                <?php echo !empty($candidate['partylist_name']) ? $candidate['partylist_name'] : 'Independent'; ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($row['max_vote'] > 1): ?>
                                <div class="disabled-overlay"></div>
                                <?php endif; ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
            <div class="text-center ballot-actions">
                <div class="ballot-buttons">
                    <button type="button" class="btn btn-success btn-flat" id="preview" name="preview">
                        <i class="fa fa-file-text"></i> Preview
                    </button>
                    <button type="submit" class="btn btn-primary btn-flat" name="vote">
                        <i class="fa fa-check"></i> Submit
                    </button>
                </div>
            </div>
        </form>

        <style>
            .candidates-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 20px;
                padding: 10px;
            }

            .candidate-card {
                position: relative;
                background: #fff;
                border: 1px solid #e0e0e0;
                border-radius: 8px;
                padding: 15px;
                cursor: pointer;
                transition: all 0.3s ease;
            }

            .candidate-card.selected {
                border-color: #249646;
                background-color: #f0fff4;
            }

            .candidate-card.disabled {
                opacity: 0.6;
                cursor: not-allowed;
                pointer-events: none;
                border-color: #ddd;
                background-color: #f8f9fa;
                transform: scale(0.98);
            }

            .disabled-overlay {
                display: none;
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.05);
                border-radius: 8px;
                transition: all 0.3s ease;
            }

            .candidate-card.disabled .disabled-overlay {
                display: block;
            }

            .candidate-photo-container {
                width: 120px;
                height: 120px;
                margin: 0 auto 12px;
                border-radius: 50%;
                overflow: hidden;
                border: 1px solid #e0e0e0;
                background-color: #f8f9fa;
            }

            .candidate-photo {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .candidate-info {
                text-align: center;
            }

            .candidate-name {
                font-size: 1.1rem;
                font-weight: 500;
                margin: 0 0 4px;
                color: #333;
                line-height: 1.3;
            }

            .candidate-party {
                font-size: 0.9rem;
                margin: 0;
                color: #666;
                line-height: 1.2;
            }

            @media (max-width: 768px) {
                .candidates-grid {
                    grid-template-columns: 1fr;
                    gap: 12px;
                    padding: 8px;
                }

                .candidate-card {
                    background: #fff;
                    border: 1px solid #e0e0e0;
                    border-radius: 8px;
                    padding: 10px 12px;
                    margin-bottom: 8px;
                }

                .mobile-flex {
                    display: flex;
                    align-items: center;
                    gap: 14px;
                }

                .candidate-photo-container {
                    width: 44px;
                    height: 44px;
                    margin: 0;
                    flex-shrink: 0;
                    border-radius: 50%;
                    overflow: hidden;
                }

                .candidate-photo {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                }

                .candidate-info {
                    text-align: left;
                    flex: 1;
                    min-width: 0;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    gap: 2px;
                }

                .candidate-name {
                    font-size: 1.1rem;
                    margin: 0;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    display: block;
                    font-weight: 500;
                    color: #333;
                    line-height: 1.3;
                }

                .candidate-party {
                    font-size: 0.9rem;
                    margin: 0;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    color: #666;
                    line-height: 1.2;
                }

                .candidate-card.selected {
                    border-color: #249646;
                    background-color: #f0fff4;
                }
            }

            .ballot-actions {
                position: sticky;
                bottom: 20px;
                z-index: 100;
                padding: 15px;
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(10px);
                border-radius: 12px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }

            .ballot-buttons {
                display: flex;
                gap: 15px;
                justify-content: center;
            }

            @media (max-width: 576px) {
                .ballot-actions {
                    bottom: 0;
                    left: 0;
                    right: 0;
                    border-radius: 12px 12px 0 0;
                    margin: 0;
                    padding: 12px;
                }

                .ballot-buttons {
                    flex-direction: column;
                    width: 100%;
                }

                .ballot-buttons button {
                    width: 100%;
                    padding: 10px;
                    font-size: 0.95em;
                }
            }

            #preview, button[name="vote"] {
                min-width: 200px;
                padding: 12px 30px;
                border-radius: 6px;
                font-weight: 500;
                transition: all 0.3s ease;
            }

            @media (max-width: 576px) {
                #preview, button[name="vote"] {
                    min-width: unset;
                }
            }

            #preview {
                background-color: #249646 !important;
                border: none;
                color: white;
            }

            button[name="vote"] {
                background-color: #1e7e34 !important;
                border: none;
                color: white;
            }

            #preview:hover, button[name="vote"]:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }
        </style>
        <?php
    }
}