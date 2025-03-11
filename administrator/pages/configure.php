<?php
require_once __DIR__ . '/../classes/View.php';
require_once __DIR__ . '/../classes/Admin.php';
require_once __DIR__ . '/../classes/Elections.php';
require_once __DIR__ . '/../classes/Logger.php';

// Initialize classes
$view = View::getInstance();
$admin = Admin::getInstance();
$election = Elections::getInstance();
$logger = AdminLogger::getInstance();

// Check if admin is logged in
if (!$admin->isLoggedIn()) {
    header('Location: ../administrator');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>E-Halal Voting System | Configure Election</title>
    <?php echo $view->renderHeader(); ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
    <?php 
    echo $view->renderNavbar();
    echo $view->renderMenubar();
    ?>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <section class="content-header">
            <h1>
                Configure Election
                <small>Set up election parameters</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Settings</a></li>
                <li class="active">Configure Election</li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <?php
            if(isset($_SESSION['error'])){
                echo "
                    <div class='alert alert-danger alert-dismissible'>
                        <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                        <h4><i class='icon fa fa-warning'></i> Error!</h4>
                        ".$_SESSION['error']."
                    </div>
                ";
                unset($_SESSION['error']);
            }
            if(isset($_SESSION['success'])){
                echo "
                    <div class='alert alert-success alert-dismissible'>
                        <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                        <h4><i class='icon fa fa-check'></i> Success!</h4>
                        ".$_SESSION['success']."
                    </div>
                ";
                unset($_SESSION['success']);
            }
            ?>

            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-body">
                            <?php
                            // Get current election status
                            $current_election = $election->getCurrentElection();
                            ?>
                            <form action="includes/controllers/election_configure.php" method="POST">
                                <div class="form-group">
                                    <label>Election Name</label>
                                    <input type="text" class="form-control" id="election_name" name="election_name" value="<?php echo isset($current_election['election_name']) ? $current_election['election_name'] : ''; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Election End Time & Date</label>
                                    <input type="datetime-local" class="form-control" id="end_time" name="end_time" value="<?php echo isset($current_election['end_time']) ? date('Y-m-d\TH:i', strtotime($current_election['end_time'])) : ''; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Election Status</label>
                                    <select class="form-control" id="status" name="status" required>
                                        <option value="on" <?php echo (isset($current_election['status']) && $current_election['status'] == 'on') ? 'selected' : ''; ?>>On</option>
                                        <option value="paused" <?php echo (isset($current_election['status']) && $current_election['status'] == 'paused') ? 'selected' : ''; ?>>Paused</option>
                                        <option value="off" <?php echo (isset($current_election['status']) && $current_election['status'] == 'off') ? 'selected' : ''; ?>>Off</option>
                                    </select>
                                </div>
                                <ol class="breadcrumb">
                                    <li class="active"><i class="fa fa-info-circle"></i> Changing the election status to off <a> will end the entire election</a> (cannot be undone).</li>
                                </ol>
                                <button type="submit" class="btn btn-success" name="save">Save Changes</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    
    <?php echo $view->renderFooter(); ?>
</div>

<?php echo $view->renderScripts(); ?>

<script>
$(function() {
    // Set minimum date/time for start_time and end_time
    var now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    document.getElementById('start_time').min = now.toISOString().slice(0,16);
    
    // Update end_time min when start_time changes
    $('#start_time').change(function() {
        document.getElementById('end_time').min = this.value;
    });
});
</script>
</body>
</html> 