<?php
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Ballot.php';
require_once __DIR__ . '/../classes/Votes.php';

$user = new User();
$ballot = new Ballot();
$votes = new Votes();
$currentVoter = $user->getCurrentUser();
?>

<!-- Preview -->
<div class="modal fade" id="preview_modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Vote Preview</h4>
            </div>
            <div class="modal-body">
              <div id="preview_body" class="scrollable-content"></div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" name="closePreview"data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
              <button type="submit" form="ballotForm" class="btn btn-success btn-flat" name="submitPreview"><i class="fa fa-check"></i> Submit</button>
            </div>
        </div>
    </div>
</div>

<!-- Platform -->
<div class="modal fade" id="platform">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center"><b><span class="candidate"></span></b></h4>
            </div>
            <div class="modal-body">
                <div class="platform-content text-center">
                    <div class="candidate-platform-image">
                        <div class="square-image">
                            <img src="" class="img-circle candidate-image" id="platform_image">
                        </div>
                    </div>
                    <div class="platform-text">
                        <p id="plat_view"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal" name="closePlatform">
                    <i class="fa fa-close"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Ballot -->
<div class="modal fade" id="view">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Your Ballot</h4>
            </div>
            <div class="modal-body">
              <div class="scrollable-content">
                <div class="ballot-header">
                  <div class="ballot-info">
                    <h5>Election: <?php echo htmlspecialchars($ballot->getElectionName()); ?></h5>
                    <p class="text-muted">
                      <i class="fa fa-user"></i> 
                      Voter: <?php echo htmlspecialchars($currentVoter['firstname'] . ' ' . $currentVoter['lastname']); ?>
                    </p>
                  </div>
                </div>
                <?php
                $voterVotes = $votes->getVoterVotes($currentVoter['id']);
                $currentPosition = '';
                
                foreach ($voterVotes as $vote) {
                    if ($currentPosition !== $vote['position']) {
                        if ($currentPosition !== '') {
                            echo '</div>'; // Close previous position div
                        }
                        $currentPosition = $vote['position'];
                        echo '<div class="position-section">
                              <div class="position-header">
                                <h4>' . htmlspecialchars($vote['position']) . '</h4>
                              </div>';
                    }
                    ?>
                    <div class="candidate-card">
                        <div class="candidate-info">
                            <div class="candidate-image-wrapper">
                                <img src="<?php echo !empty($vote['photo']) ? 'images/'.$vote['photo'] : 'images/profile.jpg'; ?>" 
                                     class="candidate-image">
                                <?php if (!empty($vote['partylist'])): ?>
                                    <span class="partylist-badge"><?php echo htmlspecialchars($vote['partylist']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="candidate-details">
                                <h4 class="candidate-name"><?php echo htmlspecialchars($vote['firstname'] . ' ' . $vote['lastname']); ?></h4>
                                <?php if (!empty($vote['platform'])): ?>
                                    <div class="platform-preview">
                                        <p><i class="fa fa-bullhorn"></i> <?php echo htmlspecialchars($vote['platform']); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                if ($currentPosition !== '') {
                    echo '</div>'; // Close last position div
                }
                ?>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" name="closePlatform" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Modal styling */
.modal-content {
    display: flex;
    flex-direction: column;
    height: 90vh;
    max-height: 90vh;
    border-radius: 12px;
    border: none;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.modal-header {
    flex: 0 0 auto;
    background: #fff;
    border-bottom: 2px solid #e9ecef;
    padding: 1.5rem;
    border-radius: 12px 12px 0 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.modal-header .modal-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.modal-header .close {
    font-size: 1.5rem;
    padding: 1rem;
    margin: -1rem;
    opacity: 0.5;
    transition: opacity 0.2s;
}

.modal-header .close:hover {
    opacity: 1;
}

.modal-body {
    flex: 1 1 auto;
    position: relative;
    padding: 0;
    background: #f8f9fa;
}

.scrollable-content {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    overflow-y: auto;
    padding: 1.5rem;
}

.modal-footer {
    flex: 0 0 auto;
    background: #fff;
    border-top: 2px solid #e9ecef;
    padding: 1.5rem;
    border-radius: 0 0 12px 12px;
    display: flex;
    justify-content: space-between;
}

/* Ballot Header */
.ballot-header {
    background: #fff;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.ballot-info h5 {
    color: #2c3e50;
    font-size: 1.2rem;
    margin: 0 0 0.5rem 0;
}

.ballot-info .text-muted {
    color: #6c757d;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}

/* Position Section */
.position-section {
    background: #fff;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.position-header {
    margin-bottom: 1.5rem;
}

.position-header h4 {
    color: #249646;
    font-size: 1.3rem;
    font-weight: 600;
    margin: 0;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #e9ecef;
}

/* Candidate Card */
.candidate-card {
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 1rem;
    transition: transform 0.2s, box-shadow 0.2s;
}

.candidate-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.candidate-info {
    display: flex;
    gap: 1.5rem;
    padding: 1rem;
}

.candidate-image-wrapper {
    position: relative;
    flex: 0 0 120px;
}

.candidate-image {
    width: 120px;
    height: 120px;
    border-radius: 10px;
    object-fit: cover;
    border: 3px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.partylist-badge {
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    background: #249646;
    color: #fff;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    white-space: nowrap;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.candidate-details {
    flex: 1;
}

.candidate-name {
    color: #2c3e50;
    font-size: 1.2rem;
    font-weight: 600;
    margin: 0 0 0.75rem 0;
}

.platform-preview {
    background: #fff;
    border-radius: 6px;
    padding: 1rem;
    margin-top: 0.75rem;
}

.platform-preview p {
    color: #4a5568;
    margin: 0;
    line-height: 1.5;
    display: flex;
    align-items: flex-start;
    gap: 8px;
}

.platform-preview i {
    color: #249646;
    margin-top: 4px;
}

/* Button styling */
.btn {
    padding: 0.5rem 1rem;
    font-weight: 500;
    border-radius: 6px;
    transition: all 0.2s;
}

.btn-success {
    background: #249646;
    border-color: #249646;
}

.btn-success:hover {
    background: #1b7235;
    border-color: #1b7235;
}

.btn i {
    margin-right: 6px;
}

/* Scrollbar styling */
.scrollable-content::-webkit-scrollbar {
    width: 8px;
}

.scrollable-content::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.scrollable-content::-webkit-scrollbar-thumb {
    background: #249646;
    border-radius: 4px;
}

.scrollable-content::-webkit-scrollbar-thumb:hover {
    background: #1b7235;
}

/* Print styles */
@media print {
    .modal-dialog {
        max-width: 100%;
        margin: 0;
    }
    
    .modal-content {
        border: none;
        box-shadow: none;
    }
    
    .modal-header .close,
    .modal-footer {
        display: none;
    }
    
    .candidate-card {
        break-inside: avoid;
    }
}

/* Close button for viewing Platform and Ballot*/
button[name="closePlatform"] {
     background-color:rgb(207, 60, 50);
    color: white; 
     border: none; 
     width: 100%;
    padding: 10px 200px; 
     font-size: 16px; 
     cursor: pointer; 
     border-radius: 5px; 
}

button[name="closePlatform"]:hover, button[name="closePlatform"]:focus, button[name="closePlatform"]:active {
    background-color:rgb(182, 38, 38) !important;
    color: white !important;
}

button[name="closePlatform"] i {
        margin-right: 5px;
}

.modal-footer {
    display: flex;
    justify-content: center;
    align-items: center; 
}

/* Close and Submit button for Preview Modal */
button[name="closePreview"] {
    background-color:rgb(207, 60, 50);
    color: white; 
    border: none; 
    width: 50%;
    padding: 10px 30px; 
    font-size: 16px; 
    cursor: pointer; 
    border-radius: 5px; 
}
button[name="closePreview"]:hover, button[name="closePreview"]:focus, button[name="closePreview"]:active {
    background-color:rgb(182, 38, 38) !important;
    color: white !important;
}

button[name="closePreview"] i {
        margin-right: 5px;
}

button[name="submitPreview"] {
    background-color:rgb(0, 166, 90);
    color: white; 
    border: none; 
    width: 50%;
    padding: 10px 30px; 
    font-size: 16px; 
    cursor: pointer; 
    border-radius: 5px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update platform modal when shown
    $('#platform').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var candidate = button.data('candidate');
        var platform = button.data('platform');
        var image = button.data('image') || 'profile.jpg';
        
        var modal = $(this);
        modal.find('.candidate').text(candidate);
        modal.find('#plat_view').text(platform);
        modal.find('#platform_image').attr('src', 'images/' + image);
    });
});
</script>
