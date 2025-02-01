<?php
require_once 'init.php';
require_once 'classes/Database.php';
require_once 'classes/CustomSessionHandler.php';
require_once 'classes/User.php';
require_once 'classes/View.php';
require_once 'classes/Election.php';
require_once 'classes/Votes.php';

$session = CustomSessionHandler::getInstance();
$user = new User();
$view = View::getInstance();
$election = new Election();
$votes = new Votes();

// Check if election has ended
if (!$election->isElectionActive() && $election->hasEnded()) {
    if ($user->isLoggedIn()) {
        $user->logout();
    }
    $electionStatus = 'off';
} else {
    if ($user->isLoggedIn()) {
        $currentVoter = $user->getCurrentUser();
        if ($votes->hasVoted($currentVoter['id'])) {
            header('location: home.php?vote=complete');
        } else {
            header('location: home.php');
        }
        exit();
    }
}

echo $view->renderHeader();
?>

<body class="hold-transition login-page">
    <div id="preloader">
        <div class="loader"></div>
    </div>
    <div class="inner-body">
        <div class="login-box">
            <div class="login-logo-container">
                <img src="images/login.jpg" alt="">
                <h1><span>E-HALAL</span> <br> BTECHenyo</h1>
            </div>
            <p class="text-center text-smaller">A WEB-BASED VOTING SYSTEM FOR<br>DALUBHASAANG POLITEKNIKO NG LUNGSOD NG BALIWAG</p>

            <?php
            if ($session->hasError()) {
                echo '<div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <ul>';
                foreach ($session->getError() as $error) {
                    echo "<li><i class='fa fa-exclamation-triangle'></i>&nbsp;" . $error . "</li>";
                }
                echo '</ul></div>';
            }
            // Clear session messages after displaying
            $session->clearError();
            $session->clearSuccess();

            // Get current election status using Election class
            $currentElection = $election->getCurrentElection();
            $electionStatus = isset($electionStatus) ? $electionStatus : ($currentElection ? $currentElection['status'] : 'no_election');
            $electionName = $currentElection ? $currentElection['election_name'] : 'Sangguniang Mag-aaral';

            // Display appropriate message based on election status
            if ($electionStatus === 'off' || ($currentElection && $election->hasEnded())): ?>
                <section class="election-message">
                    <div class="election-message-box">
                        <h2>ELECTION PERIOD ENDED</h2>
                        <p>The voting system is currently closed as the election period for <?php echo htmlspecialchars($electionName); ?> has ended. Stay tuned for future announcements, BTECHenyos!</p>
                    </div>
                    <a href="#">Have some questions?</a>
                </section>
            <?php elseif ($electionStatus === 'paused'): ?>
                <section class="election-message">
                    <div class="election-message-box">
                        <h2>ELECTION PAUSED</h2>
                        <p>The voting system for <?php echo htmlspecialchars($electionName); ?> is currently paused. Stay tuned, BTECHenyos!</p>
                        <?php if ($timeLeft = $election->getTimeRemaining()): ?>
                            <p class="time-remaining">Time Remaining: <?php
                                                                        echo $timeLeft['days'] > 0 ? ($timeLeft['days'] == 1 ? "{$timeLeft['days']} day, " : "{$timeLeft['days']} days, ") : '';
                                                                        echo $timeLeft['hours'] > 0 ? ($timeLeft['hours'] == 1 ? "{$timeLeft['hours']} hour, " : "{$timeLeft['hours']} hours, ") : '';
                                                                        echo $timeLeft['minutes'] > 0 ? ($timeLeft['minutes'] == 1 ? "{$timeLeft['minutes']} minute, " : "{$timeLeft['minutes']} minutes ") : '';
                                                                        ?></p>
                        <?php endif; ?>
                    </div>
                    <a href="#">Have some questions?</a>
                </section>
            <?php elseif ($electionStatus === 'no_election'): ?>
                <section class="election-message">
                    <div class="election-message-box">
                        <h2>NO ELECTIONS</h2>
                        <p>There are no elections going on at the moment. Stay tuned, BTECHenyos!</p>
                    </div>
                    <a href="#">Have some questions?</a>
                </section>
            <?php elseif ($electionStatus === 'on' && $election->isElectionActive()): ?>
                <div class="login-box-body">
                    <p class="text-center text-smaller lined"><span>LOGIN WITH YOUR STUDENT NUMBER</span></p>
                    <?php if ($timeLeft = $election->getTimeRemaining()): ?>
                        <p class="text-center time-remaining">Time Remaining: <?php
                                                                                echo $timeLeft['days'] > 0 ? ($timeLeft['days'] == 1 ? "{$timeLeft['days']} day, " : "{$timeLeft['days']} days, ") : '';
                                                                                echo $timeLeft['hours'] > 0 ? ($timeLeft['hours'] == 1 ? "{$timeLeft['hours']} hour, " : "{$timeLeft['hours']} hours, ") : '';
                                                                                echo $timeLeft['minutes'] > 0 ? ($timeLeft['minutes'] == 1 ? "{$timeLeft['minutes']} minute, " : "{$timeLeft['minutes']} minutes ") : '';
                                                                                ?></p>
                    <?php endif; ?>
                    <form action="login.php" method="POST" role="presentation" autocomplete="off">
                        <div class="form-group has-feedback">
                            <input type="text" autocomplete="off" class="form-control username" name="voter" placeholder="ENTER YOUR STUDENT NUMBER" required>
                            <span class="fa fa-fingerprint form-control-feedback"></span>
                        </div>
                        <div class="form-group has-feedback">
                            <input type="password" class="form-control password" name="password" placeholder="ENTER YOUR PASSWORD" required>
                            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <button type="submit" class="btn btn-primary btn-block btn-flat custom" name="login">LOGIN <i class="fa fa-sign-in"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <section class="election-message">
                    <div class="election-message-box">
                        <h2>ELECTION PENDING</h2>
                        <p>The election for <?php echo htmlspecialchars($electionName); ?> is scheduled but hasn't started yet. Please check back later.</p>
                    </div>
                    <a href="#">Have some questions?</a>
                </section>
            <?php endif; ?>
        </div>
    </div>

    <style>
        #preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #F1F1F1;
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: opacity 0.5s;
        }

        .loader {
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

        .inner-body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-box {
            width: 100%;
            max-width: 360px;
            margin: 0;
        }

        .alert-dismissible {
            margin-bottom: 20px;
        }

        .alert-dismissible ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .alert-dismissible li {
            margin-bottom: 5px;
        }

        .alert-dismissible li:last-child {
            margin-bottom: 0;
        }

        .alert .close {
            opacity: 0.8;
            text-shadow: none;
            cursor: pointer;
        }

        .alert .close:hover {
            opacity: 1;
        }
    </style>

    <!-- jQuery 3 -->
    <script src="node_modules/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap 3.3.7 -->
    <script src="node_modules/bootstrap/dist/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            // Enable Bootstrap alert dismissal
            $('.alert .close').click(function() {
                $(this).closest('.alert').fadeOut('fast');
            });
        });
    </script>
    <script>
        // Cookie handling for session-based preloader
        function getCookie(name) {
            return document.cookie.split(';').some(c => c.trim().startsWith(name + '='));
        }

        // initial duration for preloader
        const preloaderShown = getCookie('preloader_shown');
        const preloaderDuration = preloaderShown ? 500 : 3000;

        // Handle preloader transition
        const preloader = document.getElementById('preloader');
        const content = document.querySelector('.inner-body');

        setTimeout(() => {
            preloader.style.opacity = '0';
            setTimeout(() => {
                preloader.remove();
                content.style.display = 'flex';

                // Set session cookie 
                if (!preloaderShown) {
                    document.cookie = "preloader_shown=1; path=/; SameSite=Strict";
                }
            }, 500);
        }, preloaderDuration);
    </script>
</body>

</html>