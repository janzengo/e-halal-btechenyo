<?php include 'includes/session.php'; ?>
<?php include 'includes/status.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header content-page-title">
      <h1>
        Action Logs
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-wrench"></i> Admin Actions</a></li>
        <li class="active">Logs</li>
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
              <a href="#reset" data-toggle="modal" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-refresh"></i> Reset</a>
            </div>
            <div class="box-header">
            <h3 class="box-title">Action Logs — <?php echo $conn->query("SELECT COUNT(*) FROM logs")->fetch_row()[0]; ?> Total Audited Logs</h3>
            </div>
            <div class="box-body">
              <table id="example1" class="table table-bordered">
                <thead>
                  <th>Date</th>
                  <th>User</th>
                  <th>Role</th>
                  <th>Details</th>
                </thead>
                <tbody>
              <?php
                $sql = "SELECT timestamp, username, role, details FROM logs ORDER BY timestamp DESC";
                $query = $conn->query($sql);
                
                while($row = $query->fetch_assoc()){
                  echo "
                    <tr>
                      <td>".$row['timestamp']."</td>
                      <td>".$row['username']."</td>
                      <td>".$row['role']."</td>
                      <td>".$row['details']."</td>
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
  <?php include 'includes/logs_modal.php'; ?>
  <?php include 'includes/footer.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>
</body>
</html>
