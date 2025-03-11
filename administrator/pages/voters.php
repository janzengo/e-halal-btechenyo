<?php
require_once __DIR__ . '/../classes/View.php';
require_once __DIR__ . '/../classes/Admin.php';
require_once __DIR__ . '/../classes/Voter.php';

// Initialize classes
$view = View::getInstance();
$admin = Admin::getInstance();
$voter = Voter::getInstance();

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
    <title>E-Halal Voting System | Voters Management</title>
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
                Voters Management
                <small>Add, Edit, Delete Voters</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Voters</li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header with-border">
                            <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat">
                                <i class="fa fa-plus"></i> New Voter
                            </a>
                        </div>
                        <div class="box-body">
                            <table id="voterTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Student Number</th>
                                        <th>Course</th>
                                        <th>Registration Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $voters = $voter->getAllVoters();
                                    foreach($voters as $row){
                                        echo "
                                        <tr>
                                            <td>".$row['student_number']."</td>
                                            <td>".$row['course_name']."</td>
                                            <td>".date('M d, Y', strtotime($row['created_at']))."</td>
                                            <td>".($row['has_voted'] ? '<span class="label label-success">Voted</span>' : '<span class="label label-warning">Not Voted</span>')."</td>
                                            <td>
                                                <button class='btn btn-success btn-sm edit btn-flat' data-id='".$row['id']."'>
                                                    <i class='fa fa-edit'></i> Edit
                                                </button>
                                                <button class='btn btn-danger btn-sm delete btn-flat' data-id='".$row['id']."'>
                                                    <i class='fa fa-trash'></i> Delete
                                                </button>
                                            </td>
                                        </tr>";
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

    <?php echo $view->renderFooter(); ?>
</div>

<?php include 'includes/modals/voters_modal.php'; ?>

<?php echo $view->renderScripts(); ?>

<script>
var baseUrl = '<?php echo BASE_URL; ?>';

$(function() {
    $('#voterTable').DataTable({
        responsive: true,
        "order": [[ 0, "desc" ]]
    });

    // Initialize all tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Edit voter
    $(document).on('click', '.edit', function(e){
        e.preventDefault();
        $('#edit').modal('show');
        var id = $(this).data('id');
        getRow(id);
    });

    // Delete voter
    $(document).on('click', '.delete', function(e){
        e.preventDefault();
        $('#delete').modal('show');
        var id = $(this).data('id');
        getRow(id);
    });
});

function getRow(id){
    $.ajax({
        type: 'POST',
        url: baseUrl + 'administrator/pages/includes/modals/controllers/VoterController.php',
        data: {id:id, action:'get'},
        dataType: 'json',
        success: function(response){
            if (!response.error) {
                $('.voter_id').val(response.data.id);
                $('#edit_student_number').val(response.data.student_number);
                $('#edit_course').val(response.data.course_id);
                $('.student_number').html(response.data.student_number);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: response.message,
                    showConfirmButton: true
                });
            }
        },
        error: function(xhr, status, error) {
            console.error(xhr.responseText);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Could not connect to server. Please try again.',
                showConfirmButton: true
            });
        }
    });
}
</script>
</body>
</html>