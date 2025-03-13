<!-- Add -->
<div class="modal fade" id="addnew">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><b>Add New Candidate</b></h4>
            </div>
            <form id="addCandidateForm" class="form-horizontal" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="add_firstname" class="col-sm-3 control-label">First Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="add_firstname" name="firstname" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="add_lastname" class="col-sm-3 control-label">Last Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="add_lastname" name="lastname" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="position" class="col-sm-3 control-label">Position</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="position" name="position_id" required>
                                <?php
                                $positions = $position->getAllPositions();
                                foreach($positions as $pos){
                                    echo "<option value='".$pos['id']."'>".$pos['description']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="platform" class="col-sm-3 control-label">Platform</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="platform" name="platform" rows="7" required></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="add_photo" class="col-sm-3 control-label">Photo</label>
                        <div class="col-sm-9">
                            <input type="file" class="form-control" id="add_photo" name="photo">
                            <img id="add-photo-preview" src="<?php echo BASE_URL; ?>administrator/assets/images/profile.jpg" 
                                 style="max-width: 150px; margin-top: 10px;" class="img-responsive">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">
                        <i class="fa fa-close"></i> Close
                    </button>
                    <button type="submit" class="btn btn-primary btn-flat">
                        <i class="fa fa-save"></i> Save
                    </button>
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
                <h4 class="modal-title"><b>Edit Candidate</b></h4>
            </div>
            <form id="editCandidateForm" class="form-horizontal" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" class="candidate_id" name="id">
                    <div class="form-group">
                        <label for="edit_firstname" class="col-sm-3 control-label">First Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="edit_firstname" name="firstname" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_lastname" class="col-sm-3 control-label">Last Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="edit_lastname" name="lastname" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_position" class="col-sm-3 control-label">Position</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="edit_position" name="position_id" required>
                                <?php
                                foreach($positions as $pos){
                                    echo "<option value='".$pos['id']."'>".$pos['description']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_platform" class="col-sm-3 control-label">Platform</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="edit_platform" name="platform" rows="7" required></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_photo" class="col-sm-3 control-label">Photo</label>
                        <div class="col-sm-9">
                            <input type="file" class="form-control" id="edit_photo" name="photo">
                            <img id="edit-photo-preview" src="<?php echo BASE_URL; ?>administrator/assets/images/profile.jpg" 
                                 style="max-width: 150px; margin-top: 10px;" class="img-responsive">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">
                        <i class="fa fa-close"></i> Close
                    </button>
                    <button type="submit" class="btn btn-success btn-flat">
                        <i class="fa fa-check-square-o"></i> Update
                    </button>
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
            <form id="deleteCandidateForm" class="form-horizontal">
                <div class="modal-body">
                    <input type="hidden" class="candidate_id" name="id">
                    <div class="text-center">
                        <p>DELETE CANDIDATE</p>
                        <h2 class="bold fullname"></h2>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">
                        <i class="fa fa-close"></i> Close
                    </button>
                    <button type="submit" class="btn btn-danger btn-flat">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(function() {
    // Add Candidate Form Submit
    $('#addCandidateForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        formData.append('action', 'add');
        
        $.ajax({
            type: 'POST',
            url: baseUrl + 'administrator/pages/includes/modals/controllers/CandidateController.php',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (!response.error) {
                    $('#addnew').modal('hide');
                    $('#addCandidateForm')[0].reset();
                    location.reload();
                }
                Swal.fire({
                    icon: response.error ? 'error' : 'success',
                    title: response.error ? 'Error!' : 'Success!',
                    text: response.message,
                    showConfirmButton: true,
                    timer: 2000
                });
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
    });

    // Edit Candidate Form Submit
    $('#editCandidateForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        formData.append('action', 'edit');
        
        $.ajax({
            type: 'POST',
            url: baseUrl + 'administrator/pages/includes/modals/controllers/CandidateController.php',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (!response.error) {
                    $('#edit').modal('hide');
                    location.reload();
                }
                Swal.fire({
                    icon: response.error ? 'error' : 'success',
                    title: response.error ? 'Error!' : 'Success!',
                    text: response.message,
                    showConfirmButton: true,
                    timer: 2000
                });
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
    });

    // Delete Candidate Form Submit
    $('#deleteCandidateForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        formData.append('action', 'delete');
        
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
                    url: baseUrl + 'administrator/pages/includes/modals/controllers/CandidateController.php',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (!response.error) {
                            $('#delete').modal('hide');
                            location.reload();
                        }
                        Swal.fire({
                            icon: response.error ? 'error' : 'success',
                            title: response.error ? 'Error!' : 'Deleted!',
                            text: response.message,
                            showConfirmButton: true,
                            timer: 2000
                        });
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

    // Preview uploaded photo
    $("#add_photo").change(function() {
        readURL(this, '#add-photo-preview');
    });

    $("#edit_photo").change(function() {
        readURL(this, '#edit-photo-preview');
    });
    
    // Initialize photo previews
    $('#add-photo-preview').attr('src', baseUrl + 'administrator/assets/images/profile.jpg');
    $('#edit-photo-preview').attr('src', baseUrl + 'administrator/assets/images/profile.jpg');
});

function readURL(input, previewId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $(previewId).attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Function to get candidate details
function getRow(id) {
    $.ajax({
        type: 'POST',
        url: baseUrl + 'administrator/pages/includes/modals/controllers/CandidateController.php',
        data: {
            id: id,
            action: 'get'
        },
        dataType: 'json',
        success: function(response) {
            if (!response.error) {
                $('.candidate_id').val(response.data.id);
                $('#edit_firstname').val(response.data.firstname);
                $('#edit_lastname').val(response.data.lastname);
                $('#edit_position').val(response.data.position_id);
                $('#edit_platform').val(response.data.platform);
                $('.fullname').html(response.data.firstname + ' ' + response.data.lastname);
                
                if (response.data.photo) {
                    $('#edit-photo-preview').attr('src', baseUrl + 'administrator/assets/images/' + response.data.photo);
                } else {
                    $('#edit-photo-preview').attr('src', baseUrl + 'administrator/assets/images/profile.jpg');
                }
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
            console.log("Response Text: " + xhr.responseText);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Could not fetch candidate data. Please try again.',
                showConfirmButton: true
            });
        }
    });
}
</script>
