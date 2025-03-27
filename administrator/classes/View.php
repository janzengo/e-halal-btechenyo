<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/CustomSessionHandler.php';
require_once __DIR__ . '/../../classes/Election.php';
require_once 'Admin.php';
require_once 'Position.php';
require_once 'Candidate.php';
require_once 'Vote.php';
require_once 'Elections.php';
require_once 'Partylist.php';

class View {
    private $admin;
    private $adminData;
    private $session;
    private static $instance = null;
    private $elections;

    private function __construct() {
        $this->session = CustomSessionHandler::getInstance();
        $this->admin = Admin::getInstance();
        $this->adminData = $this->admin->getAdminData();
        $this->elections = Elections::getInstance();
    }

    /**
     * Check if modifications are allowed based on election status
     * @return bool
     */
    public function canModify() {
        return !$this->elections->isModificationLocked();
    }

    /**
     * Get hidden class based on election status
     * @return string
     */
    public function getHiddenClass() {
        return $this->canModify() ? '' : 'hidden';
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
                    '/e-halal/administrator/log_admin',
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
                                <img src="<?php echo (!empty($admin->getPhoto())) ? BASE_URL.'administrator/'.$admin->getPhoto() : BASE_URL.'administrator/assets/images/profile.jpg'; ?>" class="user-image" alt="User Image">
                                <span class="hidden-xs">
                                    <?php 
                                    echo $admin->getFullName(); 
                                    echo ' (' . ucfirst($admin->getRole()) . ')';
                                    ?>
                                </span>
                            </a>
                            <ul class="dropdown-menu">
                                <!-- User image -->
                                <li class="user-header">
                                    <img src="<?php echo (!empty($admin->getPhoto())) ? BASE_URL.'administrator/'.$admin->getPhoto() : BASE_URL.'administrator/assets/images/profile.jpg'; ?>" class="img-circle" alt="User Image">
                                    <p>
                                        <?php echo $admin->getFullName(); ?>
                                        <small>Member since <?php echo date('M. Y', strtotime($admin->getAdminData()['created_on'])); ?></small>
                                    </p>
                                </li>
                                <!-- Menu Footer-->
                                <li class="user-footer">
                                    <div class="pull-left">
                                        <a href="#" class="btn btn-default btn-flat" id="admin_profile">Update</a>
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
        // Get current page from URL
        $current_page = basename($_SERVER['PHP_SELF']);
        ?>
        <aside class="main-sidebar">
            <!-- sidebar: style can be found in sidebar.less -->
            <section class="sidebar">
                <!-- Sidebar user panel -->
                <!-- sidebar menu: : style can be found in sidebar.less -->
                <ul class="sidebar-menu" data-widget="tree">
                    <!-- Dashboard -->
                    <li class="<?php echo $current_page == 'home.php' ? 'active' : ''; ?>">
                        <a href="home"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a>
                    </li>

                    <!-- Election Management -->
                    <li class="header">ELECTION MANAGEMENT</li>
                    <li class="<?php echo $current_page == 'positions.php' ? 'active' : ''; ?>">
                        <a href="positions"><i class="fa fa-tasks"></i> <span>Positions</span></a>
                    </li>
                    <li class="<?php echo $current_page == 'candidates.php' ? 'active' : ''; ?>">
                        <a href="candidates"><i class="fa fa-user-tie"></i> <span>Candidates</span></a>
                    </li>
                    <li class="<?php echo $current_page == 'partylists.php' ? 'active' : ''; ?>">
                        <a href="partylists"><i class="fa fa-list"></i> <span>Partylists</span></a>
                    </li>
                    <li class="<?php echo $current_page == 'voters.php' ? 'active' : ''; ?>">
                        <a href="voters"><i class="fa fa-users"></i> <span>Voters</span></a>
                    </li>
                    <li class="<?php echo $current_page == 'courses.php' ? 'active' : ''; ?>">
                        <a href="courses"><i class="fa fa-graduation-cap"></i> <span>Courses</span></a>
                    </li>

                    <!-- Reports & Analytics -->
                    <li class="header">REPORTS & ANALYTICS</li>
                    <li class="<?php echo $current_page == 'votes.php' ? 'active' : ''; ?>">
                        <a href="votes"><i class="fa fa-chart-bar"></i> <span>Votes</span></a>
                    </li>
                    <li class="<?php echo $current_page == 'history.php' ? 'active' : ''; ?>">
                        <a href="history"><i class="fa fa-history"></i> <span>Election History</span></a>
                    </li>

                    <?php if ($this->admin->isSuperAdmin()): ?>
                    <!-- System Administration -->
                    <li class="header">ADMINISTRATION</li>
                    <li class="<?php echo $current_page == 'officers.php' ? 'active' : ''; ?>">
                        <a href="officers"><i class="fa fa-user-shield"></i> <span>Officers</span></a>
                    </li>
                    <li class="<?php echo $current_page == 'log_admin.php' ? 'active' : ''; ?>">
                        <a href="log_admin"><i class="fa fa-user-shield"></i> <span>Admin Logs</span></a>
                    </li>

                    <!-- System Settings -->
                    <li class="header">SETTINGS</li>
                    <li class="<?php echo $current_page == 'configure.php' ? 'active' : ''; ?>">
                        <a href="configure"><i class="fa fa-cogs"></i> <span>Configure Election</span></a>
                    </li>
                    <li class="<?php echo $current_page == 'ballot.php' ? 'active' : ''; ?>">
                        <a href="ballot"><i class="fa fa-ticket-alt"></i> <span>Ballot Settings</span></a>
                    </li>
                    <?php endif; ?>
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
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <title>E-Halal Voting System | Admin Dashboard</title>
            <!-- Bootstrap -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>node_modules/bootstrap/dist/css/bootstrap.min.css">
            <!-- AdminLTE -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/AdminLTE.min.css">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/skins/_all-skins.min.css">

            <!-- Custom Styles -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/custom.css">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/login.css">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/ballots.css">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>administrator/assets/css/styles.css">
            <!-- DataTables -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>node_modules\datatables.net-bs\css\dataTables.bootstrap.css">
            <!-- iCheck -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>node_modules/iCheck/skins/all.css">
            <!-- Date Range Picker -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>node_modules/bootstrap-daterangepicker/daterangepicker.css">
            <!-- Date Picker -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>node_modules/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">
            <!-- Time Picker -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>node_modules/bootstrap-timepicker/css/bootstrap-timepicker.min.css">
            <!-- SweetAlert2 -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>node_modules/sweetalert2/dist/sweetalert2.min.css">
            <!-- Custom Fonts -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/customFonts.css">
            <!-- Favicon -->
            <link rel="icon" type="image/x-icon" href="<?php echo BASE_URL; ?>images/icon.ico">
            <!-- Custom CSS -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>administrator/assets/css/styles.css">
            <!-- Font Awesome -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>plugins/font-awesome/css/all.min.css" />
            <!-- Fancybox -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>node_modules/@fancyapps/ui/dist/fancybox/fancybox.css" />
            <!-- Modals -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>administrator/assets/css/modals.css">
            
            <!-- jQuery -->
            <script src="<?php echo BASE_URL; ?>node_modules/jquery/dist/jquery.min.js"></script>            
            <!-- Bootstrap -->
            <script src="<?php echo BASE_URL; ?>node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
            <!-- DataTables -->
            <script src="<?php echo BASE_URL; ?>node_modules/datatables.net/js/jquery.dataTables.js"></script>
            <script src="<?php echo BASE_URL; ?>node_modules/datatables.net-bs/js/dataTables.bootstrap.js"></script>
            <!-- SlimScroll -->
            <script src="<?php echo BASE_URL; ?>node_modules/jquery-slimscroll/jquery.slimscroll.min.js"></script>
            <!-- FastClick -->
            <script src="<?php echo BASE_URL; ?>node_modules/fastclick/lib/fastclick.js"></script>
            <!-- Chart.js -->
            <script src="<?php echo BASE_URL; ?>node_modules/chart.js/dist/chart.umd.js"></script>
            <!-- Popper.js -->
            <script src="<?php echo BASE_URL; ?>node_modules/@popperjs/core/dist/umd/popper.min.js"></script>
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
        
        <!-- Admin Modal -->
        <?php include __DIR__ . '/../modals/admin_modal.php'; ?>
        <?php
        return ob_get_clean();
    }

