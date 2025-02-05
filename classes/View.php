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
                    <a href="#" class="navbar-brand" style="padding: 0;">
                    <img src="images/h-logo.jpg" alt="Voting System Logo" style="height: 50px;">
                    </a>
                </div>                
                <!-- Navbar Right Menu -->
                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                    <li class="user user-menu">
                        <a href="#">
                        <span>Welcome, </span>
                        <span><?php echo $voter['firstname']."!";?></span>
                        </a>
                    </li>
                    <li><a href="logout.php"><i class="fa fa-sign-out"></i> Sign Out</a></li>  
                    </ul>
                </div>
                </div>

            </nav>
        </header>
        <style>
            /* navbar responsive */
        .navbar-static-top {
            height: 50px;
        }
        .container[name="navbar"] {
            display: flex;
            justify-content: space-between;
            flex-grow: 1;
            width: 100%;
            padding: 10 50px;
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
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>node_modules/font-awesome/css/regular.min.css"/>
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/AdminLTE.css">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/skins/_all-skins.min.css">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/custom.css">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/login.css">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/ballots.css">
            <link rel="icon" type="image/x-icon" href="<?php echo BASE_URL; ?>images/icon.ico">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/customFonts.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css" />
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>node_modules\sweetalert2\dist\sweetalert2.min.css" />
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
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
        <!-- iCheck 1.0.1 -->
        <script src="<?php echo BASE_URL; ?>plugins/iCheck/icheck.min.js"></script>
        <!-- DataTables -->
        <script src="<?php echo BASE_URL; ?>node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
        <script src="<?php echo BASE_URL; ?>node_modules/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
        <!-- SlimScroll -->
        <script src="<?php echo BASE_URL; ?>node_modules/jquery-slimscroll/jquery.slimscroll.min.js"></script>
        <!-- FastClick -->
        <script src="<?php echo BASE_URL; ?>node_modules/fastclick/lib/fastclick.js"></script>
        <!-- AdminLTE App -->
        <script src="<?php echo BASE_URL; ?>dist/js/adminlte.min.js"></script>
        <!-- Font-awesome -->
        <script src="<?php echo BASE_URL;?>node_modules/font-awesome/js/regular.min.js"></script>
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
        .swal2-confirm,
        .swal2-cancel {
            padding: 10px 45px !important;
            font-size: 13px !important;
            margin: 0 10px !important;
        }
        .swal2-actions {
            margin-top: 20px !important;
        }
        </style>
        
        <script>
        $(function(){
            // Initialize iCheck
            $('.content').iCheck({
                checkboxClass: 'icheckbox_flat-green',
                radioClass: 'iradio_flat-green'
            });

            // Reset button handler
            $(document).on('click', '.reset', function(e){
                e.preventDefault();
                var positionId = $(this).data('position');
                $('input[name^="votes[' + positionId + ']"]').iCheck('uncheck');
            });

            // Platform button handler
            $(document).on('click', '.platform', function(e){
                e.preventDefault();
                $('#platform').modal('show');
                var platform = $(this).data('platform');
                var fullname = $(this).data('fullname');
                $('.candidate').html(fullname);
                $('#plat_view').html(platform);
            });

            // Preview button handler
            $(document).on('click', '#preview', function(e) {
                e.preventDefault();
                
                // Get all form data
                var formData = $('#ballotForm').serialize();
                
                // Show loading state
                Swal.fire({
                    title: 'Loading Preview...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Send AJAX request
                $.ajax({
                    url: 'preview.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        
                        if (response.error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                html: response.message.join('<br>'),
                                customClass: {
                                    container: 'my-swal'
                                }
                            });
                        } else {
                            $('#preview_body').html(response.list);
                            $('#preview_modal').modal('show');
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        console.error('Preview Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while generating the preview.',
                            customClass: {
                                container: 'my-swal'
                            }
                        });
                    }
                });
            });

            // Form submission handler
            $('#ballotForm').on('submit', function(e) {
                e.preventDefault();
                
                // Check if any votes are selected
                var hasVotes = false;
                $('input[name^="votes["]').each(function() {
                    if ($(this).is(':checked')) {
                        hasVotes = true;
                        return false; // break the loop
                    }
                });
                
                if (!hasVotes) {
                    Swal.fire({
                        icon: 'error',
                        title: 'No Votes Cast',
                        text: 'Please select at least one candidate before submitting.',
                        customClass: {
                            container: 'my-swal'
                        }
                    });
                    return false;
                }

                var form = this;

                // Confirm submission
                Swal.fire({
                    title: 'Submit Votes?',
                    text: 'Are you sure you want to submit your votes? This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#239746',
                    cancelButtonColor: '#CF3C32',
                    confirmButtonText: 'Confirm',
                    showLoaderOnConfirm: true,
                    allowOutsideClick: () => !Swal.isLoading(),
                    customClass: {
                        container: 'my-swal'
                    },
                    reverseButtons: true

                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        Swal.fire({
                            title: 'Submitting your votes...',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            willOpen: () => {
                                Swal.showLoading();
                                // Actually submit the form
                                form.submit();
                            }
                        });
                    }
                });
            });

            // Check for success status in URL
            var urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('status') === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Your ballot has been successfully submitted.',
                    customClass: {
                        container: 'my-swal'
                    }
                });
                // Clean up the URL
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }
}