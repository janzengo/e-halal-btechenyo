<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../classes/CustomSessionHandler.php';
require_once __DIR__ . '/../classes/Admin.php';
require_once __DIR__ . '/../classes/AdminOTPMailer.php';
require_once __DIR__ . '/../classes/View.php';
require_once __DIR__ . '/../classes/Elections.php';

// Initialize classes
$admin = Admin::getInstance();
$session = CustomSessionHandler::getInstance();
$otpMailer = new AdminOTPMailer();
$view = View::getInstance();
$elections = Elections::getInstance();

// Initialize variables
$message = '';
$status = '';

// If already logged in, redirect to home
if ($admin->isLoggedIn()) {
    header('Location: ../home');
    exit();
}

// Get username from session
$username = $session->getSession('temp_admin_username');
$adminData = $session->getSession('temp_admin_data');

// If no username or admin data in session, redirect to login
if (!$username || !$adminData) {
    $session->setError('Invalid session. Please login again.');
    header('Location: ../index.php');
    exit();
}

// Check if there's a valid OTP request and attempts
$otpData = $otpMailer->getOTPAttempts($adminData['email']);

if (!$otpData) {
    $session->unsetSession('temp_admin_username');
    $session->unsetSession('temp_admin_data');
    $session->setError('Invalid or expired OTP request. Please login again.');
    header('Location: ../index.php');
    exit();
}

// Check if max attempts reached
if ($otpData['attempts'] >= 5) {
    $session->unsetSession('temp_admin_username');
    $session->unsetSession('temp_admin_data');
    $session->setError('Maximum attempts reached. Please login again.');
    header('Location: ../index.php');
    exit();
}

// Handle cancel action
if (isset($_GET['action']) && $_GET['action'] === 'cancel') {
    $otpMailer->deleteOTP($adminData['email']);
    $session->unsetSession('temp_admin_username');
    $session->unsetSession('temp_admin_data');
    header('Location: ../index.php');
    exit();
}

// Handle resend action
if (isset($_GET['action']) && $_GET['action'] === 'resend') {
    $result = $otpMailer->generateAndSendOTP($adminData['email'], $adminData['firstname'] . ' ' . $adminData['lastname']);
    if ($result['success']) {
        $status = 'success';
        $message = 'A new OTP has been sent to your email.';
    } else {
        $status = 'error';
        $message = $result['message'];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = $_POST['otp'] ?? '';
    
    if (empty($otp)) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            echo json_encode(['success' => false, 'message' => 'Please enter the OTP code.']);
            exit();
        }
        $session->setError('Please enter the OTP code.');
        header('Location: otp_verify.php');
        exit();
    }

    // Validate OTP and track attempts
    $validation_result = $otpMailer->validateOTP($adminData['email'], $otp);
    
    if ($validation_result['success']) {
        // OTP is valid, complete login through Admin class
        if ($admin->completeLogin($username)) {
            // Clean up OTP and temporary data
            $otpMailer->deleteOTP($adminData['email']);
            $session->unsetSession('temp_admin_username');
            $session->unsetSession('temp_admin_data');
            $session->setSuccess('Login successful');
            
            // Get current election status and determine redirect URL
            $current_status = $elections->getCurrentStatus();
            $base_url = BASE_URL . 'administrator/';
            
            switch($current_status) {
                case 'setup':
                    $redirect_url = $base_url . 'setup';
                    break;
                case 'pending':
                    $redirect_url = $base_url . 'configure';
                    break;
                case 'active':
                    $redirect_url = $base_url . 'home';
                    break;
                case 'completed':
                    $redirect_url = $base_url . 'completed';
                    break;
                default:
                    $redirect_url = $base_url . 'home';
            }
            
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => true, 'redirect' => $redirect_url]);
                exit();
            }
            
            header('Location: ' . $redirect_url);
            exit();
        } else {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'message' => 'Failed to complete login. Please try again.']);
                exit();
            }
            $session->setError('Failed to complete login. Please try again.');
            header('Location: otp_verify.php');
            exit();
        }
    } else {
        if (isset($validation_result['remaining_attempts']) && $validation_result['remaining_attempts'] <= 0) {
            $session->unsetSession('temp_admin_username');
            $session->unsetSession('temp_admin_data');
            
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'redirect' => '../index.php']);
                exit();
            }
            
            header('Location: ../index.php');
            exit();
        }
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            echo json_encode(['success' => false, 'message' => $validation_result['message']]);
            exit();
        }
        
        $session->setError($validation_result['message']);
        header('Location: otp_verify.php');
        exit();
    }
}

