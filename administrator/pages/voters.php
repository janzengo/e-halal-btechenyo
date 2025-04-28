<?php
require_once __DIR__ . '/../classes/View.php';
require_once __DIR__ . '/../classes/Elections.php';
Elections::enforceCompletedRedirect();
Elections::enforceSetupRedirect();
require_once __DIR__ . '/../classes/Admin.php';
require_once __DIR__ . '/../classes/Voter.php';
require_once __DIR__ . '/../classes/ExcelGenerator.php';

// Initialize classes
$view = View::getInstance();
$admin = Admin::getInstance();
$voter = Voter::getInstance();
$excelGen = ExcelGenerator::getInstance();

// Handle template download
if (isset($_GET['action']) && $_GET['action'] == 'download_template') {
    $filename = $excelGen->generateVotersTemplate();
    $filepath = __DIR__ . '/../../uploads/templates/' . $filename;
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    readfile($filepath);
    unlink($filepath); // Clean up
    exit();
}

// Handle CSV Upload
if (isset($_POST['upload_csv'])) {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
        $fileName = $_FILES['csv_file']['tmp_name'];
        $fileExtension = strtolower(pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION));

        if ($fileExtension !== 'csv') {
            $_SESSION['error'] = 'Please upload a CSV file. If you\'re using Excel, make sure to save your file as "CSV (Comma delimited)" before uploading.';
            header('Location: ' . BASE_URL . 'administrator/pages/voters');
            exit();
        }
        
        try {
            // Read CSV file
            $handle = fopen($fileName, 'r');
            if ($handle === false) {
                throw new Exception('Unable to open the uploaded file. Please try again.');
            }

            // Get course mapping
            $courseMapping = $excelGen->processCourseMapping();
            
            // Skip header row
            $headers = fgetcsv($handle);
            if ($headers === false) {
                throw new Exception('The uploaded file appears to be empty. Please check the file and try again.');
            }

            // Validate headers
            $expectedHeaders = ['Student Number', 'Course'];
            $foundHeaders = array_map('trim', $headers);
            if ($foundHeaders !== $expectedHeaders) {
                throw new Exception(
                    'The file format does not match the template. Please use the provided template and ensure all columns are present.' . 
                    "\nExpected columns: " . implode(', ', $expectedHeaders) . 
                    "\nFound columns: " . implode(', ', $foundHeaders)
                );
            }
            
            $success = 0;
            $failed = 0;
            $errors = [];
            $rowNumber = 2; // Start after header row
            
            while (($row = fgetcsv($handle)) !== false) {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }
                
                // Ensure we have all columns
                if (count($row) < 2) {
                    $errors[] = "Row {$rowNumber}: Missing required information. Please ensure both Student Number and Course are provided.";
                    $failed++;
                    $rowNumber++;
                    continue;
                }

                $studentNumber = trim($row[0]);
                $courseName = trim($row[1]);
                
                // Basic validation
                if (empty($studentNumber) || empty($courseName)) {
                    $errors[] = "Row {$rowNumber}: Student Number and Course are required fields. Please check the data.";
                    $failed++;
                    $rowNumber++;
                    continue;
                }
                
                // Get course ID from mapping
                $courseId = isset($courseMapping[$courseName]) ? $courseMapping[$courseName] : null;
                
                if (!$courseId) {
                    $errors[] = "Row {$rowNumber}: Invalid course name '{$courseName}'. Please use the dropdown in the template to select a valid course.";
                    $failed++;
                    $rowNumber++;
                    continue;
                }
                
                try {
                    if ($voter->addVoter($studentNumber, $courseId)) {
                        $success++;
                    } else {
                        $failed++;
                        $errors[] = "Row {$rowNumber}: Student Number '{$studentNumber}' already exists in the system. Each student number must be unique.";
                    }
                } catch (Exception $e) {
                    $failed++;
                    $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                }
                
                $rowNumber++;
            }
            
            fclose($handle);
            
            if ($failed > 0) {
                $errorMessage = "<strong>Import Results:</strong><br>";
                $errorMessage .= "✓ Successfully added: {$success} voter" . ($success !== 1 ? 's' : '') . "<br>";
                $errorMessage .= "✗ Failed to add: {$failed} voter" . ($failed !== 1 ? 's' : '') . "<br><br>";
                $errorMessage .= "<strong>Details of Failed Entries:</strong><br>";
                $errorMessage .= "<ul style='padding-left: 20px; margin-bottom: 0;'>";
                foreach ($errors as $error) {
                    $errorMessage .= "<li>{$error}</li>";
                }
                $errorMessage .= "</ul>";
                $_SESSION['error'] = $errorMessage;
            } else {
                $_SESSION['success'] = "Successfully imported {$success} voter" . ($success !== 1 ? 's' : '') . "!";
            }
            
        } catch (Exception $e) {
            $_SESSION['error'] = "<strong>Error Processing File:</strong><br>" . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . 'administrator/voters');
        exit();
    } else {
        $_SESSION['error'] = 'Please select a valid CSV file to upload.';
        header('Location: ' . BASE_URL . 'administrator/voters');
        exit();
    }
}

