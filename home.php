<?php
require_once 'init.php';
require_once 'classes/Database.php';
require_once 'classes/CustomSessionHandler.php';
require_once 'classes/User.php';
require_once 'classes/Ballot.php';
require_once 'classes/View.php';
require_once 'classes/Votes.php';
require_once 'classes/Logger.php';

$session = CustomSessionHandler::getInstance();
$user = new User();
$ballot = new Ballot();
$view = View::getInstance();
$votes = new Votes();
$logger = Logger::getInstance();

if(!$user->isLoggedIn()) {
    header('location: index.php');
    exit();
}

$currentVoter = $user->getCurrentUser();
$hasVoted = $votes->hasVoted($currentVoter['id']);

// Log vote completion if just voted
if (isset($_SESSION['just_voted']) && $_SESSION['just_voted'] && isset($_SESSION['vote_ref'])) {
    $logger->logVoteSubmission($currentVoter['student_number'], $_SESSION['vote_ref']);
    $voteStatus = 'complete';
    unset($_SESSION['just_voted']);
} elseif ($hasVoted) {
    $voteStatus = 'already_voted';
    // Get vote reference if not in session
    if (!isset($_SESSION['vote_ref'])) {
        $_SESSION['vote_ref'] = $votes->getVoteRef($currentVoter['id']);
    }
} else {
    $voteStatus = 'current';
}

// Only redirect if the vote parameter is wrong
$requestedStatus = isset($_GET['vote']) ? $_GET['vote'] : '';
if ($requestedStatus !== $voteStatus) {
    header('Location: home.php?vote=' . $voteStatus);
    exit();
}

