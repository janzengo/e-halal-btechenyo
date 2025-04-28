<?php
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/CustomSessionHandler.php';
require_once __DIR__ . '/Votes.php';
require_once __DIR__ . '/Receipt.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
}

class Ballot {
    private $db;
    private $session;
    private $votes;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->session = CustomSessionHandler::getInstance();
        $this->votes = new Votes();
    }

    public function getPositions() {
        $sql = "SELECT * FROM positions ORDER BY priority ASC";
        return $this->db->query($sql);
    }

    public function getCandidates($position_id) {
        $sql = "SELECT candidates.*, partylists.name AS partylist_name 
                FROM candidates 
                LEFT JOIN partylists ON candidates.partylist_id = partylists.id 
                WHERE position_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $position_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getCandidate($candidate_id) {
        $sql = "SELECT * FROM candidates WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $candidate_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getPosition($position_id) {
        $sql = "SELECT * FROM positions WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $position_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
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
            $this->db->beginTransaction();
            
            // Validate votes
            $errors = $this->validateVotes($votes);
            if (!empty($errors)) {
                throw new Exception(implode("\n", $errors));
            }
            
            // Check if voter has already voted
            $sql = "SELECT has_voted FROM voters WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $voter_id);
            $stmt->execute();
            $voter = $stmt->get_result()->fetch_assoc();
            
            if (!$voter) {
                throw new Exception("Voter not found.");
            }

            if ($voter['has_voted'] == 1) {
                throw new Exception("You have already submitted your votes.");
            }
            
            // Submit votes using Votes class
            $voteResult = $this->votes->submitVotes($voter_id, $votes);
            
            if (!$voteResult['success']) {
                throw new Exception("Failed to submit votes. Please try again.");
            }
            
            $this->db->commit();
            return $voteResult;
            
        } catch (Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getVoterVotes($voter_id) {
        return $this->votes->getVoterVotes($voter_id);
    }

    public function getElectionName() {
        $sql = "SELECT election_name FROM election_status WHERE status != 'setup' ORDER BY id DESC LIMIT 1";
        $result = $this->db->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['election_name'];
        }
        return "E-HALAL BTECHenyo Election";
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
                                    <div class="candidate-photo-container">
                                        <img src="<?php echo !empty($candidate['photo']) ? 'administrator/' . $candidate['photo'] : 'administrator/assets/images/profile.jpg'; ?>" 
                                             alt="Candidate Photo" 
                                             class="candidate-photo">
                                    </div>
                                    <div class="candidate-info">
                                        <strong class="candidate-name"><?php echo $candidate['firstname'] . ' ' . $candidate['lastname']; ?></strong>
                                        <?php if (!empty($candidate['partylist_name'])): ?>
                                            <p class="candidate-party"><?php echo $candidate['partylist_name']; ?></p>
                                        <?php else: ?>
                                            <p class="candidate-party">Independent</p>
                                        <?php endif; ?>
                                        <?php if (!empty($candidate['platform'])): ?>
                                            <button type="button" class="btn btn-primary btn-sm platform" 
                                                    data-platform="<?php echo htmlspecialchars($candidate['platform']); ?>"
                                                    data-fullname="<?php echo htmlspecialchars($candidate['firstname'] . ' ' . $candidate['lastname']); ?>"
                                                    data-image="<?php echo $candidate['photo']; ?>">
                                                <i class="fa fa-search"></i> Platform
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
            <div class="text-center ballot-actions">
                <button type="button" class="btn btn-success btn-flat" id="preview" name="preview"><i class="fa fa-file-text"></i> Preview</button>
                <button type="submit" class="btn btn-primary btn-flat" name="vote"><i class="fa fa-check"></i> Submit</button>
            </div>
        </form>

        <style>
        .position-section {
            margin-bottom: 30px;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .position-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .candidates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .candidate-card {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            border-radius: 8px;
            overflow: hidden;
        }

        .candidate-card.selected {
            border-color: #249646;
            background-color: #f8fff9;
        }

        .candidate-card.disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .card-content {
            padding: 15px;
        }

        .candidate-photo-container {
            width: 150px;
            height: 150px;
            margin: 0 auto 15px;
            border-radius: 50%;
            overflow: hidden;
        }

        .candidate-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .candidate-info {
            text-align: center;
            margin-bottom: 15px;
        }

        .candidate-name {
            display: block;
            font-size: 1.1em;
            margin-bottom: 5px;
            color: #333;
        }

        .candidate-party {
            color: #666;
            margin: 0;
        }

        .platform {
            width: 100%;
            margin-top: 10px;
        }

        .ballot-actions {
            margin-top: 30px;
        }

        .ballot-actions button {
            margin: 0 10px;
        }

        /* preview and submit button */
        button[name="vote"], button[name="preview"] {
            background-color: #259646 !important;
            border: none !important;
        }
        
        button[name="vote"]:hover, button[name="preview"]:hover, button[name="vote"]:active, button[name="preview"]:active {
            background-color: #1e7e34 !important;
        }
        </style>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle candidate card selection
            document.querySelectorAll('.candidate-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    // Don't trigger if clicking platform button
                    if (e.target.closest('.platform')) return;
                    
                    const input = this.querySelector('.candidate-input');
                    const positionSection = this.closest('.position-section');
                    const maxVote = parseInt(positionSection.dataset.maxVote);
                    const isRadio = input.type === 'radio';
                    
                    if (this.classList.contains('disabled') && !input.checked) return;
                    
                    if (isRadio) {
                        // Handle radio buttons (single selection)
                        positionSection.querySelectorAll('.candidate-card').forEach(c => {
                            c.classList.remove('selected');
                        });
                        this.classList.add('selected');
                        input.checked = true;
                    } else {
                        // Handle checkboxes (multiple selection)
                        const selectedCount = positionSection.querySelectorAll('.candidate-input:checked').length;
                        
                        if (!input.checked && selectedCount >= maxVote) {
                            return; // Max selections reached
                        }
                        
                        input.checked = !input.checked;
                        this.classList.toggle('selected');
                        
                        // Update disabled state for other cards
                        const remainingSlots = maxVote - (input.checked ? selectedCount + 1 : selectedCount - 1);
                        positionSection.querySelectorAll('.candidate-card').forEach(card => {
                            const cardInput = card.querySelector('.candidate-input');
                            if (!cardInput.checked) {
                                card.classList.toggle('disabled', remainingSlots === 0);
                            }
                        });
                    }
                });
            });

            // Handle reset buttons
            document.querySelectorAll('.reset').forEach(button => {
                button.addEventListener('click', function() {
                    const positionSection = this.closest('.position-section');
                    positionSection.querySelectorAll('.candidate-card').forEach(card => {
                        card.classList.remove('selected', 'disabled');
                        card.querySelector('.candidate-input').checked = false;
                    });
                });
            });

            // Prevent form submission if no candidates selected
            document.getElementById('ballotForm').addEventListener('submit', function(e) {
                const sections = this.querySelectorAll('.position-section');
                let valid = true;
                
                sections.forEach(section => {
                    const selectedCount = section.querySelectorAll('.candidate-input:checked').length;
                    const maxVote = parseInt(section.dataset.maxVote);
                    if (selectedCount > maxVote) {
                        valid = false;
                        alert(`You can only select up to ${maxVote} candidate(s) for ${section.querySelector('.position-title').textContent}`);
                    }
                });
                
                if (!valid) {
                    e.preventDefault();
                }
            });
        });
        </script>
        <?php
    }
}
