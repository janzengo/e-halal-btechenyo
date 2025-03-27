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
            <form class="form-horizontal" method="POST" action="<?php echo BASE_URL; ?>administrator/pages/includes/modals/controllers/CandidateController.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
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
                        <label for="add_position" class="col-sm-3 control-label">Position</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="add_position" name="position_id" required>
                                <option value="">Select Position</option>
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
                        <label for="add_partylist" class="col-sm-3 control-label">Partylist</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="add_partylist" name="partylist_id">
                                <option value="">Select Partylist (Optional)</option>
                                <?php
                                $partylists = $partylist->getAllPartylists();
                                foreach($partylists as $party){
                                    echo "<option value='".$party['id']."'>".$party['name']."</option>";
                                }
                                ?>
                            </select>
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
                            <input type="file" class="form-control" id="add_photo" name="photo" accept="image/*">
                            <small class="help-block">Max file size: 2MB. Allowed formats: JPG, PNG</small>
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
            <form class="form-horizontal" method="POST" action="<?php echo BASE_URL; ?>administrator/pages/includes/modals/controllers/CandidateController.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
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
                                <option value="">Select Position</option>
                                <?php
                                foreach($positions as $pos){
                                    echo "<option value='".$pos['id']."'>".$pos['description']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_partylist" class="col-sm-3 control-label">Partylist</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="edit_partylist" name="partylist_id">
                                <option value="">Select Partylist (Optional)</option>
                                <?php
                                foreach($partylists as $party){
                                    echo "<option value='".$party['id']."'>".$party['name']."</option>";
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
                            <input type="file" class="form-control" id="edit_photo" name="photo" accept="image/*">
                            <small class="help-block">Max file size: 2MB. Allowed formats: JPG, PNG</small>
                            <div id="current_photo" class="mt-2"></div>
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
            <form class="form-horizontal" method="POST" action="<?php echo BASE_URL; ?>administrator/pages/includes/modals/controllers/CandidateController.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
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
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Edit button click handler
    $(document).on('click', '.edit', function(e){
        e.preventDefault();
        $('#edit').modal('show');
        var id = $(this).data('id');
        getRow(id);
    });

    // Delete button click handler
    $(document).on('click', '.delete', function(e){
        e.preventDefault();
        $('#delete').modal('show');
        var id = $(this).data('id');
        getRow(id);
    });
});

function getRow(id){
    $.ajax({
        type: 'POST',
        url: '<?php echo BASE_URL; ?>administrator/pages/includes/modals/controllers/CandidateController.php',
        data: {id:id, action:'get'},
        dataType: 'json',
        success: function(response){
            if (!response.error) {
                $('.candidate_id').val(response.data.id);
                $('#edit_firstname').val(response.data.firstname);
                $('#edit_lastname').val(response.data.lastname);
                $('#edit_position').val(response.data.position_id);
                $('#edit_partylist').val(response.data.partylist_id);
                $('#edit_platform').val(response.data.platform);
                $('.fullname').html(response.data.firstname+' '+response.data.lastname);
                $('#current_photo').html('<img src="<?php echo BASE_URL; ?>administrator/assets/images/candidates/' + response.data.photo + '" class="img-thumbnail" width="100">');
            }
        }
    });
}
</script>
