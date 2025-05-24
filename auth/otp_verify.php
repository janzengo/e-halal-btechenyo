<?php
require_once '../init.php';
require_once '../classes/OTPMailer.php';
require_once '../classes/CustomSessionHandler.php';
require_once '../classes/User.php';
require_once '../classes/Database.php';
require_once '../classes/View.php';

// Initialize classes
$db = Database::getInstance();
$session = CustomSessionHandler::getInstance();
$user = new User();
$view = View::getInstance();
$otpMailer = new OTPMailer();

// Check if user is already logged in
if($user->isLoggedIn()) {
    header('location: ../home.php');
    exit();
}

// Check if student number is set in session
$student_number = $session->getSession('otp_student_number');
if (!$student_number) {
    header('Location: ../index.php');
    exit();
}

// Check for valid OTP request
$stmt = $db->prepare("SELECT COUNT(*) as count FROM otp_requests WHERE student_number = ? AND expires_at > NOW() AND attempts < 5");
$stmt->bind_param("s", $student_number);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    $session->unsetSession('otp_student_number');
    $session->setError('No valid OTP found. Please request a new one.');
    header('Location: ../index.php');
    exit();
}

// Handle cancel request
if (isset($_GET['cancel'])) {
    $stmt = $db->prepare("DELETE FROM otp_requests WHERE student_number = ?");
    $stmt->bind_param("s", $student_number);
    $stmt->execute();
    
    $session->unsetSession('otp_student_number');
    header('Location: ../index.php');
    exit();
}

// Handle resend OTP request
if (isset($_GET['resend']) && $_GET['resend'] == '1') {
    $sendResult = $otpMailer->generateAndSendOTP($student_number);
    header('Content-Type: application/json');
    echo json_encode($sendResult);
    exit();
}

// Process OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
    $otp = trim($_POST['otp']);
    
    if (empty($otp)) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Please enter the OTP code.']);
            exit();
        }
        $session->setError('Please enter the OTP code.');
        header('Location: otp_verify.php');
        exit();
    }
    
    try {
        // Verify OTP and complete login in one step
        $result = $otpMailer->verifyOTPAndLogin($student_number, $otp);
        
        if ($result['success']) {
            // Clear the temporary session variable
            $session->unsetSession('otp_student_number');
            
            // Set success message in session for the next page
            $session->setSuccess('Vote wisely, BTECHenyo!');
            
            // Use BASE_URL for redirect
            $redirect_url = BASE_URL . 'home.php';
            
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'redirect' => $redirect_url,
                    'message' => 'OTP verified successfully'
                ]);
                exit();
            }
            
            header('Location: ' . $redirect_url);
            exit();
        } else {
            if (strpos($result['message'], 'Maximum attempts reached') !== false) {
                $session->unsetSession('otp_student_number');
                
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'redirect' => BASE_URL . 'index.php',
                        'message' => $result['message']
                    ]);
                    exit();
                }
                
                header('Location: ' . BASE_URL . 'index.php');
                exit();
            }
            
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $result['message']
                ]);
                exit();
            }
            
            $session->setError($result['message']);
            header('Location: otp_verify.php');
            exit();
        }
    } catch (Exception $e) {
        error_log("OTP Verification Error: " . $e->getMessage());
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred during verification. Please try again.'
            ]);
            exit();
        }
        
        $session->setError('An error occurred during verification. Please try again.');
        header('Location: otp_verify.php');
        exit();
    }
}

// Get masked email for display
$masked_email = substr($student_number, 0, 3) . '****' . '@btech.ph.education';

// Render the page
echo $view->renderHeader();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>E-Halal | OTP Verification</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap -->
    <link rel="stylesheet" href="../node_modules/bootstrap/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../plugins/font-awesome/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../dist/css/AdminLTE.css">
    <link rel="stylesheet" href="../dist/css/skins/_all-skins.min.css">
    <link rel="stylesheet" href="../dist/css/custom.css">
    <link rel="stylesheet" href="../dist/css/login.css">
