<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/CustomSessionHandler.php';
require_once __DIR__ . '/../../classes/Election.php';
require_once 'Admin.php';
require_once 'Position.php';
require_once 'Candidate.php';
require_once 'Vote.php';

class View {
    private $admin;
    private $adminData;
    private $session;
    private static $instance = null;

    private function __construct() {
        $this->session = CustomSessionHandler::getInstance();
        $this->admin = Admin::getInstance();
        $this->adminData = $this->admin->getAdminData();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function renderNavbar() {
        ob_start();
        $admin = Admin::getInstance();
        $admin_data = $admin->getAdminData();
        ?>
        <header class="main-header">
            <!-- Logo -->
            <span class="logo">
                <!-- mini logo for sidebar mini 50x50 pixels -->
                <span class="logo-mini"><img src="<?php echo BASE_URL; ?>images/Emblem.jpg" alt="E-Halal BTECHenyo Logo"></span>
                <!-- logo for regular state and mobile devices -->
            </span>
            <!-- Header Navbar: style can be found in header.less -->
            <nav class="navbar navbar-static-top">
                <!-- Sidebar toggle button-->
                <?php 
                $current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                $urls = [
                    '/e-halal/administrator/home',
                    '/e-halal/administrator/votes',
                    '/e-halal/administrator/voters',
                    '/e-halal/administrator/positions',
                    '/e-halal/administrator/candidates',
                    '/e-halal/administrator/ballot',
                    '/e-halal/administrator/configure',
                    '/e-halal/administrator/history',
                    '/e-halal/administrator/log',
                    '/e-halal/administrator/officers'
                ];
                if(in_array($current_path, $urls)) :?>
                <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                    <span class="sr-only">Toggle navigation</span>
                </a>
                <?php endif; ?>

                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        <!-- User Account: style can be found in dropdown.less -->
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <img src="<?php echo (!empty($admin_data['photo'])) ? BASE_URL.'images/'.$admin_data['photo'] : BASE_URL.'images/profile.jpg'; ?>" class="user-image" alt="User Image">
                                <span class="hidden-xs"><?php echo $admin_data['firstname'].' '.$admin_data['lastname']; ?></span>
                            </a>
                            <ul class="dropdown-menu">
                                <!-- User image -->
                                <li class="user-header">
                                    <img src="<?php echo (!empty($admin_data['photo'])) ? BASE_URL.'images/'.$admin_data['photo'] : BASE_URL.'images/profile.jpg'; ?>" class="img-circle" alt="User Image">
                                    <p>
                                        <?php echo $admin_data['firstname'].' '.$admin_data['lastname']; ?>
                                        <small>Member since <?php echo date('M. Y', strtotime($admin_data['created_on'])); ?></small>
                                    </p>
                                </li>
                                <!-- Menu Footer-->
                                <li class="user-footer">
                                    <div class="pull-left">
                                        <a href="#profile" data-toggle="modal" class="btn btn-default btn-flat" id="admin_profile">Update</a>
                                    </div>
                                    <div class="pull-right">
                                        <a href="?action=logout" class="btn btn-default btn-flat">Sign out</a>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>
        <?php
        return ob_get_clean();
    }

    public function renderMenubar() {
        ob_start();
        ?>
        <aside class="main-sidebar">
            <!-- sidebar: style can be found in sidebar.less -->
            <section class="sidebar">
                <!-- Sidebar user panel -->
                <!-- sidebar menu: : style can be found in sidebar.less -->
                <ul class="sidebar-menu" data-widget="tree">
                    <li class="header">REPORTS</li>
                    <li class=""><a href="home"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
                    <li class=""><a href="votes"><i class="fa fa-check-circle"></i> <span>Votes</span></a></li>
                    <li class="header">MANAGE</li>
                    <li class=""><a href="voters"><i class="fa fa-users"></i> <span>Voters</span></a></li>
                    <li class=""><a href="positions"><i class="fa fa-list-alt"></i> <span>Positions</span></a></li>
                    <li class=""><a href="candidates"><i class="fa fa-user"></i> <span>Candidates</span></a></li>
                    <li class="header">SETTINGS</li>
                    <li class=""><a href="ballot"><i class="fa fa-file-text"></i> <span>Ballot Position</span></a></li>
                    <li class=""><a href="configure"><i class="fa fa-sliders"></i> <span>Configure Election</span></a></li>
                    <li class=""><a href="history"><i class="fa fa-clock-rotate-left"></i> <span>Election History</span></a></li>
                    <li class="header">ADMIN ACTIONS</li>
                    <li class=""><a href="log"><i class="fa fa-file"></i> <span>View Logs</span></a></li>
                    <li class=""><a href="officers"><i class="fa fa-wrench"></i> <span>Manage Officers</span></a></li>
                </ul>
            </section>
            <!-- /.sidebar -->
        </aside>
        <?php
        return ob_get_clean();
    }

    public function renderHeader() {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>E-Halal BTECHenyo Admin Login</title>
            <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
            <!-- Bootstrap -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>node_modules/bootstrap/dist/css/bootstrap.min.css">
            <!-- Font Awesome -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
            <!-- AdminLTE -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/AdminLTE.min.css">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/skins/_all-skins.min.css">

            <!-- Custom Styles -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/custom.css">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/login.css">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/ballots.css">
            <!-- DataTables -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>node_modules/datatables.net-bs/css/dataTables.bootstrap.min.css">
            <!-- iCheck -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>plugins/iCheck/all.css">
            <!-- Date Range Picker -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>node_modules/bootstrap-daterangepicker/daterangepicker.css">
            <!-- Date Picker -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>node_modules/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">
            <!-- Time Picker -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>plugins/timepicker/bootstrap-timepicker.min.css">
            <!-- SweetAlert2 -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>node_modules/sweetalert2/dist/sweetalert2.min.css">
            <!-- Fancybox -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css">
            <!-- Custom Fonts -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/customFonts.css">
            <!-- Favicon -->
            <link rel="icon" type="image/x-icon" href="<?php echo BASE_URL; ?>images/icon.ico">

            <!-- jQuery -->
            <script src="<?php echo BASE_URL; ?>node_modules/jquery/dist/jquery.min.js"></script>
            <!-- Popper.js -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
            <!-- Bootstrap -->
            <script src="<?php echo BASE_URL; ?>node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
            <!-- DataTables -->
            <script src="<?php echo BASE_URL; ?>node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
            <script src="<?php echo BASE_URL; ?>node_modules/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
            <!-- SlimScroll -->
            <script src="<?php echo BASE_URL; ?>administrator/plugins/slimscroll/jquery.slimscroll.min.js"></script>
            <!-- FastClick -->
            <script src="<?php echo BASE_URL; ?>node_modules/fastclick/lib/fastclick.js"></script>
            <!-- Chart.js -->
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        </head>		
        <?php
        return ob_get_clean();
    }

    public function renderFooter() {
        ob_start();
        ?>
        <footer class="main-footer">
            <div class="pull-right hidden-xs">
                <b><a>Term of Use</a></b>
            </div>
            <strong>Copyright &copy; 2024 <a href="https://btech.edu.ph">Dalubhsaang Politekniko ng Lungsod ng Baliwag</a></strong>
        </footer>
        
        <!-- Back to Top Button -->
        <a href="#" class="back-to-top"><i class="fa fa-arrow-up"></i></a>

        <!-- AdminLTE App -->
        <script src="<?php echo BASE_URL; ?>dist/js/adminlte.min.js"></script>

        <script>
        $(document).ready(function() {
            // Initialize AdminLTE components
            if (typeof $.AdminLTE !== 'undefined') {
                if (typeof $.AdminLTE.layout !== 'undefined') {
                    $.AdminLTE.layout.fix();
                }
                if (typeof $.AdminLTE.pushMenu !== 'undefined') {
                    $.AdminLTE.pushMenu.activate("[data-toggle='push-menu']");
                }
            }

            // Back to top button
            $(window).scroll(function() {
                if ($(this).scrollTop() > 100) {
                    $('.back-to-top').fadeIn();
                } else {
                    $('.back-to-top').fadeOut();
                }
            });
            
            $('.back-to-top').click(function() {
                $('html, body').animate({scrollTop: 0}, 800);
                return false;
            });

            // Initialize dropdown toggle
            $('.dropdown-toggle').dropdown();
        });
        </script>
        <?php
        return ob_get_clean();
    }

    public function renderScripts() {
        ob_start();
        ?>
        <!-- SweetAlert2 -->
        <script src="<?php echo BASE_URL; ?>node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
        
        <!-- AdminLTE App -->
        <script src="<?php echo BASE_URL; ?>dist/js/adminlte.min.js"></script>

        <!-- Admin Profile Modal -->
        <div class="modal fade" id="profile">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title"><b>Admin Profile</b></h4>
                    </div>
                    <div class="modal-body">
                        <form class="form-horizontal" method="POST" action="profile_update.php?return=<?php echo basename($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="username" class="col-sm-3 control-label">Username</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="username" name="username" value="<?php echo $this->adminData['username']; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="password" class="col-sm-3 control-label">Password</label>
                                <div class="col-sm-9"> 
                                    <input type="password" class="form-control" id="password" name="password" value="<?php echo $this->adminData['password']; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="firstname" class="col-sm-3 control-label">Firstname</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="firstname" name="firstname" value="<?php echo $this->adminData['firstname']; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="lastname" class="col-sm-3 control-label">Lastname</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="lastname" name="lastname" value="<?php echo $this->adminData['lastname']; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="photo" class="col-sm-3 control-label">Photo:</label>
                                <div class="col-sm-9">
                                    <input type="file" id="photo" name="photo">
                                </div>
                            </div>
                            <hr>
                            <div class="form-group">
                                <label for="curr_password" class="col-sm-3 control-label">Current Password:</label>
                                <div class="col-sm-9">
                                    <input type="password" class="form-control" id="curr_password" name="curr_password" placeholder="input current password to save changes" required>
                                </div>
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
                        <button type="submit" class="btn btn-success btn-flat" name="save"><i class="fa fa-check-square-o"></i> Save</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
        $(function() {
            // Handle admin profile modal
            $(document).on('click', '#admin_profile', function(e) {
                e.preventDefault();
                $('#profile').modal('show');
            });
        });
        </script>
                <?php
                return ob_get_clean();
            }

    public function getChartData() {
        $position = Position::getInstance();
        $candidate = Candidate::getInstance();
        $vote = Vote::getInstance();

        $positions = $position->getAllPositions();
        $chartData = [];

        foreach ($positions as $pos) {
            $candidates = $candidate->getCandidatesByPosition($pos['id']);
            
            if (!empty($candidates)) {
                $candidateNames = [];
                $voteData = [];
                $backgroundColor = [];
                $datasets = [];

                foreach ($candidates as $cand) {
                    $candidateNames[] = $cand['firstname'] . ' ' . $cand['lastname'];
                    $votes = $vote->getCandidateVotes($cand['id']);
                    $voteData[] = $votes;
                    
                    // Generate a consistent color based on candidate ID
                    $hue = ($cand['id'] * 137.508) % 360;
                    $color = "hsla($hue, 70%, 50%, 0.8)";
                    $backgroundColor[] = $color;
                    
                    // Dataset for line charts
                    $datasets[] = [
                        'label' => $cand['firstname'] . ' ' . $cand['lastname'],
                        'data' => [0, $votes], // [initial, current]
                        'borderColor' => $color,
                        'backgroundColor' => str_replace('0.8', '0.1', $color),
                        'fill' => true,
                        'tension' => 0.4,
                        'pointRadius' => 4,
                        'pointHoverRadius' => 6
                    ];
                }

                $chartData[] = [
                    'position' => $pos['description'],
                    'candidates' => $candidateNames,
                    'votes' => $voteData,
                    'backgroundColor' => $backgroundColor,
                    'datasets' => $datasets
                ];
            }
        }

        return $chartData;
    }

    public function renderPositionCharts() {
        return ''; // Charts are now rendered client-side
    }
}