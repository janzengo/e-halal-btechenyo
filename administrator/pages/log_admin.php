<?php
require_once __DIR__ . '/../classes/View.php';
require_once __DIR__ . '/../classes/Admin.php';
require_once __DIR__ . '/../classes/Logger.php';

// Initialize classes
$view = View::getInstance();
$admin = Admin::getInstance();
$logger = AdminLogger::getInstance();

// Check if admin is logged in and is superadmin
if (!$admin->isLoggedIn() || !$admin->isSuperAdmin()) {
    $_SESSION['error'] = 'Access Denied. This page is restricted to superadmins only.';
    header('Location: home');
    exit();
}

// Handle log clearing if requested
if (isset($_POST['clear_logs']) && $admin->isAdmin()) {
    $type = $_POST['log_type'] ?? 'all';
    $logger->clearLogs($type);
    $_SESSION['success'] = 'Admin logs cleared successfully';
    header('Location: log_admin.php');
    exit();
}

// At the top of admin_logs.php, officers.php, and configure.php
if (!$admin->isSuperAdmin()) {
    $_SESSION['error'] = 'Access Denied. This page is restricted to superadmins only.';
    header('Location: home');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>E-Halal Voting System | Administrator Logs</title>
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
                Administrator Logs
                <small>View all administrator activities</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i>Reports</a></li>
                <li class="active">Admin Logs</li>
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
                <!-- Admin Logs -->
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-body">
                            <div class="table-responsive">
                                <table id="admin_logs_table" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Timestamp</th>
                                            <th>Username</th>
                                            <th>Role</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $admin_logs = $logger->getAdminLogs();
                                        if (empty($admin_logs)) {
                                            echo "<tr><td colspan='4' class='text-center'>No logs found</td></tr>";
                                        } else {
                                            foreach ($admin_logs as $log) {
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($log['timestamp']) . "</td>";
                                                echo "<td>" . htmlspecialchars($log['user_id']) . "</td>";
                                                echo "<td>" . htmlspecialchars($log['role']) . "</td>";
                                                echo "<td>" . htmlspecialchars($log['action']) . "</td>";
                                                echo "</tr>";
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
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
    var adminTable = $('#admin_logs_table');
    if (adminTable.find('tbody tr').length > 0 && adminTable.find('tbody tr td').length > 1) {
        adminTable.DataTable({
            'order': [[0, 'desc']],
            'pageLength': 15,
            'responsive': true,
            'autoWidth': false,
            'language': {
                'emptyTable': 'No admin logs found',
                'zeroRecords': 'No matching admin logs found'
            }
        });
    }
});
</script>
</body>
</html> 