    public function getChartData() {
        $position = Position::getInstance();
        $candidate = Candidate::getInstance();
        $vote = Vote::getInstance();

        $positions = $position->getAllPositions();
        $chartData = [];

        // Debug information
        error_log("Positions found: " . count($positions));

        foreach ($positions as $pos) {
            $candidates = $candidate->getCandidatesByPosition($pos['id']);
            
            // Debug information
            error_log("Position {$pos['description']}: Found " . count($candidates) . " candidates");
            
            if (!empty($candidates)) {
                $candidateNames = [];
                $voteData = [];
                $backgroundColor = [];

                foreach ($candidates as $cand) {
                    // Build candidate name with partylist
                    $fullName = $cand['firstname'] . ' ' . $cand['lastname'];
                    if (!empty($cand['partylist_id'])) {
                        $partylist = Partylist::getInstance();
                        $partylistData = $partylist->getPartylist($cand['partylist_id']);
                        if ($partylistData) {
                            $fullName .= ' (' . $partylistData['name'] . ')';
                        }
                    }
                    $candidateNames[] = $fullName;
                    
                    // Get vote count from votes table
                    $voteCount = $vote->getCandidateVotes($cand['id']);
                    $voteData[] = intval($voteCount); // Ensure integer value
                    
                    // Debug information
                    error_log("Candidate {$fullName}: {$voteCount} votes");

                    // Generate a consistent color
                    $hue = ($cand['id'] * 137.508) % 360;
                    $backgroundColor[] = "hsla($hue, 70%, 50%, 0.8)";
                }

                // Only add to chart data if we have valid vote data
                if (!empty($candidateNames) && !empty($voteData)) {
                    $chartData[] = [
                        'position' => $pos['description'],
                        'candidates' => $candidateNames,
                        'votes' => $voteData,
                        'backgroundColor' => $backgroundColor
                    ];
                }
            }
        }

        // Debug information
        error_log("Final chart data: " . json_encode($chartData));
        return $chartData;
    }

