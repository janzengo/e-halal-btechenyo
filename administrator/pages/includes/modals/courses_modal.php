<?php
// Security is handled in the main page (courses.php)
?>

<!-- Add -->
<div class="modal fade" id="addnew">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><b>Add New Course</b></h4>
            </div>
            <div class="modal-body">
                <form action="<?php echo BASE_URL; ?>administrator/pages/includes/modals/controllers/CourseController.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label>Course Description</label>
                        <input type="text" class="form-control" name="description" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
                        <button type="submit" class="btn btn-primary btn-flat"><i class="fa fa-save"></i> Save</button>
                    </div>
                </form>
            </div>
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
                <h4 class="modal-title"><b>Edit Course</b></h4>
            </div>
            <div class="modal-body">
                <form action="<?php echo BASE_URL; ?>administrator/pages/includes/modals/controllers/CourseController.php" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" class="course_id">
                    <div class="form-group">
                        <label>Course Description</label>
                        <input type="text" class="form-control" name="description" id="edit_description" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
                        <button type="submit" class="btn btn-primary btn-flat"><i class="fa fa-save"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>