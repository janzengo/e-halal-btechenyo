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
                        <label for="add_position" class="col-sm-3 control-label">Position</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="add_position" name="position_id" required>
                                <option value="">- Select Position -</option>
                                <?php
                                $positions = $position->getAllPositions();
                                foreach($positions as $row){
                                    echo "<option value='".$row['id']."'>".$row['description']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="add_firstname" class="col-sm-3 control-label">Firstname</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="add_firstname" name="firstname" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="add_lastname" class="col-sm-3 control-label">Lastname</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="add_lastname" name="lastname" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="add_platform" class="col-sm-3 control-label">Platform</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="add_platform" name="platform" rows="7" required></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="add_photo" class="col-sm-3 control-label">Photo</label>
                        <div class="col-sm-9">
                            <input type="file" id="add_photo" name="photo" accept="image/*">
                            <img id="photo-preview" src="../../../assets/images/profile.jpg" alt="Photo Preview" style="max-width: 200px; margin-top: 10px;">
                            <small class="text-muted">Leave blank to use default profile image</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-flat" name="add">Save</button>
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
                        <label for="edit_position" class="col-sm-3 control-label">Position</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="edit_position" name="position_id" required>
                                <option value="">- Select Position -</option>
                                <?php
                                foreach($positions as $row){
                                    echo "<option value='".$row['id']."'>".$row['description']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_firstname" class="col-sm-3 control-label">Firstname</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="edit_firstname" name="firstname" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_lastname" class="col-sm-3 control-label">Lastname</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="edit_lastname" name="lastname" required>
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
                            <input type="file" id="edit_photo" name="photo" accept="image/*">
                            <img id="edit-photo-preview" src="../../../assets/images/profile.jpg" alt="Photo Preview" style="max-width: 200px; margin-top: 10px;">
                            <small class="text-muted">Leave blank to keep current photo or use default profile image</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success btn-flat" name="edit">Save</button>
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
                <form class="form-horizontal" id="deleteCandidateForm">
                    <input type="hidden" class="candidate_id" name="id">
                    <div class="text-center">
                        <p>DELETE CANDIDATE</p>
                        <h2 class="bold candidate-fullname"></h2>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
                <button type="submit" class="btn btn-danger btn-flat" form="deleteCandidateForm"><i class="fa fa-trash"></i> Delete</button>
            </div>
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
            url: '../../../pages/includes/modals/controllers/CandidateController.php',
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function(response) {
                if(!response.error) {
                    $('#addnew').modal('hide');
                    $('#addCandidateForm')[0].reset();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
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
                console.log("Response Text: " + xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while processing your request.',
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
            url: '../../../pages/includes/modals/controllers/CandidateController.php',
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function(response) {
                if(!response.error) {
                    $('#edit').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
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
                console.log("Response Text: " + xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while processing your request.',
                    showConfirmButton: true
                });
            }
        });
    });
    
    // Delete Candidate Form Submit
    $('#deleteCandidateForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize() + '&action=delete';
        
        $.ajax({
            type: 'POST',
            url: '../../../pages/includes/modals/controllers/CandidateController.php',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if(!response.error) {
                    $('#delete').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
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
                console.log("Response Text: " + xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while processing your request.',
                    showConfirmButton: true
                });
            }
        });
    });
    
    // Preview image before upload
    $('#add_photo').on('change', function() {
        readURL(this, '#photo-preview');
    });
    
    $('#edit_photo').on('change', function() {
        readURL(this, '#edit-photo-preview');
    });
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
</script>