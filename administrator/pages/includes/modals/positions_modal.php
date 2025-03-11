<!-- Add -->
<div class="modal fade" id="addnew">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><b>Add New Position</b></h4>
            </div>
            <form id="addPositionForm" class="form-horizontal">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="description" class="col-sm-3 control-label">Description</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="description" name="description" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="max_vote" class="col-sm-3 control-label">Maximum Vote</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" id="max_vote" name="max_vote" required min="1">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
                    <button type="submit" class="btn btn-primary btn-flat"><i class="fa fa-save"></i> Save</button>
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
                <h4 class="modal-title"><b>Edit Position</b></h4>
            </div>
            <form id="editPositionForm" class="form-horizontal">
                <div class="modal-body">
                    <input type="hidden" class="position_id" name="id">
                    <div class="form-group">
                        <label for="edit_description" class="col-sm-3 control-label">Description</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="edit_description" name="description" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_max_vote" class="col-sm-3 control-label">Maximum Vote</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" id="edit_max_vote" name="max_vote" required min="1">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
                    <button type="submit" class="btn btn-success btn-flat"><i class="fa fa-check-square-o"></i> Update</button>
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
            <form id="deletePositionForm" class="form-horizontal">
                <div class="modal-body">
                    <input type="hidden" class="position_id" name="id">
                    <div class="text-center">
                        <p>DELETE POSITION</p>
                        <h2 class="bold description"></h2>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
                    <button type="submit" class="btn btn-danger btn-flat"><i class="fa fa-trash"></i> Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var baseUrl = '<?php echo BASE_URL; ?>';

$(function() {
    // Add Position Form Submit
    $('#addPositionForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize() + '&action=add';
        
        $.ajax({
            type: 'POST',
            url: baseUrl + 'administrator/pages/includes/modals/controllers/PositionController.php',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (!response.error) {
                    $('#addnew').modal('hide');
                    $('#addPositionForm')[0].reset();
                    location.reload();
                }
                // Show response message using SweetAlert2
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

    // Edit Position Form Submit
    $('#editPositionForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize() + '&action=edit';
        
        $.ajax({
            type: 'POST',
            url: baseUrl + 'administrator/pages/includes/modals/controllers/PositionController.php',
            data: formData,
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

    // Delete Position Form Submit
    $('#deletePositionForm').on('submit', function(e) {
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
                    url: baseUrl + 'administrator/pages/includes/modals/controllers/PositionController.php',
                    data: formData,
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
});

// Function to get position details for edit/delete modals
function getRow(id) {
    $.ajax({
        type: 'POST',
        url: baseUrl + 'administrator/pages/includes/modals/controllers/PositionController.php',
        data: {
            action: 'get',
            id: id
        },
        dataType: 'json',
        success: function(response) {
            if (!response.error) {
                $('.position_id').val(response.data.id);
                $('#edit_description').val(response.data.description);
                $('#edit_max_vote').val(response.data.max_vote);
                $('.description').html(response.data.description);
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