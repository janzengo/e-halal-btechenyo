<?php
require_once 'init.php';
require_once 'classes/Database.php';
require_once 'classes/CustomSessionHandler.php';
require_once 'classes/User.php';
require_once 'classes/View.php';
require_once 'classes/Election.php';
require_once 'classes/Receipt.php';

$user = new User();
$view = View::getInstance();
$election = new Election();
$session = CustomSessionHandler::getInstance();

$error = '';
$receipt_html = '';

// Check if user is logged in and redirect if necessary
if($user->isLoggedIn()) {
    header('location: home.php');
    exit();
}

// Add this validation function at the top of the file after initializing objects
function isValidVoteRef($vote_ref) {
    return preg_match('/^VOTE-\d{6}-\d{4}$/', $vote_ref);
}

// Handle receipt request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vote_ref'])) {
    $vote_ref = trim($_POST['vote_ref']);
    
    if (!isValidVoteRef($vote_ref)) {
        $session->setError("Invalid vote reference format. Please use the format: VOTE-YYMMDD-XXXX");
    } else {
        try {
            $receipt = new Receipt();
            $result = $receipt->requestReceipt($vote_ref);
            
            if ($result['success']) {
                $receipt_html = $result['html'];
            } else {
                $error = $result['message'];
                $session->setError($error);
            }
        } catch (Exception $e) {
            error_log("Receipt request error: " . $e->getMessage());
            $session->setError("An error occurred while retrieving the receipt.");
        }
    }
}

echo $view->renderHeader();
?>

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
<?php if (empty($receipt_html)): ?>
<body class="hold-transition login-page">
    <div class="inner-body">
        <div class="login-box">
            <div class="login-logo-container">
                <img src="images/login.jpg" alt="">
                <h1><span>E-HALAL</span> <br> BTECHenyo</h1>
            </div>
            <p class="text-center text-smaller">A WEB-BASED VOTING SYSTEM FOR<br>DALUBHASAANG POLITEKNIKO NG LUNGSOD NG BALIWAG</p>
                <div class="login-box-body">
                    <p class="text-center text-smaller lined"><span>ENTER YOUR VOTE REFERENCE</span></p>
                    
                    <?php if ($session->hasError()): ?>
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <?php echo $session->getError(); ?>
                        </div>
                    <?php endif; ?>

                    <form id="receiptForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" role="presentation" autocomplete="off">
                    <div class="form-group has-feedback">
                            <input type="text" 
                                   autocomplete="off" 
                                   class="form-control username" 
                                   name="vote_ref" 
                                   pattern="VOTE-\d{6}-\d{4}"
                                   placeholder="VOTE-YYMMDD-XXXX"
                                   title="Please enter in format: VOTE-YYMMDD-XXXX"
                                   required>
                            <span class="fa fa-barcode form-control-feedback"></span>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <button type="submit" class="btn btn-primary btn-block btn-flat custom" id="submitBtn">
                                    VIEW RECEIPT <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                    <p class="text-center text-smaller" style="margin-top: 10px;">
                        <a href="index.php">Back to Login</a>
                    </p>
                </div>
            <?php else: ?>
                <div class="receipt-container">
                    <?php echo $receipt_html; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- jQuery 3 -->
    <script src="node_modules/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap 3.3.7 -->
    <script src="node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
</body>
</html>
