<?php
require_once __DIR__ . '/../classes/View.php';
require_once __DIR__ . '/../classes/Admin.php';
require_once __DIR__ . '/../classes/Candidate.php';
require_once __DIR__ . '/../classes/Position.php';

// Initialize classes
$view = View::getInstance();
$admin = Admin::getInstance();
$candidate = Candidate::getInstance();
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
    <title>E-Halal Voting System | Candidates Management</title>
    <?php echo $view->renderHeader(); ?>
    <style>
        .candidate-photo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        .platform-text {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
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
                Candidates Management
                <small>Add, Edit, Delete Candidates</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Candidates</li>
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
                            <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat">
                                <i class="fa fa-plus"></i> New Candidate
                            </a>
                        </div>
                        <div class="box-body">
                            <table id="candidateTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Position</th>
                                        <th>Candidate</th>
                                        <th>Platform</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $candidates = $candidate->getAllCandidates();
                                    foreach($candidates as $row){
                                        $image = (!empty($row['photo'])) ? BASE_URL . 'administrator/assets/images/'.$row['photo'] : BASE_URL . 'administrator/assets/images/profile.jpg';
                                        if (!file_exists($image)) {
                                            $image = BASE_URL . 'administrator/assets/images/profile.jpg';
                                        }
                                        echo "
                                        <tr>
                                            <td>".$row['position']."</td>
                                            <td>
                                                <img src='".$image."' width='30px' height='30px' class='candidate-photo'> ".$row['firstname']." ".$row['lastname']."
                                            </td>
                                            <td>".substr($row['platform'], 0, 30).(strlen($row['platform']) > 30 ? '...' : '')."</td>
                                            <td>
                                                <button class='btn btn-success btn-sm edit' data-id='".$row['id']."' data-toggle='tooltip' title='Edit'><i class='fa fa-edit'></i></button>
                                                <button class='btn btn-danger btn-sm delete' data-id='".$row['id']."' data-toggle='tooltip' title='Delete'><i class='fa fa-trash'></i></button>
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

    <?php echo $view->renderFooter(); ?>
</div>

<?php include 'includes/modals/candidates_modal.php'; ?>

<?php echo $view->renderScripts(); ?>

<script>
var baseUrl = '<?php echo BASE_URL; ?>';

$(function() {
    $('#candidateTable').DataTable({
        responsive: true,
        "order": [[ 0, "asc" ], [ 2, "asc" ]]  // Sort by position, then name
    });

    // Initialize all tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Edit candidate
    $(document).on('click', '.edit', function(e){
        e.preventDefault();
        $('#edit').modal('show');
        var id = $(this).data('id');
        
        $.ajax({
            type: 'POST',
            url: baseUrl + 'administrator/pages/includes/modals/controllers/CandidateController.php',
            data: {id:id, action:'get'},
            dataType: 'json',
            success: function(response){
                if(!response.error){
                    $('.candidate_id').val(response.data.id);
                    $('#edit_firstname').val(response.data.firstname);
                    $('#edit_lastname').val(response.data.lastname);
                    $('#edit_position').val(response.data.position_id);
                    $('#edit_platform').val(response.data.platform);
                    
                    if(response.data.photo){
                        $('#edit-photo-preview').attr('src', baseUrl + 'administrator/assets/images/' + response.data.photo);
                    } else {
                        $('#edit-photo-preview').attr('src', baseUrl + 'administrator/assets/images/profile.jpg');
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message,
                        showConfirmButton: true
                    });
                }
            },
            error: function(xhr, status, error){
                console.error(xhr.responseText);
                console.log("AJAX Error: " + status + " - " + error);
                console.log("Response Text: " + xhr.responseText);
                alert('Could not fetch candidate data. Please try again.');
            }
        });
    });

    // Delete candidate
    $(document).on('click', '.delete', function(e){
        e.preventDefault();
        $('#delete').modal('show');
        var id = $(this).data('id');
        
        $.ajax({
            type: 'POST',
            url: baseUrl + 'administrator/pages/includes/modals/controllers/CandidateController.php',
            data: {id:id, action:'get'},
            dataType: 'json',
            success: function(response){
                if(!response.error){
                    $('.candidate_id').val(response.data.id);
                    $('.fullname').html(response.data.firstname + ' ' + response.data.lastname);
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr, status, error){
                console.error(xhr.responseText);
                console.log("AJAX Error: " + status + " - " + error);
                console.log("Response Text: " + xhr.responseText);
                alert('Could not fetch candidate data. Please try again.');
            }
        });
    });

    // Preview uploaded photo
    $("#add_photo").change(function() {
        readURL(this, '#photo-preview');
    });

    $("#edit_photo").change(function() {
        readURL(this, '#edit-photo-preview');
    });
    
    // Initialize photo preview on page load
    $('#photo-preview').attr('src', '../assets/images/profile.jpg');
});

function readURL(input, previewId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $(previewId).attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>