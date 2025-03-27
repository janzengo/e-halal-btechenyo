<?php
require_once __DIR__ . '/../classes/View.php';
require_once __DIR__ . '/../classes/Admin.php';
require_once __DIR__ . '/../classes/Partylist.php';

// Initialize classes
$view = View::getInstance();
$admin = Admin::getInstance();
$partylist = Partylist::getInstance();

// Check if admin is logged in
if (!$admin->isLoggedIn()) {
    header('Location: ../administrator');
    exit();
}

// Check if modifications are allowed
$canModify = $view->isModificationAllowed();
if (!$canModify) {
    $_SESSION['warning'] = $view->getModificationMessage();
}

// Get all partylists with candidate counts
$partylists = $partylist->getAllPartylistsWithCounts();

// Handle logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    $admin->logout();
    header('location: ../index.php');
    exit();
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>E-Halal Voting System | Partylist Management</title>
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
                Partylist Management
                <small>Add, Edit, Delete Partylists</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Manage</a></li>
                <li class="active">Partylists</li>
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
                            <button type="button" class="btn btn-primary btn-sm btn-flat" data-toggle="modal" data-target="#addnew" <?php echo $view->getDisabledAttribute(); ?> <?php echo $view->getDisabledAttribute() ? 'data-toggle="tooltip" title="' . $view->getModificationMessage() . '"' : ''; ?>>
                                <i class="fa fa-plus"></i> New Partylist
                            </button>
                        </div>
                        <div class="box-body">
                            <table id="partylistsTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Candidates</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($partylists as $p): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($p['name']); ?></td>
                                        <td><?php echo $p['candidate_count']; ?></td>
                                        <td>
                                            <button class="btn btn-primary btn-sm edit-partylist" data-id="<?php echo $p['id']; ?>" <?php echo $view->getDisabledAttribute(); ?> <?php echo $view->getDisabledAttribute() ? 'data-toggle="tooltip" title="' . $view->getModificationMessage() . '"' : ''; ?>>
                                                <i class="fa fa-edit"></i> Edit
                                            </button>
                                            <button class="btn btn-danger btn-sm delete-partylist" data-id="<?php echo $p['id']; ?>" <?php echo $view->getDisabledAttribute(); ?> <?php echo $view->getDisabledAttribute() ? 'data-toggle="tooltip" title="' . $view->getModificationMessage() . '"' : ''; ?>>
                                                <i class="fa fa-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php
                            if(isset($_SESSION['warning'])){
                                echo "
                                    <ol class='breadcrumb' style='margin-top: 20px;'>
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

    <?php echo $view->renderFooter(); ?>
</div>

<?php include 'includes/modals/partylists_modal.php'; ?>

<?php echo $view->renderScripts(); ?>

<script>
    // Global variables for partylist.js
    window.canModify = <?php echo $canModify ? 'true' : 'false'; ?>;
    window.modificationMessage = '<?php echo addslashes($view->getModificationMessage()); ?>';
    window.BASE_URL = '<?php echo BASE_URL; ?>';
</script>
<script src="<?php echo BASE_URL; ?>administrator/pages/includes/scripts/partylist.js"></script>
</body>
</html> 