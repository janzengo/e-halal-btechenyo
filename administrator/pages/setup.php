<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../classes/Admin.php';
require_once __DIR__ . '/../classes/Elections.php';
require_once __DIR__ . '/../classes/View.php';
require_once __DIR__ . '/../classes/Logger.php';

$admin = Admin::getInstance();
$elections = Elections::getInstance();
$view = View::getInstance();
$logger = AdminLogger::getInstance();

// Access control: Only Electoral Head can access
if (!$admin->isLoggedIn() || !$admin->isHead()) {
    $_SESSION['error'] = 'Access Denied. This page is restricted to Electoral Heads only.';
    header('Location: home.php');
    exit();
}

// Check current election status
$current_status = $elections->getCurrentStatus();
if ($current_status !== 'setup') {
    $_SESSION['error'] = 'Setup page is only accessible when election status is in setup mode.';
    header('Location: configure');
    exit();
}

// Get current election details
$current = $elections->getCurrentElection();

// Check if there is at least one officer in the admin table
$officers = array_filter($admin->getAllAdmins(), function($a) {
    return isset($a['role']) && $a['role'] === 'officer';
});
$hasOfficer = count($officers) > 0 ? 'true' : 'false';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        if (empty($_POST['election_name']) || empty($_POST['end_time'])) {
            throw new Exception('Election name and end time are required.');
        }

        // Validate end time is in the future
        $end_time = new DateTime($_POST['end_time']);
        $now = new DateTime();
        if ($end_time <= $now) {
            throw new Exception('End time must be in the future.');
        }

        // Update election status
        $sql = "UPDATE election_status SET 
                election_name = ?, 
                end_time = ?, 
                status = 'pending',
                last_status_change = NOW()
                WHERE status = 'setup'";
        
        $conn = $view->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $_POST['election_name'], $_POST['end_time']);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update election status.');
        }

        // Log the action
        $logger->logAdminAction(
            $admin->getUsername(),
            $admin->getRole(),
            'Election setup completed and moved to pending status'
        );

        $_SESSION['success'] = 'Election setup completed successfully. The election is now in pending status.';
        header('Location: configure');
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = 'Error completing setup: ' . $e->getMessage();
        header('Location: setup');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">    
<head>
    <meta name="base-url" content="<?php echo BASE_URL; ?>">
    <meta name="has-officer" content="<?php echo $hasOfficer; ?>">
    <?php echo $view->renderHeader(); ?>
    <title>Election Setup - E-Halal BTECHenyo</title>
