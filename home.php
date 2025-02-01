<?php 
require_once 'init.php';
require_once 'classes/Database.php';
require_once 'classes/CustomSessionHandler.php';
require_once 'classes/User.php';
require_once 'classes/Ballot.php';
require_once 'classes/View.php';
require_once 'classes/Votes.php';

$session = CustomSessionHandler::getInstance();
$user = new User();
$ballot = new Ballot();
$view = View::getInstance();
$votes = new Votes();

if(!$user->isLoggedIn()) {
    header('location: index.php');
    exit();
}

$currentVoter = $user->getCurrentUser();
$hasVoted = $votes->hasVoted($currentVoter['id']);

// Determine the correct vote status
if (isset($_SESSION['just_voted']) && $_SESSION['just_voted']) {
    $voteStatus = 'complete';
    unset($_SESSION['just_voted']); // Clear the flag after using it
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
                    echo '<h1 class="page-header text-center title title-custom"><b>'. strtoupper($title).'</b></h1>';
                    
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
                            echo '<div class="alert alert-success text-center">
                                <h4><i class="icon fa fa-check"></i> Thank you for voting, '
                                . $user->getCurrentUser()['firstname'] . '!</h4>
                                Your vote has been recorded successfully.
                            </div>';
                            break;
                            
                        case 'already_voted':
                            echo '<div class="text-center">
                                <h2>You have already voted.</h2>
                                <p class="text-muted">Please tell an election officer/proctor if you think this is a mistake.</p>
                            </div>';
                            break;
                            
                        case 'current':
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
                            } else {
                                // Show the view ballot button for those who have voted
                                ?>
                                <div class="text-center">
                                    <button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#view" name="view_ballot">
                                        <i class="fa fa-eye"></i> View My Ballot
                                    </button>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </section>
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
</style>

</html>