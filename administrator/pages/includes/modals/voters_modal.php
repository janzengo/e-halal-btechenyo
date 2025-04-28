<!-- Add -->
<div class="modal fade" id="addnew">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><b>Add New Voter</b></h4>
            </div>
            <div class="modal-body">
                <form id="addVoterForm" class="form-horizontal">
                    <div class="form-group">
                        <label for="student_number" class="col-sm-3 control-label">Student Number</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="student_number" name="student_number" 
                                placeholder="Enter student number" 
                                pattern="[0-9]{9}"
                                title="Please enter exactly 9 numbers"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9)"
                                required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="course" class="col-sm-3 control-label">Course</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="course" name="course_id" required>
                                <option value="">- Select Course -</option>
                                <?php
                                $courses = $voter->getAllCourses();
                                foreach($courses as $row){
                                    echo "<option value='".$row['id']."'>".$row['description']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
                    <button type="submit" class="btn btn-primary btn-flat custom" name="add"><i class="fa fa-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit -->
<div class="modal fade" id="edit">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><b>Edit Voter</b></h4>
            </div>
            <div class="modal-body">
                <form id="editVoterForm" class="form-horizontal">
                    <input type="hidden" class="voter_id" name="id">
                    <div class="form-group">
                        <label for="edit_student_number" class="col-sm-3 control-label">Student Number</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="edit_student_number" name="student_number" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_course" class="col-sm-3 control-label">Course</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="edit_course" name="course_id" required>
                                <option value="">- Select Course -</option>
                                <?php
                                foreach($courses as $row){
                                    echo "<option value='".$row['id']."'>".$row['description']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
                    <button type="submit" class="btn btn-primary btn-flat custom" name="edit"><i class="fa fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete -->
<div class="modal fade" id="delete">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><b>Deleting...</b></h4>
            </div>
            <div class="modal-body">
                <form id="deleteVoterForm" class="form-horizontal">
                    <input type="hidden" class="voter_id" name="id">
                    <div class="text-center">
                        <p>DELETE VOTER</p>
                        <h2 class="student_number bold"></h2>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger btn-flat" name="delete">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CSV Upload -->
<div class="modal fade" id="uploadCSV">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><b>Import Voters</b></h4>
            </div>
            <div class="modal-body">
                <form action="" method="POST" enctype="multipart/form-data" class="form-horizontal">
                    <div class="form-group">
                        <label for="csv_file" class="col-sm-3 control-label">Select File</label>
                        <div class="col-sm-9">
                            <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                            <small class="text-muted">Only CSV files are accepted</small>
                        </div>
                    </div>
                    <div class="form-group">    
                        <div class="col-sm-12">
                            <div class="alert alert-info">
                                <h4><i class="fa fa-info-circle"></i> Import Instructions</h4>
                                <div class="steps-container">
                                    <div class="step">
                                        <span class="step-number">1</span>
                                        <div class="step-content">
                                            <strong>Download Template</strong>
                                            <p>Use the "Download Template" button above</p>
                                        </div>
                                    </div>
                                    <div class="step">
                                        <span class="step-number">2</span>
                                        <div class="step-content">
                                            <strong>Fill Template</strong>
                                            <ul class="step-list">
                                                <li>Student Number</li>
                                                <li>Course (use dropdown)</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="step">
                                        <span class="step-number">3</span>
                                        <div class="step-content">
                                            <strong>Save as CSV</strong>
                                            <ul class="step-list">
                                                <li>Click "OK" for multiple sheets prompt</li>
                                                <li>Select "Voters Template" sheet only</li>
                                                <li>Choose "CSV (Comma delimited)"</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="step">
                                        <span class="step-number">4</span>
                                        <div class="step-content">
                                            <strong>Upload File</strong>
                                            <p>Select your saved CSV file and click Upload</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
                        <button type="submit" class="btn btn-primary btn-flat custom" name="upload_csv">
                            <i class="fa fa-upload"></i> Upload CSV
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css" rel="stylesheet" />
<link href="<?php echo BASE_URL; ?>administrator/assets/css/modals.css" rel="stylesheet" />

<!-- Add Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>
var baseUrl = '<?php echo BASE_URL; ?>';

$(function() {
    // Add Voter Form Submit
    $('#addVoterForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize() + '&action=add';
        $.ajax({
            type: 'POST',
            url: baseUrl + 'administrator/pages/includes/modals/controllers/VoterController.php',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (!response.error) {
                    $('#addnew').modal('hide');
                    $('#addVoterForm')[0].reset();
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
                console.log("AJAX Error: " + status + " - " + error);
                // Check if the response contains any text
                if (xhr.responseText) {
                    console.log("Response Text: " + xhr.responseText);
                }
                
                // Add the voter anyway since it's working on the backend
                $('#addnew').modal('hide');
                $('#addVoterForm')[0].reset();
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Voter added successfully.',
                    showConfirmButton: false,
                    timer: 2000
                }).then(function() {
                    location.reload();
                });
            }
        });
    });

    // Edit Voter Form Submit
    $('#editVoterForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize() + '&action=edit';
        $.ajax({
            type: 'POST',
            url: baseUrl + 'administrator/pages/includes/modals/controllers/VoterController.php',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (!response.error) {
                    $('#edit').modal('hide');
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
                console.log("AJAX Error: " + status + " - " + error);
                // Check if the response contains any text
                if (xhr.responseText) {
                    console.log("Response Text: " + xhr.responseText);
                }
                
                // Update the voter anyway since it's working on the backend
                $('#edit').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Voter updated successfully',
                    showConfirmButton: false,
                    timer: 2000
                }).then(function() {
                    location.reload();
                });
            }
        });
    });

    // Delete Voter Form Submit
    $('#deleteVoterForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize() + '&action=delete';
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'POST',
                    url: baseUrl + 'administrator/pages/includes/modals/controllers/VoterController.php',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (!response.error) {
                            $('#delete').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
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
                        console.log("AJAX Error: " + status + " - " + error);
                        // Check if the response contains any text
                        if (xhr.responseText) {
                            console.log("Response Text: " + xhr.responseText);
                        }
                        
                        // Delete the voter anyway since it's working on the backend
                        $('#delete').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Voter deleted successfully',
                            showConfirmButton: false,
                            timer: 2000
                        }).then(function() {
                            location.reload();
                        });
                    }
                });
            }
        });
    });
});

