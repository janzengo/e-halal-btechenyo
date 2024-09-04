<?php 
include 'includes/session.php';
include 'includes/status.php';

// Check the current election status
$sql = "SELECT * FROM election_status WHERE status = 'pending' ORDER BY id DESC LIMIT 1";
$query = $conn->query($sql);
$row = $query->fetch_assoc();
$current_status = $row['status'];

// Redirect based on the election status
if ($current_status == 'on') {
    $_SESSION['error'] = 'The election is currently ongoing. You cannot configure the election at this time.';
    header('location: home.php'); // Redirect to the home page or a relevant page for an ongoing election
    exit();
} elseif ($current_status == 'paused') {
    $_SESSION['error'] = 'The election is paused. Please resume or end the election before making any configuration changes.';
    header('location: election_management.php'); // Redirect to a management page where they can resume or end the election
    exit();
} elseif ($current_status != 'off' && $current_status != 'pending') {
    $_SESSION['error'] = 'Invalid election status. Please contact the system administrator.';
    header('location: error_page.php'); // Redirect to an error page or any other appropriate page
    exit();
}

// Prefill form fields if the election status is pending
$election_name = '';
$end_time = '';
if ($current_status == 'pending') {
    $election_name = $row['election_name'];
    $end_time = $row['end_time'];
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/officers_modal.php'; ?>
<body class="hold-transition skin-blue layout-top-nav">
    <style>
        .positions, .candidates {
            pointer-events: none;
        }
        .positions a, .candidates a {
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
                            <li class="active"><a href="#general" data-toggle="tab">General</a></li>
                            <li class="positions"><a href="#" id="positions-tab">Positions</a></li>
                            <li class="candidates"><a href="#" id="candidates-tab">Candidates</a></li>
                        </ul>
                        <div class="tab-content">
                            <!-- General Tab -->
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
                            if (isset($_SESSION['info'])) {
                                echo "
                                <div class='alert alert-info alert-dismissible'>
                                    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                                    <h4><i class='icon fa fa-bullhorn'></i> Notice!</h4>
                                    " . $_SESSION['info'] . "
                                </div>
                                ";
                                unset($_SESSION['info']);
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
                            <div class="tab-pane active" id="general">
                                <div class="form-group">
                                    <label for="election_name">Election Name</label>
                                    <input type="text" class="form-control" id="election_name" name="election_name" value="<?php echo $election_name;?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="end_time">Election End Time & Date</label>
                                    <input type="datetime-local" class="form-control" id="end_time" name="end_time" value="<?php echo $end_time;?>" required>
                                </div>
                            </div>

                            <!-- Content Header (Page header) -->
                            <section class="content-header content-page-title">
                                <h1>Manage Officers</h1>
                            </section>

                            <!-- Main content -->
                            <section class="content">
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="box">
                                            <div class="box-header with-border">
                                                <a href="#addnew" data-toggle="modal" class="btn btn-primary add btn-sm btn-flat"><i class="fa fa-plus"></i> New</a>
                                            </div>
                                            <div class="box-body">
                                                <table id="example1" class="table table-bordered">
                                                    <thead>
                                                        <th>Lastname</th>
                                                        <th>Firstname</th>
                                                        <th>Username</th>
                                                        <th>Tools</th>
                                                    </thead>
                                                    <tbody>
                                                    <?php
                                                    $sql = "SELECT * FROM admin WHERE role = 'officer' ORDER BY lastname";
                                                    $query = $conn->query($sql);
                                                    
                                                    while ($row = $query->fetch_assoc()) {
                                                        $image = (!empty($row['photo'])) ? '../images/' . $row['photo'] : '../images/profile.jpg';
                                                        echo "
                                                        <tr>
                                                            <td>" . $row['lastname'] . "</td>
                                                            <td>" . $row['firstname'] . "</td>";
                                                            echo "<td>" . $row['username'] . "</td>";
                                                            echo "<td>
                                                                <button class='btn btn-success btn-sm edit btn-flat' data-id='" . $row['id'] . "'><i class='fa fa-edit'></i> Edit</button>
                                                                <button class='btn btn-danger btn-sm delete btn-flat' data-id='" . $row['id'] . "'><i class='fa fa-trash'></i> Delete</button>
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
    <li class="active">
        <i class="fa fa-info-circle"></i> 
        Officers have the following duties and access to specific pages:
        <ul>
            <li><strong>Manage Positions, Candidates, and Voters:</strong> Officers can add, edit, and remove positions, candidates, and voters.</li>
            <li><strong>View Reports:</strong> Officers have access to various reports that provide insights into the currect election.</li>
        </ul>
    </li>
</ol>
                            </section>   
                            <input type="hidden" name="status" id="status" value="general">
                            <div class="box-footer"> 
                                <button type="submit" class="btn btn-primary pull-right" id="save-button">Save <i class="fa fa-arrow-right"></i></button>
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
            $('#edit #origin').val('pre_election'); // Set origin value
            var id = $(this).data('id');
            getRow(id);
        });

        $(document).on('click', '.delete', function(e){
            e.preventDefault();
            $('#delete').modal('show');
            $('#delete #origin').val('pre_election'); // Set origin value
            var id = $(this).data('id');
            getRow(id);
        });

        $(document).on('click', '.add', function(e){
            e.preventDefault();
            $('#addnew').modal('show');
            $('#addnew #origin').val('pre_election'); // Set origin value
        });

        function getRow(id){
            $.ajax({
                type: 'POST',
                url: 'officers_row.php',
                data: {id:id},
                dataType: 'json',
                success: function(response){
                    $('.id').val(response.id);
                    $('#edit_firstname').val(response.firstname);
                    $('#edit_lastname').val(response.lastname);
                    $('#edit_username').val(response.username);
                    $('#edit_password').val(response.password);
                    $('#edit_gender').val(response.gender);
                    $('.fullname').html(response.firstname+' '+response.lastname);
                }
            });
        }
    });
    </script>
</body>
</html>
