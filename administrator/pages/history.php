<?php
require_once __DIR__ . '/../classes/View.php';
require_once __DIR__ . '/../classes/Admin.php';
require_once __DIR__ . '/../classes/Elections.php';
Elections::enforceCompletedRedirect();
require_once __DIR__ . '/../classes/Logger.php';

// Initialize classes
$view = View::getInstance();
$admin = Admin::getInstance();
$election = Elections::getInstance();
$logger = AdminLogger::getInstance();

// Check if admin is logged in
if (!$admin->isLoggedIn()) {
    $_SESSION['error'] = 'You do not have permission to access this page.';
    header('Location: ../administrator');
    exit();
}

// Get page parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total records first
$total_records = count($election->getElectionHistory(0, 0));
$total_pages = max(1, ceil($total_records / $limit));

// Ensure page doesn't exceed total pages
if ($page > $total_pages) {
    $page = $total_pages;
    $offset = ($page - 1) * $limit;
}

// Get election history
$history = $election->getElectionHistory($limit, $offset);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>E-Halal BTECHenyo | Election History</title>
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
                Election History
                <small>View past elections</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Settings</a></li>
                <li class="active">Election History</li>
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
                            <h3 class="box-title">Past Elections</h3>
                        </div>
                        <div class="box-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="historyTable">
                                    <thead>
                                        <tr>
                                            <th>Election Name</th>
                                            <th>Created At</th>
                                            <th>End Time</th>
                                            <th>Status</th>
                                            <th>Documents</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (empty($history)) {
                                            echo "<tr><td colspan='5' class='text-center'>No election history found</td></tr>";
                                        } else {
                                            foreach ($history as $row) {
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($row['election_name']) . "</td>";
                                                echo "<td>" . date('M d, Y h:i A', strtotime($row['created_at'])) . "</td>";
                                                echo "<td>" . date('M d, Y h:i A', strtotime($row['end_time'])) . "</td>";
                                                echo "<td><span class='label label-" . 
                                                    ($row['status'] === 'completed' ? 'success' : 'default') . 
                                                    "'>" . ucfirst(htmlspecialchars($row['status'])) . "</span></td>";
                                                echo "<td>";
                                                if ($row['details_pdf']) {
                                                    echo "<a href='" . BASE_URL . ltrim(htmlspecialchars($row['details_pdf']), '/') . "' class='btn btn-info btn-sm' target='_blank'>
                                                            <i class='fa fa-file-pdf'></i> Summary
                                                          </a> ";
                                                }
                                                if ($row['results_pdf']) {
                                                    echo "<a href='" . BASE_URL . ltrim(htmlspecialchars($row['results_pdf']), '/') . "' class='btn btn-success btn-sm' target='_blank'>
                                                            <i class='fa fa-file-pdf'></i> Results
                                                          </a>";
                                                }
                                                echo "</td>";
                                                echo "</tr>";
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if ($total_pages > 1): ?>
                            <div class="box-footer clearfix">
                                <ul class="pagination pagination-sm no-margin pull-right">
                                    <?php if ($page > 1): ?>
                                    <li><a href="?page=1">&laquo;</a></li>
                                    <li><a href="?page=<?php echo ($page - 1); ?>">&lsaquo;</a></li>
                                    <?php endif; ?>
                                    
                                    <?php
                                    $start_page = max(1, $page - 2);
                                    $end_page = min($total_pages, $page + 2);
                                    
                                    for ($i = $start_page; $i <= $end_page; $i++) {
                                        echo '<li' . ($page == $i ? ' class="active"' : '') . '>';
                                        echo '<a href="?page=' . $i . '">' . $i . '</a>';
                                        echo '</li>';
                                    }
                                    ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                    <li><a href="?page=<?php echo ($page + 1); ?>">&rsaquo;</a></li>
                                    <li><a href="?page=<?php echo $total_pages; ?>">&raquo;</a></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
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
    // Only initialize DataTable if there are rows and they're not the empty message row
    if ($('#historyTable tbody tr').length > 0 && $('#historyTable tbody tr td[colspan]').length === 0) {
        $('#historyTable').DataTable({
            responsive: true,
            paging: false,  // Disable DataTables pagination since we're using server-side
            ordering: true,
            order: [[1, 'desc']], // Sort by created_at by default
            searching: true,
            info: false,    // Disable info since we're using server-side pagination
            autoWidth: false,
            language: {
                emptyTable: "No election history found",
                search: "Filter records:"
            },
            columnDefs: [
                { orderable: true, targets: [0, 1, 2, 3] },
                { orderable: false, targets: 4 }
            ]
        });
    } else {
        // If no data, just ensure the table has the right styling
        $('#historyTable').addClass('display').css('width', '100%');
    }

    // Hide DataTables length changing since we're using server-side pagination
    $('.dataTables_length').hide();
});
</script>
</body>
</html> 