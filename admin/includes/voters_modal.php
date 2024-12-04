<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css">
<style>
  .dropzone {
    border: 2px dashed #1d7c39 !important;
    border-radius: 5px;
    padding: 40px;
    text-align: center;
    cursor: pointer;
  }
  .dropzone .dz-message {
    font-size: 18px;
    color: #666;
    text-align: center;
    transition: all ease 0.3s;
  }
  .dropzone:hover .dz-message {
    color: #1d7c39 !important;
  }
</style>

<!-- Add -->
<div class="modal fade" id="addnew">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b>Add New Voter</b></h4>
            </div>
            <div class="modal-body">
              <form class="form-horizontal" method="POST" action="voters_add.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="firstname" class="col-sm-3 control-label">Firstname</label>

                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="firstname" name="firstname" placeholder="Enter voter's first name." required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="lastname" class="col-sm-3 control-label">Lastname</label>

                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="lastname" name="lastname" placeholder="Enter voter's last name." required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="studentNumber" class="col-sm-3 control-label">Student Number</label>

                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="studentNumber" name="studentNumber" placeholder="You will use this to log in." required>
                    </div>
                </div>

                <!-- COURSE -->
                <div class="form-group">
                    <label for="course" class="col-sm-3 control-label">Course</label>

                    <div class="col-sm-9">
                      <select class="form-control" id="course" name="course" required>
                        <option value="" selected>- Select -</option>
                        <?php
                          $sql = "SELECT * FROM courses";
                          $query = $conn->query($sql);
                          while($row = $query->fetch_assoc()){
                            echo "
                              <option value='".$row['id']."'>".$row['description']."</option>
                            ";
                          }
                        ?>
                      </select>
                    </div>
                </div>

                <div class="form-group">
                  <?php 
                    $set = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    $password = substr(str_shuffle($set), 0, 15);
                  ?>
                    <label for="password" class="col-sm-3 control-label">Password</label>

                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="password" name="password" value="<?php echo $password; ?>" required readonly>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
              <button type="submit" class="btn btn-primary btn-flat" name="add"><i class="fa fa-save"></i> Save</button>
              </form>
            </div>
        </div>
    </div>
</div>

<!-- Import CSV Modal -->
<div class="modal fade" id="importcsv">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><b>Import Voters from CSV</b></h4>
      </div>
      <div class="modal-body">
        <form id="dropzone-form" action="new_csv.php" method="POST" enctype="multipart/form-data">
          <div class="form-group">
              <div id="dropzone" class="dropzone">
                <div class="dz-message"><i class="fa fa-cloud-upload"></i> Drop CSV file here to upload</div>
            </div>
          </div>
          <div id="uploadMessage" class="alert" role="alert" style="display:none;"></div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
        <button type="button" class="btn btn-primary btn-flat" id="submit-dropzone" disabled><i class="fa fa-cloud-upload"></i> Import</button>
      </div>
    </div>
  </div>
</div>

<!-- Include Dropzone CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css">


<!-- Edit -->
<div class="modal fade" id="edit">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b>Edit Voter</b></h4>
            </div>
            <div class="modal-body">
              <form class="form-horizontal" method="POST" action="voters_edit.php">
                <input type="hidden" class="id" name="id">
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
                    <label for="edit_studentNumber" class="col-sm-3 control-label">Student Number</label>

                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="edit_studentNumber" name="studentNumber">
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit_course" class="col-sm-3 control-label">Course</label>

                    <div class="col-sm-9">
                      <select class="form-control" id="edit_course" name="course" required>
                        <option value="" selected>- Select -</option>
                        <?php
                          $sql = "SELECT * FROM courses";
                          $query = $conn->query($sql);
                          while($row = $query->fetch_assoc()){
                            echo "
                              <option value='".$row['id']."'>".$row['description']."</option>
                            ";
                          }
                        ?>
                      </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit_password" class="col-sm-3 control-label">Password</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="edit_password" name="password">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
              <button type="submit" class="btn btn-success btn-flat" name="edit"><i class="fa fa-edit"></i> Update</button>
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
              <form class="form-horizontal" method="POST" action="voters_delete.php">
                <input type="hidden" class="id" name="id">
                <div class="text-center">
                    <p>DELETE VOTER</p>
                    <h2 class="bold fullname"></h2>
                </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
              <button type="submit" class="btn btn-danger btn-flat" name="delete"><i class="fa fa-trash"></i> Delete</button>
              </form>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Delete -->
