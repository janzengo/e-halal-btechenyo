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
            <form id="addPositionForm" class="form-horizontal" action="<?php echo BASE_URL; ?>administrator/pages/includes/modals/controllers/PositionController.php">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="description" class="col-sm-3 control-label">Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="description" name="description" placeholder="Enter position name" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="max_vote" class="col-sm-3 control-label">Maximum Vote</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" id="max_vote" name="max_vote" placeholder="Enter maximum vote" required min="1">
                            <small class="text-muted"><i class="fa fa-info-circle"></i> Maximum vote is the maximum number of votes a candidate can receive for a position.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
                    <button type="submit" class="btn btn-primary btn-flat custom"><i class="fa fa-save"></i> Save</button>
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
            <form id="editPositionForm" class="form-horizontal" action="<?php echo BASE_URL; ?>administrator/pages/includes/modals/controllers/PositionController.php">
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
                    <button type="submit" class="btn btn-success btn-flat custom"><i class="fa fa-check"></i> Update</button>
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
            <form id="deletePositionForm" class="form-horizontal" action="<?php echo BASE_URL; ?>administrator/pages/includes/modals/controllers/PositionController.php">
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
</script>