<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../../classes/View.php';
require_once __DIR__ . '/../../../classes/Admin.php';
require_once __DIR__ . '/../../../classes/Ballot.php';
require_once __DIR__ . '/../slugify.php';

try {
    // Check if AdminBallot class exists and is accessible
    if (!class_exists('AdminBallot')) {
        throw new Exception("AdminBallot class not found");
    }
    
    $ballot = AdminBallot::getInstance();
    
    // Get all positions
    $positions = $ballot->getAllPositions();
    $total_positions = count($positions);

    $output = '';

    foreach($positions as $row) {
        $candidates = $ballot->getCandidatesForPosition($row['id']);
        $candidate_list = '';
        
        foreach($candidates as $candidate) {
            // Fix image paths - include the e-halal project directory
            $image = !empty($candidate['photo']) ? 
                '/e-halal/administrator/' . $candidate['photo'] : 
                '/e-halal/administrator/assets/images/profile.jpg';
            $candidate_list .= '
                <div class="candidate-card">
                    <div class="card-content">
                        <div class="candidate-photo-container">
                            <img src="' . $image . '" 
                                 alt="Candidate Photo" 
                                 class="candidate-photo">
                        </div>
                        <div class="candidate-info">
                            <strong class="candidate-name">' . $candidate['firstname'] . ' ' . $candidate['lastname'] . '</strong>
                            ' . (!empty($candidate['partylist_name']) ? '<p class="candidate-party">' . $candidate['partylist_name'] . '</p>' : '<p class="candidate-party">Independent</p>') . '
                            ' . (!empty($candidate['platform']) ? '
                                <button type="button" class="btn btn-primary btn-sm platform" 
                                        data-platform="' . htmlspecialchars($candidate['platform']) . '"
                                        data-fullname="' . htmlspecialchars($candidate['firstname'] . ' ' . $candidate['lastname']) . '"
                                        data-image="' . $candidate['photo'] . '">
                                    <i class="fa fa-search"></i> Platform
                                </button>' : '') . '
                        </div>
                    </div>
                </div>';
        }

        $updisable = ($row['priority'] == 1) ? 'disabled' : '';
        $downdisable = ($row['priority'] == $total_positions) ? 'disabled' : '';

        $output .= '
        <div class="position-section" data-max-vote="' . $row['max_vote'] . '">
            <div class="position-header">
                <div class="title-and-instruction">
                    <h3 class="position-title">' . $row['description'] . '</h3>
                    <p class="position-instruction">
                        ' . ($row['max_vote'] > 1 ? "You may select up to " . $row['max_vote'] . " candidates" : "Select only one candidate") . '
                    </p>
                </div>
                <div class="box-tools">
                    <button type="button" class="btn btn-default btn-sm moveup" data-id="' . $row['id'] . '" ' . $updisable . '><i class="fa fa-arrow-up"></i></button>
                    <button type="button" class="btn btn-default btn-sm movedown" data-id="' . $row['id'] . '" ' . $downdisable . '><i class="fa fa-arrow-down"></i></button>
                    <button type="button" class="btn btn-danger btn-sm btn-flat reset" data-position="' . $row['id'] . '">
                        <i class="fa fa-refresh"></i> Reset
                    </button>
                </div>
            </div>
            <div class="candidates-grid">
                ' . $candidate_list . '
            </div>
        </div>';
    }

    // Add the CSS styles
    $output .= '
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
        background: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
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
        border: 3px solid #f4f4f4;
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
        font-size: 0.9em;
    }

    .platform {
        width: 100%;
        margin-top: 10px;
    }

    .box-tools {
        display: flex;
        gap: 5px;
        align-items: center;
    }

    .box-tools button {
        margin-left: 5px;
    }

    .position-title {
        margin: 0;
        color: #333;
        font-size: 1.4em;
    }

    .position-instruction {
        color: #666;
        margin: 5px 0 0;
        font-size: 0.9em;
    }
    </style>';

    // Reorder positions to ensure consistent priority numbers
    $ballot->reorderPositions();

    echo json_encode($output);

} catch (Exception $e) {
    // Log error
    error_log("Error in ballot_fetch.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
} 