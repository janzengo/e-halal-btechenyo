<?php
require_once __DIR__ . '/../classes/View.php';
require_once __DIR__ . '/../classes/Admin.php';
require_once __DIR__ . '/../classes/Position.php';

// Initialize classes
$view = View::getInstance();
$admin = Admin::getInstance();
$position = Position::getInstance();

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
    <title>E-Halal Voting System | Positions Management</title>
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
                Positions Management
                <small>Add, Edit, Delete Positions</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Positions</li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <?php
            if (isset($_SESSION['error'])) {
                echo "
                    <div class='alert alert-danger alert-dismissible'>
                        <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                        <h4><i class='icon fa fa-warning'></i> Error!</h4>
                        " . $_SESSION['error'] . "
                    </div>
                ";
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success'])) {
                echo "
                    <div class='alert alert-success alert-dismissible'>
                        <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                        <h4><i class='icon fa fa-check'></i> Success!</h4>
                        " . $_SESSION['success'] . "
                    </div>
                ";
                unset($_SESSION['success']);
            }
            ?>
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header with-border">
                            <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat">
                                <i class="fa fa-plus"></i> New Position
                            </a>
                        </div>
                        <div class="box-body">
                            <table id="positionTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Description</th>
                                        <th>Maximum Vote</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $positions = $position->getAllPositions();
                                    foreach ($positions as $row) {
                                        echo "
                                            <tr>
                                                <td>" . $row['description'] . "</td>
                                                <td>" . $row['max_vote'] . "</td>
                                                <td>
                                                    <button class='btn btn-success btn-sm edit btn-flat' data-id='" . $row['id'] . "'>
                                                        <i class='fa fa-edit'></i> Edit
                                                    </button>
                                                    <button class='btn btn-danger btn-sm delete btn-flat' data-id='" . $row['id'] . "'>
                                                        <i class='fa fa-trash'></i> Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        ";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>   
    </div>
    
    <?php 
    echo $view->renderFooter();
    include 'includes/modals/positions_modal.php';
    echo $view->renderScripts(); 
    ?>
</div>

<script>
$(function() {
    // Initialize DataTable
    $('#positionTable').DataTable({
        'responsive': true,
        'autoWidth': false,
        'language': {
            'searchPlaceholder': 'Search positions...'
        }
    });

    // Handle Edit button click
    $(document).on('click', '.edit', function(e) {
        e.preventDefault();
        $('#edit').modal('show');
        var id = $(this).data('id');
        getRow(id);
    });

    // Handle Delete button click
    $(document).on('click', '.delete', function(e) {
        e.preventDefault();
        $('#delete').modal('show');
        var id = $(this).data('id');
        getRow(id);
    });
});

function getRow(id){
    $.ajax({
        type: 'POST',
        url: '<?php echo BASE_URL; ?>administrator/pages/includes/modals/controllers/PositionController.php',
        data: {id:id, action:'get'},
        dataType: 'json',
        success: function(response){
            if (!response.error) {
                $('.position_id').val(response.data.id);
                $('#edit_description').val(response.data.description);
                $('#edit_max_vote').val(response.data.max_vote);
                $('.description').html(response.data.description);
            } else {
                console.error(response.message);
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error(xhr.responseText);
            alert('Error fetching position data. Please try again.');
        }
    });
}
</script>
</body>
</html> 