echo $view->renderHeader();
?>
<body class="hold-transition skin-blue layout-top-nav">
    <div class="wrapper">
        <?php echo $view->renderNavbar(); ?>
        <div class="content-wrapper">
            <div class="container">
                <section class="content">
                    <?php
                    $title = $ballot->getElectionName();
                    if ($session->hasError()) {
                        echo '<div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <ul>';
                        foreach ($session->getError() as $error) {
                            echo "<li><i class='fa fa-exclamation-triangle'></i>&nbsp;" . $error . "</li>";
                        }
                        echo '</ul></div>';
                    }

                    // Display appropriate content based on vote status
                    switch ($voteStatus) {
                        case 'complete':
                        case 'already_voted':
                            $voteRef = $_SESSION['vote_ref'];
                            if (!$voteRef) {
                                $voteRef = $votes->getVoteRef($currentVoter['id']);
                                if ($voteRef) {
                                    $_SESSION['vote_ref'] = $voteRef;
                                }
                            }
                            ?>
                            <div class="vote-success-wrapper">
                                <div class="vote-success-container">
                                    <div class="success-content">
                                        <div class="check-circle">
                                            <i class="fa fa-check"></i>
                                        </div>
                                        <div class="success-text">
                                            <h2><?php echo $voteStatus === 'complete' ? 'Thank You for Voting!' : 'You have already voted!'; ?></h2>
                                            <?php if ($voteRef): ?>
                                                <div class="reference-section">
                                                    <div class="vote-ref">
                                                        <span class="ref-label">Reference Number:</span>
                                                        <span class="ref-number"><?php echo htmlspecialchars($voteRef); ?></span>
                                                    </div>
                                                    <p class="success-message">Your vote has been recorded successfully and a receipt has been sent to your email.</p>
                                                </div>
                                                <div class="action-buttons">
                                                    <a href="download_receipt.php?ref=<?php echo urlencode($voteRef); ?>" 
                                                    class="btn btn-primary">
                                                        <i class="fa fa-download"></i> Download PDF Receipt
                                                    </a>
                                                    <a href="logout.php" class="btn btn-secondary">
                                                        <i class="fa fa-sign-out"></i> Sign Out
                                                    </a>
                                                </div>
                                            <?php else: ?>
                                                <p class="error-message">Unable to retrieve your vote reference number. Please contact support.</p>
                                                <div class="action-buttons">
                                                    <a href="logout.php" class="btn btn-secondary">
                                                        <i class="fa fa-sign-out"></i> Sign Out
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <style>
                                .vote-success-wrapper {
                                    min-height: calc(100vh - 200px);
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    padding: 2rem 1rem;
                                }

                                .vote-success-container {
                                    background: #fff;
                                    border-radius: 12px;
                                    padding: 3rem 2rem;
                                    width: 100%;
                                    max-width: 500px;
                                    text-align: center;
                                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                                }

                                .success-content {
                                    display: flex;
                                    flex-direction: column;
                                    align-items: center;
                                    gap: 2rem;
                                }

                                .check-circle {
                                    width: 120px;
                                    height: 120px;
                                    background: #28a745;
                                    border-radius: 50%;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
                                }

                                .check-circle i {
                                    color: white;
                                    font-size: 60px;
                                }

                                .success-text {
                                    display: flex;
                                    flex-direction: column;
                                    gap: 1.5rem;
                                    width: 100%;
                                }

                                .success-text h2 {
                                    color: #333;
                                    font-size: 2rem;
                                    margin: 0;
                                    font-weight: 600;
                                }

                                .reference-section {
                                    display: flex;
                                    flex-direction: column;
                                    gap: 1rem;
                                }

                                .vote-ref {
                                    display: flex;
                                    flex-direction: column;
                                    gap: 0.5rem;
                                }

                                .ref-label {
                                    color: #666;
                                    font-size: 1.1rem;
                                }

                                .ref-number {
                                    color: #28a745;
                                    font-size: 1.5rem;
                                    font-weight: 600;
                                    letter-spacing: 0.5px;
                                }

                                .success-message {
                                    color: #666;
                                    font-size: 1rem;
                                    line-height: 1.5;
                                    margin: 0;
                                }

                                .action-buttons {
                                    display: flex;
                                    gap: 1rem;
                                    justify-content: center;
                                    margin-top: 0.5rem;
                                }

                                .action-buttons .btn {
                                    padding: 0.75rem 1.5rem;
                                    font-size: 1rem;
                                    display: flex;
                                    align-items: center;
                                    gap: 0.5rem;
                                    min-width: 180px;
                                    justify-content: center;
                                }

                                .btn-primary {
                                    background-color: #28a745;
                                    border-color: #28a745;
                                }

                                .btn-primary:hover {
                                    background-color: #218838;
                                    border-color: #1e7e34;
                                }

                                @media (max-width: 576px) {
                                    .vote-success-container {
                                        padding: 2rem 1.5rem;
                                    }

                                    .check-circle {
                                        width: 100px;
                                        height: 100px;
                                    }

                                    .check-circle i {
                                        font-size: 50px;
                                    }

                                    .success-text h2 {
                                        font-size: 1.75rem;
                                    }

                                    .ref-number {
                                        font-size: 1.25rem;
                                    }

                                    .action-buttons {
                                        flex-direction: column;
                                    }

                                    .action-buttons .btn {
                                        width: 100%;
                                        min-width: unset;
                                    }
                                }
                            </style>
                            <?php
                            break;
                            
                        case 'current':
                            echo '<h1 class="page-header text-center title title-custom"><b>'. strtoupper($title).'</b></h1>';
                            if ($session->hasSuccess()) {
                                echo '<div class="alert alert-success alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    <h4><i class="icon fa fa-check"></i> Success!</h4>'
                                    . $session->getSuccess() .
                                '</div>';
                            }
                            break;
                    }
                    ?>
                    
                    <div class="row">
                        <div class="col-sm-10 col-sm-offset-1">
                            <?php
                            // Clear session messages after displaying
                            $session->clearError();
                            $session->clearSuccess();

                            // Show appropriate content based on vote status
                            if ($voteStatus === 'current') {
                                // Show the ballot form for voting
                                echo $ballot->renderBallot();
                            }
                            ?>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        </div>
        <?php echo $view->renderFooter(); ?>
    </div>
    <?php echo $view->renderScripts(); ?>
    <?php include 'modals/ballot_modal.php'; ?>
    
    <?php if ($voteStatus === 'current'): ?>
    <script>
    $(document).ready(function(){
        // Update selected state visually
        function updateSelectedState() {
            $('.candidate-card').each(function() {
                const input = $(this).find('input');
                $(this).toggleClass('selected', input.prop('checked'));
            });
        }

        // Update disabled state for a position section
        function updateDisabledState(positionSection) {
            const maxVote = parseInt(positionSection.data('max-vote'));
            if (maxVote <= 1) return; // Only handle multiple selection positions
            
            const selectedCount = positionSection.find('input:checked').length;
            const cards = positionSection.find('.candidate-card');
            
            if (selectedCount >= maxVote) {
                // Disable unselected cards
                cards.each(function() {
                    const card = $(this);
                    const input = card.find('input');
                    if (!input.prop('checked')) {
                        card.addClass('disabled');
                    }
                });
            } else {
                // Enable all cards
                cards.removeClass('disabled');
            }
        }

        // Handle candidate selection
        $(document).on('click', '.candidate-card', function(e) {
            // Don't trigger if clicking platform button or if card is disabled
            if ($(e.target).closest('.platform').length || $(this).hasClass('disabled')) return;
            
            const input = $(this).find('input[type="radio"], input[type="checkbox"]');
            const positionSection = $(this).closest('.position-section');
            const maxVote = parseInt(positionSection.data('max-vote'));
            
            if (input.attr('type') === 'radio') {
                // Remove selected class from all cards in this position
                positionSection.find('.candidate-card').removeClass('selected');
                // Add selected class to clicked card
                $(this).addClass('selected');
                input.prop('checked', true);
            } else {
                // For checkboxes (multiple selection)
                const selectedCount = positionSection.find('input:checked').length;
                
                if (!input.prop('checked') && selectedCount >= maxVote) {
                    return; // Do nothing if max selection reached
                }
                
                input.prop('checked', !input.prop('checked'));
                $(this).toggleClass('selected', input.prop('checked'));
                
                // Update disabled state after selection changes
                updateDisabledState(positionSection);
            }
        });

        // Reset button handler
        $(document).on('click', '.reset', function(e) {
            e.preventDefault();
            const positionId = $(this).data('position');
            const positionSection = $(this).closest('.position-section');
            positionSection.find('input[name^="votes[' + positionId + ']"]').prop('checked', false);
            positionSection.find('.candidate-card').removeClass('selected disabled');
        });

        // Preview button handler
        $('#preview').on('click', function(e) {
            e.preventDefault();
            
            // Check if any votes are selected
            var hasVotes = false;
            $('input[name^="votes["]').each(function() {
                if ($(this).is(':checked')) {
                    hasVotes = true;
                    return false; // break the loop
                }
            });
            
            if (!hasVotes) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Votes Selected',
                    text: 'Please select at least one candidate before previewing.',
                    customClass: {
                        container: 'my-swal'
                    }
                });
                return;
            }
            
            // Show loading state
            Swal.fire({
                title: 'Generating Preview',
                text: 'Please wait...',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Get form data
            const formData = new FormData($('#ballotForm')[0]);
            
            // Send AJAX request to preview.php
            $.ajax({
                url: 'preview.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    // Close loading state
                    Swal.close();
                    
                    try {
                        const data = JSON.parse(response);
                        if (data.error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: Array.isArray(data.message) ? data.message.join('\n') : data.message,
                                customClass: {
                                    container: 'my-swal'
                                }
                            });
                        } else {
                            // Show preview modal
                            $('#preview_body').html(data.list);
                            $('#preview_modal').modal('show');
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to generate preview. Please try again.',
                            customClass: {
                                container: 'my-swal'
                            }
                        });
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to generate preview. Please try again.',
                        customClass: {
                            container: 'my-swal'
                        }
                    });
                }
            });
        });

        // Platform button handler
        $(document).on('click', '.platform', function(e) {
            e.preventDefault();
            var platform = $(this).data('platform');
            var fullname = $(this).data('fullname');
            var image = $(this).data('image');
            
            $('.candidate').html(fullname);
            $('#plat_view').html(platform);
            $('#platform_image').attr('src', !image ? 'administrator/assets/images/profile.jpg' : 'administrator/' + image);
            $('#platform').modal('show');
        });

        // Form submission handler
        $('#ballotForm').on('submit', function(e) {
            e.preventDefault();
            
            // Check if any votes are selected
            var hasVotes = false;
            $('input[name^="votes["]').each(function() {
                if ($(this).is(':checked')) {
                    hasVotes = true;
                    return false; // break the loop
                }
            });
            
            if (!hasVotes) {
                Swal.fire({
                    icon: 'error',
                    title: 'No Votes Cast',
                    text: 'Please select at least one candidate before submitting.',
                    customClass: {
                        container: 'my-swal'
                    }
                });
                return false;
            }

            // Confirm submission
            Swal.fire({
                title: 'Submit Votes?',
                text: 'Are you sure you want to submit your votes? This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#239746',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Confirm',
                cancelButtonText: 'Cancel',
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                customClass: {
                    container: 'my-swal',
                    actions: 'swal-buttons',
                    confirmButton: 'swal-button-confirm',
                    cancelButton: 'swal-button-cancel'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    Swal.fire({
                        title: 'Submitting your votes...',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading();
                            // Actually submit the form
                            this.submit();
                        }
                    });
                }
            });
        });
    });
    </script>

    <style>
    /* Base Responsive Styles */
    html, body {
        overflow: auto;
        -ms-overflow-style: none;  
        scrollbar-width: none;
        font-size: 16px;
    }

    @media (max-width: 768px) {
        html, body {
            font-size: 14px;
        }
    }

    html::-webkit-scrollbar, body::-webkit-scrollbar {
        display: none;
    }

    /* Hide radio buttons completely */
    .candidate-input {
        position: absolute !important;
        opacity: 0 !important;
        pointer-events: none !important;
        visibility: hidden !important;
    }

    /* Responsive Grid Layout */
    .candidates-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        padding: 10px;
    }

    /* Basic Card Styling */
    .candidate-card {
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 15px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .candidate-card.selected {
        border-color: #249646;
        background-color: #f0fff4;
    }

    .candidate-photo-container {
        width: 140px;
        height: 140px;
        margin: 0 auto 15px;
        border-radius: 50%;
        overflow: hidden;
    }

    .candidate-photo {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .card-content {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .mobile-flex {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
    }

    .candidate-info {
        text-align: center;
    }

    .candidate-name {
        font-size: 1.1rem;
        font-weight: 500;
        color: #333;
        margin: 0 0 5px;
    }

    .candidate-party {
        font-size: 0.9rem;
        color: #666;
        margin: 0;
    }

    /* Position Section Styling */
    .position-section {
        margin-bottom: 24px;
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .position-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
        gap: 12px;
    }

    .title-and-instruction {
        flex: 1;
    }

    .position-title {
        color: #249646;
        margin: 0;
        font-size: 1.2rem;
        font-weight: 600;
    }

    .position-instruction {
        color: #666;
        margin: 4px 0 0;
        font-size: 0.9rem;
    }

    /* Reset Button Styling */
    .reset {
        background-color: #dc3545;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 0.85rem;
        transition: all 0.2s ease;
        height: fit-content;
    }

    .reset:hover {
        background-color: #c82333;
    }

    .reset i {
        margin-right: 4px;
    }

    /* Mobile Styles */
    @media (max-width: 768px) {
        .position-section {
            padding: 16px;
            margin-bottom: 16px;
            border-radius: 8px;
        }

        .position-header {
            margin-bottom: 12px;
        }

        .position-title {
            font-size: 1.1rem;
        }

        .candidates-grid {
            grid-template-columns: 1fr;
            gap: 8px;
            padding: 0;
        }

        .candidate-card {
            padding: 10px;
            margin-bottom: 0;
            border-radius: 6px;
        }

        .card-content {
            display: block;
        }

        .mobile-flex {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 12px;
        }

        .candidate-photo-container {
            width: 42px;
            height: 42px;
            margin: 0;
            flex-shrink: 0;
        }

        .candidate-info {
            text-align: left;
            flex: 1;
            min-width: 0;
        }

        .candidate-name {
            font-size: 1rem;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin: 0;
            line-height: 1.3;
            color: #333;
        }

        .candidate-party {
            font-size: 0.85rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin: 0;
            line-height: 1.2;
            color: #666;
        }
    }

    /* Small Mobile Styles */
    @media (max-width: 480px) {
        .position-section {
            padding: 12px;
        }

        .position-header {
            gap: 8px;
        }

        .position-title {
            font-size: 1rem;
        }

        .position-instruction {
            font-size: 0.8rem;
        }

        .reset {
            padding: 4px 10px;
            font-size: 0.8rem;
        }

        .candidates-grid {
            gap: 6px;
        }

        .candidate-card {
            padding: 8px;
        }

        .mobile-flex {
            gap: 10px;
        }

        .candidate-photo-container {
            width: 40px;
            height: 40px;
        }

        .candidate-name {
            font-size: 0.95rem;
        }

        .candidate-party {
            font-size: 0.8rem;
        }
    }

    /* Preview/Submit Button Styling */
    .ballot-actions {
        position: sticky;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 12px 16px;
        display: flex;
        gap: 12px;
        justify-content: center;
        z-index: 100;
    }

    @media (max-width: 576px) {
        .ballot-actions {
            flex-direction: column;
            padding: 12px;
        }

        .ballot-actions button {
            width: 100%;
            margin: 0;
        }
    }

    #preview, button[name="vote"] {
        background-color: #249646 !important;
        border: none !important;
        padding: 12px 24px;
        font-size: 1rem;
        font-weight: 500;
        transition: all 0.2s ease;
        border-radius: 6px;
        color: white;
        min-width: 180px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    #preview i, button[name="vote"] i {
        font-size: 1rem;
    }

    @media (max-width: 576px) {
        #preview, button[name="vote"] {
            min-width: unset;
            width: 100%;
            padding: 12px;
        }
    }

    /* Success Container Styling */
    .vote-success-container {
        background: #fff;
        border-radius: 12px;
        padding: 2.5rem 1.5rem;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin: 2rem auto;
        max-width: 500px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .success-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1.5rem;
        width: 100%;
        max-width: 400px;
    }

    .check-circle {
        width: 120px;
        height: 120px;
        background: #28a745;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
    }

    .check-circle i {
        color: white;
        font-size: 60px;
    }

    .success-content h2 {
        color: #333;
        margin: 0;
        font-size: 2rem;
        font-weight: 600;
    }

    .vote-ref {
        color: #249646;
        margin: 0.5rem 0;
        font-size: 1.1rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        align-items: center;
    }

    .vote-ref strong {
        font-size: 1.4rem;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .success-message {
        color: #666;
        margin: 0;
        font-size: 1rem;
        line-height: 1.5;
        max-width: 90%;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 1.5rem;
        width: 100%;
    }

    .action-buttons .btn {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border-radius: 6px;
        font-weight: 500;
        min-width: 180px;
        justify-content: center;
    }

    .action-buttons .btn-primary {
        background-color: #249646;
        border-color: #249646;
    }

    .action-buttons .btn-primary:hover {
        background-color: #1e7e3a;
        border-color: #1e7e3a;
    }

    .action-buttons .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .action-buttons .btn-secondary:hover {
        background-color: #5a6268;
        border-color: #545b62;
    }

    @media (max-width: 576px) {
        .vote-success-container {
            padding: 2rem 1rem;
            margin: 1rem;
            border-radius: 8px;
        }

        .check-circle {
            width: 100px;
            height: 100px;
            margin-bottom: 0.75rem;
        }

        .check-circle i {
            font-size: 50px;
        }

        .success-content h2 {
            font-size: 1.75rem;
        }

        .vote-ref {
            font-size: 1rem;
        }

        .vote-ref strong {
            font-size: 1.25rem;
        }

        .success-message {
            font-size: 0.95rem;
            max-width: 100%;
        }

        .action-buttons {
            flex-direction: column;
            gap: 0.75rem;
        }

        .action-buttons .btn {
            width: 100%;
            min-width: unset;
        }
    }

    /* Alert Styling */
    .alert {
        position: relative;
        padding: 15px 20px;
        border: none;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .alert.alert-success {
        background-color: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }

    .alert.alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border-left: 4px solid #dc3545;
    }

    .alert .close {
        position: absolute;
        top: 50%;
        right: 15px;
        transform: translateY(-50%);
        padding: 0;
        background: none;
        border: none;
        line-height: 1;
        font-size: 1.25rem;
        font-weight: 700;
        color: inherit;
        opacity: 0.5;
        cursor: pointer;
        transition: opacity 0.2s ease;
    }

    .alert .close:hover {
        opacity: 0.75;
    }

    .alert h4 {
        margin: 0 0 8px 0;
        font-size: 1.1rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .alert h4 .icon {
        font-size: 1.2em;
    }

    .alert ul {
        margin: 0;
        padding-left: 25px;
    }

    .alert li {
        margin-bottom: 4px;
    }

    .alert li:last-child {
        margin-bottom: 0;
    }

    .alert .close {
        right: 12px;
        font-size: 1.1rem;
    }

    /* SweetAlert Customization */
    .my-swal {
        padding: 0 !important;
        font-family: inherit;
    }

    .my-swal .swal2-popup {
        width: auto !important;
        max-width: 85% !important;
        min-width: 280px !important;
        padding: 1.25rem !important;
        border-radius: 12px !important;
        margin: 1rem !important;
    }

    .my-swal .swal2-title {
        font-size: 1.1rem !important;
        line-height: 1.3 !important;
        margin-bottom: 0.5rem !important;
        padding: 0 !important;
    }

    .my-swal .swal2-html-container {
        font-size: 0.9rem !important;
        margin: 0.5rem 0 !important;
        padding: 0 !important;
    }

    .my-swal .swal2-loader {
        border-width: 0.25em;
    }

    /* Loading state specific styles */
    .my-swal.swal2-shown.loading-state .swal2-popup {
        max-width: 300px !important;
        padding: 1rem !important;
    }

    @media (max-width: 480px) {
        .my-swal .swal2-popup {
            max-width: 90% !important;
            min-width: auto !important;
            width: 280px !important;
            margin: 0.5rem auto !important;
            padding: 1rem !important;
        }

        .my-swal.loading-state .swal2-popup {
            width: 240px !important;
        }

        .my-swal .swal2-title {
            font-size: 1rem !important;
        }

        .my-swal .swal2-html-container {
            font-size: 0.85rem !important;
        }
    }
    </style>
    <?php endif; ?>
</body>

</html>