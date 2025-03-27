<?php
require_once __DIR__ . '/../classes/View.php';
require_once __DIR__ . '/../classes/Admin.php';
require_once __DIR__ . '/../classes/Elections.php';
require_once __DIR__ . '/../classes/Logger.php';

// Set timezone to Philippine time
date_default_timezone_set('Asia/Manila');

// Initialize classes
$view = View::getInstance();
$admin = Admin::getInstance();
$election = Elections::getInstance();
$logger = AdminLogger::getInstance();

// Check if admin is logged in and is superadmin
if (!$admin->isLoggedIn() || !$admin->isSuperAdmin()) {
    $_SESSION['error'] = 'Access Denied. This page is restricted to superadmins only.';
    header('Location: home');
    exit();
}

// Get current election status
$current_election = $election->getCurrentElection();
$end_time = isset($current_election['end_time']) ? new DateTime($current_election['end_time'], new DateTimeZone('Asia/Manila')) : null;
$now = new DateTime('now', new DateTimeZone('Asia/Manila'));
$is_past_end_time = $end_time && $end_time <= $now;

// Redirect to setup if in setup status
if (isset($current_election['status']) && $current_election['status'] === 'setup') {
    header('Location: setup.php');
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
                            <form action="<?php echo BASE_URL; ?>administrator/pages/includes/controllers/election_configure.php" method="POST">
                                <div class="form-group">
                                    <label>Election Name</label>
                                    <input type="text" class="form-control" id="election_name" name="election_name" 
                                           value="<?php echo isset($current_election['election_name']) ? htmlspecialchars($current_election['election_name']) : ''; ?>" 
                                           <?php echo (isset($current_election['status']) && (in_array($current_election['status'], ['active', 'completed']) || $is_past_end_time)) ? 'readonly' : ''; ?>
                                           required>
                                </div>
                                <div class="form-group">
                                    <label>End Time & Date</label>
                                    <?php 
                                    $end_time_value = isset($current_election['end_time']) ? 
                                        (new DateTime($current_election['end_time'], new DateTimeZone('Asia/Manila')))->format('Y-m-d\TH:i') : '';
                                    
                                    // Only allow end time modification in pending status
                                    $can_edit_end_time = isset($current_election['status']) && 
                                                       $current_election['status'] === 'pending';
                                    ?>
                                    <input type="datetime-local" class="form-control" id="end_time" name="end_time" 
                                           value="<?php echo $end_time_value; ?>" 
                                           <?php echo !$can_edit_end_time ? 'readonly' : ''; ?>
                                           required>
                                    <?php if (!$can_edit_end_time): ?>
                                        <small class="text-muted">End time cannot be modified after pending status.</small>
                                    <?php endif; ?>
                                    <small class="text-muted d-block">All times are in Philippine Time (UTC+8:00)</small>
                                </div>
                                <div class="form-group">
                                    <label>Election Status</label>
                                    <?php
                                    // Determine available status options based on current state and end time
                                    $availableStatuses = [];
                                    if (isset($current_election['status'])) {
                                        $currentStatus = $current_election['status'];
                                        switch ($currentStatus) {
                                            case 'pending':
                                                // Can activate or go back to setup
                                                $availableStatuses = ['pending', 'active'];
                                                break;
                                            case 'active':
                                                if ($is_past_end_time) {
                                                    // If past end time, can only complete
                                                    $availableStatuses = ['active', 'completed'];
                                                } else {
                                                    // Can pause or complete
                                                    $availableStatuses = ['active', 'paused', 'completed'];
                                                }
                                                break;
                                            case 'paused':
                                                if ($is_past_end_time) {
                                                    // If past end time, can only complete
                                                    $availableStatuses = ['paused', 'completed'];
                                                } else {
                                                    // Can resume, complete, or go back to pending
                                                    $availableStatuses = ['paused', 'active', 'completed'];
                                                }
                                                break;
                                            case 'completed':
                                                // Can only go to setup to start fresh
                                                $availableStatuses = ['completed', 'setup'];
                                                break;
                                        }
                                    } else {
                                        // New election
                                        $availableStatuses = ['pending'];
                                    }
                                    ?>
                                    <select class="form-control" id="status" name="status" required>
                                        <?php 
                                        foreach ($availableStatuses as $status): 
                                            // Skip 'completed' as it will be handled by a button
                                            if ($status === 'completed') continue;
                                        ?>
                                            <option value="<?php echo $status; ?>" 
                                                    <?php echo (isset($current_election['status']) && $current_election['status'] == $status) ? 'selected' : ''; ?>>
                                                <?php 
                                                $statusLabels = [
                                                    'pending' => 'Pending (Ready for Activation)',
                                                    'active' => 'Active (Voting Ongoing)',
                                                    'paused' => 'Paused (Maintenance)',
                                                    'completed' => 'Completed (Election Ended)'
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
                                                echo $current_election['status'] == 'active' ? 'success' : 
                                                    ($current_election['status'] == 'paused' ? 'warning' : 
                                                    ($current_election['status'] == 'pending' ? 'info' : 'danger')); 
                                            ?>">
                                                <?php echo ucfirst($current_election['status']); ?>
                                            </span>
                                        </p>
                                        <p><strong>End Time:</strong> 
                                            <?php 
                                            if ($end_time) {
                                                echo $end_time->format('F j, Y g:i A') . ' (Philippine Time)';
                                            } else {
                                                echo 'Not set';
                                            }
                                            ?>
                                        </p>
                                        
                                        <?php if ($is_past_end_time): ?>
                                            <div class="alert alert-danger">
                                                <i class="fa fa-exclamation-triangle"></i> 
                                                <strong>Warning: Election end time has been reached!</strong><br>
                                                <?php if ($current_election['status'] === 'active'): ?>
                                                    The election is still active but has passed its end time; voting is now disabled. You must end the election - it cannot be reactivated.
                                                <?php elseif ($current_election['status'] === 'paused'): ?>
                                                    The election is still paused but has passed its end time; voting is now disabled. You must end the election - it cannot be reactivated.
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <?php
                                            // Regular status messages when not past end time
                                            switch($current_election['status']) {
                                                case 'pending':
                                                    echo '<div class="alert alert-info">
                                                            <i class="fa fa-info-circle"></i> Election is ready for activation. Review all settings before starting.
                                                          </div>';
                                                    break;
                                                case 'active':
                                                    echo '<div class="alert alert-success">
                                                            <i class="fa fa-check-circle"></i> Election is active. Voting is enabled.
                                                          </div>';
                                                    break;
                                                case 'paused':
                                                    echo '<div class="alert alert-warning">
                                                            <i class="fa fa-pause-circle"></i> Election is paused. Voting is temporarily disabled.
                                                          </div>';
                                                    break;
                                                case 'completed':
                                                    echo '<div class="alert alert-danger">
                                                            <i class="fa fa-stop-circle"></i> Election is completed. No further changes allowed.
                                                          </div>';
                                                    break;
                                            }
                                            ?>
                                        <?php endif; ?>

                                        <?php if ($end_time): ?>
                                            <?php
                                            $time_remaining = $end_time->getTimestamp() - $now->getTimestamp();
                                            if ($time_remaining > 0 && in_array($current_election['status'], ['active', 'paused'])):
                                                $hours = floor($time_remaining / 3600);
                                                $minutes = floor(($time_remaining % 3600) / 60);
                                            ?>
                                                <div class="alert alert-info">
                                                    <i class="fa fa-clock"></i> 
                                                    <strong>Time Remaining:</strong> 
                                                    <?php echo $hours . ' hour' . ($hours != 1 ? 's' : '') . ' and ' . 
                                                              $minutes . ' minute' . ($minutes != 1 ? 's' : ''); ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <ol class="breadcrumb">
                                    <i class="fa fa-info-circle"></i> 
                                    <strong>Status Rules:</strong>
                                    <ul>
                                        <li><strong>Pending:</strong> Ready for activation. All modifications allowed.</li>
                                        <li><strong>Active:</strong> Election is running. No modifications allowed.</li>
                                        <li><strong>Paused:</strong> Temporary halt. Limited modifications allowed. No voting possible.</li>
                                        <li><strong>Completed:</strong> Election ended. No modifications allowed. Results available.</li>
                                        <li>End time must be set before activating the election.</li>
                                        <li>After end time is reached, election must be completed.</li>
                                        <li>To make major changes, pause the election or return to setup status.</li>
                                    </ul>
                                </ol>

                                <div class="form-group">
                                    <?php if (!$is_past_end_time): ?>
                                        <button type="submit" class="btn btn-success" name="save">
                                            <i class="fa fa-save"></i> Save Changes
                                        </button>
                                    <?php endif; ?>
                                    <?php if (in_array('completed', $availableStatuses)): ?>
                                        <button type="button" class="btn btn-danger" id="completeElectionBtn">
                                            <i class="fa fa-stop-circle"></i> End Election
                                        </button>
                                    <?php endif; ?>
                                </div>
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
    var endInput = document.getElementById('end_time');
    var statusSelect = document.getElementById('status');
    var currentStatus = statusSelect.value;
    
    // Handle Complete Election button click
    $('#completeElectionBtn').click(function() {
        Swal.fire({
            title: 'Complete Election?',
            text: "This will end the election permanently. This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, complete election',
            cancelButtonText: 'No, cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Set the status to completed and submit the form
                $('#status').val('completed');
                $('form').submit();
            }
        });
    });
    
    // Handle status changes
    $('#status').change(function() {
        var newStatus = $(this).val();
        // Convert input time to Philippine time for comparison
        var endTimeStr = endInput.value;
        if (endTimeStr) {
            var endTime = new Date(endTimeStr);
            // Add offset for Philippine time if needed
            var now = new Date();
            
            // Check if end time has passed
            if (endTime <= now) {
                if (newStatus !== 'completed') {
                    alert('Election end time has passed. The election must be completed.');
                    $(this).val(currentStatus);
                    return false;
                }
            }
        }
        
        // Require end date when moving to active status
        if (newStatus === 'active') {
            if (!endInput.value) {
                alert('End time must be set before activating the election');
                $(this).val(currentStatus);
                return false;
            }
        }
    });
    
    // Validate form before submission
    $('form').submit(function(e) {
        var status = $('#status').val();
        var formValid = true;
        var errorMessage = [];
        
        // Convert input time to Philippine time for comparison
        var endTimeStr = endInput.value;
        if (endTimeStr) {
            var endTime = new Date(endTimeStr);
            var now = new Date();
            
            // Validate required fields
            if (!$('#election_name').val()) {
                errorMessage.push('Election name is required');
                formValid = false;
            }
            
            // Validate end time
            if (!endInput.value) {
                errorMessage.push('End time is required');
                formValid = false;
            } else if (endTime <= now && status !== 'completed') {
                errorMessage.push('Election end time has passed. The election must be completed.');
                formValid = false;
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