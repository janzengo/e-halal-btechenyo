<!-- Add -->
<div class="modal fade" id="addnew">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b>Add New Officer</b></h4>
            </div>
            <div class="modal-body">
              <form class="form-horizontal" method="POST" action="<?php echo BASE_URL; ?>administrator/pages/includes/modals/controllers/OfficerController.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="add_firstname" class="col-sm-3 control-label">Firstname</label>

                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="add_firstname" name="firstname" placeholder="Enter Officer's first name." required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="add_lastname" class="col-sm-3 control-label">Lastname</label>

                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="add_lastname" name="lastname" placeholder="Enter Officer's last name." required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="add_username" class="col-sm-3 control-label">Username</label>

                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="add_username" name="username" placeholder="Officer will use this to log in." required>
                    </div>
                </div>
                
                <!-- Gender -->
                <div class="form-group">
                    <label for="add_gender" class="col-sm-3 control-label">Gender</label>

                    <div class="col-sm-9">
                      <select class="form-control" id="add_gender" name="gender" required>
                        <option value="" selected disabled>- Select -</option>
                        <option value='Male'>Male</option>
                        <option value='Female'>Female</option>
                      </select>
                    </div>
                </div>

                <div class="form-group">
                  <?php 
                    $set = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    $password = substr(str_shuffle($set), 0, 15);
                  ?>
                    <label for="add_password" class="col-sm-3 control-label">Password</label>

                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="add_password" name="password" value="<?php echo $password; ?>" required readonly>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
              <input type="hidden" name="action" value="add">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
              <button type="submit" class="btn btn-primary btn-flat" name="add"><i class="fa fa-save"></i> Save</button>
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
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b>Edit Officer</b></h4>
            </div>
            <div class="modal-body">
              <form class="form-horizontal" method="POST" action="<?php echo BASE_URL; ?>administrator/pages/includes/modals/controllers/OfficerController.php">
                <input type="hidden" class="admin_id" name="id">
                <div class="form-group">
                    <label for="edit_firstname" class="col-sm-3 control-label">Firstname</label>

                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="edit_firstname" name="firstname">
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit_lastname" class="col-sm-3 control-label">Lastname</label>

                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="edit_lastname" name="lastname">
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit_username" class="col-sm-3 control-label">Username</label>

                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="edit_username" name="username">
                    </div>
                </div>
                
                <!-- Gender -->
                <div class="form-group">
                    <label for="edit_gender" class="col-sm-3 control-label">Gender</label>

                    <div class="col-sm-9">
                      <select class="form-control" id="edit_gender" name="gender" required>
                        <option value="" selected>- Select -</option>
                        <option value='Male'>Male</option>
                        <option value='Female'>Female</option>
                      </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_password" class="col-sm-3 control-label">Password</label>

                    <div class="col-sm-9">
                      <input type="password" class="form-control" id="edit_password" name="password">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
              <button type="submit" class="btn btn-success btn-flat" name="edit"><i class="fa fa-edit"></i> Update</button>
              <input type="hidden" name="action" value="edit">
              </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete -->
<div class="modal fade" id="delete">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b>Deleting...</b></h4>
            </div>
            <div class="modal-body">
              <form class="form-horizontal" method="POST" action="<?php echo BASE_URL; ?>administrator/pages/includes/modals/controllers/OfficerController.php">
                <input type="hidden" class="admin_id" name="id">
                <div class="text-center">
                    <p>DELETE OFFICER</p>
                    <h2 class="bold fullname"></h2>
                </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
              <button type="submit" class="btn btn-danger btn-flat" name="delete"><i class="fa fa-trash"></i> Delete</button>
              <input type="hidden" name="action" value="delete">
              </form>
            </div>
        </div>
    </div>
</div>


     