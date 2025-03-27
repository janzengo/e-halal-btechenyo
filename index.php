<?php
require_once 'init.php';
require_once 'classes/Database.php';
require_once 'classes/CustomSessionHandler.php';
require_once 'classes/User.php';
require_once 'classes/View.php';
require_once 'classes/Election.php';
require_once 'classes/Votes.php';

// Initialize all classes first
$db = Database::getInstance();
$session = CustomSessionHandler::getInstance();
$user = new User();
$view = View::getInstance();
$election = new Election();
$votes = new Votes();

// Now check for active OTP session
$student_number = $session->getSession('otp_student_number');
if ($student_number) {
    // Check if there's an active OTP for this student
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM otp_requests 
        WHERE student_number = ? AND expires_at > NOW() AND attempts < 5");
    $stmt->bind_param("s", $student_number);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        // Active OTP exists, redirect back to OTP verification
        header('Location: otp_verify.php');
        exit();
    } else {
        // No active OTP, clear the session
        $session->unsetSession('otp_student_number');
    }
}

// Then continue with election checks
if ($user->isLoggedIn()) {
    $currentVoter = $user->getCurrentUser();
    if ($votes->hasVoted($currentVoter['id'])) {
        header('location: home.php?vote=complete');
    } else {
        header('location: home.php');
    }
    exit();
}

