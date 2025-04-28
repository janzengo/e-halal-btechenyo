<!-- Add -->
<div class="modal fade" id="addnew">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title"><b>Add New Officer</b></h4>
            </div>
            <form class="form-horizontal" method="POST" action="<?php echo BASE_URL; ?>administrator/pages/includes/modals/controllers/OfficerController.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label for="add_username" class="col-sm-3 control-label">Username</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="add_username" name="username" placeholder="Enter username" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="add_email" class="col-sm-3 control-label">Email</label>
                        <div class="col-sm-9">
                            <input type="email" class="form-control" id="add_email" name="email" placeholder="Enter email" required>
                            <small class="help-block">Officer credentials will be sent to this email</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <?php 
                        $set = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        $password = substr(str_shuffle($set), 0, 15);
                        ?>
                        <label for="add_password" class="col-sm-3 control-label">Password</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" id="add_password" name="password" value="<?php echo $password; ?>" required readonly>
                            <small class="help-block">System-generated password - will be sent to officer's email</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="add_firstname" class="col-sm-3 control-label">First Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="add_firstname" name="firstname" placeholder="Enter first name" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="add_lastname" class="col-sm-3 control-label">Last Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="add_lastname" name="lastname" placeholder="Enter last name" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="add_gender" class="col-sm-3 control-label">Gender</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="add_gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="add_role" class="col-sm-3 control-label">Role</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="add_role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="head">Head</option>
                                <option value="officer">Officer</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">
                        <i class="fa fa-close"></i> Close
                    </button>
                    <button type="submit" class="btn btn-primary btn-flat custom">
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
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title"><b>Edit Officer</b></h4>
            </div>
            <form class="form-horizontal" method="POST" action="<?php echo BASE_URL; ?>administrator/pages/includes/modals/controllers/OfficerController.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" class="officer_id" name="id">
                    <div class="form-group">
                        <label for="edit_username" class="col-sm-3 control-label">Username</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="edit_username" name="username" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_email" class="col-sm-3 control-label">Email</label>
                        <div class="col-sm-9">
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_password" class="col-sm-3 control-label">Password</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" id="edit_password" name="password">
                            <small class="help-block">Leave blank if you don't want to change password</small>
                        </div>
                    </div>
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
                        <label for="edit_gender" class="col-sm-3 control-label">Gender</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="edit_gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_role" class="col-sm-3 control-label">Role</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="edit_role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="head">Head</option>
                                <option value="officer">Officer</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">
                        <i class="fa fa-close"></i> Close
                    </button>
                    <button type="submit" class="btn btn-primary btn-flat custom">
                        <i class="fa fa-check"></i> Update
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
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title"><b>Deleting...</b></h4>
            </div>
            <form class="form-horizontal" method="POST" action="<?php echo BASE_URL; ?>administrator/pages/includes/modals/controllers/OfficerController.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" class="officer_id" name="id">
                    <div class="text-center">
                        <p>DELETE OFFICER</p>
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


     