// Check if admin is logged in
if (!$admin->isLoggedIn()) {
    header('Location: ../administrator');
    exit();
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
    <title>E-Halal BTECHenyo | Voters</title>
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
                Voters Management
                <small>Add, Edit, Delete Voters</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Manage</a></li>
                <li class="active">Voters</li>
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
                            <button type="button" class="btn btn-primary btn-sm btn-flat custom" data-toggle="modal" data-target="#addnew" <?php echo $view->getDisabledAttribute(); ?>>
                                <i class="fa fa-plus"></i> New Voter
                            </button>
                            <button type="button" class="btn btn-success btn-sm btn-flat" data-toggle="modal" data-target="#uploadCSV" <?php echo $view->getDisabledAttribute(); ?>>
                                <i class="fa fa-file-excel-o"></i> Import Excel/CSV
                            </button>
                            <a href="?action=download_template" class="btn btn-info btn-sm btn-flat">
                                <i class="fa fa-download"></i> Download Template
                            </a>
                            <button type="button" id="bulkDeleteBtn" class="btn btn-danger btn-sm btn-flat" style="display: none;" <?php echo $view->getDisabledAttribute(); ?>>
                                <i class="fa fa-trash"></i> Delete Selected
                            </button>
                        </div>
                        <div class="box-body">
                            <table id="example1" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="checkAll">
                                        </th>
                                        <th>Student Number</th>
                                        <th>Course</th>
                                        <th>Status</th>
                                        <th>Tools</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $voters = $voter->getAllVoters();
                                    foreach ($voters as $row) {
                                        $status = $row['has_voted'] ? '<span class="label label-success">Voted</span>' : '<span class="label label-danger">Not Voted</span>';
                                        // Combine election status and voter status restrictions
                                        // If election is active OR voter has voted, buttons should be disabled
                                        $voterDisabled = $row['has_voted'] ? 'disabled' : '';
                                        $combinedDisabled = $view->getDisabledAttribute() ? 'disabled' : $voterDisabled;
                                        
                                        // Set appropriate tooltip message
                                        $tooltip = '';
                                        if ($view->getDisabledAttribute()) {
                                            $tooltip = 'data-toggle="tooltip" title="Cannot modify voters while election is active"';
                                        } elseif ($row['has_voted']) {
                                            $tooltip = 'data-toggle="tooltip" title="Cannot modify voters who have already voted"';
                                        }
                                        
                                        echo "
                                            <tr>
                                                <td>
                                                    <input type='checkbox' class='voter-checkbox' value='" . $row['id'] . "' " . $combinedDisabled . " " . $tooltip . ">
                                                </td>
                                                <td>" . $row['student_number'] . "</td>
                                                <td>" . $row['course_name'] . "</td>
                                                <td>" . $status . "</td>
                                                <td>
                                                    <button type='button' class='btn btn-primary btn-sm edit custom' data-id='" . $row['id'] . "' " . $combinedDisabled . " " . $tooltip . ">
                                                        <i class='fa fa-edit'></i> Edit
                                                    </button>
                                                    <button type='button' class='btn btn-danger btn-sm delete' data-id='" . $row['id'] . "' " . $combinedDisabled . " " . $tooltip . ">
                                                        <i class='fa fa-trash'></i> Delete
                                                    </button>
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
        </section>
    </div>

    <?php echo $view->renderFooter(); ?>
</div>

<?php include 'includes/modals/voters_modal.php'; ?>

<?php echo $view->renderScripts(); ?>

<script>
var baseUrl = '<?php echo BASE_URL; ?>';

$(function() {
    var table = $('#example1').DataTable({
        responsive: true,
        "order": [[ 1, "desc" ]],
        columnDefs: [{
            orderable: false,
            targets: [0, 4]
        }]
    });

    // Initialize all tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Handle "Check All" checkbox
    $('#checkAll').on('change', function() {
        $('.voter-checkbox:not(:disabled)').prop('checked', $(this).prop('checked'));
        updateBulkDeleteButton();
    });

    // Handle individual checkboxes
    $(document).on('change', '.voter-checkbox', function() {
        updateBulkDeleteButton();
        // Update "Check All" state
        var allChecked = $('.voter-checkbox:not(:disabled)').length === $('.voter-checkbox:not(:disabled):checked').length;
        $('#checkAll').prop('checked', allChecked);
    });

    // Update bulk delete button visibility
    function updateBulkDeleteButton() {
        var checkedCount = $('.voter-checkbox:checked').length;
        $('#bulkDeleteBtn').toggle(checkedCount > 0);
    }

    // Handle bulk delete
    $('#bulkDeleteBtn').on('click', function() {
        var selectedIds = [];
        var checkedCount = $('.voter-checkbox:checked').length;
        
        $('.voter-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        Swal.fire({
            title: 'Delete Selected Voters',
            text: `Are you sure you want to delete ${checkedCount} selected voter(s)? This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete them!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'POST',
                    url: baseUrl + 'administrator/pages/includes/modals/controllers/VoterController.php',
                    data: {
                        action: 'bulk_delete',
                        ids: selectedIds
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (!response.error) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message || 'Selected voters have been deleted successfully.',
                                showConfirmButton: false,
                                timer: 2000
                            }).then(function() {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: response.message || 'An error occurred while deleting voters.',
                                showConfirmButton: true
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Could not connect to server. Please try again.',
                            showConfirmButton: true
                        });
                    }
                });
            }
        });
    });

    // Edit voter
    $(document).on('click', '.edit', function(e) {
        e.preventDefault();
        if (!$(this).prop('disabled')) {
            $('#edit').modal('show');
            var id = $(this).data('id');
            getRow(id);
        }
    });

    // Delete voter
    $(document).on('click', '.delete', function(e) {
        e.preventDefault();
        if (!$(this).prop('disabled')) {
            var id = $(this).data('id');
            var studentNumber = $(this).closest('tr').find('td:eq(1)').text();
            
            Swal.fire({
                title: 'Delete Voter',
                text: `Are you sure you want to delete voter ${studentNumber}? This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: baseUrl + 'administrator/pages/includes/modals/controllers/VoterController.php',
                        data: {
                            id: id,
                            action: 'delete'
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (!response.error) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 2000
                                }).then(function() {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: response.message,
                                    showConfirmButton: true
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error(xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Could not connect to server. Please try again.',
                                showConfirmButton: true
                            });
                        }
                    });
                }
            });
        }
    });
});

function getRow(id){
    $.ajax({
        type: 'POST',
        url: baseUrl + 'administrator/pages/includes/modals/controllers/VoterController.php',
        data: {id:id, action:'get'},
        dataType: 'json',
        success: function(response){
            if (!response.error) {
                $('.voter_id').val(response.data.id);
                $('#edit_student_number').val(response.data.student_number);
                $('#edit_course').val(response.data.course_id);
                $('.student_number').html(response.data.student_number);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: response.message,
                    showConfirmButton: true
                });
            }
        },
        error: function(xhr, status, error) {
            console.error(xhr.responseText);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Could not connect to server. Please try again.',
                showConfirmButton: true
            });
        }
    });
}
</script>
</body>
</html>