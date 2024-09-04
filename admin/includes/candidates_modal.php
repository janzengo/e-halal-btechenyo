<!-- Description -->
<div class="modal fade" id="view_platform">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b><span class="fullname"></span></b></h4>
            </div>
            <div class="modal-body">
                <p id="desc"></p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add -->
<div class="modal fade" id="addnew">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b>Add New Candidate</b></h4>
            </div>
            <div class="modal-body">
              <form class="form-horizontal" method="POST" action="candidates_add.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="firstname" class="col-sm-3 control-label">Firstname</label>

                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="firstname" name="firstname" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="lastname" class="col-sm-3 control-label">Lastname</label>

                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="lastname" name="lastname" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="position" class="col-sm-3 control-label">Position</label>

                    <div class="col-sm-9">
                      <select class="form-control" id="position" name="position" required>
                        <option value="" selected>- Select -</option>
                        <?php
                          $sql = "SELECT * FROM positions";
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
                    <label for="partylist" class="col-sm-3 control-label">Partylist</label>

                    <div class="col-sm-9">
                      <select class="form-control" id="partylist" name="partylist" required>
                        <option value="" selected id="posselect">- Select -</option>
                        <?php
                          $sql = "SELECT * FROM partylists";
                          $query = $conn->query($sql);
                          while($row = $query->fetch_assoc()){
                            echo "
                              <option value='".$row['id']."'>".$row['name']."</option>
                            ";
                          }
                        ?>
                      </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="photo" class="col-sm-3 control-label">Photo</label>

                    <div class="col-sm-9">
                      <input type="file" id="photo" name="photo">
                    </div>
                </div>
                <div class="form-group">
                    <label for="platform" class="col-sm-3 control-label">Platform</label>

                    <div class="col-sm-9">
                      <textarea class="form-control" id="platform" name="platform" rows="7"></textarea>
                    </div>
                </div>
            </div>
            <input type="hidden" id="origin" name="origin">
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
              <h4 class="modal-title"><b>Edit Candidate</b></h4>
            </div>
            <div class="modal-body">
              <form class="form-horizontal" method="POST" action="candidates_edit.php">
                <input type="hidden" class="id" name="id">
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
                    <label for="edit_position" class="col-sm-3 control-label">Position</label>
                    <div class="col-sm-9">
                      <select class="form-control" id="edit_position" name="position" required>
                        <option value="" selected id="posselect">- Select -</option>
                        <?php
                          $sql = "SELECT * FROM positions";
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
                    <label for="edit_partylist" class="col-sm-3 control-label">Partylist</label>
                    <div class="col-sm-9">
                      <select class="form-control" id="edit_partylist" name="partylist" required>
                        <option value="" selected id="posselect">- Select -</option>
                        <?php
                          $sql = "SELECT * FROM partylists";
                          $query = $conn->query($sql);
                          while($row = $query->fetch_assoc()){
                            echo "
                              <option value='".$row['id']."'>".$row['name']."</option>
                            ";
                          }
                        ?>
                      </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit_platform" class="col-sm-3 control-label">Platform</label>
                    <div class="col-sm-9">
                      <textarea class="form-control" id="edit_platform" name="platform" rows="7"></textarea>
                    </div>
                </div>
            </div>
            <input type="hidden" id="origin" name="origin">
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
              <form class="form-horizontal" method="POST" action="candidates_delete.php">
                <input type="hidden" class="id" name="id">
                <div class="text-center">
                    <p>DELETE CANDIDATE</p>
                    <h2 class="bold fullname"></h2>
                </div>
            </div>
            <input type="hidden" id="origin" name="origin">
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
              <button type="submit" class="btn btn-danger btn-flat" name="delete"><i class="fa fa-trash"></i> Delete</button>
              </form>
            </div>
        </div>
    </div>
</div>

<!-- Update Photo -->
<div class="modal fade" id="edit_photo">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b><span class="fullname"></span></b></h4>
            </div>
            <div class="modal-body">
              <form class="form-horizontal" method="POST" action="candidates_photo.php" enctype="multipart/form-data">
                <input type="hidden" class="id" name="id">
                <div class="form-group">
                    <label for="photo" class="col-sm-3 control-label">Photo</label>

                    <div class="col-sm-9">
                      <input type="file" id="photo" name="photo" required>
                    </div>
                </div>
            </div>
            <input type="hidden" id="origin" name="origin">
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
              <button type="submit" class="btn btn-success btn-flat" name="upload"><i class="fa fa-edit"></i> Update</button>
              </form>
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