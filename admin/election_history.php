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
    <section class="content-header">
      <h1>
        Election History
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

      <?php
        // Define the slugify function
        function slugify($text) {
          // Replace non-letter or digits by -
          $text = preg_replace('~[^\pL\d]+~u', '-', $text);

          // Transliterate
          $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

          // Remove unwanted characters
          $text = preg_replace('~[^-\w]+~', '', $text);

          // Trim
          $text = trim($text, '-');

          // Remove duplicate -
          $text = preg_replace('~-+~', '-', $text);

          // Lowercase
          $text = strtolower($text);

          if (empty($text)) {
            return 'n-a';
          }

          return $text;
        }
      ?>

      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-body">
              <table id="example1" class="table table-bordered">
                <thead>
                  <tr>
                    <th>Election Name</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Tools</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    $sql = "SELECT * FROM election_history ORDER BY end_date DESC";
                    $query = $conn->query($sql);
                    if($query->num_rows > 0){
                      while($row = $query->fetch_assoc()){
                        $folder_name = slugify($row['election_name']);
                        $details_pdf = "election_history/".$folder_name."/details.pdf";
                        $results_pdf = "election_history/".$folder_name."/results.pdf";
                        echo "
                          <tr>
                            <td>".$row['election_name']."</td>
                            <td>".date('Y-m-d', strtotime($row['start_date']))."</td>
                            <td>".date('Y-m-d', strtotime($row['end_date']))."</td>
                            <td>
                            <a href='".$details_pdf."' class='btn btn-success btn-sm' target='_blank'><i class='fa fa-info-circle'></i> View Details</a>
                            <a href='".$results_pdf."' class='btn btn-warning btn-sm' target='_blank'><i class='fa fa-check-circle'></i> View Results</a>
                            </td>
                          </tr>
                        ";
                      }
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
  <?php include 'includes/footer.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>
</body>
</html>