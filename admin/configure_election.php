<?php include "includes/session.php"; ?>
<?php include 'includes/status.php'; ?>
<?php include "includes/slugify.php"; ?>
<?php include "includes/header.php"; ?>
<body class="hold-transition skin-blue sidebar-mini">
   <div class="wrapper">
      <?php include "includes/navbar.php"; ?>
      <?php include "includes/menubar.php"; ?>
      <div class="content-wrapper">
         <!-- Content Header (Page header) -->
         <section class="content-header">
            <h1>
               Election Configuration
            </h1>
            <ol class="breadcrumb">
               <li><a href="#"><i class="fa fa-dashboard"></i> Settings</a></li>
               <li class="active">Election Configuration</li>
            </ol>
         </section>
         <!-- Main content -->
         <section class="content">
            <!-- Existing Election Configuration Box -->
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
                     <div class="box-body">
                        <?php
                           $sql = "SELECT * FROM election_status WHERE id = 1";
                           $query = $conn->query($sql);
                           $row = $query->fetch_assoc();
                        ?>
                        <form method="POST" action="save_election_config.php">
                           <div class="form-group">
                              <label for="election_name">Election Name</label>
                              <input type="text" class="form-control" id="election_name" name="election_name" value="<?php echo $row['election_name']; ?>" required>
                           </div>
                           <div class="form-group">
                              <label for="end_time">Election End Time & Date</label>
                              <input type="datetime-local" class="form-control" id="end_time" name="end_time" value="<?php echo date('Y-m-d\TH:i', strtotime($row['end_time'])); ?>" required>
                           </div>
                           <div class="form-group">
                              <label for="status">Election Status</label>
                              <select class="form-control" id="status" name="status" required>
                                 <option value="on" <?php if ($row['status'] == 'on') echo 'selected'; ?>>On</option>
                                 <option value="paused" <?php if ($row['status'] == 'paused') echo 'selected'; ?>>Paused</option>
                                 <option value="off" <?php if ($row['status'] == 'off') echo 'selected'; ?>>Off</option>
                              </select>
                           </div>
                           <ol class="breadcrumb">
                                        <li class="active"><i class="fa fa-info-circle"></i> Changing the election status to off <a> will end the entire election</a> (cannot be undone).</li>
                                    </ol>
                           <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                     </div>
                  </div>
               </div>
            </div>
            <!-- New Election History Box -->
            <div class="row">
               <div class="col-xs-12">
                  <div class="box">
                     <div class="box-header with-border">
                        <h3 class="box-title">Election History</h3>
                     </div>
                     <div class="box-body">
                        <table class="table table-bordered">
                           <thead>
                              <tr>
                                 <th>Election Name</th>
                                 <th>Start Date</th>
                                 <th>End Date</th>
                                 <th>Details</th>
                                 <th>Results</th>
                              </tr>
                           </thead>
                           <tbody>
                              <?php
                                 $sql = "SELECT * FROM election_history ORDER BY end_date DESC";
                                 $query = $conn->query($sql);
                                 while($row = $query->fetch_assoc()){
                                    $folder_name = slugify($row['election_name']);
                                    $details_pdf = "admin/elections/".$folder_name."/details.pdf";
                                    $results_pdf = "admin/elections/".$folder_name."/results.pdf";
                                    echo "
                                      <tr>
                                         <td>".$row['election_name']."</td>
                                         <td>".date('Y-m-d', strtotime($row['start_date']))."</td>
                                         <td>".date('Y-m-d', strtotime($row['end_date']))."</td>
                                         <td><a href='".$details_pdf."' class='btn btn-info btn-sm' target='_blank'>View Details</a></td>
                                         <td><a href='".$results_pdf."' class='btn btn-info btn-sm' target='_blank'>View Results</a></td>
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
      <?php include "includes/footer.php"; ?>
   </div>
   <?php include "includes/scripts.php"; ?>
</body>
</html>
