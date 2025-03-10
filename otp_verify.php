<?php
require_once 'init.php';
require_once 'classes/OTPMailer.php';
require_once 'classes/CustomSessionHandler.php';
require_once 'classes/User.php';
require_once 'classes/Database.php';
require_once 'classes/View.php';

// Initialize variables
$message = '';
$status = '';

// Initialize classes
$db = Database::getInstance();
$session = CustomSessionHandler::getInstance();
$user = new User();
$view = View::getInstance();
$otpMailer = new OTPMailer($db->getConnection());

// Check if user is already logged in
if($user->isLoggedIn()) {
    header('location: home.php');
    exit();
}

// Check if student number is set in session
$student_number = $session->getSession('otp_student_number');
if (!$student_number) {
    header('Location: index.php');
    exit();
}

// Add this new check
$stmt = $db->prepare("SELECT COUNT(*) as count FROM otp_requests WHERE student_number = ? AND expires_at > NOW() AND attempts < 5");
$stmt->bind_param("s", $student_number);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // No valid OTP exists, clear session and redirect
    $session->unsetSession('otp_student_number');
    $session->setError('No valid OTP found. Please request a new one.');
    header('Location: index.php');
    exit();
}

// Add this at the top of the file with other POST checks
if (isset($_GET['cancel'])) {
    // Delete any existing OTP for this student
    $stmt = $db->prepare("DELETE FROM otp_requests WHERE student_number = ?");
    $stmt->bind_param("s", $student_number);
    $stmt->execute();
    
    // Clear the session
    $session->unsetSession('otp_student_number');
    
    // Redirect to index
    header('Location: index.php');
    exit();
}

// Handle resend OTP request
if (isset($_GET['resend']) && $_GET['resend'] == '1') {
    $otpMailer = new OTPMailer($db->getConnection());
    $sendResult = $otpMailer->generateAndSendOTP($student_number);
    
    if ($sendResult['success']) {
        $status = 'success';
        $message = 'A new OTP has been sent to your email.';
    } else {
        $status = 'error';
        $message = 'Failed to send new OTP. Please try again.';
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
    header('Content-Type: application/json');
    
    $otp = trim($_POST['otp']);
    $response = ['success' => false];
    
    if (empty($otp)) {
        $response['message'] = 'Please enter the OTP sent to your email';
        echo json_encode($response);
        exit();
    }
    
    // Validate the OTP using the OTPMailer class
    $validation_result = $otpMailer->validateOTP($student_number, $otp);
    
    if ($validation_result['success'] === true) {
        // Authenticate the user
        if ($user->authenticateWithOTP($student_number)) {
            // Set user session
            $user->setSession();
            
            // Set success message
            $session->setSuccess('Vote wisely, BTECHenyo!');
            
            // Clear the temporary session variable
            $session->unsetSession('otp_student_number');
            
            $response['success'] = true;
            echo json_encode($response);
            exit();
        } else {
            $response['message'] = 'Student not found in the voters database.';
            echo json_encode($response);
            exit();
        }
    } else {
        if ($validation_result['remaining_attempts'] === 0) {
            $session->unsetSession('otp_student_number');
            $session->setError($validation_result['message']);
            $response['redirect'] = 'index.php';
        } else {
            $response['message'] = $validation_result['message'];
        }
        echo json_encode($response);
        exit();
    }
}

// Get masked email for display
$masked_email = substr($student_number, 0, 3) . '****' . '@btech.ph.education';

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
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $status === 'success' ? 'success' : 'danger'; ?> alert-dismissible" name="errorMessage">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true" name="closeError">&times;</button>
                    <ul>
                        <li><i class="fa fa-<?php echo $status === 'success' ? 'check' : 'exclamation-triangle'; ?>"></i>&nbsp;<?php echo $message; ?></li>
                    </ul>
                </div>
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
                            <img src="images/assets/spin-icon.svg" class="spinner d-none" width="15" height="15" alt="loading">
                        </a>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <button type="submit" class="btn btn-primary btn-block btn-flat custom" id="verifyButton">
                                VERIFY & LOGIN <i class="fa fa-sign-in" id="verifyIcon"></i>
                                <img src="images/assets/spin-icon.svg" class="spinner d-none" width="20" height="20" alt="loading">
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

    .button-loading, .link-loading {
        display: none;
    }
    
    button:disabled {
        cursor: not-allowed;
        opacity: 0.7;
    }
    
    .d-none {
        display: none;
    }
    
    .resend-link.loading {
        cursor: not-allowed;
        opacity: 0.7;
    }

    .disabled {
        pointer-events: none;
        opacity: 0.7;
    }

    .spinner {
        vertical-align: middle;
        margin-left: 5px;
    }

    .resend-link .spinner {
        width: 15px;
        height: 15px;
    }

    .success-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.95);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .success-message {
        text-align: center;
        padding: 30px;
    }

    .success-message i {
        color: #1d7c39;
        font-size: 60px;
        margin-bottom: 20px;
    }

    .success-message h3 {
        color: #1d7c39;
        margin-bottom: 10px;
    }

    .success-message p {
        color: #666;
    }

    .fade-in {
        animation: fadeIn 0.5s ease-in;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    </style>

    <?php echo $view->renderScripts(); ?>
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
                        // Create and show success overlay
                        var overlay = $('<div class="success-overlay fade-in">' +
                            '<div class="success-message">' +
                            '<i class="fa fa-check-circle"></i>' +
                            '<h3>OTP Verified Successfully!</h3>' +
                            '<p>Redirecting to dashboard...</p>' +
                            '</div></div>');
                        
                        $('body').append(overlay);
                        
                        // Redirect after 3 seconds
                        setTimeout(function() {
                            window.location.href = 'home.php';
                        }, 3000);
                    } else if (response.redirect) {
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
                },
                error: function() {
                    location.reload();
                }
            });
        });
        
        // Handle resend link
        $('#resendLink').on('click', function() {
            var link = $(this);
            var text = link.find('.link-text');
            var spinner = link.find('.spinner');
            
            link.addClass('disabled');
            text.text('Sending');
            spinner.removeClass('d-none');
        });
        
        // Handle back to login button
        $('.btn-outline-secondary').on('click', function() {
            var btn = $(this);
            btn.prop('disabled', true);
            btn.html('Returning... <img src="images/assets/spin-icon.svg" class="spinner" width="15" height="15" alt="loading">');
        });
        
        // Format OTP input
        $('.otp-input').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
    </script>
</body>
</html>
