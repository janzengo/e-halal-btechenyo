<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/CustomSessionHandler.php';
require_once __DIR__ . '/../classes/View.php';
require_once __DIR__ . '/../classes/Admin.php';
require_once __DIR__ . '/../classes/PasswordReset.php';

$session = CustomSessionHandler::getInstance();
$view = View::getInstance();
$admin = Admin::getInstance();
$passwordReset = new PasswordReset();

// Get reset token from URL
$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if (empty($token)) {
    $session->setError('Invalid password reset link');
    header('Location: ' . BASE_URL . 'administrator/forgot-password.php');
    exit();
}

// Validate token before showing the form
$validation = $passwordReset->validateToken($token);
if (!$validation['success']) {
    $session->setError($validation['message']);
    header('Location: ' . BASE_URL . 'administrator/forgot-password.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    try {
        if (empty($password) || empty($confirm_password)) {
            throw new Exception('Please enter and confirm your new password');
        }

        if ($password !== $confirm_password) {
            throw new Exception('Passwords do not match');
        }

        if (strlen($password) < 8) {
            throw new Exception('Password must be at least 8 characters long');
        }

        // Reset password
        $result = $passwordReset->resetPassword($token, $password);
        
        if ($result['success']) {
            $session->setSuccess($result['message']);
            header('Location: ' . BASE_URL . 'administrator');
            exit();
        } else {
            throw new Exception($result['message']);
        }

    } catch (Exception $e) {
        $session->setError($e->getMessage());
    }
}

// Show password reset form
echo $view->renderHeader();
?>
<body class="hold-transition login-page">
    <div class="inner-body">
        <div class="login-box">
            <div class="login-logo-container">
                <img src="<?php echo BASE_URL ?>images/login.jpg" alt="E-Halal BTECHenyo Logo">
                <h1><span>E-HALAL</span> <br> BTECHenyo</h1>
            </div>
            <p class="text-center text-smaller">A WEB-BASED VOTING SYSTEM FOR<br>DALUBHASAANG POLITEKNIKO NG LUNGSOD NG BALIWAG</p>
            
            <?php
            // Display error messages
            if ($session->hasError()) {
                echo '<div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h4><i class="icon fa fa-warning"></i> Error!</h4>
                    <ul>';
                foreach ($session->getError() as $error) {
                    echo "<li>" . htmlspecialchars($error) . "</li>";
                }
                echo '</ul></div>';
                $session->clearError();
            }
            ?>

            <div class="login-box-body">
                <p class="text-center text-smaller lined"><span>RESET PASSWORD</span></p>
                <p class="text-center">Enter your new password</p>
                
                <form action="" method="POST" role="presentation" autocomplete="off">
                    <div class="form-group has-feedback">
                        <input type="password" class="form-control password" name="password" placeholder="ENTER NEW PASSWORD" required minlength="8">
                        <span class="fa fa-key form-control-feedback"></span>
                    </div>
                    <div class="form-group has-feedback">
                        <input type="password" class="form-control password" name="confirm_password" placeholder="CONFIRM NEW PASSWORD" required minlength="8">
                        <span class="fa fa-key form-control-feedback"></span>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <button type="submit" class="btn btn-primary btn-block btn-flat custom" id="resetButton">
                                RESET PASSWORD <i class="fa fa-refresh" id="resetIcon"></i>
                                <img src="../assets/images/assets/spin-icon.svg" class="spinner d-none" width="20" height="20" alt="loading">
                            </button>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-xs-12 text-center">
                            <a href="<?php echo BASE_URL ?>administrator/forgot-password" class="text-center">Request New Reset Link</a>
                        </div>
                    </div>
                </form>
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

    .d-none {
        display: none !important;
    }

    .spinner {
        vertical-align: middle;
        margin-left: 5px;
    }

    button:disabled {
        cursor: not-allowed;
        opacity: 0.7;
    }

    .mt-3 {
        margin-top: 15px;
    }
    </style>

    <script>
    $(document).ready(function() {
        // Enable Bootstrap alert dismissal
        $('.alert .close').click(function() {
            $(this).closest('.alert').fadeOut('fast');
        });

        // Handle form submission
        $('form').on('submit', function(e) {
            var form = $(this);
            var btn = form.find('#resetButton');
            var icon = btn.find('#resetIcon');
            var spinner = btn.find('.spinner');
            
            // Show loading state
            btn.prop('disabled', true);
            icon.addClass('d-none');
            spinner.removeClass('d-none');
            btn.contents().first().replaceWith('RESETTING PASSWORD ');
        });
    });
    </script>
</body>
</html> 