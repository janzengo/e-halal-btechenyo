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
                            <form action="<?php echo BASE_URL; ?>administrator/pages/includes/controllers/election_configure.php" method="POST">
                                <div class="form-group">
                                    <label>Election Name</label>
                                    <input type="text" class="form-control" id="election_name" name="election_name" 
                                           value="<?php echo isset($current_election['election_name']) ? htmlspecialchars($current_election['election_name']) : ''; ?>" 
                                           <?php echo (isset($current_election['status']) && in_array($current_election['status'], ['on', 'off'])) ? 'readonly' : ''; ?>
                                           required>
                                </div>
                                <div class="form-group">
                                    <label>Start Time & Date</label>
                                    <?php 
                                    $now = new DateTime();
                                    $start_time = isset($current_election['start_time']) ? 
                                        (new DateTime($current_election['start_time']))->format('Y-m-d\TH:i') : '';
                                    $end_time = isset($current_election['end_time']) ? 
                                        (new DateTime($current_election['end_time']))->format('Y-m-d\TH:i') : '';
                                    
                                    $canEditDates = isset($current_election['status']) && 
                                                   in_array($current_election['status'], ['pending', 'paused']);
                                    ?>
                                    <input type="datetime-local" class="form-control" id="start_time" name="start_time" 
                                           value="<?php echo $start_time; ?>" 
                                           <?php echo !$canEditDates ? 'readonly' : ''; ?>
                                           <?php echo ($current_election['status'] !== 'pending') ? 'required' : ''; ?>>
                                </div>
                                <div class="form-group">
                                    <label>End Time & Date</label>
                                    <input type="datetime-local" class="form-control" id="end_time" name="end_time" 
                                           value="<?php echo $end_time; ?>" 
                                           <?php echo !$canEditDates ? 'readonly' : ''; ?>
                                           <?php echo ($current_election['status'] !== 'pending') ? 'required' : ''; ?>>
                                </div>
                                <div class="form-group">
                                    <label>Election Status</label>
                                    <?php
                                    // Determine available status options based on current state
                                    $availableStatuses = [];
                                    if (isset($current_election['status'])) {
                                        $currentStatus = $current_election['status'];
                                        switch ($currentStatus) {
                                            case 'pending':
                                                // Can only turn on if dates are set
                                                $availableStatuses = ['pending'];
                                                if ($start_time && $end_time) {
                                                    $availableStatuses[] = 'on';
                                                }
                                                break;
                                            case 'on':
                                                // Can only pause or turn off
                                                $availableStatuses = ['on', 'paused', 'off'];
                                                break;
                                            case 'paused':
                                                // Can resume, turn off, or go back to pending
                                                $availableStatuses = ['paused', 'on', 'off', 'pending'];
                                                break;
                                            case 'off':
                                                // Can only go to pending to start fresh
                                                $availableStatuses = ['off', 'pending'];
                                                break;
                                        }
                                    } else {
                                        // New election
                                        $availableStatuses = ['pending'];
                                    }
                                    ?>
                                    <select class="form-control" id="status" name="status" required>
                                        <?php foreach ($availableStatuses as $status): ?>
                                            <option value="<?php echo $status; ?>" 
                                                    <?php echo (isset($current_election['status']) && $current_election['status'] == $status) ? 'selected' : ''; ?>>
                                                <?php 
                                                $statusLabels = [
                                                    'pending' => 'Pending (Setup Mode)',
                                                    'on' => 'On (Active)',
                                                    'paused' => 'Paused (Maintenance)',
                                                    'off' => 'Off (Completed)'
                                                ];
                                                echo $statusLabels[$status];
                                                ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <?php if (isset($current_election) && $current_election): ?>
                                <div class="box box-info">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">Current Election Status</h3>
                                    </div>
                                    <div class="box-body">
                                        <p><strong>Name:</strong> <?php echo htmlspecialchars($current_election['election_name']); ?></p>
                                        <p><strong>Status:</strong> 
                                            <span class="label label-<?php 
                                                echo $current_election['status'] == 'on' ? 'success' : 
                                                    ($current_election['status'] == 'paused' ? 'warning' : 
                                                    ($current_election['status'] == 'pending' ? 'info' : 'danger')); 
                                            ?>">
                                                <?php echo ucfirst($current_election['status']); ?>
                                            </span>
                                        </p>
                                        <?php if ($current_election['status'] !== 'pending'): ?>
                                            <p><strong>Start Time:</strong> <?php echo $start_time ? date('F j, Y g:i A', strtotime($current_election['start_time'])) : 'Not set'; ?></p>
                                            <p><strong>End Time:</strong> <?php echo $end_time ? date('F j, Y g:i A', strtotime($current_election['end_time'])) : 'Not set'; ?></p>
                                        <?php endif; ?>
                                        
                                        <?php
                                        // Status-specific messages
                                        switch($current_election['status']) {
                                            case 'pending':
                                                echo '<div class="alert alert-info">
                                                        <i class="fa fa-info-circle"></i> Election is in setup mode. You can modify candidates, positions, and voters.
                                                      </div>';
                                                break;
                                            case 'on':
                                                echo '<div class="alert alert-success">
                                                        <i class="fa fa-check-circle"></i> Election is active. No modifications allowed.
                                                      </div>';
                                                break;
                                            case 'paused':
                                                echo '<div class="alert alert-warning">
                                                        <i class="fa fa-pause-circle"></i> Election is paused. Limited modifications allowed.
                                                      </div>';
                                                break;
                                            case 'off':
                                                echo '<div class="alert alert-danger">
                                                        <i class="fa fa-stop-circle"></i> Election is completed. No modifications allowed.
                                                      </div>';
                                                break;
                                        }
                                        ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <ol class="breadcrumb">
                                    <i class="fa fa-info-circle"></i> 
                                    <strong>Status Rules:</strong>
                                    <ul>
                                        <li><strong>Pending:</strong> Initial setup mode. All modifications allowed. No voting possible.</li>
                                        <li><strong>On:</strong> Active election. No modifications allowed. Voting enabled.</li>
                                        <li><strong>Paused:</strong> Temporary halt. Limited modifications allowed. No voting possible.</li>
                                        <li><strong>Off:</strong> Completed election. No modifications allowed. No voting possible.</li>
                                        <li>Start and end times must be set before activating the election.</li>
                                        <li>To make major changes, pause the election or return to pending status.</li>
                                    </ul>
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
$(document).ready(function() {
    var startInput = document.getElementById('start_time');
    var endInput = document.getElementById('end_time');
    var statusSelect = document.getElementById('status');
    var currentStatus = statusSelect.value;
    
    // Handle status changes
    $('#status').change(function() {
        var newStatus = $(this).val();
        
        // Only require dates when changing from pending to another status
        if (currentStatus === 'pending' && newStatus !== 'pending') {
            if (!startInput.value || !endInput.value) {
                alert('Start and end times must be set before activating the election');
                $(this).val('pending');
                return false;
            }
        }
    });
    
    // Validate form before submission
    $('form').submit(function(e) {
        var status = $('#status').val();
        var formValid = true;
        var errorMessage = [];
        
        // Allow submission with just election name if staying in pending status
        if (status === 'pending') {
            // Only validate election name
            if (!$('#election_name').val()) {
                errorMessage.push('Election name is required');
                formValid = false;
            }
        } else {
            // Validate all fields for non-pending status
            if (!startInput.value || !endInput.value) {
                errorMessage.push('Start time and End time are required for non-pending status');
                formValid = false;
            } else {
                var start = new Date(startInput.value);
                var end = new Date(endInput.value);
                
                if (end <= start) {
                    errorMessage.push('End time must be after start time');
                    formValid = false;
                }
            }
        }
        
        if (!formValid) {
            e.preventDefault();
            alert(errorMessage.join('\n'));
            return false;
        }
    });
});
</script>
</body>
</html> 