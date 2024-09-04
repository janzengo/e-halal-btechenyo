<?php
include 'includes/session.php';
include 'includes/status.php'; // Handles redirection based on status

// Custom code for positions tab
if (!isset($_SESSION['general_config_complete'])) {
    $_SESSION['error'] = 'Please complete the General tab first.';
    header('Location: pre_election.php');
    exit();
}
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/positions_modal.php'; ?>
<body class="hold-transition skin-blue layout-top-nav">
<style>
    .general, .candidates {
        pointer-events: none;
    }
    .general a, .candidates a {
        color: #bbb !important;
    }
    .nav-tabs-custom {
        border-radius: 10px !important;
    }
    .nav-tabs-custom > .tab-content {
        border-radius: 10px !important;
    }
</style>
<div class="wrapper">
    <?php include 'includes/navbar.php'; ?>
    <div class="content-wrapper">
        <div class="container">
            <section class="content">
                <form method="POST" action="save_election_config.php" id="election-form">
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="general"><a href="#general">General</a></li>
                            <li class="active"><a href="#">Positions</a></li>
                            <li class="candidates"><a href="#candidates">Candidates</a></li>
                        </ul>
                        <div class="tab-content">
                            <?php
                            if (isset($_SESSION['error'])) {
                                echo "
                                    <div class='alert alert-danger alert-dismissible'>
                                        <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                                        <h4><i class='icon fa fa-warning'></i> Error!</h4>
                                        ".$_SESSION['error']."
                                    </div>
                                ";
                                unset($_SESSION['error']);
                            }
                            if (isset($_SESSION['info'])) {
                                echo "
                                    <div class='alert alert-info alert-dismissible'>
                                        <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                                        <h4><i class='icon fa fa-bullhorn'></i> Notice!</h4>
                                        ".$_SESSION['info']."
                                    </div>
                                ";
                                unset($_SESSION['info']);
                            }
                            if (isset($_SESSION['success'])) {
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
                            <!-- General Tab -->
                            <div class="tab-pane active" id="positions">
                                <!-- Content Header (Page header) -->
                                <section class="content-header content-page-title">
                                    <h1>
                                        Manage Candidate Positions
                                    </h1>
                                </section>
                                <!-- Main content -->
                                <section class="content">
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <div class="box">
                                                <div class="box-header with-border">
                                                    <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> New</a>
                                                </div>
                                                <div class="box-body">
                                                    <table id="example1" class="table table-bordered">
                                                        <thead>
                                                            <th class="hidden"></th>
                                                            <th>Description</th>
                                                            <th>Maximum Vote</th>
                                                            <th>Tools</th>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $sql = "SELECT * FROM positions ORDER BY priority ASC";
                                                            $query = $conn->query($sql);
                                                            while ($row = $query->fetch_assoc()) {
                                                                echo "
                                                                    <tr>
                                                                        <td class='hidden'></td>
                                                                        <td>".$row['description']."</td>
                                                                        <td>".$row['max_vote']."</td>
                                                                        <td>
                                                                            <button class='btn btn-success btn-sm edit btn-flat' data-id='".$row['id']."'><i class='fa fa-edit'></i> Edit</button>
                                                                            <button class='btn btn-danger btn-sm delete btn-flat' data-id='".$row['id']."'><i class='fa fa-trash'></i> Delete</button>
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
                                    <ol class="breadcrumb">
                                        <li class="active"><i class="fa fa-info-circle"></i> Please set at least one position in order to proceed to the next tab. It is essential to define the positions that candidates will be running for in the election. Without setting at least one position, you will not be able to move forward in the setup process.
<br> <br>
We strongly recommend adding all the positions that will be available in the election. This ensures that you can provide complete and accurate details for each candidate. Defining all positions upfront helps in organizing the election process and ensures that all necessary information is captured for each candidate running for a specific position.
                                    </ol>
                                </section>
                            </div>
                            <input type="hidden" name="status" id="status" value="positions">
                            <div class="box-footer">
                                <a type="button" href="pre_election.php" class="btn btn-secondary pull-left" id="save-button"><i class="fa fa-arrow-left"></i> Back</a>
                                <button type="submit" class="btn btn-primary pull-right" id="save-button">Next <i class="fa fa-arrow-right"></i></button>
                            </div>
                        </div>
                    </div>
                </form>
            </section>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>
<script>
$(function(){
    $(document).on('click', '.edit', function(e){
        e.preventDefault();
        $('#edit').modal('show');
        var id = $(this).data('id');
        $('#edit #origin').val('pre_election'); // Set origin value
        getRow(id);
    });

    $(document).on('click', '.delete', function(e){
        e.preventDefault();
        $('#delete').modal('show');
        var id = $(this).data('id');
        $('#delete #origin').val('pre_election'); // Set origin value
        getRow(id);
    });

    $(document).on('click', '.add', function(e){
    e.preventDefault();
    $('#addnew').modal('show');
    $('#origin').val('pre_election');
    console.log('Origin set to: ' + $('#origin').val()); // For debugging
});

// Add this to ensure the origin is set when the modal opens
$('#addnew').on('show.bs.modal', function () {
    $('#origin').val('pre_election');
    console.log('Origin set on modal show: ' + $('#origin').val()); // For debugging
});
});

function getRow(id){
    $.ajax({
        type: 'POST',
        url: 'positions_row.php',
        data: {id:id},
        dataType: 'json',
        success: function(response){
            $('.id').val(response.id);
            $('#edit_description').val(response.description);
            $('#edit_max_vote').val(response.max_vote);
            $('.description').html(response.description);
        }
    });
}
</script>
</body>
</html>
