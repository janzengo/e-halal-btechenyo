<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/CustomSessionHandler.php';
require_once __DIR__ . '/../classes/View.php';
require_once __DIR__ . '/../classes/Elections.php';
require_once __DIR__ . '/../classes/Admin.php';
require_once __DIR__ . '/../classes/Ballot.php';
require_once __DIR__ . '/../includes/access_control.php';

// Initialize classes
$view = View::getInstance();
$admin = Admin::getInstance();
$ballot = AdminBallot::getInstance();
$accessControl = AccessControl::getInstance();

// Check access control
$accessControl->checkPageAccess(basename(__FILE__));

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
    <title>E-Halal BTECHenyo | Ballot</title>
    <?php echo $view->renderHeader(); ?>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>administrator/assets/css/ballots.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>administrator/assets/css/admin.css">
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
                        echo '<div id="content"></div>';
                    } else {
                        echo '
                        <div class="alert alert-danger">
                            <h4><i class="icon fa fa-warning"></i> System Error</h4>
                            <p>AdminBallot class not found. Please contact your system administrator.</p>
                        </div>';
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
  // Constants for AJAX URLs
  const URLS = {
    FETCH: '<?php echo BASE_URL; ?>administrator/pages/includes/controllers/ballot_fetch.php',
    MOVE_UP: '<?php echo BASE_URL; ?>administrator/pages/includes/controllers/ballot_up.php',
    MOVE_DOWN: '<?php echo BASE_URL; ?>administrator/pages/includes/controllers/ballot_down.php'
  };

  // Common error handler for AJAX requests
  function handleAjaxError(xhr, status, error, message) {
    console.error('Error:', error);
    console.log('Response:', xhr.responseText);
    alert(message || 'An error occurred. Please check the console for details.');
  }

  // Common function to handle position movement
  function movePosition(currentSection, targetSection, moveType, url) {
    if (!targetSection.length) return;

    const moveDistance = moveType === 'up' ? -targetSection.outerHeight(true) : targetSection.outerHeight(true);
    const targetDistance = moveType === 'up' ? currentSection.outerHeight(true) : -currentSection.outerHeight(true);

    // Animate current section
    currentSection.css('position', 'relative').animate({
      top: moveDistance
    }, 300);

    // Animate target section
    targetSection.css('position', 'relative').animate({
      top: targetDistance
    }, 300, function() {
      // After animation, make the AJAX call
      const id = currentSection.find(moveType === 'up' ? '.moveup' : '.movedown').data('id');
      $.ajax({
        url: url,
        method: 'POST',
        data: {id: id},
        dataType: 'json',
        success: function(response) {
          if (response.error) {
            alert(response.message);
            // Reset positions if there's an error
            currentSection.animate({ top: 0 }, 200);
            targetSection.animate({ top: 0 }, 200);
            return;
          }
  fetchData();
        },
        error: function(xhr, status, error) {
          handleAjaxError(xhr, status, error, `Error moving position ${moveType}. Please check the console for details.`);
          // Reset positions on error
          currentSection.animate({ top: 0 }, 200);
          targetSection.animate({ top: 0 }, 200);
        }
      });
    });
  }

  // Fetch ballot data
  function fetchData() {
    $.ajax({
      url: URLS.FETCH,
      method: 'GET',
      dataType: 'json',
      success: function(response) {
        if (response.error) {
          $('#content').html(`
            <div class="box box-default">
              <div class="box-body text-center">
                <div class="empty-state-container">
                  <i class="fa fa-ticket-alt fa-4x text-muted mb-3"></i>
                  <h3 class="text-muted">No Ballot Positions Found</h3>
                  <p class="text-muted mb-4">
                    There are no positions configured for the ballot yet. 
                    Add positions first to arrange them in the ballot.
                  </p>
                  <a href="positions" class="btn btn-primary">
                    <i class="fa fa-plus mr-2"></i> Add Positions
                  </a>
                </div>
              </div>
            </div>`);
          return;
        }
        
        // Update content
        $('#content').html(response);
      },
      error: function(xhr, status, error) {
        handleAjaxError(xhr, status, error, 'Error fetching ballot data. Please check the console for details.');
      }
    });
  }

  fetchData();

  // Move position up
  $(document).on('click', '.moveup', function() {
    const currentSection = $(this).closest('.position-section');
    const previousSection = currentSection.prev('.position-section');
    movePosition(currentSection, previousSection, 'up', URLS.MOVE_UP);
  });

  // Move position down
  $(document).on('click', '.movedown', function() {
    const currentSection = $(this).closest('.position-section');
    const nextSection = currentSection.next('.position-section');
    movePosition(currentSection, nextSection, 'down', URLS.MOVE_DOWN);
  });

  // Reset button click handler
  $(document).on('click', '.reset', function() {
    const desc = $(this).data('desc');
    $('.' + desc).iCheck('uncheck');
  });
});
</script>
</body>
</html> 