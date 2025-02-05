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
                <h4 class="modal-title text-center w-100">Your Vote Preview</h4>
            </div>
            <div class="modal-body">
                <div id="preview_body" class="scrollable-content"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" name="closePreview" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
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
                <h4 class="modal-title text-center w-100"><b><span class="candidate"></span></b></h4>
            </div>
            <div class="modal-body platform-modal-body">
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
                    <?php
                    $voterVotes = $votes->getVoterVotes($currentVoter['id']);
                    $currentPosition = '';

                    foreach ($voterVotes as $vote) {
                        // Start new position section if position changes
                        if ($currentPosition !== $vote['position']) {
                            if ($currentPosition !== '') {
                                echo '</div>'; // Close previous position div
                            }
                            $currentPosition = $vote['position'];
                            echo '<div class="well">
                              <h4>' . htmlspecialchars($vote['position']) . '</h4>';
                        }
                    ?>
                        <div class="row candidate-row">
                            <div class="col-sm-3 candidate-image-container">
                                <div class="square-image">
                                    <img src="<?php echo !empty($vote['photo']) ? 'images/' . $vote['photo'] : 'images/profile.jpg'; ?>"
                                        class="img candidate-image">
                                </div>
                            </div>
                            <div class="col-sm-9">
                                <h4><?php echo htmlspecialchars($vote['firstname'] . ' ' . $vote['lastname']); ?></h4>
                                <?php if (!empty($vote['partylist'])): ?>
                                    <p><strong>Partylist:</strong> <?php echo htmlspecialchars($vote['partylist']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($vote['platform'])): ?>
                                    <p><strong>Platform:</strong> <?php echo htmlspecialchars($vote['platform']); ?></p>
                                <?php endif; ?>
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
    .modal-title {
        color:#259646;
        font-weight: bold;
    }
    .modal-content {
        display: flex;
        flex-direction: column;
        height: 90vh;
        max-height: 90vh;
    }

    .modal-header {
        flex: 0 0 auto;
        background: #fff;
        border-bottom: 1px solid #e5e5e5;
        padding: 15px;
        position: relative;
        z-index: 1;
        cursor: pointer;
}

.modal-body {
    flex: 1 1 auto;
    position: relative;
    padding: 0;
    cursor: pointer;
}

    .scrollable-content {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        overflow-y: auto;
        padding: 15px;
    }

    .modal-footer {
        flex: 0 0 auto;
        background: #fff;
        border-top: 1px solid #e5e5e5;
        padding: 15px;
        position: relative;
        z-index: 1;
    }

    /* Content styling */
    .well {
        margin-bottom: 20px;
        background-color: #f9f9f9;
        border: 1px solid #e3e3e3;
        border-radius: 4px;
        padding: 15px;
    }

    .well h4 {
        color: #249646;
        margin-top: 0;
        margin-bottom: 15px;
        border-bottom: 1px solid #e3e3e3;
        padding-bottom: 10px;
    }

    /* Image styling */
    .square-image {
        position: relative;
        width: 100%;
        padding-bottom: 100%;
        overflow: hidden;
    }

    .candidate-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 20px;
        box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.2);
    }

    @media (max-width: 767px) {
        .candidate-row {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
    }

    .candidate-preview-row {
        display: flex;
        align-items: flex-start;
        margin-bottom: 10px;
        padding: 10px;
        background: #F9F9F9;
    }

    .preview-image-container {
        flex: 0 0 120px;
        padding-right: 15px;
    }

    .preview-candidate-image {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 20px;
        box-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
    }

    @media (max-width: 480px) {
        .candidate-preview-row {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
    }

    /* View modal specific styling */
    #view .square-image {
        max-width: 150px;
        margin: 0 auto;
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
        background: #888;
        border-radius: 4px;
    }

    .scrollable-content::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

/* Platform Modal Styling */
.platform-modal-content {
    height: auto !important;
    max-height: 90vh !important;
}

.platform-modal-body {
    position: relative !important;
    height: auto !important;
    max-height: calc(90vh - 120px) !important;
    overflow-y: auto !important;
    padding: 30px;
}

#platform .modal-dialog {
    max-width: 600px;
}

    .platform-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 20px;
    }

    .candidate-platform-image {
        width: 150px;
        margin-bottom: 20px;
    }

    .platform-text {
        background-color: #f9f9f9;
        border-radius: 8px;
        padding: 20px;
        width: 100%;
        text-align: left;
        line-height: 1.6;
        color: #333;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    #platform .modal-title {
        color: #249646;
        font-size: 1.5em;
        margin-bottom: 10px;
    }

    #plat_view {
        margin: 0;
        white-space: pre-line;
    }

    /* Close button for viewing Platform and Ballot*/
    button[name="closePlatform"] {
        background-color: rgb(207, 60, 50);
        color: white;
        border: none;
        width: 100%;
        padding: 10px 200px;
        font-size: 16px;
        cursor: pointer;
        border-radius: 5px;
    }

    button[name="closePlatform"]:hover,
    button[name="closePlatform"]:focus,
    button[name="closePlatform"]:active {
        background-color: rgb(182, 38, 38) !important;
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
        background-color: rgb(207, 60, 50);
        color: white;
        border: none;
        width: 50%;
        padding: 10px 30px;
        font-size: 16px;
        cursor: pointer;
        border-radius: 5px;
    }

    button[name="closePreview"]:hover,
    button[name="closePreview"]:focus,
    button[name="closePreview"]:active {
        background-color: rgb(182, 38, 38) !important;
        color: white !important;
    }

    button[name="closePreview"] i {
        margin-right: 5px;
    }

    button[name="submitPreview"] {
        background-color: rgb(0, 166, 90);
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
    $(document).ready(function() {
        $('.platform').on('click', function() {
            var platform = $(this).data('platform');
            var fullname = $(this).data('fullname');
            var image = $(this).data('image');

            // Update the modal content
            $('.candidate').text(fullname);
            $('#plat_view').text(platform);
            $('#platform_image').attr('src', 'images/' + image);

            // Show the modal
            $('#platform').modal('show');
            
        // Force scroll reset
        setTimeout(function() {
            modal.find('.platform-modal-body').scrollTop(0);
        }, 100);
    });

    // Ensure scroll reset on modal hide
    $('#platform').on('hide.bs.modal', function () {
        $(this).find('.platform-modal-body').scrollTop(0);
    });
    });
</script>