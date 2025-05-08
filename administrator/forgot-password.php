<?php
require_once __DIR__ .'/../init.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/CustomSessionHandler.php';
require_once __DIR__ . '/classes/View.php';
require_once __DIR__ . '/classes/Admin.php';
require_once __DIR__ . '/classes/PasswordReset.php';

$session = CustomSessionHandler::getInstance();
$view = View::getInstance();
$admin = Admin::getInstance();
$passwordReset = new PasswordReset();

// If admin is already logged in, redirect to home
if ($admin->isLoggedIn()) {
    header('Location: home');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    if (empty($email)) {
        $session->setError('Please enter your email address');
    } else {
        try {
            // Check if email exists in admin table
            $stmt = $admin->getDbInstance()->prepare("SELECT * FROM admin WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $session->setError('No account found with this email address');
            } else {
                $adminData = $result->fetch_assoc();
                
                // Create reset request and send email
                $result = $passwordReset->createResetRequest(
                    $email, 
                    $adminData['firstname'] . ' ' . $adminData['lastname']
                );
                
                if ($result['success']) {
                    $session->setSuccess($result['message']);
                } else {
                    $session->setError($result['message']);
                }
            }
        } catch (Exception $e) {
            $session->setError($e->getMessage());
        }
    }
}

// Show forgot password page
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

            // Display success messages
            if ($session->hasSuccess()) {
                echo '<div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h4><i class="icon fa fa-check"></i> Success!</h4>
                    <p>' . htmlspecialchars($session->getSuccess()) . '</p>
                </div>';
                $session->clearSuccess();
            }
            ?>

            <div class="login-box-body">
                <p class="text-center text-smaller lined"><span>RESET PASSWORD</span></p>
                <p class="text-center">Enter your email address to receive password reset instructions</p>
                <form action="" method="POST" role="presentation" autocomplete="off">
                    <div class="form-group has-feedback">
                        <input type="email" class="form-control username" name="email" placeholder="ENTER YOUR EMAIL ADDRESS" required>
                        <span class="fa fa-envelope form-control-feedback"></span>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <button type="submit" class="btn btn-primary btn-block btn-flat custom" id="resetButton">
                                SEND RESET LINK <i class="fa fa-paper-plane" id="resetIcon"></i>
                                <img src="assets/images/assets/spin-icon.svg" class="spinner d-none" width="20" height="20" alt="loading">
                            </button>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-xs-12 text-center">
                            <a href="<?php echo BASE_URL ?>administrator" class="text-center text-smaller">Back to Login</a>
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
            btn.contents().first().replaceWith('SENDING LINK ');
        });
    });
    </script>
</body>
</html> 