</head>
<body class="hold-transition login-page">
    <div class="inner-body">
        <div class="login-box">
            <div class="login-logo-container">
                <img src="../images/login.jpg" alt="">
                <h1><span>E-HALAL</span> <br> BTECHenyo</h1>
            </div>
            <p class="text-center text-smaller">A WEB-BASED VOTING SYSTEM FOR<br>DALUBHASAANG POLITEKNIKO NG LUNGSOD NG BALIWAG</p>
            
            <?php if ($session->hasSuccess()): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h4><i class="icon fa fa-check"></i> Success!</h4>
                    <?php echo $session->getSuccess(); ?>
                </div>
                <?php $session->clearSuccess(); ?>
            <?php endif; ?>

            <?php if ($session->hasError()): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <ul>
                        <?php foreach ($session->getError() as $error): ?>
                            <li><i class="fa fa-exclamation-triangle"></i>&nbsp;<?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php $session->clearError(); ?>
            <?php endif; ?>
            
            <div class="login-box-body">
                <p class="text-center text-smaller lined"><span>VERIFY OTP CODE</span></p>
                <div class="text-center mb-3">
                    <p>We've sent a verification code to:</p>
                    <h5 class="text-primary"><?php echo $masked_email; ?></h5>
                    <p class="text-muted small">Enter the 6-digit code to complete your login</p>
                </div>
                
                <form method="POST" action="" role="presentation" autocomplete="off">
                    <div class="form-group">
                        <input type="text" class="form-control otp-input" id="otp" name="otp" 
                               placeholder="------" maxlength="6" autocomplete="off" required>
                    </div>
                    <div class="text-center mb-3">
                        <span class="text-muted">Didn't receive the code? </span>
                        <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?resend=1" 
                           class="resend-link" id="resendLink">
                            <span class="link-text">Resend OTP</span>
                            <img src="../images/assets/spin-icon.svg" class="spinner d-none" width="15" height="15" alt="loading">
                        </a>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <button type="submit" class="btn btn-primary btn-block btn-flat custom" id="verifyButton">
                                VERIFY & LOGIN <i class="fa fa-sign-in" id="verifyIcon"></i>
                                <img src="../images/assets/spin-icon.svg" class="spinner d-none" width="20" height="20" alt="loading">
                            </button>
                        </div>
                    </div>
                </form>
                
                <hr>
                <div class="text-center">
                    <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?cancel=1" 
                       class="btn btn-outline-secondary btn-sm">Back to Login</a>
                </div>
            </div>
        </div>
    </div>

    <style>
    /* Styles remain the same as before */
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

    .otp-input {
        letter-spacing: 0.5rem;
        font-size: 1.5rem;
        text-align: center;
        padding: 10px;
        border-radius: 5px;
        border: 1px solid #ccc;
        background-color: #f8f9fa;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .resend-link {
        color: #1d7c39;
        text-decoration: underline;
        cursor: pointer;
    }
    
    .text-primary {
        color: #1d7c39 !important;
    }

    .btn-primary {
        background-color: #1d7c39;
        border-color: #1d7c39;
    }

    .btn-primary:hover {
        background-color: #165a2b;
        border-color: #165a2b;
    }
    
    .d-none {
        display: none;
    }
    
    .spinner {
        vertical-align: middle;
        margin-left: 5px;
    }

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
    </style>

    <!-- jQuery -->
    <script src="../node_modules/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="../node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    $(document).ready(function() {
        // Enable Bootstrap alert dismissal
        $('.alert .close').click(function() {
            $(this).closest('.alert').fadeOut('fast');
        });
        
        // Handle form submission
        $('form').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var btn = form.find('button[type="submit"]');
            var icon = btn.find('#verifyIcon');
            var spinner = btn.find('.spinner');
            
            // Show loading state
            btn.prop('disabled', true);
            icon.addClass('d-none');
            spinner.removeClass('d-none');
            btn.contents().first().replaceWith('VERIFYING ');
            
            // Submit form via AJAX
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Show success message using SweetAlert2
                        Swal.fire({
                            icon: 'success',
                            title: 'Vote wise, BTECHenyos!',
                            text: 'Redirecting to voting page...',
                            timer: 2000,
                            showConfirmButton: false,
                            allowOutsideClick: false
                        }).then(function() {
                            window.location.href = response.redirect;
                        });
                    } else {
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            // Show error message
                            var errorHtml = '<div class="alert alert-danger alert-dismissible" name="errorMessage">' +
                                '<button type="button" class="close" data-dismiss="alert" aria-hidden="true" name="closeError">&times;</button>' +
                                '<ul><li><i class="fa fa-exclamation-triangle"></i>&nbsp;' + response.message + '</li></ul></div>';
                            $('.login-box-body').before(errorHtml);
                            
                            // Reset button state
                            btn.prop('disabled', false);
                            icon.removeClass('d-none');
                            spinner.addClass('d-none');
                            btn.contents().first().replaceWith('VERIFY & LOGIN ');
                        }
                    }
                },
                error: function() {
                    location.reload();
                }
            });
        });
        
        // Handle resend link
        $('#resendLink').on('click', function(e) {
            e.preventDefault();
            var link = $(this);
            var text = link.find('.link-text');
            var spinner = link.find('.spinner');
            
            link.addClass('disabled');
            text.text('Sending');
            spinner.removeClass('d-none');
            
            $.ajax({
                url: link.attr('href'),
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var successHtml = '<div class="alert alert-success alert-dismissible">' +
                            '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
                            '<i class="fa fa-check"></i>&nbsp;' + response.message + '</div>';
                        $('.login-box-body').before(successHtml);
                    } else {
                        var errorHtml = '<div class="alert alert-danger alert-dismissible" name="errorMessage">' +
                            '<button type="button" class="close" data-dismiss="alert" aria-hidden="true" name="closeError">&times;</button>' +
                            '<i class="fa fa-exclamation-triangle"></i>&nbsp;' + response.message + '</div>';
                        $('.login-box-body').before(errorHtml);
                    }
                    
                    // Reset link state
                    link.removeClass('disabled');
                    text.text('Resend OTP');
                    spinner.addClass('d-none');
                },
                error: function() {
                    location.reload();
                }
            });
        });
        
        // Format OTP input
        $('.otp-input').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
    </script>
</body>
</html>