echo $view->renderHeader();
?>
<body class="hold-transition login-page">
    <div class="inner-body">
        <div class="login-box">
            <div class="login-logo-container">
                <img src="images/login.jpg" alt="">
                <h1><span>E-HALAL</span> <br> BTECHenyo</h1>
            </div>
            <p class="text-center text-smaller">A WEB-BASED VOTING SYSTEM FOR<br>DALUBHASAANG POLITEKNIKO NG LUNGSOD NG BALIWAG</p>
            
            <?php
            if ($session->hasError()) {
                echo '<div class="alert alert-danger alert-dismissible" name="errorMessage">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true" name="closeError">&times;</button>
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
            $electionStatus = $currentElection ? $currentElection['status'] : 'no_election';
            $electionName = $currentElection ? $currentElection['election_name'] : 'Sangguniang Mag-aaral';

            // Get current time
            $currentTime = new DateTime('now', new DateTimeZone('Asia/Manila'));
            $endTime = null;

            if ($currentElection) {
                $endTime = new DateTime($currentElection['end_time'], new DateTimeZone('Asia/Manila'));
                
                // Check if election has ended
                if ($currentTime >= $endTime) {
                    $electionStatus = Election::STATUS_COMPLETED;
                }
            }

            // Display appropriate message based on election status
            if ($electionStatus === Election::STATUS_COMPLETED || ($currentElection && $currentTime >= $endTime)): ?>
                <section class="election-message">
                    <div class="election-message-box">
                        <h2>ELECTION PERIOD ENDED</h2>
                        <p>The voting system is currently closed as the election period for <?php echo htmlspecialchars($electionName); ?> has ended. Stay tuned for future announcements, BTECHenyos!</p>
                    </div>
                    <a href="#">Have some questions?</a>
                </section>
            <?php elseif ($electionStatus === Election::STATUS_PAUSED): ?>
                <section class="election-message">
                    <div class="election-message-box">
                        <h2>ELECTION PAUSED</h2>
                        <p>The voting system for <?php echo htmlspecialchars($electionName); ?> is currently paused. Stay tuned, BTECHenyos!</p>
                    </div>
                    <a href="#">Have some questions?</a>
                </section>
            <?php elseif ($electionStatus === Election::STATUS_PENDING): ?>
                <section class="election-message">
                    <div class="election-message-box">
                        <h2>ELECTION PENDING</h2>
                        <p>The election for <?php echo htmlspecialchars($electionName); ?> is being set up. Stay tuned, BTECHenyos!</p>
                    </div>
                    <a href="#">Have some questions?</a>
                </section>
            <?php elseif ($electionStatus === 'no_election' || $electionStatus === Election::STATUS_SETUP): ?>
                <section class="election-message">
                    <div class="election-message-box">
                        <h2>NO ACTIVE ELECTION</h2>
                        <p>There are no active elections at the moment. Stay tuned, BTECHenyos!</p>
                    </div>
                    <a href="#">Have some questions?</a>
                </section>
            <?php elseif ($electionStatus === Election::STATUS_ACTIVE): ?>
                <div class="login-box-body">
                    <p class="text-center text-smaller lined"><span>LOGIN WITH YOUR STUDENT NUMBER</span></p>
                    <?php if ($timeLeft = $election->getTimeRemaining()): ?>
                    <p class="text-center time-remaining">Time Remaining: <?php 
                        echo $timeLeft['days'] > 0 ? ($timeLeft['days'] == 1 ? "{$timeLeft['days']} day, " : "{$timeLeft['days']} days, ") : '';
                        echo $timeLeft['hours'] > 0 ? ($timeLeft['hours'] == 1 ? "{$timeLeft['hours']} hour, " : "{$timeLeft['hours']} hours, ") : '';
                        echo $timeLeft['minutes'] > 0 ? ($timeLeft['minutes'] == 1 ? "{$timeLeft['minutes']} minute" : "{$timeLeft['minutes']} minutes") : '';
                    ?></p>
                    <?php endif; ?>
                    <form id="otpForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" role="presentation" autocomplete="off">
                        <div class="form-group has-feedback">
                            <input type="text" autocomplete="off" class="form-control username" name="student_number" placeholder="ENTER YOUR STUDENT NUMBER" required>
                            <span class="fa fa-fingerprint form-control-feedback"></span>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <button type="submit" class="btn btn-primary btn-block btn-flat custom" name="send_otp" id="otpButton">
                                    REQUEST OTP <i class="fa fa-paper-plane" id="sendIcon"></i>
                                    <img src="images/assets/spin-icon.svg" class="spinner d-none" width="20" height="20" alt="loading">
                                </button>
                            </div>
                        </div>
                    </form>
                    <p class="text-center text-smaller" style="margin-top: 10px;">Already voted? <a href="request_receipt.php">See your receipt here</a></p>
                </div>
            <?php 
            // Handle form submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_otp'])) {
                require_once 'classes/OTPMailer.php';
                
                $student_number = trim($_POST['student_number']);
                $response = array();
                
                // Validate student number exists in database and check voting status
                $db = Database::getInstance();
                $stmt = $db->prepare("SELECT has_voted FROM voters WHERE student_number = ?");
                $stmt->bind_param("s", $student_number);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    $session->setError('Student number not found in the database.');
                    $response['success'] = false;
                    $response['message'] = $session->getError();
                    echo json_encode($response);
                    exit();
                }

                // Check if student has already voted
                $voter = $result->fetch_assoc();
                if ($voter['has_voted'] == 1) {
                    $session->setError('You have already cast your vote in this election. Each student can only vote once.');
                    $response['success'] = false;
                    $response['message'] = $session->getError();
                    echo json_encode($response);
                    exit();
                }
                
                // If not voted, proceed with OTP generation and sending
                $otpMailer = new OTPMailer($db->getConnection());
                $sendResult = $otpMailer->generateAndSendOTP($student_number);
                
                if ($sendResult['success']) {
                    // Store student number in session for OTP verification
                    $session->setSession('otp_student_number', $student_number);
                    
                    $session->setSuccess('OTP sent successfully.');
                    $response['success'] = true;
                } else {
                    $session->setError('Failed to send OTP. Please try again.');
                    $response['success'] = false;
                    $response['message'] = $session->getError();
                }
                
                echo json_encode($response);
                exit();
            }
            endif; ?>
        </div>
    </div>

    <style>
        /* invalid cred error */
    [name="errorMessage"] {
        background-color:rgb(253, 204, 201) !important;
        border: none;
        color: rgb(185, 59, 59) !important;
        padding: 10px 30px;
    }

    button[name="closeError"] {
        color: rgb(185, 59, 59) !important;
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

    .button-loading {
        display: none;
    }
    
    button:disabled {
        cursor: not-allowed;
        opacity: 0.7;
    }
    
    .d-none {
        display: none;
    }

    .spinner {
        vertical-align: middle;
        margin-left: 5px;
    }

    .d-none {
        display: none !important;
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
        
        // Handle form submission
        $('#otpForm').on('submit', function(e) {
            e.preventDefault();
            
            var form = $(this);
            var btn = form.find('button[type="submit"]');
            var icon = btn.find('i');
            var spinner = btn.find('.spinner');
            var studentNumber = form.find('input[name="student_number"]').val();
            
            // Show loading state
            btn.prop('disabled', true);
            icon.addClass('d-none');
            spinner.removeClass('d-none');
            btn.contents().first().replaceWith('SENDING OTP ');
            
            // Create form data
            var formData = new FormData();
            formData.append('student_number', studentNumber);
            formData.append('send_otp', '1');
            
            // Send AJAX request
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        var result = JSON.parse(response);
                        if (result.success) {
                            // Simply redirect to OTP verification page
                            window.location.href = 'otp_verify.php';
                        } else {
                            // Show error message
                            var errorHtml = '<div class="alert alert-danger alert-dismissible" name="errorMessage">' +
                                '<button type="button" class="close" data-dismiss="alert" aria-hidden="true" name="closeError">&times;</button>' +
                                '<ul><li><i class="fa fa-exclamation-triangle"></i>&nbsp;' + result.message + '</li></ul></div>';
                            $('.login-box-body').before(errorHtml);
                            
                            // Reset button state
                            btn.prop('disabled', false);
                            icon.removeClass('d-none');
                            spinner.addClass('d-none');
                            btn.contents().first().replaceWith('REQUEST OTP ');
                        }
                    } catch (e) {
                        location.reload();
                    }
                },
                error: function() {
                    // Show error message
                    var errorHtml = '<div class="alert alert-danger alert-dismissible" name="errorMessage">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-hidden="true" name="closeError">&times;</button>' +
                        '<ul><li><i class="fa fa-exclamation-triangle"></i>&nbsp;An error occurred. Please try again.</li></ul></div>';
                    $('.login-box-body').before(errorHtml);
                    
                    // Reset button state
                    btn.prop('disabled', false);
                    icon.removeClass('d-none');
                    spinner.addClass('d-none');
                    btn.contents().first().replaceWith('REQUEST OTP ');
                }
            });
        });
    });
    </script>
</body>
</html>