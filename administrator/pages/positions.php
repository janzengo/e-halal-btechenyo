<?php
require_once __DIR__ . '/../classes/View.php';
require_once __DIR__ . '/../classes/Admin.php';
require_once __DIR__ . '/../classes/Position.php';
require_once __DIR__ . '/../classes/Elections.php';
Elections::enforceCompletedRedirect();
Elections::enforceSetupRedirect();

// Initialize classes
$view = View::getInstance();
$admin = Admin::getInstance();
$position = Position::getInstance();

// Check if admin is logged in
if (!$admin->isLoggedIn()) {
    header('Location: ../administrator');
    exit();
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    $admin->logout();
    header('location: ../index.php');
    exit();
}

// Check if modifications are allowed
$canModify = $view->isModificationAllowed();
if (!$canModify) {
    $_SESSION['warning'] = $view->getModificationMessage();
}

// Get all positions
$positions = $position->getAllPositionsByPriority();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>E-Halal BTECHenyo | Positions</title>
    <?php echo $view->renderHeader(); ?>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>administrator/assets/css/admin.css">
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
                Positions Management
                <small>Add, Edit, Delete Positions</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Manage</a></li>
                <li class="active">Positions</li>
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
                    <div class="box-header with-border">
                            <button type="button" class="btn btn-primary btn-sm btn-flat custom" data-toggle="modal" data-target="#addnew" <?php echo $view->getDisabledAttribute(); ?> <?php echo $view->getDisabledAttribute() ? 'data-toggle="tooltip" title="' . $view->getModificationMessage() . '"' : ''; ?>>
                                <i class="fa fa-plus"></i> New Position
                            </button>
                        </div>
                        <div class="box-body">
                            <table id="positionsTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Priority</th>
                                        <th>Description</th>
                                        <th>Maximum Vote</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($positions as $pos): ?>
                                        <tr>
                                            <td><?php echo $pos['priority']; ?></td>
                                            <td><?php echo htmlspecialchars($pos['description']); ?></td>
                                            <td><?php echo $pos['max_vote']; ?></td>
                                            <td>
                                                <button class="btn btn-primary btn-sm edit-position custom" data-id="<?php echo $pos['id']; ?>" <?php echo $view->getDisabledAttribute(); ?> <?php echo $view->getDisabledAttribute() ? 'data-toggle="tooltip" title="' . $view->getModificationMessage() . '"' : ''; ?>>
                                                    <i class="fa fa-edit"></i> Edit
                                                </button>
                                                <button class="btn btn-danger btn-sm delete-position" data-id="<?php echo $pos['id']; ?>" <?php echo $view->getDisabledAttribute(); ?> <?php echo $view->getDisabledAttribute() ? 'data-toggle="tooltip" title="' . $view->getModificationMessage() . '"' : ''; ?>>
                                                    <i class="fa fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            
                            <ol class='breadcrumb breadcrumb-info'>
                                <li class='active'><i class='fa fa-info-circle'></i> Position priority determines the order of positions on the ballot. To change position ordering, please use the <strong>Ballot Settings</strong> page.</li>
                            </ol>
                            
                            <?php
                            if(isset($_SESSION['warning'])){
                                echo "
                                    <ol class='breadcrumb breadcrumb-info'>
                                        <li class='active'><i class='fa fa-info-circle'></i> " . $_SESSION['warning'] . "</li>
                                    </ol>
                                ";
                                unset($_SESSION['warning']);
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>   
    </div>
    
    <?php 
    echo $view->renderFooter();
    include 'includes/modals/positions_modal.php';
    echo $view->renderScripts(); ?>

<script>
    // Global variables for position.js
    window.canModify = <?php echo $canModify ? 'true' : 'false'; ?>;
    window.modificationMessage = '<?php echo addslashes($view->getModificationMessage()); ?>';
    window.BASE_URL = '<?php echo BASE_URL; ?>';
</script>
<script src="<?php echo BASE_URL; ?>administrator/pages/includes/scripts/position.js"></script>
</body>
</html> 