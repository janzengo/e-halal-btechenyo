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
<?php include 'includes/candidates_modal.php'; ?>
<?php include 'includes/partylists_modal.php'; ?>
<body class="hold-transition skin-blue layout-top-nav">
<style>
    .general, .positions {
        pointer-events: none;
    }
    .general a, .positions a {
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
                            <li class="positions"><a href="#">Positions</a></li>
                            <li class="active"><a href="#candidates">Candidates</a></li>
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
                            <div class="tab-pane active" id="candidates">
                                <!-- Content Header (Page header) -->
                                <section class="content-header content-page-title">
                                    <h1>
                                        Manage Candidates & Partylists
                                    </h1>
                                </section>
                                <!-- Main content -->
        <section class="content">
        <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
              <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat add"><i class="fa fa-plus"></i> New</a>
            </div>
            <div class="box-body">
              <table id="example1" class="table table-bordered">
                <thead>
                  <th class="hidden"></th>
                  <th>Partylist</th>
                  <th>Position</th>
                  <th>Photo</th>
                  <th>Firstname</th>
                  <th>Lastname</th>
                  <th>Platform</th>
                  <th>Tools</th>
                </thead>
                <tbody>
                  <?php
                    $sql = "SELECT candidates.*, partylists.name AS partylist_name, positions.description AS position_description 
                            FROM candidates 
                            LEFT JOIN positions ON positions.id = candidates.position_id 
                            LEFT JOIN partylists ON partylists.id = candidates.partylist_id 
                            ORDER BY positions.priority ASC";
                    $query = $conn->query($sql);
                    while($row = $query->fetch_assoc()){
                      $image = (!empty($row['photo'])) ? '../images/'.$row['photo'] : '../images/profile.jpg';
                      echo "
                        <tr>
                          <td class='hidden'></td>
                          <td>".$row['partylist_name']."</td>
                          <td>".$row['position_description']."</td>
                          <td>
                            <img src='".$image."' width='30px' height='30px'>
                            <a href='#edit_photo' data-toggle='modal' class='pull-right photo' data-id='".$row['id']."'><span class='fa fa-edit'></span></a>
                          </td>
                          <td>".$row['firstname']."</td>
                          <td>".$row['lastname']."</td>
                          <td><a href='#view_platform' data-toggle='modal' class='btn btn-info btn-sm btn-flat platform' data-id='".$row['id']."'><i class='fa fa-search'></i> View</a></td>
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
      
      <div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header with-border">
                <a href="#addnew-partylist" data-toggle="modal" class="btn btn-primary btn-sm btn-flat add-partylist"><i class="fa fa-plus"></i> New Partylist</a>
            </div>
            <div class="box-body">
                <table id="partylistsTable" class="table table-bordered">
                    <thead>
                        <th class="hidden"></th>
                        <th>Partylist</th>
                        <th>Number of Candidates</th>
                        <th>Tools</th>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT partylists.id, partylists.name AS partylist_name, COUNT(candidates.id) AS num_candidates 
                                FROM partylists 
                                LEFT JOIN candidates ON candidates.partylist_id = partylists.id 
                                GROUP BY partylists.id 
                                ORDER BY partylists.name ASC";
                        $query = $conn->query($sql);
                        while($row = $query->fetch_assoc()){
                            echo "
                                <tr>
                                    <td class='hidden'></td>
                                    <td>".$row['partylist_name']."</td>
                                    <td>".$row['num_candidates']."</td>
                                    <td>
                            <button class='btn btn-success btn-sm edit-partylist btn-flat' data-id='".$row['id']."'><i class='fa fa-edit'></i> Edit Partylist</button>
                            <button class='btn btn-danger btn-sm delete-partylist btn-flat' data-id='".$row['id']."'><i class='fa fa-trash'></i> Delete Partylist</button>
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
                                        <li class="active"><i class="fa fa-info-circle"></i> The system <a>highly recommend</a>  to input every candidates and partylists for the election before proceeding.</li>
                                    </ol>
    </section>   
                            </div>
                            <input type="hidden" name="status" id="status" value="candidates">
                            <div class="box-footer">
                                <a type="button" href="pre_election_positions.php" class="btn btn-secondary pull-left" id="save-button"><i class="fa fa-arrow-left"></i> Back</a>
                                <button type="submit" class="btn btn-primary pull-right" id="save-button">Submit <i class="fa fa-arrow-right"></i></button>
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
  // Candidates script
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

  $(document).on('click', '.photo', function(e){
    e.preventDefault();
    var id = $(this).data('id');
    $('#photo #origin').val('pre_election'); // Set origin value
    getRow(id);
  });

  $(document).on('click', '.platform', function(e){
    e.preventDefault();
    var id = $(this).data('id');
    $('#platform #origin').val('pre_election'); // Set origin value
    getRow(id);
  });

  // Partylists script
  $(document).on('click', '.edit-partylist', function(e){
    e.preventDefault();
    $('#edit-partylist').modal('show');
    var id = $(this).data('id');
    $('#edit-partylist #origin').val('pre_election'); // Set origin value
    getPartylistRow(id);
  });

  $(document).on('click', '.delete-partylist', function(e){
    e.preventDefault();
    $('#delete-partylist').modal('show');
    var id = $(this).data('id');
    $('#delete-partylist #origin').val('pre_election'); // Set origin value
    getPartylistRow(id);
  });

  $(document).on('click', '.add', function(e){
    e.preventDefault();
    $('#addnew').modal('show');
    $('#origin').val('pre_election');
    console.log('Origin set to: ' + $('#origin').val()); // For debugging
  });

  $(document).on('click', '.add-partylist', function(e){
    e.preventDefault();
    $('#addnew-partylist').modal('show');
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
    url: 'candidates_row.php',
    data: {id:id},
    dataType: 'json',
    success: function(response){
      $('.id').val(response.canid);
      $('#edit_firstname').val(response.firstname);
      $('#edit_lastname').val(response.lastname);
      $('#edit_position').val(response.position_id);
      $('#edit_partylist').val(response.partylist_id);      
      $('#edit_platform').val(response.platform);
      $('.fullname').html(response.firstname+' '+response.lastname);
      $('#desc').html(response.platform);
    }
  });
}

function getPartylistRow(id){
  $.ajax({
    type: 'POST',
    url: 'partylists_row.php',
    data: {id:id},
    dataType: 'json',
    success: function(response){
      $('.id').val(response.id);
      $('#edit_name').val(response.name);
      $('.name').html(response.name);
    }
  });
}
</script>
</body>
</body>
</html>