</head>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
    <?php echo $view->renderNavbar(); ?>
    <div class="content-wrapper">
        <div class="container">
            <section class="content-header">
                <h2>Election Setup</h2>
                <div class="setup-status">
                    <span class="label label-info"><i class="fa fa-cog"></i> Setup Phase</span>
                    <span class="control-number">Control #: <?php echo htmlspecialchars($current['control_number']); ?></span>
                </div>
            </section>
            
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
                
                <div class="alert alert-info setup-checklist">
                    <div class="checklist-header">
                        <i class="fa fa-tasks"></i> <strong>Setup Checklist</strong>
                    </div>
                    <style>
                        .checklist-items {
                            margin-top: 10px;
                        }
                        .checklist-item {
                            margin: 5px 0;
                            padding: 5px 0;
                        }
                        .checklist-item i {
                            width: 20px;
                            text-align: center;
                            margin-right: 5px;
                        }
                        .checklist-item.completed {
                            color: #28a745;
                        }
                        .checklist-item.completed i {
                            color: #28a745;
                        }
                    </style>
                    <div class="checklist-items">
                        <div class="checklist-item required">
                            <i class="far fa-square"></i> Set election name
                        </div>
                        <div class="checklist-item required">
                            <i class="far fa-square"></i> Set end date and time
                        </div>
                        <div class="checklist-item optional">
                            <i class="far fa-square"></i> Add election officers <small>(optional)</small>
                        </div>
                    </div>
                    <div class="checklist-note">
                        <small><i class="fa fa-info-circle"></i> Suggestion: Consider adding officers first to help manage the election setup process.</small>
                    </div>
                </div>

                <div class="alert alert-info">
                    <strong>Setup Instructions</strong> Complete the election setup by configuring general settings and adding election officers. All settings must be configured before proceeding.
                </div>

                <!-- Start of panel -->
                <div class="panel panel-default">
                    <div class="panel-body">
                        <!-- Updated tab structure with full-width tabs -->
                        <div class="custom-tabs">
                            <button class="custom-tab-btn active" data-tab="generalSettings">General Settings</button>
                            <button class="custom-tab-btn" data-tab="officersSettings">Election Officers</button>
                                </div>

                        <!-- General Settings Tab -->
                        <div class="custom-tab-content active" id="generalSettings">
                            <form method="POST" id="generalSettingsForm" action="<?php echo BASE_URL; ?>administrator/pages/includes/controllers/save_election_settings.php">
                                    <div class="form-group">
                                        <label for="election_name">Election Name</label>
                                        <input type="text" class="form-control" id="election_name" name="election_name" 
                                               value="<?php echo htmlspecialchars($current['election_name'] ?? ''); ?>" 
                                               placeholder="Example: 2025 Sangguniang Mag-aaral Elections" required>
                                    <small class="help-text">Enter a descriptive name for this election</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="end_time">Election End Time & Date</label>
                                        <input type="datetime-local" class="form-control" id="end_time" name="end_time" 
                                               value="<?php echo htmlspecialchars($current['end_time'] ?? ''); ?>" required>
                                    <small class="help-text">All times are in Philippine Time (UTC+8:00)</small>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-success btn-lg" id="saveSettingsBtn">
                                        <i class="fa fa-save"></i> Save Settings
                                        </button>
                                    </div>
                            </form>
                        </div>

                        <!-- Officers Tab -->
                        <div class="custom-tab-content" id="officersSettings">
                            <div class="form-group">
                                <label>Election Officers</label>
                                <div class="form-actions mb-5">
                                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addnew">
                                        <i class="fa fa-plus"></i> Add Election Officer
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table id="officerTable" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Username</th>
                                                <th>Name</th>
                                                <th>Gender</th>
                                                <th>Role</th>
                                                <th>Created On</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $officers = $admin->getAllAdmins();
                                            foreach($officers as $row){
                                                // Skip head admins
                                                if($row['role'] === 'head') {
                                                    continue;
                                                }
                                                
                                                $actions = "";
                                                if($row['id'] != $_SESSION['admin']){
                                                    $actions = "
                                                        <div class='btn-group'>
                                                            <button class='btn btn-sm btn-primary edit' data-id='".$row['id']."'>
                                                                <i class='fa fa-edit'></i> Edit
                                                            </button>
                                                            <button class='btn btn-sm btn-danger delete' data-id='".$row['id']."'>
                                                                <i class='fa fa-trash'></i> Delete
                                                            </button>
                                                        </div>
                                                    ";
                                                }
                                                echo "
                                                <tr>
                                                    <td>".$row['username']."</td>
                                                    <td>".$row['firstname'].' '.$row['lastname']."</td>
                                                    <td>".$row['gender']."</td>
                                                    <td>".ucfirst($row['role'])."</td>
                                                    <td>".date('M d, Y', strtotime($row['created_on']))."</td>
                                                    <td>".$actions."</td>
                                                </tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                <small class="help-text">Officers can help manage positions, candidates, and voters during the election</small>
                            </div>

                            <div class="alert alert-info mt-4">
                                <strong>Officer Responsibilities</strong>
                                <ul class="mt-2 mb-0">
                                    <li><strong>Manage Positions:</strong> Add and configure election positions</li>
                                    <li><strong>Manage Candidates:</strong> Add candidates and their details</li>
                                    <li><strong>Manage Voters:</strong> Handle voter registration and verification</li>
                                    <li><strong>View Reports:</strong> Access election statistics and reports</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                </div>
                <!-- End of panel -->

                <!-- Action buttons container -->
                <div class="actions-container">
                    <div class="actions-title">Setup Actions</div>
                    <div class="actions-btn-group">
                        <form method="POST" id="completeSetupForm" action="<?php echo BASE_URL; ?>administrator/pages/includes/controllers/complete_setup.php">
                            <button type="submit" class="btn btn-success btn-lg" id="completeSetupBtn" disabled>
                                            <i class="fa fa-check-circle"></i> Complete Setup
                                        </button>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </div>
    
    <?php 
    include 'includes/modals/officers_modal.php';
    echo $view->renderFooter(); 
    ?>
</div>

<!-- Page specific scripts -->
<script>
    const BASE_URL = '<?php echo BASE_URL; ?>';
</script>
<script src="<?php echo BASE_URL; ?>administrator/pages/includes/scripts/setup.js"></script>
</body>
</html>
