<?php
require_once __DIR__ . '/../classes/View.php';
require_once __DIR__ . '/../classes/Admin.php';
require_once __DIR__ . '/../classes/Ballot.php';

// Initialize classes
$view = View::getInstance();
$admin = Admin::getInstance();
$ballot = AdminBallot::getInstance();

// Debug output to check if classes are loaded
echo "<!-- Classes loaded successfully -->";

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
                    <?php 
                    // Direct rendering of ballot
                    if (class_exists('AdminBallot')) {
                        $ballot = AdminBallot::getInstance();
                        echo $ballot->renderAdminBallot();
                        
                        // Ensure proper order
                        $ballot->reorderPositions();
                        
                        // Check if any positions were found
                        if (count($ballot->getAllPositions()) === 0) {
                            echo '<div class="alert alert-warning">No positions found. Please add positions in the Positions Management page first.</div>';
                        }
                    } else {
                        echo '<div class="alert alert-danger">AdminBallot class not found. Please check the system configuration.</div>';
                    }
                    ?>
                </div>
            </div>
        </section>
    </div>
    
    <?php echo $view->renderFooter(); ?>
</div>

<?php echo $view->renderScripts(); ?>

<script>
$(function() {
  fetchData();

  // Fetch ballot data
  function fetchData() {
    $.ajax({
      url: '<?php echo BASE_URL; ?>administrator/pages/includes/controllers/ballot_fetch.php',
      method: 'GET',
      dataType: 'json',
      success: function(response) {
        if (response.error) {
          alert(response.message);
          return;
        }
        
        // Update content
        $('#content').html(response);
      },
      error: function(xhr, status, error) {
        console.error('Error:', error);
        console.log('Response:', xhr.responseText);
        alert('Error fetching ballot data. Please check the console for details.');
      }
    });
  }

  // Move position up
  $(document).on('click', '.moveup', function() {
    var currentSection = $(this).closest('.position-section');
    var previousSection = currentSection.prev('.position-section');
    
    if (previousSection.length) {
      // Animate current section up
      currentSection.css('position', 'relative').animate({
        top: -previousSection.outerHeight(true)
      }, 300);
      
      // Animate previous section down
      previousSection.css('position', 'relative').animate({
        top: currentSection.outerHeight(true)
      }, 300, function() {
        // After animation, make the AJAX call
        var id = currentSection.find('.moveup').data('id');
        $.ajax({
          url: '<?php echo BASE_URL; ?>administrator/pages/includes/controllers/ballot_up.php',
          method: 'POST',
          data: {id: id},
          dataType: 'json',
          success: function(response) {
            if (response.error) {
              alert(response.message);
              // Reset positions if there's an error
              currentSection.animate({ top: 0 }, 200);
              previousSection.animate({ top: 0 }, 200);
              return;
            }
            fetchData();
          },
          error: function(xhr, status, error) {
            console.error('Error:', error);
            console.log('Response:', xhr.responseText);
            alert('Error moving position up. Please check the console for details.');
            // Reset positions on error
            currentSection.animate({ top: 0 }, 200);
            previousSection.animate({ top: 0 }, 200);
          }
        });
      });
    }
  });

  // Move position down
  $(document).on('click', '.movedown', function() {
    var currentSection = $(this).closest('.position-section');
    var nextSection = currentSection.next('.position-section');
    
    if (nextSection.length) {
      // Animate current section down
      currentSection.css('position', 'relative').animate({
        top: nextSection.outerHeight(true)
      }, 300);
      
      // Animate next section up
      nextSection.css('position', 'relative').animate({
        top: -currentSection.outerHeight(true)
      }, 300, function() {
        // After animation, make the AJAX call
        var id = currentSection.find('.movedown').data('id');
        $.ajax({
          url: '<?php echo BASE_URL; ?>administrator/pages/includes/controllers/ballot_down.php',
          method: 'POST',
          data: {id: id},
          dataType: 'json',
          success: function(response) {
            if (response.error) {
              alert(response.message);
              // Reset positions if there's an error
              currentSection.animate({ top: 0 }, 200);
              nextSection.animate({ top: 0 }, 200);
              return;
            }
            fetchData();
          },
          error: function(xhr, status, error) {
            console.error('Error:', error);
            console.log('Response:', xhr.responseText);
            alert('Error moving position down. Please check the console for details.');
            // Reset positions on error
            currentSection.animate({ top: 0 }, 200);
            nextSection.animate({ top: 0 }, 200);
          }
        });
      });
    }
  });

  // Reset button click handler
  $(document).on('click', '.reset', function() {
    var desc = $(this).data('desc');
    $('.' + desc).iCheck('uncheck');
  });
});
</script>
</body>
</html> 