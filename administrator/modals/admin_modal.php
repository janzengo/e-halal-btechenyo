<?php
require_once __DIR__ . '/../classes/Admin.php';

// Get admin data
$admin = Admin::getInstance();
$adminData = $admin->getAdminData();
?>

<!-- Admin Profile Modal -->
<div class="modal fade" id="admin_profile_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><b>Admin Profile</b></h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" action="<?php echo BASE_URL; ?>administrator/modals/controllers/AdminController.php" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update">
                    <div class="form-group">
                        <label for="username" class="col-sm-3 control-label">Username</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo $adminData['username']; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password" class="col-sm-3 control-label">Password</label>
                        <div class="col-sm-9"> 
                            <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Enter new password (leave blank to keep current)">
                            <small class="help-block">Leave blank to keep your current password</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="firstname" class="col-sm-3 control-label">Firstname</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="firstname" name="firstname" value="<?php echo $adminData['firstname']; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="lastname" class="col-sm-3 control-label">Lastname</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="lastname" name="lastname" value="<?php echo $adminData['lastname']; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="photo" class="col-sm-3 control-label">Photo:</label>
                        <div class="col-sm-9">
                            <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                            <small class="help-block">Max file size: 2MB. Allowed formats: JPG, PNG</small>
                            <?php if(!empty($adminData['photo']) && $adminData['photo'] != 'assets/images/profile.jpg'): ?>
                            <div class="mt-2">
                                <img src="<?php echo BASE_URL; ?>administrator/<?php echo $adminData['photo']; ?>" class="img-thumbnail" width="100">
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label for="curr_password" class="col-sm-3 control-label">Current Password:</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" id="curr_password" name="curr_password" placeholder="input current password to save changes" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
                    <button type="submit" class="btn btn-success btn-flat" name="save"><i class="fa fa-check-square-o"></i> Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(function() {
    // Handle admin profile modal
    $(document).on('click', '#admin_profile', function(e) {
        e.preventDefault();
        $('#admin_profile_modal').modal('show');
    });
});
</script> 