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
    private $current_page;

    private function __construct() {
        $this->session = CustomSessionHandler::getInstance();
        $this->admin = Admin::getInstance();
        $this->adminData = $this->admin->getAdminData();
        $this->elections = Elections::getInstance();
        
        // Get page from URL parameter, fallback to index if not set
        $page = isset($_GET['page']) ? $_GET['page'] : 'index';
        $this->current_page = $page . '.php';
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
        // Use the class property instead of local variable
        ?>
        <aside class="main-sidebar">
            <!-- sidebar: style can be found in sidebar.less -->
            <section class="sidebar">
                <!-- Sidebar user panel -->
                <!-- sidebar menu: : style can be found in sidebar.less -->
                <ul class="sidebar-menu" data-widget="tree">
                    <!-- Dashboard -->
                    <li class="<?php echo $this->current_page == 'home.php' ? 'active' : ''; ?>">
                        <a href="home"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a>
                    </li>

                    <!-- Election Management -->
                    <li class="header">ELECTION MANAGEMENT</li>
                    <li class="<?php echo $this->current_page == 'positions.php' ? 'active' : ''; ?>">
                        <a href="positions"><i class="fa fa-tasks"></i> <span>Positions</span></a>
                    </li>
                    <li class="<?php echo $this->current_page == 'candidates.php' ? 'active' : ''; ?>">
                        <a href="candidates"><i class="fa fa-user-tie"></i> <span>Candidates</span></a>
                    </li>
                    <li class="<?php echo $this->current_page == 'partylists.php' ? 'active' : ''; ?>">
                        <a href="partylists"><i class="fa fa-list"></i> <span>Partylists</span></a>
                    </li>
                    <li class="<?php echo $this->current_page == 'voters.php' ? 'active' : ''; ?>">
                        <a href="voters"><i class="fa fa-users"></i> <span>Voters</span></a>
                    </li>
                    <li class="<?php echo $this->current_page == 'courses.php' ? 'active' : ''; ?>">
                        <a href="courses"><i class="fa fa-graduation-cap"></i> <span>Courses</span></a>
                    </li>

                    <!-- Reports & Analytics -->
                    <li class="header">REPORTS & ANALYTICS</li>
                    <li class="<?php echo $this->current_page == 'votes.php' ? 'active' : ''; ?>">
                        <a href="votes"><i class="fa fa-chart-bar"></i> <span>Votes</span></a>
                    </li>
                    <li class="<?php echo $this->current_page == 'history.php' ? 'active' : ''; ?>">
                        <a href="history"><i class="fa fa-history"></i> <span>Election History</span></a>
                    </li>

                    <?php if ($this->admin->isElectoralHead()): ?>
                    <!-- System Administration -->
                    <li class="header">ADMINISTRATION</li>
                    <li class="<?php echo $this->current_page == 'officers.php' ? 'active' : ''; ?>">
                        <a href="officers"><i class="fa fa-user-shield"></i> <span>Officers</span></a>
                    </li>
                    <li class="<?php echo $this->current_page == 'log_admin.php' ? 'active' : ''; ?>">
                        <a href="log_admin"><i class="fa fa-user-shield"></i> <span>Admin Logs</span></a>
                    </li>

                    <!-- System Settings -->
                    <li class="header">SETTINGS</li>
                    <li class="<?php echo $this->current_page == 'configure.php' ? 'active' : ''; ?>">
                        <a href="configure"><i class="fa fa-cogs"></i> <span>Configure Election</span></a>
                    </li>
                    <li class="<?php echo $this->current_page == 'ballot.php' ? 'active' : ''; ?>">
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
            <title>E-Halal BTECHenyo | Admin Dashboard</title>
            
            <!-- Core CSS -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>node_modules/bootstrap/dist/css/bootstrap.min.css">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>plugins/font-awesome/css/all.min.css" />
            
            <!-- Third Party CSS -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>node_modules/datatables.net-bs/css/dataTables.bootstrap.css">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>node_modules/iCheck/skins/all.css">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>node_modules/bootstrap-daterangepicker/daterangepicker.css">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>node_modules/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>node_modules/bootstrap-timepicker/css/bootstrap-timepicker.min.css">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>node_modules/sweetalert2/dist/sweetalert2.min.css">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>node_modules/@fancyapps/ui/dist/fancybox/fancybox.css" />
            
            <!-- AdminLTE Theme -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/AdminLTE.min.css">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/skins/_all-skins.min.css">
            
            <!-- Custom Styles -->
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/custom.css">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/login.css">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>administrator/assets/css/styles.css">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>administrator/assets/css/modals.css">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/customFonts.css">
            <?php if ($this->current_page == 'setup.php'): ?>  
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>administrator/assets/css/setup.css">
            <?php elseif ($this->current_page == 'completed.php'): ?>
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>administrator/assets/css/completed.css?v=2">
            <?php endif; ?>
            <!-- Favicon -->
            <link rel="icon" type="image/x-icon" href="<?php echo BASE_URL; ?>images/icon.ico">
            
            <!-- Core JavaScript -->
            <script src="<?php echo BASE_URL; ?>node_modules/jquery/dist/jquery.min.js"></script>
            <script src="<?php echo BASE_URL; ?>node_modules/@popperjs/core/dist/umd/popper.min.js"></script>
            <script src="<?php echo BASE_URL; ?>node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
            
            <!-- Third Party JavaScript -->
            <script src="<?php echo BASE_URL; ?>node_modules/datatables.net/js/jquery.dataTables.js"></script>
            <script src="<?php echo BASE_URL; ?>node_modules/datatables.net-bs/js/dataTables.bootstrap.js"></script>
            <script src="<?php echo BASE_URL; ?>node_modules/jquery-slimscroll/jquery.slimscroll.min.js"></script>
            <script src="<?php echo BASE_URL; ?>node_modules/fastclick/lib/fastclick.js"></script>
            <script src="<?php echo BASE_URL; ?>node_modules/chart.js/dist/chart.umd.js"></script>
            <script src="<?php echo BASE_URL; ?>node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>

            <!-- jQuery UI Tabs dependencies -->
            <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">
            <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
            <!-- SweetAlert2 for better alerts and progress indication -->
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <!-- DataTables CSS & JS -->
            <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
            <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
            <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.min.js"></script>
        </head>		
        <?php
        return ob_get_clean();
    }

    public function renderFooter() {
        ob_start();
        ?>
        <footer class="main-footer">
            <div class="pull-right hidden-xs">
                <b><a>See Guidelines</a></b>
            </div>
            <strong>Copyright &copy; 2024 <a href="https://btech.edu.ph">Dalubhsaang Politekniko ng Lungsod ng Baliwag</a></strong>
        </footer>
        
        <!-- Back to Top Button -->
        <a href="#" class="back-to-top"><i class="fa fa-arrow-up"></i></a>

        <!-- AdminLTE App -->
        <script src="<?php echo BASE_URL; ?>dist/js/adminlte.min.js"></script>

        <?php include __DIR__ . '/../modals/admin_modal.php'; ?>

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