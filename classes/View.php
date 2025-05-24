<?php
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/CustomSessionHandler.php';
require_once __DIR__ . '/User.php';


class View {
    private $user;
    private $session;
    private static $instance = null;
    

    private function __construct() {
        $this->session = CustomSessionHandler::getInstance();
        $this->user = new User();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function renderNavbar() {
        $voter = $this->user->getCurrentUser();
        ob_start();
        ?>
        <header class="main-header">
            <nav class="navbar navbar-static-top">
                <div class="container" name="navbar">
                    <div class="navbar-header">
                        <a href="#" class="navbar-brand">
                            <img src="images/h-logo.jpg" alt="Voting System Logo" class="navbar-logo">
                        </a>
                    </div>                
                    <!-- Navbar Right Menu -->
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            <li class="user user-menu voter-id">
                                <a href="#">
                                    <span class="voter-label">Voter ID: </span>
                                    <span class="voter-number"><?php echo isset($voter['student_number']) ? $voter['student_number'] : 'Voter'; ?></span>
                                </a>
                            </li>
                            <li class="signout-btn">
                                <a href="logout.php">
                                    <i class="fa fa-sign-out"></i>
                                    <span class="signout-text">Sign Out</span>
                                </a>
                            </li>  
                        </ul>
                    </div>
                </div>
            </nav>
        </header>
        <style>
            /* Navbar Base Styles */
            .navbar-static-top {
                height: 50px;
                background-color: #fff;
                border-bottom: 1px solid rgba(0,0,0,0.1);
            }

            .container[name="navbar"] {
                display: flex;
                justify-content: space-between;
                align-items: center;
                height: 100%;
                padding: 0;
            }

            .navbar-custom-menu {
                padding-right: 15px;
            }

            /* Logo Styles */
            .navbar-brand {
                padding: 0;
                height: 50px;
                display: flex;
                align-items: center;
            }

            .navbar-logo {
                height: 50px;
                width: auto;
            }

            /* Right Menu Styles */
            .navbar-custom-menu {
                display: flex;
                align-items: center;
            }

            .navbar-nav {
                display: flex;
                align-items: center;
                margin: 0;
                padding: 0;
                list-style: none;
            }

            .navbar-nav > li > a {
                display: flex;
                align-items: center;
                padding: 15px;
                color: #333;
                text-decoration: none;
                transition: color 0.2s ease;
            }

            .navbar-nav > li > a:hover {
                color: #249646;
            }

            /* Voter ID Styles */
            .voter-id a {
                font-size: 0.95rem;
            }

            .voter-label {
                color: #666;
                margin-right: 4px;
            }

            .voter-number {
                color: #249646;
                font-weight: 500;
            }

            /* Sign Out Button Styles */
            .signout-btn a {
                gap: 6px;
            }

            /* Responsive Styles */
            @media (max-width: 768px) {
                .container[name="navbar"] {
                    padding: 0 15px;
                }

                .voter-id {
                    display: none !important;
                }

                .signout-btn a {
                    padding: 15px;
                }
            }
        </style>
        <?php
        return ob_get_clean();
    }

    public function renderHeader() {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <title>E-Halal BTECHenyo | Voting System</title>
            <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>node_modules/bootstrap/dist/css/bootstrap.min.css">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/AdminLTE.css">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/skins/_all-skins.min.css">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/custom.css">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/login.css">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/ballots.css">
            <link rel="icon" type="image/x-icon" href="<?php echo BASE_URL; ?>images/icon.ico">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/customFonts.css">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>node_modules/sweetalert2/dist/sweetalert2.min.css" />
            <!-- Font Awesome -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>plugins/font-awesome/css/all.min.css" />
            <!-- Fancybox -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>node_modules/@fancyapps/ui/dist/fancybox/fancybox.css" />
        </head>		
        <?php
        return ob_get_clean();
    }

    public function renderFooter() {
        ob_start();
        ?>
        <footer class="main-footer" style="margin-left: 0px !important;">
            <div class="container">
            <div class="text-center">
                <?php $date = new DateTime(); ?>
                <strong>Copyright &copy; <?php echo $date->format('Y'); ?> <a href="https://btech.edu.ph">Dalubhsaang Politekniko ng Lungsod ng Baliwag</a></strong>
            </div>
            </div>
        </footer>
        
        <!-- Back to Top Button -->
        <a href="#" class="back-to-top"><i class="fa fa-arrow-up"></i></a>
        
        <?php
        return ob_get_clean();
    }
    


    public function renderScripts() {
        ob_start();
        ?>
        <!-- jQuery 3 -->
        <script src="<?php echo BASE_URL; ?>node_modules/jquery/dist/jquery.min.js"></script>
        <!-- Bootstrap 3.3.7 -->
        <script src="<?php echo BASE_URL; ?>node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
        <!-- DataTables -->
        <script src="<?php echo BASE_URL; ?>node_modules/datatables.net/js/jquery.dataTables.js"></script>
        <script src="<?php echo BASE_URL; ?>node_modules/datatables.net-bs/js/dataTables.bootstrap.js"></script>
        <!-- SlimScroll -->
        <script src="<?php echo BASE_URL; ?>node_modules/jquery-slimscroll/jquery.slimscroll.min.js"></script>
        <!-- FastClick -->
        <script src="<?php echo BASE_URL; ?>node_modules/fastclick/lib/fastclick.js"></script>
        <!-- AdminLTE App -->
        <script src="<?php echo BASE_URL; ?>dist/js/adminlte.min.js"></script>
        <!-- SweetAlert2 -->
        <script src="<?php echo BASE_URL; ?>node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
        <!-- Custom JS -->
        <script src="<?php echo BASE_URL; ?>dist/js/main.js"></script>
        

        <style>
        .swal2-container {
            padding-right: 0 !important;
        }
        body.swal2-shown {
            padding-right: 0 !important;
        }
        body {
            padding-right: 0 !important;
        }
        </style>
        <?php
        return ob_get_clean();
    }

    public function renderForms() {
        ob_start();
        ?>
        <section class="election-message">
                    <div class="election-message-box">
                        <h2>ELECTION PERIOD ENDED</h2>
                        <p>The voting system is currently closed as the election period for <?php echo htmlspecialchars($electionName); ?> has ended. Stay tuned for future announcements, BTECHenyos!</p>
                    </div>
                    <a href="https://www.facebook.com/BTECHDPLB/" target="_blank">Have some questions?</a>
        </section>
        <?php
        return ob_get_clean();
    }
}