<div class="modal fade" id="bulk-delete">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b>Confirm Bulk Delete</b></h4>
            </div>
            <div class="modal-body">
              <div class="text-center">
                <p>DELETE VOTERS</p>
                <h4>Are you sure you want to delete the selected voters?</h4>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
              <button type="button" class="btn btn-danger btn-flat" id="confirm-bulk-delete-btn" name="bulk_delete"><i class="fa fa-trash"></i> Delete</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
  // Disable autoDiscover
Dropzone.autoDiscover = false;

document.addEventListener('DOMContentLoaded', function() {
    var myDropzone = new Dropzone("#dropzone", {
        url: "new_csv.php",
        method: "POST",
        paramName: "file",
        autoProcessQueue: false, // Set to false to prevent automatic upload
        acceptedFiles: ".csv",
        maxFiles: 1,
        maxFilesize: 5, // MB
        uploadMultiple: false,
        addRemoveLinks: true,
        dictRemoveFileConfirmation: "Are you sure?",
        dictFileTooBig: "File is too big ({{filesize}}mb). Max allowed file size is {{maxFilesize}}mb",
        dictInvalidFileType: "Invalid file type",
        dictCancelUpload: "Cancel",
        dictRemoveFile: "Remove",
        dictMaxFilesExceeded: "Only {{maxFiles}} file is allowed",
        dictDefaultMessage: "Drop CSV file here to upload",

        init: function() {
            var submitButton = document.querySelector("#submit-dropzone");
            var myDropzone = this;
            submitButton.disabled = true;

            this.on("addedfile", function(file) {
                submitButton.disabled = false;
            });

            this.on("removedfile", function(file) {
                if (myDropzone.files.length === 0) {
                    submitButton.disabled = true;
                }
            });

            // Adding extra data
            this.on("sending", function(file, xhr, formData) {
                formData.append("dropzone", "1"); // Additional POST data
            });

            // On success
            this.on("success", function(file, response) {
                console.log(response);
                // Display success message
                showMessage('File uploaded successfully', 'alert-success');
                setTimeout(function() {
                  window.location.href = 'voters.php';
                }, 2000);
            });

            // On error
            this.on("error", function(file, response) {
                console.log(response);
                // Display error message
                showMessage('Error uploading file: ' + response, 'alert-danger');
                setTimeout(function() {
                  window.location.href = 'voters.php';
                }, 2000);
            });
        }
    });

    document.querySelector("#submit-dropzone").addEventListener("click", function(e) {
        e.preventDefault();
        e.stopPropagation();

        if (myDropzone.files.length > 0) {
            myDropzone.processQueue();
        } else {
            document.getElementById("dropzone-form").submit();
        }
    });

    function showMessage(message, alertType) {
    const messageDiv = document.getElementById('uploadMessage');
    let iconClass = '';
    let alertTitle = '';

    switch(alertType) {
        case 'alert-success':
            iconClass = 'fa fa-check';
            alertTitle = 'Success!';
            break;
        case 'alert-danger':
            iconClass = 'fa fa-ban';
            alertTitle = 'Error!';
            break;
        case 'alert-warning':
            iconClass = 'fa fa-exclamation-triangle';
            alertTitle = 'Warning!';
            break;
        case 'alert-info':
            iconClass = 'fa fa-info';
            alertTitle = 'Info!';
            break;
        default:
            iconClass = 'fa fa-info';
            alertTitle = 'Info!';
            break;
    }

    messageDiv.className = 'alert ' + alertType;
    messageDiv.innerHTML = `<h4><i class='icon ${iconClass}'></i> ${alertTitle}</h4> ${message}`;
    messageDiv.style.display = 'block';
}
});
</script>