    public function renderPositionCharts() {
        return ''; // Charts are now rendered client-side
    }

    public function isModificationAllowed() {
        $election = Elections::getInstance();
        $current_election = $election->getCurrentElection();
        
        // If no election exists or election is in setup status, allow modifications
        if (!$current_election || $current_election['status'] === 'setup') {
            return true;
        }
        
        // If election is active or completed, prevent modifications
        if (in_array($current_election['status'], ['active', 'completed'])) {
            return false;
        }
        
        // For paused status, check if end time has passed
        if ($current_election['status'] === 'paused') {
            $end_time = new DateTime($current_election['end_time'], new DateTimeZone('Asia/Manila'));
            $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
            return $end_time > $now;
        }
        
        return true;
    }

    public function getModificationMessage() {
        $election = Elections::getInstance();
        $current_election = $election->getCurrentElection();
        
        if (!$current_election) {
            return '';
        }

        $end_time = new DateTime($current_election['end_time'], new DateTimeZone('Asia/Manila'));
        $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
        $is_past_end_time = $end_time <= $now;

        switch ($current_election['status']) {
            case 'active':
                if ($is_past_end_time) {
                    return 'Modifications are not allowed. The election has passed its end time and must be completed.';
                }
                return 'Modifications are not allowed while the election is active.';
            case 'paused':
                if ($is_past_end_time) {
                    return 'Modifications are not allowed. The election has passed its end time and must be completed.';
                }
                return 'Modifications are allowed while the election is paused.';
            case 'completed':
                return 'Modifications are not allowed. The election has been completed.';
            case 'pending':
                return 'Modifications are allowed while the election is pending.';
            default:
                return 'Modifications are not allowed at this time.';
        }
    }

    public function getDisabledAttribute() {
        return $this->isModificationAllowed() ? '' : 'disabled';
    }
}