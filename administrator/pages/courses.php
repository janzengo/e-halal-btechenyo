<?php
require_once __DIR__ . '/../classes/View.php';
require_once __DIR__ . '/../classes/Elections.php';
Elections::enforceCompletedRedirect();
require_once __DIR__ . '/../classes/Admin.php';
require_once __DIR__ . '/../classes/Course.php';

$view = View::getInstance();
$admin = Admin::getInstance();

// Check if user is logged in
if (!$admin->isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

// Get all courses with voter counts
$course = Course::getInstance();
$courses = $course->getAllCoursesWithCounts();

// Check if modifications are allowed
$canModify = $view->isModificationAllowed();
if (!$canModify) {
    $_SESSION['warning'] = $view->getModificationMessage();
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    $admin->logout();
    header('location: ../index.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>E-Halal BTECHenyo | Course Management</title>
    <?php echo $view->renderHeader(); ?>
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
        <section class="content-header">
            <h1>
                Course Management
                <small>Add, Edit, Delete Courses</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Manage</a></li>
                <li class="active">Courses</li>
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
                            <button type="button" class="btn btn-primary btn-sm btn-flat custom" data-toggle="modal" data-target="#addnew" <?php echo $view->getDisabledAttribute(); ?> <?php echo $view->getDisabledAttribute() ? 'data-toggle="tooltip" title="' . $view->getModificationMessage() . '"' : ''; ?>>
                                <i class="fa fa-plus"></i> New Course
                            </button>
                        </div>
                        <div class="box-body">
                            <table id="coursesTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Description</th>
                                        <th>Voters</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($courses as $c): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($c['description']); ?></td>
                                        <td><?php echo $c['voter_count']; ?></td>
                                        <td>
                                            <button class="btn btn-primary btn-sm edit-course custom" data-id="<?php echo $c['id']; ?>" <?php echo $view->getDisabledAttribute(); ?> <?php echo $view->getDisabledAttribute() ? 'data-toggle="tooltip" title="' . $view->getModificationMessage() . '"' : ''; ?>>
                                                <i class="fa fa-edit"></i> Edit
                                            </button>
                                            <button class="btn btn-danger btn-sm delete-course" data-id="<?php echo $c['id']; ?>" <?php echo $view->getDisabledAttribute(); ?> <?php echo $view->getDisabledAttribute() ? 'data-toggle="tooltip" title="' . $view->getModificationMessage() . '"' : ''; ?>>
                                                <i class="fa fa-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php
                            if(isset($_SESSION['warning'])){
                                echo "
                                    <ol class='breadcrumb' style='margin-top: 20px;'>
                                        <li class='active'><i class='fa fa-info-circle'></i> " . $_SESSION['warning'] . "</li>
                                    </ol>
                                ";
                                unset($_SESSION['warning']);
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php echo $view->renderFooter(); ?>
</div>

<?php include 'includes/modals/courses_modal.php'; ?>

<?php echo $view->renderScripts(); ?>

<script>
    // Global variables for course.js
    window.canModify = <?php echo $canModify ? 'true' : 'false'; ?>;
    window.modificationMessage = '<?php echo addslashes($view->getModificationMessage()); ?>';
    window.BASE_URL = '<?php echo BASE_URL; ?>';
</script>
<script src="<?php echo BASE_URL; ?>administrator/pages/includes/scripts/course.js"></script>
</body>
</html> 