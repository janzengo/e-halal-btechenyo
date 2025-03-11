<?php
require_once __DIR__ . '/../classes/View.php';
require_once __DIR__ . '/../classes/Admin.php';
require_once __DIR__ . '/../classes/Ballot.php';

// Initialize classes
$view = View::getInstance();
$admin = Admin::getInstance();
$ballot = Ballot::getInstance();

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
    <title>E-Halal Voting System | Ballot Position</title>
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
        <section class="content-header content-page-title">
            <h1>
                Ballot Position
                <small>Arrange the order of positions in the ballot</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Settings</a></li>
                <li class="active">Ballot Preview</li>
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
                <div class="col-xs-10 col-xs-offset-1" id="content">
                </div>
            </div>
        </section>
    </div>
    
    <?php echo $view->renderFooter(); ?>
</div>

<?php echo $view->renderScripts(); ?>

<script>
$(function(){
    fetch();

    $(document).on('click', '.reset', function(e){
        e.preventDefault();
        var desc = $(this).data('desc');
        $('.'+desc).iCheck('uncheck');
    });

    $(document).on('click', '.moveup', function(e){
        e.preventDefault();
        var id = $(this).data('id');
        $('#'+id).animate({
            'marginTop' : "-300px"
        });
        $.ajax({
            type: 'POST',
            url: 'includes/ballot_up.php',
            data:{id:id},
            dataType: 'json',
            success: function(response){
                if(!response.error){
                    fetch();
                }
            }
        });
    });

    $(document).on('click', '.movedown', function(e){
        e.preventDefault();
        var id = $(this).data('id');
        $('#'+id).animate({
            'marginTop' : "+300px"
        });
        $.ajax({
            type: 'POST',
            url: 'includes/ballot_down.php',
            data:{id:id},
            dataType: 'json',
            success: function(response){
                if(!response.error){
                    fetch();
                }
            }
        });
    });
});

function fetch(){
    $.ajax({
        type: 'POST',
        url: 'includes/ballot_fetch.php',
        dataType: 'json',
        success: function(response){
            $('#content').html(response).iCheck({
                checkboxClass: 'icheckbox_flat-green',
                radioClass: 'iradio_flat-green'
            });
        }
    });
}
</script>
</body>
</html> 