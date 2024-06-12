<?php include "includes/session.php"; ?>
<?php include "includes/slugify.php"; ?>
<?php include "includes/header.php"; ?>
<body class="hold-transition skin-blue sidebar-mini">
   <div class="wrapper">
      <?php include "includes/navbar.php"; ?>
      <?php include "includes/menubar.php"; ?>
      <style>
         .small-box .fa-user,.small-box .fa-users,.small-box .fa-check-circle,.small-box .fa-layer-group{
         font-size: 70px !important;
         }
         .rounded-container {
         border-radius: 10px;
         overflow: hidden;
         }
         /* Greetings Banner Styles */
         .greetings-banner {
         background: linear-gradient(to right, #EDFFD6 0%, #D8FFBC 48%, #B3FF8F 100%);
         padding: 20px;
         }
         .greetings-content {
         display: flex;
         align-items: center;
         }
         .greetings-text {
         margin-left: 20px;
         max-width: 50%;
         }
         .greetings-text h2 {
         font-size: 31px;
         font-weight: 600;
         margin: 0;
         color: #229043;
         }
         .greetings-text p {
          text-align: justify;
          text-justify: inter-character;
          font-size: 21px;
         margin: 5px 0;
         color: #229043;
         }
         .greetings-icon {
         margin-left: auto;
         }
         .greetings-icon i {
         font-size: 24px;
         color: #555; /* Adjust color as needed */
         }
      </style>
      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
         <!-- Greetings Banner -->
         <?php include '../admin/includes/greetings.php' ?>
         <!-- Content Header (Page header) -->
         <section class="content-header content-page-title">
            <h1>
               Dashboard
            </h1>
            <ol class="breadcrumb">
               <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
               <li class="active">Dashboard</li>
            </ol>
         </section>
         <!-- Main content -->
         <section class="content">
            <?php
               if (isset($_SESSION["error"])) {
                   echo "
                           <div class='alert alert-danger alert-dismissible'>
                             <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                             <h4><i class='icon fa fa-warning'></i> Error!</h4>
                             " . $_SESSION["error"] . "
                           </div>
                         ";
                   unset($_SESSION["error"]);
               }
               if (isset($_SESSION["success"])) {
                   echo "
                           <div class='alert alert-success alert-dismissible'>
                             <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                             <h4><i class='icon fa fa-check'></i> Success!</h4>
                             " . $_SESSION["success"] . "
                           </div>
                         ";
                   unset($_SESSION["success"]);
               }
               ?>
            <!-- Small boxes (Stat box) -->
            <div class="row">
               <div class="col-lg-3 col-xs-6">
                  <!-- small box -->
                  <div class="small-box bg-aqua">
                     <div class="inner">
                        <?php
                           $sql = "SELECT * FROM positions";
                           $query = $conn->query($sql);
                           echo "<h3>" . $query->num_rows . "</h3>";
                           ?>
                        <p>No. of <br>Positions</p>
                     </div>
                     <div class="icon">
                        <i class="fa fa-layer-group" style="color: #0097bc;"></i>
                     </div>
                     <a href="positions.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                  </div>
               </div>
               <!-- ./col -->
               <div class="col-lg-3 col-xs-6">
                  <!-- small box -->
                  <div class="small-box bg-green">
                     <div class="inner">
                        <?php
                           $sql = "SELECT * FROM candidates";
                           $query = $conn->query($sql);
                           echo "<h3>" . $query->num_rows . "</h3>";
                           ?>
                        <p>No. of <br>Candidates</p>
                     </div>
                     <div class="icon">
                        <i class="fa fa-user"></i>
                     </div>
                     <a href="candidates.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                  </div>
               </div>
               <!-- ./col -->
               <div class="col-lg-3 col-xs-6">
                  <!-- small box -->
                  <div class="small-box bg-yellow">
                     <div class="inner">
                        <?php
                           $sql = "SELECT * FROM voters";
                           $query = $conn->query($sql);
                           echo "<h3>" . $query->num_rows . "</h3>";
                           ?>
                        <p>Total <br>Voters</p>
                     </div>
                     <div class="icon">
                        <i class="fa fa-users"></i>
                     </div>
                     <a href="voters.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                  </div>
               </div>
               <!-- ./col -->
               <div class="col-lg-3 col-xs-6">
                  <!-- small box -->
                  <div class="small-box bg-red">
                     <div class="inner">
                        <?php
                           $sql = "SELECT * FROM votes GROUP BY voters_id";
                           $query = $conn->query($sql);
                           echo "<h3>" . $query->num_rows . "</h3>";
                           ?>
                        <p>Voters<br>Voted</p>
                     </div>
                     <div class="icon">
                        <i class="fa fa-check-circle"></i>
                     </div>
                     <a href="votes.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                  </div>
               </div>
               <!-- ./col -->
            </div>
            <div class="row">
               <div class="col-xs-12 content-page-title">
                  <h3>Votes Tally
                     <span class="pull-right">
                     <a href="print.php" class="btn btn-success btn-sm btn-flat" target="_blank"><span class="glyphicon glyphicon-print"></span> Print</a>
                     </span>
                  </h3>
               </div>
            </div>
            <?php
               $sql = "SELECT * FROM positions ORDER BY priority ASC";
               $query = $conn->query($sql);
               $inc = 2;
               while ($row = $query->fetch_assoc()) {
                   $inc = $inc == 2 ? 1 : $inc + 1;
                   if ($inc == 1) {
                       echo "<div class='row'>";
                   }
                   echo "
                           <div class='col-sm-6'>
                             <div class='box box-solid rounded-container'>
                               <div class='box-header with-border'>
                                 <h4 class='box-title'><b>" . $row["description"] . "</b></h4>
                               </div>
                               <div class='box-body'>
                                 <div class='chart'>
                                   <canvas id='" . slugify($row["description"]) . "' style='height:200px'></canvas>
                                 </div>
                               </div>
                             </div>
                           </div>
                         ";
                   if ($inc == 2) {
                       echo "</div>";
                   }
               }
               if ($inc == 1) {
                   echo "<div class='col-sm-6'></div></div>";
               }
               ?>
         </section>
         <!-- right col -->
      </div>
      <?php include "includes/footer.php"; ?>
   </div>
   <!-- ./wrapper -->
   <?php include "includes/scripts.php"; ?>
   <!-- Add the latest version of Chart.js -->
   <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
   <?php
      $sql = "SELECT * FROM positions ORDER BY priority ASC";
      $query = $conn->query($sql);
      while ($row = $query->fetch_assoc()) {
          $sql = "SELECT * FROM candidates WHERE position_id = '" . $row["id"] . "'";
          $cquery = $conn->query($sql);
          $carray = [];
          $varray = [];
          while ($crow = $cquery->fetch_assoc()) {
              array_push($carray, $crow["lastname"]);
              $sql = "SELECT * FROM votes WHERE candidate_id = '" . $crow["id"] . "'";
              $vquery = $conn->query($sql);
              array_push($varray, intval($vquery->num_rows));
          }
          $carray = json_encode($carray);
          $varray = json_encode($varray);
      ?>
   <script>
      $(function(){
        var rowid = '<?php echo $row["id"]; ?>';
        var description = '<?php echo slugify($row["description"]); ?>';
        var barChartCanvas = $('#'+description).get(0).getContext('2d');
        var barChartData = {
          labels  : <?php echo $carray; ?>,
          datasets: [
            {
              label               : 'Votes',
              backgroundColor     : 'rgba(36, 150, 70, 0.6)',
              borderColor         : 'rgba(36, 150, 70, 1)',
              borderWidth         : 1,
              hoverBackgroundColor: 'rgba(36, 150, 70, 0.8)',
              hoverBorderColor    : 'rgba(36, 150, 70, 1)',
              borderRadius        : 10, // Add this line to make the ends of the bars rounded
              data                : <?php echo $varray; ?>
            }
          ]
        };
        var barChartOptions = {
          indexAxis: 'y',
          responsive: true,
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  let label = context.dataset.label || '';
                  if (label) {
                    label += ': ';
                  }
                  if (context.dataset.data[context.dataIndex] !== null) {
                    label += context.dataset.data[context.dataIndex];
                  }
                  return label;
                }
              }
            }
          },
          scales: {
            x: {
              beginAtZero: true,
              grid: {
                display: true,
                color: 'rgba(0,0,0,.05)',
                lineWidth: 1
              }
            },
            y: {
              grid: {
                display: true,
                color: 'rgba(0,0,0,.05)',
                lineWidth: 1
              }
            }
          }
        };
      
        new Chart(barChartCanvas, {
          type: 'bar',
          data: barChartData,
          options: barChartOptions
        });
      
      });
   </script>
   <?php
      }
      ?>
</body>
</html>