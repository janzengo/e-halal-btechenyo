<?php
require_once __DIR__ .'/../init.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/CustomSessionHandler.php';
require_once __DIR__ . '/../classes/Election.php';
require_once __DIR__ . '/classes/View.php';
require_once __DIR__ . '/classes/Admin.php';

$session = CustomSessionHandler::getInstance();
$election = new Election();
$view = View::getInstance();
$admin = Admin::getInstance();

// Handle logout action
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $admin->logout();
    header('Location: ' . BASE_URL . 'administrator');
    exit();
}

// Handle page routing
$page = isset($_GET['page']) ? $_GET['page'] : '';

// If admin is already logged in and trying to access login page, redirect to home
if ($admin->isLoggedIn() && empty($page)) {
    header('Location: home');
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($page)) {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($username) || empty($password)) {
        $session->setError('Please fill in all fields');
    } else {
        if ($admin->login($username, $password)) {
            $session->setSuccess('Login successful');
            header('Location: home');
            exit();
        } else {
            $session->setError('Invalid username or password');
        }
    }
}

// If not logged in and trying to access any page other than login, redirect to login
if (!$admin->isLoggedIn() && !empty($page)) {
    header('Location: ' . BASE_URL . 'administrator');
    exit();
}

// Load appropriate page
if ($admin->isLoggedIn() && !empty($page)) {
    $file = 'pages/' . $page . '.php';
    if (file_exists($file)) {
        require_once $file;
    } else {
        header('Location: home');
        exit();
    }
} else {
    // Show login page
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
                    <p class="text-center text-smaller lined"><span>WELCOME ADMIN</span></p>
                    <form action="" method="POST" role="presentation" autocomplete="off">
                        <div class="form-group has-feedback">
                            <input type="text" class="form-control username" name="username" placeholder="ENTER YOUR USERNAME" required>
                            <span class="fa fa-fingerprint form-control-feedback"></span>
                        </div>
                        <div class="form-group has-feedback">
                            <input type="password" class="form-control password" name="password" placeholder="ENTER YOUR PASSWORD" required>
                            <span class="fa fa-key form-control-feedback"></span>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <button type="submit" class="btn btn-primary btn-block btn-flat custom">LOGIN <i class="fa fa-sign-in"></i></button>
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
        </style>

        <script>
        $(document).ready(function() {
            // Enable Bootstrap alert dismissal
            $('.alert .close').click(function() {
                $(this).closest('.alert').fadeOut('fast');
            });
        });
        </script>
    </body>
    </html>
    <?php
}
?>