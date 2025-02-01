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

if (!$user->isLoggedIn()) {
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
    <div id="preloader">
        <div class="loader"></div>
    </div>
    <div class="wrapper">
        <?php echo $view->renderNavbar(); ?>
        <div class="content-wrapper">
            <div class="container">
                <section class="content">
                    <?php
                    $title = $ballot->getElectionName();
                    echo '<h1 class="page-header text-center title title-custom"><b>' . strtoupper($title) . '</b></h1>';

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
                                    <button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#view">
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
    <style>
        #preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #F1F1F1;
            z-index: 9999;
            transition: opacity 0.5s, visibility 0.5s;
        }

        #preloader.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .loader {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border: 4px solid #f3f3f3;
            border-top: 4px solid #239746;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
    <script>
        function getCookie(name) {
            const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            return match ? match[2] : null;
        }

        const preloader = document.getElementById('preloader');
        const preloaderShown = getCookie('preloader_shown');
        const preloaderDuration = preloaderShown ? 500 : 2000;

        if (preloader) {
            setTimeout(() => {
                preloader.classList.add('hidden');
                setTimeout(() => {
                    preloader.remove();
                }, 500);

                if (!preloaderShown) {
                    document.cookie = "preloader_shown=1; path=/; SameSite=Strict";
                }
            }, preloaderDuration);
        }
    </script>
</body>

</html>