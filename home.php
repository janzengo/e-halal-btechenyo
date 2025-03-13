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
                            ?>
                            <div class="vote-success-container">
                                <div class="success-content">
                                    <div class="check-circle">
                                        <i class="fa fa-check"></i>
                                    </div>
                                    <h2>Thank You for Voting!</h2>
                                    <p class="vote-ref">Reference Number: <strong><?php echo htmlspecialchars($_SESSION['vote_ref']); ?></strong></p>
                                    <p class="success-message">Your vote has been recorded successfully and a receipt has been sent to your email.</p>
                                    <div class="action-buttons">
                                        <a href="download_receipt.php?ref=<?php echo urlencode($_SESSION['vote_ref']); ?>" 
                                           class="btn btn-primary"
                                           target="_blank">
                                            <i class="fa fa-download"></i> Download PDF Receipt
                                        </a>
                                        <a href="logout.php" class="btn btn-secondary">
                                            <i class="fa fa-sign-out"></i> Sign Out
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php
                            break;
                            
                        case 'already_voted':
                            ?>
                            <div class="vote-success-container">
                                <div class="success-content">
                                    <div class="check-circle">
                                        <i class="fa fa-check"></i>
                                    </div>
                                    <h2>You have already voted!</h2>
                                    <p class="vote-ref">Reference Number: <strong><?php echo htmlspecialchars($_SESSION['vote_ref']); ?></strong></p>
                                    <p class="success-message">Your vote has been recorded successfully and a receipt has been sent to your email.</p>
                                    <div class="action-buttons">
                                        <a href="download_receipt.php?ref=<?php echo urlencode($_SESSION['vote_ref']); ?>" target="_blank" class="btn btn-primary">
                                            <i class="fa fa-download"></i> Download PDF Receipt
                                        </a>
                                        <a href="logout.php" class="btn btn-secondary">
                                            <i class="fa fa-sign-out"></i> Sign Out
                                        </a>
                                    </div>
                                </div>
                            </div>
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
</body>

<style>
    /* Hides scrollbar but still able to scroll */
html, body {
    overflow: auto;
    -ms-overflow-style: none;  
    scrollbar-width: none;
}

html::-webkit-scrollbar, body::-webkit-scrollbar {
    display: none;
}

button[name="view_ballot"] {
    background-color: #259646;
    border: none;
    padding: 10px 30px;
}

button[name="view_ballot"]:hover, button[name="view_ballot"]:active {
    background-color: #0f6d33 !important;
}

.announcement {
    /* Vertical Align */   
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    height: 100%;
    width: 100%;
}

.vote-success-container {
    background: #fff;
    border-radius: 8px;
    padding: 30px;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin: 20px auto;
    max-width: 600px;
}

.check-circle {
    width: 80px;
    height: 80px;
    background: #28a745;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}

.check-circle i {
    color: white;
    font-size: 40px;
}

.success-content h2 {
    color: #333;
    margin-bottom: 20px;
}

.vote-ref {
    font-size: 1.2em;
    color: #259646;
    margin: 15px 0;
}

.success-message {
    color: #666;
    margin-bottom: 25px;
}

.action-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
}

.action-buttons .btn {
    padding: 10px 20px;
}
</style>

</html>