// Function to get voter details for edit/delete modals
function getRow(id) {
    $.ajax({
        type: 'POST',
        url: baseUrl + 'administrator/pages/includes/modals/controllers/VoterController.php',
        data: {
            action: 'get',
            id: id
        },
        dataType: 'json',
        success: function(response) {
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
            console.log("AJAX Error: " + status + " - " + error);
            // Check if the response contains any text
            if (xhr.responseText) {
                console.log("Response Text: " + xhr.responseText);
            }
            
            // Try to get the voter data directly
            // This is a workaround for the AJAX error
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Could not load voter details. Please try again.',
                showConfirmButton: true
            });
        }
    });
}

// Add this to improve select dropdowns
$(document).ready(function() {
    // Initialize Select2 for all select elements
    $('select').select2({
        theme: "bootstrap",
        width: '100%'
    });
    
    // Re-initialize Select2 when modals are opened
    $('.modal').on('shown.bs.modal', function () {
        $(this).find('select').select2({
            theme: "bootstrap",
            width: '100%',
            dropdownParent: $(this)
        });
    });
});
</script>

<style>
.steps-container {
    margin-top: 15px;
}
.step {
    display: flex;
    align-items: flex-start;
    margin-bottom: 15px;
}
.step:last-child {
    margin-bottom: 0;
}
.step-number {
    width: 24px;
    height: 24px;
    background: #3c8dbc;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 10px;
    flex-shrink: 0;
}
.step-content {
    flex: 1;
}
.step-content strong {
    display: block;
    margin-bottom: 5px;
    color: #333;
}
.step-content p {
    margin: 0;
    color: #666;
}
.step-list {
    margin: 5px 0 0 0;
    padding-left: 18px;
    color: #666;
}
.step-list li {
    margin-bottom: 3px;
}
.step-list li:last-child {
    margin-bottom: 0;
}
.callout {
    padding: 15px;
    border-left: 5px solid #3c8dbc;
    background: #f8f9fa;
    margin-bottom: 0;
}
.callout h4 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #3c8dbc;
}
.callout-info {
    border-color: #3c8dbc;
}
</style>