// Mask email for display
$email = $adminData['email'];
$atPos = strpos($email, '@');
$maskedEmail = substr($email, 0, 2) . str_repeat('*', $atPos - 2) . substr($email, $atPos);

// Get remaining attempts for display
$remainingAttempts = $otpMailer->getRemainingAttempts($adminData['email']);

echo $view->renderHeader();
?>
<body class="hold-transition login-page">
    <div class="inner-body">
        <div class="login-box">
            <div class="login-logo-container">
                <img src="../../images/login.jpg" alt="E-Halal Logo">
                <h1><span>E-HALAL</span> <br> BTECHenyo</h1>
            </div>
            <p class="text-center text-smaller">A WEB-BASED VOTING SYSTEM FOR<br>DALUBHASAANG POLITEKNIKO NG LUNGSOD NG BALIWAG</p>
            
            <?php if ($session->hasError()): ?>
                <div class="alert alert-danger alert-dismissible" name="errorMessage">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true" name="closeError">&times;</button>
                    <?php 
                    $errors = $session->getError();
                    if (is_array($errors)) {
                        echo "<ul>";
                        foreach ($errors as $error) {
                            echo "<li><i class='fa fa-exclamation-triangle'></i>&nbsp;" . htmlspecialchars($error) . "</li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "<i class='fa fa-exclamation-triangle'></i>&nbsp;" . htmlspecialchars($errors);
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if ($session->hasSuccess()): ?>
                <div class="alert alert-success alert-dismissible" name="successMessage">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true" name="closeSuccess">&times;</button>
                    <?php 
                    $success = $session->getSuccess();
                    if (is_array($success)) {
                        echo implode("<br><i class='fa fa-check'></i>&nbsp;", array_map('htmlspecialchars', $success));
                    } else {
                        echo "<i class='fa fa-check'></i>&nbsp;" . htmlspecialchars($success);
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $status === 'success' ? 'success' : 'danger'; ?> alert-dismissible" name="errorMessage">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true" name="closeError">&times;</button>
                    <ul>
                        <li><i class="fa fa-<?php echo $status === 'success' ? 'check' : 'exclamation-triangle'; ?>"></i>&nbsp;<?php echo htmlspecialchars($message); ?></li>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="login-box-body">
                <p class="text-center text-smaller lined"><span>VERIFY OTP CODE</span></p>
                <div class="text-center mb-3">
                    <p>We've sent a verification code to:</p>
                    <h5 class="text-primary"><?php echo htmlspecialchars($maskedEmail); ?></h5>
                    <p class="text-muted small">Enter the 6-digit code to complete your login</p>
                </div>
                
                <form method="POST" action="" role="presentation" autocomplete="off">
                    <div class="form-group">
                        <input type="text" class="form-control otp-input" id="otp" name="otp" 
                               placeholder="------" maxlength="6" autocomplete="off" required>
                    </div>
                    <div class="text-center mb-3">
                        <span class="text-muted">Didn't receive the code? </span>
                        <a href="otp_verify.php?action=resend" class="resend-link" id="resendLink">
                            <span class="link-text">Resend OTP</span>
                            <img src="../../images/assets/spin-icon.svg" class="spinner d-none" width="15" height="15" alt="loading">
                        </a>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <button type="submit" class="btn btn-primary btn-block btn-flat custom" id="verifyButton">
                                VERIFY & LOGIN <i class="fa fa-sign-in" id="verifyIcon"></i>
                                <img src="../../images/assets/spin-icon.svg" class="spinner d-none" width="20" height="20" alt="loading">
                            </button>
                        </div>
                    </div>
                </form>
                
                <hr>
                <div class="text-center">
                    <a href="otp_verify.php?action=cancel" class="btn btn-outline-secondary btn-sm">Back to Login</a>
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
                        
                        // Use the redirect URL from the response
                        setTimeout(function() {
                            window.location.href = response.redirect;
                        }, 2000);
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
            
            window.location.href = link.attr('href');
        });
        
        // Format OTP input
        $('.otp-input').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
    </script>
</body>
</html> 